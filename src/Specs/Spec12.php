<?php

namespace CycloneDX\Specs;

use CycloneDX\Enums\AbstractClassification;
use CycloneDX\Enums\AbstractHashAlgorithm;

/**
 * @author jkowalleck
 */
class Spec12
{
    use SupportsTrait;

    private const VERSION = '1.2';

    private const COMPONENT_TYPES = [
        AbstractClassification::APPLICATION,
        AbstractClassification::FRAMEWORK,
        AbstractClassification::LIBRARY,
        AbstractClassification::OPERATING_SYSTEMS,
        AbstractClassification::DEVICE,
        AbstractClassification::FILE,
        AbstractClassification::CONTAINER,
        AbstractClassification::FIRMWARE,
    ];

    private const HASH_ALGORITHMS = [
        AbstractHashAlgorithm::MD5,
        AbstractHashAlgorithm::SHA_1,
        AbstractHashAlgorithm::SHA_256,
        AbstractHashAlgorithm::SHA_384,
        AbstractHashAlgorithm::SHA_512,
        AbstractHashAlgorithm::SHA3_256,
        AbstractHashAlgorithm::SHA3_512,
        AbstractHashAlgorithm::BLAKE2B_256,
        AbstractHashAlgorithm::BLAKE2B_384,
        AbstractHashAlgorithm::BLAKE2B_512,
        AbstractHashAlgorithm::BLAKE3,
    ];

    private const HASH_CONTENT_REGEX = '/^([a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/';
}
