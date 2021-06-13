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

use CycloneDX\Composer\Plugin\CommandProvider;
use CycloneDX\Composer\Plugin\MakeBomCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Plugin\CommandProvider
 */
class CommandProviderTest extends TestCase
{
    /**
     * @uses \CycloneDX\Composer\Plugin\MakeBomCommand
     * @uses \CycloneDX\Composer\Plugin\MakeBomCommandOptions
     */
    public function testBomCommandIsRegistered(): void
    {
        $commandProvider = new CommandProvider();
        $commands = $commandProvider->getCommands();
        $bomCommands = array_filter($commands, static function ($command) { return $command instanceof MakeBomCommand; });
        self::assertCount(1, $bomCommands, 'MakeBomCommand not found exactly once');
    }
}
