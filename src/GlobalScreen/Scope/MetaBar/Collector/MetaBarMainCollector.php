<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use Closure;
use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Collector\ItemCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class MetaBarMainCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector extends AbstractBaseCollector implements ItemCollector
{

    /**
     * @var StaticMetaBarProvider[]
     */
    private $providers = [];
    /**
     * @var isItem[]
     */
    private $items = [];


    /**
     * MetaBarMainCollector constructor.
     *
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }


    public function collectStructure() : void
    {
        $items_to_merge = [];
        foreach ($this->providers as $provider) {
            $items_to_merge[] = $provider->getMetaBarItems();
        }
        $this->items = array_merge([], ...$items_to_merge);
    }


    public function filterItemsByVisibilty(bool $async_only = false) : void
    {
        $this->items = array_filter($this->items, $this->getVisibleFilter());
    }


    public function prepareItemsForUIRepresentation() : void
    {
        // TODO: Implement prepareItemsForUIRepresentation() method.
    }

    public function cleanupItemsForUIRepresentation() : void
    {
        // TODO: Implement filterItemsByVisibilty() method.
    }

    public function sortItemsForUIRepresentation() : void
    {
        $this->sortItems($this->items);
        array_walk($this->items, $this->getChildSorter());
    }


    /**
     * @return \Generator
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        yield from $this->items;
    }

    
    public function hasItems() : bool
    {
        return count($this->items) > 0;
    }
    
    public function hasVisibleItems() : bool
    {
        return $this->hasItems();
    }
    
    private function sortItems(&$items)
    {
        usort($items, $this->getItemSorter());
    }


    /**
     * @return Closure
     */
    private function getItemSorter() : Closure
    {
        return function (isItem &$a, isItem &$b) {
            return $a->getPosition() > $b->getPosition();
        };
    }


    /**
     * @return Closure
     */
    private function getChildSorter() : Closure
    {
        return function (isItem &$item) {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                $this->sortItems($children);
                $item = $item->withChildren($children);
            }
        };
    }


    /**
     * @return Closure
     */
    protected function getVisibleFilter() : Closure
    {
        return static function (isItem $item) {
            return ($item->isAvailable() && $item->isVisible());
        };
    }
}
