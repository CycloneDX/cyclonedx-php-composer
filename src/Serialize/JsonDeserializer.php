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

namespace CycloneDX\Serialize;

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use Generator;
use InvalidArgumentException;
use JsonException;

/**
 * transform JSON to data models.
 *
 * @author jkowalleck
 */
class JsonDeserializer extends AbstractSerialize implements DeserializerInterface
{
    // region DeserializerInterface

    /**
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function deserialize(string $data): Bom
    {
        // @TODO validate schema?
        $json = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        if (false === is_array($json)) {
            throw new InvalidArgumentException('does not deserialize to expected structure');
        }

        return $this->bomFromJson($json);
    }

    /**
     * @param array<string, mixed> $json
     */
    public function bomFromJson(array $json): Bom
    {
        return (new Bom())
            ->setVersion($json['version'] ?? 1)
            ->setComponents(
                array_map(
                    [$this, 'componentFromJson'],
                    $json['components'] ?? []
                )
            );
    }

    /**
     * @param array<string, mixed> $json
     */
    public function componentFromJson(array $json): Component
    {
        return (new Component($json['type'], $json['name'], $json['version']))
            ->setGroup($json['group'] ?? null)
            ->setDescription($json['description'] ?? null)
            ->setLicenses(iterator_to_array($this->licensesFromJson($json['licenses'] ?? [])))
            ->setHashes(iterator_to_array($this->hashesFromJson($json['hashes'] ?? [])));
    }

    /**
     * @param array<array<string, mixed>> $json
     *
     * @return Generator<License>
     */
    public function licensesFromJson(array $json): Generator
    {
        foreach ($json as $license) {
            if (isset($license['license'])) {
                yield $this->licenseFromJson($license['license']);
            } elseif (isset($license['expression'])) {
                // @TOD implement a model for LicenseExpression
                yield $this->licenseFromJson($license['expression']);
            }
        }
    }

    /**
     * @param array<string, mixed> $json
     */
    public function licenseFromJson(array $json): License
    {
        return (new License($json['name'] ?? $json['id']))
            ->setUrl($json['url'] ?? null);
    }

    /**
     * @param array<string, mixed> $json
     *
     * @return Generator<string, string>
     */
    public function hashesFromJson(array $json): Generator
    {
        foreach ($json as ['alg' => $algorithm, 'content' => $content]) {
            yield $algorithm => $content;
        }
    }

    // endregion DeserializerInterface
}