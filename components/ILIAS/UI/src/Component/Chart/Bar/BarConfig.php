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

namespace ILIAS\UI\Component\Chart\Bar;

use ILIAS\Data\Color;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class BarConfig
{
    protected ?Color $color = null;
    protected ?float $size = null;
    protected string $stack_group = "Stack 0";

    public function __construct()
    {
    }

    public function withColor(Color $color): self
    {
        $clone = clone $this;
        $clone->color = $color;
        return $clone;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    /**
     * Set a relative width for the bar. The chart library's default width is 1.0 (100%) to make use of
     * the maximum available space for the bars within the chart . A number less than 1.0 will make the bar appear
     * thinner, a number greater than 1.0 will make it appear thicker. Please be aware, that using multiple thicker
     * bars at the same time will result in overlapping.
     */
    public function withRelativeWidth(float $relative_width): self
    {
        $clone = clone $this;
        $clone->size = $relative_width;
        return $clone;
    }

    public function getRelativeWidth(): ?float
    {
        return $this->size;
    }

    /**
     * Datasets can be divided into multiple stacks by giving them a group. Default is "Stack 0".
     * This has no effect when the whole Bar Chart is not stacked, see Bar::withStacked()
     */
    public function withStackGroup(string $group): self
    {
        $clone = clone $this;
        $clone->stack_group = $group;
        return $clone;
    }

    public function getStackGroup(): string
    {
        return $this->stack_group;
    }
}
