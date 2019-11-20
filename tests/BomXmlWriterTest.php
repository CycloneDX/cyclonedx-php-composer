<?php

use CycloneDX\BomXmlWriter;
use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;
use CycloneDX\Model\License;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected function setUp() 
    {
        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomXmlWriter = new BomXmlWriter($this->outputMock);
    }

    public function testBomXmlWriter() 
    {
        $component = new Component;
        $component->setGroup("componentGroup");
        $component->setName("componentName");
        $component->setDescription("componentDescription");
        $component->setVersion("1.0");
        $component->setType("library");
        $component->setLicenses(array(new License("MIT"), new License("Apache-2.0")));
        $component->setHashes(array("SHA-1" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"));
        $bom = new Bom(array($component));

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument("1.0", "UTF-8");
        $domDocument->loadXML($bomXml);
        $this->assertTrue($domDocument->schemaValidate(__DIR__ . "/schema/bom-1.1.xsd"));
    }

}