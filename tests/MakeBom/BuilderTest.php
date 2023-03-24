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
use CycloneDX\Core\Enums;
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
        $builder = new Builder(false, false, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertRootComponent($sbom, true);
        self::assertComponentSymfonyLock($sbom);
        self::assertComponentPsrLog($sbom);
        self::assertComponentCdxPlugin($sbom);

        // dev requirements
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

        self::assertRootComponent($sbom, false);
        self::assertComponentSymfonyLock($sbom);
        self::assertComponentPsrLog($sbom);
        $fComponents = $sbom->getComponents()->findItem('cyclonedx-php-composer', 'cyclonedx');
        self::assertCount(0, $fComponents);
    }

    #[DataProvider('dpCreateSbomFromComposer')]
    public function testCreateSbomFromComposerOmittingPlugins(callable $setup, bool $locked, bool $installed): void
    {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: \dirname($setupManifest));
        $builder = new Builder(false, true, null);

        if (false === $locked && false === $installed) {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessageMatches('/no lockfile/i');
        }
        $sbom = $builder->createSbomFromComposer($composer);

        self::assertRootComponent($sbom, false);
        self::assertComponentSymfonyLock($sbom);
        self::assertComponentPsrLog($sbom);
        $fComponents = $sbom->getComponents()->findItem('cyclonedx-php-composer', 'cyclonedx');
        self::assertCount(0, $fComponents);
    }

    /**
     * @psalm-return \Generator<string, array{0:callable():string, 1:bool, 2:bool}>
     */
    public static function dpCreateSbomFromComposer(): Generator
    {
        yield from self::dpForSetup('testCreateSbomFromComposer');
    }

    // region helpers

    private static function assertRootComponent (Models\Bom $sbom, bool $expectCdxPlugin): void {
        $component = $sbom->getMetadata()->getComponent();
        self::assertSame(Enums\ComponentType::Application, $component->getType());
        self::assertSame('test_data_for_create-sbom-from-composer', $component->getName());
        self::assertSame('cyclonedx', $component->getGroup());
        self::assertSame('dev-master', $component->getVersion());
        self::assertSame("test setup 'testCreateSbomFromComposer'", $component->getDescription());
        self::assertCount(1, $component->getLicenses());
        self::assertSame('Apache-2.0', $component->getLicenses()->getItems()[0]->getId());
        self::assertSame('Jan Kowalleck', $component->getAuthor());
        self::assertSame('pkg:composer/cyclonedx/test_data_for_create-sbom-from-composer@dev-master', (string) $component->getPackageUrl());
        $componentProperties = $component->getProperties()->getItems();
        $fComponentProperties = array_filter($componentProperties, static fn ($c) => 'cdx:composer:package:type' === $c->getName());
        self::assertCount(1, $fComponentProperties);
        /** @var Models\Property $componentProperty */
        $componentProperty = reset($fComponentProperties);
        self::assertSame('project', $componentProperty->getValue());
        $expectedDeps = [            $sbom->getComponents()->findItem('lock', 'symfony')[0]->getBomRef()];
        if ($expectCdxPlugin)  {
            $expectedDeps[] = $sbom->getComponents()->findItem('cyclonedx-php-composer', 'cyclonedx')[0]->getBomRef();
        }
        self::assertEquals($expectedDeps, $component->getDependencies()->getItems());
    }

    private static function assertComponentSymfonyLock (Models\Bom $sbom): void {
        $fComponents = $sbom->getComponents()->findItem('lock', 'symfony');
        self::assertCount(1, $fComponents);
        $component = $fComponents[0];
        self::assertSame(Enums\ComponentType::Library, $component->getType());
        self::assertSame('lock', $component->getName());
        self::assertSame('symfony', $component->getGroup());
        self::assertSame('v6.2.7', $component->getVersion());
        self::assertCount(1, $component->getLicenses());
        self::assertSame('MIT', $component->getLicenses()->getItems()[0]->getId());
        self::assertSame('Jérémy Derussé, Symfony Community', $component->getAuthor());
        self::assertSame('pkg:composer/symfony/lock@v6.2.7', (string) $component->getPackageUrl());
        $componentProperties = $component->getProperties()->getItems();
        foreach ([
                     'cdx:composer:package:type' => ['library'],
                     'cdx:composer:package:distReference' => ['febdeed9473e568ff34bf4350c04760f5357dfe2'],
                     'cdx:composer:package:sourceReference' => ['febdeed9473e568ff34bf4350c04760f5357dfe2'],
                 ] as $propertyName => $expectedValues) {
            $fComponentPropertyValues = array_values(array_map(
                static fn ($p) => $p->getValue(),
                array_filter($componentProperties, static fn ($c) => $c->getName() === $propertyName)));
            self::assertEquals($expectedValues, $fComponentPropertyValues);
        }
        $extRefs = $component->getExternalReferences()->getItems();
        foreach ([
                     [Enums\ExternalReferenceType::Distribution, ['https://api.github.com/repos/symfony/lock/zipball/febdeed9473e568ff34bf4350c04760f5357dfe2']],
                     [Enums\ExternalReferenceType::VCS, ['https://github.com/symfony/lock.git', 'https://github.com/symfony/lock/tree/v6.2.7']],
                 ] as [$extRefType, $expectedUrls]) {
            $fExtRefUrls = array_values(array_map(
                static fn ($er) => $er->getUrl(),
                array_filter($extRefs, static fn ($er) => $er->getType() === $extRefType)));
            self::assertEquals($expectedUrls, $fExtRefUrls);
        }
        self::assertEquals(
            [$sbom->getComponents()->findItem('log', 'psr')[0]->getBomRef()],
            $component->getDependencies()->getItems());
    }

    public static function assertComponentPsrLog(Models\Bom $sbom): void {
        $fComponents = $sbom->getComponents()->findItem('log', 'psr');
        self::assertCount(1, $fComponents);
        $component = $fComponents[0];
        self::assertSame(Enums\ComponentType::Library, $component->getType());
        self::assertSame('log', $component->getName());
        self::assertSame('psr', $component->getGroup());
        self::assertSame('3.0.0', $component->getVersion());
        self::assertCount(1, $component->getLicenses());
        self::assertSame('MIT', $component->getLicenses()->getItems()[0]->getId());
        self::assertSame('PHP-FIG', $component->getAuthor());
        self::assertSame('pkg:composer/psr/log@3.0.0', (string) $component->getPackageUrl());
        $componentProperties = $component->getProperties()->getItems();
        foreach ([
                     'cdx:composer:package:type' => ['library'],
                     'cdx:composer:package:distReference' => ['fe5ea303b0887d5caefd3d431c3e61ad47037001'],
                     'cdx:composer:package:sourceReference' => ['fe5ea303b0887d5caefd3d431c3e61ad47037001'],
                 ] as $propertyName => $expectedValues) {
            $fComponentPropertyValues = array_values(array_map(
                static fn ($p) => $p->getValue(),
                array_filter($componentProperties, static fn ($c) => $c->getName() === $propertyName)));
            self::assertEquals($expectedValues, $fComponentPropertyValues);
        }
        $extRefs = $component->getExternalReferences()->getItems();
        foreach ([
                     [Enums\ExternalReferenceType::Distribution, ['https://api.github.com/repos/php-fig/log/zipball/fe5ea303b0887d5caefd3d431c3e61ad47037001']],
                     [Enums\ExternalReferenceType::VCS, ['https://github.com/php-fig/log.git', 'https://github.com/php-fig/log/tree/3.0.0']],
                 ] as [$extRefType, $expectedUrls]) {
            $fExtRefUrls = array_values(array_map(
                static fn ($er) => $er->getUrl(),
                array_filter($extRefs, static fn ($er) => $er->getType() === $extRefType)));
            self::assertEquals($expectedUrls, $fExtRefUrls);
        }
        self::assertEquals([], $component->getDependencies()->getItems());
    }


    private static function assertComponentCdxPlugin(Models\Bom $sbom): void{
        $fComponents = $sbom->getComponents()->findItem('cyclonedx-php-composer', 'cyclonedx');
        self::assertCount(1, $fComponents);
        $component = $fComponents[0];
        self::assertSame(Enums\ComponentType::Library, $component->getType());
        self::assertSame('cyclonedx-php-composer', $component->getName());
        self::assertSame('cyclonedx', $component->getGroup());
        self::assertSame('dev-master', $component->getVersion());
        self::assertCount(1, $component->getLicenses());
        self::assertSame('Apache-2.0', $component->getLicenses()->getItems()[0]->getId());
        self::assertSame('Jan Kowalleck', $component->getAuthor());
        self::assertSame('pkg:composer/cyclonedx/cyclonedx-php-composer@dev-master', (string) $component->getPackageUrl());
        $componentProperties = $component->getProperties()->getItems();
        foreach ([
                     'cdx:composer:package:isDevRequirement' => ['true'],
                     'cdx:composer:package:type' => ['composer-plugin'],
                 ] as $propertyName => $expectedValues) {
            $fComponentPropertyValues = array_values(array_map(
                static fn ($p) => $p->getValue(),
                array_filter($componentProperties, static fn ($c) => $c->getName() === $propertyName)));
            self::assertEquals($expectedValues, $fComponentPropertyValues);
        }
        $extRefs = $component->getExternalReferences()->getItems();
        foreach ([
                     [Enums\ExternalReferenceType::Distribution, ['../../../..']],
                     [Enums\ExternalReferenceType::VCS, ['https://github.com/CycloneDX/cyclonedx-php-composer/']],
                     [Enums\ExternalReferenceType::Website, ['https://github.com/CycloneDX/cyclonedx-php-composer/#readme']],
                     [Enums\ExternalReferenceType::IssueTracker, ['https://github.com/CycloneDX/cyclonedx-php-composer/issues']],
                 ] as [$extRefType, $expectedUrls]) {
            $fExtRefUrls = array_values(array_map(
                static fn ($er) => $er->getUrl(),
                array_filter($extRefs, static fn ($er) => $er->getType() === $extRefType)));
            self::assertEquals($expectedUrls, $fExtRefUrls);
        }
        self::assertEquals([
            $sbom->getComponents()->findItem('cyclonedx-library', 'cyclonedx')[0]->getBomRef(),
            $sbom->getComponents()->findItem('packageurl-php', 'package-url')[0]->getBomRef(),
        ], $component->getDependencies()->getItems());

    }

    // endregion helpers

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
