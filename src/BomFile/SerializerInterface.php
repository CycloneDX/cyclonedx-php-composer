<?php

namespace CycloneDX\BomFile;

use CycloneDX\Models\Bom;

interface SerializerInterface
{
    /**
     * Serialize a Bom to a string.
     *
     * May throw implementation-dependent Exceptions.
     *
     * @param Bom $bom The BOM to serialize
     */
    public function serialize(Bom $bom): string;
}
