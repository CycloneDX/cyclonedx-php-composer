# Plugin demo

This includes a demo project, which composer-requires `symfony/symfony` in a stable version.

Purpose is to demonstrate how _cyclonedx-php-composer_ integrates, is used 
and how the generated output will look like.

## example results

the following are examples generated by _cyclonedx-php-composer_:

* [`results/bom.xml`](results/bom.xml)
* [`results/bom.json`](results/bom.json)

## setup 

For the sake of a demo, a relative path to the _cyclonedx-php-composer_ project is used,
so the current code is symlinked and taken into action.

To get the setup up and running, run from the demo directory: 

```shell
composer -dproject update
```

## usage examples

run one of these from the demo directory:

* see _cyclonedx-php-composer_ help page:  
  ```shell
  composer -dproject make-bom --help 
  ```
* make XML sbom:  
  ```shell
  composer -dproject make-bom --exclude-dev --output-file=$PWD/results/bom.xml
  ```
* make JSON sbom:
  ```shell
  composer -dproject make-bom --exclude-dev --json --output-file=$PWD/results/bom.json
  ```