<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Json;
use CycloneDX\Models\Bom;
use CycloneDX\Specs\Spec11;
use CycloneDX\Specs\Spec12;
use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Swaggest\JsonSchema;

/**
 * @coversNothing
 */
class JsonTest extends TestCase
{
    public function testSerialization10(): void
    {
        $file = new Json(new Spec11());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');
        @$file->serialize(new Bom());
    }

    public function testSerialization11(): void
    {
        $file = new Json(new Spec11());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');
        @$file->serialize(new Bom());
    }

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::fullBomTestData()
     *
     * @throws JsonException
     * @throws JsonSchema\InvalidValue
     */
    public function testSchema12(Bom $bom): void
    {
        $file = new Json(new Spec12());

        $schema = realpath(__DIR__.'/../../res/bom-1.2.schema.json');
        self::assertIsString($schema);
        self::assertFileExists($schema);

        $json = @$file->serialize($bom);
        self::assertJson($json);
        $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $schema = JsonSchema\Schema::import('file://'.$schema);
        self::assertInstanceOf(
            JsonSchema\Structure\ObjectItem::class,
            $schema->in($data) // throws on schema mismatch
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::fullBomTestData()
     */
    public function testSerialization12(Bom $bom): void
    {
        $file = new Json(new Spec12());
        $serialized = @$file->serialize($bom);
        $deserialized = @$file->deserialize($serialized);
        self::assertEquals($bom, $deserialized);
    }
}
