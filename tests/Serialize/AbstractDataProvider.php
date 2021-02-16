<?php

namespace CycloneDX\Tests\Serialize;

use CycloneDX\Enums\AbstractClassification;
use CycloneDX\Enums\AbstractHashAlgorithm;
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
abstract class AbstractDataProvider
{
    /**
     * a set of Bom structures.
     *
     * @return Generator<array{Bom}>
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
     * @return Generator<array{Bom}>
     */
    public static function bomPlain(): Generator
    {
        yield 'plain' => [new Bom()];
        yield 'plain v23' => [(new Bom())->setVersion(23)];
    }

    /**
     * BOM with one plain component.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentPlain(): Generator
    {
        yield 'component: plain' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', 'version')),
        ])];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        $license = 'MIT';
        yield "license: ${license}" => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', 'version'))
                ->setLicenses([new License($license)]),
        ])];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        yield 'license: random' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', 'version'))
                ->setLicenses([new License('random '.bin2hex(random_bytes(32)))]),
        ])];
    }

    /**
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'License with URL' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', 'version'))
                ->setLicenses([
                    (new License('some text'))
                        ->setUrl('https://example.com/license'),
                ]),
        ])];
    }

    /**
     * BOMs with one component that has a version.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentVersion(): Generator
    {
        $versions = ['1.0', 'dev-master'];
        foreach ($versions as $version) {
            yield "version: {$version}" => [(new Bom())->setComponents([
                new Component(AbstractClassification::LIBRARY, 'name', $version),
            ])];
        }
    }

    /**
     * BOMs with all hash algorithms available.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentAllHashAlgorithms(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new \ReflectionClass(AbstractHashAlgorithm::class))->getConstants());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec10())->getSupportedHashAlgorithms());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec11())->getSupportedHashAlgorithms());
    }

    /**
     * BOMs with all hash algorithms available. in Spec11.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithmsFromSpec((new Spec12())->getSupportedHashAlgorithms());
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @param string[] $hashAlgorithms
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentHashAlgorithmsFromSpec(array $hashAlgorithms): Generator
    {
        $hashAlgorithms = array_unique($hashAlgorithms, SORT_STRING);
        $label = implode(',', $hashAlgorithms);
        yield "hash algs: {{$label}}" => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setHashes(array_fill_keys($hashAlgorithms, '12345678901234567890123456789012')),
        ])];
    }

    /**
     * BOMs with every list possible as set from assoc array.
     *
     * assoc lists might cause json encoder produce schema-invalid data if implemented wrong.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomFromAssocLists(): Generator
    {
        yield 'every list from assoc' => [(new Bom())->setComponents([
            'myComponent' => (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setLicenses(['myLicense' => new License('some license')]),
        ])];
    }

    /**
     * BOMs with components that have a description.
     *
     * @return Generator<array{Bom}>
     */
    public static function bomWithComponentDescription(): Generator
    {
        yield 'description: none' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setDescription(null),
        ])];
        yield 'description: empty' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setDescription(''),
        ])];
        yield 'description: random' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setDescription(bin2hex(random_bytes(255))),
        ])];
        yield 'description: spaces' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setDescription("\ta  test   "),
        ])];
        yield 'description: XML special chars' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setDescription(
                    'thisa&that'. // an & that is not a XML entity
                    '<strong>html<strong>'. // things that might cause schema-invalid XML
                    'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                ),
        ])];
    }
}
