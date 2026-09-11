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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\Database\Connection;
use ILIAS\KeyValueStorage\Internal\DatabaseRepository;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseRepositoryTest extends TestCase
{
    private \ilDBInterface&MockObject $db;

    private DatabaseRepository $repository;

    private StorageNamespace $namespace;

    protected function setUp(): void
    {
        $this->db = $this->createMock(\ilDBInterface::class);

        $connection = $this->createStub(Connection::class);
        $connection->method('get')->willReturn($this->db);

        $this->repository = new DatabaseRepository($connection);
        $this->namespace = new StorageNamespace(['my_component', 'view_state']);
    }

    public function testTheConnectionIsNotTouchedWhileTheRepositoryIsBuilt(): void
    {
        $this->db->expects($this->never())->method($this->anything());

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->never())->method('get');

        $this->assertInstanceOf(DatabaseRepository::class, new DatabaseRepository($connection));
    }

    public function testReadReturnsTheStoredString(): void
    {
        $statement = $this->createStub(\ilDBStatement::class);
        $this->db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->stringContains('SELECT value FROM ' . DatabaseRepository::TABLE),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['my_component.view_state', 'sort']
            )
            ->willReturn($statement);
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->with($statement)
            ->willReturn(['value' => '"title"']);

        $this->assertSame('"title"', $this->repository->read($this->namespace, 'sort'));
    }

    public function testReadReturnsNullForAnAbsentRow(): void
    {
        $this->db->expects($this->once())
            ->method('queryF')
            ->willReturn($this->createStub(\ilDBStatement::class));
        $this->db->expects($this->once())->method('fetchAssoc')->willReturn(null);

        $this->assertNull($this->repository->read($this->namespace, 'sort'));
    }

    public function testHasAnswersFromTheSameQueryAsRead(): void
    {
        $this->db->expects($this->once())
            ->method('queryF')
            ->willReturn($this->createStub(\ilDBStatement::class));
        $this->db->expects($this->once())->method('fetchAssoc')->willReturn(['value' => '1']);

        $this->assertTrue($this->repository->has($this->namespace, 'sort'));
    }

    public function testWriteUpsertsOnTheCompositeKey(): void
    {
        $this->db->expects($this->once())
            ->method('replace')
            ->with(
                DatabaseRepository::TABLE,
                [
                    'namespace' => [\ilDBConstants::T_TEXT, 'my_component.view_state'],
                    'keyword' => [\ilDBConstants::T_TEXT, 'sort'],
                ],
                [
                    'value' => [\ilDBConstants::T_CLOB, '"title"'],
                ]
            );

        $this->repository->write($this->namespace, 'sort', '"title"');
    }

    public function testRemoveDeletesOneRow(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                $this->stringContains('WHERE namespace = %s AND keyword = %s'),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['my_component.view_state', 'sort']
            );

        $this->repository->remove($this->namespace, 'sort');
    }

    public function testRemoveAllDeletesByNamespaceOnly(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('WHERE namespace = %s'),
                    $this->logicalNot($this->stringContains('keyword'))
                ),
                [\ilDBConstants::T_TEXT],
                ['my_component.view_state']
            );

        $this->repository->removeAll($this->namespace);
    }
}
