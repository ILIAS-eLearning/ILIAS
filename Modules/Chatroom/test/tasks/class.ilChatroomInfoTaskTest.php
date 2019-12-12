<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomInfoTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomInfoTaskTest extends ilChatroomAbstractTaskTest
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomInfoGUI;
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        if (!defined('DB_FETCHMODE_OBJECT')) {
            define('DB_FETCHMODE_OBJECT', 'ASSOC');
        }

        require_once './Modules/Chatroom/classes/gui/class.ilChatroomInfoGUI.php';

        $this->createGlobalIlCtrlMock();
        $this->createGlobalIlLanguageMock();
        $this->createGlobalRbacSystemMock();
        $this->createGlobalIlDBMock();
        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);

        $this->task = $this->createMock(
            'ilChatroomInfoGUI',
            array('sendResponse', 'getRoomByObjectId', 'redirectIfNoPermission', 'createInfoScreenGUI'),
            array($this->gui)
        );
    }

    public function testExecuteDefault()
    {
        $_GET['ref_id'] = 99; // if not set causes error of undefined index

        require_once './Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
        $infoScreenMock = $this->createMock('ilInfoScreenGUI', array('addMetaDataSections'), array($this->gui));
        $infoScreenMock->expects($this->any())->method('addMetaDataSections')->with(
            $this->equalTo(15),
            0,
            null
        );

        $this->task->expects($this->any())->method('redirectIfNoPermission')->with(
            $this->equalTo('view')
        );
        $this->task->expects($this->any())->method('getRoomByObjectId')->will(
            $this->returnValue($this->ilChatroomMock)
        );
        $this->task->expects($this->any())->method('createInfoScreenGUI')->will(
            $this->returnValue($infoScreenMock)
        );
        $GLOBALS['ilCtrl']->expects($this->atLeastOnce())->method('forwardCommand');

        $this->createGlobalRbacSystemCheckAccessMock('visible', true, $this->at(0)); // Suppress raiseError
        $this->createGlobalRbacSystemCheckAccessMock('read', true, $this->at(1)); // Enables News
        $this->task->executeDefault(null);

        $this->createGlobalRbacSystemCheckAccessMock('visible', true, $this->at(0)); // raiseError
        $this->createGlobalRbacSystemCheckAccessMock('read', true, $this->at(1)); // Enables News
        $this->task->executeDefault('method');
    }
}
