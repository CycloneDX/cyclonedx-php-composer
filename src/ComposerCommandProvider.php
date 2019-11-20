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

use Composer\Semver\VersionParser;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author nscuro
 */
class ComposerCommandProvider implements CommandProvider
{
    public function getCommands()
    {
        return array(new MakeBomCommand);
    }   
}

/**
 * The Plugin's makeBom command.
 * 
 * @author nscuro
 */
class MakeBomCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName("makeBom")
            ->setDescription("Generate a CycloneDX Bill of Materials");

        $this->addOption("outputFile", null, InputOption::VALUE_REQUIRED, "Path to the output file (default is bom.xml)");
        $this->addOption("excludeDev", null, InputOption::VALUE_NONE, "Exclude dev dependencies");
        $this->addOption("excludePlugins", null, InputOption::VALUE_NONE, "Exclude composer plugins");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locker = $this->getComposer()->getLocker();

        if (!$locker->isLocked()) {
            $output->writeln("<error>Lockfile does not exist</error>");
            return;
        }
        
        $output->writeln("<info>Generating BOM...</info>");
        $bomGenerator = new BomGenerator($output);
        $bom = $bomGenerator->generateBom(
            $locker->getLockData(), 
            $input->getOption("excludeDev") !== false, 
            $input->getOption("excludePlugins") !== false
        );

        $output->writeln("<info>Writing BOM XML...</info>");
        $bomWriter = new BomXmlWriter($output);
        $bomXml = $bomWriter->writeBom($bom);
        
        $outputFile = $input->getOption("outputFile") ? $input->getOption("outputFile") : "bom.xml";
        $output->writeln("<info>Writing BOM to " . $outputFile . "...</info>");
        \file_put_contents($outputFile, $bomXml);
    }
}
