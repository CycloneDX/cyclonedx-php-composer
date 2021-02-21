<?php

declare(strict_types=1);

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

namespace CycloneDX\Serialize;

use CycloneDX\Helpers\SimpleDomTrait;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Specs\Spec10;
use CycloneDX\Specs\Spec11;
use CycloneDX\Specs\Spec12;
use DomainException;
use DOMDocument;
use DOMElement;
use DOMException;
use Generator;
use RuntimeException;

/**
 * Transform data models to XML.
 *
 * @TODO see which parts need to be normalized!
 *
 * @author jkowalleck
 */
class XmlSerializer extends AbstractSerialize implements SerializerInterface
{
    use SimpleDomTrait;

    // region SerializerInterface

    /**
     * @throws DOMException
     * @throws DomainException  if a component's type is unsupported
     * @throws RuntimeException if spec version is not supported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        if (false === ($this->spec instanceof Spec10 || $this->spec instanceof Spec11 || $this->spec instanceof Spec12)) {
            throw new RuntimeException('unsupported spec version');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->appendChild($this->bomToDom($document, $bom));

        $document->formatOutput = $pretty;
        $xml = $document->saveXML(null, LIBXML_NOEMPTYTAG);
        if (false === $xml) {
            throw new DOMException('Failed to serialize to XML.');
        }

        return $xml;
    }

    /**
     * @throws DomainException when a component's type is unsupported
     */
    public function bomToDom(DOMDocument $document, Bom $bom): DOMElement
    {
        $element = $document->createElementNS(
            'http://cyclonedx.org/schema/bom/'.$this->spec->getVersion(),
            'bom'
        );
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
    public function componentToDom(DOMDocument $document, Component $component): DOMElement
    {
        $type = $component->getType();
        if (false === $this->spec->isSupportedComponentType($type)) {
            throw new DomainException("Unsupported component type: {$type}");
        }

        $purl = $component->getPackageUrl();

        $element = $document->createElement('component');
        $this->simpleDomSetAttributes($element, [
            'type' => $type,
        ]);
        $this->simpleDomAppendChildren($element, [
            // publisher
            $this->simpleDomSafeTextElement($document, 'group', $component->getGroup()),
            $this->simpleDomSafeTextElement($document, 'name', $component->getName()),
            $this->simpleDomSafeTextElement($document, 'version', $component->getVersion()),
            $this->simpleDomSafeTextElement($document, 'description', $component->getDescription()),
            // scope
            $this->simpleDomAppendChildren(
                $document->createElement('hashes'),
                $this->hashesToDom($document, $component->getHashes())
            ),
            $this->simpleDomAppendChildren(
                $document->createElement('licenses'),
                $this->simpleDomDocumentMap($document, [$this, 'licenseToDom'], $component->getLicenses())
            ),
            // copyright
            // cpe <-- DEPRECATED in latest spec
            $this->simpleDomSafeTextElement($document, 'purl', $purl ? (new PackageUrl())->serialize($purl) : null),
            // modified
            // pedigree
            // externalReferences
            // components
        ]);

        return $element;
    }

    /**
     * @psalm-param array<string, string> $hashes
     *
     * @psalm-return Generator<DOMElement>
     */
    public function hashesToDom(DOMDocument $document, array $hashes): Generator
    {
        foreach ($hashes as $algorithm => $content) {
            try {
                yield $this->hashToDom($document, $algorithm, $content);
            } catch (DomainException $exception) {
                trigger_error("skipped hash: {$exception->getMessage()} ({$algorithm}, {$content})", E_USER_WARNING);
                unset($exception);
            }
        }
    }

    /**
     * @throws DomainException if hash is not supported by spec. Code 1: algorithm unsupported Code 2:  content unsupported
     */
    public function hashToDom(DOMDocument $document, string $algorithm, string $content): DOMElement
    {
        if (false === $this->spec->isSupportedHashAlgorithm($algorithm)) {
            throw new DomainException('invalid algorithm', 1);
        }
        if (false === $this->spec->isSupportedHashContent($content)) {
            throw new DomainException('invalid content', 2);
        }

        $element = $this->simpleDomSafeTextElement($document, 'hash', $content);
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
            $this->simpleDomSafeTextElement($document, 'id', $license->getId()),
            $this->simpleDomSafeTextElement($document, 'name', $license->getName()),
            $this->simpleDomSafeTextElement($document, 'url', $license->getUrl()),
        ]);

        return $element;
    }

    // endregion SerializerInterface
}
