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

namespace CycloneDX\Core\Serialize\JSON;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;

/**
 * @author jkowalleck
 */
class NormalizerFactory
{
    public const FORMAT = Format::JSON;

    /**
     * @var SpecInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $spec;

    /**
     * @throws DomainException when the spec does not support JSON format
     */
    public function __construct(SpecInterface $spec)
    {
        $this->setSpec($spec);
    }

    public function getSpec(): SpecInterface
    {
        return $this->spec;
    }

    /**
     * @throws DomainException when the spec does not support JSON format
     *
     * @return $this
     */
    public function setSpec(SpecInterface $spec): self
    {
        if (false === $spec->isSupportedFormat(self::FORMAT)) {
            throw new DomainException('Unsupported format "'.self::FORMAT.'" for spec '.$spec->getVersion());
        }
        $this->spec = $spec;

        return $this;
    }

    public function makeForBom(): Normalizers\BomNormalizer
    {
        return new Normalizers\BomNormalizer($this);
    }

    public function makeForComponentRepository(): Normalizers\ComponentRepositoryNormalizer
    {
        return new Normalizers\ComponentRepositoryNormalizer($this);
    }

    public function makeForComponent(): Normalizers\ComponentNormalizer
    {
        return new Normalizers\ComponentNormalizer($this);
    }

    public function makeForLicenseExpression(): Normalizers\LicenseExpressionNormalizer
    {
        return new Normalizers\LicenseExpressionNormalizer($this);
    }

    public function makeForDisjunctiveLicenseRepository(): Normalizers\DisjunctiveLicenseRepositoryNormalizer
    {
        return new Normalizers\DisjunctiveLicenseRepositoryNormalizer($this);
    }

    public function makeForDisjunctiveLicense(): Normalizers\DisjunctiveLicenseNormalizer
    {
        return new Normalizers\DisjunctiveLicenseNormalizer($this);
    }

    public function makeForHashRepository(): Normalizers\HashRepositoryNormalizer
    {
        return new Normalizers\HashRepositoryNormalizer($this);
    }

    public function makeForHash(): Normalizers\HashNormalizer
    {
        return new Normalizers\HashNormalizer($this);
    }

    public function makeForMetaData(): Normalizers\MetaDataNormalizer
    {
        return new Normalizers\MetaDataNormalizer($this);
    }

    public function makeForToolRepository(): Normalizers\ToolRepositoryNormalizer
    {
        return new Normalizers\ToolRepositoryNormalizer($this);
    }

    public function makeForTool(): Normalizers\ToolNormalizer
    {
        return new Normalizers\ToolNormalizer($this);
    }

    public function makeForDependencies(): Normalizers\DependenciesNormalizer
    {
        return new Normalizers\DependenciesNormalizer($this);
    }
}
