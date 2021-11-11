<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 ********************************************************************
 */

namespace ILIAS\UI\Component\Chart\Bar;

interface Vertical extends Bar
{
    public function getIndexAxis() : string;

    /**
     * Replace the given x-labels at creation with new ones.
     *
     * @param string[] $x_labels
     * @return Vertical
     */
    public function withXLabels(array $x_labels) : Vertical;

    /**
     * @return string[]
     */
    public function getXLabels() : array;

    /**
     * Replace the labels on the y-axis, which are numerical by default, with new custom (textual) ones.
     *
     * @param string[] $y_labels
     * @return Vertical
     */
    public function withYLabels(array $y_labels) : Vertical;

    /**
     * @return string[]
     */
    public function getYLabels() : array;

    /**
     * Add a new dataset (type of formation) to the chart and reset(!) all previously added datasets.
     *
     * @param string        $label     Label of this dataset
     * @param int[]|array   $bars      Data of this dataset in numbers, can also be ranges (e.g. [[0.9, 1.1], [2,4]]
     * @param string        $color     Color of this dataset in HEX or RGBA color code
     * @param float         $bar_size  Relative width of the bars for this dataset, default is 1.0
     * @param string[]|null $tooltips  Custom values for tooltips. Array must have same size as used bars. If not
     *                                 defined, labels of y-axis are used by default.
     * @param string|null   $x_axis_id Id of x-axis which should be used for this dataset if multiple x-axes are used
     * @return Vertical
     */
    public function withData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        ?string $x_axis_id = null
    ) : Vertical;

    /**
     * Add a new dataset (type of formation) to the chart in addition to previously added datasets.
     *
     * @param string        $label     Label of this dataset
     * @param int[]|array   $bars      Data of this dataset in numbers. Can also be ranges, e.g. [[0.9, 1.1], [2,4]].
     * @param string        $color     Color of this dataset in HEX or RGBA color code
     * @param float         $bar_size  Relative width of the bars for this dataset. Default is 1.0.
     * @param string[]|null $tooltips  Custom values for tooltips. Array must have same size as used bars. If not
     *                                 defined, labels of y-axis are used by default.
     * @param string|null   $x_axis_id Id of x-axis which should be used for this dataset if multiple x-axes are used
     * @return Vertical
     */
    public function withAdditionalData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        ?string $x_axis_id = null
    ) : Vertical;

    /**
     * Create one or more custom x-axes.
     *
     * @param bool   $is_displayed Should the x-axis be displayed?
     * @param string $position     "bottom" or "top". Default is "bottom".
     * @param string $id           Id of the x-axis if multiple x-axes should be used. If not defined,
     *                             default id "x" is used.
     * @return Vertical
     */
    public function withCustomXAxis(
        bool $is_displayed,
        string $position = "bottom",
        string $id = "x"
    ) : Vertical;

    /**
     * Reset all x-axes and only use the one default x-axis.
     */
    public function withResetXAxes() : Vertical;

    public function getXAxes() : array;

    /**
     * Customize the y-axis.
     *
     * @param bool     $is_displayed  Should the y-axis be displayed?
     * @param string   $position      "left" or "right". Default is "left".
     * @param float    $step_size     Step size between each label on the y-axis. Only relevant if labels are numeric.
     *                                Default is 1.0.
     * @param bool     $begin_at_zero If true, bars start always at y=0. If false, bars start at the lowest number
     *                                of a dataset. Default is true.
     * @param int|null $min           Numeric label values below this number will not be shown on the y-axis. If not
     *                                defined, the chart determines the minimum automatically based on the datasets.
     * @param int|null $max           Numeric label values above this number will not be shown on the y-axis. If not
     *                                defined, the chart determines the maximum automatically based on the datasets.
     * @return Vertical
     */
    public function withCustomYAxis(
        bool $is_displayed,
        string $position = "left",
        float $step_size = 1.0,
        bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : Vertical;

    /**
     * Reset the y-axis to default.
     */
    public function withResetYAxis() : Vertical;

    public function getYAxis() : array;
}
