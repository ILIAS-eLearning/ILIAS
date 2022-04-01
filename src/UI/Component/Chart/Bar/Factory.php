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

use ILIAS\Data\Chart\Dataset;
use ILIAS\UI\Component\Chart\Bar\BarConfig;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Vertical Bar Charts work well with visualizing statistics with rather many
     *      value labels and rather few meassurement items.
     *   composition: >
     *      Vertical Bar Charts have one x-axis, which shows the messeaurement
     *      item labels and one y-axis, which shows the value labels. By default,
     *      value labels are numerical, but can be customized to be textual. The bars
     *      in Vertical Bar Charts run from the bottom up for positive values and
     *      from top to bottom for negative values.
     *   rivals:
     *     Horizontal Bar Charts: >
     *       Horizontal Bar Charts work well with visualizing statistics with rather few
     *       value labels and rather many meassurement items.
     * ---
     * @param string      $title        Title which is shown above the chart. Also used as aria-label.
     * @param Dataset     $dataset      Dataset with points for each defined Dimension. Will be shown as bars in the chart.
     * @param BarConfig[] $bar_configs  Configurations for Bars for each defined Dimension
     * @return \ILIAS\UI\Component\Chart\Bar\Vertical
     */
    public function vertical(
        string $title,
        Dataset $dataset,
        array $bar_configs = []
    ) : Vertical;

    /**
     * ---
     * description:
     *   purpose: >
     *      Horizontal Bar Charts work well with visualizing statistics with rather few
     *      value labels and rather many meassurement items.
     *   composition: >
     *      Horizontal Bar Charts have one y-axis, which shows the messeaurement
     *      item labels and one x-axis, which shows the value labels. By default,
     *      value labels are numerical, but can be customized to be textual. The bars
     *      in Horizontal Bar Charts run from left to right for positive values and
     *      from right to left for negative values.
     *   rivals:
     *     Vertical Bar Charts: >
     *       Vertical Bar Charts work well with visualizing statistics with rather many
     *       value labels and rather few meassurement items.
     * ---
     * @param string      $title        Title which is shown above the chart. Also used as aria-label.
     * @param Dataset     $dataset      Dataset with points for each defined Dimension. Will be shown as bars in the chart.
     * @param BarConfig[] $bar_configs  Configurations for Bars for each defined Dimension
     * @return \ILIAS\UI\Component\Chart\Bar\Horizontal
     */
    public function horizontal(
        string $title,
        Dataset $dataset,
        array $bar_configs = []
    ) : Horizontal;
}
