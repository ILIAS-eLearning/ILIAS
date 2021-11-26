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
    protected array $y_labels = [];
    protected array $x_labels = [];
    protected array $y_axes = [];
    protected array $x_axis = [
        "axis" => "x",
        "type" => "linear",
        "display" => "true",
        "position" => "bottom",
        "ticks" => [
            "callback" => null,
            ]
    ];

    public function __construct(string $id, string $title, array $y_labels, string $min_width, string $min_height)
    {
        parent::__construct($id, $title, $min_width, $min_height);
        $this->y_labels = $y_labels;
    }

    public function getIndexAxis() : string
    {
        return "y";
    }

    public function withYLabels(array $y_labels) : Horizontal
    {
        $clone = clone $this;
        $clone->y_labels = $y_labels;
        return $clone;
    }

    public function getYLabels() : array
    {
        return $this->y_labels;
    }

    public function withXLabels(array $x_labels) : Horizontal
    {
        $clone = clone $this;
        $clone->x_labels = $x_labels;
        return $clone;
    }

    public function getXLabels() : array
    {
        return $this->x_labels;
    }

    public function withData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        string $y_axis_id = "y"
    ) : Horizontal {
        if (count($this->getYLabels()) != count($bars)) {
            throw new \ArgumentCountError(
                "Number of labels on y-axis and " . "$" . "bars must be arrays of same size."
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
            "yAxisID" => $y_axis_id
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
        string $y_axis_id = "y"
    ) : Horizontal {
        if (count($this->getYLabels()) != count($bars)) {
            throw new \ArgumentCountError(
                "Number of labels on y-axis and " . "$" . "bars must be arrays of same size."
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
            "yAxisID" => $y_axis_id
        ];
        $clone->tooltips[] = $tooltips;
        return $clone;
    }

    public function withCustomYAxis(
        bool $is_displayed,
        string $position = "left",
        string $id = "y"
    ) : Horizontal {
        $clone = clone $this;
        $clone->y_axes[$id] = [
            "id" => $id,
            "axis" => "y",
            "type" => "category",
            "display" => $is_displayed,
            "position" => $position
        ];
        return $clone;
    }

    public function withResetYAxes() : Horizontal
    {
        $clone = clone $this;
        $clone->y_axes = [];
        return $clone;
    }

    public function getYAxes() : array
    {
        return $this->y_axes;
    }

    public function withCustomXAxis(
        bool $is_displayed,
        string $position = "bottom",
        float $step_size = 1.0,
        bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : Horizontal {
        $clone = clone $this;
        $clone->x_axis = [];
        $clone->x_axis = [
            "axis" => "x",
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
            $clone->x_axis["min"] = $min;
        }
        if ($max !== null) {
            $clone->x_axis["max"] = $max;
        }
        return $clone;
    }

    public function withResetXAxis() : Horizontal
    {
        $clone = clone $this;
        $clone->x_axis = [
            "axis" => "x",
            "type" => "linear",
            "display" => "true",
            "position" => "bottom",
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
