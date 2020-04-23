<?php

/**
 * Class ilChatroomUserTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomUserTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilObjUser
     */
    protected $ilUserMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroom
     */
    protected $ilChatroomMock;

    /**
     * @var ilChatroomUser
     */
    protected $user;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        require_once './Modules/Chatroom/classes/class.ilChatroomUser.php';
        //require_once 'Services/User/classes/class.ilObjUser.php';
        $this->ilUserMock = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(
            array('getId', 'isAnonymous', 'getLogin', 'getPublicName', 'getFirstname', 'getLastname')
        )->getMock();
        $this->ilChatroomMock = $this->getMockBuilder('ilChatroom')->disableOriginalConstructor()->setMethods(
            array('getRoomId', 'getSetting')
        )->getMock();

        $this->user = new ilChatroomUser($this->ilUserMock, $this->ilChatroomMock);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('ilChatroomUser', $this->user);
    }

    public function testGetUserIdIfNotAnonymous()
    {
        $userId = 6;

        $this->ilUserMock->expects($this->once())->method('getId')->will($this->returnValue($userId));
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->will($this->returnValue(false));

        $this->assertEquals($userId, $this->user->getUserId());
    }

    public function testGetUserIdFromSessionIfAnonymous()
    {
        $userId = 6;
        $roomId = 99;

        $this->ilUserMock->expects($this->once())->method('getId')->will($this->returnValue($userId));
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->will($this->returnValue(true));

        $this->ilChatroomMock->expects($this->any())->method('getRoomId')->will($this->returnValue($roomId));

        $_SESSION['chat'] = array(
            $roomId => array(
                'user_id' => $userId,
            ),
        );

        $this->assertEquals($userId, $this->user->getUserId());
    }

    public function testGetUserIdRandomGeneratedIfAnonymous()
    {
        $this->ilUserMock->expects($this->once())->method('getId')->will($this->returnValue(null));
        $this->ilUserMock->expects($this->once())->method('isAnonymous')->will($this->returnValue(true));

        $this->ilChatroomMock->expects($this->any())->method('getRoomId')->will($this->returnValue(99));

        $this->assertNotNull($this->user->getUserId());
    }

    /**
     * @dataProvider usernameDataProvider
     * @param string $username
     * @param string $expected
     */
    public function testSetUsername($username, $expected)
    {
        $this->user->setUsername($username);
        $this->assertEquals($expected, $this->user->getUsername());
    }

    public function testGetUsernameFromSession()
    {
        $username = 'username';
        $roomId = 99;
        $_SESSION['chat'][$roomId]['username'] = $username;

        $this->ilChatroomMock->expects($this->any())->method('getRoomId')->will($this->returnValue(99));

        $this->assertEquals($username, $this->user->getUsername());
    }

    /**
     * @todo if required session value is not set, there will be a warning.
     *       Need to check if required value isset.
     */
    public function testGetUsernameFromIlObjUser()
    {
        $username = 'login';
        $roomId = 99;
        $_SESSION['chat'][$roomId]['username'] = ''; // Fix missing key warning

        $this->ilUserMock->expects($this->once())->method('getLogin')->will($this->returnValue($username));
        $this->ilChatroomMock->expects($this->any())->method('getRoomId')->will($this->returnValue($roomId));

        $this->assertEquals($username, $this->user->getUsername());
    }

    public function testBuildAnonymousName()
    {
        $this->ilChatroomMock->expects($this->any())->method('getSetting')->will($this->returnValue('#_anonymous'));

        $firstName = $this->user->buildAnonymousName();
        $secondName = $this->user->buildAnonymousName();

        $this->assertNotEquals($firstName, $secondName);
    }

    public function testBuildLogin()
    {
        $username = 'username';
        $this->ilUserMock->expects($this->once())->method('getLogin')->will($this->returnValue($username));

        $this->assertEquals($username, $this->user->buildLogin());
    }

    public function testBuildFullname()
    {
        $fullname = 'John Doe';
        $this->ilUserMock->expects($this->once())->method('getPublicName')->will($this->returnValue($fullname));

        $this->assertEquals($fullname, $this->user->buildFullname());
    }

    public function testBuildShortname()
    {
        $firstname = 'John';
        $lastname = 'Doe';
        $this->ilUserMock->expects($this->once())->method('getFirstname')->will($this->returnValue($firstname));
        $this->ilUserMock->expects($this->once())->method('getLastname')->will($this->returnValue($lastname));

        $this->assertEquals('J. Doe', $this->user->buildShortname());
    }

    public function testGetChatNameSuggestionsIfAnonymous()
    {
        $this->ilUserMock->expects($this->any())->method('isAnonymous')->will($this->returnValue(true));
        $this->ilChatroomMock->expects($this->any())->method('getSetting')->will($this->returnValue('#_anonymous'));

        $first = $this->user->getChatNameSuggestions();
        $second = $this->user->getChatNameSuggestions();

        $this->assertNotEquals($first, $second);
    }

    public function testGetChatNameSuggestionsIfNotAnonymous()
    {
        $this->ilUserMock->expects($this->any())->method('isAnonymous')->will($this->returnValue(false));
        $this->ilUserMock->expects($this->once())->method('getFirstname')->will($this->returnValue('John'));
        $this->ilUserMock->expects($this->once())->method('getLastname')->will($this->returnValue('Doe'));
        $this->ilUserMock->expects($this->once())->method('getPublicName')->will($this->returnValue('John Doe'));
        $this->ilUserMock->expects($this->once())->method('getLogin')->will($this->returnValue('jdoe'));
        $this->ilChatroomMock->expects($this->any())->method('getSetting')->will($this->returnValue('#_anonymous'));

        $suggestions = $this->user->getChatNameSuggestions();

        $this->assertEquals('John Doe', $suggestions['fullname']);
        $this->assertEquals('J. Doe', $suggestions['shortname']);
        $this->assertEquals('jdoe', $suggestions['login']);
    }

    /**
     * @return array
     */
    public function usernameDataProvider()
    {
        return array(
            array('username', 'username'),
            array('>username<', '&gt;username&lt;'),
        );
    }
}
