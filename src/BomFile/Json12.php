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
use CycloneDX\Models\Hash;
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
     * The spec this Serializer is implementing.
     */
    public const SPEC_VERSION = '1.2';

    private const HASH_ALGORITHMS = [
        Hash::ALG_MD5,
        Hash::ALG_SHA_1,
        Hash::ALG_SHA_256,
        Hash::ALG_SHA_384,
        Hash::ALG_SHA_512,
        Hash::ALG_SHA3_256,
        Hash::ALG_SHA3_512,
        Hash::ALG_BLAKE2B_256,
        Hash::ALG_BLAKE2B_384,
        Hash::ALG_BLAKE2B_512,
        Hash::ALG_BLAKE3,
    ];

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
    }

    /**
     * @param mixed|null $value
     */
    private function filter_notNull($value): bool
    {
        return null !== $value;
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
                    array_map(
                        [$this, 'genHash'],
                        $component->getHashes()
                    ),
                    [$this, 'filter_notNull']
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

    /**
     * @return array<string, mixed>|null
     */
    private function genHash(Hash $hash): ?array
    {
        if (!in_array($hash->getAlg(), self::HASH_ALGORITHMS, true)) {
            return null;
        }

        return [
            'alg' => $hash->getAlg(),
            'content' => $hash->getValue(),
        ];
    }
}
