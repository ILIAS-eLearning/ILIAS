<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use Closure;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class MetaBarMainCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector
{

    /**
     * @var StaticMetaBarProvider[]
     */
    private $providers = [];


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
     * @return isItem[]
     */
    public function getStackedItems() : array
    {
        $items = [];
        foreach ($this->providers as $provider) {
            $items = array_merge($items, $provider->getMetaBarItems());
        }

        $this->sortItems($items);

        array_walk($items, $this->getChildSorter());

        $items = array_filter($items, $this->getVisibleFilter());

        return $items;
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
