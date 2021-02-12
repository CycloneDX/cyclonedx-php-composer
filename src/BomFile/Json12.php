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

use CycloneDX\Hash\Algorithm as HashAlgorithm;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;

/**
 * Writes BOMs in JSON format.
 *
 * See {@link https://cyclonedx.org/schema/bom-1.2.schema.json Schema} for format.
 *
 * @author jkowalleck
 */
class Json12 implements SerializerInterface
{
    /**
     * The schema version this Serializer is implementing.
     */
    public const SPEC_VERSION = '1.2';

    /**
     * list of hash algorithms loaded by this schema version.
     *
     * @var string[]
     */
    private $hashAlgorithms;

    public function loadHashAlgorithms(): void
    {
        if (null !== $this->hashAlgorithms) {
            return;
        }

        $this->hashAlgorithms = [
            HashAlgorithm::MD5,
            HashAlgorithm::SHA_1,
            HashAlgorithm::SHA_256,
            HashAlgorithm::SHA_384,
            HashAlgorithm::SHA_512,
            HashAlgorithm::SHA3_256,
            HashAlgorithm::SHA3_512,
            HashAlgorithm::BLAKE2B_256,
            HashAlgorithm::BLAKE2B_384,
            HashAlgorithm::BLAKE2B_512,
            HashAlgorithm::BLAKE3,
        ];
    }

    /**
     * Serialization options.
     *
     * @see https://www.php.net/manual/en/json.constants.php
     *
     * @var int
     */
    private $serializeOptions = 0;

    public function __construct(bool $pretty = true)
    {
        if ($pretty) {
            $this->serializeOptions |= JSON_PRETTY_PRINT;
        }
        $this->loadHashAlgorithms();
    }

    /**
     * @param mixed|null $value
     */
    private function filter_notNull($value): bool
    {
        return null !== $value;
    }

    /**
     * @param mixed|null $value
     */
    private function filter_knownHashAlgorithms($value): bool
    {
        return in_array($value, $this->hashAlgorithms, true);
    }

    /**
     * Serialize a Bom to JSON.
     *
     * @throws \JsonException
     */
    public function serialize(Bom $bom): string
    {
        return (string) json_encode(
            $this->genBom($bom),
            JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | $this->serializeOptions
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function genBom(Bom $bom): array
    {
        return [
            'bomFormat' => 'CycloneDX',
            'specVersion' => self::SPEC_VERSION,
            'version' => $bom->getVersion(),
            'components' => array_map(
                [$this, 'genComponent'],
                $bom->getComponents()
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function genComponent(Component $component): array
    {
        return array_filter(
            [
                'type' => $component->getType(),
                'name' => $component->getName(),
                'version' => $component->getVersion(),
                'group' => $component->getGroup(),
                'description' => $component->getDescription(),
                'licenses' => array_map(
                    [$this, 'genLicense'],
                    $component->getLicenses()
                ),
                'hashes' => array_filter(
                    $component->getHashes(),
                    [$this, 'filter_knownHashAlgorithms']
                ),
                'purl' => $component->getPackageUrl(),
            ],
            [$this, 'filter_notNull']
        );
    }

    /**
     * @return array{license: array<string, mixed>}
     */
    private function genLicense(License $license): array
    {
        return [
            'license' => array_filter(
                [
                    'id' => $license->getId(),
                    'name' => $license->getName(),
                    'text' => $license->getText(),
                    'url' => $license->getUrl(),
                ],
                [$this, 'filter_notNull']
            ),
        ];
    }
}
