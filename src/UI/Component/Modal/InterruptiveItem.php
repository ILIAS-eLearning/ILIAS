<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Image\Image;

/**
 * Interface InterruptiveItem
 *
 * Represents a item to be displayed inside an interruptive modal
 */
interface InterruptiveItem
{

    /**
     * Return an ID of the item
     *
     * @return string
     */
    public function getId();


    /**
     * Get the title of the item
     *
     * @return string
     */
    public function getTitle();


    /**
     * Get the description of a title
     *
     * @return string
     */
    public function getDescription();


    /**
     * Get the icon of the item
     *
     * @return Image
     */
    public function getIcon();
}
