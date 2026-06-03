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

use ILIAS\KeyValueStorage\Exception\StorageNotAvailableException;
use ILIAS\KeyValueStorage\Implementation\Factory;
use ILIAS\KeyValueStorage\Storage;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\StorageProvider;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testSessionReturnsStorageFromSessionProvider(): void
    {
        $storage = new StubStorage();
        $provider = new RecordingProvider(StorageBackend::SESSION, $storage);
        $factory = new Factory([$provider]);

        self::assertSame($storage, $factory->session()->storage(new StorageNamespace('ui.state')));
        self::assertSame('ui.state', $provider->last_storage_namespace);
    }

    public function testPersistentReturnsStorageFromPersistentProvider(): void
    {
        $storage = new StubStorage();
        $provider = new RecordingProvider(StorageBackend::PERSISTENT, $storage);
        $factory = new Factory([$provider]);

        self::assertSame($storage, $factory->persistent()->storage(new StorageNamespace('export.job')));
        self::assertSame('export.job', $provider->last_storage_namespace);
    }

    public function testSelectsProviderByLifetime(): void
    {
        $session_storage = new StubStorage();
        $persistent_storage = new StubStorage();
        $factory = new Factory([
            new RecordingProvider(StorageBackend::SESSION, $session_storage),
            new RecordingProvider(StorageBackend::PERSISTENT, $persistent_storage),
        ]);

        $namespace = new StorageNamespace('export.job');

        self::assertSame($session_storage, $factory->session()->storage($namespace));
        self::assertSame($persistent_storage, $factory->persistent()->storage($namespace));
    }

    public function testUsesLastProviderWhenBackendIsRegisteredTwice(): void
    {
        $first = new StubStorage();
        $second = new StubStorage();
        $factory = new Factory([
            new RecordingProvider(StorageBackend::SESSION, $first),
            new RecordingProvider(StorageBackend::SESSION, $second),
        ]);

        self::assertSame($second, $factory->session()->storage(new StorageNamespace('ui.state')));
    }

    public function testSessionThrowsWhenBackendIsNotContributed(): void
    {
        $factory = new Factory([
            new RecordingProvider(StorageBackend::PERSISTENT, new StubStorage()),
        ]);

        $this->expectException(StorageNotAvailableException::class);
        $this->expectExceptionMessage('No storage provider is registered for backend "session".');

        $factory->session();
    }

    public function testPersistentThrowsWhenBackendIsNotContributed(): void
    {
        $factory = new Factory([
            new RecordingProvider(StorageBackend::SESSION, new StubStorage()),
        ]);

        $this->expectException(StorageNotAvailableException::class);
        $this->expectExceptionMessage('No storage provider is registered for backend "persistent".');

        $factory->persistent();
    }
}

final class StubStorage implements Storage
{
    public function has(string $key): bool
    {
        return false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function delete(string $key): void
    {
    }

    public function clear(): void
    {
    }
}

final class RecordingProvider implements StorageProvider
{
    public ?string $last_storage_namespace = null;

    public function __construct(
        private readonly StorageBackend $storage_backend,
        private readonly Storage $storage
    ) {
    }

    public function backend(): StorageBackend
    {
        return $this->storage_backend;
    }

    public function storage(StorageNamespace $namespace): Storage
    {
        $this->last_storage_namespace = $namespace->value();

        return $this->storage;
    }
}
