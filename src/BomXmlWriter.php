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

namespace CycloneDX;

use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes BOMs in XML format.
 * 
 * @author nscuro
 */
class BomXmlWriter 
{

    /**
     * @var OutputInterface
     */
    private $output;

    function __construct(OutputInterface &$output) {
        $this->output = $output;
    }

    /**
     * @param Bom $bom The BOM to write
     * @return string The BOM as XML formatted string
     */
    public function writeBom(Bom $bom) 
    {
        $writer = \xmlwriter_open_memory();
        \xmlwriter_set_indent($writer, 4);

        \xmlwriter_start_document($writer, "1.0", "UTF-8");
        \xmlwriter_start_element_ns($writer, null, "bom", "http://cyclonedx.org/schema/bom/1.1");

        \xmlwriter_start_element($writer, "components");
        foreach ($bom->getComponents() as &$component) {
            $this->writeComponent($writer, $component);
        }
        \xmlwriter_end_element($writer);

        \xmlwriter_end_element($writer);
        \xmlwriter_end_document($writer);
        return \xmlwriter_output_memory($writer);
    }

    /**
     * @param $writer XMLWriter resource
     * @param Component $component The component to write
     */
    private function writeComponent($writer, Component $component) 
    {
        \xmlwriter_start_element($writer, "component");

        \xmlwriter_start_attribute($writer, "type");
        \xmlwriter_text($writer, $component->getType());
        \xmlwriter_end_attribute($writer);

        if ($component->getGroup()) {
            $this->writeTextElement($writer, "group", $component->getGroup());
        }

        $this->writeTextElement($writer, "name", $component->getName());
        $this->writeTextElement($writer, "version", $component->getVersion());

        if ($component->getDescription()) {
            $this->writeTextElement($writer, "description", $component->getDescription());
        }

        if ($component->getHashes()) {
            \xmlwriter_start_element($writer, "hashes");
            foreach ($component->getHashes() as $hashType => $hashValue) {
                \xmlwriter_start_element($writer, "hash");
                \xmlwriter_start_attribute($writer, "alg");
                \xmlwriter_text($writer, $hashType);
                \xmlwriter_end_attribute($writer);
                \xmlwriter_text($writer, $hashValue);
                \xmlwriter_end_element($writer);
            }
            \xmlwriter_end_element($writer);
        }

        if ($component->getLicenses()) {
            \xmlwriter_start_element($writer, "licenses");
            foreach ($component->getLicenses() as &$license) {
                xmlwriter_start_element($writer, "license");
                $this->writeTextElement($writer, "id", $license->getId());
                xmlwriter_end_element($writer);
            }
            \xmlwriter_end_element($writer);
        }

        if ($component->getPackageUrl()) {
            $this->writeTextElement($writer, "purl", $component->getPackageUrl());
        }

        \xmlwriter_end_element($writer);
    }

    /**
     * @param $writer XMLWriter resource
     * @param string $elementName Name of the element
     * @param string $elementText Text of the element
     */
    private function writeTextElement($writer, string $elementName, string $elementText)
    {
        \xmlwriter_start_element($writer, $elementName);
        \xmlwriter_text($writer, $elementText);
        \xmlwriter_end_element($writer);
    }

}
