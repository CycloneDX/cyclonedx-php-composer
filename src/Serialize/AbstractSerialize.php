<?php

namespace CycloneDX\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Specs\SpecInterface;

/**
 * @internal
 *
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

    public function setSpec(SpecInterface $spec): AbstractSerialize
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
