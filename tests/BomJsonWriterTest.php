<?php

/** @noinspection PhpUnhandledExceptionInspection */

use CycloneDX\BomJsonWriter;
use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Swaggest\JsonSchema\Schema;

/**
 * @coversNothing
 */
class BomJsonWriterTest extends TestCase
{
    /**
     * @var OutputInterface
     */
    private $outputMock;

    /**
     * @var BomJsonWriter
     */
    private $bomJsonWriter;

    protected function setUp() : void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomJsonWriter = new BomJsonWriter($this->outputMock);
    }

    public function testBomJsonWriter(): void
    {
        $schemaJson = file_get_contents(__DIR__ . "/schema/bom-1.2.schema-SNAPSHOT.json");
        $schema = Schema::import(json_decode($schemaJson, false));
        $component = new Component;
        $component->setGroup("componentGroup");
        $component->setName("componentName");
        $component->setDescription("componentDescription");
        $component->setVersion("1.0");
        $component->setType("library");
        $component->setLicenses(array("MIT", "Apache-2.0"));
        $component->setHashes(array("SHA-1" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"));
        $component->setPackageUrl("purl://packageurl");
        $bom = new Bom(array($component));


        $bomJson = $this->bomJsonWriter->writeBom($bom);

        // $schema->in() throws an exception if validation fails
        $schema->in(json_decode($bomJson, false));
        // this stops PHPUnit from flagging this as a risky test
        $this->expectNotToPerformAssertions();
    }

}
