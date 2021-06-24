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
use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Repositories\ComponentRepository;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Repositories\HashRepository;
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Spec\SpecInterface;
use DomainException;
use DOMDocument;
use DOMNode;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\XmlSerializer
 */
class XmlSerializeTest extends TestCase
{
    // region serialize

    /**
     * @dataProvider dpTestSerialize
     */
    public function testSerialize(bool $pretty, string $expectedXml): void
    {
        $bom = $this->createStub(Bom::class);
        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn('999');
        $serializer = $this->createPartialMock(XmlSerializer::class, ['bomToDom']);
        $serializer->setSpec($spec);
        $serializer->expects(self::once())->method('bomToDom')
            ->with()
            ->willReturnCallback(function (DOMDocument $dom): \DOMElement {
                $fake = $dom->createElement('FakeBom');
                $fake->appendChild($dom->createElement('FormatTest'));

                return $fake;
            });

        $xml = $serializer->serialize($bom, $pretty);

        self::assertSame($expectedXml, $xml);
    }

    public static function dpTestSerialize(): Generator
    {
        yield 'pretty' => [
            true,
            // dont use HEREDOC/NEWDOC - they would have strange line-ending behaviour
            '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<FakeBom>'."\n".
            '  <FormatTest/>'."\n".
            '</FakeBom>'."\n",
        ];
        yield 'not pretty' => [
            false,
            '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<FakeBom><FormatTest/></FakeBom>'."\n",
            ];
    }

    // endregion serialize

    // region bomToDom

    public function testBomToDom(): void
    {
        $components = $this->createStub(ComponentRepository::class);
        $bom = $this->createConfiguredMock(Bom::class, [
            'getVersion' => 1337,
            'getComponentRepository' => $components,
        ]);

        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn('mySpecVersion');
        $dom = new DOMDocument();
        $serializer = $this->createPartialMock(XmlSerializer::class, ['componentsToDom']);
        $serializer->setSpec($spec);
        $serializer->expects(self::once())->method('componentsToDom')
            ->with($dom, $components)
            ->willReturn($dom->createElement('fakeComponents'));

        $expectedDom = new DOMDocument();
        $expected = $expectedDom->createElementNS('http://cyclonedx.org/schema/bom/mySpecVersion', 'bom');
        $expected->setAttribute('version', '1337');
        $expected->appendChild($expectedDom->createElement('fakeComponents'));

        $data = $serializer->bomToDom($dom, $bom);

        self::assertDomNodeEqualsDomNode($expected, $data);
    }

    // endregion bomToDom

    // region componentsToJson

    public function testComponentsToJson(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $spec = $this->createStub(SpecInterface::class);
        $serializer = $this->createPartialMock(XmlSerializer::class, ['componentToDom']);
        $serializer->setSpec($spec);
        $components = $this->createStub(ComponentRepository::class);
        $components->method('count')->willReturn(2);
        $components->method('getComponents')->willReturn([$component1, $component2]);
        $expectedDom = new DOMDocument();
        $expected = $expectedDom->createElement('components');
        $expected->appendChild($expectedDom->createElement('Component1Fake'));
        $expected->appendChild($expectedDom->createElement('Component2Fake'));
        $dom = new DOMDocument();

        $serializer->expects(self::exactly(2))->method('componentToDom')
            ->withConsecutive([$dom, $component1], [$dom, $component2])
            ->willReturnOnConsecutiveCalls($dom->createElement('Component1Fake'), $dom->createElement('Component2Fake'));

        $got = $serializer->componentsToDom($dom, $components);

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testComponentsToJsonWithEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = $this->createPartialMock(XmlSerializer::class, ['componentToDom']);
        $serializer->setSpec($spec);
        $components = $this->createStub(ComponentRepository::class);
        $components->method('count')->willReturn(0);
        $components->method('getComponents')->willReturn([]);

        $serializer->expects(self::never())->method('componentToDom');

        $got = $serializer->componentsToDom(new DOMDocument(), $components);

        self::assertDomNodeEqualsDomNode((new DOMDocument())->createElement('components'), $got);
    }

    // endregion componentsToJson

    // region componentToDom

