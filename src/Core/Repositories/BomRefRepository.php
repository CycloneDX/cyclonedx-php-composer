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

use CycloneDX\Core\Models\BomRef;

/**
 * Unique list of {@see \CycloneDX\Core\Models\BomRef}.
 *
 * @author jkowalleck
 */
class BomRefRepository implements \Countable
{
    /**
     * @var BomRef[]
     * @psalm-var list<BomRef>
     */
    private $bomRefs = [];

    public function __construct(BomRef ...$bomRefs)
    {
        $this->addBomRef(...$bomRefs);
    }

    /**
     * @return $this
     */
    public function addBomRef(BomRef ...$bomRefs): self
    {
        foreach ($bomRefs as $bomRef) {
            if (\in_array($bomRef, $this->bomRefs, true)) {
                continue;
            }
            $this->bomRefs[] = $bomRef;
        }

        return $this;
    }

    /**
     * @return BomRef[]
     * @psalm-return list<BomRef>
     */
    public function getBomRefs(): array
    {
        return $this->bomRefs;
    }

    public function count(): int
    {
        return \count($this->bomRefs);
    }
}
