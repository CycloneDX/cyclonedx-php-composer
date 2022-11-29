[![shield_gh-workflow-test]][link_gh-workflow-test]
[![shield_packagist-version]][link_packagist]
[![shield_license]][license_file]  
[![shield_website]][link_website]
[![shield_slack]][link_slack]
[![shield_groups]][link_discussion]
[![shield_twitter-follow]][link_twitter]

----

# CycloneDX PHP Composer Plugin

A plugin for PHP's [Composer](https://getcomposer.org/)
that generates Software Bill of Materials (SBoM) in [CycloneDX](https://cyclonedx.org/) format.

The resulting SBOM documents follow [official specifications and standards](https://github.com/CycloneDX/specification),
and might have properties following [`cdx:npm` Namespace Taxonomy](https://github.com/CycloneDX/cyclonedx-property-taxonomy/blob/main/cdx/composer.md)
.

## Requirements

The latest version of this plugin
supports PHP `^8.0`
with Composer `^2.3`
.

There are older versions of this plugin available, which
support PHP `^5.5||^7.0||^8.0`
with Composer `^1.0||^2.0`
.

## Installation

As a global composer plugin:

```shell
composer global require cyclonedx/cyclonedx-php-composer
```

As a development dependency of the current project:

```shell
composer require --dev cyclonedx/cyclonedx-php-composer
```

## Usage

After successful installation, the composer command `make-bom` is available.

```text
$ composer make-bom --help
Description:
  Generate a CycloneDX Bill of Materials from a PHP composer project.

Usage:
  make-bom [options] [--] [<composer-file>]

Arguments:
  composer-file                      Path to composer config file.
                                     Defaults to "composer.json" file in working directory.

Options:
      --output-format=OUTPUT-FORMAT  Which output format to use.
                                     {choices: "XML", "JSON"} [default: "XML"]
      --output-file=OUTPUT-FILE      Path to the output file.
                                     Set to "-" to write to STDOUT [default: "-"]
      --omit=OMIT                    Omit dependency types.
                                     {choices: "dev", "plugin"} (multiple values allowed)
      --spec-version=SPEC-VERSION    Which version of CycloneDX spec to use.
                                     {choices: "1.4", "1.3", "1.2", "1.1"} [default: "1.4"]
      --validate|--no-validate       Validate the resulting output
      --mc-version=MC-VERSION        Version of the main component.
                                     This will override auto-detection.
  -h, --help                         Display help for the given command. When no command is given display help for the list command
```

## Demo

For a demo of _cyclonedx-php-composer_ see the [demo project][demo_readme].

## Internals

This Composer-Plugin utilizes the [CycloneDX library][cyclonedx-library] to generate the actual data structures.

This Composer-Plugin does **not** expose any additional _public_ api or classes - all code is marked as `@internal` and might change without any notice during version upgrades.

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

[shield_gh-workflow-test]: https://img.shields.io/github/workflow/status/CycloneDX/cyclonedx-php-composer/PHP%20CI/master?logo=GitHub&logoColor=white "build"
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
