<?php

require_once dirname(__FILE__) . '/class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomTaskHandlerTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomTaskHandlerTest extends ilChatroomAbstractTaskTest
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomGUIHandlerMock
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();

        $this->createGlobalIlLanguageMock();
        $this->createGlobalIlCtrlMock();
        $this->createGlobalIlUserMock();
        $this->createGlobalRbacSystemMock();
        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);

        require_once './Modules/Chatroom/test/mocks/class.ilChatroomTaskHandlerMock.php';
        $this->handler = new ilChatroomGUIHandlerMock($this->gui);
    }

    public function testExecuteDefault()
    {
        $this->assertEquals('default', $this->handler->executeDefault('default'));
    }

    public function testExecuteWithMethodExit()
    {
        $this->assertTrue($this->handler->execute('testFunc'));
    }

    public function testExecuteWithMethodNotExit()
    {
        $this->assertEquals('nonExistingFunc', $this->handler->execute('nonExistingFunc'));
    }

    public function testIsSuccessfull()
    {
        $response = array('success' => true);
        $this->assertTrue($this->handler->isSuccessful(json_encode($response)));

        $response = array('success' => false);
        $this->assertFalse($this->handler->isSuccessful(json_encode($response)));

        $response = array('failed' => false);
        $this->assertFalse($this->handler->isSuccessful(json_encode($response)));

        $response = 'nonJson';
        $this->assertFalse($this->handler->isSuccessful($response));
    }

    /**
     * @dataProvider canModerateDataProvider
     * @param boolean $isOwnerOfPrivateRoomValue
     * @param boolean $checkAccessValue
     * @param boolean $result
     */
    public function testCanModerate($isOwnerOfPrivateRoomValue, $checkAccessValue, $result)
    {
        $userId = 6;
        $subRoomId = 0;

        $this->createIlChatroomMock();
        $this->createIlChatroomIsOwnerOfPrivateRoomMock($userId, $subRoomId, $isOwnerOfPrivateRoomValue);
        $this->createGlobalRbacSystemCheckAccessMock('moderate', $checkAccessValue);

        $this->assertEquals($result, $this->handler->mockedCanModerate($this->ilChatroomMock, $subRoomId, $userId));
    }

    public function testExitIfNoRoomExists()
    {
        $this->createIlChatroomMock();
        $this->handler->mockedExitIfNoRoomExists($this->ilChatroomMock);

        $this->setExpectedException(
            'Exception',
            json_encode(
                array(
                    'success' => false,
                    'reason' => 'unkown room',
                )
            )
        );
        $this->handler->mockedExitIfNoRoomExists(null);
    }

    public function testExitIfNoRoomPermissionSuccess()
    {
        $userId = 6;
        $subRoomId = 0;

        $this->createIlChatroomMock();
        $this->createIlChatroomIsOwnerOfPrivateRoomMock($userId, $subRoomId, true);
        $this->createIlChatroomUserMock();
        $this->createIlChatroomUserGetUserIdMock($userId);
        $this->createGlobalRbacSystemCheckAccessMock('moderate', true);

        $this->handler->mockedExitIfNoRoomPermission($this->ilChatroomMock, $subRoomId, $this->ilChatroomUserMock);
    }

    public function testExitIfNoRoomPermissionFails()
    {
        $userId = 6;
        $subRoomId = 0;

        $this->createIlChatroomMock();
        $this->createIlChatroomIsOwnerOfPrivateRoomMock($userId, $subRoomId, false);
        $this->createIlChatroomUserMock();
        $this->createIlChatroomUserGetUserIdMock($userId);
        $this->createGlobalRbacSystemCheckAccessMock('moderate', false);

        $this->setExpectedException(
            'Exception',
            json_encode(
                array(
                    'success' => false,
                    'reason' => 'not owner of private room',
                )
            )
        );

        $this->handler->mockedExitIfNoRoomPermission($this->ilChatroomMock, $subRoomId, $this->ilChatroomUserMock);
    }

    /**
     * @todo Weak static call
     */
    public function testRedirectIfNoPermission()
    {
        $this->createGlobalRbacSystemCheckAccessMock('moderate', true, $this->at(0));
        $this->createGlobalRbacSystemCheckAccessMock('user', false, $this->at(1));

        $this->handler->redirectIfNoPermission('moderate');
        $this->handler->redirectIfNoPermission('user');
    }

    /**
     * @return array
     */
    public function canModerateDataProvider()
    {
        return array(
            array(true, true, true),
            array(true, false, false),
            array(false, true, false),
        );
    }
}
