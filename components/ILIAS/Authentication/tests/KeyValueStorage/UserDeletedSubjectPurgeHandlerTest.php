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

use ILIAS\Authentication\KeyValueStorage\AuthenticatedSubjectPurge;
use ILIAS\Authentication\KeyValueStorage\UserDeletedSubjectPurgeHandler;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\SubjectPurge;
use PHPUnit\Framework\TestCase;

class UserDeletedSubjectPurgeHandlerTest extends TestCase
{
    public function testHandlePurgesTheUSegmentOfThatUser(): void
    {
        $purge = $this->createMock(SubjectPurge::class);
        $purge->expects($this->once())
            ->method('purge')
            ->with($this->callback(
                static fn(SubjectId $subject): bool => $subject->storageSegment() === 'u42'
            ));

        (new UserDeletedSubjectPurgeHandler(new AuthenticatedSubjectPurge($purge)))->handle(42);
    }
}
