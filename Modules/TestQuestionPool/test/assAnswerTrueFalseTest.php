<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Unit tests
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assAnswerTrueFalseTest extends PHPUnit_Framework_TestCase
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
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';

        // Act
        $instance = new ASS_AnswerTrueFalse();

        $this->assertInstanceOf('ASS_AnswerTrueFalse', $instance);
    }

    public function test_setGetCorrectness_shouldReturnUnchangedState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        // Act
        $instance->setCorrectness($expected);
        $actual = $instance->getCorrectness();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isTrue_shouldReturnTrue()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        // Act
        $instance->setCorrectness($expected);

        // Assert
        $this->assertEquals($expected, $instance->isTrue());
        $this->assertEquals($expected, $instance->isCorrect());
    }

    public function test_isFalse_shouldReturnFalseOnTrueState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = false;

        // Act
        $instance->setCorrectness(true);

        // Assert
        $this->assertEquals($expected, $instance->isFalse());
        $this->assertEquals($expected, $instance->isIncorrect());
    }

    /**
     * @TODO: Fix bug! getCorrectness returns int instead of bool.
     */
    public function test_setFalseGetCorrectness_shouldReturnFalse()
    {
        $this->markTestIncomplete('Bug detected, fix not applied yet due to poor coverage.');

        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = false;

        // Act
        $instance->setFalse();
        $actual = $instance->getCorrectness();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setTrueIsTrue_shouldReturnUnchangedState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        // Act
        $instance->setTrue();
        $actual = $instance->isTrue();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setFalseIsFalse_shouldReturnUnchangedState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        // Act
        $instance->setFalse();
        $actual = $instance->isFalse();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
