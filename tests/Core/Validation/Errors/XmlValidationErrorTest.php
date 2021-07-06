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

namespace CycloneDX\Tests\Core\Validation\Errors;

use CycloneDX\Core\Validation\Errors\XmlValidationError;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Validation\Errors\XmlValidationError
 * @covers \CycloneDX\Core\Validation\ValidationError
 */
class XmlValidationErrorTest extends TestCase
{
    public function testFromLibXMLError(): void
    {
        $libXmlError = new \LibXMLError();
        $libXmlError->message = 'foo bar';
        $libXmlError->level = \LIBXML_ERR_ERROR;
        $libXmlError->code = 1337;
        $libXmlError->line = 23;
        $libXmlError->column = 42;

        $error = XmlValidationError::fromLibXMLError($libXmlError);

        self::assertSame('foo bar', $error->getMessage());
    }
}
