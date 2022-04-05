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
class YAxis extends Axis
{
    protected const ALLOWED_POSITIONS = ["left", "right"];

    protected string $abbreviation = "y";
    protected string $position = "left";

    public function getAbbreviation() : string
    {
        return $this->abbreviation;
    }

    /**
     * Should the y-axis be displayed left or right? Default is left.
     *
     * @param string $position "left" or "right"
     * @return $this
     */
    public function withPosition(string $position) : self
    {
        if (!in_array($position, self::ALLOWED_POSITIONS)) {
            throw new \InvalidArgumentException(
                "Position must be 'left' or 'right'."
            );
        }
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getPosition() : string
    {
        return $this->position;
    }
}
