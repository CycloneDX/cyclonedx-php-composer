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

namespace CycloneDX\Models;

use InvalidArgumentException;

/**
 * Work with known SPDX licences.
 *
 * @author jkowalleck
 */
class License
{
    /**
     * ID.
     *
     * A valid SPDX license ID.
     *
     * @see \CycloneDX\Spdx\License::validate()
     *
     * @psalm-var string|null
     */
    private $id;

    /**
     * Name.
     *
     * If SPDX does not define the license used, this field may be used to provide the license name.
     *
     * @psalm-var string|null
     */
    private $name;

    /**
     * Url.
     *
     * The URL to the license file.
     * If specified, a 'license' externalReference should also be specified for completeness.
     *
     * @psalm-var string|null
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
     * @psalm-return $this
     */
    public function setUrl(?string $url): self
    {
        if (null !== $url && false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL: ${url}");
        }
        $this->url = $url;

        return $this;
    }

    /**
     * Set Name or ID.
     *
     * If the value is a known SPDX license:
     * the {@see getId()} returns a string and the {@see getName{}} returns `null`.
     * Else: the {@see getname{}} returns a string and the {@see getId{}} returns `null`.
     *
     * @see \CycloneDX\Spdx\License::validate()
     * @see \CycloneDX\Spdx\License::getLicense()
     *
     * @param string $nameOrId name or ID of a license
     *
     * @throws \RuntimeException if loading known SPDX licenses failed
     *
     * @return $this
     */
    public function setNameOrId(string $nameOrId): self
    {
        $spdx = new \CycloneDX\Spdx\License();
        if ($spdx->validate($nameOrId)) {
            $this->id = $spdx->getLicense($nameOrId);
            $this->name = null;
        } else {
            $this->name = $nameOrId;
            $this->id = null;
        }

        return $this;
    }

    /**
     * License constructor.
     *
     * @see \CycloneDX\Models\License::setNameOrVersion()
     *
     * @throws \RuntimeException if loading known SPDX licenses failed
     */
    public function __construct(string $nameOrId)
    {
        $this->setNameOrId($nameOrId);
    }
}
