<?php

namespace CycloneDX\BomFile;

use CycloneDX\Specs\SpecInterface;

abstract class AbstractFile
{
    /**
     * @var SpecInterface
     */
    private $spec;

    public function getSpec(): SpecInterface
    {
        return $this->spec;
    }

    public function setSpec(SpecInterface $spec): AbstractFile
    {
        $this->spec = $spec;

        return $this;
    }

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }
}
