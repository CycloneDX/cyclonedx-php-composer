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

namespace CycloneDX\Factories;

use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Spdx\License as SpdxLicenseValidator;

class LicenseFactory
{
    /** @var SpdxLicenseValidator */
    private $spdxLicenseValidator;

    public function __construct(SpdxLicenseValidator $spdxLicenseValidator)
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;
    }

    public function getSpdxLicenseValidator(): SpdxLicenseValidator
    {
        return $this->spdxLicenseValidator;
    }

    public function setSpdxLicenseValidator(SpdxLicenseValidator $spdxLicenseValidator): self
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;

        return $this;
    }

    /**
     * @return DisjunctiveLicense|LicenseExpression
     */
    public function makeFromString(string $license)
    {
        return $this->isExpression($license)
            ? new LicenseExpression($license)
            : DisjunctiveLicense::createFromNameOrId($license, $this->spdxLicenseValidator);
    }

    private function isExpression(string $license): bool
    {
        // smallest known: (A or B)
        return \strlen($license) >= 8
            && '(' === $license[0]
            && ')' === $license[-1];
    }
}
