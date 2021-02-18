<?php

namespace CycloneDX\Specs;

/**
 * @author jkowalleck
 */
interface SpecInterface
{
    public function getVersion(): string;

    // region Supports

    public function isSupportedComponentType(string $classification): bool;

    /**
     * @psalm-return list<string>
     */
    public function getSupportedComponentTypes(): array;

    public function isSupportedHashAlgorithm(string $alg): bool;

    /**
     * @psalm-return list<string>
     */
    public function getSupportedHashAlgorithms(): array;

    public function isSupportedHashContent(string $content): bool;

    // endregion Supports
}
