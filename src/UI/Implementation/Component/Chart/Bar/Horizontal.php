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

namespace ILIAS\UI\Implementation\Component\Chart\Bar;

use ILIAS\UI\Component as C;

class Horizontal extends Bar implements C\Chart\Bar\Horizontal
{
    protected array $x_axis = [
        "axis" => self::AXIS_X,
        "type" => self::TYPE_LINEAR,
        "display" => true,
        "position" => self::POSITION_BOTTOM,
        "ticks" => [
            "callback" => null,
            "stepSize" => 1.0
            ]
    ];

    public function getIndexAxis() : string
    {
        return self::AXIS_Y;
    }

    public function withCustomXAxis(
        bool $is_displayed,
        ?string $position = self::POSITION_BOTTOM,
        ?float $step_size = 1.0,
        ?bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : self {
        $clone = clone $this;
        $clone->x_axis = [
            "axis" => self::AXIS_X,
            "type" => self::TYPE_LINEAR,
            "display" => $is_displayed,
            "position" => $position,
            "beginAtZero" => $begin_at_zero,
            "ticks" => [
                "callback" => null,
                "stepSize" => $step_size
            ]
        ];
        if ($min !== null) {
            $clone->x_axis["min"] = $min;
        }
        if ($max !== null) {
            $clone->x_axis["max"] = $max;
        }
        return $clone;
    }

    public function withResetXAxis() : self
    {
        $clone = clone $this;
        $clone->x_axis = [
            "axis" => self::AXIS_X,
            "type" => self::TYPE_LINEAR,
            "display" => true,
            "position" => self::POSITION_BOTTOM,
            "ticks" => [
                "callback" => null,
            ]
        ];
        return $clone;
    }

    public function getXAxis() : array
    {
        return $this->x_axis;
    }
}
