<?php


use CycloneDX\Spdx\XmlLicense;
use PHPUnit\Framework\TestCase;


/**
 * @coversNothing
 */
class ShippedXmlSpdxLicensesTest extends TestCase
{
    /**
     * @var string
     */
    private $file;

    /**
     * @retrun void
     */
    public function setUp(): void
    {
        $this->file = XmlLicense::getResourcesFile();
    }

    /**
     * @return void
     */
    public function test()
    {
        $this->assertFileExists($this->file);


        $json = file_get_contents($this->file);
        $this->assertJson($json);

        $options = 0;

        if (defined('JSON_THROW_ON_ERROR')) {
            $options |= JSON_THROW_ON_ERROR;
        }

        $licenses = json_decode($json, false, 512, $options);
        $this->assertIsArray($licenses);
        $this->assertNotEmpty($licenses);

        foreach ($licenses as &$license) {
            $this->assertIsString($license);
        }
    }
}
