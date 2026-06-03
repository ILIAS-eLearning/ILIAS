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

namespace ILIAS\Tests\Authentication\KeyValueStorage;

use ILIAS\Authentication\KeyValueStorage\SessionStoragePort;
use ILIAS\KeyValueStorage\StorageNamespace;
use PHPUnit\Framework\TestCase;

class SessionStoragePortTest extends TestCase
{
    private SessionStoragePort $port;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->port = new SessionStoragePort();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testWriteUsesExpectedSessionKey(): void
    {
        $namespace = new StorageNamespace('ui.table');

        $this->port->write($namespace, 'sort_column', 'encoded');

        self::assertSame(
            'encoded',
            $_SESSION['__ilias_kv_storage__.ui.table.sort_column'] ?? null
        );
    }

    public function testWriteAndReadValue(): void
    {
        $namespace = new StorageNamespace('ui.table');

        $this->port->write($namespace, 'sort_column', 'encoded');

        self::assertTrue($this->port->has($namespace, 'sort_column'));
        self::assertSame('encoded', $this->port->read($namespace, 'sort_column'));
    }

    public function testReadReturnsNullForMissingKey(): void
    {
        self::assertNull(
            $this->port->read(new StorageNamespace('ui.table'), 'missing')
        );
    }

    public function testRemoveDeletesValue(): void
    {
        $namespace = new StorageNamespace('ui.table');
        $this->port->write($namespace, 'sort_column', 'encoded');

        $this->port->remove($namespace, 'sort_column');

        self::assertFalse($this->port->has($namespace, 'sort_column'));
        self::assertArrayNotHasKey('__ilias_kv_storage__.ui.table.sort_column', $_SESSION);
    }

    public function testClearNamespaceRemovesOnlyMatchingKeys(): void
    {
        $namespace = new StorageNamespace('ui.table');
        $other_namespace = new StorageNamespace('other.namespace');

        $this->port->write($namespace, 'sort_column', 'encoded');
        $this->port->write($other_namespace, 'key', 'value');

        $this->port->clearNamespace($namespace);

        self::assertFalse($this->port->has($namespace, 'sort_column'));
        self::assertTrue($this->port->has($other_namespace, 'key'));
        self::assertSame('value', $this->port->read($other_namespace, 'key'));
    }
}
