<?php

namespace CycloneDX\Tests\unit\Repositories;

use CycloneDX\Models\Component;
use CycloneDX\Repositories\ComponentRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Repositories\ComponentRepository
 */
class ComponentRepositoryTest extends TestCase
{

    public function testAddAndGetComponent(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $component3 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1);
        $repo->addComponent($component2, $component3);
        $got = $repo->getComponents();

        self::assertCount(3, $got);
        self::assertContains($component1, $got);
        self::assertContains($component2, $got);
        self::assertContains($component3, $got);
    }

    public function testCount(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1);
        $repo->addComponent($component2);

        self::assertSame(2, $repo->count());
    }

    public function testConstructAndGet(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1, $component2);
        $got = $repo->getComponents();

        self::assertCount(2, $got);
        self::assertContains($component1, $got);
        self::assertContains($component2, $got);
    }
}
