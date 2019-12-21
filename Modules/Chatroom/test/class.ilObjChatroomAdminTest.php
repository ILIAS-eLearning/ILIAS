<?php

/**
 * Class ilObjChatroomAdminTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAdminTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        require_once './Services/Utilities/classes/class.ilBenchmark.php';
        $ilBenchMock = $this->createMock('ilBenchmark');
        $ilBenchMock->expects($this->any())->method('start');
        $ilBenchMock->expects($this->any())->method('stop');
        global $ilBench;
        $ilBench = $ilBenchMock;

        require_once './Modules/Chatroom/classes/class.ilObjChatroomAdmin.php';
    }

    public function testConstructor()
    {
        define('DEBUG', false);
        $admin = new ilObjChatroomAdmin();

        $this->assertInstanceOf('ilObjChatroomAdmin', $admin);
        $this->assertEquals(0, $admin->getId());
        $this->assertEquals('chta', $admin->getType());
    }
}
