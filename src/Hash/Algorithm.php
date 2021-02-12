<?php

namespace CycloneDX\Hash;

/**
 * @author jkowalleck
 */
class Algorithm
{
    /* list of known algorithms - see `loadAlgorithms()` */
    public const MD5 = 'MD5';
    public const SHA_1 = 'SHA-1';
    public const SHA_256 = 'SHA-256';
    public const SHA_384 = 'SHA-384';
    public const SHA_512 = 'SHA-512';
    public const SHA3_256 = 'SHA3-256';
    public const SHA3_512 = 'SHA3-512';
    public const BLAKE2B_256 = 'BLAKE2b-256';
    public const BLAKE2B_384 = 'BLAKE2b-384';
    public const BLAKE2B_512 = 'BLAKE2b-512';
    public const BLAKE3 = 'BLAKE3';

    /**
     * @var array<string, string>
     */
    private $algorithms;

    /**
     * @return array<string, string>
     */
    public function getAlgorithms(): array
    {
        return $this->algorithms;
    }

    public function __construct()
    {
        $this->loadAlgorithms();
    }

    private function loadAlgorithms(): void
    {
        if (null !== $this->algorithms) {
            return;
        }

        $this->algorithms = [];
        foreach ([
            self::MD5,
            self::SHA_1,
            self::SHA_256,
            self::SHA_384,
            self::SHA_512,
            self::SHA3_256,
            self::SHA3_512,
            self::BLAKE2B_256,
            self::BLAKE2B_384,
            self::BLAKE2B_512,
            self::BLAKE3,
        ] as $algorithms) {
            $this->algorithms[strtolower($algorithms)] = $algorithms;
        }
    }

    public function validate(string $algorithm): bool
    {
        return isset($this->algorithms[strtolower($algorithm)]);
    }

    public function getAlgorithm(string $algorithm): ?string
    {
        return $this->algorithms[strtolower($algorithm)] ?? null;
    }
}
