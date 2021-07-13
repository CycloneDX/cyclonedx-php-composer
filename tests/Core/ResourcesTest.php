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

namespace CycloneDX\Tests\Core;

use CycloneDX\Core\Resources;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Resources
 */
class ResourcesTest extends TestCase
{
    public function testRootDirIsReadable(): void
    {
        $root = Resources::ROOT;
        self::assertDirectoryExists($root);
        self::assertDirectoryIsReadable($root);
    }

    /**
     * @dataProvider dpFiles
     */
    public function testFileIsReadable(string $filePath): void
    {
        self::assertFileExists($filePath);
        self::assertFileIsReadable($filePath);
    }

    public function dpFiles(): \Generator
    {
        $constants = (new \ReflectionClass(Resources::class))->getConstants();
        foreach ($constants as $name => $value) {
            if (0 === strpos($name, 'FILE')) {
                yield $name => [$value];
            }
        }
    }
}
