<?php

declare(strict_types=1);

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

namespace CycloneDX\Models;

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use DomainException;
use InvalidArgumentException;

/**
 * Class Component.
 *
 * @author nscuro
 * @author jkowalleck
 */
class Component
{
    /**
     * Name.
     *
     * The name of the component. This will often be a shortened, single name
     * of the component.
     *
     * Examples: commons-lang3 and jquery
     *
     * @psalm-var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $name;

    /**
     * Group.
     *
     * The grouping name or identifier. This will often be a shortened, single
     * name of the company or project that produced the component, or the source package or
     * domain name.
     * Whitespace and special characters should be avoided.
     *
     * Examples include: apache, org.apache.commons, and apache.org.
     *
     * @psalm-var string|null
     */
    private $group;

    /**
     * Type.
     *
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
     * Description.
     *
     * Specifies a description for the component.
     *
     * @psalm-var string|null
     */
    private $description;

    /**
     * Licences.
     *
     * @psalm-var License[]
     */
    private $licenses = [];

    /**
     * Hashes.
     *
     * Specifies the file hashes of the component.
     *
     * @psalm-var array<HashAlgorithm::*, string>
     */
    private $hashes = [];

    /**
     * Version.
     *
     * The component version. The version should ideally comply with semantic versioning
     * but is not enforced.
     *
     * @psalm-var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $version;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @psalm-return $this
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
     * @psalm-return $this
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
     * @psalm-param Classification::*|string $type For a ist of Valid values see {@see Classification}
     *
     * @throws DomainException if value is unknown
     *
     * @psalm-return $this
     *
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setType(string $type): self
    {
        $types = (new \ReflectionClass(Classification::class))->getConstants();
        if (false === in_array($type, $types, true)) {
            throw new DomainException("Invalid type: {$type}");
        }
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @psalm-return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @psalm-return License[]
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

    /**
     * @psalm-param array<License> $licenses
     *
     * @throws InvalidArgumentException if list contains element that is not instance of {@see \CycloneDX\Models\License}
     *
     * @psalm-return $this
     */
    public function setLicenses(array $licenses): self
    {
        foreach ($licenses as $license) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (false === $license instanceof License) {
                throw new InvalidArgumentException('Not a License: '.var_export($license, true));
            }
        }
        $this->licenses = array_values($licenses);

        return $this;
    }

    /**
     * @psalm-return $this
     */
    public function addLicense(License ...$licenses): self
    {
        array_push($this->licenses, ...array_values($licenses));

        return $this;
    }

    /**
     * @psalm-return array<HashAlgorithm::*, string>
     */
    public function getHashes(): array
    {
        return $this->hashes;
    }

    /**
     * @psalm-assert array<HashAlgorithm::*, string> $hashes
     *
     * @throws DomainException          if any of hashes' keys is not in {@see HashAlgorithm}'s constants list
     * @throws InvalidArgumentException if any of hashes' values is not a string
     *
     * @psalm-return $this
     *
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setHashes(array $hashes): self
    {
        $algorithms = (new \ReflectionClass(HashAlgorithm::class))->getConstants();
        foreach ($hashes as $algorithm => $content) {
            if (false === in_array($algorithm, $algorithms, true)) {
                throw new DomainException("Unknown hash algorithm: {$algorithm}");
            }
            if (false === is_string($content)) {
                throw new InvalidArgumentException("Hash content for '{$algorithm}' is not string.");
            }
        }
        $this->hashes = $hashes;

        return $this;
    }

    /**
     * @psalm-param HashAlgorithm::*|string $algorithm
     *
     * @throws DomainException if $algorithm is not in {@see HashAlgorithm}'s constants list
     *
     * @psalm-return $this
     *
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setHash(string $algorithm, string $content): self
    {
        $algorithms = (new \ReflectionClass(HashAlgorithm::class))->getConstants();
        if (false === in_array($algorithm, $algorithms, true)) {
            throw new DomainException("Unknown hash algorithm: {$algorithm}");
        }
        $this->hashes[$algorithm] = $content;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @psalm-return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Package URL.
     *
     * Specifies the package-url (PURL).
     * The purl, if specified, must be valid and conform to the specification
     * defined at: {@linnk https://github.com/package-url/purl-spec}
     */
    public function getPackageUrl(): string
    {
        return 'pkg:composer/'.
            ($this->group ? "{$this->group}/" : '').
            "{$this->name}@{$this->version}"
            ;
    }

    /**
     * @see \CycloneDX\Models\Component::setType()
     * @see \CycloneDX\Models\Component::setName()
     * @see \CycloneDX\Models\Component::setVersion()
     *
     * @psalm-param Classification::*|string $type
     *
     * @throws DomainException if type is unknown
     */
    public function __construct(string $type, string $name, string $version)
    {
        $this->setType($type);
        $this->setName($name);
        $this->setVersion($version);
    }
}
