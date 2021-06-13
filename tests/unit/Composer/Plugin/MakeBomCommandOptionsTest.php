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

namespace CycloneDX\Tests\unit\Composer\Plugin;

use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Composer\Plugin\Exceptions\ValueError;
use CycloneDX\Composer\Plugin\MakeBomCommandOptions;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Specs\Version;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

/**
 * @covers \CycloneDX\Composer\Plugin\MakeBomCommandOptions
 */
class MakeBomCommandOptionsTest extends TestCase
{
    public function testConfigureCommandReturnsInput(): void
    {
        $command = $this->createConfiguredMock(Command::class, ['addOption' => new ReturnSelf()]);
        $got = MakeBomCommandOptions::configureCommand($command);
        self::assertSame($command, $got);
    }

    // region makeFromInput

    /**
     * @dataProvider dpMakeFromInput
     */
    public function testMakeFromInput(InputInterface $input, string $property, $expected): void
    {
        $options = MakeBomCommandOptions::makeFromInput($input);
        $value = $options->{$property};
        self::assertSame($expected, $value, "property: ${property}");
    }

    public static function dpMakeFromInput(): \Generator
    {
        $data = [
            /* @see MakeBomCommandOptions::bomFormat */
            'bomFormat default XML' => ['', 'bomFormat', 'XML'],
            ['--output-format=XML', 'bomFormat', 'XML'],
            ['--output-format=JSON', 'bomFormat', 'JSON'],
            /* @see MakeBomCommandOptions::bomWriterClass */
            'bomWriterClass default XML' => ['', 'bomWriterClass', XmlSerializer::class],
            ['--output-format=XML', 'bomWriterClass', XmlSerializer::class],
            ['--output-format=JSON', 'bomWriterClass', JsonSerializer::class],
            /* @see MakeBomCommandOptions::outputFile */
            'outputFile default XML' => ['', 'outputFile', 'bom.xml'],
            ['--output-format=XML', 'outputFile', 'bom.xml'],
            ['--output-format=JSON', 'outputFile', 'bom.json'],
            ['--output-file=fooBar', 'outputFile', 'fooBar'],
            /* @see MakeBomCommandOptions::excludeDev */
            'excludeDev default disabled' => ['', 'excludeDev', false],
            ['--exclude-dev', 'excludeDev', true],
            /* @see MakeBomCommandOptions::excludePlugins */
            'excludePlugins default disabled' => ['', 'excludePlugins', false],
            ['--exclude-plugins', 'excludePlugins', true],
            /* @see MakeBomCommandOptions::specVersion */
            'specVersion default latest' => ['', 'specVersion', SpecFactory::VERSION_LATEST],
            ['--spec-version=1.1', 'specVersion', Version::V_1_1],
            ['--spec-version=1.2', 'specVersion', Version::V_1_2],
            ['--spec-version=1.3', 'specVersion', Version::V_1_3],
        ];

        $command = MakeBomCommandOptions::configureCommand(new Command());

        foreach ($data as $title => [$inputString, $property, $expected]) {
            $input = new StringInput($inputString);
            $input->setInteractive(false);
            $input->bind($command->getDefinition());
            yield (
            \is_int($title)
                ? "${inputString} -> ${property}=".var_export($expected, true)
                : $title
            ) => [$input, $property, $expected];
        }
    }

    public function testMakeFromInputThrowsOnInvalidSpec(): void
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

        MakeBomCommandOptions::makeFromInput($input);
    }

    public function testMakeFromInputThrowsOnInvalidOutputFormat(): void
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

        MakeBomCommandOptions::makeFromInput($input);
    }

    // endregion makeFromInput
}
