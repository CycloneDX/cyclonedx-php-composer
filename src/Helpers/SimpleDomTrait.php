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

namespace CycloneDX\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use Generator;

/**
 * @internal
 *
 * @author jkowalleck
 */
trait SimpleDomTrait
{
    /**
     * @psalm-param iterable<string, scalar|null> $attributes
     */
    private function simpleDomSetAttributes(DOMElement $element, iterable $attributes): DOMElement
    {
        foreach ($attributes as $attName => $attValue) {
            if (null === $attValue) {
                $element->removeAttribute($attName);
            } else {
                $element->setAttribute($attName, (string) $attValue);
            }
        }

        return $element;
    }

    /**
     * @psalm-param iterable<?DOMNode> $children
     */
    private function simpleDomAppendChildren(DOMElement $element, iterable $children): DOMElement
    {
        foreach ($children as $child) {
            if (null !== $child) {
                $element->appendChild($child);
            }
        }

        return $element;
    }

    /**
     * @param mixed|null $data
     * @param bool       $null whether to return null when `$data` is null
     *
     * @return null|DOMElement ($null is true && $data is null ? null : DOMElement)
     */
    private function simpleDomSafeTextElement(DOMDocument $document, string $name, $data, bool $null = true): ?DOMElement
    {
        $element = $document->createElement($name);
        if (null !== $data) {
            $element->appendChild($document->createCDATASection((string) $data));
        } elseif ($null) {
            return null;
        }

        return $element;
    }

    /**
     * @psalm-template TCallbackReturn
     *
     * @psalm-template TIteratorKey as array-key
     * @psalm-template TIteratorItem
     *
     * @psalm-param iterable<TIteratorKey, TIteratorItem> $items
     * @psalm-param callable(DOMDocument, TIteratorItem, TIteratorKey=):TCallbackReturn $callback
     *
     * @psalm-return Generator<TIteratorKey, TCallbackReturn>
     */
    private function simpleDomDocumentMap(DOMDocument $document, callable $callback, iterable $items): Generator
    {
        foreach ($items as $key => $item) {
            yield $key => $callback($document, $item, $key);
        }
    }

    private function simpleDomGetAttribute(string $attribute, DOMElement $element, string $default = ''): string
    {
        return $element->hasAttribute($attribute)
            ? $element->getAttribute($attribute)
            : $default;
    }

    /**
     * An iterator that ignores everything but {@see \DOMElement}s.
     *
     * Needed since `$element->getElementsByTagName()` would also find tags in nested children.
     *
     * @psalm-return Generator<DOMElement>
     */
    private function simpleDomGetChildElements(DOMElement $element): Generator
    {
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                yield $childNode;
            }
        }
    }
}
