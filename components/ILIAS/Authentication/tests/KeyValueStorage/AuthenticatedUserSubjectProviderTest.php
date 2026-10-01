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

use ILIAS\Authentication\KeyValueStorage\AuthenticatedUserSubjectProvider;
use PHPUnit\Framework\TestCase;

class AuthenticatedUserSubjectProviderTest extends TestCase
{
    public function testTheSubjectOfAUserIsItsIdWithinThisProvider(): void
    {
        $subject = (new AuthenticatedUserSubjectProvider())->subjectFor(42);

        self::assertSame('authentication', $subject->provider());
        self::assertSame('42', $subject->id());
        self::assertSame(AuthenticatedUserSubjectProvider::class, $subject->providerClass());
    }

    public function testZeroIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be positive, got 0.');

        (new AuthenticatedUserSubjectProvider())->subjectFor(0);
    }

    public function testANegativeIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be positive, got -1.');

        (new AuthenticatedUserSubjectProvider())->subjectFor(-1);
    }
}
