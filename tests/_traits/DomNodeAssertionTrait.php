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

namespace CycloneDX\Tests\_traits;

use DOMDocument;
use DOMNode;
use PHPUnit\Framework\Assert;

trait DomNodeAssertionTrait
{
    /**
     * @throws \Exception
     */
    final protected static function assertDomNodeEqualsDomNode(DOMNode $expected, DomNode $actual): void
    {
        $container = new DOMDocument();

        $expectedNode = $container->appendChild($container->importNode($expected, true));
        $actualNode = $container->appendChild($container->importNode($actual, true));

        Assert::assertSame($expectedNode->C14N(), $actualNode->C14N());
    }

    /**
     * @throws \Exception
     */
    final protected static function assertDomNodeEqualsString(DomNode $expected, string $actual): void
    {
        $container = new DOMDocument();

        $expectedNode = $container->appendChild($container->importNode($expected, true));

        Assert::assertSame($expectedNode->C14N(), $actual);
    }

    /**
     * @throws \Exception
     */
    final protected static function assertStringEqualsDomNode(string $expected, DOMNode $actual): void
    {
        $container = new DOMDocument();

        $actualNode = $container->appendChild($container->importNode($actual, true));

        Assert::assertSame($expected, $actualNode->C14N());
    }
}
