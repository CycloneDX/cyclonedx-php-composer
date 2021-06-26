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

namespace CycloneDX\Core\Serialize;

use CycloneDX\Core\Helpers\HasSpecTrait;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\DisjunctiveLicense;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Core\Spec\Version;
use DomainException;
use RuntimeException;

/**
 * transform data models to Json.
 *
 * @author jkowalleck
 */
class JsonSerializer implements SerializerInterface
{
    use HasSpecTrait;

    private const BOM_FORMAT = 'CycloneDX';

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    // region SerializerInterface

    /**
     * Serialize a Bom to JSON.
     *
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
     * @param mixed|null $value
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
            'bomFormat' => self::BOM_FORMAT,
            'specVersion' => $this->spec->getVersion(),
            'version' => $bom->getVersion(),
            'components' => $this->componentsToJson($bom->getComponentRepository()),
        ];
    }

    public function componentsToJson(ComponentRepository $components): array
    {
        return 0 === \count($components)
            ? []
            : array_map(
                [$this, 'componentToJson'],
                $components->getComponents()
            );
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
            throw new DomainException("Unsupported component type: $type");
        }

        $purl = $component->getPackageUrl();

        return array_filter(
            [
                'type' => $type,
                'name' => $component->getName(),
                'version' => $component->getVersion(),
                'group' => $component->getGroup(),
                'description' => $component->getDescription(),
                'licenses' => $this->licenseToJson($component->getLicense()),
                'hashes' => $this->hashesToJson($component->getHashRepository()),
                'purl' => $purl ? (string) $purl : null,
            ],
            [$this, 'isNotNull']
        );
    }

    /**
     * @psalm-param null|LicenseExpression|DisjunctiveLicenseRepository $license
     */
    public function licenseToJson($license): ?array
    {
        if (null === $license) {
            return null;
        }

        if ($license instanceof LicenseExpression) {
            return [$this->licenseExpressionToJson($license)];
        }

        return 0 === \count($license)
            ? null
            : array_map([$this, 'disjunctiveLicenseToJson'], $license->getLicenses());
    }

    /**
     * @psalm-return array{'expression': string}
     */
    private function licenseExpressionToJson(LicenseExpression $license): array
    {
        return ['expression' => $license->getExpression()];
    }

    /**
     * @psalm-return array{'license': array<string, mixed>}
     */
    private function disjunctiveLicenseToJson(DisjunctiveLicense $license): array
    {
        return ['license' => array_filter(
            [
                'id' => $license->getId(),
                'name' => $license->getName(),
                'url' => $license->getUrl(),
            ],
            [$this, 'isNotNull']
        )];
    }

    /**
     * @psalm-return list<array{alg: string, content: string}>
     */
    public function hashesToJson(?HashRepository $hashes): ?array
    {
        if (null === $hashes) {
            return null;
        }

        $list = [];
        foreach ($hashes->getHashes() as $algorithm => $content) {
            try {
                $list[] = $this->hashToJson($algorithm, $content);
            } catch (DomainException $exception) {
                // skipped unsupported hash
                unset($exception);
            }
        }

        return 0 === \count($list)
            ? null
            : $list;
    }

    /**
     * @throws DomainException if hash is not supported by spec. Code 1: algorithm unsupported Code 2:  content unsupported
     *
     * @psalm-return array{alg: string, content: string}
     */
    public function hashToJson(string $algorithm, string $content): array
    {
        if (false === $this->spec->isSupportedHashAlgorithm($algorithm)) {
            throw new DomainException("invalid hash algorithm: $algorithm", 1);
        }
        if (false === $this->spec->isSupportedHashContent($content)) {
            throw new DomainException("invalid hash content: $content", 2);
        }

        return [
            'alg' => $algorithm,
            'content' => $content,
        ];
    }

    // endregion SerializerInterface
}
