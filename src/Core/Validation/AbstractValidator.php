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

namespace CycloneDX\Core\Validation;

use CycloneDX\Core\Helpers\HasSpecTrait;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Core\Spec\Version;

/**
 * @internal
 *
 * @author jkowalleck
 */
abstract class AbstractValidator implements ValidatorInterface
{
    use HasSpecTrait;

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    /**
     * @throws Exceptions\FailedLoadingSchemaException when schema file unknown or not readable
     */
    protected function getSchemaFile(): string
    {
        $specVersion = $this->spec->getVersion();
        $schemaFile = static::listSchemaFiles()[$specVersion] ?? null;
        if (false === \is_string($schemaFile)) {
            throw new Exceptions\FailedLoadingSchemaException("Schema file unknown for specVersion: $specVersion");
        }
        if (is_file($schemaFile) && is_readable($schemaFile)) {
            return realpath($schemaFile);
        }
        // @codeCoverageIgnoreStart
        throw new Exceptions\FailedLoadingSchemaException("Schema file not readable: $schemaFile");
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string[]|null[]
     * @psalm-return array<Version::V_*, ?string>
     */
    abstract protected static function listSchemaFiles(): array;
}
