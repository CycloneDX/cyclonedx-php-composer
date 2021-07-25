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

namespace CycloneDX\Composer;

use Composer\Repository\LockArrayRepository;
use Composer\Semver\Constraint\MatchAllConstraint;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Core\Models\Tool;

/**
 * @internal
 *
 * @author jkowalleck
 */
class ToolUpdater
{
    /**
     * @var ComponentFactory
     */
    private $componentFactory;

    public function __construct(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * update tool information with data based on composer lock.
     */
    public function updateTool(Tool $tool, LockArrayRepository $lockRepo): bool
    {
        $toolComposerName = sprintf(
            '%s/%s',
            $tool->getVendor() ?? '',
            $tool->getName() ?? '',
        );
        if ('/' === $toolComposerName) {
            return false;
        }

        $package = $lockRepo->findPackage($toolComposerName, new MatchAllConstraint());
        if (null === $package) {
            return false;
        }

        try {
            $component = $this->componentFactory->makeFromPackage($package);
        } catch (\Throwable $exception) {
            return false;
        }

        $tool->setVersion($component->getVersion());
        $tool->setHashRepository($component->getHashRepository());

        return true;
    }
}
