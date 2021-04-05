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

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author nscuro
 */
class ComposerCommandProvider implements CommandProvider
{
    public function getCommands()
    {
        return [new MakeBomCommand()];
    }
}

/**
 * The Plugin's makeBom command.
 *
 * @author nscuro
 */
class MakeBomCommand extends BaseCommand
{
    const OPTION_OUTPUT_FILE = 'output-file';
    const OPTION_EXCLUDE_DEV = 'exclude-dev';
    const OPTION_EXCLUDE_PLUGINS = 'exclude-plugins';
    const OPTION_JSON = 'json';

    protected function configure()
    {
        $this
            ->setName('make-bom')
            ->setDescription('Generate a CycloneDX Bill of Materials');

        $this->addOption($this::OPTION_OUTPUT_FILE, null, InputOption::VALUE_REQUIRED, 'Path to the output file (default is bom.xml or bom.json)');
        $this->addOption($this::OPTION_EXCLUDE_DEV, null, InputOption::VALUE_NONE, 'Exclude dev dependencies');
        $this->addOption($this::OPTION_EXCLUDE_PLUGINS, null, InputOption::VALUE_NONE, 'Exclude composer plugins');
        $this->addOption($this::OPTION_JSON, null, InputOption::VALUE_NONE, 'Produce the BOM in JSON format (preview support)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locker = $this->getComposer()->getLocker();

        if (!$locker->isLocked()) {
            $output->writeln('<error>Lockfile does not exist</error>');

            return;
        }

        $output->writeln('<info>Generating BOM from lockfile</info>');
        $bomGenerator = new BomGenerator($output);
        $bom = $bomGenerator->generateBom(
            $locker->getLockData(),
            false !== $input->getOption($this::OPTION_EXCLUDE_DEV),
            false !== $input->getOption($this::OPTION_EXCLUDE_PLUGINS)
        );

        $output->writeln('<info>Writing BOM</info>');

        if (false !== $input->getOption($this::OPTION_JSON)) {
            $bomWriter = new BomJsonWriter($output);
            $outputFile = $input->getOption($this::OPTION_OUTPUT_FILE) ? $input->getOption($this::OPTION_OUTPUT_FILE) : 'bom.json';
        } else {
            $bomWriter = new BomXmlWriter($output);
            $outputFile = $input->getOption($this::OPTION_OUTPUT_FILE) ? $input->getOption($this::OPTION_OUTPUT_FILE) : 'bom.xml';
        }

        $bomContents = $bomWriter->writeBom($bom);

        $output->writeln('<info>Writing output to '.$outputFile.'</info>');
        \file_put_contents($outputFile, $bomContents);
    }
}
