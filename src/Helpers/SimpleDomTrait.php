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
     * @param iterable<string, mixed|null> $attributes
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
     * @param iterable<?DOMNode> $children
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
     * @param int|float|string|null $data
     * @param bool                  $null whether to return null wgen `$data` is null
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
     * @param iterable<scalar, mixed> $items
     *
     * @return Generator<mixed>
     */
    private function simpleDomDocumentMap(DOMDocument $document, callable $callback, iterable $items): Generator
    {
        foreach ($items as $key => $item) {
            yield $key => $callback($document, $item, $key);
        }
    }
}
