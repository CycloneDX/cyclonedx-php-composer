<?php

namespace CycloneDX\Tests\uni\Models;

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * Class BomTest.
 *
 * @covers \CycloneDX\Models\Bom
 */
class BomTest extends TestCase
{
    /** @var Bom */
    private $bom;

    public function setUp(): void
    {
        parent::setUp();

        $this->bom = new Bom();
    }

    /**
     * @param Component[] $components
     *
     * @dataProvider componentDataProvider()
     */
    public function testComponentsSetterGetter(array $components): void
    {
        $this->bom->setComponents($components);
        self::assertEquals($components, $this->bom->getComponents());
    }

    /**
     * @return Generator<array{array<Component>}>
     */
    public function componentDataProvider(): Generator
    {
        yield 'empty' => [[]];
        yield 'some' => [[$this->createMock(Component::class)]];
    }

    public function testVersionSetterGetter(): void
    {
        $version = random_int(1, 255);
        $this->bom->setVersion($version);
        self::assertSame($version, $this->bom->getVersion());
    }

    public function testVersionSetterInvalidValue(): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(\DomainException::class);
        $this->bom->setVersion($version);
    }
}
