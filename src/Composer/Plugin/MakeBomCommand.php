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
use CycloneDX\Composer\Factories\BomFactory;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Composer\Locker;
use CycloneDX\Composer\Plugin\Exceptions\ValueError;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialize\SerializerInterface;
use CycloneDX\Core\Spdx\License as SpdxLicenseValidator;
use CycloneDX\Core\Validation\ValidationError;
use CycloneDX\Core\Validation\ValidatorInterface;
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
    private function getLocker(OutputInterface $output): ?\Composer\Package\Locker
    {
        $output->writeln('<info>Gathering lockfile</info>', OutputInterface::VERBOSITY_VERY_VERBOSE);

        try {
            $composer = $this->getComposer();
        } catch (\Exception $exception) {
            throw new RuntimeException('Composer does not exist', 0, $exception);
        }
        if (null === $composer) {
            throw new UnexpectedValueException('Composer is null');
        }

        $locker = $composer->getLocker();

        return $locker->isLocked()
            ? $locker
            : null;
    }

    /**
     * @psalm-suppress MissingThrowsDocblock - Exceptions are handled by caller
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locker = $this->getLocker($output);
        if (null === $locker) {
            $output->writeln('<error>Lockfile does not exist</error>');

            return self::INVALID;
        }

        try {
            $options = MakeBomCommandOptions::makeFromInput($input);
        } catch (ValueError $valueError) {
            $output->writeln('<error>'.OutputFormatter::escape($valueError->getMessage()).'</error>');

            return self::INVALID;
        }
        $output->writeln(
            __METHOD__.' Options: '.print_r($options, true),
            OutputInterface::VERBOSITY_DEBUG | OutputInterface::OUTPUT_RAW
        );

        $bom = $this->makeBom(new Locker($locker), $options, $output);

        return $this->writeBom($bom, $options, $output)
            ? self::SUCCESS
            : self::FAILURE;
    }

    /**
     * @throws RuntimeException
     * @throws \DomainException
     */
    private function makeBom(Locker $locker, MakeBomCommandOptions $options, OutputInterface $output): Bom
    {
        $output->writeln(
            '<info>Generating BOM from lockfile</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );

        $bom = (new BomFactory(
            $options->excludeDev, $options->excludePlugins,
            new ComponentFactory(
                new LicenseFactory(
                    new SpdxLicenseValidator()
                )
            )
        ))->makeFromLocker($locker);

        $output->writeln(
            'Bom: '.print_r($bom, true),
            OutputInterface::VERBOSITY_DEBUG | OutputInterface::OUTPUT_RAW
        );

        return $bom;
    }

    /**
     * @throws \InvalidArgumentException if SPEC version is unknown
     */
    private function writeBom(Bom $bom, MakeBomCommandOptions $options, OutputInterface $output): bool
    {
        $spec = (new SpecFactory())->make($options->specVersion);

        $bomString = $this->makeBomString(new $options->bomWriterClass($spec), $bom, $output);

        $isValid = $this->validateBomString(
            $bomString,
            null === $options->bomValidatorClass ? null : new $options->bomValidatorClass($spec),
            $output
        );
        if (false === $isValid) {
            return false;
        }

        if (MakeBomCommandOptions::OUTPUT_FILE_STDOUT === $options->outputFile) {
            $output->writeln(
                '<info>Writing output to STDOUT</info>',
                OutputInterface::VERBOSITY_VERBOSE
            );

            // don't use `$output->writeln()`, so to support `-q` cli param and still have the desired output.
            return false !== fwrite(\STDOUT, $bomString);
        }

        $output->writeln(
            '<info>Writing output to: '.
            OutputFormatter::escape($options->outputFile).
            '</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );
        $written = file_put_contents($options->outputFile, $bomString);
        if (false === $written) {
            $output->writeln(
                '<error>Failed writing to file: '.
                OutputFormatter::escape($options->outputFile).
                '</error>'
            );

            return false;
        }
        $output->writeln(
            '<info>Wrote '.
            OutputFormatter::escape($options->bomFormat).' '.
            OutputFormatter::escape($options->specVersion).
            ' to: '.OutputFormatter::escape($options->outputFile).
            '</info>'
        );

        return true;
    }

    private function makeBomString(SerializerInterface $bomWriter, Bom $bom, OutputInterface $output): string
    {
        $output->writeln(
            '<info>Serializing BOM with '.
            OutputFormatter::escape(\get_class($bomWriter)).
            ' for '.OutputFormatter::escape($bomWriter->getSpec()->getVersion()).
            '</info>',
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        return $bomWriter->serialize($bom);
    }

    private function validateBomString(string $bom, ?ValidatorInterface $validator, OutputInterface $output): ?bool
    {
        if (null === $validator) {
            $output->writeln(
                '<info>Skipping output validation</info>',
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            return null;
        }

        $output->writeln(
            '<info>Validating output with '.
            OutputFormatter::escape(\get_class($validator)).
            ' for '.OutputFormatter::escape($validator->getSpec()->getVersion()).
            '</info>',
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        try {
            $validationError = $validator->validateString($bom);
        } catch (\Exception $exception) {
            $validationError = ValidationError::fromThrowable($exception);
        }

        if (null === $validationError) {
            return true;
        }

        $output->writeln(
            print_r($validationError, true),
            OutputInterface::VERBOSITY_DEBUG | OutputInterface::OUTPUT_RAW
        );

        $output->writeln(
            [
                '<error>Failed to generate valid output.</error>',
                '<warning>Please report the issue and provide the "composer.lock" file of the current project to:</warning>',
                '<warning>https://github.com/CycloneDX/cyclonedx-php-composer/issues/new</warning>',
            ]
        );

        return false;
    }
}
