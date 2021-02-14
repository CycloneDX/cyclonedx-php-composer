<?php

namespace CycloneDX\Serialize;

use CycloneDX\Models\Bom;

/**
 * @author jkowalleck
 */
interface SerializerInterface
{
    /**
     * Serialize a Bom to a string.
     *
     * May throw {@see \RuntimeException} if spec version is not supported.
     * May throw additional implementation-dependent Exceptions.
     *
     * @param Bom  $bom    The BOM to serialize
     * @param bool $pretty pretty print
     */
    public function serialize(Bom $bom, bool $pretty = false): string;
}
