<?php

/**
 * Class ilObjChatroomAdminAccessTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAdminAccessTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ilObjChatroomAdminAccess
     */
    protected $adminAccess;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $ilAccessMock;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        require_once './Services/AccessControl/classes/class.ilAccessHandler.php';
        $this->ilAccessMock = $this->createMock('ilAccessHandler');
        global $ilAccess;
        $ilAccess = $this->ilAccessMock;

        require_once './Modules/Chatroom/classes/class.ilObjChatroomAdminAccess.php';
        $this->adminAccess = new ilObjChatroomAdminAccess();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('ilObjectAccess', $this->adminAccess);
    }

    public function test_getCommands()
    {
        $expected = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
            array("permission" => "write", "cmd" => "versions", "lang_var" => "versions"),
        );

        $commands = $this->adminAccess->_getCommands();

        $this->assertInternalType("array", $commands);
        $this->assertEquals($expected, $commands);
    }

    public function test_checkGotoReturnFalse()
    {
        $this->ilAccessMock->expects($this->any())->method('checkAccess')->with($this->equalTo('visible'), $this->equalTo(''), $this->equalTo('1'))->will($this->returnValue(false));

        $this->assertFalse($this->adminAccess->_checkGoto(''));
        $this->assertFalse($this->adminAccess->_checkGoto('chtr'));
        $this->assertFalse($this->adminAccess->_checkGoto('chtr_'));
        $this->assertFalse($this->adminAccess->_checkGoto('chtr_'));
        $this->assertFalse($this->adminAccess->_checkGoto('chtr_test'));
        $this->assertFalse($this->adminAccess->_checkGoto('chtr_1'));
    }

    public function test_checkGotoReturnTrue()
    {
        $this->ilAccessMock->expects($this->once())->method('checkAccess')->with($this->equalTo('visible'), $this->equalTo(''), $this->equalTo('5'))->will($this->returnValue(true));
        $this->assertTrue($this->adminAccess->_checkGoto('chtr_5'));
    }

    public function test_checkGotoIssueWithTargetNotAString()
    {
        $this->assertFalse($this->adminAccess->_checkGoto(array('chtr', '5')));
        $this->assertFalse($this->adminAccess->_checkGoto(5));
    }
}
