<?php

namespace CycloneDX\Tests\BomFile;

use CycloneDX\Enums\AbstractClassification;
use CycloneDX\Enums\AbstractHashAlgorithm;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use Generator;

/**
 * common DataProvider.
 */
abstract class AbstractDataProvider
{
    /**
     * All available DataProviders at once.
     *
     * @return Generator<array{0: Bom}>
     */
    public static function all(): Generator
    {
        yield from self::bomPlain();
        yield from self::bomWithComponentPlain();
        yield from self::bomWithComponentVersion();
        yield from self::bomWithComponentLicenseId();
        yield from self::bomWithComponentLicenseName();
        yield from self::bomWithComponentAllHashAlgorithms();
        yield from self::bomFromAssocLists();
    }

    /**
     * Just an plain BOM.
     *
     * @return Generator<array{0: Bom}>
     */
    public static function bomPlain(): Generator
    {
        yield 'plain' => [new Bom()];
    }

    /**
     * BOM with one plain component.
     *
     * @return Generator<array{0: Bom}>
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
     * @return Generator<array{0: Bom}>
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
     * @return Generator<array{0: Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        $license = 'some text';
        yield "license: ${license}" => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', 'version'))
                ->setLicenses([new License($license)]),
        ])];
    }

    /**
     * BOMs with one component that has a version.
     *
     * @return Generator<array{0: Bom}>
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
     * @return Generator<array{0: Bom}>
     */
    public static function bomWithComponentAllHashAlgorithms(): Generator
    {
        yield 'every hash alg' => [(new Bom())->setComponents([
            (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
            ->setHashes(
                array_fill_keys(
                    (new \ReflectionClass(AbstractHashAlgorithm::class))->getConstants(),
                    '12345678901234567890123456789012'
                )
            ),
        ])];
    }

    /**
     * BOMs with every list possible as set from assoc array.
     *
     * @return Generator<array{0: Bom}>
     */
    public static function bomFromAssocLists(): Generator
    {
        yield 'every list from assoc' => [(new Bom())->setComponents([
            'myComponent' => (new Component(AbstractClassification::LIBRARY, 'name', '1.0'))
                ->setLicenses(['myLicense' => new License('some license')]),
        ])];
    }
}
