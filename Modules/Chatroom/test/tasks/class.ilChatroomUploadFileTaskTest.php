<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomUploadFileTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomUploadFileTaskTest extends ilChatroomAbstractTaskTest
{
    protected function setUp()
    {
        parent::setUp();

        require_once './Modules/Chatroom/classes/gui/class.ilChatroomUploadFileGUI.php';

        $this->createIlObjChatroomMock(15);
        $this->createIlObjChatroomGUIMock($this->object);
    }

    public function testConstructorDies()
    {
        $this->setExpectedException('Exception', 'METHOD_NOT_IN_USE');
        $task = new ilChatroomUploadFileGUI();
    }
}
