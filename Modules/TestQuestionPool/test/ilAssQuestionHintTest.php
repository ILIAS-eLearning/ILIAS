<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class ilAssQuestionHintTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHint.php';

        // Act
        $instance = new ilAssQuestionHint();

        $this->assertInstanceOf('ilAssQuestionHint', $instance);
    }
}
