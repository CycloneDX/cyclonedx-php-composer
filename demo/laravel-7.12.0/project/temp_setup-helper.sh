#!/bin/sh
# script to prepare a temp dir that houses the demo

set -e

THIS_DIR_PATH="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
PROJECT_ROOT_PATH="$( dirname "$( dirname "$( dirname "$THIS_DIR_PATH" )" )" )"
TEMP_DIR_PATH="$( mktemp -d --suffix='.cyclonedx-php-composer_demo' )"

COMPOSER_JSON_TEMPLATE="$THIS_DIR_PATH/composer.template.json"
COMPOSER_JSON_TEMP="$TEMP_DIR_PATH/composer.json"

COMPOSER_LOCK_TEMPLATE="$THIS_DIR_PATH/composer.template.lock"
COMPOSER_LOCK_TEMP="$TEMP_DIR_PATH/composer.lock"

cp "$COMPOSER_LOCK_TEMPLATE" "$COMPOSER_LOCK_TEMP" >&2
sed -i "s@%cyclonedx-php-composer_project_path%@$PROJECT_ROOT_PATH@g" "$COMPOSER_LOCK_TEMP" >&2

cp "$COMPOSER_JSON_TEMPLATE" "$COMPOSER_JSON_TEMP" >&2
sed -i "s@%cyclonedx-php-composer_project_path%@$PROJECT_ROOT_PATH@g" "$COMPOSER_JSON_TEMP" >&2

echo "$TEMP_DIR_PATH"
