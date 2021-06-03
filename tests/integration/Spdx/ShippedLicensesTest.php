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

namespace CycloneDX\Tests\integration\Spdx;

use CycloneDX\Spdx\License;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ShippedLicensesTest extends TestCase
{
    /**
     * @psalm-var string
     */
    private $file;

    /**
     * @retrun void
     */
    protected function setUp(): void
    {
        $this->file = License::getResourcesFile();
    }

    public function test(): void
    {
        self::assertFileExists($this->file);

        $json = file_get_contents($this->file);
        self::assertIsString($json);
        self::assertJson($json);

        $licenses = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($licenses);
        self::assertNotEmpty($licenses);

        foreach ($licenses as $license) {
            self::assertIsString($license);
        }
    }
}
