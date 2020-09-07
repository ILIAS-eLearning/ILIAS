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
class assMultipleChoiceGUITest extends assBaseTestCase
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

            parent::setUp();

            require_once './Services/UICore/classes/class.ilCtrl.php';
            $ilCtrl_mock = $this->createMock('ilCtrl');
            $ilCtrl_mock->expects($this->any())->method('saveParameter');
            $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
            $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

            require_once './Services/Language/classes/class.ilLanguage.php';
            $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
            //$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
            $this->setGlobalVariable('lng', $lng_mock);

            $this->setGlobalVariable('ilias', $this->getIliasMock());
            $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
            $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
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
