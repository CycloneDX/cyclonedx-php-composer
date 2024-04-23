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

namespace CycloneDX\Composer\_internal\MakeBom;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
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
     * Env var to control `bom.metadata.tools.tool.version`.
     * Non-documented private env vars usage, because this is not public API.
     */
    private const ENV_TOOLS_VERSION_OVERRIDE = 'CDX_CP_TOOLS_VERSION_OVERRIDE';

    /**
     * Env var to control whether to exclude all own libs to `bom.metadata.tools.tool`.
     * Non-documented private env vars usage, because this is not public API.
     */
    private const ENV_TOOLS_EXCLUDE_LIBS = 'CDX_CP_TOOLS_EXCLUDE_LIBS';

    /**
     * Env var to control whether to exclude `composer` to `bom.metadata.tools.tool`.
     * Non-documented private env vars usage, because this is not public API.
     */
    private const ENV_TOOLS_EXCLUDE_COMPOSER = 'CDX_CP_TOOLS_EXCLUDE_COMPOSER';

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
     * @psalm-var array<string, Format>
     */
    private const VALUES_OUTPUT_FORMAT_MAP = [
        'XML' => Format::XML,
        // first in list is the default value - see constructor
        'JSON' => Format::JSON,
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
     *
     * @psalm-var array<string, Version>
     */
    private const VALUE_SPEC_VERSION_MAP = [
        '1.5' => Version::v1dot5,
        // first in list is the default value - see constructor
        '1.6' => Version::v1dot6,
        '1.4' => Version::v1dot4,
        '1.3' => Version::v1dot3,
        '1.2' => Version::v1dot2,
        '1.1' => Version::v1dot1,
    ];

    /**
     * @param scalar[] $values
     */
    private static function formatChoice(array $values, int $sortFlag = \SORT_STRING): string
    {
        sort($values, $sortFlag);

        return '{choices: "'.
            implode('", "', $values).
            '"}';
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     * @psalm-suppress TooManyArguments as there is an optional 6th param of {@see Command::addOption()}
     */
    public function getDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                self::OPTION_OUTPUT_FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Which output format to use.'.\PHP_EOL.
                self::formatChoice(array_keys(self::VALUES_OUTPUT_FORMAT_MAP)),
                array_search($this->outputFormat, self::VALUES_OUTPUT_FORMAT_MAP, true),
                array_keys(self::VALUES_OUTPUT_FORMAT_MAP)
            ),
            new InputOption(
                self::OPTION_OUTPUT_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the output file.'.\PHP_EOL.
                'Set to "'.self::VALUE_OUTPUT_FILE_STDOUT.'" to write to STDOUT',
                $this->outputFile
            ),
            new InputOption(
                self::OPTION_OMIT,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Omit dependency types.'.\PHP_EOL.
                self::formatChoice(self::VALUES_OMIT),
                $this->omit,
                self::VALUES_OMIT
            ),
            new InputOption(
                self::OPTION_SPEC_VERSION,
                null,
                InputOption::VALUE_REQUIRED,
                'Which version of CycloneDX spec to use.'.\PHP_EOL.
                self::formatChoice(array_keys(self::VALUE_SPEC_VERSION_MAP), \SORT_NUMERIC),
                array_search($this->specVersion, self::VALUE_SPEC_VERSION_MAP, true),
                array_keys(self::VALUE_SPEC_VERSION_MAP)
            ),
            new InputOption(
                self::SWITCH_OUTPUT_REPRODUCIBLE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Whether to go the extra mile and make the output reproducible.'.\PHP_EOL.
                'This might result in loss of time- and random-based-values.',
                $this->outputReproducible
            ),
            new InputOption(
                self::SWITCH_VALIDATE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Formal validate the resulting BOM.',
                $this->validate
            ),
            new InputOption(
                self::OPTION_MAIN_COMPONENT_VERSION,
                null,
                InputOption::VALUE_REQUIRED,
                'Version of the main component.'.\PHP_EOL.
                'This will override auto-detection.',
                $this->mainComponentVersion
            ),
            new InputArgument(
                self::ARGUMENT_COMPOSER_FILE,
                InputArgument::OPTIONAL,
                'Path to Composer config file.'.\PHP_EOL.
                '[default: "composer.json" file in current working directory]',
                null
            ),
        ]);
    }

    /**
     * @readonly
     *
     * @psalm-allow-private-mutation
     */
    public Version $specVersion;

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
     */
    public Format $outputFormat;

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

    public function __construct()
    {
        $this->outputFormat = self::VALUES_OUTPUT_FORMAT_MAP[array_key_first(self::VALUES_OUTPUT_FORMAT_MAP)];
        $this->specVersion = self::VALUE_SPEC_VERSION_MAP[array_key_first(self::VALUE_SPEC_VERSION_MAP)];
    }

    /**
     * @psalm-return null|non-empty-string
     */
    public function getToolsVersionOverride(): ?string
    {
        $version = getenv(self::ENV_TOOLS_VERSION_OVERRIDE);

        return \is_string($version) && '' !== $version
            ? $version
            : null;
    }

    public function getToolsExcludeLibs(): bool
    {
        return (bool) getenv(self::ENV_TOOLS_EXCLUDE_LIBS);
    }

    public function getToolsExcludeComposer(): bool
    {
        return (bool) getenv(self::ENV_TOOLS_EXCLUDE_COMPOSER);
    }

    /**
     * @throws Errors\OptionError
     *
     * @return $this
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function setFromInput(InputInterface $input): static
    {
        // region get from input

        $specVersion = $input->getOption(self::OPTION_SPEC_VERSION);
        \assert(\is_string($specVersion));
        if (false === \array_key_exists($specVersion, self::VALUE_SPEC_VERSION_MAP)) {
            throw new Errors\OptionError('Invalid value for option "'.self::OPTION_SPEC_VERSION.'": '.$specVersion);
        }

        $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
        \assert(\is_string($outputFormat));
        $outputFormat = strtoupper($outputFormat);
        if (false === \array_key_exists($outputFormat, self::VALUES_OUTPUT_FORMAT_MAP)) {
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

        $this->specVersion = self::VALUE_SPEC_VERSION_MAP[$specVersion];
        $this->omit = array_values(array_intersect(self::VALUES_OMIT, $omit));
        $this->outputFormat = self::VALUES_OUTPUT_FORMAT_MAP[$outputFormat];
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
