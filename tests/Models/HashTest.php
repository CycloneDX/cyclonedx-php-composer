<?php

namespace CycloneDX\Tests\Models;

use CycloneDX\Models\Hash;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Models\Hash
 */
class HashTest extends TestCase
{
    public function testSetAlg(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/unknown algorithm/i');
        new Hash('something unknown', '12345678901234567890123456789012');
    }
}