    public function testComponentToDom(): void
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
        $serializer = new XmlSerializer($spec);
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
                'getLicense' => $this->createConfiguredMock(
                    LicenseExpression::class,
                    ['getExpression' => '(Foo and Bar)']
                ),
                'getHashRepository' => $this->createConfiguredMock(
                    HashRepository::class,
                    [
                        'getHashes' => ['myAlg' => 'myHash'],
                        'count' => 2,
                    ]
                ),
            ]
        );

        $domExpected = new DOMDocument();
        $expected = $domExpected->createElement('component');
        $expected->setAttribute('type', 'myType');
        $expected->appendChild($domExpected->createElement('group', 'myGroup'));
        $expected->appendChild($domExpected->createElement('name', 'myName'));
        $expected->appendChild($domExpected->createElement('version', 'myVersion'));
        $expected->appendChild($domExpected->createElement('description', 'myDescription'));
        $expected->appendChild($domExpected->createElement('hashes'))
            ->appendChild($domExpected->createElement('hash', 'myHash'))
            ->setAttribute('alg', 'myAlg');
        $expected->appendChild($domExpected->createElement('licenses'))
            ->appendChild($domExpected->createElement('expression', '(Foo and Bar)'));
        $expected->appendChild($domExpected->createElement('purl', 'myPURL'));

        $data = $serializer->componentToDom(new DOMDocument(), $component);

        self::assertDomNodeEqualsDomNode($expected, $data);
    }

    public function testComponentToDomEradicateNulls(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);
        $serializer = new XmlSerializer($spec);
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
        $domExpected = new DOMDocument();
        $expected = $domExpected->createElement('component');
        $expected->setAttribute('type', 'myType');
        $expected->appendChild($domExpected->createElement('name', 'myName'));
        $expected->appendChild($domExpected->createElement('version', 'myVersion'));

        $data = $serializer->componentToDom(new DOMDocument(), $component);

        self::assertDomNodeEqualsDomNode($expected, $data);
    }

    public function testComponentToDomThrowsOnFalseType(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new XmlSerializer($spec);
        $component = $this->createStub(Component::class);
        $component->method('getType')->willReturn('myType');

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(false);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported component type/i');

        $serializer->componentToDom(new DOMDocument(), $component);
    }

    // endregion componentToDom

    // region hashesToJson

    public function testHashesToDomSkipsThrow()
    {
        $serializer = $this->createPartialMock(XmlSerializer::class, ['hashToDom']);
        $hashes = $this->createConfiguredMock(HashRepository::class, [
            'count' => 1,
            'getHashes' => ['MD5' => '123456'],
        ]);
        $dom = new DOMDocument();

        $serializer->expects(self::once())->method('hashToDom')
            ->with($dom, 'MD5', '123456')
            ->willThrowException(new \DomainException());

        self::assertNull($serializer->hashesToDom($dom, $hashes));
    }

    // endregion hashesToJson

    // region hashToDom

    public function testHashToDom(): void
    {
        $algorithm = $this->getRandomString();
        $content = $this->getRandomString();
        $expected = (new DOMDocument())->createElement('hash', $content);
        $expected->setAttribute('alg', $algorithm);

        $spec = $this->createStub(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->willReturn(true);
        $spec->method('isSupportedHashContent')->willReturn(true);
        $serializer = new XmlSerializer($spec);

        $got = $serializer->hashToDom(new DOMDocument(), $algorithm, $content);

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testHashToDomInvalidAlgorithm(): void
    {
        $algorithm = $this->getRandomString();

        $spec = $this->createStub(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->with($algorithm)->willReturn(false);
        $spec->method('isSupportedHashContent')->willReturn(true);
        $serializer = new XmlSerializer($spec);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash algorithm/i');
        $serializer->hashToDom(new DOMDocument(), $algorithm, $this->getRandomString());
    }

    public function testHashToDomInvalidContent(): void
    {
        $content = $this->getRandomString();

        $spec = $this->createStub(SpecInterface::class);
        $spec->method('isSupportedHashAlgorithm')->willReturn(true);
        $spec->method('isSupportedHashContent')->with($content)->willReturn(false);
        $serializer = new XmlSerializer($spec);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash content/i');
        $serializer->hashToDom(new DOMDocument(), $this->getRandomString(), $content);
    }

    // endregion hashToDom

    // region licenseToDom

    public function testLicenseToDomWithDisjunctiveLicenseWithId(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new XmlSerializer($spec);
        $license = $this->createConfiguredMock(DisjunctiveLicense::class, [
            'getId' => 'MIT',
            'getName' => null,
            'getUrl' => 'https://url.to/license',
        ]);
        $domExpected = new DOMDocument();
        $expected = $domExpected->createElement('licenses');
        $expectedLicense = $expected->appendChild($domExpected->createElement('license'));
        $expectedLicense->appendChild($domExpected->createElement('id', 'MIT'));
        $expectedLicense->appendChild($domExpected->createElement('url', 'https://url.to/license'));

        $got = $serializer->licenseToDom(new DOMDocument(), $this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 1,
            'getLicenses' => [$license],
        ]));

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testLicenseToDomWithDisjunctiveLicenseWithName(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new XmlSerializer($spec);
        $license = $this->createConfiguredMock(DisjunctiveLicense::class, [
            'getId' => null,
            'getName' => 'myLicense',
            'getUrl' => null,
        ]);
        $domExpected = new DOMDocument();
        $expected = $domExpected->createElement('licenses');
        $expected->appendChild($domExpected->createElement('license'))
                 ->appendChild($domExpected->createElement('name', 'myLicense'));

        $got = $serializer->licenseToDom(new DOMDocument(), $this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 1,
            'getLicenses' => [$license],
        ]));

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testLicenseToDomWithDisjunctiveLicenseEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new XmlSerializer($spec);

        $got = $serializer->licenseToDom(new DOMDocument(), $this->createConfiguredMock(DisjunctiveLicenseRepository::class, [
            'count' => 0,
            'getLicenses' => [],
        ]));

        self::assertNull($got);
    }

    // endregion licenseToDom

    // region helpers

    private static function assertDomNodeEqualsDomNode(DOMNode $expected, DOMNode $actual, string $message = ''): void
    {
        $expectedDom = new DOMDocument();
        $expectedElement = $expectedDom->appendChild($expectedDom->importNode($expected, true));
        $expectedXml = $expectedElement->C14N();
        self::assertNotFalse($expectedXml, 'C14N failed on: expectedElement');

        $actualDom = new DOMDocument();
        $actualElement = $actualDom->appendChild($actualDom->importNode($actual, true));
        $actualXml = $actualElement->C14N();
        self::assertNotFalse($actualXml, 'C14N failed on: actualElement');

        self::assertSame($expectedXml, $actualXml, $message);
    }

    private function getRandomString(int $length = 128): string
    {
        return bin2hex(random_bytes($length));
    }

    // endregion helpers
}
