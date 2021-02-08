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

use UnexpectedValueException;

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
     * @var string|null
     */
    private $id;

    /**
     * Name.
     *
     * If SPDX does not define the license used, this field may be used to provide the license name.
     *
     * @var string|null
     */
    private $name;

    /**
     * Text.
     *
     * Specifies the optional full text of the license.
     *
     * @var string|null
     */
    private $text;

    /**
     * Url.
     *
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

    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return $this
     */
    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @throws UnexpectedValueException
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        if (null !== $url && false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException("Invalid URL: ${url}");
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
     * @param string $nameOrId name or ID of a license
     *
     * @uses \CycloneDX\Spdx\License::validate()
     * @uses \CycloneDX\Spdx\License::getLicense()
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
     * @uses \CycloneDX\Models\License::setNameOrVersion()
     */
    public function __construct(string $nameOrId)
    {
        $this->setNameOrId($nameOrId);
    }
}
