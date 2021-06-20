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

namespace CycloneDX\Models\License;

use InvalidArgumentException;

/**
 * @author jkowalleck
 */
class DisjunctiveLicense
{
    /**
     * A valid SPDX license ID.
     *
     * @see \CycloneDX\Spdx\License::validate()
     *
     * @var string|null
     */
    private $id;

    /**
     * If SPDX does not define the license used, this field may be used to provide the license name.
     *
     * @var string|null
     */
    private $name;

    /**
     * The URL to the license file.
     * If specified, a 'license' externalReference should also be specified for completeness.
     *
     * @var string|null
     */
    private $url;

    /**
     * Set via {@see setNameOrId()].
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set via {@see setNameOrId()].
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @throws InvalidArgumentException if value is an invalid URL
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        if (null !== $url && false === filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL: $url");
        }
        $this->url = $url;

        return $this;
    }

    /**
     * create an instance with either Name or ID.
     *
     * If the value is a known SPDX license:
     * the {@see getId()} returns a string and the {@see getName{}} returns `null`.
     * Else: the {@see getname{}} returns a string and the {@see getId{}} returns `null`.
     *
     * @see \CycloneDX\Spdx\License::validate()
     * @see \CycloneDX\Spdx\License::getLicense()
     *
     * @param string $nameOrId name or SPDX-ID of a license
     */
    public static function createFromNameOrId(string $nameOrId, \CycloneDX\Spdx\License $spdxLicenseValidator): self
    {
        $id = $spdxLicenseValidator->getLicense($nameOrId);
        $name = null === $id ? $nameOrId : null;

        /**
         * The instance is not instantly returned but stored, to trick the php-cs-fixer in not removing this doc-block.
         * This way the constructor's throw can be psalm-ignored.
         *
         * @psalm-suppress MissingThrowsDocblock since it is asserted to not be thrown
         */
        $instance = new self($id, $name);

        return $instance;
    }

    /**
     * Private! Use {@see createFromNameOrId()} to create an object.
     *
     * @throws InvalidArgumentException if not exactly one argument must be null: $id or $name
     */
    private function __construct(?string $id, ?string $name)
    {
        if (false === (null === $id xor null === $name)) {
            throw new InvalidArgumentException('Exactly one argument must be null: $id or $name');
        }
        $this->id = $id;
        $this->name = $name;
    }
}
