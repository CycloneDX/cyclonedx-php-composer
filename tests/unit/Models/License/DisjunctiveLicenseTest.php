<?php

namespace CycloneDX\Tests\unit\Models\License;

use CycloneDX\Models\License\DisjunctiveLicense;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Models\License\DisjunctiveLicense
 */
class DisjunctiveLicenseTest extends TestCase
{

    public function testCreateWithIdAndGetId()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(true);
        $spdxLicenseValidator->method('getLicense')->willReturn('bar');
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        self::assertSame('bar', $license->getId());
        self::assertNull($license->getName());
    }

    public function testCreateWithnameAndGetName()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        self::assertSame('foo', $license->getName());
        self::assertNull($license->getId());
    }

    public function testSetAndGetUrl()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        $license->setUrl('http://foo.bar/baz');
        self::assertSame('http://foo.bar/baz', $license->getUrl());
    }

    public function testSetUrlThrowsOnWrongFormat()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/invalid url/i');

        $license->setUrl('foobar');
    }

}
