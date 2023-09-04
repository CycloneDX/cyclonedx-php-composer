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
use Generator;
use PackageUrl\PackageUrl;
use RuntimeException;
use Throwable;
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
        private readonly LicenseFactory $licenseFactory = new LicenseFactory()
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
        $packagesRepo = self::getPackageRepo($composer, $withDevReqs);

        // region packages & components
        /**
         * @var PackageInterface[] $packages
         *
         * @psalm-suppress MixedArgument
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $packages = method_exists($packagesRepo, 'getCanonicalPackages')
            ? $packagesRepo->getCanonicalPackages()
            : $packagesRepo->getPackages();
        /** @psalm-var array<string, Models\Component> $components */
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
        /** @var string[] $devDependencies */
        $devDependencies = method_exists($packagesRepo, 'getDevPackageNames')
            ? $packagesRepo->getDevPackageNames()
            : $composer->getLocker()->getDevPackageNames();
        foreach ($rootPackage->getDevRequires() as $required) {
            $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
            if (null !== $requiredPackage) {
                $devDependencies[] = $requiredPackage->getName();
            }
        }
        unset($required, $requiredPackage);
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
        unset($packageName, $devDependencies);
        // endregion mark dev-dependencies

        // region dependency graph
        /** ALL Components, also the RootComponent, to make circular dependencies visible */
        $allComponents = [$rootPackage->getName() => $rootComponent] + $components;
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
        }
        unset($package, $component, $required, $dependency);
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
        unset($required, $requiredPackage, $dependency, $allComponents);
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
        $component = $this->createComponentFromPackage(
            $package,
            $this->mainComponentVersion
        );

        if (RootPackage::DEFAULT_PRETTY_VERSION === $component->getVersion()) {
            $component->setVersion(null);
        }

        return $component
            ->setType(Enums\ComponentType::Application)
            ->setPackageUrl($this->createPurlFromComponent($component));
    }

    /**
     * return tuple: ($group:?string, $name:string).
     *
     * @psalm-return array{0:null|string, 1:string}
     *
     * @psalm-pure
     */
    private static function getGroupAndName(string $composerPackageName): array
    {
        $parts = explode('/', $composerPackageName, 2);

        return 2 === \count($parts)
            ? [$parts[0], $parts[1]]
            : [null, $parts[0]];
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromPackage(PackageInterface $package, string $versionOverride = null): Models\Component
    {
        [$group, $name] = self::getGroupAndName($package->getName());
        $version = $versionOverride ?? $package->getFullPrettyVersion();
        $distReference = $package->getDistReference();
        $sourceReference = $package->getSourceReference();

        $component = new Models\Component(Enums\ComponentType::Library, $name);
        $component->setBomRefValue($package->getUniqueName());
        $component->setGroup($group);
        $component->setVersion($version);
        $component->getHashes()->set(Enums\HashAlgorithm::SHA_1, $package->getDistSha1Checksum());
        $component->getExternalReferences()->addItems(
            ...self::createExternalReferencesFromPackage($package)
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

        $component->setPackageUrl($this->createPurlFromComponent($component));

        return $component;
    }

    private function createPurlFromComponent(Models\Component $component): ?PackageUrl
    {
        // TODO build from non-packagist sources

        try {
            $purl = new PackageUrl('composer', $component->getName());
        } catch (Throwable) {
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
    private static function createExternalReferencesFromPackage(PackageInterface $package): Generator
    {
        foreach ($package->getDistUrls() as $distUrl) {
            yield new Models\ExternalReference(
                Enums\ExternalReferenceType::Distribution,
                $distUrl
            );
        }

        foreach ($package->getSourceUrls() as $sourceUrl) {
            yield new Models\ExternalReference(
                // see https://github.com/CycloneDX/specification/issues/98
                Enums\ExternalReferenceType::VCS,
                $sourceUrl
            );
        }

        if ($package instanceof CompletePackageInterface) {
            $homepage = $package->getHomepage();
            if (null !== $homepage) {
                yield (new Models\ExternalReference(
                    Enums\ExternalReferenceType::Website,
                    $homepage
                ))->setComment("as detected from Composer manifest 'homepage'");
            }

            foreach ($package->getSupport() as $supportType => $supportUrl) {
                $extRefType = match ($supportType) {
                    'chat', 'irc' => Enums\ExternalReferenceType::Chat,
                    'docs' => Enums\ExternalReferenceType::Documentation,
                    'issues' => Enums\ExternalReferenceType::IssueTracker,
                    'source' => Enums\ExternalReferenceType::VCS,
                    default => Enums\ExternalReferenceType::Other,
                };
                yield (new Models\ExternalReference(
                    $extRefType,
                    $supportUrl
                ))->setComment("as detected from Composer manifest 'support.$supportType'");
            }
        }
    }

    /**
     * @throws Throwable when the repo could not be fetched
     */
    private static function getPackageRepo(Composer $composer, bool $withDevReqs): InstalledRepositoryInterface|LockArrayRepository
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
    public static function createToolsFromComposer(
        Composer $composer,
        ?string $versionOverride, bool $excludeLibs, bool $excludeComposer
    ): Generator {
        if (!$excludeComposer) {
            yield (new Models\Tool())
                ->setName('composer')
                ->setVersion($versionOverride ?? $composer::getVersion()) // use the self-proclaimed `version`
                // omit `vendor` and `externalReferences`, because we cannot be sure about the used tool's actual origin
                // omit `hashes`, because unfortunately there is no agreed process of generating them
            ;
        }

        $packageNames = [
            'cyclonedx/cyclonedx-php-composer',
        ];
        if (!$excludeLibs) {
            $packageNames[] = 'cyclonedx/cyclonedx-library';
        }

        try {
            $packagesRepo = self::getPackageRepo($composer, true);
        } catch (Throwable) {
            $packagesRepo = new LockArrayRepository();
        }

        foreach ($packageNames as $packageName) {
            try {
                yield self::createToolFromPackage(
                    $packagesRepo->findPackage($packageName, new MatchAllConstraint())
                    ?? throw new ValueError("package not found: $packageName"),
                    $versionOverride
                );
            } catch (Throwable) {
                [$group, $name] = self::getGroupAndName($packageName);
                yield (new Models\Tool())
                    ->setName($name)
                    ->setVendor($group)
                    ->setVersion(
                        $versionOverride
                        ?? (trim(
                            // try sibling of (global) installation
                            // !! dont refer to the `vendor` dir, it is a configurable and might be called differently !!
                            @file_get_contents(\dirname(__DIR__, 4)."/$group/$name/semver.txt")
                            ?: // fallback: empty string
                            ''
                        ) ?: null)
                    );
            }
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function createToolFromPackage(PackageInterface $package, string $versionOverride = null): Models\Tool
    {
        [$group, $name] = self::getGroupAndName($package->getName());

        $tool = new Models\Tool();
        $tool->setName($name);
        $tool->setVendor($group);
        $tool->setVersion($versionOverride ?? $package->getFullPrettyVersion());
        $tool->getHashes()->set(Enums\HashAlgorithm::SHA_1, $package->getDistSha1Checksum());
        $tool->getExternalReferences()->addItems(
            ...self::createExternalReferencesFromPackage($package)
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
}
