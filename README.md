[![shield_gh-workflow-test]][link_gh-workflow-test]
[![shield_packagist-version]][link_packagist]
[![shield_license]][license_file]  
[![shield_website]][link_website]
[![shield_slack]][link_slack]
[![shield_groups]][link_discussion]
[![shield_twitter-follow]][link_twitter]

----

# CycloneDX PHP Composer Plugin

A plugin for PHP's _[Composer](https://getcomposer.org/)_
that generates Software Bill of Materials (SBOM) in _[CycloneDX](https://cyclonedx.org/)_ format.

The resulting SBOM documents follow [official specifications and standards](https://github.com/CycloneDX/specification),
and might have properties following [`cdx:composer` Namespace Taxonomy](https://github.com/CycloneDX/cyclonedx-property-taxonomy/blob/main/cdx/composer.md)
.

## Requirements

* PHP `^8.1`
* Composer `^2.3`

However, there are older versions of this plugin available, which
support PHP `^5.5||^7.0||^8.0`
with Composer `^1.0||^2.0`
.

## Installation

As a global _Composer_ plugin:

```shell
composer global require cyclonedx/cyclonedx-php-composer
```

As a development dependency of the current project:

```shell
composer require --dev cyclonedx/cyclonedx-php-composer
```

## Usage

After successful installation, the _Composer_ command `CycloneDX:make-sbom` is available.

```text
$ composer CycloneDX:make-sbom --help
Description:
  Generate a CycloneDX Bill of Materials from a PHP Composer project.

Usage:
  CycloneDX:make-sbom [options] [--] [<composer-file>]

Arguments:
  composer-file                                       Path to Composer config file.
                                                      [default: "composer.json" file in current working directory]

Options:
      --output-format=OUTPUT-FORMAT                   Which output format to use.
                                                      {choices: "XML", "JSON"} [default: "XML"]
      --output-file=OUTPUT-FILE                       Path to the output file.
                                                      Set to "-" to write to STDOUT [default: "-"]
      --omit=OMIT                                     Omit dependency types.
                                                      {choices: "dev", "plugin"} (multiple values allowed)
      --spec-version=SPEC-VERSION                     Which version of CycloneDX spec to use.
                                                      {choices: "1.4", "1.3", "1.2", "1.1"} [default: "1.4"]
      --output-reproducible|--no-output-reproducible  Whether to go the extra mile and make the output reproducible.
                                                      This might result in loss of time- and random-based-values.
      --validate|--no-validate                        Validate the resulting output.
      --mc-version=MC-VERSION                         Version of the main component.
                                                      This will override auto-detection.
  -h, --help                                          Display help for the given command.
```

## Demo

For a demo of _cyclonedx-php-composer_ see the [demo projects][demo_readme].

## Internals

This _Composer_ plugin utilizes the [CycloneDX library][cyclonedx-library] to generate the actual data structures.

This _Composer_ plugin does **not** expose any additional _public_ API or classes - all code is marked as `@internal` and might change without any notice during version upgrades.

## Contributing

Feel free to open issues, bugreports or pull requests.  
See the [CONTRIBUTING][contributing_file] file for details.

## License

Permission to modify and redistribute is granted under the terms of the Apache 2.0 license.  
See the [LICENSE][license_file] file for the full license.

[license_file]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/LICENSE
[contributing_file]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/CONTRIBUTING.md
[demo_readme]: https://github.com/CycloneDX/cyclonedx-php-composer/blob/master/demo/README.md

[cyclonedx-library]: https://packagist.org/packages/cyclonedx/cyclonedx-library

[shield_gh-workflow-test]: https://img.shields.io/github/actions/workflow/status/CycloneDX/cyclonedx-php-composer/php.yml?branch=master&logo=GitHub&logoColor=white "build"
[shield_packagist-version]: https://img.shields.io/packagist/v/cyclonedx/cyclonedx-php-composer?logo=Packagist&logoColor=white "packagist"
[shield_license]: https://img.shields.io/github/license/CycloneDX/cyclonedx-php-composer?logo=open%20source%20initiative&logoColor=white "license"
[shield_website]: https://img.shields.io/badge/https://-cyclonedx.org-blue.svg "homepage"
[shield_slack]: https://img.shields.io/badge/slack-join-blue?logo=Slack&logoColor=white "slack join"
[shield_groups]: https://img.shields.io/badge/discussion-groups.io-blue.svg "groups discussion"
[shield_twitter-follow]: https://img.shields.io/badge/Twitter-follow-blue?logo=Twitter&logoColor=white "twitter follow"
[link_gh-workflow-test]: https://github.com/CycloneDX/cyclonedx-php-composer/actions/workflows/php.yml?query=branch%3Amaster
[link_packagist]: https://packagist.org/packages/cyclonedx/cyclonedx-php-composer
[link_website]: https://cyclonedx.org/
[link_slack]: https://cyclonedx.org/slack/invite
[link_discussion]: https://groups.io/g/CycloneDX
[link_twitter]: https://twitter.com/CycloneDX_Spec
