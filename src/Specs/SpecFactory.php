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

use InvalidArgumentException;

/**
 * @internal
 */
final class SpecFactory
{
    public const VERSION_LATEST = Version::V_1_3;

    /**
     * @psalm-var array<Version::V_*, class-string<SpecInterface>>
     */
    public const SPECS = [
        Version::V_1_0 => Spec10::class,
        Version::V_1_1 => Spec11::class,
        Version::V_1_2 => Spec12::class,
        Version::V_1_3 => Spec13::class,
    ];

    /**
     * @psalm-param Version::V_* $version
     *
     * @throws InvalidArgumentException if version is unknown
     */
    public function make(string $version = self::VERSION_LATEST): SpecInterface
    {
        $class = self::SPECS[$version] ?? null;
        if (null === $class) {
            throw new InvalidArgumentException("Unexpected spec-version value: ${version}");
        }

        return new $class();
    }
}
