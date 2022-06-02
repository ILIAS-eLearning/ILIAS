<?php declare(strict_types=1);

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
 
require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Chart\Bar\XAxis;
use ILIAS\UI\Component\Chart\Bar\YAxis;

/**
 * Test on Bar Configuration implementation.
 */
class AxisTest extends ILIAS_UI_TestBase
{
    public function test_x_abbreviation() : void
    {
        $x_axis = new XAxis();

        $this->assertEquals("x", $x_axis->getAbbreviation());
    }

    public function test_y_abbreviation() : void
    {
        $y_axis = new YAxis();

        $this->assertEquals("y", $y_axis->getAbbreviation());
    }

    public function test_type() : void
    {
        $x_axis = new XAxis();

        $this->assertEquals("linear", $x_axis->getType());

        $y_axis = new YAxis();

        $this->assertEquals("linear", $y_axis->getType());
    }

    public function test_with_displayed() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withDisplayed(false);

        $this->assertEquals(true, $x_axis->isDisplayed());
        $this->assertEquals(false, $x_axis1->isDisplayed());
    }

    public function test_with_step_size() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withStepSize(0.5);

        $this->assertEquals(1.0, $x_axis->getStepSize());
        $this->assertEquals(0.5, $x_axis1->getStepSize());
    }

    public function test_with_begin_at_zero() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withBeginAtZero(false);

        $this->assertEquals(true, $x_axis->isBeginAtZero());
        $this->assertEquals(false, $x_axis1->isBeginAtZero());
    }

    public function test_with_min() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withMinValue(-2);

        $this->assertEquals(null, $x_axis->getMinValue());
        $this->assertEquals(-2, $x_axis1->getMinValue());
    }

    public function test_with_max() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withMaxValue(10);

        $this->assertEquals(null, $x_axis->getMaxValue());
        $this->assertEquals(10, $x_axis1->getMaxValue());
    }

    public function test_x_with_position() : void
    {
        $x_axis = new XAxis();
        $x_axis1 = $x_axis->withPosition("top");

        $this->assertEquals("bottom", $x_axis->getPosition());
        $this->assertEquals("top", $x_axis1->getPosition());
    }

    public function test_x_with_invalid_position() : void
    {
        $x_axis = new XAxis();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Position must be 'bottom' or 'top'.");

        $x_axis = $x_axis->withPosition("left");
    }

    public function test_y_with_position() : void
    {
        $y_axis = new YAxis();
        $y_axis1 = $y_axis->withPosition("right");

        $this->assertEquals("left", $y_axis->getPosition());
        $this->assertEquals("right", $y_axis1->getPosition());
    }

    public function test_y_with_invalid_position() : void
    {
        $y_axis = new YAxis();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Position must be 'left' or 'right'.");

        $y_axis = $y_axis->withPosition("bottom");
    }
}
