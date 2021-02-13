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

namespace CycloneDX\Composer;

use Composer\Command\BaseCommand;
use CycloneDX\BomFile\Json;
use CycloneDX\BomFile\Xml;
use CycloneDX\Specs\Spec12;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Plugin's makeBom command.
 *
 * @author nscuro
 *
 * @internal
 */
class BomCommand extends BaseCommand
{
    private const OPTION_OUTPUT_FILE = 'output-file';
    private const OPTION_EXCLUDE_DEV = 'exclude-dev';
    private const OPTION_EXCLUDE_PLUGINS = 'exclude-plugins';
    private const OPTION_JSON = 'json';

    private const OUTPUT_FILE_STDOUT = '-';
    private const OUTPUT_FILE_DEFAULT_XML = 'bom.xml';
    private const OUTPUT_FILE_DEFAULT_JSON = 'bom.json';

    private const EXIT_OK = 0;
    private const EXIT_MISSING_COMPOSER = 1;
    private const EXIT_MISSING_LOCK = 2;

    protected function configure(): void
    {
        $this->setName('make-bom')
             ->setDescription('Generate a CycloneDX Bill of Materials');

        $this->addOption(self::OPTION_OUTPUT_FILE, null, InputOption::VALUE_REQUIRED, 'Path to the output file (default is '.self::OUTPUT_FILE_DEFAULT_XML.' or '.self::OUTPUT_FILE_DEFAULT_JSON.").\nSet to \"".self::OUTPUT_FILE_STDOUT.'" to write to STDOUT.');
        $this->addOption(self::OPTION_EXCLUDE_DEV, null, InputOption::VALUE_NONE, 'Exclude dev dependencies');
        $this->addOption(self::OPTION_EXCLUDE_PLUGINS, null, InputOption::VALUE_NONE, 'Exclude composer plugins');
        $this->addOption(self::OPTION_JSON, null, InputOption::VALUE_NONE, 'Produce the BOM in JSON format (preview support)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composer = $this->getComposer();
        if (null == $composer) {
            // earlier versions of composer may return `null` instead of throwing an error.
            $output->writeln('<error>Composer does not exist</error>');

            return self::EXIT_MISSING_COMPOSER;
        }
        $locker = $composer->getLocker();

        if (false === $locker->isLocked()) {
            $output->writeln('<error>Lockfile does not exist</error>');

            return self::EXIT_MISSING_LOCK;
        }

        $output->writeln('<info>Generating BOM from lockfile</info>');
        $bomGenerator = new BomGenerator($output);
        $bom = $bomGenerator->generateBom(
            $locker->getLockData(),
            false !== $input->getOption($this::OPTION_EXCLUDE_DEV),
            false !== $input->getOption($this::OPTION_EXCLUDE_PLUGINS)
        );

        /* @var string|null $outputFile */
        $outputFile = $input->getOption($this::OPTION_OUTPUT_FILE);
        if (false === is_string($outputFile) || '' === $outputFile) {
            $outputFile = null;
        }

        /* @var \CycloneDX\BomFile\SerializeInterface $bomWriter */
        if (false === $input->getOption($this::OPTION_JSON)) {
            $outputFile = $outputFile ?? self::OUTPUT_FILE_DEFAULT_JSON;
            $bomWriter = new Xml(new Spec12());
        } else {
            $outputFile = $outputFile ?? self::OUTPUT_FILE_DEFAULT_XML;
            $bomWriter = new Json(new Spec12());
        }

        $output->writeln('<info>Serializing BOM</info>');
        $bomContents = $bomWriter->serialize($bom, true);

        if (self::OUTPUT_FILE_STDOUT === $outputFile) {
            $output->writeln('<info>Writing output to STDOUT</info>');
            // don't use `$output->writeln()`, so to support `-q` cli param.
            fwrite(STDOUT, $bomContents);
            // straighten up and add a linebreak. raw output might not have done it.
            $output->writeln('');
        } else {
            $output->writeln('<info>Writing output to '.OutputFormatter::escape($outputFile).'</info>');
            \file_put_contents($outputFile, $bomContents);
        }

        return self::EXIT_OK;
    }
}
