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

namespace CycloneDX\Tests;

use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use CycloneDX\Composer\MakeBom\Command;
use CycloneDX\Composer\Plugin;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Composer\Plugin::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Composer\MakeBom\Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Composer\MakeBom\Options::class)]
final class PluginTest extends TestCase
{
    /**
     * path to composer.json file.
     */
    private const COMPOSER_JSON_FILE_PATH = __DIR__.'/../composer.json';

    /**
     * assert the correct setup as described in
     * {@link https://getcomposer.org/doc/articles/plugins.md#plugin-package the docs}.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testPackageIsComposerPlugin(): void
    {
        $composerJson = $this->getComposerJson();
        self::assertSame('composer-plugin', $composerJson['type']);
    }

    /**
     * assert the correct setup as described in
     * {@link https://getcomposer.org/doc/articles/plugins.md#plugin-package the docs}.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testPluginIsRegistered(): string
    {
        $composerJson = $this->getComposerJson();
        $pluginClass = $composerJson['extra']['class'];

        self::assertTrue(class_exists($pluginClass), "class does not exist: $pluginClass");
        self::assertSame(Plugin::class, $pluginClass);

        return $pluginClass;
    }

    private function getComposerJson(): array
    {
        return json_decode(
            file_get_contents(self::COMPOSER_JSON_FILE_PATH),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );
    }

    #[\PHPUnit\Framework\Attributes\Depends('testPluginIsRegistered')]
    public function testPluginImplementsRequiredInterfaces(string $pluginClass): PluginInterface&Capable
    {
        $implements = class_implements($pluginClass);

        self::assertContains(PluginInterface::class, $implements);
        self::assertContains(Capable::class, $implements);

        return new $pluginClass();
    }

    #[\PHPUnit\Framework\Attributes\Depends('testPluginImplementsRequiredInterfaces')]
    public function testPluginIsCapableOfCommand(Capable $plugin): CommandProvider
    {
        $capabilities = $plugin->getCapabilities();

        $commandProviderClass = $capabilities[CommandProvider::class];
        $commandProvider = new $commandProviderClass();

        self::assertInstanceOf(CommandProvider::class, $commandProvider);

        return $commandProvider;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testPluginIsCapableOfCommand')]
    public function testMakeBomCommandIsRegistered(CommandProvider $commandProvider): void
    {
        $commands = $commandProvider->getCommands();

        self::assertContainsOnlyInstancesOf(\Composer\Command\BaseCommand::class, $commands);

        self::assertCount(1, $commands);

        $command = $commands[0];
        self::assertInstanceOf(Command::class, $command);
        self::assertSame('CycloneDX:make-sbom', $command->getName());
    }

    #[\PHPUnit\Framework\Attributes\Depends('testPluginImplementsRequiredInterfaces')]
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testActivatePlugin(PluginInterface $plugin): PluginInterface
    {
        $plugin->activate(
            $this->createMock(\Composer\Composer::class),
            $this->createMock(\Composer\IO\IOInterface::class),
        );

        return $plugin;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testActivatePlugin')]
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testDeactivatePlugin(PluginInterface $plugin): PluginInterface
    {
        $plugin->deactivate(
            $this->createMock(\Composer\Composer::class),
            $this->createMock(\Composer\IO\IOInterface::class),
        );

        return $plugin;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testDeactivatePlugin')]
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testUninstallPlugin(PluginInterface $plugin): PluginInterface
    {
        $plugin->uninstall(
            $this->createMock(\Composer\Composer::class),
            $this->createMock(\Composer\IO\IOInterface::class),
        );

        return $plugin;
    }
}
