#!/usr/bin/env php
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

namespace tools;

/**
 * schema downloader.
 *
 * @internal
 *
 * @author jkowalleck
 */

const SOURCE_ROOT = 'https://raw.githubusercontent.com/CycloneDX/specification/master/schema/';
const TARGET_ROOT = __DIR__.'/../../res/';

abstract class BomXsd
{
    public const Versions = ['1.0', '1.1', '1.2', '1.3'];
    public const SourcePattern = SOURCE_ROOT.'bom-%s.xsd';
    public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.xsd';
    public const Replace = [
        'schemaLocation="http://cyclonedx.org/schema/spdx"' => 'schemaLocation="spdx.SNAPSHOT.xsd"',
    ];
}

abstract class BomJsonLax
{
    public const Versions = ['1.2', '1.3'];
    public const SourcePattern = SOURCE_ROOT.'bom-%s.schema.json';
    public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.schema.json';
    public const Replace = [
        'spdx.schema.json' => 'spdx.SNAPSHOT.schema.json',
    ];
}

abstract class BomJsonStrict extends BomJsonLax
{
    public const SourcePattern = SOURCE_ROOT.'bom-%s-strict.schema.json';
    public const TargetPattern = TARGET_ROOT.'bom-%s-strict.SNAPSHOT.schema.json';
}

const Others = [
    SOURCE_ROOT.'spdx.schema.json' => TARGET_ROOT.'spdx.SNAPSHOT.schema.json',
    SOURCE_ROOT.'spdx.xsd' => TARGET_ROOT.'spdx.SNAPSHOT.xsd',
];

foreach ([
             BomXsd::class,
             BomJsonLax::class,
             BomJsonStrict::class,
         ] as $class) {
    foreach ($class::Versions as $version) {
        $source = sprintf($class::SourcePattern, $version);
        $target = sprintf($class::TargetPattern, $version);

        $content = file_get_contents($source);
        $content = strtr($content, $class::Replace);
        file_put_contents($target, $content);
    }
}

foreach (Others as $source => $target) {
    file_put_contents($target, file_get_contents($source));
}
