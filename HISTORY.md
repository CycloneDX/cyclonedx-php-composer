# Changelog

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
