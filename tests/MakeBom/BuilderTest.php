<?php

namespace CycloneDX\Tests\MakeBom;

use Composer\Composer;
use Composer\Factory as ComposerFactory;
use Composer\IO\NullIO;
use CycloneDX\Composer\Plugin;
use CycloneDX\Core\Models;

use CycloneDX\Composer\MakeBom\Builder;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;


use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Builder::class)]
#[UsesClass(Plugin::class)]
final class BuilderTest extends TestCase {

    private const TempSetupDir = __DIR__.'/../_tmp/BuilderTest_setup';

    public function testCreateRandomBomSerialNumberHasCorrectFormat (): void {
        $uuid4v1Format = "[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-4[0-9A-Fa-f]{3}-[89AB][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}";
        $actual = Builder::createRandomBomSerialNumber();
        self::assertMatchesRegularExpression("/^urn:uuid:$uuid4v1Format$/", $actual);
    }

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


    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposer (Callable $setup, bool $locked, bool $installed): void {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: dirname($setupManifest));

        $tools = [...Builder::createToolsFromComposer($composer, null, false)];

        $fTools = array_filter(
            $tools,
            static function (Models\Tool $t): bool { return  $t->getVendor() === 'cyclonedx' && $t->getName() === 'cyclonedx-php-composer'; }
        );
        self::assertCount(1, $fTools, 'missing self');

        $fTools = array_filter(
            $tools,
            static function (Models\Tool $t): bool { return  $t->getVendor() === 'cyclonedx' && $t->getName() === 'cyclonedx-library'; }
        );
        self::assertCount(1, $fTools, 'missing library');
    }

    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposerVersionOverride(Callable $setup, bool $locked, bool $installed): void {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: dirname($setupManifest));

        $versionOverride = uniqid('v1.0-fake', true);
        $tools = [...Builder::createToolsFromComposer($composer, $versionOverride, false)];

        $fTools = array_filter(
            $tools,
            static function (Models\Tool $t): bool { return  $t->getVendor() === 'cyclonedx' && $t->getName() === 'cyclonedx-php-composer'; }
        );
        self::assertCount(1, $fTools, 'missing self');
        self::assertSame($versionOverride, $fTools[0]->getVersion());

        $fTools = array_filter(
            $tools,
            static function (Models\Tool $t): bool { return  $t->getVendor() === 'cyclonedx' && $t->getName() === 'cyclonedx-library'; }
        );
        self::assertCount(1, $fTools, 'missing library');
    }

    #[DataProvider('dpCreateToolsFromComposer')]
    public function testCreateToolsFromComposerExcludeLibs (Callable $setup, bool $locked, bool $installed): void {
        $setupManifest = $setup();
        $composer = (new ComposerFactory())->createComposer(new NullIO(), $setupManifest, cwd: dirname($setupManifest));

        $tools = [...Builder::createToolsFromComposer($composer, null, true)];

        $fTools = array_filter(
            $tools,
            static function (Models\Tool $t): bool { return  $t->getVendor() === 'cyclonedx' && $t->getName() === 'cyclonedx-php-composer'; }
        );
        self::assertCount(1, $fTools, 'missing self');

        self::assertCount(1, $tools, 'unexpected other elements');
    }

    /**
     * @psalm-return \Generator<string, array{0:callable():string, 1:bool, 2:bool}>
     */
    public static function dpCreateToolsFromComposer(): \Generator {
        $setupManifest = __DIR__ . '/../_data/setup/testCreateToolsFromComposer/composer.json';
        $setupLock = __DIR__ . '/../_data/setup/testCreateToolsFromComposer/composer.lock';

        yield 'locked NotInstalled' => [
            static fn() => $setupManifest
            ,true, false
        ];

        // !! TempDir is intentionally not cleared, to allow after-test debugging
        @mkdir(self::TempSetupDir, recursive: true);

        $tempDir = tempnam(self::TempSetupDir, 'notLocked_notInstalled_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json")
                    ? "$tempDir/composer.json"
                    : throw new \UnexpectedValueException("setup failed: $tempDir")
            , false, false
        ];

        $tempDir = tempnam(self::TempSetupDir, 'locked_installed_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json") &&
                copy($setupLock, "$tempDir/composer.lock") &&
                false !== shell_exec('composer -d ' . escapeshellarg($tempDir).' install --no-interaction --no-progress -q')
                    ? "$tempDir/composer.json"
                    : throw new \UnexpectedValueException("setup failed: $tempDir")
            , false, false
        ];


        $tempDir = tempnam(self::TempSetupDir, 'notLocked_installed_');
        yield basename($tempDir) => [
            static fn () => unlink($tempDir) &&
                mkdir($tempDir, recursive: true) &&
                copy($setupManifest, "$tempDir/composer.json") &&
                copy($setupLock, "$tempDir/composer.lock") &&
                false !== shell_exec('composer -d ' . escapeshellarg($tempDir).' install --no-interaction -q') &&
                unlink("$tempDir/composer.lock")
                    ? "$tempDir/composer.json"
                    : throw new \UnexpectedValueException("setup failed: $tempDir")
            , true, true
        ];
    }

    // endregion createToolsFromComposer
}
