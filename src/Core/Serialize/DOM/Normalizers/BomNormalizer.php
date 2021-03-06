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
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\DOM\AbstractNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class BomNormalizer extends AbstractNormalizer
{
    use SimpleDomTrait;

    private const XML_NAMESPACE_PATTERN = 'http://cyclonedx.org/schema/bom/%s';

    public function normalize(Bom $bom): DOMElement
    {
        $document = $this->getNormalizerFactory()->getDocument();

        $element = $document->createElementNS(
            sprintf(self::XML_NAMESPACE_PATTERN, $this->getNormalizerFactory()->getSpec()->getVersion()),
            'bom' // no namespace = default NS - so children w/o NS fall under this NS
        );
        $this->simpleDomSetAttributes(
            $element,
            [
                'version' => $bom->getVersion(),
                // serialNumber
            ]
        );
        $this->simpleDomAppendChildren(
            $element,
            [
                $this->normalizeComponents($bom->getComponentRepository()),
                // externalReferences
            ]
        );

        return $element;
    }

    private function normalizeComponents(ComponentRepository $components): DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return $this->simpleDomAppendChildren(
            $factory->getDocument()->createElement('components'),
            $factory->makeForComponentRepository()->normalize($components)
        );
    }
}
