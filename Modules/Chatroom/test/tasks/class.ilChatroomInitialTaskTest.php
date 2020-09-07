<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomInitialTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomInitialTaskTest extends ilChatroomAbstractTaskTest
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomInitialGUI;
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        require_once './Modules/Chatroom/classes/gui/class.ilChatroomInitialGUI.php';

        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);

        $this->task = $this->createMock(
            'ilChatroomInitialGUI',
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
