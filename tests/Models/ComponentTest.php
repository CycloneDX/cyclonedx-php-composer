<?php

namespace CycloneDX\Tests\Models;

use CycloneDX\Models\Component;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Models\Component
 */
class ComponentTest extends TestCase
{
    /** @var Component */
    private $component;

    public function setUp(): void
    {
        parent::setUp();

        $this->component = new Component(Component::TYPE_LIBRARY, 'name', 'version');
    }

    public function testSetTypeWithUnknownValue(): void
    {
        $this->expectException(\DomainException::class);
        $this->component->setType('something unknown');
        $this->component->setType('');
    }

    public function testPackageUrlWithGroup(): void
    {
        $name = uniqid('name', false);
        $group = uniqid('group', false);
        $version = uniqid('1.0+', false);
        $this->component
            ->setName($name)
            ->setGroup($group)
            ->setVersion($version);
        self::assertEquals(
            "pkg:composer/{$group}/{$name}@{$version}",
            $this->component->getPackageUrl()
        );
    }

    public function testPackageUrlWithoutGroup(): void
    {
        $name = uniqid('name', false);
        $version = uniqid('1.0+', false);
        $this->component
            ->setName($name)
            ->setGroup(null)
            ->setVersion($version);
        self::assertEquals(
            "pkg:composer/{$name}@{$version}",
            $this->component->getPackageUrl()
        );
    }
}
