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
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Composer\MakeBom;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @psalm-type TOmittable = "dev"|"plugin"
 *
 * @internal
 *
 * @author jkowalleck
 */
class Options
{
    /**
     * Env var to control whether to set self as `sbom.metadata.tools.tool`.
     * Non-documented private env vars usage, because this is not public API.
     */
    private const ENV_TOOL_VERSION_OVERRIDE = 'CDX_CP_TOOL_VERSION_OVERRIDE';

    private const OPTION_OUTPUT_FORMAT = 'output-format';
    private const OPTION_OUTPUT_FILE = 'output-file';
    private const OPTION_SPEC_VERSION = 'spec-version';
    private const OPTION_MAIN_COMPONENT_VERSION = 'mc-version';
    private const OPTION_OMIT = 'omit';

    private const SWITCH_OUTPUT_REPRODUCIBLE = 'output-reproducible';
    private const SWITCH_VALIDATE = 'validate';

    private const ARGUMENT_COMPOSER_FILE = 'composer-file';

    /**
     * Possible output formats.
     * First in list is the default value.
     *
     * @psalm-var non-empty-list<Format::*>
     */
    private const VALUES_OUTPUT_FORMAT = [
        Format::XML,
        Format::JSON,
    ];

    public const VALUE_OUTPUT_FILE_STDOUT = '-';

    /**
     * Possible omittables.
     *
     * @psalm-var non-empty-list<TOmittable>
     */
    private const VALUES_OMIT = [
        'dev',
        'plugin',
    ];

    /**
     * Possible spec versions.
     * First in list is the default value.
     */
    private const VALUE_SPEC_VERSION = [
        Version::v1dot4,
        Version::v1dot3,
        Version::v1dot2,
        Version::v1dot1,
    ];

