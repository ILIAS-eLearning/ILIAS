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

use ILIAS\KeyValueStorage\Internal\DatabaseRepository;
use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\KeyValueStorage\Subject\SubjectId;
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

        $this->repository = new DatabaseRepository($this->db);
        $this->namespace = new StorageNamespace(['my_component', 'view_state']);
    }

    public function testTheConnectionIsNotTouchedWhileTheRepositoryIsBuilt(): void
    {
        $this->db->expects($this->never())->method($this->anything());
        $this->assertInstanceOf(DatabaseRepository::class, new DatabaseRepository($this->db));
    }

    public function testReadReturnsTheStoredString(): void
    {
        $statement = $this->createStub(\ilDBStatement::class);
        $this->db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('SELECT value FROM ' . DatabaseRepository::TABLE),
                    $this->stringContains('WHERE subject = %s AND namespace = %s AND keyword = %s')
                ),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['', 'my_component.view_state', 'sort']
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
                    'subject' => [\ilDBConstants::T_TEXT, ''],
                    'namespace' => [\ilDBConstants::T_TEXT, 'my_component.view_state'],
                    'keyword' => [\ilDBConstants::T_TEXT, 'sort'],
                ],
                [
                    'value' => [\ilDBConstants::T_TEXT, '"title"'],
                ]
            );

        $this->repository->write($this->namespace, 'sort', '"title"');
    }

    public function testRemoveDeletesOneRow(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                $this->stringContains('WHERE subject = %s AND namespace = %s AND keyword = %s'),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['', 'my_component.view_state', 'sort']
            );

        $this->repository->remove($this->namespace, 'sort');
    }

    public function testRemoveAllDeletesByNamespaceOnly(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('WHERE subject = %s AND namespace = %s'),
                    $this->logicalNot($this->stringContains('keyword'))
                ),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['', 'my_component.view_state']
            );

        $this->repository->removeAll($this->namespace);
    }

    public function testReadAllReturnsEveryEntryOfTheNamespaceInOneQuery(): void
    {
        $statement = $this->createStub(\ilDBStatement::class);
        $this->db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('SELECT keyword, value FROM ' . DatabaseRepository::TABLE),
                    $this->stringContains('WHERE subject = %s AND namespace = %s'),
                    $this->logicalNot($this->stringContains('keyword ='))
                ),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['', 'my_component.view_state']
            )
            ->willReturn($statement);
        $this->db->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->with($statement)
            ->willReturnOnConsecutiveCalls(
                ['keyword' => 'limit', 'value' => '10'],
                ['keyword' => 'sort', 'value' => '"title"'],
                null
            );

        $this->assertSame(
            ['limit' => '10', 'sort' => '"title"'],
            $this->repository->readAll($this->namespace)
        );
    }

    public function testReadAllReturnsAnEmptyMapWhenTheNamespaceHasNoRows(): void
    {
        $this->db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->stringContains('WHERE subject = %s AND namespace = %s'),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['', 'my_component.view_state']
            )
            ->willReturn($this->createStub(\ilDBStatement::class));
        $this->db->expects($this->once())->method('fetchAssoc')->willReturn(null);

        $this->assertSame([], $this->repository->readAll($this->namespace));
    }

    public function testASubjectRowIsAddressedByItsSegmentInTheSameTable(): void
    {
        $statement = $this->createStub(\ilDBStatement::class);
        $this->db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->stringContains('FROM ' . DatabaseRepository::TABLE),
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                ['u42', 'my_component.view_state', 'sort']
            )
            ->willReturn($statement);
        $this->db->expects($this->once())->method('fetchAssoc')->with($statement)->willReturn(['value' => '"mine"']);

        $this->assertSame(
            '"mine"',
            $this->repository->readFor(new SubjectId('u42'), $this->namespace, 'sort')
        );
    }

    public function testWriteRejectsAValueLongerThanTheColumn(): void
    {
        $this->db->expects($this->never())->method($this->anything());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Stored value must not exceed ' . DatabaseRepository::MAX_VALUE_LENGTH . ' characters, got '
            . (DatabaseRepository::MAX_VALUE_LENGTH + 1) . '.'
        );

        $this->repository->write($this->namespace, 'sort', str_repeat('a', DatabaseRepository::MAX_VALUE_LENGTH + 1));
    }

    public function testWriteRejectsAKeyLongerThanTheColumn(): void
    {
        $this->db->expects($this->never())->method($this->anything());

        $length = KeyRules::MAX_LENGTH + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'A storage key must not be longer than ' . KeyRules::MAX_LENGTH . ' characters, got ' . $length . '.'
        );

        $this->repository->write($this->namespace, str_repeat('ä', $length), '"title"');
    }

    public function testWriteRejectsANamespaceLongerThanTheColumn(): void
    {
        $namespace = new \ReflectionClass(StorageNamespace::class)->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(StorageNamespace::class, 'value');
        $property->setValue($namespace, str_repeat('ä', StorageNamespace::MAX_LENGTH + 1));

        $this->db->expects($this->never())->method($this->anything());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'A storage namespace must not be longer than ' . StorageNamespace::MAX_LENGTH . ' characters, got '
            . (StorageNamespace::MAX_LENGTH + 1) . '.'
        );

        $this->repository->write($namespace, 'sort', '"title"');
    }

    public function testWriteAcceptsAValueAtTheColumnLength(): void
    {
        $value = str_repeat('ä', DatabaseRepository::MAX_VALUE_LENGTH);
        $this->db->expects($this->once())
            ->method('replace')
            ->with(
                DatabaseRepository::TABLE,
                $this->anything(),
                ['value' => [\ilDBConstants::T_TEXT, $value]]
            );

        $this->repository->writeFor(new SubjectId('u42'), $this->namespace, 'sort', $value);
    }

    public function testRemoveSubjectDeletesOnlyThatSubject(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('DELETE FROM ' . DatabaseRepository::TABLE),
                    $this->stringContains('WHERE subject = %s'),
                    $this->logicalNot($this->stringContains('namespace'))
                ),
                [\ilDBConstants::T_TEXT],
                ['u42']
            );

        $this->repository->removeSubject(new SubjectId('u42'));
    }

    public function testRemoveSubjectsWithAnEmptyListDoesNotTouchTheConnection(): void
    {
        $this->db->expects($this->never())->method($this->anything());

        $this->repository->removeSubjects([]);
    }

    public function testRemoveSubjectsDeletesTheGivenSegmentsInOneStatement(): void
    {
        $this->db->expects($this->once())
            ->method('in')
            ->with('subject', ['u42', 'u7'], false, \ilDBConstants::T_TEXT)
            ->willReturn("subject IN ('u42','u7')");
        $this->db->expects($this->once())
            ->method('manipulate')
            ->with(
                'DELETE FROM ' . DatabaseRepository::TABLE . " WHERE subject IN ('u42','u7')"
            );
        $this->db->expects($this->never())->method('manipulateF');

        $this->repository->removeSubjects([new SubjectId('u42'), new SubjectId('u7')]);
    }

    public function testRemoveSubjectsRejectsAValueThatIsNotASubjectId(): void
    {
        $this->db->expects($this->never())->method('manipulate');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a subject id.');

        $this->repository->removeSubjects([new SubjectId('u42'), 'u7']);
    }
}
