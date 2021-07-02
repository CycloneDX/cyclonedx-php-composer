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

namespace CycloneDX\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Helpers\SimpleDomTrait;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\DOM\AbstractNormalizer;
use DomainException;
use DOMElement;
use PackageUrl\PackageUrl;

/**
 * @author jkowalleck
 */
class ComponentNormalizer extends AbstractNormalizer
{
    use SimpleDomTrait;

    /**
     * @throws DomainException
     */
    public function normalize(Component $component): DOMElement
    {
        $name = $component->getName();
        $group = $component->getGroup();
        $version = $component->getVersion();

        $type = $component->getType();
        if (false === $this->getNormalizerFactory()->getSpec()->isSupportedComponentType($type)) {
            $reportFQN = "$group/$name@$version";
            throw new DomainException("Component '$reportFQN' has unsupported type: $type");
        }

        $document = $this->getNormalizerFactory()->getDocument();

        $element = $document->createElement('component');
        $this->simpleDomSetAttributes($element, ['type' => $type]);

        return $this->simpleDomAppendChildren(
            $element,
            [
                // publisher
                $this->simpleDomSafeTextElement($document, 'group', $group),
                $this->simpleDomSafeTextElement($document, 'name', $name),
                $this->simpleDomSafeTextElement($document, 'version', $version),
                $this->simpleDomSafeTextElement($document, 'description', $component->getDescription()),
                // scope
                $this->normalizeHashes($component->getHashRepository()),
                $this->normalizeformLicense($component->getLicense()),
                // copyright
                // cpe <-- DEPRECATED in latest spec
                $this->normalizePurl($component->getPackageUrl()),
                // modified
                // pedigree
                // externalReferences
                // components
            ]
        );
    }

    /**
     * @param LicenseExpression|DisjunctiveLicenseRepository|null $license
     */
    private function normalizeformLicense($license): ?DOMElement
    {
        if ($license instanceof LicenseExpression) {
            return $this->simpleDomAppendChildren(
                $this->getNormalizerFactory()->getDocument()->createElement('licenses'),
                [$this->getNormalizerFactory()->makeForLicenseExpression()->normalize($license)]
            );
        }

        if ($license instanceof DisjunctiveLicenseRepository) {
            return 0 === \count($license)
                ? null
                : $this->simpleDomAppendChildren(
                    $this->getNormalizerFactory()->getDocument()->createElement('licenses'),
                    $this->getNormalizerFactory()->makeForDisjunctiveLicenseRepository()->normalize($license)
                );
        }

        return null;
    }

    private function normalizeHashes(?HashRepository $hashes): ?DOMElement
    {
        return null === $hashes || 0 === \count($hashes)
            ? null
            : $this->simpleDomAppendChildren(
                $this->getNormalizerFactory()->getDocument()->createElement('hashes'),
                $this->getNormalizerFactory()->makeForHashRepository()->normalize($hashes)
            );
    }

    private function normalizePurl(?PackageUrl $purl): ?DOMElement
    {
        return null === $purl
            ? null
            : $this->simpleDomSafeTextElement($this->getNormalizerFactory()->getDocument(), 'purl', (string) $purl);
    }
}
