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

use ILIAS\KeyValueStorage\Implementation\NamespacedStorageFactory;
use ILIAS\KeyValueStorage\Implementation\RequestScopeCache;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use ILIAS\KeyValueStorage\Implementation\StorageProviderFactory;
use ILIAS\KeyValueStorage\KeyValidator;
use ILIAS\KeyValueStorage\PersistentStoragePort;
use ILIAS\KeyValueStorage\SessionStoragePort;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\ValueCodec;
use PHPUnit\Framework\TestCase;

class StorageProviderFactoryTest extends TestCase
{
    public function testSessionReturnsProviderWithInternalRequestCache(): void
    {
        $port = new FactoryTestRecordingPort();
        $provider = new StorageProviderFactory(
            new NamespacedStorageFactory(new KeyValidator(), new ValueCodec()),
            new RequestScopeCache()
        )->session($port);

        $storage = $provider->storage(new StorageNamespace('export.job'));
        $storage->set('state', 'running');

        self::assertSame('running', $storage->get('state'));
        self::assertSame('running', $storage->get('state'));
        self::assertSame(0, $port->read_count);
        self::assertSame(StorageBackend::SESSION, $provider->backend());
    }

    public function testPersistentReturnsProviderForPersistentBackend(): void
    {
        $port = new FactoryTestRecordingPort();
        $provider = new StorageProviderFactory(
            new NamespacedStorageFactory(new KeyValidator(), new ValueCodec()),
            new RequestScopeCache()
        )->persistent($port);

        self::assertSame(StorageBackend::PERSISTENT, $provider->backend());
    }
}

final class FactoryTestRecordingPort implements SessionStoragePort, PersistentStoragePort
{
    public int $read_count = 0;

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return false;
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        ++$this->read_count;

        return null;
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
    }

    public function clearNamespace(StorageNamespace $namespace): void
    {
    }
}
