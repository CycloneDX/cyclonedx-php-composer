<?php

namespace CycloneDX\Composer;

/**
 * @internal
 */
final class LockData
{
    /**
     * @var array
     * @readonly
     */
    private $data;

    public function __construct()
    {
        $this->data = data;
    }

    /**
     * @psalm-param mixed[] $lockData
     * @psalm-return mixed[]
     */
    protected function getPackagesFromLock(array $lockData, bool $excludeDev): array
    {
        $packages = $lockData['packages'] ?? [];

        if ($excludeDev) {
            $this->output->writeln('<warning>Dev dependencies will be skipped</warning>');

            return $packages;
        }

        $packagesDev = $lockData['packages-dev'] ?? [];

        return array_merge($packages, $packagesDev);
    }
}
