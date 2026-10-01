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
use ILIAS\Authentication\KeyValueStorage\AuthenticatedUserSubjectProvider;
use ILIAS\KeyValueStorage\Services;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticatedSubjectPurgeTest extends TestCase
{
    public function testAPositiveUserIdPurgesThatUserOfTheProvider(): void
    {
        $storage = $this->createMock(Services::class);
        $storage->expects($this->once())
            ->method('purgeSubject')
            ->with($this->callback(
                static fn(SubjectId $subject): bool => $subject->provider() === AuthenticatedUserSubjectProvider::NAME
                    && $subject->id() === '42'
            ));

        (new AuthenticatedSubjectPurge($storage, new AuthenticatedUserSubjectProvider()))->purgeForUserId(42);
    }

    public function testZeroDoesNotPurge(): void
    {
        $storage = $this->storageThatIsNeverCalled();

        (new AuthenticatedSubjectPurge($storage, new AuthenticatedUserSubjectProvider()))->purgeForUserId(0);
    }

    public function testANegativeUserIdDoesNotPurge(): void
    {
        $storage = $this->storageThatIsNeverCalled();

        (new AuthenticatedSubjectPurge($storage, new AuthenticatedUserSubjectProvider()))->purgeForUserId(-1);
    }

    private function storageThatIsNeverCalled(): Services&MockObject
    {
        $storage = $this->createMock(Services::class);
        $storage->expects($this->never())->method($this->anything());

        return $storage;
    }
}
