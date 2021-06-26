<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Core\Serialize\DomTransformer;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use DOMDocument;

/**
 * @author jkowalleck
 */
class Factory
{
    public const FORMAT = Format::XML;

    /**
     * @var SpecInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $spec;

    /** @var DOMDocument */
    private $document;

    /**
     * @throws DomainException when the spec does not support XML format
     */
    public function __construct(SpecInterface $spec)
    {
        $this->setSpec($spec);
        $this->document = new DOMDocument();
    }

    public function getSpec(): SpecInterface
    {
        return $this->spec;
    }

    /**
     * @throws DomainException when the spec does not support XML format
     *
     * @return $this
     */
    public function setSpec(SpecInterface $spec): self
    {
        if (false === $spec->supportsFormat(self::FORMAT)) {
            throw new DomainException('Unsupported format "'.self::FORMAT.'" for spec '.$spec->getVersion());
        }
        $this->spec = $spec;

        return $this;
    }

    public function getDocument(): DOMDocument
    {
        return $this->document;
    }

    public function makeForBom(): BomTransformer
    {
        return new BomTransformer($this);
    }

    public function makeForComponentRepository(): ComponentRepositoryTransformer
    {
        return new ComponentRepositoryTransformer($this);
    }

    public function makeForComponent(): ComponentTransformer
    {
        return new ComponentTransformer($this);
    }

    public function makeForLicenseExpression(): LicenseExpressionTransformer
    {
        return new LicenseExpressionTransformer($this);
    }

    public function makeForDisjunctiveLicenseRepository(): DisjunctiveLicenseRepositoryTransformer
    {
        return new DisjunctiveLicenseRepositoryTransformer($this);
    }

    public function makeForDisjunctiveLicense(): DisjunctiveLicenseTransformer
    {
        return new DisjunctiveLicenseTransformer($this);
    }

    public function makeForHashRepository(): HashRepositoryTransformer
    {
        return new HashRepositoryTransformer($this);
    }

    public function makeForHash(): HashTransformer
    {
        return new HashTransformer($this);
    }
}
