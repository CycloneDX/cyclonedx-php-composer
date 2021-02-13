<?php

namespace CycloneDX\Specs;

use CycloneDX\Enums\AbstractClassification;
use CycloneDX\Enums\AbstractHashAlgorithm;

/**
 * @author jkowalleck
 */
class Spec10
{
    use SupportsTrait;

    private const VERSION = '1.0';

    private const COMPONENT_TYPES = [
        AbstractClassification::APPLICATION,
        AbstractClassification::FRAMEWORK,
        AbstractClassification::LIBRARY,
        AbstractClassification::OPERATING_SYSTEMS,
        AbstractClassification::DEVICE,
    ];

    private const HASH_ALGORITHMS = [
        AbstractHashAlgorithm::MD5,
        AbstractHashAlgorithm::SHA_1,
        AbstractHashAlgorithm::SHA_256,
        AbstractHashAlgorithm::SHA_384,
        AbstractHashAlgorithm::SHA_512,
        AbstractHashAlgorithm::SHA3_256,
        AbstractHashAlgorithm::SHA3_512,
    ];

    private const HASH_CONTENT_REGEX = '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/';
}
