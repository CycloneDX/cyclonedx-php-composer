<?php

namespace CycloneDX\Specs;

/**
 * @author jkowalleck
 *
 * @internal
 */
trait SupportsTrait
{
    public function isSupportedComponentType(string $classification): bool
    {
        return in_array($classification, self::COMPONENT_TYPES, true);
    }

    /**
     * @psalm-return list<string>
     */
    public function getSupportedComponentTypes(): array
    {
        return self::COMPONENT_TYPES;
    }

    public function isSupportedHashAlgorithm(string $alg): bool
    {
        return in_array($alg, self::HASH_ALGORITHMS, true);
    }

    /**
     * @psalm-return list<string>
     */
    public function getSupportedHashAlgorithms(): array
    {
        return self::HASH_ALGORITHMS;
    }

    public function isSupportedHashContent(string $content): bool
    {
        return false !== preg_match(self::HASH_CONTENT_REGEX, $content);
    }
}
