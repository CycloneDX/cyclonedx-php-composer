<?php

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

    public function testGenerateBom()
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
        $this->assertEquals(2, sizeof($bom->getComponents()));

        $componentNames = array_map(function($component) { return $component->getName(); }, $bom->getComponents());
        $this->assertContains("packageName", $componentNames);
        $this->assertContains("packageNameDev", $componentNames);
    }

    public function testGenerateBomExcludeDev()
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
        $this->assertEquals(1, sizeof($bom->getComponents()));

        $componentNames = array_map(function($component) { return $component->getName(); }, $bom->getComponents());
        $this->assertContains("packageName", $componentNames);
    }

    public function testGenerateBomExcludePlugins()
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
        $this->assertEquals(0, sizeof($bom->getComponents()));
    }

    public function testBuildComponent()
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

        $this->assertEquals("packageName", $component->getName());
        $this->assertEquals("vendorName", $component->getGroup());
        $this->assertEquals("6.6.6", $component->getVersion());
        $this->assertEquals("packageDescription", $component->getDescription());
        $this->assertEquals("library", $component->getType());
        $this->assertEquals(1, sizeof($component->getLicenses()));
        $this->assertContains("MIT", $component->getLicenses());
        $this->assertArrayHasKey("SHA-1", $component->getHashes());
        $this->assertEquals("7e240de74fb1ed08fa08d38063f6a6a91462a815", $component->getHashes()["SHA-1"]);
        $this->assertEquals("pkg:composer/vendorName/packageName@6.6.6", $component->getPackageUrl());
    }

    public function testBuildComponentWithoutVendor()
    {
        $packageData = array(
            "name" => "packageName",
            "version" => "1.0",
        );

        $component = $this->bomGenerator->buildComponent($packageData);

        $this->assertEquals("packageName", $component->getName());
        $this->assertNull($component->getGroup());
        $this->assertEquals("1.0", $component->getVersion());
        $this->assertNull($component->getDescription());
        $this->assertEmpty($component->getLicenses());
        $this->assertEmpty($component->getHashes());
        $this->assertEquals("pkg:composer/packageName@1.0", $component->getPackageUrl());
    }

    public function testBuildComponentWithoutName()
    {
        $packageData = array("version" => "1.0");

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Encountered package without name: {\"version\":\"1.0\"}");

        $this->bomGenerator->buildComponent($packageData);
    }

    public function testBuildComponentWithoutVersion()
    {
        $packageData = array("name" => "vendorName/packageName");

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Encountered package without version: vendorName/packageName");

        $this->bomGenerator->buildComponent($packageData);
    }

    public function testReadLicensesWithLicenseString()
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "MIT"));
        $this->assertEquals(1, sizeof($licenses));
        $this->assertContains("MIT", $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseString()
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "(MIT or Apache-2.0)"));
        $this->assertEquals(2, sizeof($licenses));
        $this->assertContains("MIT", $licenses);
        $this->assertContains("Apache-2.0", $licenses);
    }

    public function testReadLicensesWithConjunctiveLicenseString()
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => "(MIT and Apache-2.0)"));
        $this->assertEquals(2, sizeof($licenses));
        $this->assertContains("MIT", $licenses);
        $this->assertContains("Apache-2.0", $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseArray()
    {
        $licenses = $this->bomGenerator->readLicenses(array("license" => array("MIT", "Apache-2.0")));
        $this->assertEquals(2, sizeof($licenses));
        $this->assertContains("MIT", $licenses);
        $this->assertContains("Apache-2.0", $licenses);
    }
}
