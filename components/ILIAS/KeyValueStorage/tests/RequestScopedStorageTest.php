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

use ILIAS\KeyValueStorage\Implementation\RequestScopeCache;
use ILIAS\KeyValueStorage\Implementation\RequestScopedStorage;
use ILIAS\KeyValueStorage\Storage;
use PHPUnit\Framework\TestCase;

class RequestScopedStorageTest extends TestCase
{
    public function testReadsFromInnerOnlyOncePerKey(): void
    {
        $inner = new RequestScopedStorageTestStorage(['sort_column' => 'title']);
        $storage = new RequestScopedStorage($inner, 'session:reads_once', new RequestScopeCache());

        self::assertSame('title', $storage->get('sort_column', 'id'));
        self::assertSame('title', $storage->get('sort_column', 'id'));

        self::assertSame(1, $inner->read_count);
        self::assertSame(0, $inner->has_count);
    }

    public function testHasUsesCacheAfterFirstGet(): void
    {
        $inner = new RequestScopedStorageTestStorage(['sort_column' => 'title']);
        $storage = new RequestScopedStorage($inner, 'session:has_cache', new RequestScopeCache());

        $storage->get('sort_column');
        $inner->has_count = 0;

        self::assertTrue($storage->has('sort_column'));
        self::assertSame(0, $inner->has_count);
    }

    public function testSetUpdatesCacheWithoutExtraRead(): void
    {
        $inner = new RequestScopedStorageTestStorage();
        $storage = new RequestScopedStorage($inner, 'session:write_through', new RequestScopeCache());

        $storage->set('sort_column', 'title');
        $inner->read_count = 0;

        self::assertSame('title', $storage->get('sort_column'));
        self::assertSame(0, $inner->read_count);
    }

    public function testGetReturnsDefaultWithoutCachingMiss(): void
    {
        $inner = new RequestScopedStorageTestStorage();
        $storage = new RequestScopedStorage($inner, 'session:default', new RequestScopeCache());

        self::assertSame('fallback', $storage->get('missing', 'fallback'));
        self::assertSame(1, $inner->read_count);
        self::assertFalse($storage->has('missing'));
    }

    public function testDeleteRemovesCachedValue(): void
    {
        $inner = new RequestScopedStorageTestStorage();
        $storage = new RequestScopedStorage($inner, 'session:delete', new RequestScopeCache());

        $storage->set('sort_column', 'title');
        $storage->delete('sort_column');

        self::assertFalse($storage->has('sort_column'));
        self::assertSame(1, $inner->delete_count);
    }

    public function testClearDropsCachedNamespace(): void
    {
        $inner = new RequestScopedStorageTestStorage();
        $storage = new RequestScopedStorage($inner, 'session:clear', new RequestScopeCache());

        $storage->set('sort_column', 'title');
        $storage->clear();

        self::assertFalse($storage->has('sort_column'));
        self::assertSame([], $inner->values);
        self::assertSame(1, $inner->clear_count);
    }

    public function testSeparateScopesDoNotShareCache(): void
    {
        $inner = new RequestScopedStorageTestStorage(['sort_column' => 'title']);
        $request_scope_cache = new RequestScopeCache();
        $first = new RequestScopedStorage($inner, 'session:scope_a', $request_scope_cache);
        $second = new RequestScopedStorage($inner, 'session:scope_b', $request_scope_cache);

        self::assertSame('title', $first->get('sort_column'));
        $inner->read_count = 0;

        self::assertSame('title', $second->get('sort_column'));
        self::assertSame(1, $inner->read_count);
    }

    public function testCachesNullValue(): void
    {
        $inner = new RequestScopedStorageTestStorage();
        $storage = new RequestScopedStorage($inner, 'session:null', new RequestScopeCache());

        $storage->set('nullable', null);

        self::assertTrue($storage->has('nullable'));
        self::assertNull($storage->get('nullable'));
        self::assertSame(0, $inner->read_count);
    }
}

final class RequestScopedStorageTestStorage implements Storage
{
    public int $read_count = 0;

    public int $has_count = 0;

    public int $delete_count = 0;

    public int $clear_count = 0;

    /** @var array<string, mixed> */
    public array $values;

    /** @param array<string, mixed> $values */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function has(string $key): bool
    {
        ++$this->has_count;

        return \array_key_exists($key, $this->values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        ++$this->read_count;

        if (!\array_key_exists($key, $this->values)) {
            return $default;
        }

        return $this->values[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function delete(string $key): void
    {
        ++$this->delete_count;
        unset($this->values[$key]);
    }

    public function clear(): void
    {
        ++$this->clear_count;
        $this->values = [];
    }
}
