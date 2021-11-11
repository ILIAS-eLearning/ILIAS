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

interface Horizontal extends Bar
{
    public function getIndexAxis() : string;

    /**
     * Replace the labels on the y-axis, which were defined at creation, with new ones.
     *
     * @param string[] $y_labels
     * @return Horizontal
     */
    public function withYLabels(array $y_labels) : Horizontal;

    /**
     * @return string[]
     */
    public function getYLabels() : array;

    /**
     * Replace the labels on the x-axis, which are numerical by default, with new custom (textual) ones.
     *
     * @param string[] $x_labels
     * @return Horizontal
     */
    public function withXLabels(array $x_labels) : Horizontal;

    /**
     * @return string[]
     */
    public function getXLabels() : array;

    /**
     * Add a new dataset (type of formation) to the chart and reset(!) all previously added datasets.
     *
     * @param string        $label     Label of this dataset
     * @param int[]|array   $bars      Data of this dataset in numbers, can also be ranges (e.g. [[0.9, 1.1], [2,4]]
     * @param string        $color     Color of this dataset in HEX or RGBA color code
     * @param float         $bar_size  Relative width of the bars for this dataset, default is 1.0
     * @param string[]|null $tooltips  Custom values for tooltips. Array must have same size as used bars. If not
     *                                 defined, labels of x-axis are used by default.
     * @param string|null   $y_axis_id Id of y-axis which should be used for this dataset if multiple y-axes are used
     * @return Horizontal
     */
    public function withData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        ?string $y_axis_id = null
    ) : Horizontal;

    /**
     * Add a new dataset (type of formation) to the chart in addition to previously added datasets.
     *
     * @param string        $label     Label of this dataset
     * @param int[]|array   $bars      Data of this dataset in numbers. Can also be ranges, e.g. [[0.9, 1.1], [2,4]].
     * @param string        $color     Color of this dataset in HEX or RGBA color code
     * @param float         $bar_size  Relative width of the bars for this dataset. Default is 1.0.
     * @param string[]|null $tooltips  Custom values for tooltips. Array must have same size as used bars. If not
     *                                 defined, labels of x-axis are used by default.
     * @param string|null   $y_axis_id Id of y-axis which should be used for this dataset if multiple y-axes are used
     * @return Horizontal
     */
    public function withAdditionalData(
        string $label,
        array $bars,
        string $color,
        float $bar_size = 1.0,
        ?array $tooltips = null,
        ?string $y_axis_id = null
    ) : Horizontal;

    /**
     * Create one or more custom y-axes.
     *
     * @param bool   $is_displayed Should the y-axis be displayed?
     * @param string $position     "left" or "right". Default is "left".
     * @param string $id           Id of the y-axis if multiple y-axes should be used. If not defined,
     *                             default id "y" is used.
     * @return Horizontal
     */
    public function withCustomYAxis(
        bool $is_displayed,
        string $position = "left",
        string $id = "y"
    ) : Horizontal;

    /**
     * Reset all y-axes and only use the one default y-axis.
     */
    public function withResetYAxes() : Horizontal;

    public function getYAxes() : array;

    /**
     * Customize the x-axis.
     *
     * @param bool     $is_displayed  Should the x-axis be displayed?
     * @param string   $position      "bottom" or "top". Default is "bottom".
     * @param float    $step_size     Step size between each label on the x-axis. Only relevant if labels are numeric.
     *                                Default is 1.0.
     * @param bool     $begin_at_zero If true, bars start always at x=0. If false, bars start at the lowest number
     *                                of a dataset. Default is true.
     * @param int|null $min           Numeric label values below this number will not be shown on the x-axis. If not
     *                                defined, the chart determines the minimum automatically based on the datasets.
     * @param int|null $max           Numeric label values above this number will not be shown on the x-axis. If not
     *                                defined, the chart determines the maximum automatically based on the datasets.
     * @return Horizontal
     */
    public function withCustomXAxis(
        bool $is_displayed,
        string $position = "bottom",
        float $step_size = 1.0,
        bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : Horizontal;

    /**
     * Reset the x-axis to default.
     */
    public function withResetXAxis() : Horizontal;

    public function getXAxis() : array;
}
