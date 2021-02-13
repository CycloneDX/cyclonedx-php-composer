<?php

namespace CycloneDX\Enums;

/**
 * See {@link https://cyclonedx.org/schema/bom/1.0 Schema} for `hashAlg`.
 * See {@link https://cyclonedx.org/schema/bom/1.1 Schema} for `hashAlg`.
 * See {@link https://cyclonedx.org/schema/bom/1.2 Schema} for `hashAlg`.
 */
abstract class AbstractHashAlgorithm
{
    public const MD5 = 'MD5';
    public const SHA_1 = 'SHA-1';
    public const SHA_256 = 'SHA-256';
    public const SHA_384 = 'SHA-384';
    public const SHA_512 = 'SHA-512';
    public const SHA3_256 = 'SHA3-256';
    public const SHA3_512 = 'SHA3-512';
    /** @since schema 1.2 */
    public const BLAKE2B_256 = 'BLAKE2b-256';
    /** @since schema 1.2 */
    public const BLAKE2B_384 = 'BLAKE2b-384';
    /** @since schema 1.2 */
    public const BLAKE2B_512 = 'BLAKE2b-512';
    /** @since schema 1.2 */
    public const BLAKE3 = 'BLAKE3';
}
