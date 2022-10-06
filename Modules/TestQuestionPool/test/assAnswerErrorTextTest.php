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
* Unit tests for assAnswerErrorTextTest
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerErrorTextTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';

        // Act
        $instance = new assAnswerErrorText('errortext');

        // Assert
        $this->assertTrue(true);
    }


    public function test_instantiateObjectFull(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';

        // Act
        $instance = new assAnswerErrorText(
            'errortext',
            'correcttext',
            1
        );

        // Assert
        $this->assertTrue(true);
    }

    public function test_setGetPoints_valid(): void
    {
        //$this->markTestIncomplete('Testing an uncommitted feature.');
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';
        $instance = new assAnswerErrorText('errortext');
        $expected = 0.01;

        // Act
        $instance->points = $expected;
        $actual = $instance->points;

        // Assert
        $this->assertEquals($actual, $expected);
    }

    public function test_setGetTextCorrect(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';
        $instance = new assAnswerErrorText('errortext');
        $expected = 'Correct text';

        // Act
        $instance->text_correct = $expected;
        $actual = $instance->text_correct;

        // Assert
        $this->assertEquals($actual, $expected);
    }

    public function test_setGetTextWrong_valid(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';
        $instance = new assAnswerErrorText('errortext');
        $expected = 'Errortext';

        // Act
        $instance->text_wrong = $expected;
        $actual = $instance->text_wrong;

        // Assert
        $this->assertEquals($actual, $expected);
    }

    public function test_setTextWrong_invalid(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerErrorText.php';
        $instance = new assAnswerErrorText('errortext');
        $expected = '';

        // Act
        $instance->text_wrong = $expected;
        $actual = $instance->text_wrong;

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
