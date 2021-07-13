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

namespace CycloneDX\Tests\Core\Validation\Helpers;

use CycloneDX\Core\Resources;
use CycloneDX\Core\Validation\Helpers\JsonSchemaRemoteRefProviderForSnapshotResources;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Validation\Helpers\JsonSchemaRemoteRefProviderForSnapshotResources
 */
class JsonSchemaRemoteRefProviderForSnapshotResourcesTest extends TestCase
{
    public function testGetSchemaDataFailsIfUrlNotParsable(): void
    {
        $url = 'http://foobar';
        $provider = new JsonSchemaRemoteRefProviderForSnapshotResources();

        $got = $provider->getSchemaData($url);

        self::assertFalse($got);
    }

    public function testGetSchemaDataFailsIfNotSnapshot(): void
    {
        $url = '/foo/bar';
        $provider = new JsonSchemaRemoteRefProviderForSnapshotResources();

        $got = $provider->getSchemaData($url);

        self::assertFalse($got);
    }

    public function testGetSchemaDataFailsIfNotExists(): void
    {
        $url = 'http://foo.bar/bar.SNAPSHOT.schema.json';
        $provider = new JsonSchemaRemoteRefProviderForSnapshotResources();

        $got = $provider->getSchemaData($url);

        self::assertFalse($got);
    }

    public function testGetSchemaData(): void
    {
        $url = 'http://foo.bar/'.Resources::FILE_SPDX_JSON_SCHEMA;
        $provider = new JsonSchemaRemoteRefProviderForSnapshotResources();

        $got = $provider->getSchemaData($url);

        self::assertNotFalse($got);
        self::assertEquals(
            json_decode(file_get_contents(Resources::FILE_SPDX_JSON_SCHEMA), false),
            $got
        );
    }
}
