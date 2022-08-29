<?php

declare(strict_types=1);

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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilChatroomUserTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomUserTest extends ilChatroomAbstractTest
{
    /** @var ilObjUser&MockObject */
    protected ilObjUser $ilUserMock;
    protected ilChatroomUser $user;

    public function testGetUserIdIfNotAnonymous(): void
    {
        $userId = 6;

        $this->ilUserMock->expects($this->once())->method('getId')->willReturn($userId);
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->willReturn(false);

        $this->assertSame($userId, $this->user->getUserId());
    }

    public function testGetUserIdFromSessionIfAnonymous(): void
    {
        $userId = 6;
        $roomId = 99;

        $this->ilUserMock->expects($this->once())->method('getId')->willReturn($userId);
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->willReturn(true);

        $this->ilChatroomMock->method('getRoomId')->willReturn($roomId);

        $session = [
            $roomId => [
                'user_id' => $userId,
            ],
        ];
        ilSession::set('chat', $session);

        $this->assertSame($userId, $this->user->getUserId());
    }

    public function testGetUserIdRandomGeneratedIfAnonymous(): void
    {
        $this->ilUserMock->expects($this->once())->method('getId')->willReturn(0);
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->willReturn(true);

        $this->ilChatroomMock->method('getRoomId')->willReturn(99);

        $this->assertNotNull($this->user->getUserId());
    }

    /**
     * @dataProvider usernameDataProvider
     * @param string $username
     * @param string $expected
     */
    public function testSetUsername(string $username, string $expected): void
    {
        $this->user->setUsername($username);
        $this->assertSame($expected, $this->user->getUsername());
    }

    public function testGetUsernameFromSession(): void
    {
        $username = 'username';
        $roomId = 99;

        ilSession::set('chat', [
            $roomId => [
                'username' => $username,
            ],
        ]);

        $this->ilChatroomMock->method('getRoomId')->willReturn(99);

        $this->assertSame($username, $this->user->getUsername());
    }

    /**
     * @todo if required session value is not set, there will be a warning.
     *       Need to check if required value isset.
     */
    public function testGetUsernameFromIlObjUser(): void
    {
        $username = 'login';
        $roomId = 99;
        ilSession::set('chat', [
            $roomId => [
                'username' => '',
            ],
        ]);

        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn($username);
        $this->ilChatroomMock->method('getRoomId')->willReturn($roomId);

        $this->assertSame($username, $this->user->getUsername());
    }

    public function testBuildAnonymousName(): void
    {
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $firstName = $this->user->buildAnonymousName();
        $secondName = $this->user->buildAnonymousName();

        $this->assertNotEquals($firstName, $secondName);
    }

    public function testBuildLogin(): void
    {
        $username = 'username';
        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn($username);

        $this->assertSame($username, $this->user->buildLogin());
    }

    public function testBuildFullname(): void
    {
        $fullname = 'John Doe';
        $this->ilUserMock->expects($this->once())->method('getPublicName')->willReturn($fullname);

        $this->assertSame($fullname, $this->user->buildFullname());
    }

    public function testBuildShortname(): void
    {
        $firstname = 'John';
        $lastname = 'Doe';
        $this->ilUserMock->expects($this->once())->method('getFirstname')->willReturn($firstname);
        $this->ilUserMock->expects($this->once())->method('getLastname')->willReturn($lastname);

        $this->assertSame('J. Doe', $this->user->buildShortname());
    }

    public function testGetChatNameSuggestionsIfAnonymous(): void
    {
        $this->ilUserMock->method('isAnonymous')->willReturn(true);
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $first = $this->user->getChatNameSuggestions();
        $second = $this->user->getChatNameSuggestions();

        $this->assertNotEquals($first, $second);
    }

    public function testGetChatNameSuggestionsIfNotAnonymous(): void
    {
        $this->ilUserMock->method('isAnonymous')->willReturn(false);
        $this->ilUserMock->expects($this->once())->method('getFirstname')->willReturn('John');
        $this->ilUserMock->expects($this->once())->method('getLastname')->willReturn('Doe');
        $this->ilUserMock->expects($this->once())->method('getPublicName')->willReturn('John Doe');
        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn('jdoe');
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $suggestions = $this->user->getChatNameSuggestions();

        $this->assertSame('John Doe', $suggestions['fullname']);
        $this->assertSame('J. Doe', $suggestions['shortname']);
        $this->assertSame('jdoe', $suggestions['login']);
    }

    /**
     * @return array
     */
    public function usernameDataProvider(): array
    {
        return [
            ['username', 'username'],
            ['>username<', '&gt;username&lt;'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ilUserMock = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getId', 'isAnonymous', 'getLogin', 'getPublicName', 'getFirstname', 'getLastname']
        )->getMock();
        $this->ilChatroomMock = $this->getMockBuilder(ilChatroom::class)->disableOriginalConstructor()->onlyMethods(
            ['getRoomId', 'getSetting']
        )->getMock();

        $this->user = new ilChatroomUser($this->ilUserMock, $this->ilChatroomMock);
    }
}
