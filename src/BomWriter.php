<?php

namespace CycloneDX;

use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;

class BomWriter 
{
    public function writeBom(Bom $bom) 
    {
        $writer = \xmlwriter_open_memory();
        \xmlwriter_set_indent($writer, 2);

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

    private function writeComponent($writer, Component $component) 
    {
        \xmlwriter_start_element($writer, "component");

        if ($component->getType()) {
            \xmlwriter_start_attribute($writer, "type");
            \xmlwriter_text($writer, $component->getType());
            \xmlwriter_end_attribute($writer);
        }

        if ($component->getGroup()) {
            $this->writeTextElement($writer, "group", $component->getGroup());
        }

        $this->writeTextElement($writer, "name", $component->getName());
        $this->writeTextElement($writer, "version", $component->getVersion());

        if ($component->getDescription()) {
            $this->writeTextElement($writer, "description", $component->getDescription());
        }

        if ($component->getLicenses()) {
            \xmlwriter_start_element($writer, "licenses");
            foreach ($component->getLicenses() as &$license) {
                $this->writeTextElement($writer, "license", $license);
            }
            \xmlwriter_end_element($writer);
        }

        if ($component->getHashes()) {
            \xmlwriter_start_element($writer, "hashes");
            foreach ($component->getHashes() as $hashType => $hashValue) {
                \xmlwriter_start_element($writer, "hash");
                \xmlwriter_start_attribute($writer, "algo");
                \xmlwriter_text($writer, $hashType);
                \xmlwriter_end_attribute($writer);
                \xmlwriter_text($writer, $hashValue);
                \xmlwriter_end_element($writer);
            }
            \xmlwriter_end_element($writer);
        }

        if ($component->getPackageUrl()) {
            $this->writeTextElement($writer, "purl", $component->getPackageUrl());
        }

        \xmlwriter_end_element($writer);
    }

    private function writeTextElement($writer, string $elementName, string $elementText)
    {
        \xmlwriter_start_element($writer, $elementName);
        \xmlwriter_text($writer, $elementText);
        \xmlwriter_end_element($writer);
    }

}
