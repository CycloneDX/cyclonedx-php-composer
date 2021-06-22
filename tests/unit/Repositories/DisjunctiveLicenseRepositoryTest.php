<?php

namespace CycloneDX\Tests\unit\Repositories;

use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Repositories\DisjunctiveLicenseRepository
 */
class DisjunctiveLicenseRepositoryTest extends TestCase
{

    public function testAddAndGetLicense(): void
    {
        $license1 = $this->createStub(DisjunctiveLicense::class);
        $license2 = $this->createStub(DisjunctiveLicense::class);
        $license3 = $this->createStub(DisjunctiveLicense::class);
        $repo = new DisjunctiveLicenseRepository($license1);

        $repo->addLicense($license2, $license3);

        $got = $repo->getLicenses();

        self::assertCount(3, $got);
        self::assertContains($license1, $got);
        self::assertContains($license2, $got);
        self::assertContains($license3, $got);
    }

    public function testConstructAndGet(): void
    {
        $license1 = $this->createStub(DisjunctiveLicense::class);
        $license2 = $this->createStub(DisjunctiveLicense::class);
        $repo = new DisjunctiveLicenseRepository($license1, $license2);
        $got = $repo->getLicenses();

        self::assertCount(2, $got);
        self::assertContains($license1, $got);
        self::assertContains($license2, $got);
    }

    public function testCount(): void
    {
        $license1 = $this->createStub(DisjunctiveLicense::class);
        $license2 = $this->createStub(DisjunctiveLicense::class);
        $repo = new DisjunctiveLicenseRepository($license1);
        $repo->addLicense($license2);

        self::assertSame(2, $repo->count());
    }
}
