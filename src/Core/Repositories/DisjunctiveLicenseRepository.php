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

namespace CycloneDX\Core\Repositories;

use CycloneDX\Core\Models\License\AbstractDisjunctiveLicense;

/**
 * @author jkowalleck
 */
class DisjunctiveLicenseRepository implements \Countable
{
    /**
     * @var AbstractDisjunctiveLicense[]
     * @psalm-var list<AbstractDisjunctiveLicense>
     */
    private $licenses = [];

    public function __construct(AbstractDisjunctiveLicense ...$licenses)
    {
        $this->addLicense(...$licenses);
    }

    /**
     * @return $this
     */
    public function addLicense(AbstractDisjunctiveLicense ...$licenses): self
    {
        array_push($this->licenses, ...array_values($licenses));

        return $this;
    }

    /**
     * @return AbstractDisjunctiveLicense[]
     * @psalm-return list<AbstractDisjunctiveLicense>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

    public function count(): int
    {
        return \count($this->licenses);
    }
}
