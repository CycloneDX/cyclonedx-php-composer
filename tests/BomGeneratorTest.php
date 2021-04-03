<?php

/** @noinspection PhpUnhandledExceptionInspection */

use CycloneDX\BomGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversNothing
 */
class BomGeneratorTest extends TestCase
{
    /**
     * @var OutputInterface
     */
    private $outputMock;

    /**
     * @var BomGenerator
     */
    private $bomGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomGenerator = new BomGenerator($this->outputMock);
    }

    public function testGenerateBom(): void
    {
        $packages = array(
            array(
                "name" => "vendorName/packageName",
                "version" => "1.0",
                "type" => "library"
            )
        );
        $packagesDev = array(
            array(
                "name" => "vendorNameDev/packageNameDev",
                "version" => "2.0",
                "type" => "library"
            )
        );
        $lockData = array(
            "packages" => $packages,
            "packages-dev" => $packagesDev
        );

        $bom = $this->bomGenerator->generateBom($lockData, false, false);
        self::assertCount(2, $bom->getComponents());

        $componentNames = array_map(static function($component) { return $component->getName(); }, $bom->getComponents());
        self::assertContains("packageName", $componentNames);
        self::assertContains("packageNameDev", $componentNames);
    }

    public function testGenerateBomExcludeDev(): void
    {
        $packages = array(
            array(
                "name" => "vendorName/packageName",
                "version" => "1.0",
                "type" => "library"
            )
        );
        $packagesDev = array(
            array(
                "name" => "vendorNameDev/packageNameDev",
                "version" => "2.0",
                "type" => "library"
            )
        );
        $lockData = array(
            "packages" => $packages,
            "packages-dev" => $packagesDev
        );

        $bom = $this->bomGenerator->generateBom($lockData, true, false);
        self::assertCount(1, $bom->getComponents());

        $componentNames = array_map(static function($component) { return $component->getName(); }, $bom->getComponents());
        self::assertContains("packageName", $componentNames);
    }

    public function testGenerateBomExcludePlugins(): void
    {
        $packages = array(
            array(
                "name" => "vendorName/packageName",
                "version" => "1.0",
                "type" => "composer-plugin"
            )
        );
        $lockData = array(
            "packages" => $packages,
            "packages-dev" => array()
        );

        $bom = $this->bomGenerator->generateBom($lockData, false, true);
        self::assertCount(0, $bom->getComponents());
    }

    public function testBuildComponent(): void
    {
        $packageData = array(
            "name" => "vendorName/packageName",
            "version" => "v6.6.6",
            "description" => "packageDescription",
            "license" => "MIT",
            "dist" => array(
                "shasum" => "7e240de74fb1ed08fa08d38063f6a6a91462a815"
            )
        );

        $component = $this->bomGenerator->buildComponent($packageData);

        self::assertSame("packageName", $component->getName());
        self::assertSame("vendorName", $component->getGroup());
        self::assertSame("6.6.6", $component->getVersion());
        self::assertSame("packageDescription", $component->getDescription());
        self::assertSame("library", $component->getType());
        self::assertCount(1, $component->getLicenses());
        self::assertContains("MIT", $component->getLicenses());
        self::assertArrayHasKey("SHA-1", $component->getHashes());
        self::assertSame("7e240de74fb1ed08fa08d38063f6a6a91462a815", $component->getHashes()["SHA-1"]);
        self::assertSame("pkg:composer/vendorName/packageName@6.6.6", $component->getPackageUrl());
    }

    public function testBuildComponentWithoutVendor(): void
    {
        $packageData = array(
            "name" => "packageName",
            "version" => "1.0",
        );

        $component = $this->bomGenerator->buildComponent($packageData);

        self::assertSame("packageName", $component->getName());
        self::assertNull($component->getGroup());
        self::assertSame("1.0", $component->getVersion());
        self::assertNull($component->getDescription());
        self::assertEmpty($component->getLicenses());
        self::assertEmpty($component->getHashes());
        self::assertSame("pkg:composer/packageName@1.0", $component->getPackageUrl());
    }

    public function testBuildComponentWithoutName(): void
    {
        $packageData = array("version" => "1.0");

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Encountered package without name: {\"version\":\"1.0\"}");

        $this->bomGenerator->buildComponent($packageData);
    }

    public function testBuildComponentWithoutVersion(): void
    {
        $packageData = array("name" => "vendorName/packageName");

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Encountered package without version: vendorName/packageName");

        $this->bomGenerator->buildComponent($packageData);
    }

    public function testReadLicensesWithLicenseString(): void
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "MIT"));
        self::assertCount(1, $licenses);
        self::assertContains("MIT", $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "(MIT or Apache-2.0)"));
        self::assertCount(2, $licenses);
        self::assertContains("MIT", $licenses);
        self::assertContains("Apache-2.0", $licenses);
    }

    public function testReadLicensesWithConjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "(MIT and Apache-2.0)"));
        self::assertCount(2, $licenses);
        self::assertContains("MIT", $licenses);
        self::assertContains("Apache-2.0", $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseArray(): void
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => array("MIT", "Apache-2.0")));
        self::assertCount(2, $licenses);
        self::assertContains("MIT", $licenses);
        self::assertContains("Apache-2.0", $licenses);
    }
}
