<?php

namespace CycloneDX\Enums;

/**
 * See {@link https://cyclonedx.org/schema/bom/1.0 Schema} for `classification`.
 * See {@link https://cyclonedx.org/schema/bom/1.1 Schema} for `classification`.
 * See {@link https://cyclonedx.org/schema/bom/1.2 Schema} for `classification`.
 */
abstract class AbstractClassification
{
    public const APPLICATION = 'application';
    public const FRAMEWORK = 'framework';
    public const LIBRARY = 'library';
    public const OPERATING_SYSTEMS = 'operating-system';
    public const DEVICE = 'device';
    /** @since schema 1.1 */
    public const FILE = 'file';
    /** @since schema 1.2 */
    public const CONTAINER = 'container';
    /** @since schema 1.2 */
    public const FIRMWARE = 'firmware';
}
