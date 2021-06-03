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

namespace CycloneDX\Tests\unit\Specs;

use CycloneDX\Specs\Spec10;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Specs\Spec10
 */
class Spec10Test extends TestCase
{
    private $spec;

    public function setUp(): void
    {
        $this->spec = new Spec10();
    }

    public function testVersion(): void
    {
        $version = $this->spec->getVersion();
        self::assertSame('1.0', $version);
    }
}
