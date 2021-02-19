<?php

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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
use CycloneDX\Models\License;
use CycloneDX\Specs\Spec10;
use CycloneDX\Specs\Spec11;
use CycloneDX\Specs\Spec12;
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
        yield from self::bomWithComponentLicenseUrl();
        yield from self::bomFromAssocLists();
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
        yield 'component: plain' => [(new Bom())->addComponent(
            new Component(Classification::LIBRARY, 'name', 'version')
        )];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        $license = 'MIT';
        yield "license: ${license}" => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->addLicense(new License($license))
        )];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        yield 'license: random' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->addLicense(new License('random '.bin2hex(random_bytes(32))))
        )];
    }

    /**
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'License with URL' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', 'version'))
                ->addLicense(
                    (new License('some text'))
                        ->setUrl('https://example.com/license'),
                )
        )];
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
            yield "version: {$version}" => [(new Bom())->addComponent(
                new Component(Classification::LIBRARY, 'name', $version),
            )];
        }
    }

    /**
     * BOMs with all hash algorithms available.
     *
     * @psalm-return Generator<array{Bom}>
     *
     * @psalm-suppress InvalidArgument
     */
    public static function bomWithComponentAllHashAlgorithms(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new \ReflectionClass(HashAlgorithm::class))->getConstants());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec10())->getSupportedHashAlgorithms());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec11())->getSupportedHashAlgorithms());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec12())->getSupportedHashAlgorithms());
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
        $hashAlgorithms = array_unique($hashAlgorithms, SORT_STRING);
        $label = implode(',', $hashAlgorithms);
        yield "hash algs: {{$label}}" => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setHashes(array_fill_keys($hashAlgorithms, '12345678901234567890123456789012'))
        )];
    }

    /**
     * BOMs with every list possible as set from assoc array.
     *
     * assoc lists might cause json encoder produce schema-invalid data if implemented wrong.
     *
     * @psalm-return Generator<array{Bom}>
     */
    public static function bomFromAssocLists(): Generator
    {
        yield 'set every list from assoc' => [(new Bom())->setComponents([
            'myComponent' => (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setLicenses(['myLicense' => new License('some license')]),
        ])];
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            yield 'add every list from assoc' => [
                (new Bom())->addComponent(
                    ...[
                    'myComponent' => (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->addLicense(...[
                            'myLicense' => new License('some license'),
                        ]),
                ]
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
        yield 'description: none' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(null)
        )];
        yield 'description: empty' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription('')
        )];
        yield 'description: random' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(bin2hex(random_bytes(255)))
        )];
        yield 'description: spaces' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription("\ta  test   ")
        )];
        yield 'description: XML special chars' => [(new Bom())->addComponent(
            (new Component(Classification::LIBRARY, 'name', '1.0'))
                ->setDescription(
                    'thisa&that'. // an & that is not a XML entity
                    '<strong>html<strong>'. // things that might cause schema-invalid XML
                    'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                )
        )];
    }
}
