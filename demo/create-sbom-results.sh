#!/bin/sh
set -ex

THIS_DIR="$(dirname "$0")"

COMPOSER_BIN="${COMPOSER_BIN:-$(which composer)}"

find "$THIS_DIR" -mindepth 2 -maxdepth 2 -type d -name 'project' \
-print \
-exec "$COMPOSER_BIN" -d'{}' setup \; \
-exec "$COMPOSER_BIN" -d'{}' create-sbom-results -vv \;
