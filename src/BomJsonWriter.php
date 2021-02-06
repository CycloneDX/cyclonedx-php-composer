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
 * Writes BOMs in JSON format.
 * 
 * @author nscuro
 */
class BomJsonWriter 
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
     * @return string The BOM as JSON formatted string
     */
    public function writeBom(Bom $bom) 
    {
        $jsonBom = [
            "bomFormat" => "CycloneDX",
            "specVersion" => "1.2",
            "version" => 1,
            "components" => $bom->getComponents(),
        ];

        return json_encode($jsonBom, JSON_PRETTY_PRINT);
    }

}
