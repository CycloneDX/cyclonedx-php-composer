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

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use CycloneDX\Specs\Version;
use DomainException;
use Generator;
use JsonException;
use RuntimeException;

/**
 * transform data models to Json.
 *
 * @author jkowalleck
 */
class JsonSerializer extends AbstractSerialize implements SerializerInterface
{
    // region SerializerInterface

    /**
     * Serialize a Bom to JSON.
     *
     * @throws JsonException
     * @throws DomainException  if a component's type is unsupported
     * @throws RuntimeException if spec version is not supported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        if (version_compare($this->spec->getVersion(), Version::V_1_2, '<')) {
            throw new RuntimeException('Unsupported spec version. requires >= '.Version::V_1_2);
        }

        $options = \JSON_THROW_ON_ERROR | \JSON_PRESERVE_ZERO_FRACTION;
        if ($pretty) {
            $options |= \JSON_PRETTY_PRINT;
        }

        $json = json_encode($this->bomToJson($bom), $options);
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is set

        return $json;
    }

    /**
     * @psalm-param mixed|null $value
     * @psalm-assert-if-true !null $value
     */
    private function isNotNull($value): bool
    {
        return null !== $value;
    }

    /**
     * @throws DomainException when a component's type is unsupported
     *
     * @psalm-return array<string, mixed>
     */
    public function bomToJson(Bom $bom): array
    {
        return [
            'bomFormat' => 'CycloneDX',
            'specVersion' => $this->spec->getVersion(),
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
     * @psalm-return array<string, mixed>
     */
    public function componentToJson(Component $component): array
    {
        $type = $component->getType();
        if (false === $this->spec->isSupportedComponentType($type)) {
            throw new DomainException("Unsupported component type: {$type}");
        }

        $purl = $component->getPackageUrl();

        return array_filter(
            [
                'type' => $type,
                'name' => $component->getName(),
                'version' => $component->getVersion(),
                'group' => $component->getGroup(),
                'description' => $component->getDescription(),
                'licenses' => iterator_to_array($this->licensesToJson($component->getLicenses())) ?: null,
                'hashes' => iterator_to_array($this->hashesToJson($component->getHashes())) ?: null,
                'purl' => $purl ? (string) $purl : null,
            ],
            [$this, 'isNotNull']
        );
    }

    /**
     * @psalm-param License[] $licenses
     *
     * @psalm-return Generator<array{license: array<string, mixed>}>
     */
    public function licensesToJson(array $licenses): Generator
    {
        foreach ($licenses as $license) {
            yield ['license' => $this->licenseToJson($license)];
        }
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function licenseToJson(License $license): array
    {
        return array_filter(
            [
                'id' => $license->getId(),
                'name' => $license->getName(),
                'url' => $license->getUrl(),
            ],
            [$this, 'isNotNull']
        );
    }

    /**
     * @psalm-param array<string, string> $hashes
     *
     * @psalm-return Generator<array{alg: string, content: string}>
     */
    public function hashesToJson(array $hashes): Generator
    {
        foreach ($hashes as $algorithm => $content) {
            try {
                yield $this->hashToJson($algorithm, $content);
            } catch (DomainException $exception) {
                // skipped unsupported hash
                unset($exception);
            }
        }
    }

    /**
     * @throws DomainException if hash is not supported by spec. Code 1: algorithm unsupported Code 2:  content unsupported
     *
     * @psalm-return array{alg: string, content: string}
     */
    public function hashToJson(string $algorithm, string $content): array
    {
        if (false === $this->spec->isSupportedHashAlgorithm($algorithm)) {
            throw new DomainException('invalid algorithm', 1);
        }
        if (false === $this->spec->isSupportedHashContent($content)) {
            throw new DomainException('invalid content', 2);
        }

        return [
            'alg' => $algorithm,
            'content' => $content,
        ];
    }

    // endregion SerializerInterface
}
