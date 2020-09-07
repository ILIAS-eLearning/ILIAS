<?php

/**
 * Class ilChatroomAbstractTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroom
     */
    protected $ilChatroomMock;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomUser
     */
    protected $ilChatroomUserMock;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }
    }

    protected function createIlChatroomMock()
    {
        require_once './Modules/Chatroom/classes/class.ilChatroom.php';
        require_once './Services/Utilities/classes/class.ilUtil.php';

        $this->ilChatroomMock = $this->getMockBuilder('ilChatroom')->disableOriginalConstructor()->setMethods(
            array('isOwnerOfPrivateRoom', 'clearMessages')
        )->getMock();

        return $this->ilChatroomMock;
    }

    protected function createIlChatroomUserMock()
    {
        require_once './Modules/Chatroom/classes/class.ilChatroomUser.php';

        $this->ilChatroomUserMock = $this->getMockBuilder('ilChatroomUser')->disableOriginalConstructor()->setMethods(
            array('getUserId', 'getUsername')
        )->getMock();

        return $this->ilChatroomUserMock;
    }

    protected function createGlobalIlDBMock()
    {
        $GLOBALS['ilDB'] = $this->getMockBuilder('ilDBMySQL')->disableOriginalConstructor()->setMethods(
            array('quote', 'query', 'fetchAssoc')
        )->getMock();

        return $GLOBALS['ilDB'];
    }
}
