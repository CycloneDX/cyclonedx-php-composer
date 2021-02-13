<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Json;
use CycloneDX\Models\Bom;
use CycloneDX\Specs\Spec12;
use CycloneDX\Specs\SpecInterface;
use JsonException;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;

/**
 * @covers \CycloneDX\BomFile\Json
 *
 * @uses \CycloneDX\Models\Bom
 * @uses \CycloneDX\Models\Component
 * @uses \CycloneDX\Models\License
 */
class JsonSerializeTest extends TestCase
{
    /** @var Json */
    private $serializer;

    /** @var \Swaggest\JsonSchema\SchemaContract */
    private $schema;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Json(new Spec12());

        $schema_file = __DIR__.'/../../res/bom-1.2.schema.json';
        $this->schema = Schema::import('file://'.realpath($schema_file));
    }

    /**
     * @throws JsonException
     * @throws \Swaggest\JsonSchema\InvalidValue
     *
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::all()
     *
     * @coversNothing
     */
    public function testSchema(Bom $bom): void
    {
        $json = @$this->json_encode($bom);
        self::assertJson($json);
        self::assertInstanceOf(
            \Swaggest\JsonSchema\Structure\ObjectItem::class,
            $this->schema->in($this->json_decode($json)), // throws on schema mismatch
            $json
        );
    }

    /**
     * @throws JsonException
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::bomWithComponentPlain()
     */
    public function testBomHasComponent(Bom $bom): void
    {
        $data = $this->json_decode($this->json_encode($bom));
        self::assertIsArray($data->components);
        self::assertNotEmpty($data->components);
    }

    /**
     * @throws JsonException
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::bomWithComponentLicenseId()
     */
    public function testComponentsHaveLicenseId(Bom $bom): void
    {
        $data = $this->json_decode($this->json_encode($bom));
        foreach ($data->components as $component) {
            foreach ($component->licenses as $license) {
                self::assertObjectHasAttribute('id', $license->license);
                self::assertObjectNotHasAttribute('name', $license->license);
            }
        }
    }

    /**
     * @throws JsonException
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::bomWithComponentLicenseName()
     */
    public function testComponentsHaveLicenseName(Bom $bom): void
    {
        $data = $this->json_decode($this->json_encode($bom));
        foreach ($data->components as $component) {
            foreach ($component->licenses as $license) {
                self::assertObjectHasAttribute('name', $license->license);
                self::assertObjectNotHasAttribute('id', $license->license);
            }
        }
    }

    /**
     * @throws JsonException
     */
    private function json_encode(Bom $bom): string
    {
        return $this->serializer->serialize($bom, false);
    }

    /**
     * @throws JsonException
     *
     * @return mixed|array|object
     */
    private function json_decode(string $json, bool $associative = false)
    {
        return json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
    }
}
