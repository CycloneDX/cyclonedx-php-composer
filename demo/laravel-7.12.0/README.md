# Demo: laravel 7.12.0

*ATTENTION*: this demo might use known vulnerable dependencies for showcasing purposes.

The output is *reproducible*, due to the [shipped composer-locked](project/composer.lock) versions.  
Therefore, the demo requires a special php environment, which is caused by the composer-requirements:

Targets `php>=7.3` -- but this is enforced by composer's platform-override anyway.

## Reproducible results

Generated example results are also available at
[CycloneDX/sbom-examples](https://github.com/CycloneDX/sbom-examples/)
as "laravel-7.12.0".

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
composer -d project setup
```

## Usage examples

Run one of these from the demo directory:

* See *cyclonedx-php-composer* help page:

  ```shell
  composer -d project CycloneDX:make-sbom--help 
  ```

* Make XML sbom via composer script:

  ```shell
  composer -d project create-sbom-results:XML
  ```

* Make JSON sbom via composer script:

  ```shell
  composer -d project create-sbom-results:JSON
  ```
