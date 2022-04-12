<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\Data\Color;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Player\Audio;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Standard extends Item implements C\Item\Standard
{
    protected ?Color $color = null;

    /**
     * @var null|string|Image
     */
    protected $lead = null;
    protected ?C\Chart\ProgressMeter\ProgressMeter $chart = null;
    protected ?Audio $audio = null;

    /**
     * @inheritdoc
     */
    public function withColor(Color $color) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->color = $color;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getColor() : ?Color
    {
        return $this->color;
    }

    /**
     * @inheritdoc
     */
    public function withLeadImage(Image $image) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $image;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAudioPlayer(Audio $audio) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->audio = $audio;
        return $clone;
    }

    public function getAudioPlayer() : ?Audio
    {
        return $this->audio;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(Icon $icon) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadText(string $text) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNoLead() : C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = null;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @inheritdoc
     */
    public function withProgress(C\Chart\ProgressMeter\ProgressMeter $chart) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->chart = $chart;
        return $clone;
    }

    public function getProgress() : ?C\Chart\ProgressMeter\ProgressMeter
    {
        return $this->chart;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions) : C\Item\Standard
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
