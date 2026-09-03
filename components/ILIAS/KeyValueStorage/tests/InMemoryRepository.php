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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\KeyValueStorage\Repository;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;

class InMemoryRepository implements Repository
{
    /** @var array<string, array<string, string>> */
    public array $entries = [];

    public int $reads = 0;

    public int $writes = 0;

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return isset($this->entries[$namespace->value()][$key]);
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        $this->reads++;

        return $this->entries[$namespace->value()][$key] ?? null;
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->writes++;
        $this->entries[$namespace->value()][$key] = $value;
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        unset($this->entries[$namespace->value()][$key]);
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        unset($this->entries[$namespace->value()]);
    }
}
