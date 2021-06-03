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

namespace CycloneDX\Tests\unit\Composer;

use CycloneDX\Composer\BomGenerator;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BomGeneratorTest.
 *
 * @covers \CycloneDX\Composer\BomGenerator
 */
class BomGeneratorTest extends TestCase
{
    /** @psalm-var BomGenerator */
    private $bomGenerator;

    /**
     * @psalm-var \PHPUnit\Framework\MockObject\MockObject|OutputInterface
     * @psalm-var \PHPUnit\Framework\MockObject\MockObject&OutputInterface
     */
    private $outputMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomGenerator = new BomGenerator($this->outputMock);
    }

    // region getPackagesFromLock

    /**
     * @dataProvider LockProvider
     *
     * @psalm-param array<string, mixed> $lock
     * @psalm-param array<string, mixed> $expected
     */
    public function testGetPackagesFromLock(array $lock, bool $excludeDev, array $expected): void
    {
        /* @see BomGenerator::getPackagesFromLock() */
        $getPackagesFromLock = (new ReflectionClass(BomGenerator::class))->getMethod('getPackagesFromLock');
        $getPackagesFromLock->setAccessible(true);

        if ($excludeDev) {
            $this->outputMock
                ->expects(self::once())
                ->method('writeln')
                ->with(self::matchesRegularExpression('/dev dependencies will be skipped/i'));
        }

        $packages = $getPackagesFromLock->invoke($this->bomGenerator, $lock, $excludeDev);
        self::assertEquals($expected, $packages);
    }

    /**
     * @psalm-return Generator<array{array, bool, array}>
     */
    public function LockProvider(): Generator
    {
        $packages = [];
        $packagesDev = [];

        yield 'both, includeDev' => [
            ['packages' => $packages, 'packages-dev' => $packagesDev],
            false,
            $packages,
        ];
        yield 'packagesDev, includeDev' => [
            ['packages-dev' => $packagesDev],
            false,
            [],
        ];
        yield 'both, excludeDev' => [
            ['packages' => $packages, 'packages-dev' => $packagesDev],
            true,
            array_merge($packages, $packagesDev),
        ];
        yield 'packages, excludeDev' => [
            ['packages' => $packages],
            true,
            $packages,
        ];
        yield 'packagesDev, excludeDev' => [
            ['packages-dev' => $packagesDev],
            true,
            $packagesDev,
        ];
    }

    // endregion getPackagesFromLock

    // region filterOutPlugins

    /**
     * @dataProvider packageProvider
     *
     * @psalm-param array<string, mixed> $notPlugins
     * @psalm-param array<string, mixed> $plugins
     */
    public function testFilterOutPlugins(array $notPlugins, array $plugins): void
    {
        $packages = array_merge($notPlugins, $plugins);

        /* @see BomGenerator::filterOutPlugins() */
        $filterOutPlugins = (new ReflectionClass(BomGenerator::class))->getMethod('filterOutPlugins');
        $filterOutPlugins->setAccessible(true);

        foreach ($plugins as ['name' => $pluginName]) {
            $this->outputMock
                ->expects(self::once())
                ->method('writeln')
                ->with(self::matchesRegularExpression('/Skipping plugin .*'.preg_quote($pluginName, '/').'/i'));
        }

        $filtered = iterator_to_array($filterOutPlugins->invoke($this->bomGenerator, $packages));
        self::assertEquals($notPlugins, $filtered);
    }

    /**
     * @psalm-return Generator<array{array, array}>
     */
    public function packageProvider(): Generator
    {
        $notPlugins = [
            ['type' => 'library', 'name' => 'acme/library'],
        ];
        $plugins = [
            ['type' => 'composer-plugin', 'name' => 'acme/plugin'],
        ];

        yield 'non-plugins only' => [$notPlugins, []];
        yield 'plugins only' => [[], $plugins];
        yield 'both' => [$notPlugins, $plugins];
    }

    // endregion filterOutPlugins

    // region normalizeVersion

    /**
     * @dataProvider versionProvider
     */
    public function testNormalizeVersion(string $version, string $expected): void
    {
        /* @see BomGenerator::normalizeVersion() */
        $normalizeVersion = (new ReflectionClass(BomGenerator::class))->getMethod('normalizeVersion');
        $normalizeVersion->setAccessible(true);
        $normalized = $normalizeVersion->invoke($this->bomGenerator, $version);
        self::assertEquals($expected, $normalized);
    }

    /**
     * @psalm-return Generator<array{string, string}>
     */
    public function versionProvider(): Generator
    {
        yield ['1.0.0', '1.0.0'];
        yield ['v1.0.0', '1.0.0'];
        yield ['dev-master', 'dev-master'];
    }

    // endregion normalizeVersion

    // region splitLicenses

    public function testReadLicensesWithLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('MIT');
        self::assertEquals(['MIT'], $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('(MIT or Apache-2.0)');
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    public function testReadLicensesWithConjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('(MIT and Apache-2.0)');
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseArray(): void
    {
        $licenses = $this->bomGenerator->splitLicenses(['MIT', 'Apache-2.0']);
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    // endregion splitLicenses
}
