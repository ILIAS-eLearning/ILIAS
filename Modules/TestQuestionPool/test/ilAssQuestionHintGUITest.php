<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class ilAssQuestionHintGUITest extends PHPUnit_Framework_TestCase
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
        }
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        $this->markTestIncomplete('Needs question mock.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';

        // Act
        $instance = new ilAssQuestionHintGUI();

        $this->assertInstanceOf('ilAssQuestionHintGUI', $instance);
    }
}
