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

use ILIAS\KeyValueStorage\Storage;

/**
 * Request-scoped write-through cache in front of another storage.
 *
 * @internal Applied by {@see StorageProviderFactory}; not part of the consumer API.
 *
 * Avoids repeated reads from session or database within one HTTP request. The
 * cache is updated on every write and discarded when the request ends. This is
 * not a cross-request cache — use ILIAS\Cache for that.
 */
final readonly class RequestScopedStorage implements Storage
{
    private object $cache_miss;

    public function __construct(
        private Storage $inner,
        private string $scope_key,
        private RequestScopeCache $request_scope_cache
    ) {
        $this->cache_miss = new \stdClass();
    }

    public function has(string $key): bool
    {
        if ($this->request_scope_cache->has($this->scope_key, $key)) {
            return true;
        }

        return $this->inner->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->request_scope_cache->has($this->scope_key, $key)) {
            return $this->request_scope_cache->get($this->scope_key, $key);
        }

        $value = $this->inner->get($key, $this->cache_miss);
        if ($value === $this->cache_miss) {
            return $default;
        }

        $this->request_scope_cache->set($this->scope_key, $key, $value);

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $this->inner->set($key, $value);
        $this->request_scope_cache->set($this->scope_key, $key, $value);
    }

    public function delete(string $key): void
    {
        $this->inner->delete($key);
        $this->request_scope_cache->forget($this->scope_key, $key);
    }

    public function clear(): void
    {
        $this->inner->clear();
        $this->request_scope_cache->clearScope($this->scope_key);
    }
}
