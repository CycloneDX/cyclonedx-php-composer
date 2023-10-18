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

use Composer\Console\Application;
use CycloneDX\Composer\MakeBom\Command;
use PHPUnit\Framework\TestCase;

abstract class CommandTestCase extends TestCase
{
    protected const DEMO_ROOT = __DIR__.'/../../demo';

    protected static function make_app(Command $command): Application
    {
        $app = new Application();
        $app->add($command);
        $app->setAutoExit(false);

        return $app;
    }
}
