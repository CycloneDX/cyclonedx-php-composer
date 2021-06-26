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

namespace CycloneDX\Tests\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialize\JsonSerializer;
use CycloneDX\Core\Spec\Spec11;
use CycloneDX\Core\Spec\Spec12;
use CycloneDX\Core\Spec\Spec13;
use DomainException;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema;

class SnapshotRemoteRefProvider implements JsonSchema\RemoteRefProvider
{
    public function getSchemaData($url)
    {
        $path = parse_url($url, \PHP_URL_PATH);
        if (false !== $path) {
            $file = basename($path);
            if (false !== preg_match('/\.SNAPSHOT\./', $file)) {
                $url = realpath(__DIR__.'/../../../res/'.$file);
            }
        }

        return json_decode(file_get_contents($url));
    }
}

/**
 * @coversNothing
 */
class JsonTest extends TestCase
{
    private $schemaContracts = [];

    private function getSchemaContract(string $schema): JsonSchema\SchemaContract
    {
        if (false === \array_key_exists($schema, $this->schemaContracts)) {
            $this->schemaContracts[$schema] = JsonSchema\Schema::import(
                $schema,
                new JsonSchema\Context(new SnapshotRemoteRefProvider())
            );
        }

        return $this->schemaContracts[$schema];
    }

    // region Spec 1.0
    // Spec 1.0 is not implemented
    // endregion Spec 1.0

    // region Spec 1.1

    /**
     * Schema 1.1 is not specified for JSON.
     */
    public function testSerialization11(): void
    {
        $spec = new Spec11();
        $serializer = new JsonSerializer($spec);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported format/i');

        $serializer->serialize(new Bom());
    }

    // endregion Spec 1.1

    // region Spec 1.2

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentHashAlgorithmsSpec12()
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentTypeSpec12()
     */
    public function testSchema12(Bom $bom): void
    {
        $spec = new Spec12();
        $schemaPath = realpath(__DIR__.'/../../../res/bom-1.2.SNAPSHOT.schema.json');

        self::assertIsString($schemaPath);
        self::assertFileExists($schemaPath);

        $serializer = new JsonSerializer($spec);

        $json = $serializer->serialize($bom);
        self::assertJson($json);
        $data = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);

        $schemaContracts = $this->getSchemaContract($schemaPath);
        self::assertInstanceOf(
            JsonSchema\Structure\ObjectItem::class,
            $schemaContracts->in($data) // throws on schema mismatch
        );
    }

    // endregion Spec 1.2

    // region Spec 1.3

    /**
     * This test might be slow.
     * This test might require online-connectivity.
     *
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::fullBomTestData
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentHashAlgorithmsSpec13()
     * @dataProvider \CycloneDX\Tests\_data\BomModelProvider::bomWithComponentTypeSpec13()
     */
    public function testSchema13(Bom $bom): void
    {
        $spec = new Spec13();
        $schemaPath = realpath(__DIR__.'/../../../res/bom-1.3.SNAPSHOT.schema.json');

        self::assertIsString($schemaPath);
        self::assertFileExists($schemaPath);

        $serializer = new JsonSerializer($spec);

        $json = $serializer->serialize($bom);
        self::assertJson($json);
        $data = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);

        $schemaContracts = $this->getSchemaContract($schemaPath);
        self::assertInstanceOf(
            JsonSchema\Structure\ObjectItem::class,
            $schemaContracts->in($data) // throws on schema mismatch
        );
    }

    // endregion Spec 1.3
}
