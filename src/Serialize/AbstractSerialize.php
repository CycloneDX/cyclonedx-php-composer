<?php

namespace CycloneDX\Serialize;

use CycloneDX\Specs\SpecInterface;

/**
 * @author jkowalleck
 */
abstract class AbstractSerialize
{
    // region spec

    /**
     * @var SpecInterface
     */
    protected $spec;

    public function getSpec(): SpecInterface
    {
        return $this->spec;
    }

    /**
     * @return $this
     */
    public function setSpec(SpecInterface $spec): self
    {
        $this->spec = $spec;

        return $this;
    }

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    // endregion spec
}