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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\Data\Chart\Dataset;
use ILIAS\UI\Component\Chart\Bar\BarConfig;

interface Bar extends Component, JavaScriptBindable
{
    /**
     * Replace the given title at creation with a new title.
     */
    public function withTitle(string $title) : self;

    public function getTitle() : string;

    /**
     * Replace the given dataset at creation with a new one.
     */
    public function withDataset(Dataset $dataset) : self;

    /**
     * @return Dataset
     */
    public function getDataset() : Dataset;

    /**
     * Replace the given bars at creation with new ones.
     * @param BarConfig[] $bar_configs
     */
    public function withBarConfigs(array $bar_configs) : self;

    /**
     * @return BarConfig[]
     */
    public function getBarConfigs() : array;

    public function withTitleVisible(bool $title_visible) : self;

    public function isTitleVisible() : bool;

    public function withLegendVisible(bool $legend_visible) : self;

    public function isLegendVisible() : bool;

    /**
     * On which side next to the chart should the legend be placed? Default is top.
     *
     * @param string $legend_position "top", "bottom", "left" or "right"
     * @return Bar
     */
    public function withLegendPosition(string $legend_position) : self;

    public function getLegendPosition() : string;

    /**
     * Should the tooltips pop up when hovering over the bars in the chart?
     */
    public function withTooltipsVisible(bool $tooltips_visible) : self;

    public function isTooltipsVisible() : bool;
}
