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
 * Class Bom.
 *
 * @author nscuro
 */
class Bom
{
    /**
     * Components.
     *
     * @var Component[]
     */
    private $components;

    /**
     * Version.
     *
     * The version allows component publishers/authors to make changes to existing BOMs to update various aspects of the document such as description or licenses.
     * When a system is presented with multiple BOMs for the same component, the system should use the most recent version of the BOM.
     * The default version is '1' and should be incremented for each version of the BOM that is published.
     * Each version of a component should have a unique BOM and if no changes are made to the BOMs, then each BOM will have a version of '1'.
     *
     * @var int
     */
    private $version = 1;

    /**
     * @return Component[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param Component[] $components
     *
     * @return $this
     */
    public function setComponents(array $components): self
    {
        $this->components = $components;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return $this
     */
    public function setVersion(int $version): self
    {
        if ($version <= 0) {
            throw new UnexpectedValueException("Invalid value: {$version}");
        }
        $this->version = $version;

        return $this;
    }

    /**
     * @param Component[] $components
     *
     * @uses \CycloneDX\Models\Bom::setComponents()
     */
    public function __construct(array $components = [])
    {
        $this->setComponents($components);
    }
}
