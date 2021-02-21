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
use DOMDocument;
use DOMElement;
use Generator;
use InvalidArgumentException;

/**
 * transform XML to data models.
 *
 * @author jkowalleck
 */
class XmlDeserializer extends AbstractSerialize implements DeserializerInterface
{
    use SimpleDomTrait;

    // region DeserializerInterface

    /**
     * @throws \DOMException    if XML cannot be loaded
     * @throws \DomainException if version <= 0
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
        if (false === $loaded) {
            throw new \DOMException('does not deserialize to expected structure');
        }

        // @TODO normalize

        return $this->bomFromDom($dom->documentElement);
    }

    /**
     * @throws \DomainException if version <= 0
     */
    public function bomFromDom(DOMElement $element): Bom
    {
        $bom = new Bom();
        $bom->setVersion((int) $this->simpleDomGetAttribute('version', $element, '1'));
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            if ('components' === $childElement->tagName) {
                $bom->addComponent(...array_map(
                    [$this, 'componentFromDom'],
                    iterator_to_array($this->simpleDomGetChildElements($childElement))
                ));
            }
        }

        return $bom;
    }

    /**
     * @throws \DomainException         if any of component's hashes' keys is not in {@see HashAlgorithm}'s constants list
     * @throws InvalidArgumentException if any of component's hashes' values is not a string
     */
    public function componentFromDom(DOMElement $element): Component
    {
        $name = null;
        $version = null;
        $group = null;
        $description = null;
        $hashes = null;
        $licenses = [];
        $purl = null;
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
                case 'description':
                    $description = $childElement->nodeValue;
                    break;
                case 'licenses':
                    $licenses = $this->licensesFromDom($childElement);
                    break;
                case 'hashes':
                    $hashes = $this->hashesFromDom($childElement);
                    break;
                case 'purl':
                    $purl = $childElement->nodeValue;
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
            ->addLicense(...$licenses)
            ->setHashes(null === $hashes ? [] : iterator_to_array($hashes))
            ->setPackageUrl(null === $purl ? null : (new PackageUrl())->deserialize($purl));
    }

    /**
     * @psalm-return Generator<License>
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
                        trigger_error('Found unsupported LicenseExpression. Using License instead', E_USER_NOTICE);
                        yield new License($element->nodeValue);
                    }
                    break;
            }
        }
    }

    /**
     * @throws InvalidArgumentException if URL is invalid
     * @throws \RuntimeException        if loading known SPDX licenses failed
     */
    public function licenseFromDom(DOMElement $element): License
    {
        $nameOrId = null; // essentials
        $url = null; // non-essentials
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            switch ($childElement->nodeName) {
                case 'name':
                case 'id':
                    $nameOrId = $childElement->nodeValue;
                    break;
                case 'url':
                    $url = $childElement->nodeValue;
                    break;
            }
        }

        // asserted by SCHEMA
        assert(null !== $nameOrId);

        return (new License($nameOrId))
            ->setUrl($url);
    }

    /**
     * @psalm-return Generator<string, string>
     */
    public function hashesFromDom(DOMElement $element): Generator
    {
        foreach ($this->simpleDomGetChildElements($element) as $childElement) {
            yield from $this->hashFromDom($childElement);
        }
    }

    /**
     * @psalm-return Generator<string, string>
     */
    public function hashFromDom(DOMElement $element): Generator
    {
        yield $element->getAttribute('alg') => $element->nodeValue;
    }

    // endregion DeserializerInterface
}
