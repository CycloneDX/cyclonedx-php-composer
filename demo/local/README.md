# Demo: local

This is a demo of how locally hosted/located requirements are treated.

## Reproducible results

* [`results/bom.1.1.xml`](results/bom.1.1.xml)
* [`results/bom.1.2.xml`](results/bom.1.2.xml)
* [`results/bom.1.3.xml`](results/bom.1.3.xml)
* [`results/bom.1.4.xml`](results/bom.1.4.xml)
* [`results/bom.1.2.json`](results/bom.1.2.json)
* [`results/bom.1.3.json`](results/bom.1.3.json)
* [`results/bom.1.4.json`](results/bom.1.4.json)

## Setup

For the sake of a demo, a relative path to the *cyclonedx-php-composer* project is used,
so the current code is symlinked and taken into action.

To get the setup up and running, run from this demo directory:

```shell
composer -dproject setup
```

## Usage examples

Run one of these from the demo directory:

* See *cyclonedx-php-composer* help page:

  ```shell
  composer -dproject make-bom --help 
  ```

* Make XML sbom via composer script:

  ```shell
  composer -dproject create-sbom-results:XML
  ```

* Make JSON sbom via composer script:

  ```shell
  composer -dproject create-sbom-results:JSON
  ```
