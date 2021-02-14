<?php

namespace CycloneDX\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Specs\SpecInterface;

/**
 * @internal
 *
 * @author jkowalleck
 */
interface DeserializerInterface
{
    /**
     * Deserialize a Bom to a string.
     *
     * May throw {@see \RuntimeException} if spec version is not supported.
     * May throw additional implementation-dependent Exceptions.
     */
    public function deserialize(string $data): Bom;

}
