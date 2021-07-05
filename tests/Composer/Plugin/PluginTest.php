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

namespace CycloneDX\Tests\Composer\Plugin;

use CycloneDX\Composer\Plugin\CommandProvider;
use CycloneDX\Composer\Plugin\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Plugin\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * path to composer.json file.
     */
    private const COMPOSER_JSON_FILE_PATH = __DIR__.'/../../../composer.json';

    /**
     * assert the correct setup as described in
     * {@link https://getcomposer.org/doc/articles/plugins.md#plugin-package the docs}.
     *
     * @coversNothing
     */
    public function testPackageIsComposerPlugin(): void
    {
        $composerJson = $this->getComposerJson();
        self::assertSame('composer-plugin', $composerJson['type']);
    }

    /**
     * assert the correct setup as described in
     * {@link https://getcomposer.org/doc/articles/plugins.md#plugin-package the docs}.
     *
     * @coversNothing
     */
    public function testPluginIsRegistered(): void
    {
        $composerJson = $this->getComposerJson();
        self::assertSame(Plugin::class, $composerJson['extra']['class']);
    }

    private function getComposerJson(): array
    {
        return json_decode(
            file_get_contents(self::COMPOSER_JSON_FILE_PATH),
            true, 512, \JSON_THROW_ON_ERROR);
    }

    public function testCommandProviderClassIsRegistered(): void
    {
        $plugin = new Plugin();

        $capabilities = $plugin->getCapabilities();
        $commandProviderClass = $capabilities[\Composer\Plugin\Capability\CommandProvider::class];

        self::assertSame($commandProviderClass, CommandProvider::class);
    }
}