    /**
     * @param scalar[] $values
     */
    private static function formatChoice(array $values): string
    {
        return '{choices: "'.
            implode('", "', $values).
            '"}';
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     * @psalm-suppress TooManyArguments as there is an optional 6th param of {@see Command::addOption()}
     */
    public function configureCommand(Command $command): Command
    {
        return $command
            ->addOption(
                self::OPTION_OUTPUT_FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Which output format to use.'.\PHP_EOL.
                self::formatChoice(self::VALUES_OUTPUT_FORMAT),
                $this->outputFormat,
                self::VALUES_OUTPUT_FORMAT
            )
            ->addOption(
                self::OPTION_OUTPUT_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the output file.'.\PHP_EOL.
                'Set to "'.self::VALUE_OUTPUT_FILE_STDOUT.'" to write to STDOUT',
                $this->outputFile
            )
            ->addOption(
                self::OPTION_OMIT,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Omit dependency types.'.\PHP_EOL.
                self::formatChoice(self::VALUES_OMIT),
                $this->omit,
                self::VALUES_OMIT
            )
            ->addOption(
                self::OPTION_SPEC_VERSION,
                null,
                InputOption::VALUE_REQUIRED,
                'Which version of CycloneDX spec to use.'.\PHP_EOL.
                self::formatChoice(self::VALUE_SPEC_VERSION),
                $this->specVersion,
                self::VALUE_SPEC_VERSION
            )
            ->addOption(
                self::SWITCH_OUTPUT_REPRODUCIBLE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Whether to go the extra mile and make the output reproducible.'.\PHP_EOL.
                'This might result in loss of time- and random-based-values.',
                $this->outputReproducible
            )
            ->addOption(
                self::SWITCH_VALIDATE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Validate the resulting output.',
                $this->validate
            )
            ->addOption(
                self::OPTION_MAIN_COMPONENT_VERSION,
                null,
                InputOption::VALUE_REQUIRED,
                'Version of the main component.'.\PHP_EOL.
                'This will override auto-detection.',
                $this->mainComponentVersion
            )
            ->addArgument(
                self::ARGUMENT_COMPOSER_FILE,
                InputArgument::OPTIONAL,
                'Path to composer config file.'.\PHP_EOL.
                '[default: "composer.json" file in current working directory]',
                null
            );
    }

    /**
     * @readonly
     *
     * @psalm-var Version::*
     *
     * @psalm-allow-private-mutation
     */
    public string $specVersion = self::VALUE_SPEC_VERSION[0];

    /**
     * @readonly
     *
     * @var string[]
     *
     * @psalm-var list<TOmittable>
     *
     * @psalm-allow-private-mutation
     */
    public array $omit = [];

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     *
     * @psalm-var Format::*
     */
    public string $outputFormat = self::VALUES_OUTPUT_FORMAT[0];

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     */
    public bool $outputReproducible = false;

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     */
    public bool $validate = true;

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     *
     * @psalm-var non-empty-string
     */
    public string $outputFile = self::VALUE_OUTPUT_FILE_STDOUT;

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     *
     * @psalm-var null|non-empty-string
     */
    public ?string $composerFile = null;

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     *
     * @psalm-var null|non-empty-string
     */
    public ?string $mainComponentVersion = null;

    /**
     * @psalm-return null|non-empty-string
     */
    public function getToolVersionOverride(): ?string
    {
        $version = getenv(self::ENV_TOOL_VERSION_OVERRIDE);

        return \is_string($version) && '' !== $version
        ? $version
        : null;
    }

    /**
     * @throws Errors\OptionError
     *
     * @return $this
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function setFromInput(InputInterface $input): self
    {
        // region get from input

        $specVersion = $input->getOption(self::OPTION_SPEC_VERSION);
        \assert(\is_string($specVersion));
        if (false === \in_array($specVersion, self::VALUE_SPEC_VERSION, true)) {
            throw new Errors\OptionError('Invalid value for option "'.self::OPTION_SPEC_VERSION.'": '.$specVersion);
        }

        $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
        \assert(\is_string($outputFormat));
        $outputFormat = strtoupper($outputFormat);
        if (false === \in_array($outputFormat, self::VALUES_OUTPUT_FORMAT, true)) {
            throw new Errors\OptionError('Invalid value for option "'.self::OPTION_OUTPUT_FORMAT.'": '.$outputFormat);
        }

        $outputFile = $input->getOption(self::OPTION_OUTPUT_FILE);
        \assert(\is_string($outputFile));
        if ('' === $outputFile) {
            throw new Errors\OptionError('Invalid value for option "'.self::OPTION_OUTPUT_FILE.'": '.$outputFile);
        }
        // no additional restrictions to $outputFile - stuff like 'ftp://user:pass@host/path/file' is acceptable.

        $omit = $input->getOption(self::OPTION_OMIT);
        \assert(\is_array($omit));
        $outputReproducible = false !== $input->getOption(self::SWITCH_OUTPUT_REPRODUCIBLE);
        $validate = false !== $input->getOption(self::SWITCH_VALIDATE);
        $composerFile = $input->getArgument(self::ARGUMENT_COMPOSER_FILE);
        \assert(null === $composerFile || \is_string($composerFile));
        $mainComponentVersion = $input->getOption(self::OPTION_MAIN_COMPONENT_VERSION);
        \assert(null === $mainComponentVersion || \is_string($mainComponentVersion));

        // endregion get from input

        /* those regions are split,
         * so the state does not modify unless everything is clear
         */

        // region set state

        $this->specVersion = $specVersion;
        $this->omit = array_values(array_intersect(self::VALUES_OMIT, $omit));
        $this->outputFormat = $outputFormat;
        $this->outputReproducible = $outputReproducible;
        $this->validate = $validate;
        $this->outputFile = $outputFile;
        $this->mainComponentVersion = '' !== $mainComponentVersion
            ? $mainComponentVersion
            : null;
        $this->composerFile = '' !== $composerFile
            ? $composerFile
            : null;

        // endregion set state

        return $this;
    }
}
