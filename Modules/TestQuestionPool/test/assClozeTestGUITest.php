<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');

        global $DIC;

        require_once './Services/UICore/classes/class.ilCtrl.php';
        $ilCtrl_mock = $this->createMock('ilCtrl');
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');

        unset($DIC['ilCtrl']);
        $DIC['ilCtrl'] = $ilCtrl_mock;
        $GLOBALS['ilCtrl'] = $DIC['ilCtrl'];

        require_once './Services/Language/classes/class.ilLanguage.php';
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->once())->method('txt')->will($this->returnValue('Test'));
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $ilias_mock = new stdClass();
        $ilias_mock->account = new stdClass();
        $ilias_mock->account->id = 6;
        $ilias_mock->account->fullname = 'Esther Tester';

        unset($DIC['ilias']);
        $DIC['ilias'] = $ilias_mock;
        $GLOBALS['ilias'] = $DIC['ilias'];

        $dataCache_mock = $this->createMock('ilObjectDataCache', array(), array(), '', false);
        $DIC['ilObjDataCache'] = $dataCache_mock;
        $GLOBALS['ilObjDataCache'] = $DIC['ilObjDataCache'];

        $access_mock = $this->createMock('ilAccess', array(), array(), '', false);
        $DIC['ilAccess'] = $access_mock;
        $GLOBALS['ilAccess'] = $DIC['ilAccess'];

        $help_mock = $this->createMock('ilHelpGUI', array(), array(), '', false);
        $DIC['ilHelp'] = $help_mock;
        $GLOBALS['ilHelp'] = $help_mock;

        $user_mock = $this->createMock('ilObjUser', array(), array(), '', false);
        $DIC['ilUser'] = $user_mock;
        $GLOBALS['ilUser'] = $user_mock;
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
