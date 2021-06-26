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

namespace CycloneDX\Core\Serialize\DomTransformer;

use CycloneDX\Core\Helpers\SimpleDomTrait;
use CycloneDX\Core\Repositories\HashRepository;
use DomainException;
use DOMElement;

/**
 * @author jkowalleck
 */
class HashRepositoryTransformer extends AbstractTransformer
{
    use SimpleDomTrait;

    /**
     * @throws DomainException
     *
     * @return DOMElement[]
     * @psalm-return list<DOMElement>
     */
    public function transform(HashRepository $repo): array
    {
        $hashes = $repo->getHashes();

        return array_map(
            [$this->getFactory()->makeForHash(), 'transform'],
            array_keys($hashes),
            array_values($hashes)
        );
    }
}
