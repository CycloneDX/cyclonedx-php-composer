# Changelog

All notable changes to this project will be documented in this file.

## unreleased

<!-- add unreleased items here -->

* Refactor
    * Migrated to `cyclonedx/cyclonedx-library:^4.0` (via [#619])
* Dependencies
    * Raised dependency `cyclonedx/cyclonedx-library:^4.0`, was `:^4.0` (via [#619])

[#619]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/619

## 6.1.0 - 2026-01-07

* Added
  * Officially support PHP 8.5 ([#595] via [#587])
* Refactor
  * Migrated to `cyclonedx/cyclonedx-library:^3.9` (via [#594])
* Dependencies
  * Raised dependency `cyclonedx/cyclonedx-library:^3.9`, was `:^3.3` (via [#594])

[#594]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/594
[#595]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/595
[#587]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/587

## 6.0.0 - 2025-11-17

* Breaking Change
  * Fix: no longer issue git/hg commit IDs when analysing dev-resource. ([#586] via [#588])

[#586]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/586
[#588]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/588

## 5.3.0 - 2025-10-27

Added _basic_ support for [_CycloneDX_ Specification-1.7](https://github.com/CycloneDX/specification/releases/tag/1.7).

* Changed
  * This tool may support _CycloneDX_ Specification-1.7 now (via [#579])  
    This feature depends on `cyclonedx/cyclonedx-library:^3.8`.
* Refactor
  * Reworked internals to automatically support any SpecVersion provided by `cyclonedx/cyclonedx-library`. (via [#579])  
    Previously, the supported versions were managed by this very tool and needed manual updates.

[#579]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/579

## 5.2.3 - 2025-05-12

Maintenance release.

## 5.2.2 - 2025-02-18

* Added
  * Officially support PHP 8.4 ([#500] via [#522])
* Misc
  * Added`Override` markers where needed (via [#531])  
    See also: <https://wiki.php.net/rfc/marking_overriden_methods>

[#500]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/500
[#522]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/522
[#531]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/531

## 5.2.1 - 2025-01-27

* Added
  * Officially support Composer 2.8 ([#520] via [#523])
  * Officially support Composer 2.7 ([#521] via [#523])
* Style
  * Applied latest PHP Coding Standards (via [#507])
* Misc
  * Various refactors

[#507]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/507
[#520]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/520
[#521]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/521
[#523]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/523

## 5.2.0 - 2024-04-30

* Added
  * Declared licenses are marked as such ([#474] via [#479]) 
* Dependencies
  * Raised dependency `cyclonedx/cyclonedx-library:^3.3`, was `:^3.2` (via [#479])

[#474]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/474
[#479]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/479

## 5.1.0 - 2024-04-23

Added _basic_ support for [_CycloneDX_ Specification-1.6](https://github.com/CycloneDX/specification/releases/tag/1.6).

* Changed
  * This tool supports _CycloneDX_ Specification-1.6 now (via [#477])
* Added
  * CLI switch `--spec-version` now supports value `1.6` to reflect _CycloneDX_ Specification-1.6 (via [#477])  
    Default value for that switch is unchanged - still `1.5`.
* Style
  * Applied latest PHP Coding Standards (via [#469])
* Dependencies
  * Raised dependency `cyclonedx/cyclonedx-library:^3.2`, was `:^3.1` (via [#477])

[#469]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/469
[#477]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/477

## 5.0.1 - 2024-02-05

* Style
  * Applied latest PHP Coding Standards (via [#451], [#459])

[#451]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/451
[#459]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/459

## 5.0.0 - 2023-12-03

* BREAKING changes
  * CLI switch `--spec-version` defaults to `1.5`, was `1.4` ([#442] via [#441])
* Dependencies
  * Raised dependency `cyclonedx/cyclonedx-library:^3.1`, was `:^2.3 || ^3.0` (via [#441])

[#441]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/441
[#442]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/442

## 4.2.3 - 2023-11-27

Maintenance release.

* Misc
  * Officially support PHP 8.3 (via [#342])

[#342]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/342

## 4.2.2 - 2023-11-05

* Added
  * SBOM results might have the `externalReferences[].comment` populated (via [#432])
* Fixed
  * SBOM results might have the `externalReferences[].hashes` populated ([#430] via [#432])  
    The hashes might have wrongly appeared as `components[].hashes` before.

[#430]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/430
[#432]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/432

## 4.2.1 - 2023-10-27

* Docs
  * Moved all non-public API into a sub-namespace called `_internal`, so that its reliability is obvious. (via [#427])

[#427]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/427

## 4.2.0 - 2023-09-04

* Added
  * SBOM result might have additional items in `metadata.tools` populated ([#402] via [#403]; [#404] via [#405])

[#402]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/402
[#403]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/403
[#404]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/404
[#405]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/405

## 4.1.1 - 2023-08-28

* Dependencies
  * Requires `cyclonedx/cyclonedx-library:^2.3||^3.0`, was `:^2.3` (via [#398])
* Style
  * Applied latest PHP Coding Standards (via [#395])

[#395]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/395
[#398]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/398

## 4.1.0 - 2023-07-04

Added support for [_CycloneDX_ Specification-1.5](https://github.com/CycloneDX/specification/releases/tag/1.5).

* Changed
  * This tool supports _CycloneDX_ Specification-1.5 now ([#380] via [#383])
* Added
  * CLI switch `--spec-version` now supports value `1.5` to reflect _CycloneDX_ Specification-1.5 ([#380] via [#383])  
    Default value for that switch is unchanged - still `1.4`.
* Dependencies
  * Requires `cyclonedx/cyclonedx-library:^2.3`, was `:^2.1` ([#380] via [#383])

[#380]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/380
[#383]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/383

## 4.0.2 - 2023-04-30

* Fixed
  * Typo: "compoer" -> "composer" ([#367] via [#368])

[#367]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/367
[#368]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/368

## 4.0.1 - 2023-04-24

* Fixed
  * Improved error reporting in case an invalid BOM would be created (via [#363]) 

[#363]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/363

## 4.0.0 - 2023-03-31

Based on [OWASP Software Component Verification Standard for Software Bill of Materials](https://scvs.owasp.org/scvs/v2-software-bill-of-materials/)
(SCVS SBOM) criteria, this tool is now capable of producing SBOM documents almost passing Level-2 (only signing needs to be done externally).  
Affective changes based on these SCVS SBOM criteria:

* 2.1  – Added Support for CycloneDX 1.4 (via [#250])
* 2.3  – SBOM has a unique identifier ([#279] via [#250], [#353])
* 2.7  – SBOM is timestamped ([#112] via [#250])
* 2.9  – Accuracy of Inventory was improved  ([#102], [#122], [#261], [#313] via [#250])
* 2.10 – Accuracy of Inventory of all test components was improved ([#102], [#122], [#261], [#313] via [#250])
* 2.11 – SBOM metadata was enhanced ([#171] via [#250])
* 2.15 – SPDX license expression detection fixed ([#128] via [#250])

### 4.0.0 - Details

* BREAKING changes
  * Removed support for PHP `<8.1` ([#91], [#128] via [#250])
  * Removed support for Composer `<2.3` ([#153] via [#250])
  * CLI
    * Removed deprecated composer command `make-bom`, call `composer CycloneDX:make-sbom` instead ([#293] via [#309])
    * Changed option `output-file` to default to `-` now, which causes to print to STDOUT (via [#250])
    * Removed option `exclude-dev` in favor of new option `omit` (via [#250])
    * Removed option `exclude-plugins` in favor of new option `omit` (via [#250])
    * Removed option `no-version-normalization` ([#102] via [#250])
  * SBOM results
    * Components' version is no longer artificially normalized ([#102] via [#250])
  * Dependencies
    * Requires `cyclonedx/cyclonedx-library:^2.1`, was `:^1.4.2` ([#128] via [#250], [#353])
* Changed
  * Evidence analysis prefers actually installed packages over lock file ([#122] via [#250])
  * Root component's versions is unset, if version detection fails ([#154] via [#250])
  * Composer packages of type "composer-installer" are treated as composer plugins (via [#250])
* Added
  * Evidence collection knows actually installed packages ([#122] via [#250])
  * SBOM results
    * Support for CycloneDX Spec v1.4 (via [#250])
    * might have `serialnumber` populated ([#279] via [#250], [#353])
    * might have `metadata.timestamp` populated ([#112] via [#250])
    * might have `metadata.tools[].tool.externalReferences` populated ([#171] via [#250])
    * might have `components[].component.author` populated ([#261] via [#250])
    * might have `components[].component.properties` populated according to [`cdx:composer` Namespace Taxonomy](https://github.com/CycloneDX/cyclonedx-property-taxonomy/blob/main/cdx/composer.md) ([#313] via [#250])
  * CLI
    * New option `omit` (via [#250])
    * New switch `validate` to override `no-validate` (via [#250])
    * New switches `output-reproducible` and `no-output-reproducible` (via [#250])
* Misc
  * Added demo and reproducible continuous integration test "devReq" that is dedicated to composer's `require-dev` feature (via [#250])
  * Reworked demo setups to be more global-install like (via [#250])

[#91]:  https://github.com/CycloneDX/cyclonedx-php-composer/issues/91
[#102]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/102
[#112]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/112
[#122]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/122
[#128]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/128
[#153]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/153
[#154]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/154
[#171]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/171
[#250]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/250
[#261]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/261
[#279]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/279
[#293]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/293
[#309]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/309
[#313]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/313
[#353]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/353

## 3.11.0 - 2023-02-11

* Changed
  * CLI via `composer make-bom` became deprecated, use `composer CycloneDX:make-sbom` instead. ([#293] via [#308])  
    The composer command `make-bom` will be removed in the next major version.

[#293]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/293
[#308]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/308

## 3.10.2 - 2022-09-15

Maintenance Release.

* Legal
  * Transferred copyright to OWASP Foundation. (via [#244])

[#244]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/244

## 3.10.1 - 2022-08-16

* Maintenance release.

## 3.10.0 - 2022-04-02

* Dependencies
  * Raised dependency `cyclonedx/cyclonedx-library:^1.4.2`, was `:^1.3.1`. (via [#192])
* Misc
  * Adjusted internal typing and typehints. (via [#192])
  * Improved compatibility to Composer v2.3 (via [#212])

[#192]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/192
[#212]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/212

## 3.9.2 - 2021-12-04

* Fixed
  * ExternalReferences fetched from composer's `support.email` are correctly prefixed with "mailto:". (via [#161])  
    Value was unmodified in the past.

[#161]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/161

## 3.9.1 - 2021-12-03

* Fixed
  * XML validation error for ExternalReference. ([#158] via [#159])
* Changed
  * The `ValidationError` message requests reporting with the "ValidationError" issue template. (via [#160])  
    No template was used in the past.

[#158]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/158
[#159]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/159
[#160]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/160

## 3.9.0 - 2021-12-01

* Added
  * The resulting SBoM hold ExternalReferences as fetched from package descriptions. (via [#145])

[#145]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/145

## 3.8.0 - 2021-11-30

* Fixed
  * Compatibility with composer v2.0.0 to v2.0.4 was improved. (via [#152])
  * Possible crashes when composer was not able to detect component's version properly.

[#152]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/152

## 3.7.0 - 2021-11-10

* Added
  * CLI got a new switch `--no-version-normalization`. (via [#138])  
    That allows to omit component version-string normalization.  
    Per default this plugin will normalize version strings by stripping leading "v".  
    This is a compatibility-switch. The next major-version of this plugin will not modify component versions. (see [#102])

[#138]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/138
[#102]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/102

## 3.6.0 - 2021-10-15

* Added
  * CLI got a new option `--mc-version`. (via [#133])  
    That allows to set the main component's version in the resulting SBoM,
    so that the auto-detection can be overridden.
* Fixed
  * The resulting SBoM's main component's `purl` does not get a version assigned,
    if the version auto-detection fails. (via [#134])

[#133]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/133
[#134]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/134

## 3.5.0 - 2021-10-07

* Changed
  * Core library
    * Was moved to an own package: <https://packagist.org/packages/cyclonedx/cyclonedx-library>  
      The new external package/library is a one-to-one copy of the original code from this project.  
      The new external package/library is a dependency/required of this project. So usage/leverage of the original code is still possible without any changes for third parties.  
      See [#87] for details.

[#87]: https://github.com/CycloneDX/cyclonedx-php-composer/issues/87

## 3.4.1 - 2021-09-16

* Fixed
  * Improved compatibility to composer. (via [#125])  
    This was made possible since composer's type hints are getting fixed.  
    See <https://github.com/composer/composer/releases/tag/2.1.7>  
    > Added many type annotations internally, which may have an effect on CI/static analysis for people using Composer as a dependency.

[#125]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/125

## 3.4.0 - 2021-09-12

* Changed
  * Core library
    * Some repository data-types are lists of unique items, so no duplicates are kept.  
      Affected classes/data-types:
      * `ComponentRepository`
      * `DisjunctiveLicenseRepository`
      * `ToolRepository`
* Added
  * CLI via `composer make-bom`
    * Will try to populate dependencies of the SBoM result.
  * Core library
    * Added `BomRef` model to link bom elements in general.  
      Added `BomRefRepository` data type as a collection of unique `BomRef`.
    * Added bomRef to `Component` model to link components as dependencies.  
      Added dependencies to `Component` model.
    * Added ability to serialize `dependencies` to XML.
    * Added ability to serialize `dependencies` to JSON.
* Misc
  * Moved development docs to [`docs/dev/`](docs/dev).
  * Refactored the plugin's internals.

## 3.3.1 - 2021-07-29

* Fixed
  * CLI via `composer make-bom`
    * Will ignore "AliasPackages" when generating the SBoM, since their alias-target is part of the SBoM already.

## 3.3.0 - 2021-07-25

* Changed
  * Core library
    * SerializersGroups will skip unsupported elements silently, instead of forwarding caught exceptions.  
      This results in an overall smoother SBoM generation process, just as intended.
* Added
  * CLI via `composer make-bom`
    * Will try to populate metadata of the SBoM result.
  * Core library
    * Added models for spec elements: `metadata`, `tools`, `tool`
    * Added ability to serialize `metadata` to XML.
    * Added ability to serialize `metadata` to JSON.
* Fixed
  * CLI via `composer make-bom`
    * composer packages of type `project` or `composer-plugin`
      result as CycloneDX component of type `application`, was `library`.
* Misc
  * Updated demos/examples to reflect current state of SBoM results including metadata.
  * Split some tests to more fine-grained scenarios.

## 3.2.0 - 2021-07-19

* Changed
  * CLI via `composer make-bom`
    * All informational/error output will appear on _STDERR_, was _STDOUT_.
      Output of the SBoM might still happen on _STDOUT_.  
      This makes utilization of _STDOUT_ via `--output-file=-` more flexible (pipe, redirect)
      whilst verbosity can be increased via `-v`.
* Added
  * CLI via `composer make-bom`
    * Added an optional argument `composer-file`.  
      If given, then the SBoM is generated based on that file instead of the file in the current working directory.  
      This enables the plugin to analyze projects outside the plugin's own setup.
* Fixed
  * Fixed detection of invalid/outdated composer lock file.
  * Fixed a rare case that caused the CLI to crash unexpectedly, if the composer lock file was unexpected.
* Misc
  * Added composer keywords.
  * Refactored the plugin's internals.
  * Added more tests for internals.

## 3.1.1 - 2021-07-13

* Misc
  * Updated some documentation.
  * Bumped some dev-tools.
  * Added normalizer for `composer.json` files.

## 3.1.0 - 2021-07-13

* Added
  * CLI via `composer make-bom`
    * Per default the command will validate the resulting SBoM before writing it to file/stdOut.
    * Added a switch `--no-validate` to disable result validation.
    * When the verbosity at "debug" level, then detailed debug info will be put out.
      This should help to find validation issues.
  * Validation classes/methods to test SBoM
    in XML and JSON format
    for spec 1.1, 1.2, 1.3

## 3.0.0 - 2021-07-05

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

## 2.1.1 - 2021-07-05

* Maintenance release.

## 2.1.0 - 2021-05-24

* Added
  * CLI got an option `--output-format` to decide the output format. (via [#80])  
    Supported values: "XML", "JSON".  
    Defaults to "XML".  
    The use of this new option replaces the switch `--json`.
* Deprecated
  * CLI switch `--json` was marked as deprecated. (via [#80])  
    Use option `--output-format=JSON` instead.

[#80]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/80

## 2.0.3 - 2021-05-13

* Misc
  * Removed `php-cs-fixer` config from dist release.

## 2.0.2 - 2021-05-13

* Misc
  * Applied latest rules of `php-cs-fixer` to the code. (via [#78])

[#78]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/78

## 2.0.1 - 2021-04-11

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

## 2.0 - 2021-02-06

* Breaking changes
  * Removed support for PHP < 7.1 (via [#17])
* Added
  * Support for PHP 8 (via [#17])

[#17]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/17

## 1.2 - 2021-02-06

* Added
  * Initial JSON support (via [#16])
* Fixed
  * Some cases when the XML BoM generator created schema-invalid data. (via [#15])
  * Added missing but needed composer requirements `ext-xmlwriter`. (via [#11])

[#16]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/16
[#15]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/15
[#11]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/11

## 1.1 - 2020-11-25

* Added
  * Support for composer v2 (via [#9])

[#9]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/9

## 1.0.1 - 2020-10-13

* Fixed
  * Removed unneeded double forward slash from package URLs (via [#7])
* Misc
  * Added release workflow (via [#8])

[#7]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/7
[#8]: https://github.com/CycloneDX/cyclonedx-php-composer/pull/8

## 1.0 - 2019-12-05

Initial release.
