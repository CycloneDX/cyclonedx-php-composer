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

use CycloneDX\Composer\MakeBom\Options;
use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Version;
use DomainException;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;

/**
 * @covers \CycloneDX\Composer\MakeBom\Options
 */
class OptionsTest extends TestCase
{
    /**
     * @dataProvider dpProducesOption
     */
    public function testProducesOption(string $inputString, array $expecteds): void
    {
        $command = new Command(__FUNCTION__);

        $options = new Options();
        $options->configureCommand($command);

        $input = new StringInput($inputString);
        $input->setInteractive(false);
        $input->bind($command->getDefinition());

        $options->setFromInput($input);

        foreach ($expecteds as $property => $expected) {
            self::assertSame($expected, $options->{$property});
        }
    }

    public function dpProducesOption(): Generator
    {
        yield 'defaults' => [
            '',
            [
                'outputFormat' => Format::XML,
                'outputFile' => Options::VALUE_OUTPUT_FILE_STDOUT,
                'omit' => [],
                'specVersion' => Version::v1dot4,
                'validate' => true,
                'mainComponentVersion' => null,
                'composerFile' => null,
            ],
        ];
        foreach ([Format::XML, Format::JSON] as $outputFormat) {
            yield "outputFormat $outputFormat" => [
                '--output-format '.escapeshellarg($outputFormat),
                ['outputFormat' => $outputFormat],
            ];
            $outputFormatLC = strtolower($outputFormat);
            yield "outputFormat $outputFormatLC -> $outputFormat" => [
                '--output-format '.escapeshellarg($outputFormatLC),
                ['outputFormat' => $outputFormat],
            ];
        }
        $randomFile = '/tmp/foo/'.uniqid('testing', true);
        yield 'outputFile' => [
            '--output-file '.escapeshellarg($randomFile),
            ['outputFile' => $randomFile],
        ];
        yield 'omit some' => [
            '--omit=dev --omit plugin --omit invalid-value',
            ['omit' => ['dev', 'plugin']],
        ];
        foreach ([Version::v1dot4, Version::v1dot3, Version::v1dot2, Version::v1dot1] as $specVersion) {
            yield "specVersion '$specVersion'" => [
                '--spec-version '.escapeshellarg($specVersion),
                ['specVersion' => $specVersion],
            ];
        }
        yield 'mainComponentVersion EmptyString -> null' => [
            '--mc-version ""',
            ['mainComponentVersion' => null],
        ];
        yield 'validate:true' => [
            '--validate',
            ['validate' => true],
        ];
        yield 'no-validate' => [
            '--no-validate',
            ['validate' => false],
        ];
        yield 'no-validate but validate ' => [
            '--no-validate --validate',
            ['validate' => true],
        ];
        $randVersion = uniqid('v', true);
        yield 'mainComponentVersion some' => [
            '--mc-version '.escapeshellarg($randVersion),
            ['mainComponentVersion' => $randVersion],
        ];
        $randVersion = uniqid('v', true);
        yield 'mainComponentVersion NonEmptyString' => [
            '--mc-version '.escapeshellarg($randVersion),
            ['mainComponentVersion' => $randVersion],
        ];
        yield 'no composerFile -> null' => [
            '--',
            ['composerFile' => null],
        ];
        yield 'empty composerFile -> null' => [
            "-- ''",
            ['composerFile' => null],
        ];
        $randomFile = 'foo/composer.json';
        yield 'some composerFile' => [
            '-- '.escapeshellarg($randomFile),
            ['composerFile' => $randomFile],
        ];
    }

    /**
     * @psalm-param class-string<\Throwable> $exception
     *
     * @dataProvider dpProducesOptionError
     */
    public function testProducesOptionError(string $inputString, string $exception, string $exceptionErrorMessage): void
    {
        $command = new Command(__FUNCTION__);

        $options = new Options();
        $options->configureCommand($command);

        $this->expectException($exception);
        $this->expectExceptionMessageMatches($exceptionErrorMessage);

        $input = new StringInput($inputString);
        $input->setInteractive(false);
        $input->bind($command->getDefinition());

        $options->setFromInput($input);
    }

    public function dpProducesOptionError(): Generator
    {
        yield 'unexpected option' => [
            '--unexpected-option foo',
            RuntimeException::class,
            '/option does not exist/i',
        ];
        $randomString = uniqid('', true);
        yield 'unexpected output-format' => [
            '--output-format '.escapeshellarg($randomString),
            DomainException::class,
            '/invalid value/i',
        ];
        yield 'empty output-file' => [
            '--output-file ""',
            DomainException::class,
            '/invalid value/i',
        ];
        yield 'unexpected spec-version' => [
            '--spec-version '.escapeshellarg($randomString),
            DomainException::class,
            '/invalid value/i',
        ];
        yield 'empty omit' => [
            '--omit',
            RuntimeException::class,
            '/option requires a value/i',
        ];
        yield 'empty mainComponentVersion' => [
            '--mc-version',
            RuntimeException::class,
            '/option requires a value/i',
        ];
    }
}
