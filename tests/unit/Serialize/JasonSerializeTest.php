<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\unit\Serialize;

use CycloneDX\Models\License;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Specs\SpecInterface;
use DomainException;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\JsonSerializer
 * @covers \CycloneDX\Serialize\AbstractSerialize
 */
class JasonSerializeTest extends TestCase
{


    // region hashToJson

    public function testHashToJson(): void
    {
        $hash = ['alg' => $this->getRandomString(), 'content' => $this->getRandomString()];

        $spec = $this->createMock(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->with($hash['alg'])->willReturn(true);
        $spec->method('isSupportedHashContent')->with($hash['content'])->willReturn(true);
        $serializer = new JsonSerializer($spec);

        $data = $serializer->hashToJson($hash['alg'], $hash['content']);

        self::assertEquals($hash, $data);
    }

    public function testHashToJsonInvalidAlgorithm(): void
    {
        $algorithm = $this->getRandomString();

        $spec = $this->createMock(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->with($algorithm)->willReturn(false);
        $spec->method('isSupportedHashContent')->willReturn(true);
        $serializer = new JsonSerializer($spec);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid algorithm/i');
        $serializer->hashToJson($algorithm, $this->getRandomString());
    }

    public function testHashToJsonInvalidContent(): void
    {
        $content = $this->getRandomString();

        $spec = $this->createMock(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->willReturn(true);
        $spec->method('isSupportedHashContent')->with($content)->willReturn(false);
        $serializer = new JsonSerializer($spec);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid content/i');
        $serializer->hashToJson($this->getRandomString(), $content);
    }

    // endregion hashToJson

    // region licenseToJson

    /**
     * @dataProvider licenseDataProvider
     *
     * @psalm-param mixed $expected
     */
    public function testLicenseToJson(License $license, $expected): void
    {
        $serializer = new JsonSerializer($this->createMock(SpecInterface::class));
        $data = $serializer->licenseToJson($license);
        self::assertEquals($expected, $data);
    }

    /**
     * @psalm-return Generator<string, array{0:License, 1:array}>
     */
    public function licenseDataProvider(): Generator
    {
        $name = $this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getName')->willReturn($name);
        $expected = ['name' => $name];
        yield 'withName' => [$license, $expected];

        $id = $this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getId')->willReturn($id);
        $expected = ['id' => $id];
        yield 'withId' => [$license, $expected];

        $name = $this->getRandomString();
        $url = 'https://example.com/license/'.$this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getUrl')->willReturn($url);
        $license->method('getName')->willReturn($name);
        $expected = ['name' => $name, 'url' => $url];
        yield 'withUrl' => [$license, $expected];
    }

    // endregion licenseToJson

    // region licensesToJson

    public function testLicensesToJson(): void
    {
        $serializer = $this->createPartialMock(JsonSerializer::class, ['licenseToJson']);

        $license = $this->createMock(License::class);
        $licenseFake = ['dummy' => $this->getRandomString()];
        $licenses = [$license];

        $serializer->expects(self::once())->method('licenseToJson')
            ->with($license)
            ->willReturn($licenseFake);

        $data = iterator_to_array($serializer->licensesToJson($licenses));

        self::assertSame([['license' => $licenseFake]], $data);
    }

    // endregion licensesToJson

    // region hashesToJson

    public function testHashesToJson(): void
    {
        $serializer = $this->createPartialMock(JsonSerializer::class, ['hashToJson']);

        $algorithm = $this->getRandomString();
        $content = $this->getRandomString();
        $hashes = [
            $algorithm => $content,
        ];
        $hashToJsonFake = [$algorithm, $content];
        $expected = [$hashToJsonFake];

        $serializer->expects(self::once())->method('hashToJson')
            ->with($algorithm, $content)
            ->willReturn($hashToJsonFake);

        $serialized = iterator_to_array($serializer->hashesToJson($hashes));

        self::assertEquals($expected, $serialized);
    }

    public function testHashesToJsonThrows(): void
    {
        $serializer = $this->createPartialMock(JsonSerializer::class, ['hashToJson']);

        $errorMessage = $this->getRandomString();
        $algorithm = $this->getRandomString();
        $content = $this->getRandomString();
        $hashes = [
            $algorithm => $content,
        ];

        $serializer->method('hashToJson')
            ->with($algorithm, $content)
            ->willThrowException(new DomainException($errorMessage));

        $this->expectWarning();
        $this->expectWarningMessageMatches('/skipped hash/i');
        $this->expectWarningMessageMatches('/'.preg_quote($errorMessage, '/').'/');

        iterator_to_array($serializer->hashesToJson($hashes));
    }

    // endregion

    // region helpers

    private function getRandomString(int $length = 128): string
    {
        return bin2hex(random_bytes($length));
    }

    // endregion helpers
}
