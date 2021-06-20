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

namespace CycloneDX\Tests\functional\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Serialize\XmlDeserializer;
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Spec\Spec11;
use CycloneDX\Spec\Spec12;
use CycloneDX\Spec\Spec13;
use CycloneDX\Tests\_data\SpdxLicenseValidatorSingleton;
use DOMDocument;
use DOMException;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @TODO write the deserializer and enable this test
 */
class XmlTestTODO extends TestCase
{
    // region Spec 1.0
    // Spec 1.0 is not implemented
    // endregion Spec 1.0

    // region Spec 1.1

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentAllHashAlgorithms
     */
    public function testSchema11(Bom $bom): void
    {
        $spec = new Spec11();
        $schema = realpath(__DIR__.'/../../_spec/bom-1.1.SNAPSHOT.xsd');

        self::assertIsString($schema);
        self::assertFileExists($schema);

        $serializer = new XmlSerializer($spec);

        $xml = $serializer->serialize($bom);
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
        $deserializer = new XmlDeserializer($spec, SpdxLicenseValidatorSingleton::getInstance());

        $serialized = $serializer->serialize($bom);
        $deserialized = @$deserializer->deserialize($serialized);

        self::assertEquals($bom, $deserialized);
    }

    // endregion Spec 1.1

    // region Spec 1.2

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentAllHashAlgorithms
     */
    public function testSchema12(Bom $bom): void
    {
        $spec = new Spec12();
        $schema = realpath(__DIR__.'/../../_spec/bom-1.2.SNAPSHOT.xsd');

        self::assertIsString($schema);
        self::assertFileExists($schema);

        $serializer = new XmlSerializer($spec);

        $xml = $serializer->serialize($bom);
        $doc = $this->loadDomFromXml($xml); // throws on error

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentHashAlgorithmsSpec12()
     */
    public function testSerializer12(Bom $bom): void
    {
        $spec = new Spec12();
        $serializer = new XmlSerializer($spec);
        $deserializer = new XmlDeserializer($spec, SpdxLicenseValidatorSingleton::getInstance());

        $serialized = $serializer->serialize($bom);
        $deserialized = @$deserializer->deserialize($serialized);

        self::assertEquals($bom, $deserialized);
    }

    // endregion Spec 1.2

    // region Spec 1.3

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentAllHashAlgorithms
     */
    public function testSchema13(Bom $bom): void
    {
        $spec = new Spec13();
        $schema = realpath(__DIR__.'/../../_spec/bom-1.3.SNAPSHOT.xsd');

        self::assertIsString($schema);
        self::assertFileExists($schema);

        $serializer = new XmlSerializer($spec);

        $xml = $serializer->serialize($bom);
        $doc = $this->loadDomFromXml($xml); // throws on error

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentHashAlgorithmsSpec13()
     */
    public function testSerializer13(Bom $bom): void
    {
        $spec = new Spec13();
        $serializer = new XmlSerializer($spec);
        $deserializer = new XmlDeserializer($spec, SpdxLicenseValidatorSingleton::getInstance());

        $serialized = $serializer->serialize($bom);
        $deserialized = @$deserializer->deserialize($serialized);

        self::assertEquals($bom, $deserialized);
    }

    // endregion Spec 1.3

    // region helpers

    private function loadDomFromXml(string $xml): DOMDocument
    {
        $doc = new DOMDocument();
        $options = \LIBXML_NONET;
        if (\defined('LIBXML_COMPACT')) {
            $options |= \LIBXML_COMPACT;
        }
        if (\defined('LIBXML_PARSEHUGE')) {
            $options |= \LIBXML_PARSEHUGE;
        }
        $loaded = $doc->loadXML($xml, $options);
        if (false === $loaded) {
            throw new DOMException('loading failed');
        }

        return $doc;
    }

    // endregion helpers
}
