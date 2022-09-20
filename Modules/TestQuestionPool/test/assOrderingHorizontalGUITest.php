<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assOrderingHorizontalGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();

        require_once './Services/UICore/classes/class.ilCtrl.php';
        $ilCtrl_mock = $this->createMock('ilCtrl');
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        require_once './Services/Language/classes/class.ilLanguage.php';
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('Test'));
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assOrderingHorizontalGUI.php';

        // Act
        $instance = new assOrderingHorizontalGUI();

        $this->assertInstanceOf('assOrderingHorizontalGUI', $instance);
    }
}
