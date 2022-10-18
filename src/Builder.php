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
    public function __construct()
    {
        $this->licenseFactory = new LicenseFactory(new SpdxLicenseValidator());
    }

    public function createBomFromComposer(Composer $composer): Models\Bom
    {
        $bom = new Models\Bom();

        $bom->getMetadata()->setComponent(
            $this->createComponentFromRootPackage($composer->getPackage())
        );

        $withDev = true; // TODO
        try {
            $dependencies = $composer->getLocker()->getLockedRepository($withDev)->getCanonicalPackages();
        } catch (\Throwable) {
            $dependencies = [];
        }

        foreach ($dependencies as $package) {
            $component = $this->createComponentFromPackage($package);
            $bom->getComponents()->addItems($component);
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
        [$group, $name] = explode($package->getName(), '/', 2);
        if ('' === $name) {
            [$name, $group] = [$group, null];
        }
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
