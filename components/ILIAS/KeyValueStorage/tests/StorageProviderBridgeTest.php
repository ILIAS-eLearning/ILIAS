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
use ILIAS\KeyValueStorage\Implementation\StorageProviderBridge;
use ILIAS\KeyValueStorage\KeyValidator;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\StoragePort;
use ILIAS\KeyValueStorage\ValueCodec;
use PHPUnit\Framework\TestCase;

class StorageProviderBridgeTest extends TestCase
{
    private BridgeRecordingStoragePort $port;
    private StorageProviderBridge $bridge;

    protected function setUp(): void
    {
        $this->port = new BridgeRecordingStoragePort();
        $this->bridge = new StorageProviderBridge(
            StorageBackend::PERSISTENT,
            $this->port,
            new NamespacedStorageFactory(new KeyValidator(), new ValueCodec()),
            new RequestScopeCache()
        );
    }

    public function testBackendReturnsConfiguredBackend(): void
    {
        self::assertSame(StorageBackend::PERSISTENT, $this->bridge->backend());
    }

    public function testStorageMemoizesReadsWithinRequest(): void
    {
        $storage = $this->bridge->storage(new StorageNamespace('export.job'));
        $storage->set('state', 'running');

        self::assertSame('running', $storage->get('state'));
        self::assertSame('running', $storage->get('state'));
        self::assertSame(0, $this->port->read_count);
    }

    public function testStorageWritesThroughToBackend(): void
    {
        $storage = $this->bridge->storage(new StorageNamespace('export.job'));

        $storage->set('state', 'running');

        self::assertSame(['state' => '"running"'], $this->port->writes_for('export.job'));
    }

    public function testSeparateNamespacesUseSeparateRequestCaches(): void
    {
        $first = $this->bridge->storage(new StorageNamespace('export.job'));
        $second = $this->bridge->storage(new StorageNamespace('ui.table'));

        $first->set('state', 'queued');
        $second->set('state', 'sorted');

        self::assertSame('queued', $first->get('state'));
        self::assertSame('sorted', $second->get('state'));
        self::assertSame(0, $this->port->read_count);
    }
}

final class BridgeRecordingStoragePort implements StoragePort
{
    public int $read_count = 0;

    /** @var array<string, array<string, string>> */
    private array $data = [];

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return \array_key_exists($key, $this->data[$namespace->value()] ?? []);
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        ++$this->read_count;

        return $this->data[$namespace->value()][$key] ?? null;
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->data[$namespace->value()][$key] = $value;
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        unset($this->data[$namespace->value()][$key]);
    }

    public function clearNamespace(StorageNamespace $namespace): void
    {
        unset($this->data[$namespace->value()]);
    }

    /** @return array<string, string> */
    public function writes_for(string $namespace): array
    {
        return $this->data[$namespace] ?? [];
    }
}
