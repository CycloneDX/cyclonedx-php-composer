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

namespace CycloneDX\Tests\Core\Models\License;

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Spdx\License as LicenseValidator;
use DomainException;

/**
 * @covers \CycloneDX\Core\Models\License\DisjunctiveLicenseWithId
 * @covers \CycloneDX\Core\Models\License\AbstractDisjunctiveLicense
 */
class DisjunctiveLicenseWithIdTest extends AbstractDisjunctiveLicenseTestCase
{
    public function testConstruct(): void
    {
        $spdxLicenseValidator = $this->createMock(LicenseValidator::class);
        $spdxLicenseValidator->method('validate')->with('foo')->willReturn(true);
        $spdxLicenseValidator->method('getLicense')->with('foo')->willReturn('bar');

        $license = DisjunctiveLicenseWithId::makeValidated('foo', $spdxLicenseValidator);

        self::assertSame('bar', $license->getId());
    }

    public function testConstructThrowsWHenUnknown(): void
    {
        $spdxLicenseValidator = $this->createMock(LicenseValidator::class);
        $spdxLicenseValidator->method('validate')->with('foo')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->with('foo')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid SPDX license/i');

        DisjunctiveLicenseWithId::makeValidated('foo', $spdxLicenseValidator);
    }
}
