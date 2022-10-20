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
    // TODO register in https://github.com/CycloneDX/cyclonedx-property-taxonomy
    private const PropertyName_PackageType = 'cdx:composer:package:type';

    private LicenseFactory $licenseFactory;

    /**
     * @throws RuntimeException if loading licenses failed
     */
    public function __construct()
    {
        $this->licenseFactory = new LicenseFactory(new SpdxLicenseValidator());
    }

    public function createBomFromComposer(Composer $composer): Models\Bom
    {
        $bom = new Models\Bom();

        $rootPackage = $composer->getPackage();
        $rootComponent = $this->createComponentFromRootPackage($rootPackage);
        $bom->getMetadata()->setComponent($rootComponent);

        $withDev = true; // TODO
        try {
            $packagesRepo = $composer->getLocker()->getLockedRepository($withDev);
        } catch (\Throwable) {
            $packagesRepo = null;
        }

        if (null !== $packagesRepo) {
            $packages = $packagesRepo->getCanonicalPackages();
            /** @psalm-var array<string, Models\Component> */
            $components = [$rootPackage->getUniqueName() => $rootComponent];

            foreach ($packages as $package) {
                $component = $this->createComponentFromPackage($package);
                $bom->getComponents()->addItems($component);
                $components[$package->getUniqueName()] = $component;
                unset($component, $package);
            }
            /** @var PackageInterface $package */
            foreach ([$rootPackage, ...$packages] as $package) {
                $component = $components[$package->getUniqueName()] ?? null;
                \assert(null !== $component);
                foreach ($package->getRequires() as $requires) {
                    $requiredPackage = $packagesRepo->findPackage($requires->getTarget(), $requires->getConstraint());
                    if (null === $requiredPackage) {
                        continue;
                    }
                    $dependency = $components[$requiredPackage->getUniqueName()] ?? null;
                    if (null !== $dependency) {
                        $component->getDependencies()->addItems($dependency->getBomRef());
                    }
                }
                unset($package, $component, $requires, $dependency);
            }
            if ($withDev) {
                foreach ($rootPackage->getDevRequires() as $requires) {
                    $requiredPackage = $packagesRepo->findPackage($requires->getTarget(), $requires->getConstraint());
                    if (null === $requiredPackage) {
                        continue;
                    }
                    $dependency = $components[$requiredPackage->getUniqueName()] ?? null;
                    if (null !== $dependency) {
                        $rootComponent->getDependencies()->addItems($dependency->getBomRef());
                    }
                }
                unset($requires,$requiredPackage, $dependency);
            }
        }

        return $bom;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromRootPackage(RootPackageInterface $package): Models\Component
    {
        $component = $this->createComponentFromPackage($package);

        return $component
            ->setType(Enums\Classification::APPLICATION)
            ->setPackageUrl(
                $this->createPurlFromComponent($component)
            );
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    private function createComponentFromPackage(PackageInterface $package): Models\Component
    {
        $groupAndName = explode('/', $package->getName(), 2);
        [$group, $name] = 2 === \count($groupAndName)
            ? $groupAndName
            : [null, reset($groupAndName)];

        $distUrl = $package->getDistUrl();
        $sourceUrl = $package->getSourceUrl();

        $component = new Models\Component(Enums\Classification::LIBRARY, $name);
        $component->setBomRefValue($package->getUniqueName());
        // TODO author(s)
        $component->setGroup($group);
        $component->setVersion($package->getVersion());
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
            new Models\Property(self::PropertyName_PackageType, $package->getType())
            // TODO test whether a component was a devDependency or not
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

        $sha1Sum = $component->getHashes()->get(Enums\HashAlgorithm::SHA_1);
        if (null !== $sha1Sum) {
            $purl->setChecksums(["sha1:$sha1Sum"]);
        }

        return $purl;
    }

    /**
     * @return Generator<Models\ExternalReference>
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
