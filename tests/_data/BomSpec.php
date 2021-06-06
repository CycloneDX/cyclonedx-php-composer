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

namespace CycloneDX\Tests\_data;

use Generator;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertNotCount;
use SimpleXMLElement;

abstract class BomSpec
{
    public static function getSpecFilePath(string $version): string
    {
        $file = realpath(__DIR__."/../_spec/bom-${version}.SNAPSHOT.xsd");
        assertFileExists($file);

        return $file;
    }

    /**
     * @psalm-return list<string> sorted list
     */
    public static function getClassificationEnumForVersion(string $version): array
    {
        return self::getEnumValuesForName($version, 'classification');
    }

    /**
     * @psalm-return list<string> sorted list
     */
    public static function getHashAlgEnumForVersion(string $version): array
    {
        return self::getEnumValuesForName($version, 'hashAlg');
    }

    // region helpers

    private static $enumValueCache = [];

    /**
     * @psalm-return list<string> sorted list
     */
    private static function getEnumValuesForName(string $version, string $name): array
    {
        $values = self::$enumValueCache[$version][$name] ?? null;
        if (null === $values) {
            $values = iterator_to_array(self::getEnumValuesForNameFromFile($version, $name));
            assertNotCount(0, $values);
            sort($values, \SORT_STRING);
            self::$enumValueCache[$version][$name] = $values;
        }

        return $values;
    }

    /**
     * @psalm-return Generator<string>
     */
    private static function getEnumValuesForNameFromFile(string $version, string $name): Generator
    {
        $specXml = self::getSpecFilePath($version);
        $xml = new SimpleXMLElement($specXml, 0, true);
        $xmlEnumElems = $xml->xpath("xs:simpleType[@name='${name}']/xs:restriction/xs:enumeration/@value");
        /** @var SimpleXMLElement $xmlEnumElem */
        foreach ($xmlEnumElems as $xmlEnumElem) {
            yield (string) $xmlEnumElem;
        }
    }

    // endregion helpers
}
