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
class assAnswerClozeTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');
    }

    public function test_constructorShouldReturnInstance(): void
    {
        $instance = new assAnswerCloze();

        $this->assertInstanceOf(assAnswerCloze::class, $instance);
    }

    public function test_setGetLowerBound(): void
    {
        $instance = new assAnswerCloze('2');

        $expected = '1';
        $instance->setLowerBound($expected);
        $actual = $instance->getLowerBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetLowerBond_GreaterThanAnswerShouldSetAnswertext(): void
    {
        $instance = new assAnswerCloze('2');

        $expected = '2';
        $instance->setLowerBound(4);
        $actual = $instance->getLowerBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetLowerBound_nonNumericShouldSetAnswertext(): void
    {
        $instance = new assAnswerCloze('3');

        $expected = '3';
        $instance->setLowerBound('test');
        $actual = $instance->getLowerBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetUpperBound(): void
    {
        $instance = new assAnswerCloze('1');

        $expected = '3';
        $instance->setUpperBound($expected);
        $actual = $instance->getUpperBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetUpperBound_smallerThanAnswerShouldSetAnswertext(): void
    {
        $instance = new assAnswerCloze('4');

        $expected = '4';
        $instance->setUpperBound(2);
        $actual = $instance->getUpperBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetUpperBound_nonNumericShouldSetAnswertext(): void
    {
        $instance = new assAnswerCloze('4');

        $expected = '4';
        $instance->setUpperBound('test');
        $actual = $instance->getUpperBound();

        $this->assertEquals($expected, $actual);
    }
}
