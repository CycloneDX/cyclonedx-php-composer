<?php

namespace CycloneDX\Tests\Serialize;

use CycloneDX\Models\License;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Specs\SpecInterface;
use DomainException;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\JsonSerializer
 */
class SerializeTest extends TestCase
{
    // region hashToJson

    public function testHashToJson(): void
    {
        $hash = ['alg' => random_bytes(8), 'content' => random_bytes(32)];

        $serializer = $this->getSerializer();
        /** @var \PHPUnit\Framework\MockObject\MockObject $spec */
        $spec = $serializer->getSpec();
        $spec->method('isSupportedHashAlgorithm')->willReturn(true);
        $spec->method('isSupportedHashContent')->willReturn(true);

        $data = $serializer->hashToJson($hash['alg'], $hash['content']);
        self::assertIsArray($data);
        self::assertEquals($hash, $data);
    }

    public function testHashToJsonInvalidAlgorithm(): void
    {
        $algorithm = random_bytes(32);

        $serializer = $this->getSerializer();
        /** @var \PHPUnit\Framework\MockObject\MockObject $spec */
        $spec = $serializer->getSpec();
        $spec->method('isSupportedHashAlgorithm')->with($algorithm)->willReturn(false);
        $spec->method('isSupportedHashContent')->willReturn(true);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid algorithm/i');
        $serializer->hashToJson($algorithm, random_bytes(32));
    }

    public function testHashToJsonInvalidContent(): void
    {
        $content = random_bytes(32);

        $serializer = $this->getSerializer();
        /** @var \PHPUnit\Framework\MockObject\MockObject $spec */
        $spec = $serializer->getSpec();
        $spec->method('isSupportedHashAlgorithm')->willReturn(true);
        $spec->method('isSupportedHashContent')->with($content)->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid content/i');
        $serializer->hashToJson(random_bytes(32), $content);
    }

    // endregion hashToJson

    // region licenseToJson

    /**
     * @dataProvider licenseDataProvider
     *
     * @param mixed $expected
     */
    public function testLicenseToJson(License $license, $expected): void
    {
        $serializer = $this->getSerializer();
        $data = $serializer->licenseToJson($license);
        self::assertEquals($expected, $data);
    }

    /**
     * @return Generator<string, array{0:License, 1:array}>
     */
    public function licenseDataProvider(): Generator
    {
        $name = random_bytes(32);
        $license = $this->createStub(License::class);
        $license->method('getName')->willReturn($name);
        $expected = ['name' => $name];
        yield 'withName' => [$license, $expected];

        $id = random_bytes(32);
        $license = $this->createStub(License::class);
        $license->method('getId')->willReturn($id);
        $expected = ['id' => $id];
        yield 'withId' => [$license, $expected];

        $name = random_bytes(32);
        $url = 'https://example.com/license/'.random_bytes(32);
        $license = $this->createStub(License::class);
        $license->method('getUrl')->willReturn($url);
        $license->method('getName')->willReturn($name);
        $expected = ['name' => $name, 'url' => $url];
        yield 'withUrl' => [$license, $expected];
    }

    // endregion licenseToJson

    // region hashesToJson

    public function testHashesToJson(): void
    {
        $serializer = $this->getSerializer(['hashToJson']);

        $algorithm = random_bytes(32);
        $content = random_bytes(32);
        $hashes = [
            $algorithm => $content,
        ];
        $hashToJsonFake = [$algorithm, $content];

        $expected = [$hashToJsonFake];
        $serializer->method('hashToJson')
            ->with($algorithm, $content)
            ->willReturn($hashToJsonFake);

        $serialized = iterator_to_array($serializer->hashesToJson($hashes));

        self::assertEquals($expected, $serialized);
    }

    // endregion

    // region helpers

    /**
     * @param string[] $methods
     *
     * @return JsonSerializer|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getSerializer(array $methods = [])
    {
        return $this->createPartialMock(JsonSerializer::class, $methods)
            ->setSpec($this->createStub(SpecInterface::class));
    }

    // endregion helpers
}
