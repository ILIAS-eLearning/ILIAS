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

class Bar implements C\Chart\Bar\Bar
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected string $id;
    protected string $title;
    protected string $min_width;
    protected string $min_height;
    protected string $width = "";
    protected string $height = "";
    protected bool $responsive = true;
    protected bool $title_visible = true;
    protected bool $legend_visible = true;
    protected string $legend_position = "top";
    protected bool $tooltips_visible = true;
    protected array $data = [];
    protected ?array $tooltips = null;

    public function __construct(string $id, string $title, string $min_width, string $min_height)
    {
        $this->id = $id;
        $this->title = $title;
        $this->min_width = $min_width;
        $this->min_height = $min_height;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function withTitle(string $title) : Bar
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getMinimumWidth() : string
    {
        return $this->min_width;
    }

    public function getMinimumHeight() : string
    {
        return $this->min_height;
    }

    public function withFixedSize(string $width, string $height) : Bar
    {
        $clone = clone $this;
        $clone->width = $width;
        $clone->min_width = $width;
        $clone->height = $height;
        $clone->min_height = $height;
        $clone->responsive = false;
        return $clone;
    }

    public function getWidth() : string
    {
        return $this->width;
    }

    public function getHeight() : string
    {
        return $this->height;
    }

    public function isResponsive() : bool
    {
        return $this->responsive;
    }

    public function withTitleVisible(bool $title_visible) : Bar
    {
        $clone = clone $this;
        $clone->title_visible = $title_visible;
        return $clone;
    }

    public function isTitleVisible() : bool
    {
        return $this->title_visible;
    }

    public function withLegendVisible(bool $legend_visible) : Bar
    {
        $clone = clone $this;
        $clone->legend_visible = $legend_visible;
        return $clone;
    }

    public function isLegendVisible() : bool
    {
        return $this->legend_visible;
    }

    public function withLegendPosition(string $legend_position) : Bar
    {
        $clone = clone $this;
        $clone->legend_position = $legend_position;
        return $clone;
    }

    public function getLegendPosition() : string
    {
        return $this->legend_position;
    }

    public function withTooltipsVisible(bool $tooltips_visible) : Bar
    {
        $clone = clone $this;
        $clone->tooltips_visible = $tooltips_visible;
        return $clone;
    }

    public function isTooltipsVisible() : bool
    {
        return $this->tooltips_visible;
    }

    public function withResetData() : Bar
    {
        $clone = clone $this;
        $clone->data = [];
        return $clone;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function getTooltips() : ?array
    {
        return $this->tooltips;
    }
}
