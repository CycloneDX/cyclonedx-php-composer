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

namespace CycloneDX\Tests\unit\Core\Enums;

use CycloneDX\Core\Enums\Classification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Enums\Classification
 */
class ClassificationTest extends TestCase
{
    /**
     * @dataProvider dpKnownValues
     * @dataProvider dpUnknownValue
     */
    public function testIsValidValue(string $value, bool $expected): void
    {
        self::assertSame($expected, Classification::isValidValue($value));
    }

    public function dpKnownValues(): \Generator
    {
        $allValues = (new \ReflectionClass(Classification::class))->getConstants();
        foreach ($allValues as $value) {
            yield $value => [$value, true];
        }
    }

    public function dpUnknownValue(): \Generator
    {
        yield 'invalid' => ['UnknownClassification', false];
    }
}
