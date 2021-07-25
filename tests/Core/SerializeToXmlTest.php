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

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialize\XmlSerializer;
use CycloneDX\Core\Spec\Spec11;
use CycloneDX\Core\Spec\Spec12;
use CycloneDX\Core\Spec\Spec13;
use CycloneDX\Core\Validation\Validators\XmlValidator;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SerializeToXmlTest extends TestCase
{
    // region Spec 1.0
    // Spec 1.0 is not implemented
    // endregion Spec 1.0

    // region Spec 1.1

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::allBomTestData
     */
    public function testSchema11(Bom $bom): void
    {
        $spec = new Spec11();
        $serializer = new XmlSerializer($spec);
        $validator = new XmlValidator($spec);

        $xml = $serializer->serialize($bom);
        $validationErrors = $validator->validateString($xml);

        self::assertNull($validationErrors);
    }

    // endregion Spec 1.1

    // region Spec 1.2

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::allBomTestData
     */
    public function testSchema12(Bom $bom): void
    {
        $spec = new Spec12();
        $serializer = new XmlSerializer($spec);
        $validator = new XmlValidator($spec);

        $xml = $serializer->serialize($bom);
        $validationErrors = $validator->validateString($xml);

        self::assertNull($validationErrors);
    }

    // endregion Spec 1.2

    // region Spec 1.3

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::allBomTestData
     */
    public function testSchema13(Bom $bom): void
    {
        $spec = new Spec13();
        $serializer = new XmlSerializer($spec);
        $validator = new XmlValidator($spec);

        $xml = $serializer->serialize($bom);
        $validationErrors = $validator->validateString($xml);

        self::assertNull($validationErrors);
    }

    // endregion Spec 1.3
}
