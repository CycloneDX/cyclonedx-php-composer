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

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\BomRefRepository;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\DependenciesNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\DependenciesNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class DependenciesNormalizerTest extends TestCase
{
    /**
     * @var NormalizerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var DependenciesNormalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(NormalizerFactory::class);
        $this->normalizer = new DependenciesNormalizer($this->factory);
    }

    /**
     * @param string[] $expecteds
     *
     * @dataProvider dpNormalize
     *
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testNormalize(Bom $bom, array $expecteds): void
    {
        $actuals = $this->normalizer->normalize($bom);

        self::assertSameSize($expecteds, $actuals);

        $missing = [];
        foreach ($expecteds as $expected) {
            foreach ($actuals as $actual) {
                try {
                    self::assertEquals($expected, $actual);
                    continue 2; // expected was found
                } catch (\Exception $exception) {
                    // pass
                }
            }
            $missing[] = $expected;
        }

        self::assertCount(
            0,
            $missing,
            sprintf("missing:\n%s\nin:\n%s",
                print_r($missing, true),
                print_r($actuals, true),
            )
        );
    }

    public function dpNormalize(): \Generator
    {
        $componentWithoutBomRefValue = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef(null),
                'getDependenciesBomRefRepository' => null,
            ]
        );

        $componentWithoutDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithoutDeps'),
                'getDependenciesBomRefRepository' => null,
            ]
        );
        $componentWithNoDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithNoDeps'),
                'getDependenciesBomRefRepository' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    ['getBomRefs' => []]
                ),
            ]
        );
        $componentWithDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithDeps'),
                'getDependenciesBomRefRepository' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    [
                        'getBomRefs' => [
                            $componentWithoutDeps->getBomRef(),
                            $componentWithNoDeps->getBomRef(),
                        ],
                    ]
                ),
            ]
        );
        $rootComponent = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('myRootComponent'),
                'getDependenciesBomRefRepository' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    [
                        'getBomRefs' => [
                            $componentWithDeps->getBomRef(),
                            $componentWithoutDeps->getBomRef(),
                            $componentWithoutBomRefValue->getBomRef(),
                            new BomRef('SomethingOutsideOfTheActualBom'),
                        ],
                    ]
                ),
            ]
        );

        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getComponentRepository' => $this->createConfiguredMock(
                    ComponentRepository::class,
                    [
                        'getComponents' => [
                            $componentWithoutDeps,
                            $componentWithNoDeps,
                            $componentWithDeps,
                            $componentWithoutBomRefValue,
                        ],
                    ]
                ),
                'getMetaData' => $this->createConfiguredMock(
                    MetaData::class,
                    [
                        'getComponent' => $rootComponent,
                    ]
                ),
            ]
        );

        yield 'with metadata' => [
            $bom,
            [
                [
                    'ref' => 'myRootComponent',
                    'dependsOn' => [
                        'ComponentWithDeps',
                        'ComponentWithoutDeps',
                    ],
                ],
                ['ref' => 'ComponentWithoutDeps'],
                ['ref' => 'ComponentWithNoDeps'],
                [
                    'ref' => 'ComponentWithDeps',
                    'dependsOn' => [
                        'ComponentWithoutDeps',
                        'ComponentWithNoDeps',
                    ],
                ],
                // $componentWithoutBomRefValue is expected to be skipped
            ],
        ];
    }
}
