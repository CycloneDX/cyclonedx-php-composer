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

/**
 * @author jkowalleck
 */
interface SerializerInterface
{
    /**
     * Serialize a Bom to a string.
     *
     * May throw {@see \RuntimeException} if spec version is not supported.
     * May throw additional implementation-dependent Exceptions.
     *
     * @psalm-param Bom  $bom    The BOM to serialize
     * @psalm-param bool $pretty pretty print
     */
    public function serialize(Bom $bom, bool $pretty = false): string;
}
