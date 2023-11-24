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
* @ingroup components\ILIASTestQuestionPool
*/
class assAnswerErrorTextTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        $instance = new assAnswerErrorText('errortext');

        $this->assertInstanceOf(assAnswerErrorText::class, $instance);
    }


    public function test_instantiateObjectFull(): void
    {
        $instance = new assAnswerErrorText(
            'errortext',
            'correcttext',
            0.01,
            21,
        );

        $this->assertInstanceOf(assAnswerErrorText::class, $instance);
    }

    public function test_instantiateObjectFullHasCorrectValues(): void
    {
        $instance = new assAnswerErrorText(
            'errortext',
            'correcttext',
            0.01,
            21,
        );

        $this->assertInstanceOf(assAnswerErrorText::class, $instance);
        $this->assertEquals('errortext', $instance->getTextWrong());
        $this->assertEquals('correcttext', $instance->getTextCorrect());
        $this->assertEquals(0.01, $instance->getPoints());
        $this->assertEquals(21, $instance->getPosition());
        $this->assertEquals(1, $instance->getLength());
    }

    public function test_withPoints_valid(): void
    {
        $instance = new assAnswerErrorText('errortext');
        $expected = 0.01;

        $instance_with_points = $instance->withPoints($expected);
        $actual = $instance_with_points->getPoints();

        $this->assertEquals($actual, $expected);
    }

    public function test_withPosition_valid(): void
    {
        $instance = new assAnswerErrorText('errortext');
        $expected = 21;

        $instance_with_position = $instance->withPosition($expected);
        $actual = $instance_with_position->getPosition();

        $this->assertEquals($actual, $expected);
    }
}
