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
use CycloneDX\Specs\Spec10;
use CycloneDX\Specs\Spec11;
use CycloneDX\Specs\Spec12;
use CycloneDX\Specs\SpecInterface;
use DomainException;
use DOMDocument;
use DOMElement;
use DOMException;
use Generator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Writes BOMs in XML format.
 *
 * @author jkowalleck
 */
class Xml extends AbstractFile
{
    use SimpleDomTrait;

    /**
     * @var string
     */
    private $namespaceUrl;

    public function getNamespaceUrl(): string
    {
        return $this->namespaceUrl;
    }

    public function __construct(SpecInterface $spec)
    {
        parent::__construct($spec);
        $this->namespaceUrl = 'http://cyclonedx.org/schema/bom/'.$spec->getVersion();
    }

    // region Serialize

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
     * @internal
     *
     * @throws DomainException when a component's type is unsupported
     */
    public function bomToDom(DOMDocument $document, Bom $bom): DOMElement
    {
        $element = $document->createElementNS(
            $this->getNamespaceUrl(),
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
     *
     * @internal
     */
    public function componentToDom(DOMDocument $document, Component $component): DOMElement
    {
        $type = $component->getType();
        if (false === $this->spec->isSupportedComponentType($type)) {
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
            $this->simpleDomSaveTextElement($document, 'purl', $component->getPackageUrl()),
            // modified
            // pedigree
            // externalReferences
            // components
        ]);

        return $element;
    }

    /**
     * @param array<string, string> $hashes
     *
     * @return Generator<DOMElement>
     */
    private function hashesToDom(DOMDocument $document, array $hashes): Generator
    {
        foreach ($hashes as $algorithm => $content) {
            try {
                yield $this->hashToDom($document, $algorithm, $content);
            } catch (DomainException $ex) {
                trigger_error("skipped hash: {$ex->getMessage()} ({$algorithm}, {$content})", E_USER_WARNING);
                unset($ex);
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

    // endregion Serialize

    // region Deserialize

    /**
     * @throws InvalidArgumentException
     */
    public function deserialize(string $data): Bom
    {
        $dom = new DOMDocument();
        // @TODO add NOBLANKS ? see if all tests still pass
        $options = LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET;
        if (defined('LIBXML_COMPACT')) {
            $options |= LIBXML_COMPACT;
        }
        if (defined('LIBXML_PARSEHUGE')) {
            $options |= LIBXML_PARSEHUGE;
        }
        $loaded = $dom->loadXML($data, $options);
        if (false === $loaded || null === $dom->documentElement) {
            throw new InvalidArgumentException('does not deserialize to expected structure');
        }

        // @TODO normalize

        return $this->bomFromDom($dom->documentElement);
    }

    public function bomFromDom(DOMElement $element): Bom
    {
        $bom = new Bom();
        $bom->setVersion((int) $this->simpleDomGetAttribute('version', $element, '1'));
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            if ('components' === $childElement->tagName) {
                $bom->setComponents(array_map(
                    [$this, 'componentFromDom'],
                    iterator_to_array($this->simpleDomGetChildElements($childElement))
                ));
            }
        }

        return $bom;
    }

    public function componentFromDom(DOMElement $element): Component
    {
        $name = $version = null; // essentials
        $group = $description = $licenses = $hashes = null; // non-essentials
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            switch ($childElement->nodeName) {
                case 'name':
                    $name = $childElement->nodeValue;
                    break;
                case 'version':
                    $version = $childElement->nodeValue;
                    break;
                case 'group':
                    $group = $childElement->nodeValue;
                    break;
                case 'licenses':
                    $licenses = iterator_to_array($this->licensesFromDom($childElement));
                    break;
                case 'hashes':
                    $hashes = iterator_to_array($this->hashesFromDom($childElement));
                    break;
            }
        }

        // asserted by SCHEMA
        $type = $element->getAttribute('type');
        assert(null !== $name);
        assert(null !== $version);

        return (new Component($type, $name, $version))
            ->setGroup($group)
            ->setDescription($description)
            ->setLicenses($licenses ?? [])
            ->setHashes($hashes ?? []);
    }

    /**
     * @return Generator<License>
     */
    public function licensesFromDom(DOMElement $element): Generator
    {
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            switch ($childElement->nodeName) {
                case 'license':
                    yield $this->licenseFromDom($childElement);
                    break;
                case 'expression':
                    if ($this->spec->getVersion() >= '1.2') {
                        // @TOD implement a model for LicenseExpression
                        yield new License($element->nodeValue);
                    } else {
                        // @TODO split on LicenseExpression
                        yield new License($element->nodeValue);
                    }
                    break;
            }
        }
    }

    public function licenseFromDom(DOMElement $element): License
    {
        return new License($element->nodeValue);
    }

    /**
     * @return Generator<string, string>
     */
    private function hashesFromDom(DOMElement $element): Generator
    {
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            yield from $this->hashFromDom($childElement);
        }
    }

    /**
     * @return Generator<string, string>
     */
    public function hashFromDom(DOMElement $element): Generator
    {
        yield $element->getAttribute('alg') => $element->nodeValue;
    }

    // endregion Deserialize
}
