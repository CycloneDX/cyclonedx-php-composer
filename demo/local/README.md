# Demo: local

This is a demo of how locally hosted/located requirements are treated.

## Reproducible results

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
