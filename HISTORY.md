# Changelog

## unreleased

## 3.1.1

* Misc
  * Updated some documentation.
  * Bumped some dev-tools.
  * Added normalizer for `composer.json` files.

## 3.1.0

* Added
  * CLI via `composer make-bom`
    * Per default the command will validate the resulting SBoM before writing it to file/stdOut.
    * Added a switch `--no-validate` to disable result validation.
    * When the verbosity at "debug" level, then detailed debug info will be put out.
      This should help to find validation issues.
  * Validation classes/methods to test SBoM
    in XML and JSON format
    for spec 1.1, 1.2, 1.3

## 3.0.0

* Breaking Changes
  * Now requires php `^7.3 || ^8.0`, was `^7.1 || ^8.0`.
  * Now requires composer v2 - `composer-plugin-api:^2.0`, was `composer-plugin-api:^1.1||^2.0`. 
  * CLI via `composer make-bom`
    * Now defaults to the latest supported version of CycloneDX spec: 1.3  
      See option `--spec-version`.
    * Deprecated switch `--json` was removed.  
      Use option `--output-format=JSON` instead.
  * Component's license in SpdxLicenseExpression format is no longer split into disjunctive licenses.
    Still using licenses properly in the resulting output file.
  * Complete rewrite/refactor.  
    Expect library classes/methods/functions to be removed, renamed or incompatible to previous versions - see the source for changes.
* Added
  * CLI
    * Output is less verbose per default. Can be increased via `-v`, `-vv`, `-vvv`.
    * Support for output to _STDOUT_. Use option `--output-file=-`.
    * Added an optional option `--spec-version` for the CycloneDX spec version.  
      Supported values: "1.1", "1.2", "1.3".  
      Defaults to "1.3".
  * Support for JSON output format.  
    JSON support was a preview before and became a basic part of the plugin now.
* Removed
  * This plugin no longer supports `php<7.3`.
  * This plugin no longer supports composer v1.
  * CLI
    * Deprecated switch `--json` was removed.  
      Use option `--output-format=JSON` instead.
* Fixed
  * Some cases when the JSON SBoM generator created schema-invalid data.
* Misc
  * Utilize [`package-url/packageurl-php`](https://packagist.org/packages/package-url/packageurl-php)
    over own implementation.
  * Added more tests during the build process.
  * Added [Psalm](https://psalm.dev/) & [PHP-CS-Fixer](https://cs.symfony.com/) to the CI chain and fixed all findings accordingly.
  * Added a demo run of the plugin to the CI chain.

## 2.1.1

* Maintenance release.

## 2.1.0

* Added
  * CLI got an option `--output-format` to decide the output format. (via [#80])  
    Supported values: "XML", "JSON".  
    Defaults to "XML".  
    The use of this new option replaces the switch `--json`.
* Deprecated
  * CLI switch `--json` was marked as deprecated. (via [#80])  
    Use option `--output-format=JSON` instead.

[#80]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/80

## 2.0.3

* Misc
  * Removed `php-cs-fixer` config from dist release.

## 2.0.2

* Misc
  * Applied latest rules of `php-cs-fixer` to the code. (via [#78])

[#78]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/78

## 2.0.1

* Added
  * Support for slim dist-builds (via [#24])
* Misc
  * Pinned dev-requirements to exact versions to ensure reproducible tests. (via [#37])
  * Added (code) quality tests to the dev-process. (see [#23])
  * CI's unit-tests just run reasonable combinations of OperatingSystem, PhpVersions, dependencies. (via [#34], [#54])
  * applied coding standards to all php files. (via [#40])

[#23]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/23
[#24]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/24
[#34]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/34
[#37]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/37
[#40]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/40
[#54]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/54

## 2.0

* Breaking changes
  * Removed support for PHP < 7.1 (via [#17])
* Added
  * Support for PHP 8 (via [#17])

[#17]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/17

## 1.2

* Added
  * Initial JSON support (via [#16])
* Fixed
  * Some cases when the XML BoM generator created schema-invalid data. (via [#15])
  * Added missing but needed composer requirements `ext-xmlwriter`. (via [#11])

[#16]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/16
[#15]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/15
[#11]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/11

## 1.1

* Added
  * Support for composer v2 (via [#9])

[#9]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/9

## 1.0.1

* Fixed
  * Removed unneeded double forward slash from package URLs (via [#7])
* Misc
  * Added release workflow (via [#8])

[#7]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/7
[#8]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/8

## 1.0

Initial release.
