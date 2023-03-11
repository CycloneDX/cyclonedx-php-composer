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
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Composer;

/**
 * CDX properties' names and well-known values - specific to this very tool.
 *
 * Based on {@link https://github.com/CycloneDX/cyclonedx-property-taxonomy/blob/main/cdx/composer.md `cdx:composer` Namespace Taxonomy}
 *
 * @internal
 *
 * @author jkowalleck
 */
abstract class Properties
{
    public const Name_PackageType = 'cdx:composer:package:type';
    public const Name_DevRequirement = 'cdx:composer:package:isDevRequirement';

    public const Name_SourceReference = 'cdx:composer:package:sourceReference';
    public const Name_DistReference = 'cdx:composer:package:distReference';

    public const Value_True = 'true';
    public const Value_False = 'false';
}
