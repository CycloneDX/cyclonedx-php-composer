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

namespace CycloneDX\Models;

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Repositories\HashRepository;
use CycloneDX\Repositories\LicenseRepository;
use DomainException;
use InvalidArgumentException;
use PackageUrl\PackageUrl;

/**
 * @author nscuro
 * @author jkowalleck
 */
class Component
{
    /**
     * The name of the component. This will often be a shortened, single name
     * of the component.
     *
     * Examples: commons-lang3 and jquery
     *
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $name;

    /**
     * The grouping name or identifier. This will often be a shortened, single
     * name of the company or project that produced the component, or the source package or
     * domain name.
     * Whitespace and special characters should be avoided.
     *
     * Examples include: apache, org.apache.commons, and apache.org.
     *
     * @var string|null
     */
    private $group;

    /**
     * Specifies the type of component. For software components, classify as application if no more
     * specific appropriate classification is available or cannot be determined for the component.
     * Valid choices are: application, framework, library, operating-system, device, or file.
     *
     * Refer to the {@link https://cyclonedx.org/schema/bom/1.1 bom:classification documentation}
     * for information describing each one.
     *
     * @psalm-var Classification::*
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $type;

    /**
     * Specifies a description for the component.
     *
     * @var string|null
     */
    private $description;

    /**
     * Package-URL (PURL).
     *
     * The purl, if specified, must be valid and conform to the specification
     * defined at: {@linnk https://github.com/package-url/purl-spec/blob/master/README.rst#purl}.
     *
     * @var PackageUrl|null
     */
    private $packageUrl;

    /**
     * List of licences.
     *
     * @var LicenseRepository
     */
    private $licenses;

    /**
     * Specifies the file hashes of the component.
     *
     * @var HashRepository
     */
    private $hashes;

    /**
     * The component version. The version should ideally comply with semantic versioning
     * but is not enforced.
     *
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $version;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @psalm-return Classification::*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @psalm-param Classification::*|string $type For a ist of Valid values see {@see \CycloneDX\Enums\Classification}
     *
     * @throws DomainException if value is unknown
     *
     * @return $this
     *
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setType(string $type): self
    {
        $types = (new \ReflectionClass(Classification::class))->getConstants();
        if (false === \in_array($type, $types, true)) {
            throw new DomainException("Invalid type: $type");
        }
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLicenses(): int
    {
        return $this->licenses;
    }

    /**
     * @return $this
     */
    public function setLicenses(LicenseRepository $licenses): self
    {
        $this->licenses = $licenses;
        return $this;
    }

    public function getHashes(): HashRepository
    {
        return $this->hashes;
    }

    /**
     * @return $this
     */
    public function setHashes(HashRepository $hashes): self
    {
        $this->hashes = $hashes;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getPackageUrl(): ?PackageUrl
    {
        return $this->packageUrl;
    }

    public function setPackageUrl(?PackageUrl $purl): self
    {
        $this->packageUrl = $purl;

        return $this;
    }

    /**
     * @psalm-param Classification::*|string $type
     *
     * @throws DomainException if type is unknown
     */
    public function __construct(string $type, string $name, string $version)
    {
        $this->setType($type);
        $this->setName($name);
        $this->setVersion($version);
        $this->licenses = new LicenseRepository();
    }
}
