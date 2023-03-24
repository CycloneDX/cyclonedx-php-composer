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

namespace CycloneDX\Tests\MakeBom;

use Composer\Factory as ComposerFactory;
use Composer\IO\NullIO;
use CycloneDX\Composer\MakeBom\Builder;
use CycloneDX\Composer\Plugin;
use CycloneDX\Core\Models;
use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Builder::class)]
#[UsesClass(Plugin::class)]
final class BuilderTest extends TestCase
{
    // region createSbomFromComposer

    #[DataProvider('dpCreateSbomFromComposer')]
    public function testCreateSbomFromComposer(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));
        $builder = new Builder(true, false, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertFalse($sbom, '@TODO');
    }

    #[DataProvider('dpCreateSbomFromComposer')]
    public function testCreateSbomFromComposerOmittingDev(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));
        $builder = new Builder(true, false, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertFalse($sbom, '@TODO');
    }

    #[DataProvider('dpCreateSbomFromComposer')]
    public function testCreateSbomFromComposerOmittingPlugins(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));
        $builder = new Builder(true, false, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertFalse($sbom, '@TODO');
    }

    #[DataProvider('dpCreateSbomFromComposer')]
    public function testCreateSbomFromComposerMCVersionOverride(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));
        $builder = new Builder(true, false, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertFalse($sbom, '@TODO');
    }

    /**
     * @psalm-return \Generator<string, array{0:callable():string, 1:bool, 2:bool}>
     */
    public static function dpCreateSbomFromComposer(): Generator
    {
        yield from self::dpForSetup('testCreateSbomFromComposer');
    }

    // endregion createSbomFromComposer

    // region createToolsFromComposer

    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposer(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));

        $tools = [...Builder::createToolsFromComposer($composer, null, false)];

        $fTools = array_filter(
            $tools,
            static fn (Models\Tool $t): bool => 'cyclonedx' === $t->getVendor() && 'cyclonedx-php-composer' === $t->getName()
        );
        self::assertCount(1, $fTools, 'missing self');
        /** @var Models\Tool $fTool */
        $fTool = reset($fTools);
        if ($installed || $locked) {
            self::assertMatchesRegularExpression('/^v?4\./', $fTool->getVersion());
        } else {
            self::assertNull($fTool->getVersion());
        }

        $fTools = array_filter(
            $tools,
            static fn (Models\Tool $t): bool => 'cyclonedx' === $t->getVendor() && 'cyclonedx-library' === $t->getName()
        );
        self::assertCount(1, $fTools, 'missing library');
        /** @var Models\Tool $fTool */
        $fTool = reset($fTools);
        if ($installed || $locked) {
            self::assertMatchesRegularExpression('/^v?2\./', $fTool->getVersion());
        } else {
            self::assertNull($fTool->getVersion());
        }
    }

    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposerVersionOverride(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));

        $versionOverride = uniqid('v1.0-fake', true);
        $tools = [...Builder::createToolsFromComposer($composer, $versionOverride, false)];

        $fTools = array_filter(
            $tools,
            static fn (Models\Tool $t): bool => 'cyclonedx' === $t->getVendor() && 'cyclonedx-php-composer' === $t->getName()
        );
        self::assertCount(1, $fTools, 'missing self');
        /** @var Models\Tool $fTool */
        $fTool = reset($fTools);
        self::assertSame($versionOverride, $fTool->getVersion());

        $fTools = array_filter(
            $tools,
            static fn (Models\Tool $t): bool => 'cyclonedx' === $t->getVendor() && 'cyclonedx-library' === $t->getName()
        );
        self::assertCount(1, $fTools, 'missing library');
        /** @var Models\Tool $fTool */
        $fTool = reset($fTools);
        self::assertSame($versionOverride, $fTool->getVersion());
    }

    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposerExcludeLibs(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));

        $tools = [...Builder::createToolsFromComposer($composer, null, true)];

        $fTools = array_filter(
            $tools,
            static fn (Models\Tool $t): bool => 'cyclonedx' === $t->getVendor() && 'cyclonedx-php-composer' === $t->getName()
        );
        self::assertCount(1, $fTools, 'missing self');

        self::assertCount(1, $tools, 'unexpected other elements');
    }

    /**
     * @psalm-return \Generator<string, array{0:callable():string, 1:bool, 2:bool}>
     */
    public static function dpCreateToolsFromComposer(): Generator
    {
        yield from self::dpForSetup('testCreateToolsFromComposer');
    }

    // endregion createToolsFromComposer

    // region helpers

    /**
     * The temp dir must be in a controlled depth/structure so that certain operations are working as expected.
     * But the temp dir must be outside the 'tests' folder, so that phpunit does not scan them.
     */
    private static function getTempDir(): string
    {
        $tempSetupDir = __DIR__.'/../../.tmp/BuilderTest/setup';
        if (is_dir($tempSetupDir) || mkdir($tempSetupDir, recursive: true)) {
            return $tempSetupDir;
        }
        throw new UnexpectedValueException('failed to create tempDir: '.$tempSetupDir);
    }

    /**
     * @psalm-return \Generator<string, array{0:callable():string, 1:bool, 2:bool}>
     */
    private static function dpForSetup(string $setupTemplate): Generator
    {
        $setupManifest = __DIR__."/../_data/setup/$setupTemplate/composer.json";
        $setupLock = __DIR__."/../_data/setup/$setupTemplate/composer.lock";

        yield 'locked NotInstalled' => [
            static fn () => $setupManifest,
            true,
            false,
        ];

        $tempSetupDir = self::getTempDir();

        $tempDir = tempnam($tempSetupDir, 'notLocked_notInstalled_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json")
                    ? "$tempDir/composer.json"
                    : throw new UnexpectedValueException("setup failed: $tempDir"),
            false,
            false,
        ];

        $tempDir = tempnam($tempSetupDir, 'locked_installed_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json") &&
                copy($setupLock, "$tempDir/composer.lock") &&
                false !== shell_exec('composer -d '.escapeshellarg($tempDir).' install --no-interaction --no-progress -q')
                    ? "$tempDir/composer.json"
                    : throw new UnexpectedValueException("setup failed: $tempDir"),
            true,
            true,
        ];

        $tempDir = tempnam($tempSetupDir, 'notLocked_installed_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json") &&
                copy($setupLock, "$tempDir/composer.lock") &&
                false !== shell_exec('composer -d '.escapeshellarg($tempDir).' install --no-interaction -q') &&
                unlink("$tempDir/composer.lock")
                    ? "$tempDir/composer.json"
                    : throw new UnexpectedValueException("setup failed: $tempDir"),
            false,
            true,
        ];
    }

    // endregion helpers
}
