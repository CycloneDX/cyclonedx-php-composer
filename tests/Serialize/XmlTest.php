<?php

namespace CycloneDX\Tests\Serialize;

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
    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @large
     *
     * @dataProvider \CycloneDX\Tests\Serialize\AbstractDataProvider::fullBomTestData()
     */
    public function testSchema11(Bom $bom): void
    {
        $spec = new Spec11();
        $schema = realpath(__DIR__.'/../../res/bom-1.1.xsd');

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
     * @dataProvider \CycloneDX\Tests\Serialize\AbstractDataProvider::fullBomTestData()
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
}
