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

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @internal
 *
 * @author jkowalleck
 */
class Command extends BaseCommand
{
    private Options $options;

    /**
     * @throws LogicException When the command name is empty
     */
    public function __construct(
        Options $options,
        string $name
    ) {
        $this->options = $options;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->options->configureCommand($this);
        $this->setDescription('Generate a CycloneDX Bill of Materials from a PHP composer project.');
    }

    /*
     * ALL LOG OUTPUT MUST BE WRITTEN AS ERROR, SO OUTPUT REDIRECT/PIPE OF RESULT WORKS PROPERLY
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getIO();

        try {
            $this->options->setFromInput($input);
        } catch (Throwable $valueError) {
            $io->writeErrorRaw((string) $valueError, true, IOInterface::DEBUG);
            $io->writeError(
                sprintf(
                    '<error>Option Error: %s</error>',
                    OutputFormatter::escape($valueError->getMessage())
                )
            );

            return self::INVALID;
        }
        $io->writeErrorRaw(__METHOD__.' Options: '.print_r($this->options, true), true, IOInterface::DEBUG);

        return 0;
    }
}
