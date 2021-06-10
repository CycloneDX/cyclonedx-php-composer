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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Composer\Factories;

use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Component;
use PackageUrl\PackageUrl;
use UnexpectedValueException;

/**
 * @internal
 */
class ComponentFactory
{
    public const PURL_TYPE = 'composer';

    /** @var LicenseFactory */
    public $licenseFactory;

    public function __construct()
    {
        $this->licenseFactory = new LicenseFactory();
    }

    /**
     * @throws UnexpectedValueException if the given package does not provide a name or version
     * @throws \DomainException         if the bom structure had unexpected values
     * @throws \RuntimeException        if loading known SPDX licenses failed
     */
    public function makeFromPackage(PackageInterface $package): Component
    {
        $rawName = $package->getPrettyName();
        if (empty($rawName)) {
            throw new UnexpectedValueException('Encountered package without name:'.\PHP_EOL.print_r($package, true));
        }

        $version = $this->normalizeVersion($package->getPrettyVersion());
        if ('' === $version) {
            throw new UnexpectedValueException("Encountered package without version: ${rawName}");
        }

        [$name, $vendor] = $this->splitNameAndVendor($rawName);

        if ($package instanceof CompletePackageInterface) {
            $description = $package->getDescription();
            $licenses = $this->licenseFactory->makeFromPackage($package);
        } else {
            $description = null;
            $licenses = [];
        }

        // composer has no option to distinguish framework/library/application, yet
        $type = Classification::LIBRARY;

        /**
         * @psalm-suppress DocblockTypeContradiction since return value happened to be `null` for local packages
         * @psalm-suppress RedundantConditionGivenDocblockType since return value happened to be `null` for local packages
         */
        $sha1sum = $package->getDistSha1Checksum() ?? '';

        $component = (new Component($type, $name, $version))
            ->setGroup($vendor)
            ->setDescription($description)
            ->addLicense(...$licenses);

        $purl = (new PackageUrl(self::PURL_TYPE, $component->getName()))
            ->setNamespace($component->getGroup())
            ->setVersion($component->getVersion());
        $component->setPackageUrl($purl);

        if ('' !== $sha1sum) {
            $component->setHash(HashAlgorithm::SHA_1, $sha1sum);
            $purl->setChecksums(["sha1:${sha1sum}"]);
        }

        return $component;
    }

    /**
     * @psalm-param string $packageName package name
     *
     * @psalm-return array{string, ?string}
     */
    private function splitNameAndVendor(string $packageName): array
    {
        // Composer requires published packages to be named like <vendor>/<packageName>.
        // Because this is a loose requirement that doesn't apply to "internal" packages,
        // we need to consider that the vendor name may be omitted.
        // See https://getcomposer.org/doc/04-schema.md#name
        if (false === strpos($packageName, '/')) {
            $name = $packageName;
            $vendor = null;
        } else {
            [$vendor, $name] = explode('/', $packageName, 2);
        }

        return [$name, $vendor];
    }

    /**
     * Versions of Composer packages may be prefixed with "v".
     * This prefix appears to be problematic for CPE and PURL matching and thus is removed here.
     *
     * See for example {@link https://ossindex.sonatype.org/component/pkg:composer/phpmailer/phpmailer@v6.0.7}
     * vs {@link https://ossindex.sonatype.org/component/pkg:composer/phpmailer/phpmailer@6.0.7}.
     *
     * @psalm-param string $packageVersion The version to normalize
     *
     * @psalm-return string The normalized version
     *
     * @internal this functionality is pretty clumsy and might be reworked in the future
     */
    private function normalizeVersion(string $packageVersion): string
    {
        if (0 === substr_compare($packageVersion, 'v', 0, 1)) {
            return substr($packageVersion, 1, \strlen($packageVersion));
        }

        return $packageVersion;
    }
}
