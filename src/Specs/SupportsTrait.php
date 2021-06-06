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

namespace CycloneDX\Specs;

/**
 * @author jkowalleck
 *
 * @internal
 */
trait SupportsTrait
{
    /**
     * @psalm-return Version::V_*
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function isSupportedComponentType(string $classification): bool
    {
        return \in_array($classification, self::COMPONENT_TYPES, true);
    }

    /**
     * @psalm-return list<\CycloneDX\Enums\Classification::*>
     */
    public function getSupportedComponentTypes(): array
    {
        return self::COMPONENT_TYPES;
    }

    public function isSupportedHashAlgorithm(string $alg): bool
    {
        return \in_array($alg, self::HASH_ALGORITHMS, true);
    }

    /**
     * @psalm-return list<\CycloneDX\Enums\HashAlgorithm::*>
     */
    public function getSupportedHashAlgorithms(): array
    {
        return self::HASH_ALGORITHMS;
    }

    public function isSupportedHashContent(string $content): bool
    {
        return 1 === preg_match(self::HASH_CONTENT_REGEX, $content);
    }
}
