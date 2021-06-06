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
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Specs\SpecInterface;
use DOMDocument;
use DOMNode;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\XmlSerializer
 * @covers \CycloneDX\Serialize\AbstractSerialize
 */
class XmlSerializeTest extends TestCase
{
    // region licenseToDom

    /**
     * @dataProvider dpLicenseToDom
     */
    public function testLicenseToDom(License $license, $expected): void
    {
        $dom = new DOMDocument();
        $serializer = new XmlSerializer($this->createStub(SpecInterface::class));
        $domElem = $serializer->licenseToDom($dom, $license);
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
