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

namespace CycloneDX\Tests\unit\Specs;

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Specs\SpecInterface;
use CycloneDX\Specs\Version;
use CycloneDX\Tests\_data\BomSpec;
use Generator;
use PHPUnit\Framework\TestCase;

abstract class AbstractSpecTestCase extends TestCase
{
    abstract protected function getSpec(): SpecInterface;

    /**
     * @psalm-return Version::V_*
     */
    abstract protected function getSpecVersion(): string;

    final public function testVersionMatches(): void
    {
        $version = $this->getSpec()->getVersion();
        self::assertSame($this->getSpecVersion(), $version);
    }

    final public function testGetSupportedComponentTypes(): void
    {
        $expected = BomSpec::getClassificationEnumForVersion($this->getSpecVersion());

        $values = $this->getSpec()->getSupportedComponentTypes();

        self::assertNotCount(0, $values);
        sort($values, \SORT_STRING);
        self::assertSame($expected, $values);
    }

    /**
     * @dataProvider dpIsSupportedComponentType
     */
    final public function testIsSupportedComponentType(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedComponentType($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedComponentType(): Generator
    {
        yield 'unknown' => [uniqid('Classification', false), false];
        $known = BomSpec::getClassificationEnumForVersion($this->getSpecVersion());
        $values = (new \ReflectionClass(Classification::class))->getConstants();
        foreach ($values as $value) {
            yield $value => [$value, \in_array($value, $known, true)];
        }
    }

    final public function testGetSupportedHashAlgorithms(): void
    {
        $expected = BomSpec::getHashAlgEnumForVersion($this->getSpecVersion());

        $values = $this->getSpec()->getSupportedHashAlgorithms();

        self::assertNotCount(0, $values);
        sort($values, \SORT_STRING);
        self::assertSame($expected, $values);
    }

    /**
     * @dataProvider dpIsSupportedHashAlgorithm
     */
    final public function testIsSupportedHashAlgorithm(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedHashAlgorithm($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedHashAlgorithm(): Generator
    {
        yield 'unknown' => [uniqid('HashAlg', false), false];
        $known = BomSpec::getHashAlgEnumForVersion($this->getSpecVersion());
        $values = (new \ReflectionClass(HashAlgorithm::class))->getConstants();
        foreach ($values as $value) {
            yield $value => [$value, \in_array($value, $known, true)];
        }
    }

    /**
     * @dataProvider dpIsSupportedHashContent
     */
    final public function testIsSupportedHashContent(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedHashContent($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedHashContent(): Generator
    {
        yield 'crap' => ['this is an invalid hash', false];
        yield 'valid sha1' => ['a052cfe45093f1c2d26bd854d06aa370ceca3b38', true];
    }
}
