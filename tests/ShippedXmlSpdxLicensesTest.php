<?php

/** @noinspection PhpUnhandledExceptionInspection */

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

    public function test(): void
    {
        self::assertFileExists($this->file);


        $json = file_get_contents($this->file);
        self::assertJson($json);

        $options = 0;

        if (defined('JSON_THROW_ON_ERROR')) {
            $options |= JSON_THROW_ON_ERROR;
        }

        $licenses = json_decode($json, false, 512, $options);
        self::assertIsArray($licenses);
        self::assertNotEmpty($licenses);

        foreach ($licenses as $license) {
            self::assertIsString($license);
        }
    }
}
