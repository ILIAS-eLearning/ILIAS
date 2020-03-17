<?php

/**
 * Class ilObjChatroomAccessTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAccessTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ilObjChatroomAccess
     */
    protected $access;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        require_once './Services/Administration/classes/class.ilSetting.php';
        require_once './Modules/Chatroom/classes/class.ilObjChatroomAccess.php';
        $this->access = new ilObjChatroomAccess();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('ilObjectAccess', $this->access);
    }

    public function test_getCommands()
    {
        $expected = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true),
            array("permission" => "write", "cmd" => "settings-general", "lang_var" => "settings"),
        );

        $commands = $this->access->_getCommands();

        $this->assertInternalType("array", $commands);
        $this->assertEquals($expected, $commands);
    }

    public function test_checkGotoReturnFalse()
    {
        $GLOBALS['rbacsystem'] = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->setMethods(
            array('checkAccess',)
        )->getMock();
        $GLOBALS['rbacsystem']->expects($this->any())->method('checkAccess')->with(
            $this->equalTo('read'),
            $this->equalTo('1')
        )->will($this->returnValue(false));

        $this->assertFalse($this->access->_checkGoto(''));
        $this->assertFalse($this->access->_checkGoto('chtr'));
        $this->assertFalse($this->access->_checkGoto('chtr_'));
        $this->assertFalse($this->access->_checkGoto('chtr_'));
        $this->assertFalse($this->access->_checkGoto('chtr_test'));
        $this->assertFalse($this->access->_checkGoto('chtr_1'));
    }

    public function test_checkGotoReturnTrue()
    {
        $GLOBALS['rbacsystem'] = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->setMethods(
            array('checkAccess')
        )->getMock();
        $GLOBALS['rbacsystem']->expects($this->once())->method('checkAccess')->with(
            $this->equalTo('read'),
            $this->equalTo('5')
        )->will($this->returnValue(true));
        $this->assertTrue($this->access->_checkGoto('chtr_5'));
    }

    public function test_checkGotoIssueWithTargetNotAString()
    {
        $this->assertFalse($this->access->_checkGoto(array('chtr', '5')));
        $this->assertFalse($this->access->_checkGoto(5));
    }

    public function test_checkAccessReturnFalse()
    {
        $userId = 1;
        $refId = 99;
        $GLOBALS['ilUser'] = $this->getMockBuilder('ilUser')->disableOriginalConstructor()->setMethods(
            array('getId')
        )->getMock();
        $GLOBALS['ilUser']->expects($this->once())->method('getId')->will($this->returnValue($userId));

        $GLOBALS['ilDB'] = $this->getMockBuilder('ilDBMySQL')->disableOriginalConstructor()->setMethods(
            array('quote', 'query', 'fetchAssoc')
        )->getMock();
        $GLOBALS['ilDB']->expects($this->any())->method('quote');
        $GLOBALS['ilDB']->expects($this->any())->method('query');
        $GLOBALS['ilDB']->expects($this->at(0))->method('fetchAssoc')->will(
            $this->returnValue(array('keyword' => 'chat_enabled', 'value' => false))
        );

        $GLOBALS['rbacsystem'] = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->setMethods(
            array('checkAccessOfUser')
        )->getMock();
        $GLOBALS['rbacsystem']->expects($this->once())->method('checkAccessOfUser')->with(
            $this->equalTo($userId),
            $this->equalTo('write'),
            $this->equalTo($refId)
        )->will($this->returnValue(false));

        $this->assertFalse($this->access->_checkAccess('unused', 'unused', $refId, 'unused'));
    }

    public function test_checkAccessReturnTrueWithRbacAccess()
    {
        $userId = 1;
        $refId = 99;
        $GLOBALS['ilUser'] = $this->getMockBuilder('ilUser')->disableOriginalConstructor()->setMethods(
            array('getId')
        )->getMock();
        $GLOBALS['ilUser']->expects($this->once())->method('getId')->will($this->returnValue($userId));

        $GLOBALS['ilDB'] = $this->getMockBuilder('ilDBMySQL')->disableOriginalConstructor()->setMethods(
            array('quote', 'query', 'fetchAssoc')
        )->getMock();
        $GLOBALS['ilDB']->expects($this->any())->method('quote');
        $GLOBALS['ilDB']->expects($this->any())->method('query');
        $GLOBALS['ilDB']->expects($this->any())->method('fetchAssoc')->will(
            $this->returnValue(array('keyword' => 'chat_enabled', 'value' => false))
        );

        $GLOBALS['rbacsystem'] = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->setMethods(
            array('checkAccessOfUser')
        )->getMock();
        $GLOBALS['rbacsystem']->expects($this->once())->method('checkAccessOfUser')->with(
            $this->equalTo($userId),
            $this->equalTo('write'),
            $this->equalTo($refId)
        )->will($this->returnValue(true));

        $this->assertTrue($this->access->_checkAccess('unused', 'unused', $refId, 'unused'));
    }
}
