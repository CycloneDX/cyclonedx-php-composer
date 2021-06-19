<?php

namespace CycloneDX\Repositories;

use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;

class DisjunctiveLicenseRepository
{

    /**
     * @psalm-var list<DisjunctiveLicense>
     */
    private $licenses = [];

    public function addLicense(DisjunctiveLicense ...$licenses): self
    {
        array_push($this->licenses, ...array_values($licenses));
        return $this;
    }

    /**
     * @psalm-return list<DisjunctiveLicense>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

}
