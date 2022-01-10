<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class Map
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class Map implements Filterable, Walkable
{
    protected ArrayObject $raw;
    /**
     * @var Closure[]
     */
    protected array $filters = [];
    private ArrayObject $filtered;
    
    /**
     * Tree constructor.
     */
    public function __construct()
    {
        $this->raw = new ArrayObject();
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
    
    /**
     * @return bool
     */
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
                $filter_copy = array_filter($filter_copy, $filter);
            }
            $this->filtered->exchangeArray($filter_copy);
            $this->filters = [];
        }
    }
    
    /**
     * @return \Iterator<\ArrayObject>
     */
    public function getAllFromFilter() : \Iterator
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
        $to_walk = (array) $this->filtered;
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
        $sorter = function (isItem $item_one, isItem $item_two) : int {
            /**
             * @var $parent isParent
             */
            if ($item_one instanceof isChild) {
                $parent            = $this->getSingleItemFromFilter($item_one->getParent());
                $position_item_one = ($parent->getPosition() * 1000) + $item_one->getPosition();
            } else {
                $position_item_one = $item_one->getPosition();
            }
            
            if ($item_two instanceof isChild) {
                $parent            = $this->getSingleItemFromFilter($item_two->getParent());
                $position_item_two = ($parent->getPosition() * 1000) + $item_two->getPosition();
            } else {
                $position_item_two = $item_two->getPosition();
            }
            
            return $position_item_one <=> $position_item_two;
        };
        
        $this->filtered->uasort($sorter);
        
        $this->walk(static function (isItem &$item) use ($sorter) : isItem {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                uasort($children, $sorter);
                $item = $item->withChildren($children);
            }
            return $item;
        });
    }
    
    /**
     * @param IdentificationInterface $identification
     * @return Lost
     */
    private function getLostItem(IdentificationInterface $identification) : Lost
    {
        global $DIC;
        
        return $DIC->globalScreen()->mainBar()->custom(Lost::class, new NullIdentification($identification))
                   ->withAlwaysAvailable(true)
                   ->withNonAvailableReason($DIC->ui()->factory()->legacy("{$DIC->language()->txt('mme_lost_item_reason')}"))
                   ->withVisibilityCallable(
                       function () use ($DIC) : bool {
                           return (bool) ($DIC->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                       }
                   )->withTitle($DIC->language()->txt("mme_lost_item_title"));
    }
}
