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

### In place

_Requires composer2_

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
