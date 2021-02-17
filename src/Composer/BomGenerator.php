<?php

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use Generator;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Generates BOMs based on Composer's lockData.
 *
 * @author nscuro
 * @author jkowalleck
 *
 * @internal
 */
class BomGenerator
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param array<string, mixed> $lockData
     *
     * @return array<string, mixed>
     */
    public function getPackagesFromLock(array $lockData, bool $excludeDev): array
    {
        $packages = $lockData['packages'] ?? [];
        $packagesDev = $lockData['packages-dev'] ?? [];

        if ($excludeDev) {
            $this->output->writeln('<warning>Dev dependencies will be skipped</warning>');

            return $packages;
        }

        return array_merge($packages, $packagesDev);
    }

    /**
     * @param array<array<string, mixed>> $packages
     *
     * @return Generator<array<string, mixed>>
     */
    public function filterOutPlugins(array $packages): Generator
    {
        foreach ($packages as $package) {
            if ('composer-plugin' === $package['type']) {
                $this->output->writeln('<warning>Skipping plugin '.OutputFormatter::escape($package['name']).'</warning>');
                continue;
            }
            yield $package;
        }
    }

    /**
     * @param array<string, mixed> $lockData       Composer's lockData to generate a BOM for
     * @param bool                 $excludeDev     Exclude Dev dependencies
     * @param bool                 $excludePlugins Exclude composer plugins
     *
     * @return Bom The resulting BOM
     */
    public function generateBom(array $lockData, bool $excludeDev, bool $excludePlugins): Bom
    {
        $packages = $this->getPackagesFromLock($lockData, $excludeDev);
        if ($excludePlugins) {
            $packages = iterator_to_array($this->filterOutPlugins($packages));
        }
        $components = array_map([$this, 'buildComponent'], $packages);

        return (new Bom())
            ->setComponents($components);
    }

    /**
     * @param array<string, mixed> $package The lockData's package data to build a component from
     *
     * @throws UnexpectedValueException When the given package does not provide a name or version
     *
     * @return Component The resulting component
     */
    public function buildComponent(array $package): Component
    {
        if (false === isset($package['name']) || '' === $package['name']) {
            throw new UnexpectedValueException('Encountered package without name: '.json_encode($package));
        }

        if (false === isset($package['version']) || '' === $package['version']) {
            throw new UnexpectedValueException("Encountered package without version: {$package['name']}");
        }

        [$name, $vendor] = $this->splitNameAndVendor($package['name']);
        $version = $this->normalizeVersion($package['version']);

        $component = (new Component(Classification::LIBRARY, $name, $version))
            ->setGroup($vendor)
            ->setDescription($package['description'] ?? null)
            ->setLicenses(array_map(
                static function (string $license): License { return new License($license); },
                $this->splitLicenses($package['license'] ?? [])
            ));

        if (!empty($package['dist']['shasum'])) {
            $component->setHashes([HashAlgorithm::SHA_1 => $package['dist']['shasum']]);
        }

        return $component;
    }

    /**
     * @param string $packageName package name
     *
     * @return array{string, ?string}
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
     * @param string $packageVersion The version to normalize
     *
     * @return string The normalized version
     *
     * @internal this functionality is pretty clumsy and might be reworked in the future
     */
    private function normalizeVersion(string $packageVersion): string
    {
        if (0 === substr_compare($packageVersion, 'v', 0, 1)) {
            return substr($packageVersion, 1, strlen($packageVersion));
        }

        return $packageVersion;
    }

    /**
     * @see https://getcomposer.org/doc/04-schema.md#license
     * @see https://spdx.dev/specifications/
     *
     * @param string|string[] $licenseData
     *
     * @return string[]
     *
     * @internal this functionality is pretty clumsy and might be reworked in the future
     */
    public function splitLicenses($licenseData): array
    {
        if (is_array($licenseData)) {
            // Disjunctive license provided as array
            return $licenseData;
        }

        if (preg_match('/\((?:[\w.\-]+(?: or | and )?)+\)/', $licenseData)) {
            // Conjunctive or disjunctive license provided as string
            $licenseDataSplit = preg_split('/[()]/', $licenseData, -1, PREG_SPLIT_NO_EMPTY);
            if (false !== $licenseDataSplit) {
                return preg_split('/ or | and /', $licenseDataSplit[0], -1, PREG_SPLIT_NO_EMPTY)
                    ?: [$licenseData];
            }
        }

        // A single license provided as string
        return [$licenseData];
    }
}
