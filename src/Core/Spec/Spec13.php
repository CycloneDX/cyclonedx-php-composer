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

namespace CycloneDX\Core\Spec;

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Enums\HashAlgorithm;

/**
 * @author jkowalleck
 */
final class Spec13 implements SpecInterface
{
    use SupportsTrait;

    // region SupportsTrait

    private const VERSION = Version::V_1_3;

    private const COMPONENT_TYPES = [
        Classification::APPLICATION,
        Classification::FRAMEWORK,
        Classification::LIBRARY,
        Classification::OPERATING_SYSTEMS,
        Classification::DEVICE,
        Classification::FILE,
        Classification::CONTAINER,
        Classification::FIRMWARE,
    ];

    private const HASH_ALGORITHMS = [
        HashAlgorithm::MD5,
        HashAlgorithm::SHA_1,
        HashAlgorithm::SHA_256,
        HashAlgorithm::SHA_384,
        HashAlgorithm::SHA_512,
        HashAlgorithm::SHA3_256,
        HashAlgorithm::SHA3_384,
        HashAlgorithm::SHA3_512,
        HashAlgorithm::BLAKE2B_256,
        HashAlgorithm::BLAKE2B_384,
        HashAlgorithm::BLAKE2B_512,
        HashAlgorithm::BLAKE3,
    ];

    private const HASH_CONTENT_REGEX = '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/';

    // endregion SupportsTrait
}
