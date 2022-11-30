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

namespace CycloneDX\Composer;

use Composer\Composer;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use CycloneDX\Core\Enums;
use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models;
use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use Generator;
use PackageUrl\PackageUrl;
use RuntimeException;

/**
 * @internal
 *
 * @author jkowalleck
 */
class Builder
{
    private LicenseFactory $licenseFactory;

    /**
     * @throws RuntimeException if loading licenses failed
     */
    public function __construct(
        private bool $omitDev,
        private bool $omitPlugin,
        private ?string $mainComponentVersion
    ) {
        $this->licenseFactory = new LicenseFactory(new SpdxLicenseValidator());
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function createBomFromComposer(Composer $composer): Models\Bom
    {
        $bom = new Models\Bom();

        $rootPackage = $composer->getPackage();
        $rootComponent = $this->createComponentFromRootPackage($rootPackage);
        $bom->getMetadata()->setComponent($rootComponent);
        $composerLocker = $composer->getLocker();

        $withDevReqs = false === $this->omitDev && isset($composerLocker->getLockData()['packages-dev']);
        $packagesRepo = $composerLocker->getLockedRepository($withDevReqs);

        /**
         * @psalm-var list<\Composer\Package\PackageInterface> $packages
         *
         * @psalm-suppress MixedArgument
         */
        $packages = array_values(
            method_exists($packagesRepo, 'getCanonicalPackages')
            // since composer 2.4
            ? $packagesRepo->getCanonicalPackages()
            : $packagesRepo->getPackages()
        );
        /** @psalm-var array<string, Models\Component> */
        $components = [$rootPackage->getUniqueName() => $rootComponent];

        foreach ($packages as $package) {
            $component = $this->createComponentFromPackage($package);
            $bom->getComponents()->addItems($component);
            $components[$package->getUniqueName()] = $component;
            unset($component, $package);
        }
        /**
         * @var PackageInterface $package
         *
         * @psalm-suppress UnnecessaryVarAnnotation -- as it is needed for some IDE
         */
        foreach ([$rootPackage, ...$packages] as $package) {
            $component = $components[$package->getUniqueName()] ?? null;
            \assert(null !== $component);
            foreach ($package->getRequires() as $required) {
                $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
                if (null === $requiredPackage) {
                    continue;
                }
                $dependency = $components[$requiredPackage->getUniqueName()] ?? null;
                if (null !== $dependency) {
                    $component->getDependencies()->addItems($dependency->getBomRef());
                }
            }
            unset($package, $component, $required, $dependency);
        }
        if ($withDevReqs) {
            foreach ($rootPackage->getDevRequires() as $required) {
                $requiredPackage = $packagesRepo->findPackage($required->getTarget(), $required->getConstraint());
                if (null === $requiredPackage) {
                    continue;
                }
                $dependency = $components[$requiredPackage->getUniqueName()] ?? null;
                if (null !== $dependency) {
                    $dependency->getProperties()->addItems(
                        new Models\Property(Properties::Name_DevRequirement, Properties::Value_True));
                    $rootComponent->getDependencies()->addItems($dependency->getBomRef());
                }
            }
            unset($required,$requiredPackage, $dependency);
        }

        return $bom;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromRootPackage(RootPackageInterface $package): Models\Component
    {
        $component = $this->createComponentFromPackage($package, $this->mainComponentVersion);

        return $component
            ->setType(Enums\ComponentType::APPLICATION)
            ->setPackageUrl($this->createPurlFromComponent($component));
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromPackage(PackageInterface $package, ?string $versionOverride=null): Models\Component
    {
        $groupAndName = explode('/', $package->getName(), 2);
        [$group, $name] = 2 === \count($groupAndName)
            ? $groupAndName
            : [null, $groupAndName[0]];
        /** @psalm-suppress RedundantCondition */
        \assert(null === $group || \is_string($group));
        \assert(\is_string($name));

        $distUrl = $package->getDistUrl();
        $sourceUrl = $package->getSourceUrl();
        $version = $versionOverride ?? $package->getFullPrettyVersion();

        $component = new Models\Component(Enums\ComponentType::LIBRARY, $name);
        $component->setBomRefValue($package->getUniqueName());
        // TODO author(s)
        $component->setGroup($group);
        $component->setVersion($version);
        if ($distUrl) {
            $component->getExternalReferences()->addItems(
                new Models\ExternalReference(
                    Enums\ExternalReferenceType::DISTRIBUTION,
                    $distUrl
                )
            );
            $component->getHashes()->set(Enums\HashAlgorithm::SHA_1, $package->getDistSha1Checksum());
        }
        if ($sourceUrl) {
            $component->getExternalReferences()->addItems(
                new Models\ExternalReference(
                    Enums\ExternalReferenceType::DISTRIBUTION,
                    $sourceUrl
                )
            );
        }

        if ($package instanceof CompletePackageInterface) {
            $component->setDescription($package->getDescription());
            $component->getLicenses()->addItems(
                ...array_map(
                    [$this->licenseFactory, 'makeFromString'],
                    $package->getLicense()
                )
            );
            $component->getExternalReferences()->addItems(
                ...iterator_to_array($this->createExternalReferencesFromPackage($package))
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
     * @return Generator<Models\ExternalReference>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createExternalReferencesFromPackage(CompletePackageInterface $package): Generator
    {
        $homepage = $package->getHomepage();
        if (null !== $homepage) {
            yield (new Models\ExternalReference(
                Enums\ExternalReferenceType::WEBSITE,
                $homepage
            ))->setComment('as detected from composer manifest "homepage"');
        }

        foreach ($package->getSupport() as $supportType => $supportUrl) {
            $extRefType = match ($supportType) {
                'chat' => Enums\ExternalReferenceType::CHAT,
                'docs' => Enums\ExternalReferenceType::DOCUMENTATION,
                'irc' => Enums\ExternalReferenceType::CHAT,
                'issues' => Enums\ExternalReferenceType::ISSUE_TRACKER,
                'source' => Enums\ExternalReferenceType::VCS,
                default => Enums\ExternalReferenceType::OTHER,
            };
            yield (new Models\ExternalReference(
                $extRefType,
                $supportUrl
            ))->setComment("as detected from composer manifest 'support.$supportType'");
        }
    }
}
