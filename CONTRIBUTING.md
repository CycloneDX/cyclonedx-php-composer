# Contributing

Pull requests are welcome.
But please read the
[CycloneDX contributing guidelines](https://github.com/CycloneDX/.github/blob/master/CONTRIBUTING.md)
first.

## Issues

When creating a new issue, please select the appropriate issue type from the available templates and fill out the provided form.  
These templates ensure that all necessary information is captured consistently.

## Pullrequests

When opening a pull request, use the repository’s pull request template and complete all required fields.  
Keep each pull request focused on a single topic or problem.

Every pull request must reference an existing issue that it aims to address.  
If no issue exists for your topic, please create one first using the appropriate issue template, then link your pull request to it.

## Setup

The development-setup requires PHP >= 8.1.

To start developing simply run `composer run-script dev-setup` to install dev-dependencies and tools.

## Tests

Make sure

* to run `composer run-script cs-fix` to have the coding standards applied.
* to run `composer run-script test` and pass all tests.

## Sign off your commits

Please sign off your commits, to show that you agree to publish your changes under the current terms and licenses of the project
, and to indicate agreement with [Developer Certificate of Origin (DCO)](https://developercertificate.org/).

```shell
git commit -s ...
```
