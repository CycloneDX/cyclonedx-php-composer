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

namespace CycloneDX\Core\Serialize\JsonTransformer;

use CycloneDX\Core\Helpers\NullAssertionTrait;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use DomainException;
use PackageUrl\PackageUrl;

/**
 * @author jkowalleck
 */
class ComponentTransformer extends AbstractTransformer
{
    use NullAssertionTrait;

    /**
     * @throws DomainException
     *
     * @psalm-return array<string, mixed>
     */
    public function transform(Component $component): array
    {
        $name = $component->getName();
        $group = $component->getGroup();
        $version = $component->getVersion();

        $type = $component->getType();
        if (false === $this->getFactory()->getSpec()->isSupportedComponentType($type)) {
            $reportFQN = "$group/$name@$version";
            throw new DomainException("Component '$reportFQN' has unsupported type: $type");
        }

        return array_filter(
            [
                'type' => $type,
                'name' => $name,
                'version' => $version,
                'group' => $group,
                'description' => $component->getDescription(),
                'licenses' => $this->transformLicense($component->getLicense()),
                'hashes' => $this->transformHashes($component->getHashRepository()),
                'purl' => $this->transformPurl($component->getPackageUrl()),
            ],
            [$this, 'isNotNull']
        );
    }

    /**
     * @param LicenseExpression|DisjunctiveLicenseRepository|null $license
     */
    private function transformLicense($license): ?array
    {
        if ($license instanceof LicenseExpression) {
            return [$this->getFactory()->makeForLicenseExpression()->transform($license)];
        }

        if ($license instanceof DisjunctiveLicenseRepository) {
            return 0 === \count($license)
                ? null
                : $this->getFactory()->makeForDisjunctiveLicenseRepository()->transform($license);
        }

        return null;
    }

    public function transformHashes(?HashRepository $hashes): ?array
    {
        return null === $hashes || 0 === \count($hashes)
            ? null
            : $this->getFactory()->makeForHashRepository()->transform($hashes);
    }

    private function transformPurl(?PackageUrl $purl): ?string
    {
        return null === $purl
            ? null
            : (string) $purl;
    }
}
