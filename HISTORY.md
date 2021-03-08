# Changelog

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
