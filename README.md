[![shield_gh-workflow-test]][link_gh-workflow-test]
[![shield_packagist-version]][link_packagist]
[![shield_license]][license_file]  
[![shiled_website]][link_website]
[![shield_slack]][link_slack]
[![shield_groups]][link_discussion]
[![shield_twitter-follow]][link_twitter]

----

# CycloneDX PHP Composer Plugin

A plugin for PHP's [Composer](https://getcomposer.org/) that generates Bill of Materials in [CycloneDX](https://cyclonedx.org/) format.

## Usage

### Requirements

The plugin supports PHP `^7.3 || ^8.0` with composer `^2.0`.  
There are older versions of this plugin available, which support php `^5.5 || ^7.0` with composer `^1.0 || ^2.0`.

### Installation

`composer require --dev cyclonedx/cyclonedx-php-composer`

### Options

After successful installation, the composer command `make-bom` is available.

```
$ composer make-bom -h
Usage:
  make-bom [options]

Options:
      --output-format=OUTPUT-FORMAT  Which output format to use.
                                     Values: "XML", "JSON" [default: "XML"]
      --output-file=OUTPUT-FILE      Path to the output file.
                                     Set to "-" to write to STDOUT.
                                     Depending on the output-format, default is one of: "bom.xml", "bom.json"
      --exclude-dev                  Exclude dev dependencies
      --exclude-plugins              Exclude composer plugins
      --spec-version=SPEC-VERSION    Which version of CycloneDX spec to use.
                                     Values: "1.1", "1.2", "1.3" [default: "1.3"]
      --no-validate                  Dont validate the resulting output
  -h, --help                         Display this help message
  -q, --quiet                        Do not output any message
  -V, --version                      Display this application version
      --ansi                         Force ANSI output
      --no-ansi                      Disable ANSI output
  -n, --no-interaction               Do not ask any interactive question
      --profile                      Display timing and memory usage information
      --no-plugins                   Whether to disable plugins.
  -d, --working-dir=WORKING-DIR      If specified, use the given directory as working directory.
      --no-cache                     Prevent use of the cache
  -v|vv|vvv, --verbose               Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Generate a CycloneDX Bill of Materials
```

## Demo

For a demo of _cyclonedx-php-composer_ see the [demo project][demo_readme].

## Contributing

Feel free to open pull requests.

To start developing simply run `composer run-script dev-setup` to install dev-dependencies and tools.

Make sure
* to run `composer run-script cs-fix` to have the coding standards applied.
* to run `composer run-script test` and pass all tests.

## License

Permission to modify and redistribute is granted under the terms of the Apache 2.0 license.
See the [LICENSE][license_file] file for the full license.

[license_file]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/LICENSE
[demo_readme]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/demo/README.md

[shield_gh-workflow-test]: https://img.shields.io/github/workflow/status/CycloneDX/cyclonedx-php-composer/PHP%20CI/master?logo=GitHub&logoColor=white "build"
[shield_packagist-version]: https://img.shields.io/packagist/v/cyclonedx/cyclonedx-php-composer?logo=&logoColor=white "packagist"
[shield_license]: https://img.shields.io/github/license/CycloneDX/cyclonedx-php-composer "license"
[shiled_website]: https://img.shields.io/badge/https://-cyclonedx.org-blue.svg "homepage"
[shield_slack]: https://img.shields.io/badge/slack-join-blue?logo=Slack&logoColor=white "slack join"
[shield_groups]: https://img.shields.io/badge/discussion-groups.io-blue.svg "groups discussion"
[shield_twitter-follow]: https://img.shields.io/badge/Twitter-follow-blue?logo=Twitter&logoColor=white "twitter follow"
[link_gh-workflow-test]: https://github.com/CycloneDX/cyclonedx-php-composer/actions/workflows/php.yml?query=branch%3Amaster
[link_packagist]: https://packagist.org/packages/cyclonedx/cyclonedx-php-composer
[link_website]: https://cyclonedx.org/
[link_slack]: https://cyclonedx.org/slack/invite
[link_discussion]: https://groups.io/g/CycloneDX
[link_twitter]: https://twitter.com/CycloneDX_Spec
