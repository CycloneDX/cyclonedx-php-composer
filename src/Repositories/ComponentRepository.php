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
 */
class ComponentRepository implements \Countable
{
    /**
     * @var Component[]
     * @psalm-var list<Component>
     */
    private $components = [];

    /**
     * @no-named-arguments
     */
    public function __construct(Component ...$components)
    {
        $this->addComponent(...$components);
    }

    /**
     * @no-named-arguments
     *
     * @return $this
     */
    public function addComponent(Component ...$components): self
    {
        array_push($this->components, ...$components);

        return $this;
    }

    /**
     * @return Component[]
     * @psalm-return list<Component>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function getComponent(string $name, ?string $group): ?Component
    {
        foreach ($this->components as $component) {
            if ($component->getName() === $name && $component->getGroup() === $group) {
                return $component;
            }
        }

        return null;
    }

    public function count(): int
    {
        return \count($this->components);
    }
}
