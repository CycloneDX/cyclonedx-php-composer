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

namespace CycloneDX\Core\Models\License;

use InvalidArgumentException;

/**
 * @author jkowalleck
 */
abstract class AbstractDisjunctiveLicense
{
    /**
     * The URL to the license file.
     * If specified, a 'license' externalReference should also be specified for completeness.
     *
     * @var string|null
     */
    private $url;

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
}
