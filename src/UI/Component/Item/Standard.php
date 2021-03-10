<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use \ILIAS\UI\Component\Symbol\Icon\Icon;
use \ILIAS\UI\Component\Image\Image;
use \ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use \ILIAS\Data\Color;

/**
 * Interface Standard Item
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Standard extends Item
{
    /**
     * Set a color
     */
    public function withColor(Color $a_color) : Item;

    /**
     * Return the given color
     */
    public function getColor() : ?Color ;

    /**
     * Set image as lead
     */
    public function withLeadImage(Image $image) : Item;

    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $icon) : Item;

    /**
     * Set image as lead
     */
    public function withLeadText(string $text) : Item;

    /**
     * Reset lead to null
     */
    public function withNoLead() : Item;

    /**
     * @return null|string|\ILIAS\UI\Component\Image\Image|\ILIAS\UI\Component\Symbol\Icon\Icon
     */
    public function getLead();

    /**
     * Set progress meter chart
     */
    public function withProgress(ProgressMeter $chart) : Item;

    /**
     * @return null|ProgressMeter
     */
    public function getProgress() : ?ProgressMeter;
}
