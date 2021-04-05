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

use RuntimeException;

/**
 * Work with known SPDX licences.
 *
 * @author jkowalleck
 */
class XmlLicense
{
    public const LICENSES_FILE = 'xml-spdx-licenses.json';

    /**
     * @var string[]
     */
    private $licenses;

    public function __construct()
    {
        $this->loadLicenses();
    }

    /**
     * @return string
     */
    public static function getResourcesFile()
    {
        return __DIR__.'/../../res/'.self::LICENSES_FILE;
    }

    /**
     * @return void
     */
    private function loadLicenses()
    {
        if (null !== $this->licenses) {
            return;
        }

        $file = self::getResourcesFile();
        $json = file_get_contents($file);
        if (false === $json) {
            throw new RuntimeException('Missing license file in '.$file);
        }

        $this->licenses = [];

        $options = 0;

        if (defined('JSON_THROW_ON_ERROR')) {
            $options |= JSON_THROW_ON_ERROR;
        }

        foreach (json_decode($json, false, 2, $options) as $license) {
            $this->licenses[strtolower($license)] = $license;
        }
    }

    /**
     * @param string
     *
     * @return bool
     */
    public function validate($identifier)
    {
        return isset($this->licenses[strtolower($identifier)]);
    }

    /**
     * @param string
     *
     * @return string|null
     */
    public function getLicense($identifier)
    {
        $key = strtolower($identifier);

        return array_key_exists($key, $this->licenses)
            ? $this->licenses[$key]
            : null;
    }
}
