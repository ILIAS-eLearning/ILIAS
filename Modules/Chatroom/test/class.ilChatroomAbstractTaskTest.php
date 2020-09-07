<?php

require_once 'class.ilChatroomAbstractTest.php';

/**
 * Class ilChatroomAbstractTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTaskTest extends ilChatroomAbstractTest
{
    /**
     * @var int
     */
    const TEST_REF_ID = 99;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomObjectGui
     */
    protected $gui;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomServerConnector
     */
    protected $ilChatroomServerConnectorMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilObjChatroom
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();

        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', time());
        }
    }

    protected function createGlobalIlLanguageMock()
    {
        require_once './Services/Language/classes/class.ilLanguage.php';
        $GLOBALS['lng'] = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->setMethods(
            array('loadLanguageModule', 'txt')
        )->getMock();

        $GLOBALS['lng']->expects($this->any())->method('loadLanguageModule')->with($this->logicalOr($this->equalTo('chatroom'), $this->equalTo('meta')));
        $GLOBALS['lng']->expects($this->any())->method('txt');

        return $GLOBALS['lng'];
    }

    protected function createGlobalRbacSystemMock()
    {
        require_once './Services/AccessControl/classes/class.ilRbacSystem.php';
        $GLOBALS['rbacsystem'] = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->setMethods(
            array('checkAccess')
        )->getMock();

        return $GLOBALS['rbacsystem'];
    }

    /**
     * @param PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount $times
     * @param string                                               $permission
     * @param boolean                                              $result
     */
    protected function createGlobalRbacSystemCheckAccessMock($permission, $result, $times = null)
    {
        if ($times == null) {
            $times = $this->any();
        }

        return $GLOBALS['rbacsystem']->expects($times)->method('checkAccess')->with($this->equalTo($permission))->will(
            $this->returnValue($result)
        );
    }

    protected function createGlobalIlCtrlMock()
    {
        require_once './Services/UICore/classes/class.ilCtrl.php';

        $GLOBALS['ilCtrl'] = $this->getMockBuilder('ilCtrl')->disableOriginalConstructor()->setMethods(
            array('setParameterByClass', 'redirectByClass', 'forwardCommand')
        )->getMock();
        $GLOBALS['ilCtrl']->expects($this->any())->method('setParameterByClass');
        $GLOBALS['ilCtrl']->expects($this->any())->method('redirectByClass');

        return $GLOBALS['ilCtrl'];
    }

    protected function createGlobalIlUserMock()
    {
        require_once 'Services/User/classes/class.ilObjUser.php';

        $GLOBALS['ilUser'] = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(
            array()
        )->getMock();

        return $GLOBALS['ilUser'];
    }

    protected function createIlObjChatroomGUIMock($object)
    {
        require_once './Modules/Chatroom/classes/class.ilObjChatroomGUI.php';

        $this->gui = $this->getMockBuilder('ilObjChatroomGUI')->disableOriginalConstructor()->disableArgumentCloning()->setMethods(
            array('getRefId', 'getConnector', 'switchToVisibleMode')
        )->getMock();
        $this->gui->ref_id = self::TEST_REF_ID;
        $this->gui->object = $object;

        return $this->gui;
    }

    protected function createIlObjChatroomGUIGetConnectorMock($returnValue)
    {
        $this->gui->expects($this->any())->method('getConnector')->will($this->returnValue($returnValue));
    }

    /**
     * @param int     $userId
     * @param int     $subRoomId
     * @param boolean $result
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createIlChatroomIsOwnerOfPrivateRoomMock($userId, $subRoomId, $result)
    {
        return $this->ilChatroomMock->expects($this->at(0))->method('isOwnerOfPrivateRoom')->with(
            $this->equalTo($userId),
            $this->equalTo($subRoomId)
        )->will($this->returnValue($result));
    }

    protected function createIlChatroomUserGetUserIdMock($userId)
    {
        return $this->ilChatroomUserMock->expects($this->any())->method('getUserId')->will($this->returnValue($userId));
    }

    /**
     * @param ilChatroomServerSettings $settings
     * @return ilChatroomServerConnector|PHPUnit_Framework_MockObject_MockObject
     */
    protected function createIlChatroomServerConnectorMock($settings)
    {
        require_once './Modules/Chatroom/classes/class.ilChatroomServerConnector.php';

        $this->ilChatroomServerConnectorMock = $this->getMockBuilder('ilChatroomServerConnector')->setConstructorArgs(
            array($settings)
        )->setMethods(array('file_get_contents'))->getMock();

        return $this->ilChatroomServerConnectorMock;
    }

    protected function createIlChatroomServerConnectorFileGetContentsMock($returnValue)
    {
        return $this->ilChatroomServerConnectorMock->expects($this->any())->method('file_get_contents')->will(
            $this->returnValue($returnValue)
        );
    }

    protected function createIlObjChatroomMock($id)
    {
        require_once './Modules/Chatroom/classes/class.ilObjChatroom.php';

        $this->object = $this->getMockBuilder('ilObjChatroom')->disableOriginalConstructor()->setMethods(
            array('getId')
        )->getMock();
        $this->object->expects($this->any())->method('getId')->will($this->returnValue($id));

        return $this->object;
    }

    protected function createSendResponseMock($mock, $response)
    {
        $mock->expects($this->once())->method('sendResponse')->with(
            $this->equalTo($response)
        )->will(
            $this->returnCallback(
                function () {
                    throw new Exception('Exit', 0);
                }
            )
        );
    }
}
