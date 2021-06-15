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

namespace CycloneDX\Tests\unit\Helpers;

use CycloneDX\Helpers\HasSpecTrait;
use CycloneDX\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Helpers\HasSpecTrait
 */
class HasSpecTraitTest extends TestCase
{
    public function testGetSetSpec(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = $this->getMockForTrait(HasSpecTrait::class);

        $serializer->setSpec($spec);
        $got = $serializer->getSpec();

        self::assertSame($spec, $got);
    }
}
