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
 
namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Dropdown;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Common interface to all items.
 */
class Group implements Item\Group
{
    use ComponentHelper;

    protected string $title;

    /**
     * @var Item\Item[]
     */
    protected array $items;
    protected ?Dropdown\Standard $actions = null;

    /**
     * @param Item\Item[] $items
     */
    public function __construct(string $title, array $items)
    {
        $this->title = $title;
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function withActions(Dropdown\Standard $dropdown) : Item\Group
    {
        $clone = clone $this;
        $clone->actions = $dropdown;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?Dropdown\Standard
    {
        return $this->actions;
    }
}
