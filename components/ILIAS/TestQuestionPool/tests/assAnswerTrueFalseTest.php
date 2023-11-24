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
 * @ingroup components\ILIASTestQuestionPool
 */
class assAnswerTrueFalseTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ASS_AnswerTrueFalse();

        $this->assertInstanceOf(ASS_AnswerTrueFalse::class, $instance);
    }

    public function test_setGetCorrectness_shouldReturnUnchangedState(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        $instance->setCorrectness($expected);
        $actual = $instance->getCorrectness();

        $this->assertEquals($expected, $actual);
    }

    public function test_isTrue_shouldReturnTrue(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        $instance->setCorrectness($expected);

        $this->assertEquals($expected, $instance->isTrue());
        $this->assertEquals($expected, $instance->isCorrect());
    }

    public function test_isFalse_shouldReturnFalseOnTrueState(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = false;

        $instance->setCorrectness(true);

        $this->assertEquals($expected, $instance->isFalse());
        $this->assertEquals($expected, $instance->isIncorrect());
    }

    public function test_setFalseGetCorrectness_shouldReturnFalse(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = false;

        $instance->setFalse();
        $actual = $instance->getCorrectness();

        $this->assertEquals((bool) $expected, (bool) $actual);
    }

    public function test_setTrueIsTrue_shouldReturnUnchangedState(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        $instance->setTrue();
        $actual = $instance->isTrue();

        $this->assertEquals($expected, $actual);
    }

    public function test_setFalseIsFalse_shouldReturnUnchangedState(): void
    {
        $instance = new ASS_AnswerTrueFalse();
        $expected = true;

        $instance->setFalse();
        $actual = $instance->isFalse();

        $this->assertEquals($expected, $actual);
    }
}
