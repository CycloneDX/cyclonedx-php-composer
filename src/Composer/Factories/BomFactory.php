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

use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Core\Models\Bom;

/**
 * @internal
 *
 * @author jkowalleck
 */
class BomFactory
{
    /** @var ComponentFactory */
    private $componentFactory;

    public function __construct(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * Generates BOMs based on Composer's lockData.
     *
     * @throws \UnexpectedValueException if a package does not provide a name or version
     * @throws \DomainException          if the bom structure had unexpected values
     * @throws \RuntimeException
     */
    public function makeForPackageWithComponents(PackageInterface $package, LockArrayRepository $components): Bom
    {
        return new Bom(
            $this->componentFactory->makeFromPackages(
                $components->getPackages()
            )
        );
    }
}
