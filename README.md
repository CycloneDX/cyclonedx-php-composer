[![Build Status](https://github.com/CycloneDX/cyclonedx-php-composer/workflows/PHP%20CI/badge.svg)](https://github.com/CycloneDX/cyclonedx-php-composer/actions?workflow=PHP+CI)
[![Packagist Version](https://img.shields.io/packagist/v/cyclonedx/cyclonedx-php-composer)](https://packagist.org/packages/cyclonedx/cyclonedx-php-composer)
[![License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)][License]
[![Website](https://img.shields.io/badge/https://-cyclonedx.org-blue.svg)](https://cyclonedx.org/)
[![Group Discussion](https://img.shields.io/badge/discussion-groups.io-blue.svg)](https://groups.io/g/CycloneDX)
[![Twitter](https://img.shields.io/twitter/url/http/shields.io.svg?style=social&label=Follow)](https://twitter.com/CycloneDX_Spec)

# CycloneDX PHP Composer Plugin

A plugin for PHP's [Composer](https://getcomposer.org/) that generates Bill of Materials in [CycloneDX](https://cyclonedx.org/) format.

## Usage

### Requirements

The plugin supports PHP 5.5 or later. This includes PHP 7.0 and later.

### Installation

`composer require --dev cyclonedx/cyclonedx-php-composer`

**There's no stable release available yet**. You can test this plugin by either [setting your `minimum-stability` to `dev`](https://getcomposer.org/doc/04-schema.md#minimum-stability) or explicitly requesting the `dev-master` version: `composer require --dev cyclonedx/cyclonedx-php-composer:dev-master`.

### Options

After successful installation, the composer command `make-bom` is available.

```sh
$ composer make-bom -h
Usage:
  make-bom [options]

Options:
      --output-file=OUTPUT-FILE  Path to the output file (default is bom.xml)
      --exclude-dev              Exclude dev dependencies
      --exclude-plugins          Exclude composer plugins
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --profile                  Display timing and memory usage information
      --no-plugins               Whether to disable plugins.
  -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
      --no-cache                 Prevent use of the cache
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Generate a CycloneDX Bill of Materials
```

# License

Permission to modify and redistribute is granted under the terms of the Apache 2.0 license. See the [LICENSE] file for the full license.

[License]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/LICENSE
