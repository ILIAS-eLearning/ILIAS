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

use ILIAS\KeyValueStorage\Internal\StorageNamespace;

/**
 * Persistence contract of one storage scope.
 *
 * Values are passed through as opaque strings; validation and encoding happen
 * above this layer.
 */
interface Repository
{
    public function has(StorageNamespace $namespace, string $key): bool;

    /**
     * @return string|null null if the key is not present
     */
    public function read(StorageNamespace $namespace, string $key): ?string;

    public function write(StorageNamespace $namespace, string $key, string $value): void;

    public function remove(StorageNamespace $namespace, string $key): void;

    public function removeAll(StorageNamespace $namespace): void;
}
