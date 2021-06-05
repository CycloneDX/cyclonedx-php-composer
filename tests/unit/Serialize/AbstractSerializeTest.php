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

namespace CycloneDX\Tests\unit\Serialize;

use CycloneDX\Serialize\AbstractSerialize;
use CycloneDX\Specs\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\AbstractSerialize
 */
class AbstractSerializeTest extends TestCase
{
    public function testGetSetSpec(): void
    {
        $spec1 = $this->createMock(SpecInterface::class);
        $spec2 = $this->createMock(SpecInterface::class);

        $serializer = $this->getMockForAbstractClass(AbstractSerialize::class, [$spec1]);
        $serializer->setSpec($spec2);
        $got = $serializer->getSpec();

        self::assertSame($spec2, $got);
    }
}