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

use CycloneDX\BomJsonWriter;
use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversNothing
 */
class BomJsonWriterTest extends TestCase
{
    /**
     * @var OutputInterface
     */
    private $outputMock;

    /**
     * @var BomJsonWriter
     */
    private $bomJsonWriter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomJsonWriter = new BomJsonWriter($this->outputMock);
    }

    public function testBomJsonWriter(): void
    {
        $schemaJson = file_get_contents(__DIR__.'/schema/bom-1.2.schema-SNAPSHOT.json');
        $schema = Schema::import(json_decode($schemaJson, false));
        $component = new Component();
        $component->setGroup('componentGroup');
        $component->setName('componentName');
        $component->setDescription('componentDescription');
        $component->setVersion('1.0');
        $component->setType('library');
        $component->setLicenses(['MIT', 'Apache-2.0']);
        $component->setHashes(['SHA-1' => '7e240de74fb1ed08fa08d38063f6a6a91462a815']);
        $component->setPackageUrl('purl://packageurl');
        $bom = new Bom([$component]);

        $bomJson = $this->bomJsonWriter->writeBom($bom);

        // $schema->in() throws an exception if validation fails
        $schema->in(json_decode($bomJson, false));
        // this stops PHPUnit from flagging this as a risky test
        $this->expectNotToPerformAssertions();
    }
}
