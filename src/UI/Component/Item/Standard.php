<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Interface Standard Item
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Standard extends Item
{
    /**
     * Set a color
     * @param \ILIAS\Data\Color $a_color color
     * @return Item
     */
    public function withColor(\ILIAS\Data\Color $a_color);

    /**
     * @return \ILIAS\Data\Color color
     */
    public function getColor();

    /**
     * Set image as lead
     * @param \ILIAS\UI\Component\Image\Image $image lead image
     * @return Item
     */
    public function withLeadImage(\ILIAS\UI\Component\Image\Image $image);

    /**
     * Set icon as lead
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon lead icon
     * @return Icon
     */
    public function withLeadIcon(\ILIAS\UI\Component\Symbol\Icon\Icon $icon);

    /**
     * Set image as lead
     * @param string $text lead text
     * @return Item
     */
    public function withLeadText($text);

    /**
     * Reset lead to null
     * @return Item
     */
    public function withNoLead();

    /**
     * @return null|string|\ILIAS\UI\Component\Image\Image|\ILIAS\UI\Component\Symbol\Icon\Icon
     */
    public function getLead();
}