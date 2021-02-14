<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Xml;
use CycloneDX\Models\Bom;
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
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::fullBomTestData()
     */
    public function testSchema11(Bom $bom): void
    {
        $file = new Xml(new Spec11());

        $schema = realpath(__DIR__.'/../../res/bom-1.1.xsd');
        self::assertIsString($schema);
        self::assertFileExists($schema);

        $xml = @$file->serialize($bom);
        $doc = $this->loadDomFromXml($xml); // throws on error

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::fullBomTestData()
     */
    public function testSerializer11(Bom $bom): void
    {
        $file = new Xml(new Spec11());
        $serialized = @$file->serialize($bom);
        $deserialized = @$file->deserialize($serialized);
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
