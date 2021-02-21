<?php

declare(strict_types=1);

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

namespace CycloneDX\Serialize;

use CycloneDX\Models\PackageUrl as PackageUrlModel;
use DomainException;

/**
 * according to {@link https://github.com/package-url/purl-spec/blob/master/README.rst#purl}
 * it's the following format: "scheme:type/namespace/name@version?qualifiers#subpath".
 *
 * > The purl components are mapped to these URL components:
 * > - purl scheme: this is a URL scheme with a constant value: pkg
 * > - purl type, namespace, name and version components: these are collectively mapped to a URL path
 * > - purl qualifiers: this maps to a URL query
 * > - purl subpath: this is a URL fragment
 * > - In a purl there is no support for a URL Authority (e.g. NO username, password, host and port components).
 *
 *
 * implementation is not yet completely conform to
 * {@link https://github.com/package-url/purl-spec/blob/master/README.rst#a-purl-is-a-url}
 *
 * @author jkowalleck
 */
class PackageUrl
{
    public const SCHEME = 'pkg';

    /**
     * implementation is not yet completely conform to
     * {@link https://github.com/package-url/purl-spec/blob/master/README.rst#a-purl-is-a-url}.
     */
    public function serialize(PackageUrlModel $data): string
    {
        $type = $data->getType();
        $namespace = $data->getNamespace();
        $name = $data->getName();
        $version = $data->getVersion();
        $qualifiers = $data->getQualifiers();
        $subpath = $data->getSubpath();

        return self::SCHEME.
            ':'.$type.
            ($namespace ? '/'.$namespace : '').
            '/'.$name.
            ($version ? '@'.$version : '').
            ($qualifiers ? '?'.$qualifiers : '').
            ($subpath ? '#'.$subpath : '')
            ;
    }

    /**
     * @throws DomainException if the data is invalid according to the specification
     */
    public function deserialize(string $data): ?PackageUrlModel
    {
        if ('' === $data) {
            return null;
        }

        $parts = parse_url($data);

        $scheme = ($parts['scheme'] ?? '<MISSING>');
        if (self::SCHEME !== $scheme) {
            throw new DomainException("invalid schema: {$scheme}");
        }

        if (false === isset($parts['path'])) {
            throw new DomainException('missing path');
        }
        /** @psalm-var array{string, string|null} $partsPath */
        $partsPath = explode('@', $parts['path'], 2);
        [$typeNamespaceName, $version] = 2 === count($partsPath) ? $partsPath : [$partsPath[0], null];

        $partsTypeNamespaceName = explode('/', $typeNamespaceName, 3);
        switch (count($partsTypeNamespaceName)) {
            case 2:
                [$type, $namespace, $name] = [$partsTypeNamespaceName[0], null, $partsTypeNamespaceName[1]];
                break;
            case 3:
                [$type, $namespace, $name] = $partsTypeNamespaceName;
                break;
            default:
                throw new DomainException('missing type or name');
        }

        return (new PackageUrlModel($type, $name))
            ->setNamespace($namespace)
            ->setVersion($version)
            ->setQualifiers($parts['query'] ?? null)
            ->setSubpath($parts['fragment'] ?? null)
        ;
    }
}
