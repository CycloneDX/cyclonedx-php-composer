<?php

namespace CycloneDX\Specs;

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;

/**
 * @author jkowalleck
 */
class Spec11 implements SpecInterface
{
    use SupportsTrait;

    public function getVersion(): string
    {
        return '1.1';
    }

    // region SupportsTrait

    public const COMPONENT_TYPES = [
        Classification::APPLICATION,
        Classification::FRAMEWORK,
        Classification::LIBRARY,
        Classification::OPERATING_SYSTEMS,
        Classification::DEVICE,
        Classification::FILE,
    ];

    public const HASH_ALGORITHMS = [
        HashAlgorithm::MD5,
        HashAlgorithm::SHA_1,
        HashAlgorithm::SHA_256,
        HashAlgorithm::SHA_384,
        HashAlgorithm::SHA_512,
        HashAlgorithm::SHA3_256,
        HashAlgorithm::SHA3_512,
    ];

    public const HASH_CONTENT_REGEX = '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/';

    // endregion SupportsTrait
}
