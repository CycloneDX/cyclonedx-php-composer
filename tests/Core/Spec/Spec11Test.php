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

namespace CycloneDX\Tests\Core\Spec;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Spec11;
use CycloneDX\Core\Spec\SpecInterface;

/**
 * @covers \CycloneDX\Core\Spec\Spec11
 */
class Spec11Test extends AbstractSpecTestCase
{
    protected function getSpec(): SpecInterface
    {
        return new Spec11();
    }

    protected function getSpecVersion(): string
    {
        return '1.1';
    }

    protected function shouldSupportFormats(): array
    {
        return [Format::XML];
    }

    public function shouldSupportLicenseExpression(): bool
    {
        return true;
    }
}
