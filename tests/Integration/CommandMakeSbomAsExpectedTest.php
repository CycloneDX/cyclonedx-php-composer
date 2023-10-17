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

namespace CycloneDX\Tests\Integration;

use CycloneDX\Composer\MakeBom\Builder;
use CycloneDX\Composer\MakeBom\Command;
use CycloneDX\Composer\MakeBom\Options;
use CycloneDX\Composer\Plugin;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(Command::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(Options::class)]
#[UsesClass(Builder::class)]
final class CommandMakeSbomAsExpectedTest extends CommandTestCase
{
    protected function setUp(): void
    {
        self::assertTrue(putenv('CDX_CP_TOOLS_VERSION_OVERRIDE=in-dev'));
        self::assertTrue(putenv('CDX_CP_TOOLS_EXCLUDE_LIBS=1'));
        self::assertTrue(putenv('CDX_CP_TOOLS_EXCLUDE_COMPOSER=1'));
    }

    protected function tearDown(): void
    {
        self::assertTrue(putenv('CDX_CP_TOOLS_VERSION_OVERRIDE='));
        self::assertTrue(putenv('CDX_CP_TOOLS_EXCLUDE_LIBS='));
        self::assertTrue(putenv('CDX_CP_TOOLS_EXCLUDE_COMPOSER='));
    }

    #[DataProvider('dp')]
    public function test(string $outputFormat, string $specV, string $composeFile, array $omit,
        string $expectedSbomFile): void
    {
        $outFile = tempnam(sys_get_temp_dir(), 'CommandMakeSbomAsExpectedTest');
        $in = new ArrayInput([
            'command' => 'CycloneDX:make-sbom',
            '--output-format' => $outputFormat,
            '--spec-version' => $specV,
            '--output-reproducible' => true,
            '--validate' => true,
            '--output-file' => $outFile,
            '--omit' => $omit,
            'composer-file' => $composeFile,
        ]);
        $out = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL);
        $app = self::make_app((new Plugin())->getCommands()[0]);

        $exitCode = $app->run($in, $out);

        self::assertSame(0, $exitCode, $out->fetch());

        if ('1' === getenv('CDX_CP_TESTS_UPDATE_SNAPSHOTS')) {
            copy($outFile, $expectedSbomFile);
        }
        self::assertFileEquals($expectedSbomFile, $outFile);
    }

    public static function dp(): Generator
    {
        foreach (['devReq', 'laravel-7.12.0', 'local'] as $purpose) {
            foreach (['json', 'xml'] as $outputFormat) {
                $specVs = ['1.5', '1.4', '1.3', '1.2'];
                if ('xml' === $outputFormat) {
                    $specVs[] = '1.1';
                }
                foreach ($specVs as $specV) {
                    $composeFile = self::DEMO_ROOT."/$purpose/project/composer.json";
                    $snapshotFile = self::DEMO_ROOT."/$purpose/results/bom.$specV.$outputFormat";
                    yield "demo: $purpose $outputFormat $specV" => [
                        $outputFormat,
                        $specV,
                        $composeFile,
                        'devReq' === $purpose ? [] : ['dev'],
                        $snapshotFile,
                    ];
                }
            }
        }
    }
}
