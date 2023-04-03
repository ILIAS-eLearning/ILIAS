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
namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Collector\ItemCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use Generator;

/**
 * Class MetaBarMainCollector
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector extends AbstractBaseCollector implements ItemCollector
{
    /**
     * @var StaticMetaBarProvider[]
     */
    private $providers;
    /**
     * @var isItem[]
     */
    private $items = [];

    /**
     * MetaBarMainCollector constructor.
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
        $this->items = array_filter($this->items, $this->getVisibleFilter() ?? function ($v, $k) : bool {
            return !empty($v);
        }, $this->getVisibleFilter() === null ? ARRAY_FILTER_USE_BOTH : 0);
    }

    public function prepareItemsForUIRepresentation() : void
    {
        // noting to do here
    }

    public function cleanupItemsForUIRepresentation() : void
    {
        // noting to do here
    }

    public function sortItemsForUIRepresentation() : void
    {
        $this->sortItems($this->items);
        array_walk($this->items, $this->getChildSorter());
    }

    /**
     * @return Generator
     */
    public function getItemsForUIRepresentation() : Generator
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

    private function getItemSorter() : callable
    {
        return static function (isItem $a, isItem $b) : int {
            return $a->getPosition() - $b->getPosition();
        };
    }

    private function getChildSorter() : callable
    {
        return function (isItem &$item) : void {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                $this->sortItems($children);
                $item = $item->withChildren($children);
            }
        };
    }

    protected function getVisibleFilter() : callable
    {
        return static function (isItem $item) : bool {
            return ($item->isAvailable() && $item->isVisible());
        };
    }
}
