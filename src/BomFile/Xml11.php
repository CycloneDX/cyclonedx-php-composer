<?php

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

namespace CycloneDX\BomFile;

use CycloneDX\Helpers\SimpleDomTrait;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Specs\Spec11;
use DomainException;
use DOMDocument;
use DOMElement;
use RuntimeException;

/**
 * Writes BOMs in XML format.
 *
 * @author jkowalleck
 */
class Xml11 extends Spec11 implements SerializeInterface
{
    use SimpleDomTrait;

    public const NS_URL = 'http://cyclonedx.org/schema/bom/1.1';

    /**
     * @throws RuntimeException when serialization to string failed
     * @throws DomainException  when a component's type is unsupported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->appendChild($this->bomToDom($document, $bom));

        $document->formatOutput = $pretty;
        $saved = $document->saveXML(null, LIBXML_NOEMPTYTAG);
        if (false === $saved) {
            throw new RuntimeException('Failed to serialize to XML.');
        }

        return $saved;
    }

    /**
     * @internal
     *
     * @throws DomainException when a component's type is unsupported
     */
    public function bomToDom(DOMDocument $document, Bom $bom): DOMElement
    {
        $element = $document->createElementNS(self::NS_URL, 'bom');
        $this->simpleDomSetAttributes($element, [
            'version' => $bom->getVersion(),
            // serialNumber
        ]);
        $this->simpleDomAppendChildren($element, [
            $this->simpleDomAppendChildren(
                $document->createElement('components'),
                $this->simpleDomDocumentMap($document, [$this, 'componentToDom'], $bom->getComponents())
            ),
            // externalReferences
        ]);

        return $element;
    }

    /**
     * @throws DomainException when type is unsupported
     */
    private function componentToDom(DOMDocument $document, Component $component): DOMElement
    {
        $type = $component->getType();
        if (false === $this->isSupportedComponentType($type)) {
            throw new DomainException("Unsupported component type: {$type}");
        }

        $element = $document->createElement('component');
        $this->simpleDomSetAttributes($element, [
            'type' => $type,
        ]);
        $this->simpleDomAppendChildren($element, [
            // publisher
            $this->simpleDomSaveTextElement($document, 'group', $component->getGroup()),
            $this->simpleDomSaveTextElement($document, 'name', $component->getName()),
            $this->simpleDomSaveTextElement($document, 'version', $component->getVersion()),
            $this->simpleDomSaveTextElement($document, 'description', $component->getDescription()),
            // skope
            $this->simpleDomAppendChildren(
                $document->createElement('hashes'),
                $this->simpleDomDocumentMap($document, [$this, 'hashToDom'], $component->getHashes())),
            $this->simpleDomAppendChildren(
                $document->createElement('licenses'),
                $this->simpleDomDocumentMap($document, [$this, 'licenseToDom'], $component->getLicenses())
            ),
            // copyright
            // cpe <-- DEPRECATED
            $this->simpleDomSaveTextElement($document, 'purl', $component->getPackageUrl()),
            // modified
            // pedigree
            // externalReferences
            // components
        ]);

        return $element;
    }

    private function hashToDom(DOMDocument $document, string $content, string $algorithm): ?DOMElement
    {
        if (false === $this->isSupportedHashAlgorithm($algorithm)) {
            trigger_error("skipped Hash with invalid algorithm: {$algorithm}", E_USER_WARNING);

            return null;
        }
        if (false === $this->isSupportedHashContent($content)) {
            trigger_error("skipped Hash with invalid content: {$content}", E_USER_WARNING);

            return null;
        }
        $element = $this->simpleDomSaveTextElement($document, 'hash', $content);
        assert(null !== $element);
        $this->simpleDomSetAttributes($element, [
            'alg' => $algorithm,
        ]);

        return $element;
    }

    private function licenseToDom(DOMDocument $document, License $license): DOMElement
    {
        $element = $document->createElement('license');
        $this->simpleDomAppendChildren($element, [
            $this->simpleDomSaveTextElement($document, 'id', $license->getId()),
            $this->simpleDomSaveTextElement($document, 'name', $license->getName()),
            $this->simpleDomSaveTextElement($document, 'url', $license->getUrl()),
            $this->simpleDomSaveTextElement($document, 'text', $license->getText()),
        ]);

        return $element;
    }
}
