<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerSimpleTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';

        // Act
        $instance = new ASS_AnswerSimple();

        $this->assertInstanceOf('ASS_AnswerSimple', $instance);
    }

    public function test_setGetId_shouldReturnUnchangedId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
        $instance = new ASS_AnswerSimple("", 0.0, 0, -1, 0);
        $expected = 1;

        // Act
        $instance->setId($expected);
        $actual = $instance->getId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetAnswertext_shouldReturnUnchangedAnswertext(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
        $instance = new ASS_AnswerSimple("", 0.0, 0, -1, 0);
        $expected = 'The answer, of course, is 42.';

        // Act
        $instance->setAnswertext($expected);
        $actual = $instance->getAnswertext();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPoints_shouldReturnUnchangedPoints(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
        $instance = new ASS_AnswerSimple("", 0.0, 0, -1, 0);
        $expected = 42;

        // Act
        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPoints_shouldReturnUnchangedZeroOnNonNumericInput(): void
    {
        // Note: We want to get rid of this functionality in the class.

        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
        $instance = new ASS_AnswerSimple();
        $expected = 0.0;

        // Act
        $instance->setPoints('Günther');
        $actual = $instance->getPoints();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetOrder_shouldReturnUnchangedOrder(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
        $instance = new ASS_AnswerSimple("", 0.0, 0, -1, 0);
        $expected = 42;

        // Act
        $instance->setOrder($expected);
        $actual = $instance->getOrder();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
