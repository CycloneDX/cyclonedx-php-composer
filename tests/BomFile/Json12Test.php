<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\BomFile\Json12;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;

/**
 * @covers \CycloneDX\BomFile\Json12
 */
class Json12Test extends TestCase
{
    /** @var Json12 */
    private $serializer;

    /** @var \Swaggest\JsonSchema\SchemaContract */
    private $schema;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Json12(false);

        if (null === $this->schema) {
            $schema_file = __DIR__.'/../schema/bom-1.2.schema-SNAPSHOT.json';
            $this->schema = Schema::import('file://'.realpath($schema_file));
        }
    }

    /**
     * @throws \JsonException
     * @throws \Swaggest\JsonSchema\InvalidValue
     *
     * @dataProvider bomEmptyDataProvider
     * @dataProvider bomWithComponentLicenseDataProvider
     * @dataProvider bomWithComponentVersionDataProvider
     */
    public function testSchema(Bom $bom): void
    {
        $json = $this->json_encode($bom);
        self::assertJson($json);
        $this->schema->in($this->json_decode($json)); // throws on schema mismatch
    }

    /**
     * @throws \JsonException
     * @dataProvider bomWithComponentLicenseDataProvider
     */
    public function testComponentsHaveLicenses(Bom $bom): void
    {
        $data = $this->json_decode($this->json_encode($bom));
        foreach ($data->components as $component) {
            self::assertIsArray($component->licenses);
            self::assertNotEmpty($component->licenses);
        }
    }

    /**
     * @return \Generator<array{0: Bom}>
     */
    public function bomEmptyDataProvider(): \Generator
    {
        yield 'plain' => [new Bom()];
    }

    /**
     * @return \Generator<array{0: Bom}>
     */
    public function bomWithComponentVersionDataProvider(): \Generator
    {
        $versions = ['1.0', 'dev-master'];
        foreach ($versions as $version) {
            yield "version: {$version}" => [(new Bom())->setComponents([
                new Component(Component::TYPE_LIBRARY, 'name', $version),
            ])];
        }
    }

    /**
     * @return \Generator<array{0: Bom}>
     */
    public function bomWithComponentLicenseDataProvider(): \Generator
    {
        $licenses = ['MIT', 'some text'];
        foreach ($licenses as $license) {
            yield "license: ${license}" => [(new Bom())->setComponents([
                (new Component(Component::TYPE_LIBRARY, 'name', 'version'))
                    ->setLicenses([new License($license)]),
            ])];
        }
    }

    /**
     * @throws \JsonException
     */
    private function json_encode(Bom $bom): string
    {
        return $this->serializer->serialize($bom);
    }

    /**
     * @throws \JsonException
     *
     * @return mixed
     */
    private function json_decode(string $json, bool $associative = false)
    {
        return json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
    }
}
