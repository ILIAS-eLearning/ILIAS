<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\Data\Color;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Player\Audio;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

class Standard extends Item implements C\Item\Standard
{
    protected ?Color $color = null;

    /**
     * @var null|string|Image|Avatar
     */
    protected $lead = null;
    protected ?C\Chart\ProgressMeter\ProgressMeter $chart = null;
    protected ?Audio $audio = null;

    /**
     * @inheritdoc
     */
    public function withColor(Color $color): C\Item\Standard
    {
        $clone = clone $this;
        $clone->color = $color;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getColor(): ?Color
    {
        return $this->color;
    }

    /**
     * @inheritdoc
     */
    public function withLeadImage(Image $image): C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $image;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadAvatar(Avatar $avatar): C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $avatar;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAudioPlayer(Audio $audio): C\Item\Standard
    {
        $clone = clone $this;
        $clone->audio = $audio;
        return $clone;
    }

    public function getAudioPlayer(): ?Audio
    {
        return $this->audio;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(Icon $icon): C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadText(string $text): C\Item\Standard
    {
        $clone = clone $this;
        $clone->lead = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNoLead(): C\Item\Standard
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
    public function withProgress(C\Chart\ProgressMeter\ProgressMeter $chart): C\Item\Standard
    {
        $clone = clone $this;
        $clone->chart = $chart;
        return $clone;
    }

    public function getProgress(): ?C\Chart\ProgressMeter\ProgressMeter
    {
        return $this->chart;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions): C\Item\Standard
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions(): ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
