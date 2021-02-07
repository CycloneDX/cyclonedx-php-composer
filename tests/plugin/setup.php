#!/usr/bin/env php
<?php
/**
 * @author jkowalleck
 */
error_reporting(E_ALL);
ini_set('display_errors', 'on');

define('PLUGIN_PATH_PLACEHOLDER', '"%PLUGIN_PATH%"');

$templateFile = __DIR__.'/composer.template.json';
$pluginDir = realpath(dirname(__DIR__, 2));

$targetDir = $argv[1] ?? getcwd();
if (false === is_dir($targetDir) || false === is_writable($targetDir)) {
    throw new RuntimeException("not a writable dir: {$targetDir}");
}
$targetFile = $targetDir.DIRECTORY_SEPARATOR.'composer.json';

$template = file_get_contents($templateFile);
if (false === $template) {
    throw new RuntimeException('failed to get template');
}

$written = file_put_contents(
    $targetFile,
    str_replace(
        PLUGIN_PATH_PLACEHOLDER,
        json_encode($pluginDir, JSON_THROW_ON_ERROR),
        $template
    )
);

exit(false === $written ? 1 : 0);
