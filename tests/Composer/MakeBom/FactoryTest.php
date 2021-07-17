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

namespace CycloneDX\Tests\Composer\MakeBom;

use Composer\Composer;
use Composer\Factory as ComposerFactory;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\MakeBom\Exceptions\LockerIsOutdatedError;
use CycloneDX\Composer\MakeBom\Factory;
use CycloneDX\Composer\MakeBom\Options;
use CycloneDX\Core\Serialize\JsonSerializer;
use CycloneDX\Core\Serialize\XmlSerializer;
use CycloneDX\Core\Spec\Spec11;
use CycloneDX\Core\Spec\Spec12;
use CycloneDX\Core\Spec\Spec13;
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
     * @psalm-var ComposerFactory&\PHPUnit\Framework\MockObject\MockObject
     */
    private $composerFactory;

    protected function setUp(): void
    {
        $this->composerFactory = $this->createMock(ComposerFactory::class);
        $this->factory = new Factory($this->composerFactory);
    }

    /**
     * @dataProvider dpMakeSerializerFromOptions
     *
     * @uses         \CycloneDX\Composer\Factories\SpecFactory
     * @uses         \CycloneDX\Core\Serialize\JsonSerializer
     * @uses         \CycloneDX\Core\Serialize\XmlSerializer
     */
    public function testMakeSerializerFromOptions(string $outputFormat, string $expectedClass): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFormat = $outputFormat;

        $got = $this->factory->makeSerializerFromOptions($options);

        self::assertInstanceOf($expectedClass, $got);
    }

    public function dpMakeSerializerFromOptions()
    {
        yield 'xml' => ['XML', XmlSerializer::class];
        yield 'json' => ['JSON', JsonSerializer::class];
    }

    /**
     * @dataProvider dpMakeSpecFromOptions
     *
     * @uses         \CycloneDX\Composer\Factories\SpecFactory
     */
    public function testMakeSpecFromOptions(string $specVersion, string $specClass): void
    {
        $options = $this->createMock(Options::class);
        $options->specVersion = $specVersion;

        $got = $this->factory->makeSpecFromOptions($options);

        self::assertInstanceOf($specClass, $got);
    }

    public function dpMakeSpecFromOptions(): \Generator
    {
        yield '1.1' => ['1.1', Spec11::class];
        yield '1.2' => ['1.2', Spec12::class];
        yield '1.3' => ['1.3', Spec13::class];
    }

    /**
     * @dataProvider dpMakeValidatorFromOptions
     *
     * @uses         \CycloneDX\Composer\Factories\SpecFactory
     * @uses         \CycloneDX\Core\Validation\AbstractValidator
     * @uses         \CycloneDX\Core\Validation\Validators\XmlValidator
     * @uses         \CycloneDX\Core\Validation\Validators\JsonValidator
     */
    public function testMakeValidatorFromOptions(string $outputFormat, string $expectedClass): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFormat = $outputFormat;
        $options->skipOutputValidation = false;

        $got = $this->factory->makeValidatorFromOptions($options);

        self::assertInstanceOf($expectedClass, $got);
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

        $got = $this->factory->makeValidatorFromOptions($options);

        self::assertNull($got);
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

    public function testMakeLockerFromComposerForOptionsTrowsWHenNotFresh(): void
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
            ['isLocked' => true, 'isFresh' => true, 'getDevPackageNames' => ['foo']]
        );
        $lockedRepository = $this->createStub(LockArrayRepository::class);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = false;
        $options->excludePlugins = false;

        $locker->expects(self::once())->method('getLockedRepository')
            ->with(true)
            ->willReturn($lockedRepository);

        $got = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $got);
    }

    public function testMakeLockerFromComposerExcludesDev(): void
    {
        $locker = $this->createConfiguredMock(
            Locker::class,
            ['isLocked' => true, 'isFresh' => true, 'getDevPackageNames' => []]
        );
        $lockedRepository = $this->createStub(LockArrayRepository::class);
        $composer = $this->createConfiguredMock(Composer::class, ['getLocker' => $locker]);
        $options = $this->createStub(Options::class);
        $options->excludeDev = true;
        $options->excludePlugins = false;

        $locker->expects(self::once())->method('getLockedRepository')
            ->with(false)
            ->willReturn($lockedRepository);

        $got = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $got);
    }

    public function testMakeLockerFromComposerExcludesPlugins(): void
    {
        $package1 = $this->createConfiguredMock(Package::class, ['getType' => 'library']);
        $package2 = $this->createConfiguredMock(Package::class, ['getType' => 'composer-plugin']);
        $locker = $this->createConfiguredMock(
            Locker::class,
            ['isLocked' => true, 'isFresh' => true, 'getDevPackageNames' => []]
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

        $got = $this->factory->makeLockerFromComposerForOptions($composer, $options);

        self::assertSame($lockedRepository, $got);
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

        $got = $this->factory->makeComposer($options, $io);

        self::assertSame($composer, $got);
    }

    // region test makeBomOutput

    public function testMakeBomOutputStdOutIsNull(): void
    {
        $options = $this->createMock(Options::class);
        $options->outputFile = '-';

        $got = $this->factory->makeBomOutput($options);

        self::assertNull($got);
    }

    public function testMakeBomOutputForFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), __METHOD__);
        file_put_contents($tempFile, 'baz');
        try {
            $options = $this->createMock(Options::class);
            $options->outputFile = $tempFile;

            $got = $this->factory->makeBomOutput($options);
            self::assertNotNull($got);

            $got->write('foo! bar');
            self::assertSame('foo! bar', file_get_contents($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    // endregion test makeBomOutput
}
