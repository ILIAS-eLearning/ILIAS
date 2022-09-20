<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMultipleResponseTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';

        // Act
        $instance = new ASS_AnswerMultipleResponse();

        // Assert
        $this->assertInstanceOf('ASS_AnswerMultipleResponse', $instance);
    }

    public function test_setGetPointsUnchecked(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 1;

        // Act
        $instance->setPointsUnchecked($expected);
        $actual = $instance->getPointsUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsUnchecked_InvalidPointsBecomeZero(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 0;

        // Act
        $instance->setPointsUnchecked('Günther');
        $actual = $instance->getPointsUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsChecked(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 2;

        // Act
        $instance->setPointsChecked($expected);
        $actual = $instance->getPointsChecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
