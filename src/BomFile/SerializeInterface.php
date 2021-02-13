<?php

namespace CycloneDX\BomFile;

use CycloneDX\Models\Bom;

interface SerializeInterface
{
    /**
     * Serialize a Bom to a string.
     *
     * May throw implementation-dependent Exceptions.
     *
     * @param Bom  $bom    The BOM to serialize
     * @param bool $pretty pretty print
     */
    public function serialize(Bom $bom, bool $pretty = false): string;
}
