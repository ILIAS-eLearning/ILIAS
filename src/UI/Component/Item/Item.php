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
 *********************************************************************/
 
namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;

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
     * @param array<string,string|Shy|Icon> $properties Label => Content
     */
    public function withProperties(array $properties) : Item;

    /**
     * Get the properties of the appointment.
     * @return array<string,string|Shy|Icon>		Title => Content
     */
    public function getProperties() : array;
}
