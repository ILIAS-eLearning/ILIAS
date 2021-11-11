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

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Vertical Bar Charts work well with visualizing statistics with rather many
     *      value labels and rather few meassurement items.
     *   composition: >
     *      Vertical Bar Charts have one or more x-axes, which show the messeaurement
     *      item labels. Vertical Bar Charts have one y-axis, which shows the value
     *      labels. By default, value labels are numerical, but can customized to be
     *      textual. The bars in Vertical Bar Charts run from the bottom up.
     *   rivals:
     *     Horizontal Bar Charts: >
     *       Horizontal Bar Charts work well with visualizing statistics with rather few
     *       value labels and rather many meassurement items.
     * ---
     * @param string   $id         Id of the chart to distinguish multiple charts rendered on one site
     * @param string   $title      Title which is shown above the chart. Also used as aria-label.
     * @param string[] $x_labels   The messeaurement item labels which should be shown on the x-axis
     * @param string   $min_width  Minimum width of the chart on the screen which will not be undercut
     *                             (e.g. 300px, 40vw, 50%)
     * @param string   $min_height Minimum height of the chart on the screen which will not be undercut
     *                             (e.g. 300px, 40vh, 50%)
     * @return Vertical
     */
    public function vertical(
        string $id,
        string $title,
        array $x_labels,
        string $min_width,
        string $min_height
    ) : Vertical;

    /**
     * ---
     * description:
     *   purpose: >
     *      Horizontal Bar Charts work well with visualizing statistics with rather few
     *      value labels and rather many meassurement items.
     *   composition: >
     *      Horizontal Bar Charts have one or more y-axes, which show the messeaurement
     *      item labels. Horizontal Bar Charts have one x-axis, which shows the value
     *      labels. By default, value labels are numerical, but can customized to be
     *      textual. The bars in Horizontal Bar Charts run from left to right.
     *   rivals:
     *     Vertical Bar Charts: >
     *       Vertical Bar Charts work well with visualizing statistics with rather many
     *       value labels and rather few meassurement items.
     * ---
     * @param string   $id         Id of the chart to distinguish multiple charts rendered on one site
     * @param string   $title      Title which is shown above the chart. Also used as aria-label.
     * @param string[] $y_labels   The messeaurement item labels which should be shown on the y-axis
     * @param string   $min_width  Minimum width of the chart on the screen which will not be undercut
     *                             (e.g. 300px, 40vw, 50%)
     * @param string   $min_height Minimum height of the chart on the screen which will not be undercut
     *                             (e.g. 300px, 40vh, 50%)
     * @return Horizontal
     */
    public function horizontal(
        string $id,
        string $title,
        array $y_labels,
        string $min_width,
        string $min_height
    ) : Horizontal;
}
