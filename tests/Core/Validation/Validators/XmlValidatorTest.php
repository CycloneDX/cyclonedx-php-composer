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
use CycloneDX\Core\Validation\Errors\XmlValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\Validators\XmlValidator;
use DOMDocument;
use DOMException;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \CycloneDX\Core\Validation\Validators\XmlValidator
 * @covers   \CycloneDX\Core\Validation\BaseValidator
 */
class XmlValidatorTest extends TestCase
{
    public function testConstructor(): XmlValidator
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator = new XmlValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpec(XmlValidator $validator): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $validator->setSpec($spec);
        self::assertSame($spec, $validator->getSpec());
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(XmlValidator::class, ['validateDom']);
        $xml = '<bom/>';

        $validator->expects(self::once())->method('validateDom')
            ->with(new IsInstanceOf(DOMDocument::class))
            ->willReturn(null);

        $error = $validator->validateString($xml);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(XmlValidator::class, ['validateDom']);
        $xml = '<bom/>';
        $expectedError = $this->createStub(XmlValidationError::class);

        $validator->expects(self::once())->method('validateDom')
            ->with(new IsInstanceOf(DOMDocument::class))
            ->willReturn($expectedError);

        $error = $validator->validateString($xml);

        self::assertSame($expectedError, $error);
    }

    public function testValidateStringThrowsWhenNotParseable(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new XmlValidator($spec);
        $xml = '<bom>some invalid XML';

        $this->expectException(DOMException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($xml);
    }

    public function testValidateDomPasses(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library">
                            <name>tomcat-catalina</name>
                            <version>9.0.14</version>
                            <licenses>
                                <license>
                                    <id>MIT</id>
                                </license>
                            </licenses>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);
        self::assertNull($error);
    }

    /**
     * @uses \CycloneDX\Core\Validation\Errors\XmlValidationError
     * @uses \CycloneDX\Core\Validation\ValidationError
     */
    public function testValidateDomFails(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library">
                            <name>tomcat-catalina</name>
                            <version>9.0.14</version>
                            <licenses>
                                <license>
                                    <id>MIT</id>
                                    <name>Some License</name>
                                    <!-- Errors: eiter ID or name ... -->
                                </license>
                            </licenses>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);

        self::assertNotNull($error);
        self::assertInstanceOf(XmlValidationError::class, $error);
        self::assertStringContainsString(
            "Element '{http://cyclonedx.org/schema/bom/1.2}name': This element is not expected.",
            $error->getMessage()
        );
    }

    /**
     * @uses \CycloneDX\Core\Validation\Errors\XmlValidationError
     * @uses \CycloneDX\Core\Validation\ValidationError
     */
    public function testValidateDomThrowsOnDuplicateBomRef(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library" bom-ref="BomRef-foo">
                            <name>foo1</name>
                            <version>1.2.3</version>
                        </component>
                        <component type="library" bom-ref="BomRef-foo">
                            <name>foo2</name>
                            <version>1.0.0</version>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);

        self::assertNotNull($error);
        self::assertInstanceOf(XmlValidationError::class, $error);
        self::assertStringContainsString(
            "Element '{http://cyclonedx.org/schema/bom/1.2}component': Duplicate key-sequence ['BomRef-foo'] in unique identity-constraint '{http://cyclonedx.org/schema/bom/1.2}bom-ref'.",
            $error->getMessage()
        );
    }

    public function testValidateDomThrowsOnSchemaFileUnknown(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => 'unknown']);
        $validator = new XmlValidator($spec);
        $doc = $this->createPartialMock(DOMDocument::class, ['schemaValidate']);

        $doc->expects(self::never())->method('schemaValidate');

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateDom($doc);
    }
}
