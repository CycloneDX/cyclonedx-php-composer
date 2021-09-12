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

namespace CycloneDX\Tests\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\BomRefRepository;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\BaseSerializer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\BaseSerializer
 *
 * @uses   \CycloneDX\Core\Serialize\BomRefDiscriminator
 */
class BaseSerializerTest extends TestCase
{
    /**
     * @var BaseSerializer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @var SpecInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $spec;

    protected function setUp(): void
    {
        $this->spec = $this->createMock(SpecInterface::class);
        $this->serializer = $this->getMockForAbstractClass(BaseSerializer::class, [$this->spec]);
    }

    public function testSetSpec(): void
    {
        $spec = $this->createMock(SpecInterface::class);
        self::assertNotSame($spec, $this->serializer->getSpec());

        $this->serializer->setSpec($spec);

        self::assertSame($spec, $this->serializer->getSpec());
    }

    /**
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testSerializeCallsNormalize(): void
    {
        $bom = $this->createStub(Bom::class);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn('foobar');

        $actual = $this->serializer->serialize($bom);

        self::assertSame('foobar', $actual);
    }

    /**
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testSerializeForwardsExceptionsFromNormalize(): void
    {
        $bom = $this->createStub(Bom::class);
        $exception = $this->createMock(\Exception::class);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->serializer->serialize($bom);
    }

    /**
     * @param BomRef[] $allBomRefs
     *
     * @dataProvider dpBomWithRefs
     *
     * @covers       \CycloneDX\Core\Serialize\BomRefDiscriminator
     *
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testSerializeUsesUniqueBomRefsAndResetThemAfterwards(Bom $bom, array $allBomRefs): void
    {
        $allBomRefsValuesOriginal = [];
        foreach ($allBomRefs as $bomRef) {
            $allBomRefsValuesOriginal[] = [$bomRef, $bomRef->getValue()];
        }

        $allBomRefsValuesOnNormalize = [];

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturnCallback(
                function () use ($allBomRefsValuesOriginal, &$allBomRefsValuesOnNormalize) {
                    /**
                     * @var BomRef $bomRef
                     */
                    foreach ($allBomRefsValuesOriginal as [$bomRef]) {
                        $allBomRefsValuesOnNormalize[] = [$bomRef, $bomRef->getValue()];
                    }

                    return 'foobar';
                }
            );

        $actual = $this->serializer->serialize($bom);

        foreach ($allBomRefsValuesOriginal as [$bomRef, $bomRefValueOriginal]) {
            self::assertSame($bomRefValueOriginal, $bomRef->getValue());
        }

        $valuesOnNormalize = array_column($allBomRefsValuesOnNormalize, 1);
        self::assertSameSize(
            $valuesOnNormalize,
            array_unique($valuesOnNormalize, \SORT_STRING),
            'some values were found not unique in:'.\PHP_EOL.
            print_r($valuesOnNormalize, true)
        );

        self::assertSame('foobar', $actual);
    }

    public function dpBomWithRefs(): \Generator
    {
        foreach (['null' => null, 'common string' => 'foo'] as $name => $bomRefValue) {
            $componentNullDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependenciesBomRefRepository' => null,
                ]
            );
            $componentEmptyDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependenciesBomRefRepository' => $this->createMock(BomRefRepository::class),
                ]
            );
            $componentKnownDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependenciesBomRefRepository' => $this->createConfiguredMock(
                        BomRefRepository::class,
                        [
                            'getBomRefs' => [$componentNullDeps->getBomRef()],
                        ]
                    ),
                ]
            );
            $componentRoot = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependenciesBomRefRepository' => $this->createConfiguredMock(
                        BomRefRepository::class,
                        [
                            'getBomRefs' => [
                                $componentKnownDeps->getBomRef(),
                                $componentEmptyDeps->getBomRef(),
                            ],
                        ]
                    ),
                ]
            );

            yield "bom with components and meta: bomRef=$name" => [
                $this->createConfiguredMock(
                    Bom::class,
                    [
                        'getComponentRepository' => $this->createConfiguredMock(
                            ComponentRepository::class,
                            [
                                'getComponents' => [
                                    $componentNullDeps,
                                    $componentEmptyDeps,
                                    $componentKnownDeps,
                                ],
                            ]
                        ),
                        'getMetaData' => $this->createConfiguredMock(
                            MetaData::class,
                            [
                                'getComponent' => $componentRoot,
                            ]
                        ),
                    ]
                ),
                [
                    $componentRoot->getBomRef(),
                    $componentNullDeps->getBomRef(),
                    $componentEmptyDeps->getBomRef(),
                    $componentKnownDeps->getBomRef(),
                ],
            ];
        }
    }
}
