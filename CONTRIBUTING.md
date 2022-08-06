# Contributing

Pull requests are welcome.
But please read the
[CycloneDX contributing guidelines](https://github.com/CycloneDX/.github/blob/master/CONTRIBUTING.md)
first.

## Setup

The development-setup requires PHP >= 7.4,
even though the project might support PHP 7.3 on runtime.

To start developing simply run `composer run-script dev-setup` to install dev-dependencies and tools.

## Tests

Make sure

* to run `composer run-script cs-fix` to have the coding standards applied.
* to run `composer run-script test` and pass all tests.

## Sign your commits

Please sign your commits,
to show that you agree to publish your changes under the current terms and licenses of the project.

```shell
git commit --signed-off ...
```
