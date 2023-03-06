<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Composer\MakeBom;

use Composer\Composer;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\LockArrayRepository;
use Composer\Semver\Constraint\MatchAllConstraint;
use CycloneDX\Composer\Properties;
use CycloneDX\Core\Enums;
use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models;
use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use Exception;
use Generator;
use PackageUrl\PackageUrl;
use RuntimeException;
use ValueError;

/**
 * @internal
 *
 * @author jkowalleck
 */
class Builder
{
    private const ComposerPackageType_Plugin = [
        'composer-plugin',
        'composer-installer',
    ];

    /**
     * @throws RuntimeException if loading licenses failed
     */
    public function __construct(
        private readonly bool $omitDev,
        private readonly bool $omitPlugin,
        private readonly ?string $mainComponentVersion,
        private readonly LicenseFactory $licenseFactory = new LicenseFactory(new SpdxLicenseValidator())
    ) {
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function createSbomFromComposer(Composer $composer): Models\Bom
    {
        $rootPackage = $composer->getPackage();
        $rootComponent = $this->createComponentFromRootPackage($rootPackage);

        $withDevReqs = false === $this->omitDev;
        $packagesRepo = $this->getPackageRepo($composer, $withDevReqs);

        // region packages & components
        /**
         * @psalm-var list<PackageInterface> $packages
         *
         * @psalm-suppress MixedArgument
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $packages = array_values(
            method_exists($packagesRepo, 'getCanonicalPackages')
            // since composer 2.4
            ? $packagesRepo->getCanonicalPackages()
            : $packagesRepo->getPackages()
        );
        /** @psalm-var array<string, Models\Component> */
        $components = [];
        foreach ($packages as $package) {
            if ($this->omitPlugin && \in_array($package->getType(), self::ComposerPackageType_Plugin)) {
                continue;
            }
            $components[$package->getName()] = $this->createComponentFromPackage($package);
        }
        unset($package);
        // endregion packages & components

        // region mark/omit dev-dependencies
        $devDependencies = $packagesRepo instanceof InstalledRepositoryInterface
            ? $packagesRepo->getDevPackageNames()
            : $composer->getLocker()->getDevPackageNames();
        foreach ($rootPackage->getDevRequires() as $required) {
            $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
            if (null === $requiredPackage) {
                continue;
            }
            $devDependencies[] = $requiredPackage->getName();
            unset($required, $requiredPackage);
        }
        if ($withDevReqs) {
            foreach (array_unique($devDependencies) as $packageName) {
                if (isset($components[$packageName])) {
                    $components[$packageName]->getProperties()->addItems(
                        new Models\Property(Properties::Name_DevRequirement, Properties::Value_True));
                }
            }
        } else {
            foreach ($devDependencies as $packageName) {
                unset($components[$packageName]);
            }
        }
        unset($devDependencies, $packageName);
        // endregion mark dev-dependencies

        // region dependency graph
        /** ALL Components, also the RootComponent, to make circular dependencies visible */
        $allComponents = [$rootPackage->getName() => $rootComponent] + $components;
        /**
         * @var PackageInterface $package
         *
         * @psalm-suppress UnnecessaryVarAnnotation -- as it is needed for some IDE
         */
        foreach ($packages as $package) {
            $component = $allComponents[$package->getName()] ?? null;
            if (null === $component) {
                continue;
            }
            foreach ($package->getRequires() as $required) {
                $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
                if (null === $requiredPackage) {
                    continue;
                }
                $dependency = $allComponents[$requiredPackage->getName()] ?? null;
                if (null !== $dependency) {
                    $component->getDependencies()->addItems($dependency->getBomRef());
                }
            }
            unset($package, $component, $required, $dependency);
        }
        foreach ([...$rootPackage->getRequires(), ...$rootPackage->getDevRequires()] as $required) {
            $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
            if (null === $requiredPackage) {
                continue;
            }
            $dependency = $allComponents[$requiredPackage->getName()] ?? null;
            if (null !== $dependency) {
                $rootComponent->getDependencies()->addItems($dependency->getBomRef());
            }
        }
        unset($allComponents);
        // endregion dependency graph

        // region finalize components
        $bom = new Models\Bom();
        $bom->getMetadata()->setComponent($rootComponent);
        $bom->getComponents()->addItems(...$components);
        // endregion finalize components

        return $bom;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromRootPackage(RootPackageInterface $package): Models\Component
    {
        $component = $this->createComponentFromPackage($package, $this->mainComponentVersion);

        if (RootPackage::DEFAULT_PRETTY_VERSION === $component->getVersion()) {
            $component->setVersion(null);
        }

        return $component
            ->setType(Enums\ComponentType::APPLICATION)
            ->setPackageUrl($this->createPurlFromComponent($component));
    }

    /**
     * return tuple: ($group:?string, $name:string).
     *
     * @psalm-return array{0:null|string, 1:string}
     */
    private function getGroupAndName(string $composerPackageName): array
    {
        $groupAndName = explode('/', $composerPackageName, 2);

        return 2 === \count($groupAndName)
            ? [$groupAndName[0], $groupAndName[1]]
            : [null, $groupAndName[0]];
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromPackage(PackageInterface $package, ?string $versionOverride = null): Models\Component
    {
        [$group, $name] = $this->getGroupAndName($package->getName());
        $version = $versionOverride ?? $package->getFullPrettyVersion();
        $distReference = $package->getDistReference();
        $sourceReference = $package->getSourceReference();

        $component = new Models\Component(Enums\ComponentType::LIBRARY, $name);
        $component->setBomRefValue($package->getUniqueName());
        $component->setGroup($group);
        $component->setVersion($version);
        $component->getHashes()->set(Enums\HashAlgorithm::SHA_1, $package->getDistSha1Checksum());
        $component->getExternalReferences()->addItems(
            ...$this->createExternalReferencesFromPackage($package)
        );

        if (null !== $distReference && '' !== $distReference) {
            $component->getProperties()->addItems(
                new Models\Property(Properties::Name_DistReference, $distReference)
            );
        }
        if (null !== $sourceReference && '' !== $sourceReference) {
            $component->getProperties()->addItems(
                new Models\Property(Properties::Name_SourceReference, $sourceReference)
            );
        }

        if ($package instanceof CompletePackageInterface) {
            $component->setDescription($package->getDescription());
            $component->setAuthor($this->createAuthorString($package));
            $component->getLicenses()->addItems(
                ...array_map(
                    $this->licenseFactory->makeFromString(...),
                    $package->getLicense()
                )
            );
        }

        $component->getProperties()->addItems(
            new Models\Property(Properties::Name_PackageType, $package->getType())
        );

        // TODO continue set needed information

        return $component;
    }

    private function createPurlFromComponent(Models\Component $component): ?PackageUrl
    {
        // TODO build from non-packagist sources

        try {
            $purl = new PackageUrl('composer', $component->getName());
        } catch (\Throwable) {
            return null;
        }

        $purl->setNamespace($component->getGroup());
        $purl->setVersion($component->getVersion());

        $sha1Sum = $component->getHashes()->get(Enums\HashAlgorithm::SHA_1);
        if (null !== $sha1Sum) {
            $purl->setChecksums(["sha1:$sha1Sum"]);
        }

        return $purl;
    }

    /**
     * @psalm-return Generator<int,Models\ExternalReference>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createExternalReferencesFromPackage(PackageInterface $package): Generator
    {
        foreach ($package->getDistUrls() as $distUrl) {
            yield new Models\ExternalReference(
                Enums\ExternalReferenceType::DISTRIBUTION,
                $distUrl
            );
        }

        foreach ($package->getSourceUrls() as $sourceUrl) {
            yield new Models\ExternalReference(
                Enums\ExternalReferenceType::VCS,
                $sourceUrl
            );
        }

        if ($package instanceof CompletePackageInterface) {
            $homepage = $package->getHomepage();
            if (null !== $homepage) {
                yield (new Models\ExternalReference(
                    Enums\ExternalReferenceType::WEBSITE,
                    $homepage
                ))->setComment("as detected from Composer manifest 'homepage'");
            }

            foreach ($package->getSupport() as $supportType => $supportUrl) {
                $extRefType = match ($supportType) {
                    'chat', 'irc' => Enums\ExternalReferenceType::CHAT,
                    'docs' => Enums\ExternalReferenceType::DOCUMENTATION,
                    'issues' => Enums\ExternalReferenceType::ISSUE_TRACKER,
                    'source' => Enums\ExternalReferenceType::VCS,
                    default => Enums\ExternalReferenceType::OTHER,
                };
                yield (new Models\ExternalReference(
                    $extRefType,
                    $supportUrl
                ))->setComment("as detected from Composer manifest 'support.$supportType'");
            }
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function getPackageRepo(Composer $composer, bool $withDevReqs): InstalledRepositoryInterface|LockArrayRepository
    {
        $packagesRepo = $composer->getRepositoryManager()->getLocalRepository();
        if (!$packagesRepo->isFresh()) {
            return $packagesRepo;
        }

        $composerLocker = $composer->getLocker();
        $withDevReqs = $withDevReqs && isset($composerLocker->getLockData()['packages-dev']);

        return $composerLocker->getLockedRepository($withDevReqs);
    }

    /**
     * @psalm-return Generator<int,Models\Tool>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function createToolsFromComposer(
        Composer $composer,
        ?string $versionOverride = null, bool $excludeLibs = false
    ): Generator {
        $packageNames = [
            'cyclonedx/cyclonedx-php-composer',
        ];
        if (!$excludeLibs) {
            $packageNames[] = 'cyclonedx/cyclonedx-library';
        }

        $packagesRepo = $this->getPackageRepo($composer, true);

        foreach ($packageNames as $packageName) {
            try {
                yield $this->createToolFromPackage(
                    $packagesRepo->findPackage($packageName, new MatchAllConstraint())
                    ?? throw new ValueError("package not found: $packageName"),
                    $versionOverride
                );
            } catch (\Throwable) {
                /* pass */
            }
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createToolFromPackage(PackageInterface $package, ?string $versionOverride = null): Models\Tool
    {
        [$group, $name] = $this->getGroupAndName($package->getName());

        $tool = new Models\Tool();
        $tool->setName($name);
        $tool->setVendor($group);
        $tool->setVersion($versionOverride ?? $package->getFullPrettyVersion());
        $tool->getHashes()->set(Enums\HashAlgorithm::SHA_1, $package->getDistSha1Checksum());
        $tool->getExternalReferences()->addItems(
            ...$this->createExternalReferencesFromPackage($package)
        );

        return $tool;
    }

    private function createAuthorString(CompletePackageInterface $package): string
    {
        return implode(', ', array_filter(
            array_map(
                static fn (array $a): string => trim($a['name'] ?? ''),
                $package->getAuthors()
            )
        ));
    }

    /**
     * @throws Exception if an appropriate source of randomness cannot be found
     */
    public static function createRandomBomSerialNumber(): string
    {
        return sprintf(
            'urn:uuid:%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),
            // UUID version 4
            random_int(0, 0x0FFF) | 0x4000,
            // UUID version 4 variant 1
            random_int(0, 0x3FFF) | 0x8000,
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),
        );
    }
}
