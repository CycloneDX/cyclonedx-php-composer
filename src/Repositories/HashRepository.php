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

namespace CycloneDX\Repositories;

use CycloneDX\Enums\HashAlgorithm;
use DomainException;

/**
 * @author jkowalleck
 *
 * @psalm-type Hashes = array<HashAlgorithm::*, string>
 */
class HashRepository implements \Countable
{
    /**
     * Specifies the file hashes of the component.
     *
     * @psalm-var Hashes
     */
    private $hashes = [];

    /**
     * @psalm-assert HashAlgorithm::* $algorithm
     *
     * @throws DomainException if $algorithm is not in {@see \CycloneDX\Enums\HashAlgorithm}'s constants list
     *
     * @return $this
     */
    public function setHash(string $algorithm, string $content): self
    {
        if (false === $this->isValidAlgorithm($algorithm)) {
            throw new DomainException("Unknown hash algorithm: $algorithm");
        }
        $this->hashes[$algorithm] = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetHash(string $algorithm): self
    {
        unset($this->hashes[$algorithm]);

        return $this;
    }

    public function getHash(string $algorithm): ?string
    {
        return $this->hashes[$algorithm] ?? null;
    }

    /** @psalm-return Hashes */
    public function getHashes(): array
    {
        return $this->hashes;
    }

    /**
     * @psalm-assert-if-true HashAlgorithm::* $algorithm
     */
    private function isValidAlgorithm(string $algorithm): bool
    {
        /** @psalm-var  list<HashAlgorithm::*> */
        $algorithms = (new \ReflectionClass(HashAlgorithm::class))->getConstants();

        return \in_array($algorithm, $algorithms, true);
    }

    public function count(): int
    {
        return \count($this->hashes);
    }
}
