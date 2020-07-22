<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomClearTask
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomClearTaskTest extends ilChatroomAbstractTaskTest
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomClearGUI;
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        require_once './Modules/Chatroom/classes/gui/class.ilChatroomClearGUI.php';
        require_once './Modules/Chatroom/classes/class.ilChatroomServerSettings.php';

        $settings = new ilChatroomServerSettings();

        $this->createGlobalIlUserMock();
        $this->createGlobalIlCtrlMock();
        $this->createGlobalIlLanguageMock();
        $this->createGlobalRbacSystemMock();
        $this->createilChatroomMock();
        $this->createIlChatroomServerConnectorMock($settings);
        $this->createIlChatroomServerConnectorFileGetContentsMock(array('success' => true));
        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);
        $this->createIlObjChatroomGUIGetConnectorMock($this->ilChatroomServerConnectorMock);

        $this->task = $this->createMock(
            'ilChatroomClearGUI',
            array('sendResponse', 'getRoomByObjectId', 'redirectIfNoPermission'),
            array($this->gui)
        );
    }

    public function testExecuteDefault()
    {
        $_REQUEST['sub'] = 0;

        $this->task->expects($this->once())->method('redirectIfNoPermission')->with(
            $this->equalTo('moderate')
        );
        $this->task->expects($this->any())->method('getRoomByObjectId')->will(
            $this->returnValue($this->ilChatroomMock)
        );
        $this->createSendResponseMock($this->task, array(
            'success' => true,
        ));

        $this->setExpectedException('Exception', 'Exit', 0);
        $this->task->executeDefault(null);
    }

    public function testExecuteDefaultFailedPermission()
    {
        $_REQUEST['sub'] = 0;

        $this->task->expects($this->once())->method('redirectIfNoPermission')->with('moderate')->will($this->returnCallback(function () {
            throw new Exception('Weak redirect on no Permission. Send just failure message instead', 1456351768);
        }));

        $this->setExpectedException('Exception', "", 1456351768);
        $this->task->executeDefault(null);
    }

    public function testExecuteDefaultFailedNoRoomExists()
    {
        $_REQUEST['sub'] = 0;

        $this->task->expects($this->once())->method('redirectIfNoPermission')->with(
            $this->equalTo('moderate')
        );
        $this->task->expects($this->any())->method('getRoomByObjectId')->will(
            $this->returnValue(null)
        );
        $this->createSendResponseMock($this->task, array(
            'success' => false,
            'reason' => 'unkown room',
        ));

        $this->setExpectedException('Exception', 'Exit', 0);
        $this->task->executeDefault(null);
    }
}
