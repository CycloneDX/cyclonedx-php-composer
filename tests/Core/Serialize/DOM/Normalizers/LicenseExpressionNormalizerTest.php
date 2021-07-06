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

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\LicenseExpressionNormalizer;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\LicenseExpressionNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 * @covers \CycloneDX\Core\Helpers\SimpleDomTrait
 */
class LicenseExpressionNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testConstructor(): LicenseExpressionNormalizer
    {
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getDocument' => new DOMDocument()]);

        $normalizer = new LicenseExpressionNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());

        return $normalizer;
    }

    /**
     * @depends testConstructor
     */
    public function testNormalize(LicenseExpressionNormalizer $normalizer): void
    {
        $license = $this->createMock(LicenseExpression::class);
        $license->method('getExpression')->willReturn('foo');

        $normalized = $normalizer->normalize($license);

        self::assertStringEqualsDomNode('<expression>foo</expression>', $normalized);
    }
}
