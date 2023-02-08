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

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Composer\MakeBom\Options::class)]
final class OptionsTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpProducesOption')]
    public function testProducesOption(string $inputString, array $expecteds): void
    {
        $options = new Options();
        $command = new Command(__FUNCTION__);

        $command->setDefinition($options->getDefinition());

        $input = new StringInput($inputString);
        $input->setInteractive(false);
        $input->bind($command->getDefinition());

        $options->setFromInput($input);

        foreach ($expecteds as $property => $expected) {
            self::assertSame($expected, $options->{$property});
        }
    }

    public static function dpProducesOption(): Generator
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
        foreach ([
            'XML' => Format::XML,
            'JSON' => Format::JSON,
         ] as $outputFormatIn => $outputFormat) {
            yield "outputFormat $outputFormatIn" => [
                '--output-format '.escapeshellarg($outputFormatIn),
                ['outputFormat' => $outputFormat],
            ];
            $outputFormatLC = strtolower($outputFormatIn);
            yield "outputFormat $outputFormatLC -> $outputFormatIn" => [
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
        foreach ([
            '1.4' => Version::v1dot4,
            '1.3' => Version::v1dot3,
            '1.2' => Version::v1dot2,
            '1.1' => Version::v1dot1,
         ] as $specVersionIn => $specVersion) {
            yield "specVersion '$specVersionIn'" => [
                '--spec-version '.escapeshellarg($specVersionIn),
                ['specVersion' => $specVersion],
            ];
        }
        yield 'mainComponentVersion EmptyString -> null' => [
            '--mc-version ""',
            ['mainComponentVersion' => null],
        ];
        yield 'output-reproducible:true' => [
            '--output-reproducible',
            ['outputReproducible' => true],
        ];
        yield 'no-output-reproducible' => [
            '--no-output-reproducible',
            ['outputReproducible' => false],
        ];
        yield 'no-output-reproducible but output-reproducible' => [
            '--no-output-reproducible --output-reproducible',
            ['outputReproducible' => true],
        ];
        yield 'output-reproducible but no-output-reproducible' => [
            '--output-reproducible --no-output-reproducible',
            ['outputReproducible' => false],
        ];
        yield 'validate:true' => [
            '--validate',
            ['validate' => true],
        ];
        yield 'no-validate' => [
            '--no-validate',
            ['validate' => false],
        ];
        yield 'no-validate but validate' => [
            '--no-validate --validate',
            ['validate' => true],
        ];
        yield 'validate but no-validate' => [
            '--validate --no-validate',
            ['validate' => false],
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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpProducesOptionError')]
    public function testProducesOptionError(string $inputString, string $exception, string $exceptionErrorMessage): void
    {
        $options = new Options();
        $command = new Command(__FUNCTION__);

        $command->setDefinition($options->getDefinition());

        $this->expectException($exception);
        $this->expectExceptionMessageMatches($exceptionErrorMessage);

        $input = new StringInput($inputString);
        $input->setInteractive(false);
        $input->bind($command->getDefinition());

        $options->setFromInput($input);
    }

    public static function dpProducesOptionError(): Generator
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
