<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestGUITest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');

            require_once './Services/UICore/classes/class.ilCtrl.php';
            $ilCtrl_mock = $this->createMock('ilCtrl');
            $ilCtrl_mock->expects($this->any())->method('saveParameter');
            $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
            global $DIC;
            unset($DIC['ilCtrl']);
            $DIC['ilCtrl'] = $ilCtrl_mock;
            $GLOBALS['ilCtrl'] = $DIC['ilCtrl'];

            require_once './Services/Language/classes/class.ilLanguage.php';
            $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
            $lng_mock->expects($this->once())->method('txt')->will($this->returnValue('Test'));
            global $DIC;
            unset($DIC['lng']);
            $DIC['lng'] = $lng_mock;
            $GLOBALS['lng'] = $DIC['lng'];

            $ilias_mock = new stdClass();
            $ilias_mock->account = new stdClass();
            $ilias_mock->account->id = 6;
            $ilias_mock->account->fullname = 'Esther Tester';
            global $DIC;
            unset($DIC['ilias']);
            $DIC['ilias'] = $ilias_mock;
            $GLOBALS['ilias'] = $DIC['ilias'];
        }
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        /**
         * @runInSeparateProcess
         * @preserveGlobalState enabled
         */
        //$this->markTestIncomplete('Needs mock ilCtrl.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTestGUI.php';



        // Act
        $instance = new assClozeTestGUI();

        $this->assertInstanceOf('assClozeTestGUI', $instance);
    }
}
