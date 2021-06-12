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

use Composer\Package\Locker as ComposerPackageLocker;
use Composer\Repository\LockArrayRepository;

/**
 * @internal
 *
 * @author jkowalleck
 */
class Locker
{
    private const COMPOSER_PACKAGE_TYPE_PLUGIN = 'composer-plugin';

    /** @var ComposerPackageLocker */
    public $composerPackageLocker;

    public function __construct(ComposerPackageLocker $composerPackageLocker)
    {
        $this->composerPackageLocker = $composerPackageLocker;
    }

    /**
     * @throws \RuntimeException if fetching the Lock failed
     */
    public function getLockedRepository(bool $excludeDev, bool $excludePlugins): LockArrayRepository
    {
        $repo = $this->composerPackageLocker
            ->getLockedRepository(false === $excludeDev);

        if ($excludePlugins) {
            foreach ($repo->getPackages() as $package) {
                if (self::COMPOSER_PACKAGE_TYPE_PLUGIN === $package->getType()) {
                    $repo->removePackage($package);
                }
            }
        }

        return $repo;
    }
}
