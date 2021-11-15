<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Image\Image;

/**
 * Interface InterruptiveItem
 *
 * Represents an item to be displayed inside an interruptive modal
 */
interface InterruptiveItem
{
    /**
     * Return an ID of the item
     */
    public function getId() : string;

    /**
     * Get the title of the item
     */
    public function getTitle() : string;

    /**
     * Get the description of a title
     */
    public function getDescription() : string;

    /**
     * Get the icon of the item
     */
    public function getIcon() : ?Image;
}
