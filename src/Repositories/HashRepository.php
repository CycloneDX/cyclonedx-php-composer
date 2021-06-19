<?php

namespace CycloneDX\Repositories;

use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Component;
use DomainException;

/**
 * @psalm-type Hashes = array<HashAlgorithm::*, string>
 */
class HashRepository
{
    /**
     * Specifies the file hashes of the component.
     *
     * @psalm-var Hashes
     */
    private $hashes = [];

    /**
     * @psalm-assert HashAlgorithm::* $algorithm
     * @psalm-param HashAlgorithm::*|string $algorithm
     *
     * @throws DomainException if $algorithm is not in {@see \CycloneDX\Enums\HashAlgorithm}'s constants list
     *
     * @return $this
     *
     */
    public function setHash(string $algorithm, string $content): self
    {
        if (false === $this->isAlgorithm($algorithm)) {
            throw new DomainException("Unknown hash algorithm: {$algorithm}");
        }
        $this->hashes[$algorithm] = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetHash(string $algorithm): self
    {
        unset($this->hashes[$algorithm]);

        return $this;
    }

    public function getHash(string $algorithm): ?string
    {
        return $this->hashes[$algorithm] ?? null;
    }

    /** @salm-return Hashes */
    public function getHashes(): array
    {
        return $this->hashes;
    }


    /**
     * @psalm-assert-if-true HashAlgorithm::* $algorithm
     */
    private function isAlgorithm(string $algorithm): bool
    {
        /** @psalm-var  list<HashAlgorithm::*> */
        $algorithms = (new \ReflectionClass(HashAlgorithm::class))->getConstants();
        return \in_array($algorithm, $algorithms, true);
    }

}
