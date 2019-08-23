<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use Closure;
use ILIAS\GlobalScreen\Collector\Collector;
use ILIAS\GlobalScreen\Collector\LogicException;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class MetaBarMainCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector implements Collector
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


    /**
     * @inheritDoc
     */
    public function collect() : void
    {
        $items = [];
        foreach ($this->providers as $provider) {
            $items = array_merge($items, $provider->getMetaBarItems());
        }

        $this->sortItems($items);

        array_walk($items, $this->getChildSorter());

        $this->items = array_filter($items, $this->getVisibleFilter());
    }


    /**
     * @return isItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }


    /**
     * @inheritDoc
     */
    public function hasItems() : bool
    {
        return count($this->items) > 0;
    }


    /**
     * @param $items
     */
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
        return function (isItem $item) {
            $b = ($item->isAvailable() && $item->isVisible());

            return $b;
        };
    }
}
