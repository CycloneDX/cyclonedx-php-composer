<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Xml;
use CycloneDX\Models\Bom;
use CycloneDX\Specs\Spec11;
use JsonException;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema;
use DOMDocument;

/**
 * @coversNothing
 */
class XmlTest extends TestCase
{
    /**
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::all()
     *
     * @throws JsonException
     * @throws JsonSchema\InvalidValue
     */
    public function testSchema11(Bom $bom): void
    {
        $file = new Xml(new Spec11());

        $schema = realpath(__DIR__.'/../../res/bom-1.1.xsd');
        self::assertFileExists($schema);

        $xml = @$file->serialize($bom);

        $doc = $this->loadDomFromXml($xml);
        self::assertInstanceOf(DOMDocument::class, $doc);

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );

        self::assertEquals($bom, $file->deserialize($xml));
    }


    private function loadDomFromXml(string $xml): ?DOMDocument
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
            return null;
        }

        return $doc;
    }

}
