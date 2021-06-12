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
 *
 * @author jkowalleck
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

        // #TODO add the package hash if package is inDev()

        return $component;
    }

    /**
     * @psalm-param string $packageName package name
     *
     * @psalm-return array{string, ?string}
     */
    private function splitNameAndVendor(string $packageName): array
    {
        // Composer2 requires published packages to be named like <vendor>/<packageName>.
        // Because this was a loose requirement in composer1 that doesn't apply to "internal" packages,
        // we need to consider that the vendor name may be omitted.
        // See https://getcomposer.org/doc/04-schema.md#name
        // This is still done for backward compatibility reasons.
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
     */
    private function normalizeVersion(string $version): string
    {
        // A _numeric_ version can be prefixed with 'v'.
        // Strip leading 'v' must not be applied if the "version" is actually a branch name,
        // which is totally fine in the composer ecosystem.
        if (1 === preg_match('/^v\\d/', $version)) {
            return substr($version, 1);
        }

        return $version;
    }
}
