<?php

namespace CycloneDX\Repositories;

use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;

/**
 * @psalm-type License = DisjunctiveLicense|LicenseExpression
 * @psalm-type Licenses = list<License>
 */
class LicenseRepository {

    /** @psalm-var Licenses */
    private $licenses = [];

    /**
     * @psalm-param  list<DisjunctiveLicense> $licenses
     * @return $this
     */
    public function addLicenseExpression(LicenseExpression ...$licenses): self
    {
        array_push($this->licenses, ...$licenses);
        return $this;
    }

    /**
     * @psalm-param  list<DisjunctiveLicense> $licenses
     * @return $this
     */
    public function addDisjunctiveLicense(DisjunctiveLicense ...$licenses): self
    {
        array_push($this->licenses, ...$licenses);
        return $this;
    }

    /**
     * @psalm-return Licenses
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

}
