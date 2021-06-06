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

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Specs\SpecInterface;
use DomainException;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\JsonSerializer
 * @covers \CycloneDX\Serialize\AbstractSerialize
 */
class JasonSerializeTest extends TestCase
{
    // region serialize

    /**
     * @dataProvider dpTestSerialize
     */
    public function testSerialize(bool $pretty, string $expectedJson): void
    {
        $bom = $this->createStub(Bom::class);
        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn('999');
        $serializer = $this->createPartialMock(JsonSerializer::class, ['bomToJson']);
        $serializer->setSpec($spec);
        $serializer->expects(self::once())->method('bomToJson')
            ->with($bom)
            ->willReturn(['bomToJsonFake' => true]);

        $json = $serializer->serialize($bom, $pretty);

        self::assertSame($expectedJson, $json);
    }

    public static function dpTestSerialize(): Generator
    {
        yield 'pretty' => [
            true,
            // dont use HEREDOC/NEWDOC - they would have strange line-ending behaviour
            '{'."\n".
            '    "bomToJsonFake": true'."\n".
            '}',
        ];
        yield 'not pretty' => [false, '{"bomToJsonFake":true}'];
    }

    /**
     * @dataProvider dpUnsupportedSpecVersions
     */
    public function testSerializeThrowsOnLowVersion(string $version): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn($version);
        $serializer = new JsonSerializer($spec);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/unsupported spec version/i');

        $serializer->serialize($this->createStub(Bom::class));
    }

    public static function dpUnsupportedSpecVersions(): Generator
    {
        $versions = [
            'myVersion',
            '1.1',
            '1.0',
        ];
        foreach ($versions as $version) {
            yield $version => [$version];
        }
    }

    // endregion serialize

    // region bomToJson

    public function testBomToJson(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn('mySpecVersion');
        $spec->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);

        $fakeComponent = $this->createStub(Component::class);
        $serializer = $this->createPartialMock(JsonSerializer::class, ['componentToJson']);
        $serializer->setSpec($spec);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1337,
                'getComponents' => [$fakeComponent],
            ]
        );

        $serializer->expects(self::once())->method('componentToJson')
            ->with($fakeComponent)
            ->willReturn(['fakeComponent' => true]);

        $data = $serializer->bomToJson($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => 'mySpecVersion',
                'version' => 1337,
                'components' => [
                    [
                        'fakeComponent' => true,
                    ],
                ],
            ],
            $data
        );
    }

    // endregion bomToJson

    // region componentToJson

    public function testComponentToJsonThrowsOnFalseType(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new JsonSerializer($spec);
        $component = $this->createStub(Component::class);
        $component->method('getType')->willReturn('myType');

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(false);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported component type/i');

        $serializer->componentToJson($component);
    }

    public function testComponentToJson(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);
        $spec->expects(self::once())->method('isSupportedHashAlgorithm')
            ->with('myAlg')
            ->willReturn(true);
        $spec->expects(self::once())->method('isSupportedHashContent')
            ->with('myHash')
            ->willReturn(true);
        $serializer = new JsonSerializer($spec);
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getType' => 'myType',
                'getPackageUrl' => $this->createConfiguredMock(
                    PackageUrl::class,
                    ['toString' => 'myPURL', '__toString' => 'myPURL']
                ),
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getGroup' => 'myGroup',
                'getDescription' => 'myDescription',
                'getLicenses' => [
                    $this->createConfiguredMock(
                        License::class,
                        ['getId' => null, 'getName' => 'myLicense']
                    ),
                ],
                'getHashes' => ['myAlg' => 'myHash'],
            ]
        );

        $data = $serializer->componentToJson($component);

        self::assertSame(
            [
                'type' => 'myType',
                'name' => 'myName',
                'version' => 'myVersion',
                'group' => 'myGroup',
                'description' => 'myDescription',
                'licenses' => [
                    [
                        'license' => [
                            'name' => 'myLicense',
                        ],
                    ],
                ],
                'hashes' => [
                    [
                        'alg' => 'myAlg',
                        'content' => 'myHash',
                    ],
                ],
                'purl' => 'myPURL',
            ],
            $data
        );
    }

    public function testComponentToJsonEradicateNulls(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);
        $serializer = new JsonSerializer($spec);
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getType' => 'myType',
                'getPackageUrl' => null,
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getGroup' => null,
                'getDescription' => null,
                'getLicenses' => [],
                'getHashes' => [],
            ]
        );

        $data = $serializer->componentToJson($component);

        self::assertSame(
            [
                'type' => 'myType',
                'name' => 'myName',
                'version' => 'myVersion',
            ],
            $data
        );
    }

    // endregion componentToJson

    // region hashToJson

    public function testHashToJson(): void
    {
        $hash = ['alg' => $this->getRandomString(), 'content' => $this->getRandomString()];

        $spec = $this->createStub(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->with($hash['alg'])->willReturn(true);
        $spec->method('isSupportedHashContent')->with($hash['content'])->willReturn(true);
        $serializer = new JsonSerializer($spec);

        $data = $serializer->hashToJson($hash['alg'], $hash['content']);

        self::assertEquals($hash, $data);
    }

    public function testHashToJsonInvalidAlgorithm(): void
    {
        $algorithm = $this->getRandomString();

        $spec = $this->createStub(SpecInterface::class);
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

        $spec = $this->createStub(SpecInterface::class);
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
        $serializer = new JsonSerializer($this->createStub(SpecInterface::class));
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

        $license = $this->createStub(License::class);
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
