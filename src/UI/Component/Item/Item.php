<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>, Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Common interface to all items.
 */
interface Item extends \ILIAS\UI\Component\Component
{
    /**
     * Gets the title of the item
     *
     * @return string|\ILIAS\UI\Component\Button\Shy
     */
    public function getTitle();

    /**
     * Create a new item with an attached description.
     * @param string $description
     * @return Item
     */
    public function withDescription($description);

    /**
     * Get the description of the item.
     * @return string
     */
    public function getDescription();

    /**
     * Get a new item with the given properties as key-value pairs.
     *
     * The key is holding the title and the value is holding the content of the
     * specific data set.
     *
     * @param array<string,string|\ILIAS\UI\Component\Button\Shy> $properties Label => Content
     * @return self
     */
    public function withProperties(array $properties);

    /**
     * Get the properties of the appointment.
     *
     * @return array<string,string|\ILIAS\UI\Component\Button\Shy>		Title => Content
     */
    public function getProperties();

    /**
     * Create a new appointment item with a set of actions to perform on it.
     *
     * @param \ILIAS\UI\Component\Dropdown\Standard $actions
     * @return Item
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions);

    /**
     * Get the actions of the item.
     *
     * @return \ILIAS\UI\Component\Dropdown\Standard
     */
    public function getActions();

    /**
     * Set a color
     *
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
     *
     * @param \ILIAS\UI\Component\Image\Image $image lead image
     * @return Item
     */
    public function withLeadImage(\ILIAS\UI\Component\Image\Image $image);

    /**
     * Set icon as lead
     *
     * @param \ILIAS\UI\Component\Icon\Icon $icon lead icon
     * @return Icon
     */
    public function withLeadIcon(\ILIAS\UI\Component\Icon\Icon $icon);

    /**
     * Set image as lead
     *
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
     * @return null|string|\ILIAS\UI\Component\Image\Image|\ILIAS\UI\Component\Icon\Icon
     */
    public function getLead();
}
