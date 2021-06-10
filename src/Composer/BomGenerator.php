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

namespace CycloneDX\Composer;

use Composer\Package\CompletePackage;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use PackageUrl\PackageUrl;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Generates BOMs based on Composer's lockData.
 *
 * @author jkowalleck
 *
 * @internal
 */
class BomGenerator
{
    public const PURL_TYPE = 'composer';

    /**
     * @var LockArrayRepository
     * @readonly
     */
    private $lockArrayRepository;

    public function __construct(LockArrayRepository $lockArrayRepository)
    {
        $this->lockArrayRepository = $lockArrayRepository;
    }

    /**
     * @psalm-param bool $excludeDev Exclude Dev dependencies
     * @psalm-param bool $excludePlugins Exclude composer plugins
     *
     * @throws UnexpectedValueException if a package does not provide a name or version
     * @throws \DomainException         if the bom structure had unexpected values
     *
     * @psalm-return Bom The resulting BOM
     */
    public function generateBom(bool $excludeDev, bool $excludePlugins): Bom
    {
        $packages = $this->lockArrayRepository->getPackages() ?? [];
        if ($excludeDev) {
            $packages = array_filter($packages, [$this, 'packageIsNotDev']);
        }
        if ($excludePlugins) {
            $packages = array_filter($packages, [$this, 'packageIsNotPlugin']);
        }
        $components = array_map([$this, 'buildComponent'], $packages);

        return (new Bom())
            ->addComponent(...$components);
    }

    /**
     * @throws UnexpectedValueException if the given package does not provide a name or version
     * @throws \DomainException         if the bom structure had unexpected values
     *
     * @psalm-return Component The resulting component
     */
    public function buildComponent(PackageInterface $package): Component
    {
        $rawName = $package->getPrettyName();
        if (empty($rawName)) {
            throw new UnexpectedValueException('Encountered package without name:'. PHP_EOL. print_r($package, true));
        }

        $version = $this->normalizeVersion($package->getPrettyVersion());
        if ('' === $version) {
            throw new UnexpectedValueException("Encountered package without version: ${rawName}");
        }

        [$name, $vendor] = $this->splitNameAndVendor($rawName);

        if ($package instanceof CompletePackageInterface) {
            $description = $package->getDescription();
            $licenses = $this->createLicenses($package->getLicense());
        } else {
            $description = null;
            $licenses = [];
        }

        // composer has no option to distinguish framework/library/application, yet
        $type = Classification::LIBRARY;

        $component = (new Component($type, $name, $version))
            ->setGroup($vendor)
            ->setDescription($description)
            ->addLicense(...$licenses);

        $purl = (new PackageUrl(self::PURL_TYPE, $component->getName()))
            ->setNamespace($component->getGroup())
            ->setVersion($component->getVersion());
        $component->setPackageUrl($purl);

        $sha1sum = $package->getDistSha1Checksum() ?? ''; // happened to be `null` for local packages
        if ('' !== $sha1sum) {
            $component->setHash(HashAlgorithm::SHA_1, $sha1sum);
            $purl->setChecksums(["sha1:${sha1sum}"]);
        }

        return $component;
    }

    private function packageIsNotDev(PackageInterface $package): bool
    {
        // @FIXME this determiens if a dev-version is installed or not ...
        // so it is a wrong implementation
        return false === $package->isDev();
    }

    private function packageIsNotPlugin(PackageInterface $package): bool
    {
        return 'composer-plugin' !== $package->getType();
    }

    /**
     * @psalm-param string $packageName package name
     *
     * @psalm-return array{string, ?string}
     */
    protected function splitNameAndVendor(string $packageName): array
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
    protected function normalizeVersion(string $packageVersion): string
    {
        if (0 === substr_compare($packageVersion, 'v', 0, 1)) {
            return substr($packageVersion, 1, \strlen($packageVersion));
        }

        return $packageVersion;
    }

    /**
     * @psalm-param array<string> $rawLicenses
     * @psalm-return list<License>
     */
    protected function createLicenses(array $rawLicenses): array
    {
        $splitLicenses = [];
        foreach ($rawLicenses as $rawLicense) {
            $splitLicenses[] = $this->splitLicenses($rawLicense);
        }

        return array_map(
            [$this, 'createLicense'],
            array_unique(array_merge(...$splitLicenses))
        );
    }

    /**
     * @psalm-pure
     */
    private function createLicense(string $nameOdId): License
    {
        return new License($nameOdId);
    }


    /**
     * @see https://getcomposer.org/doc/04-schema.md#license
     * @see https://spdx.dev/specifications/
     *
     * @psalm-param string|string[] $licenseData
     *
     * @psalm-return list<string>
     *
     * @internal this functionality is pretty clumsy and might be reworked in the future
     */
    public function splitLicenses($licenseData): array
    {
        if (\is_array($licenseData)) {
            // Disjunctive license provided as array
            return $licenseData;
        }

        if (preg_match('/\((?:[\w.\-]+(?: or | and )?)+\)/', $licenseData)) {
            // Conjunctive or disjunctive license provided as string
            $licenseDataSplit = preg_split('/[()]/', $licenseData, -1, \PREG_SPLIT_NO_EMPTY);
            if (false !== $licenseDataSplit) {
                return preg_split('/ or | and /', $licenseDataSplit[0], -1, \PREG_SPLIT_NO_EMPTY)
                    ?: [$licenseData];
            }
        }

        // A single license provided as string
        return [$licenseData];
    }

}
