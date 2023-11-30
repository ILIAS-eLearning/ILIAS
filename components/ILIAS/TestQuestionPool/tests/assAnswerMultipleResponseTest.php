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
class assAnswerMultipleResponseTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        $instance = new ASS_AnswerMultipleResponse();

        $this->assertInstanceOf(ASS_AnswerMultipleResponse::class, $instance);
    }

    public function test_setGetPointsUnchecked(): void
    {
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 1;

        $instance->setPointsUnchecked($expected);
        $actual = $instance->getPointsUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsUnchecked_InvalidPointsBecomeZero(): void
    {
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 0;

        $instance->setPointsUnchecked('Günther');
        $actual = $instance->getPointsUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsChecked(): void
    {
        $instance = new ASS_AnswerMultipleResponse();
        $expected = 2;

        $instance->setPointsChecked($expected);
        $actual = $instance->getPointsChecked();

        $this->assertEquals($expected, $actual);
    }
}
