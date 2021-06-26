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

namespace CycloneDX\Core\Serialize;

use CycloneDX\Core\Helpers\HasSpecTrait;
use CycloneDX\Core\Helpers\SimpleDomTrait;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\DisjunctiveLicense;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use DOMDocument;
use DOMElement;
use RuntimeException;

/**
 * Transform data models to XML.
 *
 * @author jkowalleck
 */
class XmlSerializer implements SerializerInterface
{
    use HasSpecTrait;
    use SimpleDomTrait;

    private const XML_VERSION = '1.0';
    private const XML_ENCODING = 'UTF-8';

    private const DOC_NAMESPACE_PATH = 'http://cyclonedx.org/schema/bom/';

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    // region SerializerInterface

    /**
     * @throws DomainException  if a component's type is unsupported
     * @throws RuntimeException if spec version is not supported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        $document = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $document->appendChild($this->bomToDom($document, $bom));

        $document->formatOutput = $pretty;
        $xml = $document->saveXML();
        \assert(false !== $xml);

        return $xml;
    }

    /**
     * @throws DomainException when a component's type is unsupported
     */
    public function bomToDom(DOMDocument $document, Bom $bom): DOMElement
    {
        $element = $document->createElementNS(
            self::DOC_NAMESPACE_PATH.$this->spec->getVersion(),
            'bom'
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
                $this->componentsToDom($document, $bom->getComponentRepository()),
                // externalReferences
            ]
        );

        return $element;
    }

    public function componentsToDom(DOMDocument $document, ComponentRepository $components): DOMElement
    {
        $element = $document->createElement('components');

        return 0 === \count($components)
            ? $element
            : $this->simpleDomAppendChildren(
                $element,
                $this->simpleDomDocumentMap($document, [$this, 'componentToDom'], $components->getComponents())
            );
    }

    /**
     * @throws DomainException when type is unsupported
     */
    public function componentToDom(DOMDocument $document, Component $component): DOMElement
    {
        $type = $component->getType();
        if (false === $this->spec->isSupportedComponentType($type)) {
            throw new DomainException("Unsupported component type: $type");
        }

        $purl = $component->getPackageUrl();

        $element = $document->createElement('component');
        $this->simpleDomSetAttributes(
            $element,
            [
                'type' => $type,
            ]
        );
        $this->simpleDomAppendChildren(
            $element,
            [
                // publisher
                $this->simpleDomSafeTextElement($document, 'group', $component->getGroup()),
                $this->simpleDomSafeTextElement($document, 'name', $component->getName()),
                $this->simpleDomSafeTextElement($document, 'version', $component->getVersion()),
                $this->simpleDomSafeTextElement($document, 'description', $component->getDescription()),
                // scope
                $this->hashesToDom($document, $component->getHashRepository()),
                $this->licenseToDom($document, $component->getLicense()),
                // copyright
                // cpe <-- DEPRECATED in latest spec
                $purl ? $this->simpleDomSafeTextElement($document, 'purl', (string) $purl) : null,
                // modified
                // pedigree
                // externalReferences
                // components
            ]
        );

        return $element;
    }

    public function hashesToDom(DOMDocument $document, ?HashRepository $hashes): ?DOMElement
    {
        if (null === $hashes) {
            return null;
        }

        $hashElems = [];
        foreach ($hashes->getHashes() as $algorithm => $content) {
            try {
                $hashElems[] = $this->hashToDom($document, $algorithm, $content);
            } catch (DomainException $exception) {
                // skipped unsupported hash
                unset($exception);
            }
        }

        return 0 === \count($hashElems)
            ? null
            : $this->simpleDomAppendChildren($document->createElement('hashes'), $hashElems);
    }

    /**
     * @throws DomainException if hash is not supported by spec. Code 1: algorithm unsupported Code 2:  content unsupported
     */
    public function hashToDom(DOMDocument $document, string $algorithm, string $content): DOMElement
    {
        if (false === $this->spec->isSupportedHashAlgorithm($algorithm)) {
            throw new DomainException("invalid hash algorithm: $algorithm", 1);
        }
        if (false === $this->spec->isSupportedHashContent($content)) {
            throw new DomainException("invalid hash content: $content", 2);
        }

        $element = $this->simpleDomSafeTextElement($document, 'hash', $content);
        \assert(null !== $element);
        $this->simpleDomSetAttributes($element, ['alg' => $algorithm]);

        return $element;
    }

    /**
     * @psalm-param null|LicenseExpression|DisjunctiveLicenseRepository $license
     */
    public function licenseToDom(DOMDocument $document, $license): ?DOMElement
    {
        if (null === $license) {
            return null;
        }

        $element = $document->createElement('licenses');
        if ($license instanceof LicenseExpression) {
            $element->appendChild($this->licenseExpressionToDom($document, $license));

            return $element;
        }

        return 0 === \count($license)
            ? null
            : $this->simpleDomAppendChildren(
                $element,
                $this->simpleDomDocumentMap($document, [$this, 'disjunctiveLicenseToDom'], $license->getLicenses())
            );
    }

    private function licenseExpressionToDom(DOMDocument $document, LicenseExpression $license): DOMElement
    {
        $element = $this->simpleDomSafeTextElement($document, 'expression', $license->getExpression());
        \assert(null !== $element);

        return $element;
    }

    private function disjunctiveLicenseToDom(DOMDocument $document, DisjunctiveLicense $license): DOMElement
    {
        $element = $document->createElement('license');
        $this->simpleDomAppendChildren(
            $element,
            [
                $this->simpleDomSafeTextElement($document, 'id', $license->getId()),
                $this->simpleDomSafeTextElement($document, 'name', $license->getName()),
                $this->simpleDomSafeTextElement($document, 'url', $license->getUrl()),
            ]
        );

        return $element;
    }

    // endregion SerializerInterface
}
