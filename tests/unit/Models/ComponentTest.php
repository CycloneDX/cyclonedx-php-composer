<?php

declare(strict_types=1);

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

namespace CycloneDX\Tests\uni\Models;

use CycloneDX\Enums\Classification;
use CycloneDX\Models\Component;
use CycloneDX\Models\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Models\Component
 */
class ComponentTest extends TestCase
{
    /** @psalm-var Component */
    private $component;

    public function setUp(): void
    {
        parent::setUp();

        $this->component = new Component(Classification::LIBRARY, 'name', 'version');
    }

    // region type getter&setter

    public function testSetTypeWithUnknownValue(): void
    {
        $this->expectException(\DomainException::class);
        $this->component->setType('something unknown');
    }

    // endregion type getter&setter

    // region purl setter&getter

    public function testPackageUrlSetterGetter(): void
    {
        $url = $this->createMock(PackageUrl::class);
        $this->component->setPackageUrl($url);
        self::assertEquals($url, $this->component->getPackageUrl());
    }

    // endregion purl setter&getter
}
