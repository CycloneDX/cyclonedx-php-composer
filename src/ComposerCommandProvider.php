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
    private const OPTION_OUTPUT_FORMAT = 'output-format';
    public const OPTION_OUTPUT_FILE = 'output-file';
    public const OPTION_EXCLUDE_DEV = 'exclude-dev';
    public const OPTION_EXCLUDE_PLUGINS = 'exclude-plugins';
    public const OPTION_JSON = 'json';

    private const OUTPUT_FORMAT_XML = 'XML';
    private const OUTPUT_FORMAT_JSON = 'JSON';

    private const OUTPUT_FILE_DEFAULT = [
        self::OUTPUT_FORMAT_XML => 'bom.xml',
        self::OUTPUT_FORMAT_JSON => 'bom.json',
    ];

    private const EXIT_OK = 0;
    private const EXIT_MISSING_LOCK = 1;
    private const EXIT_UNSUPPORTED_OUTPUT_FORMAT = 2;

    private const SERIALISERS = [
        self::OUTPUT_FORMAT_XML => BomXmlWriter::class,
        self::OUTPUT_FORMAT_JSON => BomJsonWriter::class,
    ];

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    protected function configure()
    {
        $this
            ->setName('make-bom')
            ->setDescription('Generate a CycloneDX Bill of Materials');

        $this->addOption(
            self::OPTION_OUTPUT_FORMAT,
            null,
            InputOption::VALUE_REQUIRED,
            'Which output format to use.'.PHP_EOL.
            'Values: "'.self::OUTPUT_FORMAT_XML.'", "'.self::OUTPUT_FORMAT_JSON.'"',
            self::OUTPUT_FORMAT_XML
        );

        $this->addOption($this::OPTION_OUTPUT_FILE, null, InputOption::VALUE_REQUIRED, 'Path to the output file (default is bom.xml or bom.json)');
        $this->addOption($this::OPTION_EXCLUDE_DEV, null, InputOption::VALUE_NONE, 'Exclude dev dependencies');
        $this->addOption($this::OPTION_EXCLUDE_PLUGINS, null, InputOption::VALUE_NONE, 'Exclude composer plugins');
        $this->addOption($this::OPTION_JSON, null, InputOption::VALUE_NONE, 'Produce the BOM in JSON format (preview support)'.PHP_EOL.'DEPRECATED. USE "--'.self::OPTION_OUTPUT_FORMAT.'='.self::OUTPUT_FORMAT_JSON.'" instead');
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locker = $this->getComposer()->getLocker();

        if (!$locker->isLocked()) {
            $output->writeln('<error>Lockfile does not exist</error>');

            return self::EXIT_MISSING_LOCK;
        }

        $output->writeln('<info>Generating BOM from lockfile</info>');
        $bomGenerator = new BomGenerator($output);
        $bom = $bomGenerator->generateBom(
            $locker->getLockData(),
            false !== $input->getOption(self::OPTION_EXCLUDE_DEV),
            false !== $input->getOption(self::OPTION_EXCLUDE_PLUGINS)
        );

        if (false !== $input->getOption(self::OPTION_JSON)) {
            // keep a deprecated CLI switch forwards-compatible
            $output->writeln('<warning>DEPRICATED CLI usage: use --'.self::OPTION_OUTPUT_FORMAT.'='.self::OUTPUT_FORMAT_JSON.' instead of --'.self::OPTION_JSON.'.</warning>');
            $input->setOption(self::OPTION_OUTPUT_FORMAT, self::OUTPUT_FORMAT_JSON);
        }

        $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
        assert(is_string($outputFormat));
        $bomFormat = strtoupper($outputFormat);
        unset($outputFormat);

        $bomWriterClass = self::SERIALISERS[$bomFormat] ?? null;
        if (null === $bomWriterClass) {
            $output->writeln("<error>Unsupported output-format: ${bomFormat}<error>");

            return self::EXIT_UNSUPPORTED_OUTPUT_FORMAT;
        }
        $bomWriter = new $bomWriterClass($output);

        $output->writeln("<info>Writing BOM: ${bomFormat}</info>");
        $bomContents = $bomWriter->writeBom($bom);

        $outputFile = $input->getOption($this::OPTION_OUTPUT_FILE) ?: self::OUTPUT_FILE_DEFAULT[$bomFormat];
        $output->writeln('<info>Writing output to: '.$outputFile.'</info>');
        \file_put_contents($outputFile, $bomContents);

        return self::EXIT_OK;
    }
}
