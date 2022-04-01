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
class XAxis extends Axis
{
    protected const ALLOWED_POSITIONS = ["bottom", "top"];

    protected string $abbreviation = "x";
    protected string $position = "bottom";

    public function getAbbreviation() : string
    {
        return $this->abbreviation;
    }

    /**
     * Should the x-axis be displayed at the bottom or at the top? Default is bottom.
     *
     * @param string $position "bottom" or "top"
     * @return $this
     */
    public function withPosition(string $position) : self
    {
        if (!in_array($position, self::ALLOWED_POSITIONS)) {
            throw new \InvalidArgumentException(
                "Position must be 'bottom' or 'top'."
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
