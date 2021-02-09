<?php

namespace CycloneDX\Models;

use UnexpectedValueException;

/**
 * @author jkowalleck
 */
class Hash
{
    public const ALG_MD5 = 'MD5';
    public const ALG_SHA_1 = 'SHA-1';
    public const ALG_SHA_256 = 'SHA-256';
    public const ALG_SHA_384 = 'SHA-384';
    public const ALG_SHA_512 = 'SHA-512';
    public const ALG_SHA3_256 = 'SHA3-256';
    public const ALG_SHA3_512 = 'SHA3-512';
    public const ALG_BLAKE2B_256 = 'BLAKE2b-256';
    public const ALG_BLAKE2B_384 = 'BLAKE2b-384';
    public const ALG_BLAKE2B_512 = 'BLAKE2b-512';
    public const ALG_BLAKE3 = 'BLAKE3';

    /**
     * Known types.
     *
     * See {@link https://cyclonedx.org/schema/bom/1.1 Schema} for `hashAlg`.
     *
     * @return string[]
     */
    public const ALGORITHMS = [
        self::ALG_MD5,
        self::ALG_SHA_1,
        self::ALG_SHA_256,
        self::ALG_SHA_384,
        self::ALG_SHA_512,
        self::ALG_SHA3_256,
        self::ALG_SHA3_512,
        self::ALG_BLAKE2B_256,
        self::ALG_BLAKE2B_384,
        self::ALG_BLAKE2B_512,
        self::ALG_BLAKE3,
    ];

    /**
     * Specifies the algorithm used to create the hash.
     *
     * @var string
     */
    private $alg;

    /**
     * @var string
     */
    private $value;

    public function getAlg(): string
    {
        return $this->alg;
    }

    /**
     * @throws UnexpectedValueException if value is an unknown algorithm
     *
     * @return $this
     */
    public function setAlg(string $alg): self
    {
        if (!in_array($alg, self::ALGORITHMS, true)) {
            throw new UnexpectedValueException("Unknown algorithm: {$alg}");
        }
        $this->alg = $alg;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return $this
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function __construct(string $alg, string $value)
    {
        $this->setAlg($alg);
        $this->setValue($value);
    }
}
