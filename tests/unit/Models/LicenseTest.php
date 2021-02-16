<?php

namespace CycloneDX\Tests\uni\Models;

use CycloneDX\Models\License;
use PHPUnit\Framework\TestCase;

/**
 * Class LicenseTest.
 *
 * @covers \CycloneDX\Models\License
 *
 * @uses \CycloneDX\Spdx\License
 */
class LicenseTest extends TestCase
{
    /** @var License */
    private $license;

    public function setUp(): void
    {
        parent::setUp();

        $this->license = new License(random_bytes(255));
    }

    public function testWithId(): void
    {
        $id = 'MIT';
        $this->license->setNameOrId($id);
        self::assertEquals($id, $this->license->getId());
        self::assertNull($this->license->getName());
    }

    public function testWithName(): void
    {
        $name = 'some non-SPDX license name';
        $this->license->setNameOrId($name);
        self::assertEquals($name, $this->license->getName());
        self::assertNull($this->license->getId());
    }
}
