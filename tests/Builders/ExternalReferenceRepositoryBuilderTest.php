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

namespace CycloneDX\Tests\Builders;

use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use CycloneDX\Composer\Builders\ExternalReferenceRepositoryBuilder;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Repositories\HashRepository;

/**
 * @covers \CycloneDX\Composer\Builders\ExternalReferenceRepositoryBuilder
 *
 * @uses   \CycloneDX\Core\Repositories\ExternalReferenceRepository
 * @uses   \CycloneDX\Core\Models\ExternalReference
 * @uses   \CycloneDX\Core\Enums\ExternalReferenceType
 */
class ExternalReferenceRepositoryBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param ExternalReference[] $expectedRefs
     *
     * @dataProvider dpMakeFromPackageEmpty
     * @dataProvider dpMakeFromPackageSource
     * @dataProvider dpMakeFromPackageDist
     * @dataProvider dpMakeFromPackageHomepage
     * @dataProvider dpMakeFromPackageSupport
     * @dataProvider dpMakeFromPackageFunding
     */
    public function testMakeFromPackage(PackageInterface $package, array $expectedRefs): void
    {
        $builder = new ExternalReferenceRepositoryBuilder();

        $actual = $builder->makeFromPackage($package);

        self::assertSameSize($expectedRefs, $actual);
        foreach ($expectedRefs as $expectedRef) {
            self::assertContainsEquals($expectedRef, $actual->getExternalReferences());
        }
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageEmpty(): \Generator
    {
        yield 'package empty' => [
            $this->createConfiguredMock(PackageInterface::class, []),
            [/* empty*/],
        ];
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageSource(): \Generator
    {
        yield 'package sources' => [
            $this->createConfiguredMock(PackageInterface::class, [
                'getSourceUrls' => ['some-source-url'],
                'getSourceType' => 'some-source-type',
                'getSourceReference' => 'some-source-reference',
            ]),
            [
                (new ExternalReference(ExternalReferenceType::DISTRIBUTION, 'some-source-url'))
                    ->setComment(
                        "As detected by composer's `getSourceUrls()` (type=some-source-type & reference=some-source-reference)"
                    ),
            ],
        ];

        yield 'package source: empty' => [
            $this->createConfiguredMock(PackageInterface::class, [
                'getSourceUrls' => [''], // empty string
                'getSourceType' => 'some-source-type',
                'getSourceReference' => 'some-source-reference',
            ]),
            [/* empty */],
        ];
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageDist(): \Generator
    {
        yield 'package dists' => [
            $this->createConfiguredMock(PackageInterface::class, [
                'getDistUrls' => ['some-dist-url'],
                'getDistType' => 'some-dist-type',
                'getDistReference' => 'some-dist-reference',
                'getDistSha1Checksum' => '12345678901234567890123456789012',
            ]),
            [
                (new ExternalReference(ExternalReferenceType::DISTRIBUTION, 'some-dist-url'))
                    ->setComment(
                        "As detected by composer's `getDistUrls()` (type=some-dist-type & reference=some-dist-reference & sha1=12345678901234567890123456789012)"
                    )
                    ->setHashRepository(
                        new HashRepository([HashAlgorithm::SHA_1 => '12345678901234567890123456789012'])
                    ),
            ],
        ];

        yield 'package dists: no hash' => [
            $this->createConfiguredMock(PackageInterface::class, [
                'getDistUrls' => ['some-dist-url'],
                'getDistType' => 'some-dist-type',
                'getDistReference' => 'some-dist-reference',
                'getDistSha1Checksum' => '',
            ]),
            [
                (new ExternalReference(ExternalReferenceType::DISTRIBUTION, 'some-dist-url'))
                    ->setComment(
                        "As detected by composer's `getDistUrls()` (type=some-dist-type & reference=some-dist-reference & sha1=UNDEFINED)"
                    ),
            ],
        ];

        yield 'package dists: empty' => [
            $this->createConfiguredMock(PackageInterface::class, [
                'getDistUrls' => [''], //  empty  string
                'getDistType' => 'some-dist-type',
                'getDistReference' => 'some-dist-reference',
                'getDistSha1Checksum' => '12345678901234567890123456789012',
            ]),
            [/* empty */],
        ];
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageHomepage(): \Generator
    {
        yield 'package homepage' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getHomepage' => 'some-homepage',
            ]),
            [
                (new ExternalReference(ExternalReferenceType::WEBSITE, 'some-homepage'))
                    ->setComment('As set via `homepage` in composer package definition.'),
            ],
        ];

        yield 'package homepage: empty' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getHomepage' => '', // empty string
            ]),
            [/* empty */],
        ];
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageSupport(): \Generator
    {
        yield 'package support: issues' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['issues' => 'some-support-issues'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::ISSUE_TRACKER, 'some-support-issues'))
                    ->setComment('As set via `support.issues` in composer package definition.'),
            ],
        ];

        yield 'package support: chat' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['chat' => 'some-support-chat'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::CHAT, 'some-support-chat'))
                    ->setComment('As set via `support.chat` in composer package definition.'),
            ],
        ];

        yield 'package support: irc' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['irc' => 'some-support-irc'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::CHAT, 'some-support-irc'))
                    ->setComment('As set via `support.irc` in composer package definition.'),
            ],
        ];

        yield 'package support: docs' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['docs' => 'some-support-docs'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::DOCUMENTATION, 'some-support-docs'))
                    ->setComment('As set via `support.docs` in composer package definition.'),
            ],
        ];

        yield 'package support: wiki' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['wiki' => 'some-support-wiki'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::DOCUMENTATION, 'some-support-wiki'))
                    ->setComment('As set via `support.wiki` in composer package definition.'),
            ],
        ];

        yield 'package support: email empty' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['email' => '' /* empty string */],
            ]),
            [/* empty */],
        ];
        yield 'package support: email with mailto' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['email' => 'mailto:support@example.com'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::OTHER, 'mailto:support@example.com'))
                    ->setComment('As set via `support.email` in composer package definition.'),
            ],
        ];
        yield 'package support: email add mailto' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['email' => 'support@example.com'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::OTHER, 'mailto:support@example.com'))
                    ->setComment('As set via `support.email` in composer package definition.'),
            ],
        ];

        yield 'package support: unknown is general support' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['foo' => 'some-support-foo'],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::SUPPORT, 'some-support-foo'))
                    ->setComment('As set via `support.foo` in composer package definition.'),
            ],
        ];

        yield 'package support: empty url' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => ['issues' => ''],
            ]),
            [/* empty */],
        ];

        yield 'package support: empty' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getSupport' => [/* empty */],
            ]),
            [/* empty */],
        ];
    }

    /**
     * @psalm-return \Generator<string, array{0: PackageInterface, 1: list<TraversableContainsEqual>}>
     */
    public function dpMakeFromPackageFunding(): \Generator
    {
        yield 'package funding' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getFunding' => [['type' => 'some-type', 'url' => 'some-url']],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::OTHER, 'some-url'))
                    ->setComment('As set via `funding` in composer package definition. (type=some-type)'),
            ],
        ];

        yield 'package funding: missing type' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getFunding' => [['url' => 'some-funding']],
            ]),
            [
                (new ExternalReference(ExternalReferenceType::OTHER, 'some-funding'))
                    ->setComment('As set via `funding` in composer package definition. (type=UNDEFINED)'),
            ],
        ];

        yield 'package funding: missing url' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getFunding' => [['type' => 'foo']],
            ]),
            [/* empty */],
        ];

        yield 'package funding: empty url' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getFunding' => [['type' => 'foo', 'url' => '']],
            ]),
            [/* empty */],
        ];

        yield 'package funding: empty' => [
            $this->createConfiguredMock(CompletePackageInterface::class, [
                'getFunding' => [/* empty */],
            ]),
            [/* empty */],
        ];
    }
}
