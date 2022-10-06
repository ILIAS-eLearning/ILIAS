<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Unit tests
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assAnswerTrueFalseTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';

        // Act
        $instance = new ASS_AnswerTrueFalse();

        $this->assertInstanceOf('ASS_AnswerTrueFalse', $instance);
    }

    public function test_setGetCorrectness_shouldReturnUnchangedState(): void
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

    public function test_isTrue_shouldReturnTrue(): void
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

    public function test_isFalse_shouldReturnFalseOnTrueState(): void
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

    public function test_setFalseGetCorrectness_shouldReturnFalse(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerTrueFalse.php';
        $instance = new ASS_AnswerTrueFalse();
        $expected = false;

        // Act
        $instance->setFalse();
        $actual = $instance->getCorrectness();

        // Assert
        $this->assertEquals((bool) $expected, (bool) $actual);
    }

    public function test_setTrueIsTrue_shouldReturnUnchangedState(): void
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

    public function test_setFalseIsFalse_shouldReturnUnchangedState(): void
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
