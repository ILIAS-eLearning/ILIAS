<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomUserTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomUserTest extends ilChatroomAbstractTest
{
    protected ilObjUser $ilUserMock;

    protected ilChatroomUser $user;

    public function testGetUserIdIfNotAnonymous() : void
    {
        $userId = 6;

        $this->ilUserMock->expects($this->once())->method('getId')->willReturn($userId);
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->willReturn(false);

        $this->assertEquals($userId, $this->user->getUserId());
    }

    public function testGetUserIdFromSessionIfAnonymous() : void
    {
        $userId = 6;
        $roomId = 99;

        $this->ilUserMock->expects($this->once())->method('getId')->willReturn($userId);
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->willReturn(true);

        $this->ilChatroomMock->method('getRoomId')->willReturn($roomId);

        $_SESSION['chat'] = [
            $roomId => [
                'user_id' => $userId,
            ],
        ];

        $this->assertEquals($userId, $this->user->getUserId());
    }

    public function testGetUserIdRandomGeneratedIfAnonymous() : void
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
    public function testSetUsername(string $username, string $expected) : void
    {
        $this->user->setUsername($username);
        $this->assertEquals($expected, $this->user->getUsername());
    }

    public function testGetUsernameFromSession() : void
    {
        $username = 'username';
        $roomId = 99;
        $_SESSION['chat'][$roomId]['username'] = $username;

        $this->ilChatroomMock->method('getRoomId')->willReturn(99);

        $this->assertEquals($username, $this->user->getUsername());
    }

    /**
     * @todo if required session value is not set, there will be a warning.
     *       Need to check if required value isset.
     */
    public function testGetUsernameFromIlObjUser() : void
    {
        $username = 'login';
        $roomId = 99;
        $_SESSION['chat'][$roomId]['username'] = ''; // Fix missing key warning

        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn($username);
        $this->ilChatroomMock->method('getRoomId')->willReturn($roomId);

        $this->assertEquals($username, $this->user->getUsername());
    }

    public function testBuildAnonymousName() : void
    {
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $firstName = $this->user->buildAnonymousName();
        $secondName = $this->user->buildAnonymousName();

        $this->assertNotEquals($firstName, $secondName);
    }

    public function testBuildLogin() : void
    {
        $username = 'username';
        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn($username);

        $this->assertEquals($username, $this->user->buildLogin());
    }

    public function testBuildFullname() : void
    {
        $fullname = 'John Doe';
        $this->ilUserMock->expects($this->once())->method('getPublicName')->willReturn($fullname);

        $this->assertEquals($fullname, $this->user->buildFullname());
    }

    public function testBuildShortname() : void
    {
        $firstname = 'John';
        $lastname = 'Doe';
        $this->ilUserMock->expects($this->once())->method('getFirstname')->willReturn($firstname);
        $this->ilUserMock->expects($this->once())->method('getLastname')->willReturn($lastname);

        $this->assertEquals('J. Doe', $this->user->buildShortname());
    }

    public function testGetChatNameSuggestionsIfAnonymous() : void
    {
        $this->ilUserMock->method('isAnonymous')->willReturn(true);
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $first = $this->user->getChatNameSuggestions();
        $second = $this->user->getChatNameSuggestions();

        $this->assertNotEquals($first, $second);
    }

    public function testGetChatNameSuggestionsIfNotAnonymous() : void
    {
        $this->ilUserMock->method('isAnonymous')->willReturn(false);
        $this->ilUserMock->expects($this->once())->method('getFirstname')->willReturn('John');
        $this->ilUserMock->expects($this->once())->method('getLastname')->willReturn('Doe');
        $this->ilUserMock->expects($this->once())->method('getPublicName')->willReturn('John Doe');
        $this->ilUserMock->expects($this->once())->method('getLogin')->willReturn('jdoe');
        $this->ilChatroomMock->method('getSetting')->willReturn('#_anonymous');

        $suggestions = $this->user->getChatNameSuggestions();

        $this->assertEquals('John Doe', $suggestions['fullname']);
        $this->assertEquals('J. Doe', $suggestions['shortname']);
        $this->assertEquals('jdoe', $suggestions['login']);
    }

    /**
     * @return array
     */
    public function usernameDataProvider() : array
    {
        return [
            ['username', 'username'],
            ['>username<', '&gt;username&lt;'],
        ];
    }

    protected function setUp() : void
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
