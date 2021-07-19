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

namespace CycloneDX\Tests\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialize\XmlSerializer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\XmlSerializer
 */
class XmlSerializerTest extends TestCase
{
    /**
     * @covers \CycloneDX\Core\Serialize\XmlSerializer
     *
     * @uses   \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
     * @uses   \CycloneDX\Core\Serialize\DOM\NormalizerFactory
     * @uses   \CycloneDX\Core\Serialize\DOM\Normalizers\BomNormalizer
     * @uses   \CycloneDX\Core\Serialize\DOM\Normalizers\ComponentRepositoryNormalizer
     * @uses   \CycloneDX\Core\Serialize\DOM\Normalizers\ComponentNormalizer
     */
    public function testSerialize(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'isSupportedFormat' => true,
            ]
        );
        $serializer = new XmlSerializer($spec);
        $bom = $this->createStub(Bom::class);

        $actual = $serializer->serialize($bom);

        self::assertXmlStringEqualsXmlString(
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="0">
                  <components/>
                </bom>
                XML,
            $actual
        );
    }
}
