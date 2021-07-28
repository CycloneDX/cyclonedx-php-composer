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

use Composer\Package\RootPackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ToolRepository;

/**
 * @internal
 *
 * @author jkowalleck
 */
class BomFactory
{
    /** @var ComponentFactory */
    private $componentFactory;

    /**
     * @var Tool|null
     */
    private $tool;

    public function __construct(ComponentFactory $componentFactory, ?Tool $tool = null)
    {
        $this->componentFactory = $componentFactory;
        $this->tool = $tool;
    }

    public function getComponentFactory(): ComponentFactory
    {
        return $this->componentFactory;
    }

    public function getTool(): ?Tool
    {
        return $this->tool;
    }

    /**
     * Generates BOMs based on Composer's lockData.
     *
     * @throws \UnexpectedValueException if a package does not provide a name or version
     * @throws \DomainException          if the bom structure had unexpected values
     * @throws \RuntimeException
     */
    public function makeForPackageWithRequires(RootPackageInterface $rootPackage, LockArrayRepository $requires): Bom
    {
        $tools = null === $this->tool ? null : new ToolRepository($this->tool);
        $rootComponent = $this->componentFactory->makeFromPackage($rootPackage);

        $metadata = (new MetaData())
            ->setComponent($rootComponent)
            ->setTools($tools);
        $components = $this->componentFactory->makeFromPackages($requires->getPackages());

        return (new Bom($components))
            ->setMetaData($metadata);
    }
}
