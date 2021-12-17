<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>, Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Dropdown\Standard;

/**
 * Common interface to all items.
 */
interface Item extends Component
{
    /**
     * Gets the title of the item
     * @return string|Shy|Link
     */
    public function getTitle();

    /**
     * Create a new item with an attached description.
     */
    public function withDescription(string $description) : Item;

    /**
     * Get the description of the item.
     */
    public function getDescription() : ?string;

    /**
     * Get a new item with the given properties as key-value pairs.
     * The key is holding the title and the value is holding the content of the
     * specific data set.
     * @param array<string,string|Shy> $properties Label => Content
     */
    public function withProperties(array $properties) : Item;

    /**
     * Get the properties of the appointment.
     * @return array<string,string|Shy>		Title => Content
     */
    public function getProperties() : array;
}
