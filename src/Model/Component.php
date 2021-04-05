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

namespace CycloneDX\Model;

/**
 * @author nscuro
 */
class Component implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $licenses;

    /**
     * @var string
     */
    private $packageUrl;

    /**
     * @var array
     */
    private $hashes;

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param array $licenses
     */
    public function setLicenses($licenses)
    {
        $this->licenses = $licenses;
    }

    /**
     * @return string
     */
    public function getPackageUrl()
    {
        return $this->packageUrl;
    }

    /**
     * @param string $packageUrl
     */
    public function setPackageUrl($packageUrl)
    {
        $this->packageUrl = $packageUrl;
    }

    /**
     * @return array
     */
    public function getHashes()
    {
        return $this->hashes;
    }

    /**
     * @param array $hashes
     */
    public function setHashes($hashes)
    {
        $this->hashes = $hashes;
    }

    public function jsonSerialize()
    {
        $licenses = [];
        foreach ($this->licenses as $license) {
            $licenses[] = [
                'license' => [
                    'id' => $license,
                ],
            ];
        }

        return [
            'name' => $this->name,
            'group' => $this->group,
            'type' => $this->type,
            'version' => $this->version,
            'description' => $this->description,
            'licenses' => $licenses,
            'purl' => $this->packageUrl,
        ];
    }
}
