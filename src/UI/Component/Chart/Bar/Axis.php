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
 ********************************************************************
 */

namespace ILIAS\UI\Component\Chart\Bar;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
abstract class Axis
{
    protected string $type = "linear";
    protected bool $displayed = true;
    protected float $step_size = 1.0;
    protected bool $begin_at_zero = true;
    protected ?int $min = null;
    protected ?int $max = null;


    abstract public function getAbbreviation() : string;

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Should the axis be displayed? Default is true.
     */
    public function withDisplayed(bool $displayed) : self
    {
        $clone = clone $this;
        $clone->displayed = $displayed;
        return $clone;
    }

    public function isDisplayed() : bool
    {
        return $this->displayed;
    }

    abstract public function withPosition(string $position) : self;

    abstract public function getPosition() : string;

    /**
     * Step size between each label on the x-axis. Only relevant if labels are numeric. Default is 1.0.
     * Values less than 1.0 make the axis more detailed, values greater than 1.0 make it less detailed.
     */
    public function withStepSize(float $step_size) : self
    {
        $clone = clone $this;
        $clone->step_size = $step_size;
        return $clone;
    }

    public function getStepSize() : float
    {
        return $this->step_size;
    }

    /**
     * If true, bars start always at x=0 (Horizontal Bar Chart) or y=0 (Vertical Bar Chart). If false, bars start
     * at the lowest number of a Dataset. Default is true.
     */
    public function withBeginAtZero(bool $begin_at_zero) : self
    {
        $clone = clone $this;
        $clone->begin_at_zero = $begin_at_zero;
        return $clone;
    }

    public function isBeginAtZero() : bool
    {
        return $this->begin_at_zero;
    }

    /**
     * Numeric label values below this number will not be shown on the x-axis (Horizontal Bar Chart) or
     * y-axis (Vertical Bar Chart). If not defined, the chart determines the minimum automatically based on the Dataset.
     */
    public function withMinValue(int $min) : self
    {
        $clone = clone $this;
        $clone->min = $min;
        return $clone;
    }

    public function getMinValue() : ?int
    {
        return $this->min;
    }

    /**
     * Numeric label values above this number will not be shown on the x-axis (Horizontal Bar Chart) or
     * y-axis (Vertical Bar Chart). If not defined, the chart determines the maximum automatically based on the Dataset.
     */
    public function withMaxValue(int $max) : self
    {
        $clone = clone $this;
        $clone->max = $max;
        return $clone;
    }

    public function getMaxValue() : ?int
    {
        return $this->max;
    }
}
