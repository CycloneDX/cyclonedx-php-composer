<?php

namespace CycloneDX\Tests\functional\Composer;

use CycloneDX\Composer\BomGenerator;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Class BomGeneratorTest.
 *
 * @coversNothing
 */
class BomGeneratorTest extends TestCase
{
    /**
     * @psalm-var BomGenerator
     */
    private $bomGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $outputMock = $this->createMock(OutputInterface::class);
        $this->bomGenerator = new BomGenerator($outputMock);
    }

    private static function getComponentsNames(Component $component): string
    {
        return $component->getName();
    }

    public function testGenerateBom(): void
    {
        $packages = [
            [
                'name' => 'vendorName/packageName',
                'version' => '1.0',
                'type' => 'library',
            ],
        ];
        $packagesDev = [
            [
                'name' => 'vendorNameDev/packageNameDev',
                'version' => '2.0',
                'type' => 'library',
            ],
        ];
        $lockData = [
            'packages' => $packages,
            'packages-dev' => $packagesDev,
        ];

        $bom = $this->bomGenerator->generateBom($lockData, false, false);

        $componentNames = array_map([self::class, 'getComponentsNames'], $bom->getComponents());
        self::assertEquals(['packageName', 'packageNameDev'], $componentNames);
    }

    public function testGenerateBomExcludeDev(): void
    {
        $packages = [
            [
                'name' => 'vendorName/packageName',
                'version' => '1.0',
                'type' => 'library',
            ],
        ];
        $packagesDev = [
            [
                'name' => 'vendorNameDev/packageNameDev',
                'version' => '2.0',
                'type' => 'library',
            ],
        ];
        $lockData = [
            'packages' => $packages,
            'packages-dev' => $packagesDev,
        ];

        $bom = $this->bomGenerator->generateBom($lockData, true, false);

        $componentNames = array_map([self::class, 'getComponentsNames'], $bom->getComponents());
        self::assertEquals(['packageName'], $componentNames);
    }

    public function testGenerateBomExcludePlugins(): void
    {
        $packages = [
            [
                'name' => 'vendorName/packageName',
                'version' => '1.0',
                'type' => 'composer-plugin',
            ],
        ];
        $lockData = [
            'packages' => $packages,
            'packages-dev' => [],
        ];

        $bom = $this->bomGenerator->generateBom($lockData, false, true);
        self::assertEmpty($bom->getComponents());
    }

    public function testBuildComponent(): void
    {
        $packageData = [
            'name' => 'vendorName/packageName',
            'version' => 'v6.6.6',
            'description' => 'packageDescription',
            'license' => 'MIT',
            'dist' => [
                'shasum' => '7e240de74fb1ed08fa08d38063f6a6a91462a815',
            ],
        ];

        $component = $this->bomGenerator->buildComponent($packageData);

        self::assertEquals('packageName', $component->getName());
        self::assertEquals('vendorName', $component->getGroup());
        self::assertEquals('6.6.6', $component->getVersion());
        self::assertEquals('packageDescription', $component->getDescription());
        self::assertEquals('library', $component->getType());
        self::assertEquals([new License('MIT')], $component->getLicenses());
        self::assertEquals([HashAlgorithm::SHA_1 => '7e240de74fb1ed08fa08d38063f6a6a91462a815'], $component->getHashes());
        self::assertEquals('pkg:composer/vendorName/packageName@6.6.6', $component->getPackageUrl());
    }

    public function testBuildComponentWithoutVendor(): void
    {
        $packageData = [
            'name' => 'packageName',
            'version' => '1.0',
        ];

        $component = $this->bomGenerator->buildComponent($packageData);

        self::assertEquals('packageName', $component->getName());
        self::assertNull($component->getGroup());
        self::assertEquals('1.0', $component->getVersion());
        self::assertNull($component->getDescription());
        self::assertEmpty($component->getLicenses());
        self::assertEmpty($component->getHashes());
        self::assertEquals('pkg:composer/packageName@1.0', $component->getPackageUrl());
    }

    public function testBuildComponentWithoutName(): void
    {
        $packageData = ['version' => '1.0'];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encountered package without name: {"version":"1.0"}');

        $this->bomGenerator->buildComponent($packageData);
    }

    public function testBuildComponentWithoutVersion(): void
    {
        $packageData = ['name' => 'vendorName/packageName'];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encountered package without version: vendorName/packageName');

        $this->bomGenerator->buildComponent($packageData);
    }
}
