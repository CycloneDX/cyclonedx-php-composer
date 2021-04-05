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

use CycloneDX\BomXmlWriter;
use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversNothing
 */
class BomXmlWriterTest extends TestCase
{
    /**
     * @var OutputInterface
     */
    private $outputMock;

    /**
     * @var BomXmlWriter
     */
    private $bomXmlWriter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->bomXmlWriter = new BomXmlWriter($this->outputMock);
    }

    public function testBomXmlWriter(): void
    {
        $component = new Component();
        $component->setGroup('componentGroup');
        $component->setName('componentName');
        $component->setDescription('componentDescription');
        $component->setVersion('1.0');
        $component->setType('library');
        $component->setLicenses(['MIT', 'Apache-2.0']);
        $component->setHashes(['SHA-1' => '7e240de74fb1ed08fa08d38063f6a6a91462a815']);
        $bom = new Bom([$component]);

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__.'/schema/bom-1.1.xsd'));
    }

    /**
     * license is unknown to https://cyclonedx.org/schema/spdx
     * but BOM still valid output.
     */
    public function testUnknownSpdxLicense(): void
    {
        $component = new Component();
        $component->setGroup('componentGroup');
        $component->setName('componentName');
        $component->setDescription('componentDescription');
        $component->setVersion('1.0');
        $component->setType('library');
        $component->setLicenses(['proprietary']);
        $component->setHashes(['SHA-1' => '7e240de74fb1ed08fa08d38063f6a6a91462a815']);
        $bom = new Bom([$component]);

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__.'/schema/bom-1.1.xsd'));
    }

    /**
     * license is unknown to https://cyclonedx.org/schema/spdx
     * but BOM still valid output.
     */
    public function testCaseMismatchSpdxLicense(): void
    {
        $mismatch = ['mit', 'aPACHE-2.0'];
        $expected = ['MIT', 'Apache-2.0'];

        $component = new Component();
        $component->setGroup('componentGroup');
        $component->setName('componentName');
        $component->setDescription('componentDescription');
        $component->setVersion('1.0');
        $component->setType('library');
        $component->setLicenses($mismatch);
        $component->setHashes(['SHA-1' => '7e240de74fb1ed08fa08d38063f6a6a91462a815']);
        $bom = new Bom([$component]);

        $bomXml = $this->bomXmlWriter->writeBom($bom);

        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML($bomXml);
        self::assertTrue($domDocument->schemaValidate(__DIR__.'/schema/bom-1.1.xsd'));

        $domDocumentLicenses = [];
        foreach ($domDocument->getElementsByTagName('license') as $domDocumentLicense) {
            $domDocumentLicenses[] = $domDocumentLicense->getElementsByTagName('id')[0]->nodeValue;
        }
        self::assertSame($expected, $domDocumentLicenses);
    }
}
