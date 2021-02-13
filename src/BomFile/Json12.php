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

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Specs\Spec12;
use DomainException;
use JsonException;

/**
 * Writes BOMs in JSON format.
 *
 * See {@link https://cyclonedx.org/schema/bom-1.2.schema.json Schema} for format.
 *
 * @author jkowalleck
 */
class Json12 extends Spec12 implements SerializeInterface
{
    /**
     * @param mixed|null $value
     */
    private function isNotNull($value): bool
    {
        return null !== $value;
    }

    /**
     * Serialize a Bom to JSON.
     *
     * @throws JsonException
     * @throws DomainException when a component's type is unsupported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        $options = JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION;
        if ($pretty) {
            $options |= JSON_PRETTY_PRINT;
        }

        return (string) json_encode($this->bomToJson($bom), $options);
    }

    /**
     * @throws DomainException when a component's type is unsupported
     *
     * @return array<string, mixed>
     *
     * @internal
     */
    public function bomToJson(Bom $bom): array
    {
        return [
            'bomFormat' => 'CycloneDX',
            'specVersion' => $this->getSpecVersion(),
            'version' => $bom->getVersion(),
            'components' => array_map(
                [$this, 'componentToJson'],
                $bom->getComponents()
            ),
        ];
    }

    /**
     * @throws DomainException
     *
     * @return array<string, mixed>
     */
    private function componentToJson(Component $component): array
    {
        $type = $component->getType();
        if (false === $this->isSupportedComponentType($type)) {
            throw new DomainException("Unsupported component type: {$type}");
        }

        return array_filter(
            [
                'type' => $type,
                'name' => $component->getName(),
                'version' => $component->getVersion(),
                'group' => $component->getGroup(),
                'description' => $component->getDescription(),
                'licenses' => array_map(
                    [$this, 'licenseToJson'],
                    $component->getLicenses()
                ),
                'hashes' => iterator_to_array($this->hashesToJson($component->getHashes())),
                'purl' => $component->getPackageUrl(),
            ],
            [$this, 'isNotNull']
        );
    }

    /**
     * @return array{license: array<string, mixed>}
     */
    private function licenseToJson(License $license): array
    {
        return [
            'license' => array_filter(
                [
                    'id' => $license->getId(),
                    'name' => $license->getName(),
                    'text' => $license->getText(),
                    'url' => $license->getUrl(),
                ],
                [$this, 'isNotNull']
            ),
        ];
    }

    /**
     * @param array<string, string> $hashes
     *
     * @return \Generator<array{alg: string, content: string}>
     */
    private function hashesToJson(array $hashes): \Generator
    {
        foreach ($hashes as $algorithm => $content) {
            if (false === $this->isSupportedHashAlgorithm($algorithm)) {
                trigger_error("skipped Hash with invalid algorithm: {$algorithm}", E_USER_WARNING);
                continue;
            }
            if (false === $this->isSupportedHashContent($content)) {
                trigger_error("skipped Hash with invalid content: {$content}", E_USER_WARNING);
                continue;
            }
            yield [
                'alg' => $algorithm,
                'content' => $content,
            ];
        }
    }
}
