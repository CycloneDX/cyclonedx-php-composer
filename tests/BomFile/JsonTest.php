<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Json;
use CycloneDX\Models\Bom;
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
    public function testSchema10(): void
    {
        $file = new Json(new Spec10());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');
        $file->serialize(new Bom());
    }

    public function testSchema11(): void
    {
        $file = new Json(new Spec11());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported spec version');
        $file->serialize(new Bom());
    }

    /**
     * @dataProvider \CycloneDX\Tests\BomFile\AbstractDataProvider::all()
     *
     * @throws JsonException
     * @throws JsonSchema\InvalidValue
     */
    public function testSchema12(Bom $bom): void
    {
        $file = new Json(new Spec12());

        $schema = realpath(__DIR__.'/../../res/bom-1.2.schema.json');
        self::assertFileExists($schema);


        $json = @$file->serialize($bom);
        self::assertJson($json);
        $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $schema = JsonSchema\Schema::import('file://'.$schema);
        self::assertInstanceOf(
            JsonSchema\Structure\ObjectItem::class,
            $schema->in($data) // throws on schema mismatch
        );

        self::assertEquals($bom, $file->deserialize($json));
    }
}
