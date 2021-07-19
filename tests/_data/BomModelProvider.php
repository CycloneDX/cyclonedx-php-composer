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

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Repositories\ToolRepository;
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
    public static function allBomTestData(): Generator
    {
        yield from self::bomPlain();

        yield from self::bomWithAllComponents();

        yield from self::bomWithAllMetadata();
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
     * BOM wil all possible components.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithAllComponents(): Generator
    {
        yield from self::bomWithComponentPlain();
        yield from self::bomWithComponentVersion();
        yield from self::bomWithComponentDescription();

        yield from self::bomWithComponentLicenseId();
        yield from self::bomWithComponentLicenseName();
        yield from self::bomWithComponentLicenseExpression();
        yield from self::bomWithComponentLicenseUrl();

        yield from self::bomWithComponentHashAlgorithmsAllKnown();

        yield from self::bomWithComponentTypeAllKnown();
    }

    /**
     * BOM wil all possible metadata.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithAllMetadata(): Generator
    {
        yield from self::bomWithMetaDataPlain();
        yield from self::bomWithMetaDataTools();
        yield from self::bomWithMetaDataComponent();
    }

    /**
     * BOM with one plain component.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentPlain(): Generator
    {
        yield 'component: plain' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    new Component(Classification::LIBRARY, 'name', 'version')
                )
            ),
        ];
    }

    /**
     * BOMs with all classification types known.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypeAllKnown(): Generator
    {
        /** @psalm-var list<string> $known */
        $known = array_values((new \ReflectionClass(Classification::class))->getConstants());
        yield from self::bomWithComponentTypes(
            ...$known,
            ...BomSpecData::getClassificationEnumForVersion('1.0'),
            ...BomSpecData::getClassificationEnumForVersion('1.1'),
            ...BomSpecData::getClassificationEnumForVersion('1.2'),
            ...BomSpecData::getClassificationEnumForVersion('1.3')
        );
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypeSpec10(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.0'));
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypeSpec11(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.1'));
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypeSpec12(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.2'));
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypeSpec13(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.3'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentTypes(string ...$types): Generator
    {
        $types = array_unique($types, \SORT_STRING);
        foreach ($types as $type) {
            yield "component types: $type" => [
                (new Bom())->setComponentRepository(
                    new ComponentRepository(
                        new Component($type, "dummy_$type", 'v0')
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        $license = 'MIT';
        yield "component license: $license" => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicense(
                            new DisjunctiveLicenseRepository(
                                DisjunctiveLicenseWithId::makeValidated(
                                    $license,
                                    SpdxLicenseValidatorSingleton::getInstance()
                                )
                            )
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        $license = 'random '.bin2hex(random_bytes(32));
        yield 'component license: random' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicense(
                            new DisjunctiveLicenseRepository(
                                new DisjunctiveLicenseWithName($license)
                            )
                        )
                )
            ),
        ];
    }

    public static function bomWithComponentLicenseExpression(): Generator
    {
        yield 'component license expression' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicense(
                            new LicenseExpression('(Foo or Bar)')
                        )
                )
            ),
        ];
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'component license with URL' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicense(
                            new DisjunctiveLicenseRepository(
                                (new DisjunctiveLicenseWithName('some text'))
                                    ->setUrl('https://example.com/license'),
                            )
                        )
                )
            ),
        ];
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
            yield "component version: $version" => [
                (new Bom())->setComponentRepository(
                    new ComponentRepository(
                        new Component(Classification::LIBRARY, 'name', $version),
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with all hash algorithms known.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsAllKnown(): Generator
    {
        /** @psalm-var list<string> $known */
        $known = array_values((new \ReflectionClass(HashAlgorithm::class))->getConstants());
        yield from self::bomWithComponentHashAlgorithms(
            ...$known,
            ...BomSpecData::getHashAlgEnumForVersion('1.0'),
            ...BomSpecData::getHashAlgEnumForVersion('1.1'),
            ...BomSpecData::getHashAlgEnumForVersion('1.2'),
            ...BomSpecData::getHashAlgEnumForVersion('1.3'),
        );
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.0.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.0'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.1.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.1'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.2.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.2'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.3.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec13(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.3'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithms(string ...$hashAlgorithms): Generator
    {
        $hashAlgorithms = array_unique($hashAlgorithms, \SORT_STRING);
        foreach ($hashAlgorithms as $hashAlgorithm) {
            yield "component hash alg: $hashAlgorithm" => [
                (new Bom())->setComponentRepository(
                    new ComponentRepository(
                        (new Component(Classification::LIBRARY, 'name', '1.0'))
                            ->setHashRepository(
                                new HashRepository([$hashAlgorithm => '12345678901234567890123456789012'])
                            )
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with components that have a description.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentDescription(): Generator
    {
        yield 'component description: none' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(null)
                )
            ),
        ];
        yield 'component description: empty' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription('')
                )
            ),
        ];
        yield 'component description: random' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(bin2hex(random_bytes(32)))
                )
            ),
        ];
        yield 'component description: spaces' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription("\ta  test   ")
                    )
                )
            ),
        ];
        yield 'component description: XML special chars' => [
            (new Bom())->setComponentRepository(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(
                            'thisa&that'. // an & that is not a XML entity
                            '<strong>html<strong>'. // things that might cause schema-invalid XML
                            'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with plain metadata.
     *
     * @psalm-return Generator<array{Bom}>
     */
    private static function bomWithMetaDataPlain(): Generator
    {
        yield 'metadata: plain' => [
            (new Bom())->setMetaData(new MetaData()),
        ];
    }

    /**
     * BOMs with plain metadata that have tools.
     *
     * @psalm-return Generator<array{Bom}>
     */
    private static function bomWithMetaDataTools(): Generator
    {
        yield 'metadata: empty tools' => [
            (new Bom())->setMetaData(
                (new MetaData())->setTools(new ToolRepository())
            ),
        ];

        yield 'metadata: some tools' => [
            (new Bom())->setMetaData(
                (new MetaData())->setTools(
                    new ToolRepository(
                        new Tool(),
                        (new Tool())
                            ->setVendor('myToolVendor')
                            ->setName('myTool')
                            ->setVersion('toolVersion')
                            ->setHashRepository(
                                new HashRepository([HashAlgorithm::MD5 => '12345678901234567890123456789012'])
                            ),
                    )
                )
            ),
        ];
    }

    /**
     * BOMs with plain metadata that have a component.
     *
     * @psalm-return Generator<array{Bom}>
     */
    private static function bomWithMetaDataComponent(): Generator
    {
        yield 'metadata: minimal component' => [
            (new Bom())->setMetaData(
                (new MetaData())->setComponent(
                    new Component(
                        Classification::APPLICATION,
                        'foo',
                        'bar'
                    )
                )
            ),
        ];
    }
}
