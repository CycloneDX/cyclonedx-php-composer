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
use CycloneDX\Core\Helpers\SimpleDomTrait;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialize\DomTransformer\Factory;
use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use DOMDocument;

/**
 * Transform data models to XML.
 *
 * @author jkowalleck
 */
class XmlSerializer implements SerializerInterface
{
    use HasSpecTrait;
    use SimpleDomTrait;

    private const FORMAT = Format::XML;

    private const XML_VERSION = '1.0';
    private const XML_ENCODING = 'UTF-8';

    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec;
    }

    /**
     * @throws DomainException if something was not supported
     */
    public function serialize(Bom $bom, bool $pretty = true): string
    {
        $document = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $document->appendChild(
            $document->importNode(
                (new Factory($this->spec))
                    ->makeForBom()
                    ->transform($bom),
                true
            )
        );

        $document->formatOutput = $pretty;

        // option LIBXML_NOEMPTYTAG might lead to errors in consumers
        $xml = $document->saveXML();
        \assert(false !== $xml);

        return $xml;
    }
}
