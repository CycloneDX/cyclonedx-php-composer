# Demo: laravel 7.12.0

*ATTENTION*: this demo might use known vulnerable dependencies for showcasing purposes.

The output is _reproducible_, due to the [shipped composer-locked](project/composer.lock) versions.  
Therefore, the demo requires a special php environment, which is caused by the composer-requirements:
* php>=7.3, <8

## Reproducible results

Generated example results are also available at
[CycloneDX/sbom-examples](https://github.com/CycloneDX/sbom-examples/)
as "laravel-7.12.0".

* [`results/bom.1.1.xml`](results/bom.1.1.xml)
* [`results/bom.1.2.xml`](results/bom.1.2.xml)
* [`results/bom.1.3.xml`](results/bom.1.3.xml)
* [`results/bom.1.2.json`](results/bom.1.2.json)
* [`results/bom.1.3.json`](results/bom.1.3.json)


## Setup

For the sake of a demo, a relative path to the _cyclonedx-php-composer_ project is used,
so the current code is symlinked and taken into action.

To get the setup up and running, run from the demo directory:

```shell
composer -dproject install
```

## Usage examples

Run one of these from the demo directory:

* See _cyclonedx-php-composer_ help page:
  ```shell
  composer -dproject make-bom --help 
  ```
* Make XML sbom:
  ```shell
  composer -dproject make-bom --exclude-dev --spec-version=1.1 --output-format=XML --output-file="$PWD/results/bom.1.1.xml"
  composer -dproject make-bom --exclude-dev --spec-version=1.2 --output-format=XML --output-file="$PWD/results/bom.1.2.xml"
  composer -dproject make-bom --exclude-dev --spec-version=1.3 --output-format=XML --output-file="$PWD/results/bom.1.3.xml"
  ```
* Make JSON sbom:
  ```shell
  composer -dproject make-bom --exclude-dev --spec-version=1.2 --output-format=JSON --output-file="$PWD/results/bom.1.2.json"
  composer -dproject make-bom --exclude-dev --spec-version=1.3 --output-format=JSON --output-file="$PWD/results/bom.1.3.json"
  ```

## dev-maintenance

Lock-file should stay in a certain state, after updating dependencies.

Upgrade the `composer.lock` tile to the latest changes to the plugin via:
1. run `composer -dproject update 'cyclonedx/cyclonedx-php-composer'`
2. revert in the `composer.lock` some setup 
   * for package `cyclonedx/cyclonedx-php-composer`:
     * set `version` to `dev-master`
     * delete the `dist.reference`
   * set `plugin-api-version` to `2.0.0`

Then re-generate all results as shown in section above.
