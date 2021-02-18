<?php

namespace CycloneDX\Tests\unit\Composer;

use CycloneDX\Composer\BomGenerator;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BomGeneratorTest.
 *
 * @covers \CycloneDX\Composer\BomGenerator
 */
class BomGeneratorTest extends TestCase
{

    /** @var BomGenerator  */
    private $bomGenerator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OutputInterface
     * @psalm-var \PHPUnit\Framework\MockObject\MockObject&OutputInterface
     */
    private $outputMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomGenerator = new BomGenerator($this->outputMock);
    }

    // region getPackagesFromLock

    /**
     * @dataProvider LockProvider
     *
     * @param array<string, mixed> $lock
     * @param array<string, mixed> $expected
     */
    public function testGetPackagesFromLock(array $lock, bool $excludeDev, array $expected): void
    {
        /* @see BomGenerator::getPackagesFromLock() */
        $getPackagesFromLock = (new ReflectionClass(BomGenerator::class))->getMethod('getPackagesFromLock');
        $getPackagesFromLock->setAccessible(true);

        if ($excludeDev) {
            $this->outputMock
                ->expects(self::once())
                ->method('writeln')
                ->with(self::matchesRegularExpression('/dev dependencies will be skipped/i'));
        }

        $packages = $getPackagesFromLock->invoke($this->bomGenerator, $lock, $excludeDev);
        self::assertEquals($expected, $packages);
    }

    /**
     * @return Generator<array{array, bool, array}>
     */
    public function LockProvider(): Generator
    {
        $packages = [];
        $packagesDev = [];

        yield 'both, includeDev' => [
            ['packages' => $packages, 'packages-dev' => $packagesDev],
            false,
            $packages,
        ];
        yield 'packagesDev, includeDev' => [
            ['packages-dev' => $packagesDev],
            false,
            [],
        ];
        yield 'both, excludeDev' => [
            ['packages' => $packages, 'packages-dev' => $packagesDev],
            true,
            array_merge($packages, $packagesDev),
        ];
        yield 'packages, excludeDev' => [
            ['packages' => $packages],
            true,
            $packages,
        ];
        yield 'packagesDev, excludeDev' => [
            ['packages-dev' => $packagesDev],
            true,
            $packagesDev,
        ];
    }

    // endregion getPackagesFromLock

    // region filterOutPlugins

    /**
     * @dataProvider packageProvider
     *
     * @param array<string, mixed> $notPlugins
     * @param array<string, mixed> $plugins
     */
    public function testFilterOutPlugins(array $notPlugins, array $plugins): void
    {
        $packages = array_merge($notPlugins, $plugins);

        /* @see BomGenerator::filterOutPlugins() */
        $filterOutPlugins = (new ReflectionClass(BomGenerator::class))->getMethod('filterOutPlugins');
        $filterOutPlugins->setAccessible(true);

        foreach ($plugins as ['name' => $pluginName]) {
            $this->outputMock
                ->expects(self::once())
                ->method('writeln')
                ->with(self::matchesRegularExpression('/Skipping plugin .*'.preg_quote($pluginName, '/').'/i'));
        }

        $filtered = iterator_to_array($filterOutPlugins->invoke($this->bomGenerator, $packages));
        self::assertEquals($notPlugins, $filtered);
    }

    /**
     * @return Generator<array{array, array}>
     */
    public function packageProvider(): Generator
    {
        $notPlugins = [
            ['type' => 'library', 'name' => 'acme/library'],
        ];
        $plugins = [
            ['type' => 'composer-plugin', 'name' => 'acme/plugin'],
        ];

        yield 'non-plugins only' => [$notPlugins, []];
        yield 'plugins only' => [[], $plugins];
        yield 'both' => [$notPlugins, $plugins];
    }

    // endregion filterOutPlugins

    // region normalizeVersion

    /**
     * @dataProvider versionProvider
     */
    public function testNormalizeVersion(string $version, string $expected): void
    {
        /* @see BomGenerator::normalizeVersion() */
        $normalizeVersion = (new ReflectionClass(BomGenerator::class))->getMethod('normalizeVersion');
        $normalizeVersion->setAccessible(true);
        $normalized = $normalizeVersion->invoke($this->bomGenerator, $version);
        self::assertEquals($expected, $normalized);
    }

    /**
     * @return Generator<array{string, string}>
     */
    public function versionProvider(): Generator
    {
        yield ['1.0.0', '1.0.0'];
        yield ['v1.0.0', '1.0.0'];
        yield ['dev-master', 'dev-master'];
    }

    // endregion normalizeVersion

    // region splitLicenses

    public function testReadLicensesWithLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('MIT');
        self::assertEquals(['MIT'], $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('(MIT or Apache-2.0)');
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    public function testReadLicensesWithConjunctiveLicenseString(): void
    {
        $licenses = $this->bomGenerator->splitLicenses('(MIT and Apache-2.0)');
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    public function testReadLicensesWithDisjunctiveLicenseArray(): void
    {
        $licenses = $this->bomGenerator->splitLicenses(['MIT', 'Apache-2.0']);
        self::assertEquals(['MIT', 'Apache-2.0'], $licenses);
    }

    // endregion splitLicenses
}
