<?php

namespace CycloneDX\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use Generator;

/**
 * @author jkowalleck
 *
 * @internal
 */
trait SimpleDomTrait
{
    /**
     * @psalm-param iterable<string, mixed|null> $attributes
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
     * @psalm-param int|float|string|null $data
     * @psalm-param bool                  $null whether to return null wgen `$data` is null
     */
    private function simpleDomSaveTextElement(DOMDocument $document, string $name, $data, bool $null = true): ?DOMElement
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
     * @psalm-param iterable<scalar, mixed> $items
     *
     * @psalm-return Generator<mixed>
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
     * An iterator that ignores everything but {@see DOMElement}s.
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
