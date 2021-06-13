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

namespace CycloneDX\Composer\Plugin;

use CycloneDX\Composer\Plugin\Exceptions\ValueError;
use CycloneDX\Serialize\AbstractSerialize;
use CycloneDX\Serialize\JsonSerializer;
use CycloneDX\Serialize\SerializerInterface;
use CycloneDX\Serialize\XmlSerializer;
use CycloneDX\Specs\SpecFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 *
 * @author jkowalleck
 */
class MakeBomCommandOptions
{
    private const OPTION_OUTPUT_FORMAT = 'output-format';
    private const OPTION_OUTPUT_FILE = 'output-file';
    private const OPTION_EXCLUDE_DEV = 'exclude-dev';
    private const OPTION_EXCLUDE_PLUGINS = 'exclude-plugins';
    private const OPTION_SPEC_VERSION = 'spec-version';

    private const OUTPUT_FORMAT_XML = 'XML';
    private const OUTPUT_FORMAT_JSON = 'JSON';

    public const OUTPUT_FILE_STDOUT = '-';
    private const OUTPUT_FILE_DEFAULT = [
        self::OUTPUT_FORMAT_XML => 'bom.xml',
        self::OUTPUT_FORMAT_JSON => 'bom.json',
    ];

    private const SERIALISERS = [
        self::OUTPUT_FORMAT_XML => XmlSerializer::class,
        self::OUTPUT_FORMAT_JSON => JsonSerializer::class,
    ];

    /**
     * @return Command the command that was put in
     *
     * @psalm-suppress MissingThrowsDocblock since {@see Command::addOption()} is intended to work this way
     */
    public static function configureCommand(Command $command): Command
    {
        return $command
            ->addOption(
                self::OPTION_OUTPUT_FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Which output format to use.'.\PHP_EOL.
                'Values: "'.implode('", "', array_keys(self::SERIALISERS)).'"',
                self::OUTPUT_FORMAT_XML
            )
            ->addOption(
                self::OPTION_OUTPUT_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the output file.'.\PHP_EOL.
                'Set to "'.self::OUTPUT_FILE_STDOUT.'" to write to STDOUT, best used with flag -q.'.\PHP_EOL.
                '(depending on the output-format, defaults to: "'.implode(
                    '" or "',
                    array_values(self::OUTPUT_FILE_DEFAULT)
                ).'")'
            )
            ->addOption(
                self::OPTION_EXCLUDE_DEV,
                null,
                InputOption::VALUE_NONE,
                'Exclude dev dependencies'
            )
            ->addOption(
                self::OPTION_EXCLUDE_PLUGINS,
                null,
                InputOption::VALUE_NONE,
                'Exclude composer plugins'
            )
            ->addOption(
                self::OPTION_SPEC_VERSION,
                null,
                InputOption::VALUE_REQUIRED,
                'Which version of CycloneDX spec to use.'.\PHP_EOL.
                'Values: "'.implode('", "', array_keys(SpecFactory::SPECS)).'"',
                SpecFactory::VERSION_LATEST
            );
    }

    /**
     * @psalm-var \CycloneDX\Specs\Version::*
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $specVersion = SpecFactory::VERSION_LATEST;

    /**
     * @var bool
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $excludeDev = false;

    /**
     * @var bool
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $excludePlugins = false;

    /**
     * @psalm-var MakeBomCommandOptions::OUTPUT_FORMAT_*
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $bomFormat = self::OUTPUT_FORMAT_XML;

    /**
     * @psalm-var class-string<AbstractSerialize&SerializerInterface>
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $bomWriterClass = self::SERIALISERS[self::OUTPUT_FORMAT_XML];

    /**
     * @var string
     * @readonly
     * @psalm-allow-private-mutation
     */
    public $outputFile = self::OUTPUT_FILE_STDOUT;

    /**
     * @throws ValueError
     *
     * @psalm-suppress MissingThrowsDocblock since {@see InputInterface::getOption()} is intended to work this way
     */
    public static function makeFromInput(InputInterface $input): self
    {
        $options = new self();

        $specVersion = $input->getOption(self::OPTION_SPEC_VERSION);
        \assert(\is_string($specVersion));
        if (false === \array_key_exists($specVersion, SpecFactory::SPECS)) {
            throw new ValueError('Invalid value for option "'.self::OPTION_SPEC_VERSION.'": '.$specVersion);
        }
        $options->specVersion = $specVersion;

        $options->excludeDev = false !== $input->getOption(self::OPTION_EXCLUDE_DEV);
        $options->excludePlugins = false !== $input->getOption(self::OPTION_EXCLUDE_PLUGINS);

        $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
        \assert(\is_string($outputFormat));
        $bomFormat = strtoupper($outputFormat);
        unset($outputFormat);

        if (false === \array_key_exists($bomFormat, self::SERIALISERS)) {
            throw new ValueError('Invalid value for option "'.self::OPTION_OUTPUT_FORMAT.'": '.$bomFormat);
        }
        $options->bomFormat = $bomFormat;
        $options->bomWriterClass = self::SERIALISERS[$bomFormat];

        $outputFile = $input->getOption(self::OPTION_OUTPUT_FILE);
        $options->outputFile = false === \is_string($outputFile) || '' === $outputFile
            ? self::OUTPUT_FILE_DEFAULT[$bomFormat]
            : $outputFile;

        return $options;
    }
}
