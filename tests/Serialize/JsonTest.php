<?php

namespace CycloneDX\Tests\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Serialize\JsonDeserializer;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Specs\Spec10;
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
    /**
     * Schema 1.0 is not specified for JSON.
     */
    public function testSerialization10(): void
    {
        $spec = new Spec10();
        $serializer = new JsonSerializer($spec);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');

        @$serializer->serialize(new Bom());
    }

    /**
     * Schema 1.1 is not specified for JSON.
     */
    public function testSerialization11(): void
    {
        $spec = new Spec11();
        $serializer = new JsonSerializer($spec);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');

        @$serializer->serialize(new Bom());
    }

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @large
     *
     * @dataProvider \CycloneDX\Tests\Serialize\AbstractDataProvider::fullBomTestData()
     *
     * @throws JsonException
     * @throws JsonSchema\Exception
     * @throws JsonSchema\InvalidValue
     */
    public function testSchema12(Bom $bom): void
    {
        $spec = new Spec12();
        $schema = realpath(__DIR__.'/../../res/bom-1.2.schema.json');

        self::assertIsString($schema);
        self::assertFileExists($schema);

        $serializer = new JsonSerializer($spec);

        $json = @$serializer->serialize($bom);
        self::assertJson($json);
        $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $schema = JsonSchema\Schema::import('file://'.$schema);
        self::assertInstanceOf(
            JsonSchema\Structure\ObjectItem::class,
            $schema->in($data) // throws on schema mismatch
        );
    }

    /**
     * @dataProvider \CycloneDX\Tests\Serialize\AbstractDataProvider::fullBomTestData()
     */
    public function testSerialization12(Bom $bom): void
    {
        $spec = new Spec12();
        $serializer = new JsonSerializer($spec);
        $deserializer = new JsonDeserializer($spec);

        $serialized = @$serializer->serialize($bom);
        $deserialized = @$deserializer->deserialize($serialized);

        self::assertEquals($bom, $deserialized);
    }
}
