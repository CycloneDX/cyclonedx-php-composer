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

namespace CycloneDX\Tests\functional\Enums;

use CycloneDX\Enums\Classification;
use CycloneDX\Tests\_data\BomSpecData;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ClassificationTest extends TestCase
{
    /**
     * @dataProvider dpSchemaValues
     */
    public function testIsValidValue(string $value): void
    {
        self::assertTrue(Classification::isValidValue($value));
    }

    public function dpSchemaValues(): \Generator
    {
        $allValues = array_unique(array_merge(
            BomSpecData::getClassificationEnumForVersion('1.0'),
            BomSpecData::getClassificationEnumForVersion('1.1'),
            BomSpecData::getClassificationEnumForVersion('1.2'),
            BomSpecData::getClassificationEnumForVersion('1.3'),
        ));
        foreach ($allValues as $value) {
            yield $value => [$value];
        }
    }
}
