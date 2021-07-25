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

use Composer\Composer;
use Composer\Factory as ComposerFactory;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Spdx\License as SpdxLicenseValidator;

/**
 * @internal
 *
 * @author jkowalleck
 */
class Plugin implements PluginInterface, Capable, CommandProvider
{
    private const FILE_VERSION = __DIR__.'/../../semver.txt';

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
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
        $componentFactory = new Factories\ComponentFactory(
            new Factories\LicenseFactory(
                new SpdxLicenseValidator()
            )
        );

        return [
            new MakeBom\Command(
                new MakeBom\Options(),
                new MakeBom\Factory(new ComposerFactory()),
                new Factories\BomFactory(
                    $componentFactory,
                    $this->getTool()
                ),
                new ToolUpdater($componentFactory),
                'make-bom'
            ),
        ];
    }

    private function getTool(): Tool
    {
        $version = file_get_contents(self::FILE_VERSION);

        return (new Tool())
            ->setVendor('cyclonedx')
            ->setName('cyclonedx-php-composer')
            ->setVersion(false === $version ? null : trim($version));
    }
}
