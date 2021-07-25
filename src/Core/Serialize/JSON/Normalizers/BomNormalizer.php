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

use CycloneDX\Core\Helpers\NullAssertionTrait;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Serialize\JSON\AbstractNormalizer;

/**
 * @author jkowalleck
 */
class BomNormalizer extends AbstractNormalizer
{
    use NullAssertionTrait;

    private const BOM_FORMAT = 'CycloneDX';

    public function normalize(Bom $bom): array
    {
        $factory = $this->getNormalizerFactory();

        return array_filter(
            [
                'bomFormat' => self::BOM_FORMAT,
                'specVersion' => $factory->getSpec()->getVersion(),
                'version' => $bom->getVersion(),
                'metadata' => $this->normalizeMetaData($bom->getMetaData()),
                'components' => $factory->makeForComponentRepository()->normalize($bom->getComponentRepository()),
            ],
            [$this, 'isNotNull']
        );
    }

    private function normalizeMetaData(?MetaData $metaData): ?array
    {
        if (null === $metaData) {
            return null;
        }

        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsMetaData()) {
            return null;
        }

        $data = $factory->makeForMetaData()->normalize($metaData);

        return empty($data)
            ? null
            : $data;
    }
}
