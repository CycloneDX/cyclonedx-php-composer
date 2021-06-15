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

use Composer\Command\BaseCommand;
use Composer\Composer;
use CycloneDX\Composer\Factories\BomFactory;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Composer\Locker;
use CycloneDX\Composer\Plugin\Exceptions\ValueError;
use CycloneDX\Spdx\License as SpdxLicenseValidator;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * @internal
 *
 * @author jkowalleck
 */
class MakeBomCommand extends BaseCommand
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    /**
     * @psalm-suppress MissingThrowsDocblock - Exceptions are handled by caller
     */
    protected function configure(): void
    {
        $this->setDescription('Generate a CycloneDX Bill of Materials');
        MakeBomCommandOptions::configureCommand($this);
    }

    /**
     * @throws RuntimeException
     */
    private function compat_getComposer(): Composer
    {
        try {
            $composer = $this->getComposer();
        } catch (\Exception $exception) {
            throw new RuntimeException('Composer does not exist', 0, $exception);
        }
        if (null === $composer) {
            throw new UnexpectedValueException('Composer is null');
        }

        return $composer;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock - Exceptions are handled by caller
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locker = $this->compat_getComposer()->getLocker();
        if (false === $locker->isLocked()) {
            $output->writeln('<error>Lockfile does not exist</error>');

            return self::INVALID;
        }

        try {
            $options = MakeBomCommandOptions::makeFromInput($input);
        } catch (ValueError $valueError) {
            $output->writeln('<error>'.OutputFormatter::escape($valueError->getMessage()).'</error>');

            return self::INVALID;
        }

        return $this->makeAndWrite(new Locker($locker), $options, $output)
            ? self::SUCCESS
            : self::FAILURE;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock - Exceptions are handled by caller
     */
    private function makeAndWrite(Locker $locker, MakeBomCommandOptions $options, OutputInterface $output): bool
    {
        $isVerbose = OutputInterface::VERBOSITY_VERBOSE & $output->getVerbosity();

        $isVerbose && $output->writeln('<info>Generating BOM from lockfile</info>');
        $bom = (new BomFactory(
            $options->excludeDev, $options->excludePlugins,
            new ComponentFactory(
                new LicenseFactory(
                    new SpdxLicenseValidator()
                )
            )
        ))->makeFromLocker($locker);

        $spec = (new SpecFactory())->make($options->specVersion);
        $bomWriter = new $options->bomWriterClass($spec);

        $isVerbose && $output->writeln(
            '<info>Serializing BOM: '.OutputFormatter::escape($options->bomFormat).' '.OutputFormatter::escape(
                $options->specVersion
            ).'</info>'
        );
        $bomContents = $bomWriter->serialize($bom, true);

        if (MakeBomCommandOptions::OUTPUT_FILE_STDOUT === $options->outputFile) {
            $isVerbose && $output->writeln('<info>Writing output to STDOUT</info>');
            // don't use `$output->writeln()`, so to support `-q` cli param.
            $written = fwrite(\STDOUT, $bomContents);
            // straighten up and add a linebreak. raw output might not have done it.
            $output->writeln('');
        } else {
            $isVerbose && $output->writeln(
                '<info>Writing output to: '.OutputFormatter::escape($options->outputFile).'</info>'
            );
            $written = file_put_contents($options->outputFile, $bomContents);
            $output->writeln(
                '<info>Wrote '.OutputFormatter::escape($options->bomFormat).' '.OutputFormatter::escape(
                    $options->specVersion
                ).' to: '.OutputFormatter::escape($options->outputFile).'</info>'
            );
        }

        return false !== $written;
    }
}
