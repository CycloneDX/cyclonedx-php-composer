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
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

/**
 * @internal
 *
 * @author jkowalleck
 *
 * @psalm-suppress UnusedClass
 */
class Plugin implements PluginInterface, Capable, CommandProvider
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        /* nothing to do, but required by the `PluginInterface`  */
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        /* nothing to do, but required by the `PluginInterface`  */
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        /* nothing to do, but required by the `PluginInterface`  */
    }

    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => self::class,
        ];
    }

    /**
     * @psalm-suppress MissingThrowsDocblock - Exceptions are handled by caller
     */
    public function getCommands(): array
    {
        return [
            new MakeBom\Command(
                'CycloneDX:make-sbom',
                new MakeBom\Options(),
                new Factory()
            ),
        ];
    }
}
