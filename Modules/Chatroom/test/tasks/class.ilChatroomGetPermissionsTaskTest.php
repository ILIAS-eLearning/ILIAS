<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomGetPermissionsTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomGetPermissionsTaskTest extends ilChatroomAbstractTaskTest
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomGetPermissionsGUI;
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        require_once './Modules/Chatroom/classes/gui/class.ilChatroomGetPermissionsTask.php';

        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);

        $this->task = $this->createMock(
            'ilChatroomGetPermissionsGUI',
            array('sendResponse', 'getRoomByObjectId', 'redirectIfNoPermission'),
            array($this->gui)
        );
    }

    public function testExecuteDefaultDies()
    {
        $this->setExpectedException('Exception', 'METHOD_NOT_IN_USE');
        $this->task->executeDefault(null);
    }
}
