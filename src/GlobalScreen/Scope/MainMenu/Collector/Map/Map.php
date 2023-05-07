<?php

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

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use Iterator;

/**
 * Class Map
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class Map implements Filterable, Walkable
{
    /**
     * @var \ArrayObject
     */
    private $raw;
    /**
     * @var Closure[]
     */
    private $filters = [];
    /**
     * @var \ArrayObject
     */
    private $filtered;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory
     */
    private $factory;

    /**
     * Tree constructor.
     */
    public function __construct(MainMenuItemFactory $factory)
    {
        $this->raw = new ArrayObject();
        $this->factory = $factory;
    }

    private function getSorter() : Closure
    {
        return function (isItem $item_one, isItem $item_two) : int {
            return $item_one->getPosition() - $item_two->getPosition();
        };
    }

    /**
     * @param isItem $item
     */
    public function add(isItem $item) : void
    {
        $serialize = $item->getProviderIdentification()->serialize();
        if (0 < strlen($serialize)) {
            $this->raw[$serialize] = $item;
        }
    }

    /**
     * @param isItem ...$items
     */
    public function addMultiple(isItem ...$items) : void
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * @param IdentificationInterface $identification
     * @return isItem
     */
    public function getSingleItemFromRaw(IdentificationInterface $identification) : isItem
    {
        if ($this->raw->offsetExists($identification->serialize())) {
            $item = $this->raw->offsetGet($identification->serialize());

            return $item ?? $this->getLostItem($identification);
        }
        return $this->getLostItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return isItem
     */
    public function getSingleItemFromFilter(IdentificationInterface $identification) : isItem
    {
        $this->applyFilters();

        if ($this->filtered->offsetExists($identification->serialize())) {
            $item = $this->filtered->offsetGet($identification->serialize());
        }

        return $item ?? $this->getLostItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     */
    public function remove(IdentificationInterface $identification) : void
    {
        $this->raw->offsetUnset($identification->serialize());
    }

    /**
     * @param IdentificationInterface $identification
     * @return bool
     */
    public function existsInFilter(IdentificationInterface $identification) : bool
    {
        $this->applyFilters();

        return $this->filtered->offsetExists($identification->serialize());
    }

    public function has() : bool
    {
        return $this->raw->count() > 0;
    }


    private function applyFilters() : void
    {
        if (!isset($this->filtered)) {
            $this->filtered = new ArrayObject($this->raw->getArrayCopy());
        }
        if (count($this->filters) > 0) {
            if (!isset($this->filtered)) {
                $filter_copy = $this->raw->getArrayCopy();
            } else {
                $filter_copy = $this->filtered->getArrayCopy();
            }
            foreach ($this->filters as $filter) {
                $filter_copy = array_filter($filter_copy, $filter ?? function ($v, $k) : bool {
                    return !empty($v);
                }, $filter === null ? ARRAY_FILTER_USE_BOTH : 0);
            }
            $this->filtered->exchangeArray($filter_copy);
            $this->filters = [];
        }
    }

    /**
     * @return \Generator|isItem[]
     */
    public function getAllFromRaw() : \Generator
    {
        yield from $this->raw;
    }

    /**
     * @return \Generator|isItem[]
     */
    public function getAllFromFilter() : \Generator
    {
        $this->applyFilters();

        yield from $this->filtered;
    }

    /**
     * @inheritDoc
     */
    public function walk(Closure $c) : void
    {
        $this->applyFilters();
        $to_walk = (array) $this->filtered->getArrayCopy();
        array_walk($to_walk, $c);
        $this->filtered = new ArrayObject($to_walk);
    }

    /**
     * @inheritDoc
     */
    public function filter(Closure $c) : void
    {
        $this->filters[] = $c;
    }

    public function sort() : void
    {
        $this->applyFilters();

        $this->filtered->uasort($this->getSorter());

        $replace_children_sorted = function (isItem &$item) {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                uasort($children, $this->getSorter());
                $item = $item->withChildren($children);
            }
        };
        $this->walk($replace_children_sorted);
    }

    private function getLostItem(IdentificationInterface $identification) : Lost
    {
        return $this->factory->custom(Lost::class, new NullIdentification($identification))
                             ->withAlwaysAvailable(true)
                             ->withVisibilityCallable(
                                 function () : bool {
                                     return false;
                                 }
                             )->withTitle('Lost');
    }
}
