<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests for single choice questions
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id: assMultipleChoiceTest.php 35946 2012-08-02 21:48:44Z mbecker $
*
*
* @ingroup ServicesTree
*/
class assMultipleChoiceGUITest extends PHPUnit_Framework_TestCase
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
            global $ilCtrl;
            $ilCtrl = $ilCtrl_mock;

            require_once './Services/Language/classes/class.ilLanguage.php';
            $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
            //$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
            global $lng;
            $lng = $lng_mock;

            $ilias_mock = new stdClass();
            $ilias_mock->account = new stdClass();
            $ilias_mock->account->id = 6;
            $ilias_mock->account->fullname = 'Esther Tester';
            global $ilias;
            $ilias = $ilias_mock;
        }
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assMultipleChoiceGUI.php';

        // Act
        $instance = new assMultipleChoiceGUI();

        $this->assertInstanceOf('assMultipleChoiceGUI', $instance);
    }
}
