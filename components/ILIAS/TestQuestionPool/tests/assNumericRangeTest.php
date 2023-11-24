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
class assNumericRangeTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new assNumericRange();

        $this->assertInstanceOf(assNumericRange::class, $instance);
    }

    public function test_setGetLowerLimit_shouldReturnUnchangedLowerLimit(): void
    {
        $instance = new assNumericRange();
        $expected = 1.00;

        $instance->setLowerLimit($expected);
        $actual = $instance->getLowerLimit();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetUpperLimit_shouldReturnUnchangedUpperLimit(): void
    {
        $instance = new assNumericRange();
        $expected = 10.00;

        $instance->setUpperLimit($expected);
        $actual = $instance->getUpperLimit();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetOrder_shouldReturnUnchangedOrder(): void
    {
        $instance = new assNumericRange();
        $expected = 10;

        $instance->setOrder($expected);
        $actual = $instance->getOrder();

        $this->assertEquals($expected, $actual);
    }

    public function test_setPoints_shouldReturnUnchangedPoints(): void
    {
        $instance = new assNumericRange();
        $expected = 10;

        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnTrueIfValueIsContained(): void
    {
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = true;

        $actual = $instance->contains(5.00);

        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnFalseIfValueIsNotContained(): void
    {
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = false;

        $actual = $instance->contains(15.00);

        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnFalseIfValueIsHokum(): void
    {
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = false;

        $actual = $instance->contains('Günther');

        $this->assertEquals($expected, $actual);
    }
}
