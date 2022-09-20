<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerBinaryStateTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';

        // Act
        $instance = new ASS_AnswerBinaryState();

        $this->assertInstanceOf('ASS_AnswerBinaryState', $instance);
    }

    public function test_setGetState_shouldReturnUnchangedState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->getState();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isStateChecked_shouldReturnActualState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->isStateChecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isStateSet_shouldReturnActualState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->isStateSet();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isStateUnset_shouldReturnActualState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = !$instance->isStateUnset();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isStateUnchecked_shouldReturnActualState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = !$instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setChecked_shouldAlterState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 0;
        $instance->setState($expected);

        // Act
        $instance->setChecked();
        $actual = $instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setUnchecked_shouldAlterState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;
        $instance->setState($expected);

        // Act
        $instance->setUnchecked();
        $actual = $instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setSet_shouldAlterState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 0;
        $instance->setState($expected);

        // Act
        $instance->setSet();
        $actual = $instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setUnset_shouldAlterState(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;
        $instance->setState($expected);

        // Act
        $instance->setUnset();
        $actual = $instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
