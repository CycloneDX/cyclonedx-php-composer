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

namespace CycloneDX\Tests\Core\Validate\Validators;

use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Core\Validation\Errors\JsonValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\Validators\JsonStrictValidator;
use JsonException;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CycloneDX\Core\Validation\Validators\JsonStrictValidator
 * @covers \CycloneDX\Core\Validation\Validators\JsonValidator
 * @covers \CycloneDX\Core\Validation\AbstractValidator
 *
 * @uses   \CycloneDX\Core\Validation\Helpers\JsonSchemaRemoteRefProviderForSnapshotResources
 */
class JsonStrictValidatorTest extends TestCase
{
    public function testConstructor(): JsonStrictValidator
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator = new JsonStrictValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpec(JsonStrictValidator $validator): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator->setSpec($spec);
        self::assertSame($spec, $validator->getSpec());
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(JsonStrictValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';

        $validator->expects(self::once())->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn(null);

        $error = $validator->validateString($json);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(JsonStrictValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';
        $expectedError = $this->createStub(JsonValidationError::class);

        $validator->expects(self::once())->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn($expectedError);

        $error = $validator->validateString($json);

        self::assertSame($expectedError, $error);
    }

    public function testValidateStringThrowsWhenNotParseable(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new JsonStrictValidator($spec);
        $json = '{"dummy":';

        $this->expectException(JsonException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($json);
    }

    public function testValidateDataPasses(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new JsonStrictValidator($spec);
        $data = (object) [
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.2',
            'version' => 1,
            'components' => [
                (object) [
                    'type' => 'library',
                    'group' => 'org.acme',
                    'name' => 'web-framework',
                    'version' => '1.0.0',
                    'purl' => 'pkg:maven/org.acme/web-framework@1.0.0',
                    'licenses' => [
                        (object) ['license' => (object) ['id' => 'MIT']],
                    ],
                ],
            ],
        ];

        $error = $validator->validateData($data);

        self::assertNull($error);
    }

    /**
     * @uses \CycloneDX\Core\Validation\Errors\JsonValidationError
     * @uses \CycloneDX\Core\Validation\ValidationError
     */
    public function testValidateDataFails(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new JsonStrictValidator($spec);
        $data = (object) [
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.2',
            'version' => 1,
            'components' => [
                (object) [
                    'type' => 'library',
                    'group' => 'org.acme',
                    'name' => 'web-framework',
                    'version' => '1.0.0',
                    'purl' => 'pkg:maven/org.acme/web-framework@1.0.0',
                    'licenses' => [
                        (object) [
                            'license' => (object) [
                                'id' => 'MIT',
                                'foo' => 'bare',
                                // Error: no additional values allowed here
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $error = $validator->validateData($data);

        self::assertNotNull($error);
    }

    public function testValidateDataThrowsOnSchemaFileUnknown(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => 'unknown']);
        $validator = new JsonStrictValidator($spec);

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateData(new stdClass());
    }
}
