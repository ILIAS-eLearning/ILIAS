<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\KeyValueStorage;

/**
 * Low-level persistence contract for one backend implementation.
 *
 * Values are transported as opaque strings; encoding is handled above this layer.
 */
interface StoragePort
{
    public function has(StorageNamespace $namespace, string $key): bool;

    public function read(StorageNamespace $namespace, string $key): ?string;

    public function write(StorageNamespace $namespace, string $key, string $value): void;

    public function remove(StorageNamespace $namespace, string $key): void;

    public function clearNamespace(StorageNamespace $namespace): void;
}
