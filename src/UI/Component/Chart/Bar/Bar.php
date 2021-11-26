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

interface Bar extends Component, JavaScriptBindable
{
    public function getId() : string;

    /**
     * Replace the given title at creation with a new title.
     */
    public function withTitle(string $title) : Bar;

    public function getTitle() : string;

    public function getMinimumWidth() : string;

    public function getMinimumHeight() : string;

    /**
     * Set a fixed width and height for the chart. Makes the chart also non-responsive to make this work correctly.
     *
     * @param string $width  width and unit (e.g. 300px, 40vw, 50%)
     * @param string $height height and unit (e.g. 300px, 40vh, 50%)
     * @return Bar
     */
    public function withFixedSize(string $width, string $height) : Bar;

    public function getWidth() : string;

    public function getHeight() : string;

    public function isResponsive() : bool;

    public function withTitleVisible(bool $title_visible) : Bar;

    public function isTitleVisible() : bool;

    public function withLegendVisible(bool $legend_visible) : Bar;

    public function isLegendVisible() : bool;

    /**
     * On which side next to the chart should the legend be placed? Default is "top".
     *
     * @param string $legend_position "left", "right", "bottom" or "top"
     * @return Bar
     */
    public function withLegendPosition(string $legend_position) : Bar;

    public function getLegendPosition() : string;

    public function withTooltipsVisible(bool $tooltips_visible) : Bar;

    public function isTooltipsVisible() : bool;

    /**
     * Make the chart empty with no bars.
     */
    public function withResetData() : Bar;

    public function getData() : array;
}
