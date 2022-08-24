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

    protected function setUp(): void
    {
        parent::setUp();

        $ilCtrl_mock = $this->getMockBuilder(ilCtrl::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $ilCtrl_mock->method('saveParameter');
        $ilCtrl_mock->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $lng_mock = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['txt'])
                         ->getMock();
        $lng_mock->method('txt')->will($this->returnValue('Test'));
        $this->setGlobalVariable('lng', $lng_mock);

        $ilias_mock = new stdClass();
        $ilias_mock->account = new stdClass();
        $ilias_mock->account->id = 6;
        $ilias_mock->account->fullname = 'Esther Tester';

        $this->setGlobalVariable('ilias', $ilias_mock);
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
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
