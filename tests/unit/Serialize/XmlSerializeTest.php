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
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Specs\SpecInterface;
use DomainException;
use DOMDocument;
use DOMNode;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\XmlSerializer
 * @covers \CycloneDX\Serialize\AbstractSerialize
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

    public function testBomToBom(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $spec->method('getVersion')->willReturn('mySpecVersion');
        $spec->method('isSupportedComponentType')
            ->with('myType')
            ->willReturn(true);

        $fakeComponent = $this->createStub(Component::class);
        $serializer = $this->createPartialMock(XmlSerializer::class, ['componentToDom']);
        $serializer->setSpec($spec);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1337,
                'getComponents' => [$fakeComponent],
            ]
        );
        $dom = new DOMDocument();
        $serializer->expects(self::once())->method('componentToDom')
            ->with($dom, $fakeComponent)
            ->willReturn($dom->createElement('fakeComponent'));
        $expectedDom = new DOMDocument();
        $expected = $expectedDom->createElementNS('http://cyclonedx.org/schema/bom/mySpecVersion', 'bom');
        $expected->setAttribute('version', '1337');
        $expected->appendChild($expectedDom->createElement('components'))
            ->appendChild($expectedDom->createElement('fakeComponent'));

        $data = $serializer->bomToDom($dom, $bom);

        self::assertDomNodeEqualsDomNode($expected, $data);
    }

    // endregion bomToDom

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
                'getLicenses' => [
                    $this->createConfiguredMock(
                        License::class,
                        ['getId' => null, 'getName' => 'myLicense']
                    ),
                ],
                'getHashes' => ['myAlg' => 'myHash'],
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
            ->appendChild($domExpected->createElement('license'))
            ->appendChild($domExpected->createElement('name', 'myLicense'));
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
                'getLicenses' => [],
                'getHashes' => [],
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

    // region hashesToDom

    public function testHashesToDom(): void
    {
        $algorithm = $this->getRandomString();
        $content = $this->getRandomString();
        $expectedDOM = new DOMDocument();
        $expected = $expectedDOM->createElement('hashes');
        $expected->appendChild($expectedDOM->createElement('hash', 'FakeHash'));

        $dom = new DOMDocument();
        $serializer = $this->createPartialMock(XmlSerializer::class, ['hashToDom']);
        $serializer->expects(self::once())->method('hashToDom')
            ->with($dom, $algorithm, $content)
            ->willReturn($dom->createElement('hash', 'FakeHash'));

        $got = $serializer->hashesToDom($dom, [$algorithm => $content]);

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testHashesToDomNullWhenEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $serializer = new XmlSerializer($spec);

        $got = $serializer->hashesToDom(new DOMDocument(), []);

        self::assertNull($got);
    }

    public function testHashesToDomSkipWhenThrown(): void
    {
        $algorithm = $this->getRandomString();
        $content = $this->getRandomString();
        $dom = new DOMDocument();

        $serializer = $this->createPartialMock(XmlSerializer::class, ['hashToDom']);
        $serializer->expects(self::once())->method('hashToDom')
            ->with($dom, $algorithm, $content)
            ->willThrowException(new DomainException($this->getRandomString()));

        $got = $serializer->hashesToDom($dom, [$algorithm => $content]);

        self::assertNull($got);
    }

    // endregion hashesToDom

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
        $this->expectExceptionMessageMatches('/invalid algorithm/i');
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
        $this->expectExceptionMessageMatches('/invalid content/i');
        $serializer->hashToDom(new DOMDocument(), $this->getRandomString(), $content);
    }

    // endregion hashToDom

    // region licensesToDom

    public function testLicenses(): void
    {
        $expectedDOM = new DOMDocument();
        $expected = $expectedDOM->createElement('licenses');
        $expected->appendChild($expectedDOM->createElement('license', 'FakeLicenseResult'));

        $license = $this->createStub(License::class);
        $dom = new DOMDocument();
        $serializer = $this->createPartialMock(XmlSerializer::class, ['licenseToDom']);
        $serializer->expects(self::once())->method('licenseToDom')
            ->with($dom, $license)
            ->willReturn($dom->createElement('license', 'FakeLicenseResult'));

        $got = $serializer->licensesToDom($dom, [$license]);

        self::assertDomNodeEqualsDomNode($expected, $got);
    }

    public function testLicensesToDomNullWhenEmpty(): void
    {
        $serializer = new XmlSerializer($this->createStub(SpecInterface::class));

        $got = $serializer->licensesToDom(new DOMDocument(), []);

        self::assertNull($got);
    }

    // endregion licensesToDom

    // region licenseToDom

    /**
     * @dataProvider dpLicenseToDom
     */
    public function testLicenseToDom(License $license, $expected): void
    {
        $serializer = new XmlSerializer($this->createStub(SpecInterface::class));
        $domElem = $serializer->licenseToDom(new DOMDocument(), $license);
        self::assertDomNodeEqualsDomNode($expected, $domElem);
    }

    public function dpLicenseToDom(): Generator
    {
        $dom = new DOMDocument();

        $name = $this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getName')->willReturn($name);
        $expected = $dom->createElement('license');
        $expected->appendChild($dom->createElement('name', $name));
        yield 'withName' => [$license, $expected];

        $id = $this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getId')->willReturn($id);
        $expected = $dom->createElement('license');
        $expected->appendChild($dom->createElement('id', $id));
        yield 'withId' => [$license, $expected];

        $name = $this->getRandomString();
        $url = 'https://example.com/license/'.$this->getRandomString();
        $license = $this->createStub(License::class);
        $license->method('getUrl')->willReturn($url);
        $license->method('getName')->willReturn($name);
        $expected = $dom->createElement('license');
        $expected->appendChild($dom->createElement('name', $name));
        $expected->appendChild($dom->createElement('url', $url));
        yield 'withUrl' => [$license, $expected];
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