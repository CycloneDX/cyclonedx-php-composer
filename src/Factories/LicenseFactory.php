<?php

namespace CycloneDX\Factories;

use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Spdx\License as SpdxLicenseValidator;

class LicenseFactory
{

    /** @var SpdxLicenseValidator */
    private $spdxLicenseValidator;

    public function __construct(SpdxLicenseValidator $spdxLicenseValidator)
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;
    }

    public function getSpdxLicenseValidator(): SpdxLicenseValidator
    {
        return $this->spdxLicenseValidator;
    }

    public function setSpdxLicenseValidator(SpdxLicenseValidator $spdxLicenseValidator): self
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;

        return $this;
    }

    /**
     * @return DisjunctiveLicense|LicenseExpression
     */
    public function makeFromString(string $license)
    {
        return $this->isExpression($license)
            ? new LicenseExpression($license)
            : DisjunctiveLicense::createFromNameOrId($license, $this->spdxLicenseValidator);
    }

    private function isExpression(string $license): bool
    {
        // smallest known: (A or B)
        return strlen($license) >= 8
            && $license[0] === '('
            && $license[-1] === ')';
    }
}
