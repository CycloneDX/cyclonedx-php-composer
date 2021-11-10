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

namespace Tests\MakeBom;

use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Composer\MakeBom\Exceptions\ValueError;
use CycloneDX\Composer\MakeBom\Options;
use CycloneDX\Core\Spec\Version;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

/**
 * @covers \CycloneDX\Composer\MakeBom\Options
 */
class OptionsTest extends TestCase
{
    //region configureCommand

    public function testConfigureCommand(): void
    {
        $command = $this->createMock(Command::class);

        $command->expects(self::atLeastOnce())->method('addOption')
            ->willReturnSelf();

        (new Options())->configureCommand($command);
    }

    // endregion configureCommand

    // region setFromInput

    /**
     * @dataProvider dpSetFromInput
     */
    public function testSetFromInput(InputInterface $input, string $property, $expected): void
    {
        $options = (new Options())->setFromInput($input);
        $value = $options->{$property};
        self::assertSame($expected, $value, "property: $property");
    }

    public static function dpSetFromInput(): \Generator
    {
        $data = [
            /* @see \CycloneDX\Composer\MakeBom\Options::$outputFormat */
            'bomFormat default XML' => ['', 'outputFormat', 'XML'],
            ['--output-format=XML', 'outputFormat', 'XML'],
            ['--output-format=JSON', 'outputFormat', 'JSON'],
            /* @see \CycloneDX\Composer\MakeBom\Options::$skipOutputValidation */
            'skipOutputValidation default false' => ['', 'skipOutputValidation', false],
            ['--no-validate', 'skipOutputValidation', true],
            /* @see \CycloneDX\Composer\MakeBom\Options::$outputFile */
            'outputFile default XML' => ['', 'outputFile', 'bom.xml'],
            ['--output-format=XML', 'outputFile', 'bom.xml'],
            ['--output-format=JSON', 'outputFile', 'bom.json'],
            ['--output-file=fooBar', 'outputFile', 'fooBar'],
            ['--output-file=-', 'outputFile', '-'],
            /* @see \CycloneDX\Composer\MakeBom\Options::$excludeDev */
            'excludeDev default disabled' => ['', 'excludeDev', false],
            ['--exclude-dev', 'excludeDev', true],
            /* @see \CycloneDX\Composer\MakeBom\Options::$excludePlugins */
            'excludePlugins default disabled' => ['', 'excludePlugins', false],
            ['--exclude-plugins', 'excludePlugins', true],
            /* @see \CycloneDX\Composer\MakeBom\Options::$specVersion */
            'specVersion default latest' => ['', 'specVersion', SpecFactory::VERSION_LATEST],
            ['--spec-version=1.1', 'specVersion', Version::V_1_1],
            ['--spec-version=1.2', 'specVersion', Version::V_1_2],
            ['--spec-version=1.3', 'specVersion', Version::V_1_3],
            /* @see \CycloneDX\Composer\MakeBom\Options::$omitVersionNormalization */
            'omitVersionNormalization default' => ['', 'omitVersionNormalization', false],
            ['--no-version-normalization', 'omitVersionNormalization', true],
            /* @see \CycloneDX\Composer\MakeBom\Options::$composerFile */
            'composerFile default to null' => ['', 'composerFile', null],
            ['my/project/composer.json', 'composerFile', 'my/project/composer.json'],
        ];

        $command = new Command('dummy');
        (new Options())->configureCommand($command);

        foreach ($data as $title => [$inputString, $property, $expected]) {
            $input = new StringInput($inputString);
            $input->setInteractive(false);
            $input->bind($command->getDefinition());
            yield (
            \is_int($title)
                ? "$inputString -> $property=".var_export($expected, true)
                : $title
            ) => [$input, $property, $expected];
        }
    }

    public function testSetFromInputThrowsOnInvalidSpec(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->willReturnMap(
                [
                    ['spec-version', 'FOO'], // test object
                    ['output-format', 'XML'],
                ]
            );

        $this->expectException(ValueError::class);
        $this->expectExceptionMessageMatches('/invalid value for option "spec-version"/i');

        (new Options())->setFromInput($input);
    }

    public function testSetFromInputThrowsOnInvalidOutputFormat(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->willReturnMap(
                [
                    ['spec-version', '1.2'],
                    ['output-format', 'Foo'], // test object
                ]
            );

        $this->expectException(ValueError::class);
        $this->expectExceptionMessageMatches('/invalid value for option "output-format"/i');

        (new Options())->setFromInput($input);
    }

    // endregion setFromInput
}
