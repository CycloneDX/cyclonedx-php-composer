<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\LicenseExpressionNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class ComponentNormalizerTest extends TestCase
{
    public function testNormalizeThrowsOnUnsupportedType(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

    public function testNormalizeMinimal(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getGroup' => null,
                'getDescription' => null,
                'getLicense' => null,
                'getHashRepository' => null,
                'getPackageUrl' => null,
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);

        $got = $normalizer->normalize($component);

        self::assertSame([
            'type' => 'FakeType',
            'name' => 'foo',
            'version' => 'some-version',
        ], $got);
    }

    public function testNormalizeFull(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getGroup' => 'myGroup',
                'getDescription' => 'my description',
                'getLicense' => $this->createStub(LicenseExpression::class),
                'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 1]),
                'getPackageUrl' => $this->createConfiguredMock(PackageUrl::class, ['toString' => 'FakePURL', '__toString' => 'FakePURL']),
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsLicenseExpression' => true,
        ]);
        $licenseExpressionNormalizer = $this->createMock(LicenseExpressionNormalizer::class);
        $hashRepositoryNormalizer = $this->createMock(HashRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseExpression' => $licenseExpressionNormalizer,
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseExpressionNormalizer->expects(self::once())->method('normalize')
            ->with($component->getLicense())
            ->willReturn(['FakeLicense']);
        $hashRepositoryNormalizer->expects(self::once())->method('normalize')
            ->with($component->getHashRepository())
            ->willReturn(['FakeHashes']);

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
            'name' => 'myName',
            'version' => 'some-version',
            'group' => 'myGroup',
            'description' => 'my description',
            'hashes' => ['FakeHashes'],
            'licenses' => [['FakeLicense']],
            'purl' => 'FakePURL',
        ], $got);
    }

    /**
     * @uses \CycloneDX\Core\Models\License\DisjunctiveLicenseWithName
     * @uses \CycloneDX\Core\Repositories\DisjunctiveLicenseRepository
     */
    public function testNormalizeUnsupportedLicenseExpression(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getLicense' => $this->createConfiguredMock(LicenseExpression::class, ['getExpression' => 'myLicense']),
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class,
        [
            'supportsLicenseExpression' => false,
        ]);
        $licenseNormalizer = $this->createMock(DisjunctiveLicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForDisjunctiveLicenseRepository' => $licenseNormalizer,
        ]);
        $normalizer = new ComponentNormalizer($factory);

        $transformedLicenseTest = function (DisjunctiveLicenseRepository $licenses): bool {
            $licenses = $licenses->getLicenses();
            self::assertCount(1, $licenses);
            self::assertSame('myLicense', $licenses[0]->getName());

            return true;
        };

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseNormalizer->expects(self::once())->method('normalize')
            ->with($this->callback($transformedLicenseTest))
            ->willReturn(['FakeLicense']);

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
            'name' => 'myName',
            'version' => 'some-version',
            'licenses' => ['FakeLicense'],
        ], $got);
    }

    public function testNormalizeDisjunctiveLicenses(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getLicense' => $this->createConfiguredMock(DisjunctiveLicenseRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $licenseNormalizer = $this->createMock(DisjunctiveLicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForDisjunctiveLicenseRepository' => $licenseNormalizer,
        ]);
        $normalizer = new ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseNormalizer->expects(self::once())->method('normalize')
            ->with($component->getLicense())
            ->willReturn(['FakeLicenses']);

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
            'name' => 'myName',
            'version' => 'some-version',
            'licenses' => ['FakeLicenses'],
        ], $got);
    }

    public function testNormalizeDisjunctiveLicensesEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getLicense' => $this->createConfiguredMock(DisjunctiveLicenseRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $licenseNormalizer = $this->createMock(DisjunctiveLicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForDisjunctiveLicenseRepository' => $licenseNormalizer,
        ]);
        $normalizer = new ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseNormalizer->expects(self::never())->method('normalize');

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
            'name' => 'myName',
            'version' => 'some-version',
        ], $got);
    }
}
