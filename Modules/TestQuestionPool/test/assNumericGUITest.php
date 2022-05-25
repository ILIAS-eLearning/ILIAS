<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assNumericGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();

        $ilCtrl_mock = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $ilias_mock = new stdClass();
        $ilias_mock->account = new stdClass();
        $ilias_mock->account->id = 6;
        $ilias_mock->account->fullname = 'Esther Tester';
        $this->setGlobalVariable('ilias', $ilias_mock);
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericGUI.php';

        // Act
        $instance = new assNumericGUI();

        $this->assertInstanceOf('assNumericGUI', $instance);
    }
}
