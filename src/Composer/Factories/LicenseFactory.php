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
use CycloneDX\Models\License;

class LicenseFactory
{
    /**
     * @psalm-return list<License>
     *
     * @throws \RuntimeException if loading known SPDX licenses failed
     */
    public function makeFromPackage(CompletePackageInterface $package): array
    {
        $splitLicenses = array_map(
            [$this, 'splitLicenses'],
            $package->getLicense()
        );

        return array_values(
            array_map(
                [$this, 'makeFromString'],
                array_unique(array_merge(...$splitLicenses))
        ));
    }

    /**
     * @throws \RuntimeException if loading known SPDX licenses failed
     */
    private function makeFromString(string $nameOdId): License
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
    private function splitLicenses($licenseData): array
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
