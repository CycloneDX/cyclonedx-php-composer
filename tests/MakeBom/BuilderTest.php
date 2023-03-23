<?php

namespace CycloneDX\Tests\MakeBom;

use Composer\Composer;
use CycloneDX\Composer\MakeBom\Builder;
use CycloneDX\Composer\MakeBom\Options;
use PHPUnit\Framework\TestCase;


use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

#[CoversClass(Builder::class)]
final class BuilderTest extends TestCase {

    // region createSbomFromComposer

    public function testCreateSbomFromComposer (): void {
        $builder = new Builder(false, false, null);
        $composer = $this->createMock(Composer::class); // use actual
        $sbom = $builder->createSbomFromComposer($composer);
        self::assertFalse($sbom);
    }

    public function testCreateSbomFromComposerOmittingDev (): void {
        $builder = new Builder(true, false, null);
        $composer = $this->createMock(Composer::class); // use actual
        $sbom = $builder->createSbomFromComposer($composer);
        self::assertFalse($sbom);
    }

    public function testCreateSbomFromComposerOmittingPlugins (): void {
        $builder = new Builder(false, true, null);
        $composer = $this->createMock(Composer::class); // use actual
        $sbom = $builder->createSbomFromComposer($composer);
        self::assertFalse($sbom);
    }

    public function testCreateSbomFromComposerMCVersionOverride (): void {
        $builder = new Builder(false, false, 'v1.0-fake');
        $composer = $this->createMock(Composer::class); // use actual
        $sbom = $builder->createSbomFromComposer($composer);
        self::assertFalse($sbom);
    }

    // endregion createSbomFromComposer

    // region createToolsFromComposer
    public function testCreateToolsFromComposer (): void {
        $composer = $this->createMock(Composer::class); // use actual
        $tools = [...Builder::createToolsFromComposer($composer, null, false)];
        self::assertFalse($tools);
    }

    public function testCreateToolsFromComposerVersionOverride (): void {
        $composer = $this->createMock(Composer::class); // use actual
        $tools = [...Builder::createToolsFromComposer($composer, 'v1.0-fake', false)];
        self::assertFalse($tools);

    }

    public function testCreateToolsFromComposerExcludeLibs (): void {
        $composer = $this->createMock(Composer::class); // use actual
        $tools = [...Builder::createToolsFromComposer($composer, null, true)];
        self::assertFalse($tools);
    }

    // endregion createToolsFromComposer
}
