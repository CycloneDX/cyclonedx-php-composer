<?php

declare(strict_types=1);

$header = <<<'EOF'
This file is part of CycloneDX PHP Composer Plugin.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

SPDX-License-Identifier: Apache-2.0
Copyright (c) OWASP Foundation. All Rights Reserved.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests');

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules(
    // docs: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/doc/rules/index.rst
    // assistance via tool: https://mlocati.github.io/php-cs-fixer-configurator/
        [
            '@PHP71Migration:risky' => true,
            '@PHP73Migration' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'declare_strict_types' => true,
            'phpdoc_order' => true,
            'header_comment' => ['header' => $header],
            'fopen_flags' => ['b_mode' => true],
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder);
