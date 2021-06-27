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
use CycloneDX\Core\Models\License\AbstractDisjunctiveLicense;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use DomainException;

/**
 * @author jkowalleck
 */
class DisjunctiveLicenseTransformer extends AbstractTransformer
{
    use NullAssertionTrait;

    /**
     * @throws DomainException
     *
     * @psalm-return array{'license': array<string, mixed>}
     */
    public function transform(AbstractDisjunctiveLicense $license): array
    {
        if ($license instanceof DisjunctiveLicenseWithId) {
            $id = $license->getId();
            $name = null;
        } elseif ($license instanceof DisjunctiveLicenseWithName) {
            $id = null;
            $name = $license->getName();
        } else {
            throw new DomainException('Missing id and name for license');
        }

        return ['license' => array_filter(
            [
                'id' => $id,
                'name' => $name,
                'url' => $license->getUrl(),
            ],
            [$this, 'isNotNull']
        )];
    }
}
