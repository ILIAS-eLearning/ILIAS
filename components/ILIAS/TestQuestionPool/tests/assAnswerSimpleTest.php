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
class assAnswerSimpleTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ASS_AnswerSimple();

        $this->assertInstanceOf(ASS_AnswerSimple::class, $instance);
    }

    public function test_setGetId_shouldReturnUnchangedId(): void
    {
        $instance = new ASS_AnswerSimple('', 0.0, 0, -1, 0);
        $expected = 1;

        $instance->setId($expected);
        $actual = $instance->getId();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetAnswertext_shouldReturnUnchangedAnswertext(): void
    {
        $instance = new ASS_AnswerSimple('', 0.0, 0, -1, 0);
        $expected = 'The answer, of course, is 42.';

        $instance->setAnswertext($expected);
        $actual = $instance->getAnswertext();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPoints_shouldReturnUnchangedPoints(): void
    {
        $instance = new ASS_AnswerSimple('', 0.0, 0, -1, 0);
        $expected = 42;

        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPoints_shouldReturnUnchangedZeroOnNonNumericInput(): void
    {
        $instance = new ASS_AnswerSimple();
        $expected = 0.0;

        $instance->setPoints('Günther');
        $actual = $instance->getPoints();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetOrder_shouldReturnUnchangedOrder(): void
    {
        $instance = new ASS_AnswerSimple('', 0.0, 0, -1, 0);
        $expected = 42;

        $instance->setOrder($expected);
        $actual = $instance->getOrder();

        $this->assertEquals($expected, $actual);
    }
}
