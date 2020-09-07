<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class ilAssQuestionHintsOrderingClipboardTest extends PHPUnit_Framework_TestCase
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
        $this->markTestIncomplete('Needs mock.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHintsOrderingClipboard.php';

        // Act
        $instance = new ilAssQuestionHintsOrderingClipboard();

        $this->assertInstanceOf('ilAssQuestionHintsOrderingClipboard', $instance);
    }
}
