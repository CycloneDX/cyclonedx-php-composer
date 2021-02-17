[![Build Status](https://github.com/CycloneDX/cyclonedx-php-composer/workflows/PHP%20CI/badge.svg)](https://github.com/CycloneDX/cyclonedx-php-composer/actions?workflow=PHP+CI)
[![Packagist Version](https://img.shields.io/packagist/v/cyclonedx/cyclonedx-php-composer)](https://packagist.org/packages/cyclonedx/cyclonedx-php-composer)
[![License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)][License]
[![Website](https://img.shields.io/badge/https://-cyclonedx.org-blue.svg)](https://cyclonedx.org/)
[![Slack Invite](https://img.shields.io/badge/Slack-Join-blue?logo=slack&labelColor=393939)](https://cyclonedx.org/slack/invite)
[![Group Discussion](https://img.shields.io/badge/discussion-groups.io-blue.svg)](https://groups.io/g/CycloneDX)
[![Twitter](https://img.shields.io/twitter/url/http/shields.io.svg?style=social&label=Follow)](https://twitter.com/CycloneDX_Spec)

# CycloneDX PHP Composer Plugin

A plugin for PHP's [Composer](https://getcomposer.org/) that generates Bill of Materials in [CycloneDX](https://cyclonedx.org/) format.

## Usage

### Requirements

The plugin supports PHP `^7.3 || ^8.0`
with composer `^1.3 || ^2.0`.

### Installation

`composer require --dev cyclonedx/cyclonedx-php-composer`

### Options

After successful installation, the composer command `make-bom` is available.

```
$ composer make-bom -h
Usage:
  make-bom [options]

Options:
      --output-file=OUTPUT-FILE  Path to the output file (default is bom.xml or bom.json).
                                 Set to "-" to write to STDOUT.
      --exclude-dev              Exclude dev dependencies
      --exclude-plugins          Exclude composer plugins
      --json                     Produce the BOM in JSON format (preview support)
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

# Contributing

Feel free to open pull requests.

To start developing simply run `tools/composer run-script dev-setup` to install dev-dependencies and tools.

Make sure 
* to run `tools/composer run-script cs-fix` to have your changes aligned with our coding standards.  
* to run `tools/composer run-script test` and pass all tests.

# License

Permission to modify and redistribute is granted under the terms of the Apache 2.0 license. See the [LICENSE] file for the full license.

[License]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/LICENSE
