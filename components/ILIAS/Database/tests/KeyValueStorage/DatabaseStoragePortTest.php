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

namespace ILIAS\Tests\Database\KeyValueStorage;

use ILIAS\Database\KeyValueStorage\DatabaseConnection;
use ILIAS\Database\KeyValueStorage\DatabaseStoragePort;
use ILIAS\KeyValueStorage\StorageNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseStoragePortTest extends TestCase
{
    public function testConstructDoesNotResolveDatabaseConnection(): void
    {
        $database_connection = $this->createMock(DatabaseConnection::class);
        $database_connection->expects(self::never())
            ->method('get');

        new DatabaseStoragePort($database_connection);
    }

    public function testWriteResolvesDatabaseConnectionOnce(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('replace')
            ->with(
                'il_kv_storage',
                [
                    'namespace' => [\ilDBConstants::T_TEXT, 'ui.table'],
                    'keyword' => [\ilDBConstants::T_TEXT, 'sort_column'],
                ],
                [
                    'value' => [\ilDBConstants::T_CLOB, 'encoded'],
                ]
            );

        $database_connection = $this->createMock(DatabaseConnection::class);
        $database_connection->expects(self::once())
            ->method('get')
            ->willReturn($db);

        $port = new DatabaseStoragePort($database_connection);
        $port->write(new StorageNamespace('ui.table'), 'sort_column', 'encoded');
    }

    public function testReadReturnsStoredValue(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('query')
            ->with(
                "SELECT value FROM il_kv_storage WHERE namespace = 'ui.table' AND keyword = 'sort_column'"
            )
            ->willReturn($this->createStub(\ilDBStatement::class));
        $db->expects(self::once())
            ->method('fetchAssoc')
            ->willReturn(['value' => 'encoded']);
        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);

        self::assertSame('encoded', $port->read(new StorageNamespace('ui.table'), 'sort_column'));
    }

    public function testReadReturnsNullWhenRowMissing(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('query')
            ->willReturn($this->createStub(\ilDBStatement::class));
        $db->expects(self::once())
            ->method('fetchAssoc')
            ->willReturn(null);

        $port = $this->createPort($db);

        self::assertNull($port->read(new StorageNamespace('ui.table'), 'missing'));
    }

    public function testHasUsesExistsQuery(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('query')
            ->with(
                "SELECT EXISTS(SELECT 1 FROM il_kv_storage WHERE namespace = 'ui.table' "
                . "AND keyword = 'sort_column') AS row_exists"
            )
            ->willReturn($this->createStub(\ilDBStatement::class));
        $db->expects(self::once())
            ->method('fetchAssoc')
            ->willReturn(['row_exists' => 1]);
        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);

        self::assertTrue($port->has(new StorageNamespace('ui.table'), 'sort_column'));
    }

    public function testHasReturnsFalseWhenExistsIsZero(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('query')
            ->willReturn($this->createStub(\ilDBStatement::class));
        $db->expects(self::once())
            ->method('fetchAssoc')
            ->willReturn(['row_exists' => 0]);
        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);

        self::assertFalse($port->has(new StorageNamespace('ui.table'), 'missing'));
    }

    public function testHasReturnsFalseWhenResultRowMissing(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('query')
            ->willReturn($this->createStub(\ilDBStatement::class));
        $db->expects(self::once())
            ->method('fetchAssoc')
            ->willReturn(null);
        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);

        self::assertFalse($port->has(new StorageNamespace('ui.table'), 'missing'));
    }

    public function testRemoveDeletesByNamespaceAndKey(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('manipulate')
            ->with(
                "DELETE FROM il_kv_storage WHERE namespace = 'ui.table' AND keyword = 'sort_column'"
            );
        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);
        $port->remove(new StorageNamespace('ui.table'), 'sort_column');
    }

    public function testClearNamespaceDeletesByNamespace(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects(self::once())
            ->method('manipulate')
            ->with(
                'DELETE FROM il_kv_storage WHERE namespace = ' .
                "'ui.table'"
            );

        $db->method('quote')->willReturnCallback(static fn(string $value): string => "'" . $value . "'");

        $port = $this->createPort($db);
        $port->clearNamespace(new StorageNamespace('ui.table'));
    }

    private function createPort(MockObject&\ilDBInterface $db): DatabaseStoragePort
    {
        $database_connection = $this->createMock(DatabaseConnection::class);
        $database_connection->expects(self::once())
            ->method('get')
            ->willReturn($db);

        return new DatabaseStoragePort($database_connection);
    }
}
