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
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\Data\Chart\Dataset;
use ILIAS\Data\Dimension\CardinalDimension;
use ILIAS\Data\Dimension\RangeDimension;

class Bar implements C\Chart\Bar\Bar
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected const ALLOWED_POSITIONS = ["top", "bottom", "left", "right"];

    protected string $title;
    protected Dataset $dataset;
    /**
     * @var BarConfig[]
     */
    protected array $bar_configs;
    protected bool $title_visible = true;
    protected bool $legend_visible = true;
    protected string $legend_position = "top";
    protected bool $tooltips_visible = true;

    public function __construct(string $title, Dataset $dataset, array $bar_configs = [])
    {
        $this->title = $title;
        if ($dataset->isEmpty()) {
            throw new \LogicException(
                "Dataset must not be empty."
            );
        } else {
            foreach ($dataset->getDimensions() as $dimension) {
                if (!$dimension instanceof CardinalDimension &&
                    !$dimension instanceof RangeDimension
                ) {
                    throw new \InvalidArgumentException(
                        "Expected parameter to be a CardinalDimension or RangeDimension."
                    );
                }
            }
            $this->dataset = $dataset;
        }
        $this->bar_configs = $bar_configs;
    }

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withDataset(Dataset $dataset) : self
    {
        $clone = clone $this;
        $clone->dataset = $dataset;
        return $clone;
    }

    public function getDataset() : Dataset
    {
        return $this->dataset;
    }

    public function withBarConfigs(array $bar_configs) : self
    {
        $clone = clone $this;
        $clone->bar_configs = $bar_configs;
        return $clone;
    }

    public function getBarConfigs() : array
    {
        return $this->bar_configs;
    }

    public function withTitleVisible(bool $title_visible) : self
    {
        $clone = clone $this;
        $clone->title_visible = $title_visible;
        return $clone;
    }

    public function isTitleVisible() : bool
    {
        return $this->title_visible;
    }

    public function withLegendVisible(bool $legend_visible) : self
    {
        $clone = clone $this;
        $clone->legend_visible = $legend_visible;
        return $clone;
    }

    public function isLegendVisible() : bool
    {
        return $this->legend_visible;
    }

    public function withLegendPosition(string $legend_position) : self
    {
        if (!in_array($legend_position, self::ALLOWED_POSITIONS)) {
            throw new \InvalidArgumentException(
                "Position must be 'bottom', 'top', 'left' or 'right'."
            );
        }
        $clone = clone $this;
        $clone->legend_position = $legend_position;
        return $clone;
    }

    public function getLegendPosition() : string
    {
        return $this->legend_position;
    }

    public function withTooltipsVisible(bool $tooltips_visible) : self
    {
        $clone = clone $this;
        $clone->tooltips_visible = $tooltips_visible;
        return $clone;
    }

    public function isTooltipsVisible() : bool
    {
        return $this->tooltips_visible;
    }
}
