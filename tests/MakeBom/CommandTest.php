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

use Composer\IO\NullIO;
use CycloneDX\Composer\Builders\BomBuilder;
use CycloneDX\Composer\MakeBom\Command;
use CycloneDX\Composer\MakeBom\Exceptions\ValueError;
use CycloneDX\Composer\MakeBom\Factory;
use CycloneDX\Composer\MakeBom\Options;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \CycloneDX\Composer\MakeBom\Command
 *
 * @uses   \CycloneDX\Composer\MakeBom\Options::configureCommand
 */
class CommandTest extends TestCase
{
    /**
     * @var Options|\PHPUnit\Framework\MockObject\MockObject
     *
     * @psalm-var Options&\PHPUnit\Framework\MockObject\MockObject
     */
    private $options;
    /**
     * @var Factory|\PHPUnit\Framework\MockObject\MockObject
     *
     * @psalm-var Factory&\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;
    /**
     * @var Command
     */
    private $command;

    /**
     * @var BomBuilder|\PHPUnit\Framework\MockObject\MockObject
     *
     * @psalm-var \CycloneDX\Composer\Builders\BomBuilder&\PHPUnit\Framework\MockObject\MockObject
     */
    private $bomFactory;

    protected function setUp(): void
    {
        $this->options = $this->createTestProxy(Options::class);
        $this->factory = $this->createMock(Factory::class);
        $this->bomFactory = $this->createMock(BomBuilder::class);
        $this->command = new Command($this->options, $this->factory, $this->bomFactory, null, 'test-dummy');
        $this->command->setIO(new NullIO());
    }

    public function testConfigureUsesOptions(): void
    {
        $this->options->expects(self::once())
            ->method('configureCommand')
            ->with(new IsInstanceOf(Command::class));

        new Command($this->options, $this->factory, $this->bomFactory, null, 'test-dummy');
    }

    public function testRunFailsWhenOptionsInvalid(): void
    {
        $input = new ArrayInput(
            ['--output-file' => '-'],
            $this->command->getDefinition()
        );
        $input->setInteractive(false);
        $output = new BufferedOutput();

        $this->options->expects(self::once())
            ->method('setFromInput')
            ->with($input)
            ->willThrowException(new ValueError('foo bar'));

        $status = $this->command->run($input, $output);
        $written = $output->fetch();

        self::assertSame(2, $status);
        self::assertSame('', $written);
    }
}
