# Demo: local

This is a demo of how locally hosted/located requirements are treated.

## Reproducible results

* [`results/bom.1.1.xml`](results/bom.1.1.xml)
* [`results/bom.1.2.xml`](results/bom.1.2.xml)
* [`results/bom.1.3.xml`](results/bom.1.3.xml)
* [`results/bom.1.4.xml`](results/bom.1.4.xml)
* [`results/bom.1.5.xml`](results/bom.1.5.xml)
* [`results/bom.1.6.xml`](results/bom.1.6.xml)
* [`results/bom.1.7.xml`](results/bom.1.7.xml)
* [`results/bom.1.2.json`](results/bom.1.2.json)
* [`results/bom.1.3.json`](results/bom.1.3.json)
* [`results/bom.1.4.json`](results/bom.1.4.json)
* [`results/bom.1.5.json`](results/bom.1.5.json)
* [`results/bom.1.6.json`](results/bom.1.6.json)
* [`results/bom.1.7.json`](results/bom.1.7.json)

## Setup

For the sake of a demo, a relative path to the *cyclonedx-php-composer* project is used,
so the current code is symlinked and taken into action.

To get the setup up and running, run from this demo directory:

```shell
composer -d project setup
```

## Usage examples

Run one of these from the demo directory:

* See *cyclonedx-php-composer* help page:

  ```shell
  composer -d project CycloneDX:make-sbom --help 
  ```

* Make XML sbom via composer script:

  ```shell
  composer -d project create-sbom-results:XML
  ```

* Make JSON sbom via composer script:

  ```shell
  composer -d project create-sbom-results:JSON
  ```
