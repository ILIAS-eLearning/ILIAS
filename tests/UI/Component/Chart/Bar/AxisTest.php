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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Chart\Bar\XAxis;
use ILIAS\UI\Component\Chart\Bar\YAxis;

/**
 * Test on Bar Configuration implementation.
 */
class AxisTest extends ILIAS_UI_TestBase
{
    public function testXAbbreviation(): void
    {
        $x_axis = new XAxis();

        $this->assertEquals("x", $x_axis->getAbbreviation());
    }

    public function testYAbbreviation(): void
    {
        $y_axis = new YAxis();

        $this->assertEquals("y", $y_axis->getAbbreviation());
    }

    public function testType(): void
    {
        $x_axis = new XAxis();

        $this->assertEquals("linear", $x_axis->getType());

        $y_axis = new YAxis();

        $this->assertEquals("linear", $y_axis->getType());
    }

    public function testWithDisplayed(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withDisplayed(false);

        $this->assertEquals(true, $x_axis->isDisplayed());
        $this->assertEquals(false, $x_axis1->isDisplayed());
    }

    public function testWithStepSize(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withStepSize(0.5);

        $this->assertEquals(1.0, $x_axis->getStepSize());
        $this->assertEquals(0.5, $x_axis1->getStepSize());
    }

    public function testWithBeginAtZero(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withBeginAtZero(false);

        $this->assertEquals(true, $x_axis->isBeginAtZero());
        $this->assertEquals(false, $x_axis1->isBeginAtZero());
    }

    public function testWithMin(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withMinValue(-2);

        $this->assertEquals(null, $x_axis->getMinValue());
        $this->assertEquals(-2, $x_axis1->getMinValue());
    }

    public function testWithMax(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withMaxValue(10);

        $this->assertEquals(null, $x_axis->getMaxValue());
        $this->assertEquals(10, $x_axis1->getMaxValue());
    }

    public function testXWithPosition(): void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withPosition("top");

        $this->assertEquals("bottom", $x_axis->getPosition());
        $this->assertEquals("top", $x_axis1->getPosition());
    }

    public function testXWithInvalidPosition(): void
    {
        $x_axis = new XAxis();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Position must be 'bottom' or 'top'.");

        $x_axis = $x_axis->withPosition("left");
    }

    public function testYWithPosition(): void
    {
        $y_axis = new YAxis();
        $y_axis1 = $y_axis->withPosition("right");

        $this->assertEquals("left", $y_axis->getPosition());
        $this->assertEquals("right", $y_axis1->getPosition());
    }

    public function testYWithInvalidPosition(): void
    {
        $y_axis = new YAxis();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Position must be 'left' or 'right'.");

        $y_axis = $y_axis->withPosition("bottom");
    }
}
