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
final class CommandMakeSbomFailsTest extends CommandTestCase
{
    #[DataProvider('dp')]
    public function test(array $input, string $expectedOutput): void
    {
        $outFile = tempnam(sys_get_temp_dir(), 'testMakeSbom');
        $in = new ArrayInput([
            'command' => 'CycloneDX:make-sbom',
            '--output-file' => $outFile,
            ...$input,
        ]);
        $out = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL);
        $app = self::make_app((new Plugin())->getCommands()[0]);

        $exitCode = $app->run($in, $out);

        self::assertGreaterThan(0, $exitCode);
        self::assertStringContainsString($expectedOutput, $out->fetch());
    }

    public static function dp(): Generator
    {
        yield 'unexpected options' => [
            ['--spec-version' => '1.foo'],
            'Invalid value for option "spec-version"',
        ];
        yield 'no composer-file' => [
            ['composer-file' => self::DEMO_ROOT.'/does-not-exist/project/composer.json'],
            'could not find the config file: '.self::DEMO_ROOT.'/does-not-exist/project/composer.json',
        ];
        yield 'not possible target-file' => [
            [
                '--output-file' => self::DEMO_ROOT.'/does-not-exist/result/bom',
                'composer-file' => self::DEMO_ROOT.'/laravel-7.12.0/project/composer.json',
            ],
            'failed to open output: '.self::DEMO_ROOT.'/does-not-exist/result/bom',
        ];
    }
}
