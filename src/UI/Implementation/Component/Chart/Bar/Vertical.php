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
    protected array $x_labels = [];
    protected array $y_labels = [];
    protected array $x_axes = [];
    protected array $y_axis = [
        "axis" => "y",
        "type" => "linear",
        "display" => "true",
        "position" => "left",
        "ticks" => [
            "callback" => null,
        ]
    ];

    public function __construct(string $id, string $title, array $x_labels, string $min_width, string $min_height)
    {
        parent::__construct($id, $title, $min_width, $min_height);
        $this->x_labels = $x_labels;
    }

    public function getIndexAxis() : string
    {
        return "x";
    }

    public function withXLabels(array $x_labels) : Vertical
    {
        $clone = clone $this;
        $clone->x_labels = $x_labels;
        return $clone;
    }

    public function getXLabels() : array
    {
        return $this->x_labels;
    }

    public function withYLabels(array $y_labels) : Vertical
    {
        $clone = clone $this;
        $clone->y_labels = $y_labels;
        return $clone;
    }

    public function getYLabels() : array
    {
        return $this->y_labels;
    }

    public function withData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        string $x_axis_id = "x"
    ) : Vertical {
        if (count($this->getXLabels()) != count($bars)) {
            throw new \ArgumentCountError(
                "Number of labels on x-axis and " . "$" . "bars must be arrays of same size."
            );
        }
        if (!is_null($tooltips) && count($bars) != count($tooltips)) {
            throw new \ArgumentCountError(
                "$" . "bars and " . "$" . "tooltips must be arrays of same size."
            );
        }
        $clone = clone $this;
        $clone->data = [];
        $clone->data[] = [
            "label" => $label,
            "data" => $bars,
            "backgroundColor" => $color,
            "barPercentage" => $bar_size,
            "xAxisID" => $x_axis_id
        ];
        $clone->tooltips = [];
        $clone->tooltips[] = $tooltips;
        return $clone;
    }

    public function withAdditionalData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        string $x_axis_id = "x"
    ) : Vertical {
        if (count($this->getXLabels()) != count($bars)) {
            throw new \ArgumentCountError(
                "Number of labels on x-axis and " . "$" . "bars must be arrays of same size."
            );
        }
        if (!is_null($tooltips) && count($bars) != count($tooltips)) {
            throw new \ArgumentCountError(
                "$" . "bars and " . "$" . "tooltips must be arrays of same size."
            );
        }
        $clone = clone $this;
        $clone->data[] = [
            "label" => $label,
            "data" => $bars,
            "backgroundColor" => $color,
            "barPercentage" => $bar_size,
            "xAxisID" => $x_axis_id
        ];
        $clone->tooltips[] = $tooltips;
        return $clone;
    }

    public function withCustomXAxis(
        bool $is_displayed,
        string $position = "bottom",
        string $id = "x"
    ) : Vertical {
        $clone = clone $this;
        $clone->x_axes[$id] = [
            "id" => $id,
            "axis" => "x",
            "type" => "category",
            "display" => $is_displayed,
            "position" => $position
        ];
        return $clone;
    }

    public function withResetXAxes() : Vertical
    {
        $clone = clone $this;
        $clone->x_axes = [];
        return $clone;
    }

    public function getXAxes() : array
    {
        return $this->x_axes;
    }

    public function withCustomYAxis(
        bool $is_displayed,
        string $position = "left",
        float $step_size = 1.0,
        bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : Vertical {
        $clone = clone $this;
        $clone->y_axis = [];
        $clone->y_axis = [
            "axis" => "y",
            "type" => "linear",
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

    public function withResetYAxis() : Vertical
    {
        $clone = clone $this;
        $clone->y_axis = [
            "axis" => "y",
            "type" => "linear",
            "display" => "true",
            "position" => "bottom",
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
