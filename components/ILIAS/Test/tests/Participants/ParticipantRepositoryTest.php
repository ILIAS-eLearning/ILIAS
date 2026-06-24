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

namespace ILIAS\Test\Tests\Participants;

use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Participants\User;

class ParticipantRepositoryTest extends \ilTestBaseTestCase
{
    public function testGetUsersByActiveIdsReturnsEmptyArrayForEmptyInput(): void
    {
        $database = $this->createMock(\ilDBInterface::class);
        $database->expects($this->never())->method('queryF');

        $repository = new ParticipantRepository($database);

        $this->assertSame([], $repository->getUsersByActiveIds(1, []));
    }

    public function testGetUsersByActiveIdsIssuesSingleQueryAndKeysByActiveId(): void
    {
        $statement = $this->createMock(\ilDBStatement::class);
        $database = $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->expects($this->once())->method('in')->willReturn('ta.active_id IN (1, 2)');
        $database->expects($this->once())->method('queryF')->willReturn($statement);
        $database->expects($this->exactly(3))->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            [
                'active_id' => 1,
                'user_fi' => 10,
                'importname' => null,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'login' => 'jdoe',
                'matriculation' => 'M1',
            ],
            [
                'active_id' => 2,
                'user_fi' => ANONYMOUS_USER_ID,
                'importname' => 'Imported Name',
                'firstname' => '',
                'lastname' => '',
                'login' => '',
                'matriculation' => '',
            ],
            null
        );

        $repository = new ParticipantRepository($database);
        $users = $repository->getUsersByActiveIds(5, [1, 2]);

        $this->assertCount(2, $users);
        $this->assertArrayHasKey(1, $users);
        $this->assertArrayHasKey(2, $users);
        $this->assertInstanceOf(User::class, $users[1]);
        $this->assertSame('John', $users[1]->getFirstname());
        $this->assertSame('Imported Name', $users[2]->getImportname());
    }

    public function testGetUsersByActiveIdsReturnsSingleUser(): void
    {
        $statement = $this->createMock(\ilDBStatement::class);
        $database = $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->method('in')->willReturn('ta.active_id IN (7)');
        $database->expects($this->once())->method('queryF')->willReturn($statement);
        $database->expects($this->exactly(2))->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            [
                'active_id' => 7,
                'user_fi' => 99,
                'importname' => null,
                'firstname' => 'Jane',
                'lastname' => 'Roe',
                'login' => 'jroe',
                'matriculation' => '',
            ],
            null
        );

        $repository = new ParticipantRepository($database);
        $users = $repository->getUsersByActiveIds(3, [7]);

        $this->assertInstanceOf(User::class, $users[7]);
        $this->assertSame('Jane', $users[7]->getFirstname());
    }

    public function testGetUsersByActiveIdsReturnsEmptyResultWhenNotFound(): void
    {
        $statement = $this->createMock(\ilDBStatement::class);
        $database = $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->method('in')->willReturn('ta.active_id IN (7)');
        $database->method('queryF')->willReturn($statement);
        $database->method('fetchAssoc')->willReturn(null);

        $repository = new ParticipantRepository($database);

        $this->assertSame([], $repository->getUsersByActiveIds(3, [7]));
    }
}
