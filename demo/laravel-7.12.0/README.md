# Demo: laravel 7.12.0

*ATTENTION*: this demo might use known vulnerable dependencies for showcasing purposes.

The output is reproducible, due to the [shipped composer-locked](project/composer.lock) versions.  
Therefore, the demo requires a special php environment, which is caused by the composer-requirements:
* php>=7.3, <8

## Reproducible results

Generated example results are also available at
[CycloneDX/sbom-examples](https://github.com/CycloneDX/sbom-examples/)
as "laravel-7.12.0".

* [`results/bom.xml`](results/bom.xml)
* [`results/bom.json`](results/bom.json)


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
  composer -dproject make-bom --exclude-dev --output-file="$PWD/results/bom.xml"
  ```
* Make JSON sbom:
  ```shell
  composer -dproject make-bom --exclude-dev --json --output-file="$PWD/results/bom.json"
  ```

### In a temp dir

You might want to run the demo in a temporary directory.  
You might be forced to do so, if you are using composer1 instead of composer2.

To do this, run the following from this directory:

```shell
DEMO_TEMP_DIR_PATH="$( project/temp_setup-helper.sh )"
composer -d"$DEMO_TEMP_DIR_PATH" install
# run the demos like tis:
composer -d"$DEMO_TEMP_DIR_PATH" make-bom --help
```

You will see a warning, that the lockfile is outdated,
because the lockfile hash does not match, due to its creation of a template.

## Template maintenance

To be able to run the demo in a temp dir, adjustments to the path of _cyclonedx-php-composer_'s
source code path are required.  
Therefore, a `project/temp_setup-helper.sh` is utilized, that replaces these paths in `project/*.template.*` files.  
These files are copies of the non-template files with just one change: path to _cyclonedx-php-composer_'s source code
is set to `%cyclonedx-php-composer_project_path%` instead of `../../..`.

So if one of the original files changes, simply copy them as a template and adjust the paths.
