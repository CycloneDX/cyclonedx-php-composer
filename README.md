[![Build Status](https://github.com/CycloneDX/cyclonedx-php-composer/workflows/PHP%20CI/badge.svg)](https://github.com/CycloneDX/cyclonedx-php-composer/actions?workflow=PHP+CI)
[![Packagist Version](https://img.shields.io/packagist/v/cyclonedx/bom)](https://packagist.org/)
[![License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)][License]
[![Website](https://img.shields.io/badge/https://-cyclonedx.org-blue.svg)](https://cyclonedx.org/)
[![Group Discussion](https://img.shields.io/badge/discussion-groups.io-blue.svg)](https://groups.io/g/CycloneDX)
[![Twitter](https://img.shields.io/twitter/url/http/shields.io.svg?style=social&label=Follow)](https://twitter.com/CycloneDX_Spec)

# CycloneDX PHP Composer Plugin

## Usage

### Installation

`composer require --dev cyclonedx/cyclonedx-php-composer`

### Options

```sh 
$ composer makeBom -h
Usage:
  makeBom [options]

Options:
      --outputFile=OUTPUTFILE    Path to the output file (default is bom.xml)
      --excludeDev               Exclude dev dependencies
      --excludePlugins           Exclude composer plugins
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
