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

namespace CycloneDX\Spdx;

use JsonException;
use RuntimeException;

/**
 * Work with known SPDX licences.
 *
 * @author jkowalleck
 */
class License
{
    private const LICENSES_FILE = 'spdx-licenses.json';

    /**
     * @psalm-var array<string, string>
     */
    private $licenses;

    /**
     * @psalm-return array<string, string>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

    public static function getResourcesFile(): string
    {
        return __DIR__.'/../../res/'.self::LICENSES_FILE;
    }

    /**
     * XmlLicense constructor.
     */
    public function __construct()
    {
        $this->loadLicenses();
    }

    public function validate(string $identifier): bool
    {
        return isset($this->licenses[strtolower($identifier)]);
    }

    public function getLicense(string $identifier): ?string
    {
        return $this->licenses[strtolower($identifier)] ?? null;
    }

    /**
     * @throws RuntimeException
     */
    private function loadLicenses(): void
    {
        if (null !== $this->licenses) {
            return;
        }

        $file = self::getResourcesFile();
        $json = file_get_contents($file);
        if (false === $json) {
            throw new RuntimeException("Missing licenses file: ${file}");
        }

        try {
            $licenses = json_decode($json, false, 2, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new RuntimeException("Malformed licenses file ${file}", 0, $ex);
        }

        $this->licenses = [];
        foreach ($licenses as $license) {
            $this->licenses[strtolower($license)] = $license;
        }
    }
}
