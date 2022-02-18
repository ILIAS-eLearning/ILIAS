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

class Vertical extends Bar implements C\Chart\Bar\Vertical
{
    protected array $y_axis = [
        "axis" => self::AXIS_Y,
        "type" => self::TYPE_LINEAR,
        "display" => true,
        "position" => self::POSITION_LEFT,
        "ticks" => [
            "callback" => null,
            "stepSize" => 1.0
        ]
    ];

    public function getIndexAxis() : string
    {
        return self::AXIS_X;
    }

    public function withCustomYAxis(
        bool $is_displayed,
        ?string $position = self::POSITION_LEFT,
        ?float $step_size = 1.0,
        ?bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : self {
        $clone = clone $this;
        $clone->y_axis = [
            "axis" => self::AXIS_Y,
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
            $clone->y_axis["min"] = $min;
        }
        if ($max !== null) {
            $clone->y_axis["max"] = $max;
        }
        return $clone;
    }

    public function withResetYAxis() : self
    {
        $clone = clone $this;
        $clone->y_axis = [
            "axis" => self::AXIS_Y,
            "type" => self::TYPE_LINEAR,
            "display" => true,
            "position" => self::POSITION_LEFT,
            "ticks" => [
                "callback" => null,
            ]
        ];
        return $clone;
    }

    public function getYAxis() : array
    {
        return $this->y_axis;
    }
}
