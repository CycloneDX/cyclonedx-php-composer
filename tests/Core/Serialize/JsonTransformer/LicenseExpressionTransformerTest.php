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

namespace CycloneDX\Tests\Core\Serialize\JsonTransformer;

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Serialize\JsonTransformer\Factory;
use CycloneDX\Core\Serialize\JsonTransformer\LicenseExpressionTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\LicenseExpressionTransformer
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class LicenseExpressionTransformerTest extends TestCase
{
    public function testConstructor(): LicenseExpressionTransformer
    {
        $factory = $this->createStub(Factory::class);

        $transformer = new LicenseExpressionTransformer($factory);

        self::assertSame($factory, $transformer->getFactory());

        return $transformer;
    }

    /**
     * @depends testConstructor
     */
    public function testTransform(LicenseExpressionTransformer $transformer): void
    {
        $license = $this->createMock(LicenseExpression::class);
        $license->method('getExpression')->willReturn('foo');

        $transformed = $transformer->transform($license);

        self::assertSame(['expression' => 'foo'], $transformed);
    }
}
