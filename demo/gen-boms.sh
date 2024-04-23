#!/bin/sh
set -ex

## purpose: generate example results from the demos

THIS_DIR="$(dirname "$0")"

for manifest in "$THIS_DIR"/*/project/composer.json
do
  echo ">>> $manifest"
  project="$(dirname "$manifest")"

  composer -d "$project" create-sbom-results
done
