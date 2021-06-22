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

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Repositories\ComponentRepository;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Repositories\HashRepository;
use Generator;

/**
 * common DataProvider.
 */
abstract class BomModelProvider
{
    /**
     * a set of Bom structures.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function fullBomTestData(): Generator
    {
        yield from self::bomPlain();
        yield from self::bomWithComponentPlain();
        yield from self::bomWithComponentVersion();
        yield from self::bomWithComponentDescription();
        yield from self::bomWithComponentLicenseId();
        yield from self::bomWithComponentLicenseName();
        yield from self::bomWithComponentLicenseExpression();
        yield from self::bomWithComponentLicenseUrl();
    }

    /**
     * Just an plain BOM.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomPlain(): Generator
    {
        yield 'plain' => [new Bom()];
        yield 'plain v23' => [(new Bom())->setVersion(23)];
    }

    /**
     * BOM with one plain component.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentPlain(): Generator
    {
        yield 'component: plain' => [(new Bom())->setComponentRepository(new ComponentRepository(
            new Component(Classification::LIBRARY, 'name', 'version')
        ))];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        $license = 'MIT';
        yield "license: $license" => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->setLicense(new DisjunctiveLicenseRepository(
                    DisjunctiveLicense::createFromNameOrId($license, SpdxLicenseValidatorSingleton::getInstance()))
        )))];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        $license = 'random '.bin2hex(random_bytes(32));
        yield 'license: random' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicense(
                            new DisjunctiveLicenseRepository(
                                DisjunctiveLicense::createFromNameOrId(
                                    $license,
                                    SpdxLicenseValidatorSingleton::getInstance()
                                )
                            )
                        )
                )
            ),
        ];
    }

    public static function bomWithComponentLicenseExpression(): Generator
    {
        yield 'license expression' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->setLicense(new LicenseExpression('(Foo or Bar)')
                )))];
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'License with URL' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->setLicense(new DisjunctiveLicenseRepository(
                    DisjunctiveLicense::createFromNameOrId('some text', SpdxLicenseValidatorSingleton::getInstance())
                        ->setUrl('https://example.com/license'),
                ))
        ))];
    }

    /**
     * BOMs with one component that has a version.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentVersion(): Generator
    {
        $versions = ['1.0', 'dev-master'];
        foreach ($versions as $version) {
            yield "version: {$version}" => [(new Bom())->setComponentRepository(new ComponentRepository(
                new Component(Classification::LIBRARY, 'name', $version),
            ))];
        }
    }

    /**
     * BOMs with all hash algorithms available.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentAllHashAlgorithms(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new \ReflectionClass(HashAlgorithm::class))->getConstants());
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.0.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec(BomSpecData::getHashAlgEnumForVersion('1.0'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.1.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec(BomSpecData::getHashAlgEnumForVersion('1.1'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.2.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec(BomSpecData::getHashAlgEnumForVersion('1.2'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.3.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec13(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec(BomSpecData::getHashAlgEnumForVersion('1.3'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @psalm-param list<HashAlgorithm::*> $hashAlgorithms
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsFromSpec(array $hashAlgorithms): Generator
    {
        $hashAlgorithms = array_unique($hashAlgorithms, \SORT_STRING);
        $label = implode(',', $hashAlgorithms);
        yield "hash algs: {{$label}}" => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setHashRepository(new HashRepository(array_fill_keys($hashAlgorithms, '12345678901234567890123456789012'))
        )))];
    }

    /**
     * BOMs with components that have a description.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentDescription(): Generator
    {
        yield 'description: none' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(null)
        ))];
        yield 'description: empty' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription('')
        ))];
        yield 'description: random' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(bin2hex(random_bytes(32)))
        ))];
        yield 'description: spaces' => [(new Bom())->setComponentRepository(new ComponentRepository((
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription("\ta  test   ")
        )))];
        yield 'description: XML special chars' => [(new Bom())->setComponentRepository(new ComponentRepository(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(
                    'thisa&that'. // an & that is not a XML entity
                    '<strong>html<strong>'. // things that might cause schema-invalid XML
                    'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                )
        ))];
    }
}
