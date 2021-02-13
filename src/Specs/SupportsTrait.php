<?php

namespace CycloneDX\Specs;

/**
 * @author jkowalleck
 *
 * @internal
 */
trait SupportsTrait
{
    public function getSpecVersion(): string
    {
        return self::VERSION;
    }

    public function isSupportedHashAlgorithm(string $alg): bool
    {
        return in_array($alg, self::HASH_ALGORITHMS, true);
    }

    public function isSupportedComponentType(string $classification): bool
    {
        return in_array($classification, self::COMPONENT_TYPES, true);
    }

    public function isSupportedHashContent(string $content): bool
    {
        return false !== preg_match(self::HASH_CONTENT_REGEX, $content);
    }
}
