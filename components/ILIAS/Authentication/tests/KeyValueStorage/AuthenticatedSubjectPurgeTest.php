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
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\SubjectPurge;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticatedSubjectPurgeTest extends TestCase
{
    public function testAPositiveUserIdPurgesTheUSegment(): void
    {
        $purge = $this->createMock(SubjectPurge::class);
        $purge->expects($this->once())
            ->method('purge')
            ->with($this->callback(
                static fn(SubjectId $subject): bool => $subject->storageSegment() === 'u42'
            ));
        $purge->expects($this->never())->method('purgeMany');

        (new AuthenticatedSubjectPurge($purge))->purgeForUserId(42);
    }

    public function testZeroDoesNotPurge(): void
    {
        $purge = $this->purgeThatIsNeverCalled();

        (new AuthenticatedSubjectPurge($purge))->purgeForUserId(0);
    }

    public function testANegativeUserIdDoesNotPurge(): void
    {
        $purge = $this->purgeThatIsNeverCalled();

        (new AuthenticatedSubjectPurge($purge))->purgeForUserId(-1);
    }

    private function purgeThatIsNeverCalled(): SubjectPurge&MockObject
    {
        $purge = $this->createMock(SubjectPurge::class);
        $purge->expects($this->never())->method($this->anything());

        return $purge;
    }
}
