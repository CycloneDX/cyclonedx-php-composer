<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Xml11;
use CycloneDX\Models\Bom;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\BomFile\Xml11
 */
class Xml11SerializeTest extends TestCase
{
    /** @var Xml11 */
    private $serializer;

    /** @var string */
    private $schema;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Xml11();

        $this->schema = __DIR__.'/../../res/bom-1.1.xsd';
    }

    /**
     * @throws \Swaggest\JsonSchema\InvalidValue
     *
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::all()
     */
    public function testSchema(Bom $bom): void
    {
        $xml = @$this->toXml($bom);
        $doc = $this->fromXml($xml);
        self::assertInstanceOf(DOMDocument::class, $doc);

        libxml_use_internal_errors(false); // send errors to PHPUnit
        self::assertTrue(
            $doc->schemaValidate($this->schema), // warns on schema mismatch. might be handled by PHPUnit as error.
            $xml
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::bomWithComponentPlain()
     */
    public function testBomHasComponent(Bom $bom): void
    {
        $doc = $this->fromXml($this->toXml($bom));
        self::assertInstanceOf(DOMDocument::class, $doc);
        $docElement = $doc->documentElement;
        self::assertInstanceOf(\DOMElement::class, $docElement);
        $componentElements = $docElement->getElementsByTagName('components');
        self::assertGreaterThan(0, $componentElements->length);
    }

    // @TODO ake tests

    private function toXml(Bom $bom): string
    {
        return $this->serializer->serialize($bom, false);
    }

    private function fromXml(string $xml): ?DOMDocument
    {
        $doc = new DOMDocument();
        $options = LIBXML_NOBLANKS | LIBXML_NOCDATA | LIBXML_NONET;
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
