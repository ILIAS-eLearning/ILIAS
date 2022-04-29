<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Image\Image;
use \ILIAS\UI\Component\Player\Audio;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\Data\Color;
use ILIAS\UI\Component\Dropdown\Standard as DropdownStandard;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

/**
 * Interface Standard Item
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Standard extends Item
{
    /**
     * Set a color
     */
    public function withColor(Color $color) : Standard;

    /**
     * Return the given color
     */
    public function getColor() : ?Color ;

    /**
     * Set image as lead
     */
    public function withLeadImage(Image $image) : Standard;

    /**
     * Set audio player
     */
    public function withAudioPlayer(Audio $audio) : Standard;

    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $icon) : Standard;

    /**
     * Set avatar as lead
     */
    public function withLeadAvatar(Avatar $avatar) : Standard;

    /**
     * Set image as lead
     */
    public function withLeadText(string $text) : Standard;

    /**
     * Reset lead to null
     */
    public function withNoLead() : Standard;

    /**
     * @return null|string|Image|Icon|Avatar
     */
    public function getLead();

    public function getAudioPlayer() : ?Audio;

    /**
     * Set progress meter chart
     */
    public function withProgress(ProgressMeter $chart) : Standard;

    public function getProgress() : ?ProgressMeter;

    /**
     * Create a new appointment item with a set of actions to perform on it.
     */
    public function withActions(DropdownStandard $actions) : Standard;

    /**
     * Get the actions of the item.
     */
    public function getActions() : ?DropdownStandard;
}
