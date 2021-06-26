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

namespace CycloneDX\Tests\unit\Core\Models;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Repositories\ComponentRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class BomTest.
 *
 * @covers \CycloneDX\Core\Models\Bom
 */
class BomTest extends TestCase
{
    /** @psalm-var Bom */
    private $bom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bom = new Bom($this->createStub(ComponentRepository::class));
    }

    // region components setter&getter&modifiers

    public function testComponentsSetterGetter(): void
    {
        $components = $this->createStub(ComponentRepository::class);
        $this->bom->setComponentRepository($components);
        self::assertSame($components, $this->bom->getComponentRepository());
    }

    // endregion components setter&getter&modifiers

    // region version setter&getter

    public function testVersionSetterGetter(): void
    {
        $version = random_int(1, 255);
        $this->bom->setVersion($version);
        self::assertSame($version, $this->bom->getVersion());
    }

    public function testVersionSetterInvalidValue(): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(\DomainException::class);
        $this->bom->setVersion($version);
    }

    // endregion version setter&getter
}
