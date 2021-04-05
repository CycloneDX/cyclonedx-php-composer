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
use CycloneDX\Spdx\XmlLicense;

use Symfony\Component\Console\Output\OutputInterface;
use XMLWriter;

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

    function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * @param Bom $bom The BOM to write
     * @return string The BOM as XML formatted string
     */
    public function writeBom(Bom $bom)
    {
        $xmlWriter = new XMLWriter;
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString("    ");

        $xmlWriter->startDocument("1.0", "utf-8");
        $xmlWriter->startElementNs(null, "bom", "http://cyclonedx.org/schema/bom/1.1");

        $xmlWriter->startElement("components");
        foreach ($bom->getComponents() as $component) {
            $this->writeComponent($xmlWriter, $component);
        }
        $xmlWriter->endElement(); // components

        $xmlWriter->endElement(); // bom
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();
    }

    /**
     * @param XMLWriter $xmlWriter The XMLWriter instance to use
     * @param Component $component The component to write
     */
    private function writeComponent(XMLWriter $xmlWriter, Component $component)
    {
        $xmlWriter->startElement("component");

        $xmlWriter->startAttribute("type");
        $xmlWriter->text($component->getType());
        $xmlWriter->endAttribute();

        if ($component->getGroup()) {
            $this->writeTextElement($xmlWriter, "group", $component->getGroup());
        }
        $this->writeTextElement($xmlWriter, "name", $component->getName());
        $this->writeTextElement($xmlWriter, "version", $component->getVersion());

        if ($component->getDescription()) {
            $this->writeTextElement($xmlWriter, "description", $component->getDescription());
        }

        if ($component->getHashes()) {
            $xmlWriter->startElement("hashes");
            foreach ($component->getHashes() as $hashType => $hashValue) {
                $xmlWriter->startElement("hash");

                $xmlWriter->startAttribute("alg");
                $xmlWriter->text($hashType);
                $xmlWriter->endAttribute();

                $xmlWriter->text($hashValue);
                $xmlWriter->endElement(); // hash
            }
            $xmlWriter->endElement(); // hashes
        }

        if ($component->getLicenses()) {
            $xmlWriter->startElement("licenses");
            $spdxLicense = new XmlLicense();
            foreach ($component->getLicenses() as &$license) {
                $xmlWriter->startElement("license");
                if ($spdxLicense->validate($license)) {
                    $this->writeTextElement($xmlWriter, "id", $spdxLicense->getLicense($license));
                } else {
                    $this->writeTextElement($xmlWriter, "name", $license);
                }
                $xmlWriter->endElement(); // license
            }
            unset($license, $spdxLicense);
            $xmlWriter->endElement(); // licenses
        }

        if ($component->getPackageUrl()) {
            $this->writeTextElement($xmlWriter, "purl", $component->getPackageUrl());
        }

        $xmlWriter->endElement(); // component
    }

    /**
     * @param XMLWriter $xmlWriter The XMLWriter instance to use
     * @param string $elementName Name of the element
     * @param string $elementText Text of the element
     */
    private function writeTextElement(XMLWriter $xmlWriter, $elementName, $elementText)
    {
        $xmlWriter->startElement($elementName);
        $xmlWriter->text($elementText);
        $xmlWriter->endElement();
    }

}
