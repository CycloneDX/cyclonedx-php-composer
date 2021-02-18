<?php

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

namespace CycloneDX\Tests\functional\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Serialize\XmlDeserializer;
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Specs\Spec11;
use DOMDocument;
use DOMException;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class XmlTest extends TestCase
{
    // @TODO add Spec 10 tests

    // region Spec11

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @large
     * @group online
     * @group slow
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentAllHashAlgorithms
     */
    public function testSchema11(Bom $bom): void
    {
        $spec = new Spec11();
        $schema = realpath(__DIR__.'/../../../res/bom-1.1.xsd');

        self::assertIsString($schema);
        self::assertFileExists($schema);

        $serializer = new XmlSerializer($spec);

        $xml = @$serializer->serialize($bom);
        $doc = $this->loadDomFromXml($xml); // throws on error

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentHashAlgorithmsSpec11()
     */
    public function testSerializer11(Bom $bom): void
    {
        $spec = new Spec11();
        $serializer = new XmlSerializer($spec);
        $deserializer = new XmlDeserializer($spec);

        $serialized = @$serializer->serialize($bom);
        $deserialized = @$deserializer->deserialize($serialized);

        self::assertEquals($bom, $deserialized);
    }

    // endregion Spec11

    // @TODO add Spec 12 tests

    // region helpers

    /**
     * @throws DOMException
     */
    private function loadDomFromXml(string $xml): DOMDocument
    {
        $doc = new DOMDocument();
        $options = LIBXML_NONET;
        if (defined('LIBXML_COMPACT')) {
            $options |= LIBXML_COMPACT;
        }
        if (defined('LIBXML_PARSEHUGE')) {
            $options |= LIBXML_PARSEHUGE;
        }
        $loaded = $doc->loadXML($xml, $options);
        if (false === $loaded) {
            throw new DOMException('loading failed');
        }

        return $doc;
    }

    // endregion helpers
}
