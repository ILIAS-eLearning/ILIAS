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

namespace ILIAS\KeyValueStorage\Implementation;

/**
 * In-request key-value cache bucket store shared by request-scoped storage instances.
 *
 * @internal Used by {@see StorageProviderFactory}.
 */
class RequestScopeCache
{
    /** @var array<string, array<string, mixed>> */
    private array $values = [];

    public function has(string $scope_key, string $key): bool
    {
        return isset($this->values[$scope_key])
            && \array_key_exists($key, $this->values[$scope_key]);
    }

    public function get(string $scope_key, string $key): mixed
    {
        return $this->values[$scope_key][$key];
    }

    public function set(string $scope_key, string $key, mixed $value): void
    {
        $this->values[$scope_key][$key] = $value;
    }

    public function forget(string $scope_key, string $key): void
    {
        unset($this->values[$scope_key][$key]);
    }

    public function clearScope(string $scope_key): void
    {
        unset($this->values[$scope_key]);
    }
}
