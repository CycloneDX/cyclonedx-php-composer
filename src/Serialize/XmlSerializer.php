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

namespace CycloneDX\Serialize;

use CycloneDX\Helpers\HasSpecTrait;
use CycloneDX\Helpers\SimpleDomTrait;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Spec\SpecInterface;
use DomainException;
use DOMDocument;
use DOMElement;
use DOMException;
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
     * @throws DOMException
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
                $this->simpleDomAppendChildren(
                    $document->createElement('components'),
                    $this->simpleDomDocumentMap($document, [$this, 'componentToDom'], $bom->getComponents())
                ),
                // externalReferences
            ]
        );

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
                $this->hashesToDom($document, $component->getHashes()),
                $this->licensesToDom($document, $component->getLicenses()),
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

    /**
     * @psalm-param array<string, string> $hashes
     */
    public function hashesToDom(DOMDocument $document, array $hashes): ?DOMElement
    {
        $hashElems = [];
        foreach ($hashes as $algorithm => $content) {
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
        $this->simpleDomSetAttributes(
            $element,
            [
                'alg' => $algorithm,
            ]
        );

        return $element;
    }

    /**
     * @param License[] $licenses
     */
    public function licensesToDom(DOMDocument $document, array $licenses): ?DOMElement
    {
        return 0 === \count($licenses)
            ? null
            : $this->simpleDomAppendChildren(
                $document->createElement('licenses'),
                $this->simpleDomDocumentMap($document, [$this, 'licenseToDom'], $licenses)
            );
    }

    public function licenseToDom(DOMDocument $document, License $license): DOMElement
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
