<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assQuestionTest extends assBaseTestCase
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
        $this->markTestIncomplete('Abstract class - needs fixture class for tests.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';

        // Act
        //$instance = new assQuestion();

        //$this->assertInstanceOf('assQuestion', $instance);
    }
}
