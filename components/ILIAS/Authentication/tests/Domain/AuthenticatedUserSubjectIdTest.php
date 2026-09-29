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

namespace ILIAS\Tests\Authentication\Domain;

use ILIAS\Authentication\Domain\AuthenticatedUserSubjectId;
use PHPUnit\Framework\TestCase;

class AuthenticatedUserSubjectIdTest extends TestCase
{
    public function testAPositiveUserIdBecomesTheUSegment(): void
    {
        self::assertSame('u42', AuthenticatedUserSubjectId::fromUserId(42)->storageSegment());
    }

    public function testZeroIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be positive, got 0.');

        AuthenticatedUserSubjectId::fromUserId(0);
    }

    public function testANegativeUserIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be positive, got -1.');

        AuthenticatedUserSubjectId::fromUserId(-1);
    }
}
