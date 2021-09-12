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

namespace CycloneDX\Tests\Core\Validation\Validators;

use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Core\Validation\Errors\JsonValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\Validators\JsonValidator;
use JsonException;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CycloneDX\Core\Validation\Validators\JsonValidator
 * @covers \CycloneDX\Core\Validation\BaseValidator
 *
 * @uses   \CycloneDX\Core\Validation\Helpers\JsonSchemaRemoteRefProviderForSnapshotResources
 */
class JsonValidatorTest extends TestCase
{
    public function testConstructor(): JsonValidator
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator = new JsonValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpec(JsonValidator $validator): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator->setSpec($spec);
        self::assertSame($spec, $validator->getSpec());
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(JsonValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';

        $validator->expects(self::once())->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn(null);

        $error = $validator->validateString($json);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(JsonValidator::class, ['validateData']);
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
        $validator = new JsonValidator($spec);
        $json = '{"dummy":';

        $this->expectException(JsonException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($json);
    }

    public function testValidateDataPasses(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new JsonValidator($spec);
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
                                'foo' => 'bar',
                                // additional properties are allowed in non-strict mode
                            ],
                        ],
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
        $validator = new JsonValidator($spec);
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
                                'name' => 'Some License',
                                //  Errors: eiter ID or name ...
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
        $validator = new JsonValidator($spec);

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateData(new stdClass());
    }
}
