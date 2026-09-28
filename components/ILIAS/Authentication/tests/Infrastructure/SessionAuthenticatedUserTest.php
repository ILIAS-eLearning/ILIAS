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

namespace ILIAS\Tests\Authentication\Infrastructure;

use ILIAS\Authentication\Infrastructure\SessionAuthenticatedUser;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionAuthenticatedUserTest extends TestCase
{
    private \ilAuthSession&MockObject $auth_session;

    protected function setUp(): void
    {
        $this->auth_session = $this->createMock(\ilAuthSession::class);
    }

    public function testReturnsAuthenticatedUserIdForFullyAuthenticatedUser(): void
    {
        $this->auth_session->expects($this->once())
            ->method('isFullyAuthenticated')
            ->willReturn(true);
        $this->auth_session->expects($this->once())
            ->method('getUserId')
            ->willReturn(42);
        $this->auth_session->expects($this->never())
            ->method('isAnonymouslyAuthenticated');

        $result = new SessionAuthenticatedUser($this->auth_session)->id();

        self::assertInstanceOf(Ok::class, $result);
        self::assertTrue($result->isOK());
        self::assertFalse($result->isError());
        self::assertSame(42, $result->value());
    }

    public function testReturnsErrorForAnonymousSession(): void
    {
        $this->auth_session->expects($this->once())
            ->method('isFullyAuthenticated')
            ->willReturn(false);
        $this->auth_session->expects($this->once())
            ->method('isAnonymouslyAuthenticated')
            ->willReturn(true);
        $this->auth_session->expects($this->never())
            ->method('getUserId');

        $result = new SessionAuthenticatedUser($this->auth_session)->id();

        self::assertInstanceOf(Error::class, $result);
        self::assertTrue($result->isError());
        self::assertFalse($result->isOK());
        self::assertSame(
            'Anonymous session actor is not an authenticated user.',
            $result->error()
        );
    }

    public function testReturnsErrorWhenSessionIsNotAuthenticated(): void
    {
        $this->auth_session->expects($this->once())
            ->method('isFullyAuthenticated')
            ->willReturn(false);
        $this->auth_session->expects($this->once())
            ->method('isAnonymouslyAuthenticated')
            ->willReturn(false);
        $this->auth_session->expects($this->never())
            ->method('getUserId');

        $result = new SessionAuthenticatedUser($this->auth_session)->id();

        self::assertInstanceOf(Error::class, $result);
        self::assertTrue($result->isError());
        self::assertFalse($result->isOK());
        self::assertSame('No authenticated user in session.', $result->error());
    }
}
