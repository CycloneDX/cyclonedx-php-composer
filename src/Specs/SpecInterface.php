<?php

namespace CycloneDX\Specs;

/**
 * @author jkowalleck
 */
interface SpecInterface
{
    public function getVersion(): string;

    // region Supports

    public function isSupportedHashAlgorithm(string $alg): bool;

    public function isSupportedComponentType(string $classification): bool;

    public function isSupportedHashContent(string $content): bool;

    // endregion Supports
}
