<?php

namespace CycloneDX\Tests\integration\Spdx;

use CycloneDX\Spdx\License;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ShippedLicensesTest extends TestCase
{
    /**
     * @psalm-var string
     */
    private $file;

    /**
     * @retrun void
     */
    protected function setUp(): void
    {
        $this->file = License::getResourcesFile();
    }

    /**
     * @throws \JsonException
     */
    public function test(): void
    {
        self::assertFileExists($this->file);

        $json = file_get_contents($this->file);
        self::assertIsString($json);
        self::assertJson($json);

        $licenses = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($licenses);
        self::assertNotEmpty($licenses);

        foreach ($licenses as $license) {
            self::assertIsString($license);
        }
    }
}
