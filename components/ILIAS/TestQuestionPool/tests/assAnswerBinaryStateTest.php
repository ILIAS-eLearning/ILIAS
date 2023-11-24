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
class assAnswerBinaryStateTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ASS_AnswerBinaryState();

        $this->assertInstanceOf(ASS_AnswerBinaryState::class, $instance);
    }

    public function test_setGetState_shouldReturnUnchangedState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        $instance->setState($expected);
        $actual = $instance->getState();

        $this->assertEquals($expected, $actual);
    }

    public function test_isStateChecked_shouldReturnActualState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        $instance->setState($expected);
        $actual = $instance->isStateChecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_isStateSet_shouldReturnActualState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        $instance->setState($expected);
        $actual = $instance->isStateSet();

        $this->assertEquals($expected, $actual);
    }

    public function test_isStateUnset_shouldReturnActualState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        $instance->setState($expected);
        $actual = !$instance->isStateUnset();

        $this->assertEquals($expected, $actual);
    }

    public function test_isStateUnchecked_shouldReturnActualState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        $instance->setState($expected);
        $actual = !$instance->isStateUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setChecked_shouldAlterState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 0;
        $instance->setState($expected);

        $instance->setChecked();
        $actual = $instance->isStateUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setUnchecked_shouldAlterState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;
        $instance->setState($expected);

        $instance->setUnchecked();
        $actual = $instance->isStateUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setSet_shouldAlterState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 0;
        $instance->setState($expected);

        $instance->setSet();
        $actual = $instance->isStateUnchecked();

        $this->assertEquals($expected, $actual);
    }

    public function test_setUnset_shouldAlterState(): void
    {
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;
        $instance->setState($expected);

        $instance->setUnset();
        $actual = $instance->isStateUnchecked();

        $this->assertEquals($expected, $actual);
    }
}
