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
    /* docs: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
     * docs: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
     * assistance via tool: https://mlocati.github.io/php-cs-fixer-configurator/
     */
        [
            '@PHP81Migration' => true,
            '@PHP80Migration:risky' => true,
            '@PHPUnit100Migration:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'declare_strict_types' => true,
            'header_comment' => ['header' => $header],
            'global_namespace_import' => true,
            'fopen_flags' => ['b_mode' => true],
            'phpdoc_order' => true,
            'phpdoc_to_comment' => [
                'ignored_tags' => [
                    'psalm-suppress', // needed when PSALM introduced some issues that only manual hints can solve
                ],
            ],
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder);
