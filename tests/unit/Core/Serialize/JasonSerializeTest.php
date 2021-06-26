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

namespace CycloneDX\Tests\unit\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\DisjunctiveLicense;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JsonSerializer;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonSerializer
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
        yield 'not pretty' => [
            false,
            '{"bomToJsonFake":true}',
        ];
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
                'getComponentRepository' => $this->createConfiguredMock(
                    ComponentRepository::class,
                    [
                        'getComponents' => [$fakeComponent],
                        'count' => 1,
                    ]
                ),
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

    // region componentsToJson

    public function testComponentsToJson(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $spec = $this->createStub(SpecInterface::class);
        $serializer = $this->createPartialMock(JsonSerializer::class, ['componentToJson']);
        $serializer->setSpec($spec);
        $components = $this->createStub(ComponentRepository::class);
        $components->method('count')->willReturn(2);
        $components->method('getComponents')->willReturn([$component1, $component2]);

        $serializer->expects(self::exactly(2))->method('componentToJson')
            ->withConsecutive([$component1], [$component2])
            ->willReturnOnConsecutiveCalls(['Component!Fake'], ['Component!Fake']);

        $got = $serializer->componentsToJson($components);

        self::assertSame([['Component!Fake'], ['Component!Fake']], $got);
    }

    public function testComponentsToJsonWithEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = $this->createPartialMock(JsonSerializer::class, ['componentToJson']);
        $serializer->setSpec($spec);
        $components = $this->createStub(ComponentRepository::class);
        $components->method('count')->willReturn(0);
        $components->method('getComponents')->willReturn([]);

        $serializer->expects(self::never())->method('componentToJson');

        $got = $serializer->componentsToJson($components);

        self::assertSame([], $got);
    }

    // endregion componentsToJson

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

    public function testComponentWithFullValues(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);
        $spec->expects(self::once())->method('isSupportedHashAlgorithm')
            ->with('MD5')
            ->willReturn(true);
        $spec->expects(self::once())->method('isSupportedHashContent')
            ->with('1234567890')
            ->willReturn(true);
        $purl = $this->createConfiguredMock(PackageUrl::class, [
            'toString' => 'myPURL',
            '__toString' => 'myPURL',
        ]);
        $license = $this->createConfiguredMock(LicenseExpression::class, [
            'getExpression' => '(Some or License)',
        ]);
        $hashes = $this->createConfiguredMock(HashRepository::class, [
                'getHashes' => ['MD5' => '1234567890'],
            ]
        );
        $serializer = new JsonSerializer($spec);
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getType' => 'myType',
                'getPackageUrl' => $purl,
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getGroup' => 'myGroup',
                'getDescription' => 'my description',
                'getLicense' => $license,
                'getHashRepository' => $hashes,
            ]
        );

        $data = $serializer->componentToJson($component);

        self::assertSame(
            [
                'type' => 'myType',
                'name' => 'myName',
                'version' => 'myVersion',
                'group' => 'myGroup',
                'description' => 'my description',
                'licenses' => [
                    [
                        'expression' => '(Some or License)',
                    ],
                ],
                'hashes' => [
                    [
                        'alg' => 'MD5',
                        'content' => '1234567890',
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
                'getLicense' => null,
                'getHashRepository' => null,
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

    // region hashesToJson

    public function testHashesToDomSkipsThrow(): void
    {
        $serializer = $this->createPartialMock(JsonSerializer::class, ['hashToJson']);
        $hashes = $this->createConfiguredMock(HashRepository::class, [
            'count' => 1,
            'getHashes' => ['MD5' => '123456'],
        ]);

        $serializer->expects(self::once())->method('hashToJson')
            ->with('MD5', '123456')
            ->willThrowException(new \DomainException());

        self::assertNull($serializer->hashesToJson($hashes));
    }

    // endregion hashesToJson

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
        $this->expectExceptionMessageMatches('/invalid hash algorithm/i');
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
        $this->expectExceptionMessageMatches('/invalid hash content/i');
        $serializer->hashToJson($this->getRandomString(), $content);
    }

    // endregion hashToJson

    // region licenseToJson

    public function testLicenseToJsonWithDisjunctiveLicenseWithId(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new JsonSerializer($spec);
        $license = $this->createConfiguredMock(DisjunctiveLicense::class, [
            'getId' => 'MIT',
            'getName' => null,
            'getUrl' => 'https://url.to/license',
        ]);

        $got = $serializer->licenseToJson($this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 1,
            'getLicenses' => [$license],
        ]));

        self::assertSame([['license' => [
            'id' => 'MIT',
            'url' => 'https://url.to/license',
        ]]], $got);
    }

    public function testLicenseToJsonWithDisjunctiveLicenseWithName(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new JsonSerializer($spec);
        $license = $this->createConfiguredMock(DisjunctiveLicense::class, [
            'getId' => null,
            'getName' => 'myLicense',
            'getUrl' => null,
        ]);

        $got = $serializer->licenseToJson($this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 1,
            'getLicenses' => [$license],
        ]));

        self::assertSame([['license' => [
            'name' => 'myLicense',
        ]]], $got);
    }

    public function testLicenseToDomWithDisjunctiveLicenseEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new JsonSerializer($spec);

        $got = $serializer->licenseToJson($this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 0,
            'getLicenses' => [],
        ]));

        self::assertNull($got);
    }

    // endregion licenseToJson

    // region helpers

    private function getRandomString(int $length = 128): string
    {
        return bin2hex(random_bytes($length));
    }

    // endregion helpers
}
