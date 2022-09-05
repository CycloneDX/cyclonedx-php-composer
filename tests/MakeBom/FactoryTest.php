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

namespace CycloneDX\Tests\MakeBom;

use Composer\Composer;
use Composer\Factory as ComposerFactory;
use Composer\Package\AliasPackage;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Composer\MakeBom\Exceptions\LockerIsOutdatedError;
use CycloneDX\Composer\MakeBom\Factory;
use CycloneDX\Composer\MakeBom\Options;
use CycloneDX\Core\Serialize\JsonSerializer;
use CycloneDX\Core\Serialize\XmlSerializer;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Core\Validation\Validators\JsonValidator;
use CycloneDX\Core\Validation\Validators\XmlValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\MakeBom\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var ComposerFactory|\PHPUnit\Framework\MockObject\MockObject
     *
     * @psalm-var ComposerFactory&\PHPUnit\Framework\MockObject\MockObject
     */
    private $composerFactory;

    /**
     * @var SpecFactory|\PHPUnit\Framework\MockObject\MockObject
     *
     * @psalm-var  SpecFactory&\PHPUnit\Framework\MockObject\MockObject
     */
    private $specFactory;

    protected function setUp(): void
    {
        $this->composerFactory = $this->createMock(ComposerFactory::class);
        $this->specFactory = $this->createMock(SpecFactory::class);
        $this->factory = new Factory($this->composerFactory, $this->specFactory);
    }

    /**
     * @dataProvider dpMakeSerializerFromOptions
     *
     * @uses         \CycloneDX\Core\Serialize\JsonSerializer
     * @uses         \CycloneDX\Core\Serialize\XmlSerializer
     * @uses         \CycloneDX\Core\Serialize\BaseSerializer
     */
    public function testMakeSerializerFromOptions(string $outputFormat, string $expectedClass): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFormat = $outputFormat;

        $actual = $this->factory->makeSerializerFromOptions($options);

        self::assertInstanceOf($expectedClass, $actual);
    }

    public function dpMakeSerializerFromOptions()
    {
        yield 'xml' => ['XML', XmlSerializer::class];
        yield 'json' => ['JSON', JsonSerializer::class];
    }

    public function testMakeSpecFromOptions(): void
    {
        $options = $this->createMock(Options::class);
        $options->specVersion = 'foobar';
        $spec = $this->createStub(SpecInterface::class);

        $this->specFactory->expects(self::once())
            ->method('make')
            ->with('foobar')
            ->willReturn($spec);

        $actual = $this->factory->makeSpecFromOptions($options);

        self::assertSame($spec, $actual);
    }

    /**
     * @dataProvider dpMakeValidatorFromOptions
     *
     * @uses         \CycloneDX\Core\Validation\BaseValidator
     * @uses         \CycloneDX\Core\Validation\Validators\XmlValidator
     * @uses         \CycloneDX\Core\Validation\Validators\JsonValidator
     */
    public function testMakeValidatorFromOptions(string $outputFormat, string $expectedClass): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFormat = $outputFormat;
        $options->skipOutputValidation = false;

        $actual = $this->factory->makeValidatorFromOptions($options);

        self::assertInstanceOf($expectedClass, $actual);
    }

    public function dpMakeValidatorFromOptions()
    {
        yield 'xml' => ['XML', XmlValidator::class];
        yield 'json' => ['JSON', JsonValidator::class];
    }

    public function testMakeValidatorFromOptionsWhenSkippedIsNull(): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFormat = uniqid('format', true);
        $options->skipOutputValidation = true;

        $actual = $this->factory->makeValidatorFromOptions($options);

        self::assertNull($actual);
    }

    // region test makeLockerFromComposerForOptions

    public function testMakeLockerFromComposerForOptionsTrowsWHenNotLocked(): void
    {
        $locker = $this->createConfiguredMock(Locker::class, ['isLocked' => false, 'isFresh' => true]);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);

        $this->expectException(LockerIsOutdatedError::class);

        $this->factory->makeLockerFromComposerForOptions($composer, $options);
    }

    public function testMakeLockerFromComposerForOptionsTrowsWhenNotFresh(): void
    {
        $locker = $this->createConfiguredMock(Locker::class, ['isLocked' => true, 'isFresh' => false]);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);

        $this->expectException(LockerIsOutdatedError::class);

        $this->factory->makeLockerFromComposerForOptions($composer, $options);
    }

    public function testMakeLockerFromComposer(): void
    {
        $locker = $this->createConfiguredMock(
            Locker::class,
            [
                'isLocked' => true,
                'isFresh' => true,
                'getLockData' => ['packages-dev' => ['foo' => [/* some data */]]],
            ]
        );
        $lockedRepository = $this->createConfiguredMock(LockArrayRepository::class, ['getPackages' => []]);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = false;
        $options->excludePlugins = false;

        $locker->expects(self::once())->method('getLockedRepository')
            ->with(true)
            ->willReturn($lockedRepository);

        $actual = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $actual);
    }

    public function testMakeLockerFromComposerExcludesDev(): void
    {
        $locker = $this->createConfiguredMock(
            Locker::class,
            [
                'isLocked' => true,
                'isFresh' => true,
                'getLockData' => ['packages-dev' => []],
            ]
        );
        $lockedRepository = $this->createConfiguredMock(LockArrayRepository::class, ['getPackages' => []]);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = true;
        $options->excludePlugins = false;

        $locker->expects(self::once())->method('getLockedRepository')
            ->with(false)
            ->willReturn($lockedRepository);

        $actual = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $actual);
    }

    public function testMakeLockerFromComposerExcludesPlugins(): void
    {
        $package1 = $this->createConfiguredMock(Package::class, ['getType' => 'library']);
        $package2 = $this->createConfiguredMock(Package::class, ['getType' => 'composer-plugin']);
        $locker = $this->createConfiguredMock(
            Locker::class,
            [
                'isLocked' => true,
                'isFresh' => true,
                'getLockData' => ['packages-dev' => []],
            ]
        );
        $lockedRepository = $this->createConfiguredMock(
            LockArrayRepository::class,
            ['getPackages' => [$package1, $package2]]
        );
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = false;
        $options->excludePlugins = true;

        $locker->expects(self::once())->method('getLockedRepository')
            ->willReturn($lockedRepository);

        $lockedRepository->expects(self::once())->method('removePackage')
            ->with($package2);

        $actual = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $actual);
    }

    public function testMakeLockerFromComposerExcludesAliases(): void
    {
        $package1 = $this->createConfiguredMock(Package::class, ['getType' => 'library']);
        $package2 = $this->createConfiguredMock(AliasPackage::class, ['getType' => 'library']);
        $locker = $this->createConfiguredMock(
            Locker::class,
            [
                'isLocked' => true,
                'isFresh' => true,
                'getLockData' => ['packages-dev' => []],
            ]
        );
        $lockedRepository = $this->createConfiguredMock(
            LockArrayRepository::class,
            ['getPackages' => [$package1, $package2]]
        );
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = false;
        $options->excludePlugins = true;

        $locker->expects(self::once())->method('getLockedRepository')
            ->willReturn($lockedRepository);

        $lockedRepository->expects(self::once())->method('removePackage')
            ->with($package2);

        $actual = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $actual);
    }

    // endregion test makeLockerFromComposerForOptions

    public function testMakeComposer(): void
    {
        $io = $this->createStub(\Composer\IO\IOInterface::class);
        $composer = $this->createStub(Composer::class);
        $options = $this->createMock(Options::class);
        $options->composerFile = 'foo/bar';

        $this->composerFactory->expects(self::once())->method('createComposer')
            ->with($io, 'foo/bar', true)
            ->willReturn($composer);

        $actual = $this->factory->makeComposer($options, $io);

        self::assertSame($composer, $actual);
    }

    // region test makeBomOutput

    public function testMakeBomOutputStdOutIsNull(): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFile = '-';

        $actual = $this->factory->makeBomOutput($options);

        self::assertNull($actual);
    }

    public function testMakeBomOutputForFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), __METHOD__);
        file_put_contents($tempFile, 'baz');
        try {
            $options = $this->createMock(Options::class);
            $options->outputFile = $tempFile;

            $actual = $this->factory->makeBomOutput($options);
            self::assertNotNull($actual);

            $actual->write('foo! bar');
            self::assertSame('foo! bar', file_get_contents($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    // endregion test makeBomOutput
}
