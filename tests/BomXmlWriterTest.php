<?php

/** @noinspection PhpUnhandledExceptionInspection */


use CycloneDX\BomXmlWriter;
use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversNothing
 */
class BomXmlWriterTest extends TestCase
{
    /**
     * @var OutputInterface
     */
    private $outputMock;

    /**
     * @var BomXmlWriter
     */
    private $bomXmlWriter;

    protected function setUp() : void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomXmlWriter = new BomXmlWriter($this->outputMock);
    }

    public function testBomXmlWriter(): void
    {
        $component = new Component;
        $component->setGroup("componentGroup");
        $component->setName("componentName");
        $component->setDescription("componentDescription");
        $component->setVersion("1.0");
        $component->setType("library");
        $component->setLicenses(array("MIT", "Apache-2.0"));
        $component->setHashes(array("SHA-1" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"));
        $bom = new Bom(array($component));

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument("1.0", "UTF-8");
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__ . "/schema/bom-1.1.xsd"));
    }

    /**
     * license is unknown to https://cyclonedx.org/schema/spdx
     * but BOM still valid output
     */
    public function testUnknownSpdxLicense(): void
    {
        $component = new Component;
        $component->setGroup("componentGroup");
        $component->setName("componentName");
        $component->setDescription("componentDescription");
        $component->setVersion("1.0");
        $component->setType("library");
        $component->setLicenses(array("proprietary"));
        $component->setHashes(array("SHA-1" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"));
        $bom = new Bom(array($component));

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument("1.0", "UTF-8");
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__ . "/schema/bom-1.1.xsd"));
    }

    /**
     * license is unknown to https://cyclonedx.org/schema/spdx
     * but BOM still valid output
     */
    public function testCaseMismatchSpdxLicense(): void
    {
        $mismatch = array("mit", "aPACHE-2.0");
        $expected = array("MIT", "Apache-2.0");

        $component = new Component;
        $component->setGroup("componentGroup");
        $component->setName("componentName");
        $component->setDescription("componentDescription");
        $component->setVersion("1.0");
        $component->setType("library");
        $component->setLicenses($mismatch);
        $component->setHashes(array("SHA-1" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"));
        $bom = new Bom(array($component));

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument("1.0", "UTF-8");
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__ . "/schema/bom-1.1.xsd"));

        $domDocumentLicenses = array();
        foreach ($domDocument->getElementsByTagName('license') as $domDocumentLicense) {
            $domDocumentLicenses[] = $domDocumentLicense->getElementsByTagName('id')[0]->nodeValue;
        }
        self::assertSame($expected, $domDocumentLicenses);
    }

}
