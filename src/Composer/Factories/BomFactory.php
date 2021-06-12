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

namespace CycloneDX\Composer\Factories;

use CycloneDX\Composer\Locker;
use CycloneDX\Models\Bom;
use UnexpectedValueException;

/**
 * @internal
 *
 * @author jkowalleck
 */
class BomFactory
{
    /** @var bool */
    public $excludeDev;

    /** @var bool */
    public $excludePlugins;

    /** @var ComponentFactory */
    public $componentFactory;

    public function __construct(bool $excludeDev, bool $excludePlugins)
    {
        $this->excludeDev = $excludeDev;
        $this->excludePlugins = $excludePlugins;
        $this->componentFactory = new ComponentFactory();
    }

    /**
     * Generates BOMs based on Composer's lockData.
     *
     * @throws UnexpectedValueException if a package does not provide a name or version
     * @throws \DomainException         if the bom structure had unexpected values
     * @throws \RuntimeException        if loading known SPDX licenses failed
     */
    public function makeFromLocker(Locker $locker): Bom
    {
        $components = array_map(
            [$this->componentFactory, 'makeFromPackage'],
            $locker->getLockedRepository(
                $this->excludeDev,
                $this->excludePlugins
            )->getPackages()
        );

        return (new Bom())
            ->addComponent(...$components);
    }
}
