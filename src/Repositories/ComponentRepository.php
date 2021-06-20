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

namespace CycloneDX\Repositories;

use CycloneDX\Models\Component;

/**
 * @author jkowalleck
 *
 * @psalm-type Components = list<Component>
 */
class ComponentRepository implements \Countable
{
    /** @psalm-var Components */
    private $component = [];

    /**
     * @no-named-arguments
     *
     * @return $this
     */
    public function addComponent(Component ...$components): self
    {
        array_push($this->component, ...$components);

        return $this;
    }

    /** @psalm-return Components */
    public function getComponents(): array
    {
        return $this->component;
    }

    public function count(): int
    {
        return \count($this->component);
    }
}
