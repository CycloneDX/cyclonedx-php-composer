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
use CycloneDX\Model\License;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates BOMs based on Composer's lockData.
 * 
 * @author nscuro
 */
class BomGenerator 
{

    /**
     * @var OutputInterface
     */
    private $output;

    function __construct(OutputInterface &$output) 
    {
        $this->output = $output;
    }

    /**
     * @param array $lockData Composer's lockData to generate a BOM for
     * @param bool $excludeDev Exclude Dev dependencies
     * @param bool $excludePlugins Exclude composer plugins
     * @return Bom The resulting BOM
     */
    public function generateBom(array $lockData, bool $excludeDev, bool $excludePlugins)
    {
        $packages = $lockData["packages"];
        $packagesDev = $lockData["packages-dev"];

        if (!$excludeDev) {
            $packages = array_merge($packages, $packagesDev);
        } else {
            $this->output->writeln("<warning>Dev dependencies will be skipped</warning>");
        }

        $components = array();
        foreach ($packages as &$package) {
            if ($package["type"] === "composer-plugin" && $excludePlugins) {
                $this->output->writeln("<warning>Skipping plugin " . $package["name"] . "</warning>");
                continue;
            }

            array_push($components, $this->buildComponent($package));
        }

        return new Bom($components);
    }

    /**
     * @param array $package The lockData's package data to build a component from
     * @return Component The resulting component
     */
    public function buildComponent(array $package)
    {
        $component = new Component;

        // Composer requires published packages to be named like <vendor>/<packageName>.
        // Because this is a loose requirement that doesn't apply to "internal" packages,
        // we need to consider that the vendor name may be omitted.
        // See https://getcomposer.org/doc/04-schema.md#name
        $splittedName = \explode("/", $package["name"], 2);
        $splittedNameCount = count($splittedName);
        if ($splittedNameCount == 2) {
            $component->setGroup($splittedName[0]);
            $component->setName($splittedName[1]);
        } else if ($splittedNameCount == 1) {
            $component->setName($splittedName[0]);
        } else {
            throw new Exception("Encountered package without name");
        }

        $component->setVersion($this->normalizeVersion($package["version"]));

        if (array_key_exists("description", $package) && $package["description"]) {
            $component->setDescription($package["description"]);
        }
        
        // https://getcomposer.org/doc/04-schema.md#type
        $component->setType("library");

        // TODO: Validate License with SPDX license list
        if (array_key_exists("license", $package) && is_array($package["license"])) {
            $component->setLicenses(array_map(
                function($licenseId) { 
                    return new License($licenseId); 
                }, 
                $package["license"])
            );
        }

        if (array_key_exists("dist", $package) && array_key_exists("shasum", $package["dist"]) && $package["dist"]["shasum"]) {
            $component->setHashes(array("SHA-1" => $package["dist"]["shasum"]));
        }

        if ($component->getGroup()) {
            $component->setPackageUrl(\sprintf("pkg://composer/%s/%s@%s", $component->getGroup(), $component->getName(), $component->getVersion()));
        } else {
            $component->setPackageUrl(\sprintf("pkg://composer/%s@%s", $component->getName(), $component->getVersion()));
        }

        return $component;
    }

    /**
     * Versions of Composer packages may be prefixed with "v".
     * This prefix appears to be problematic for CPE and PURL matching and thus is removed here.
     * 
     * See for example https://ossindex.sonatype.org/component/pkg:composer/phpmailer/phpmailer@v6.0.7
     * vs https://ossindex.sonatype.org/component/pkg:composer/phpmailer/phpmailer@6.0.7.
     * 
     * @param $packageVersion The version to normalize
     * @return string The normalized version
     */
    private function normalizeVersion(string $packageVersion) 
    {
        if (\substr_compare($packageVersion, "v", 0, 1) === 0) {
            return \substr($packageVersion, 1, \strlen($packageVersion));
        }
        return $packageVersion;
    }

}