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
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;

/**
 * transform data models to JSON.
 *
 * @author jkowalleck
 */
class JsonSerializer implements SerializerInterface
{
    use HasSpecTrait;

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    /**
     * Serialize a Bom to JSON.
     *
     * @throws DomainException if something was not supported
     */
    public function serialize(Bom $bom): string
    {
        $options = \JSON_THROW_ON_ERROR | \JSON_PRESERVE_ZERO_FRACTION | \JSON_PRETTY_PRINT;

        $json = json_encode(
            (new NormalizerFactory($this->spec))
                ->makeForBom()
                ->normalize($bom),
            $options
        );
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is set

        return $json;
    }
}
