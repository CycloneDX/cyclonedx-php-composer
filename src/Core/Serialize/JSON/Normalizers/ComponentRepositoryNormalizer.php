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

namespace CycloneDX\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\JSON\AbstractNormalizer;

/**
 * @author jkowalleck
 */
class ComponentRepositoryNormalizer extends AbstractNormalizer
{
    /**
     * @psalm-return list<mixed>
     */
    public function normalize(ComponentRepository $repo): array
    {
        $components = [];

        $normalizer = $this->getNormalizerFactory()->makeForComponent();
        foreach ($repo->getComponents() as $component) {
            try {
                $components[] = $normalizer->normalize($component);
            } catch (\DomainException $exception) {
                continue;
            }
        }

        return $components;
    }
}
