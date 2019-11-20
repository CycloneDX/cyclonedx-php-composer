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
class Component 
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

    /**
     * @var bool
     */
    private $modified;

    public function getName() {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup(string $group) {
        $this->group = $group;
    }

    public function getType() {
        return $this->type;
    }

    public function setType(string $type) {
        $this->type = $type;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion(string $version) {
        $this->version = $version;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription(string $description) {
        $this->description = $description;
    }

    public function getLicenses() {
        return $this->licenses;
    }

    public function setLicenses(array $licenses) {
        $this->licenses = $licenses;
    }

    public function getPackageUrl() {
        return $this->packageUrl;
    }

    public function setPackageUrl(string $packageUrl) {
        $this->packageUrl = $packageUrl;
    }

    public function getHashes() {
        return $this->hashes;
    }

    public function setHashes(array $hashes) {
        $this->hashes = $hashes;
    }

    public function isModified() {
        return $this->modified;
    }

    public function setModified(bool $modified) {
        $this->modified = $modified;
    }

}