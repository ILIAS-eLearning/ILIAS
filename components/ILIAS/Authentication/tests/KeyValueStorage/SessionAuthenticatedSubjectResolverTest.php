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

use ILIAS\Authentication\Domain\AuthenticatedUser;
use ILIAS\Authentication\KeyValueStorage\AuthenticatedUserSubjectProvider;
use ILIAS\Authentication\KeyValueStorage\SessionAuthenticatedSubjectResolver;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;
use PHPUnit\Framework\TestCase;

class SessionAuthenticatedSubjectResolverTest extends TestCase
{
    public function testALoggedInUserBecomesTheSubjectOfItsIdAndMayPersist(): void
    {
        $resolver = new SessionAuthenticatedSubjectResolver($this->user(new Ok(42)), new AuthenticatedUserSubjectProvider());

        self::assertSame(AuthenticatedUserSubjectProvider::NAME, $resolver->subject()->id()->provider());
        self::assertSame('42', $resolver->subject()->id()->id());
        self::assertTrue($resolver->supportsPersistentStorage());
    }

    public function testAnErrorBecomesAnonymousAndMayNotPersist(): void
    {
        $resolver = new SessionAuthenticatedSubjectResolver(
            $this->user(new Error('No authenticated user in session.')),
            new AuthenticatedUserSubjectProvider()
        );

        self::assertTrue($resolver->subject()->isAnonymous());
        self::assertFalse($resolver->supportsPersistentStorage());
    }

    public function testANonPositiveIdBecomesAnonymousAndMayNotPersist(): void
    {
        $resolver = new SessionAuthenticatedSubjectResolver($this->user(new Ok(0)), new AuthenticatedUserSubjectProvider());

        self::assertTrue($resolver->subject()->isAnonymous());
        self::assertFalse($resolver->supportsPersistentStorage());
    }

    private function user(Result $id): AuthenticatedUser
    {
        $user = $this->createStub(AuthenticatedUser::class);
        $user->method('id')->willReturn($id);

        return $user;
    }
}
