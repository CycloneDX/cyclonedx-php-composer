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
use Composer\Factory as ComposerFactory;
use Composer\IO\IOInterface;
use CycloneDX\Composer\Builder;
use CycloneDX\Core\Serialization;
use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\SpecFactory;
use CycloneDX\Core\Validation\Validator;
use CycloneDX\Core\Validation\Validators;
use DateTime;
use DomainException;
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
    public function __construct(Options $options, string $name)
    {
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
        } catch (Throwable $error) {
            $io->writeErrorRaw((string) $error, true, IOInterface::DEBUG);
            $io->writeError(sprintf(
                '<error>InputError: %s</error>',
                OutputFormatter::escape($error->getMessage())
            ));

            return self::INVALID;
        }
        $io->writeErrorRaw(__METHOD__.' Options: '.var_export($this->options, true), true, IOInterface::DEBUG);

        try {
            $spec = SpecFactory::makeForVersion($this->options->specVersion);
            $bom = $this->generateBom($io, $spec);
            $this->validateBom($bom, $spec, $io);
            $this->writeBom($bom, $io);
        } catch (Throwable $error) {
            $io->writeErrorRaw((string) $error, true, IOInterface::DEBUG);
            $io->writeError(sprintf(
                '<error>Error: %s</error>',
                OutputFormatter::escape($error->getMessage())
            ));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @throws Throwable on error
     *
     * @psalm-return non-empty-string
     */
    private function generateBom(IOInterface $io, Spec $spec): string
    {
        $io->writeError('<info>generate BOM...</info>', verbosity: IOInterface::VERBOSE);

        $builder = new Builder(
            \in_array('dev', $this->options->omit),
            \in_array('plugin', $this->options->omit),
            $this->options->mainComponentVersion,
        );

        $subjectComposer = (new ComposerFactory())->createComposer($io, $this->options->composerFile, fullLoad: true);
        /** @psalm-suppress RedundantConditionGivenDocblockType -- as with lowest-compatible dependencies this is needed  */
        \assert($subjectComposer instanceof \Composer\Composer);
        $bom = $builder->createBomFromComposer($subjectComposer);
        unset($subjectComposer);

        if (!$this->options->outputReproducible) {
            try {
                $bom->setSerialNumber(Builder::createRandomBomSerialNumber());
            } catch (\Exception) {
                /* pass */
            }
            $bom->getMetadata()->setTimestamp(new DateTime());
        }

        $selfComposer = (new ComposerFactory())->createComposer($io, __DIR__.'/../../composer.json',
            fullLoad: false, disablePlugins: true, disableScripts: true);
        /** @psalm-suppress RedundantConditionGivenDocblockType -- as with lowest-compatible dependencies this is needed  */
        \assert($selfComposer instanceof \Composer\PartialComposer);
        $bom->getMetadata()->getTools()->addItems(
            $builder->createToolFromPackage(
                $selfComposer->getPackage()
            )->setVersion(
                $this->options->getToolVersionOverride()
                    ?? trim(file_get_contents(__DIR__.'/../../semver.txt')
                    ))
        );
        unset($selfComposer);

        $io->writeError('<info>serialize BOM...</info>', verbosity: IOInterface::VERBOSE);
        /** @var Serialization\Serializer */
        $serializer = match ($this->options->outputFormat) {
            Format::JSON => new Serialization\JsonSerializer(new Serialization\JSON\NormalizerFactory($spec)),
            Format::XML => new Serialization\XmlSerializer(new Serialization\DOM\NormalizerFactory($spec)),
            default => throw new DomainException("unsupported format: {$this->options->outputFormat->name}"),
        };
        $io->writeErrorRaw('using '.$serializer::class, true, IOInterface::DEBUG);

        return $serializer->serialize($bom, prettyPrint: true);
    }

    /**
     * @param non-empty-string $bom
     *
     * @throws Throwable on error
     */
    private function validateBom(string $bom, Spec $spec, IOInterface $io): void
    {
        if (false === $this->options->validate) {
            $io->writeError('<info>skipped BOM validation.</info>', verbosity: IOInterface::VERBOSE);

            return;
        }
        $io->writeError('<info>validate BOM...</info>', verbosity: IOInterface::VERBOSE);
        /** @var Validator */
        $validator = match ($this->options->outputFormat) {
            Format::JSON => new Validators\JsonStrictValidator($spec),
            Format::XML => new Validators\XmlValidator($spec),
            default => throw new DomainException("unsupported format: {$this->options->outputFormat->name}"),
        };
        $io->writeErrorRaw('using '.$validator::class, true, IOInterface::DEBUG);

        $validationError = $validator->validateString($bom);
        if (null !== $validationError) {
            throw new Errors\ValidationError($validationError->getMessage());
        }
    }

    /**
     * @param non-empty-string $bom
     *
     * @throws Throwable on error
     */
    private function writeBom(string $bom, IOInterface $io): void
    {
        $io->writeError('<info>write BOM...</info>', verbosity: IOInterface::VERBOSE);

        $outputFile = $this->options->outputFile;
        $outputStream = Options::VALUE_OUTPUT_FILE_STDOUT === $outputFile
            ? \STDOUT
            : fopen($outputFile, 'wb');
        if (false === $outputStream) {
            throw new Errors\OutputError("failed to open output: $outputFile");
        }

        $written = fwrite($outputStream, $bom);
        if (false === $written || 0 === $written) {
            throw new Errors\OutputError("failed to write BOM: $outputFile");
        }

        $io->writeError(
            sprintf(
                '<info>wrote %d bytes to %s</info>',
                $written,
                OutputFormatter::escape($outputFile)
            ),
            verbosity: IOInterface::VERY_VERBOSE
        );
    }
}
