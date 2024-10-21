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
use ILIAS\GlobalScreen\Scope\isDecorateable;
use ILIAS\UI\Help\Topic;

/**
 * Class MetaBarMainCollector
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector extends AbstractBaseCollector implements ItemCollector
{
    /**
     * @var isItem[]
     */
    private array $items = [];
    private array $raw_items = [];
    private readonly Map $map;

    private bool $default_topics = false;

    public function __construct(
        private readonly array $providers
    ) {
        $this->map = new Map();
    }

    private function getProvidersFromList(): \Iterator
    {
        yield from $this->providers;
    }


    public function collectStructure(): void
    {

        foreach ($this->getProvidersFromList() as $provider) {
            $this->map->addMultiple(...$provider->getMetaBarItems());
        }
    }

    public function filterItemsByVisibilty(bool $async_only = false): void
    {
        $this->map->filter($this->getVisibleFilter());
    }

    public function getRawItems(): array
    {
        return iterator_to_array($this->map->getAllFromRaw());
    }

    public function prepareItemsForUIRepresentation(): void
    {
        if ($this->default_topics) {
            $add_default_topic = static function (isItem &$item) use (&$add_default_topic): isItem {
                if ($item instanceof isDecorateable) {
                    $item = $item->withTopics(new Topic($item->getProviderIdentification()->getInternalIdentifier()));
                }
                if ($item instanceof isParent) {
                    foreach ($item->getChildren() as $child) {
                        $child = $add_default_topic($child);
                    }
                }

                return $item;
            };

            $this->map->walk($add_default_topic);
        }
    }

    public function cleanupItemsForUIRepresentation(): void
    {
        // noting to do here
    }

    public function sortItemsForUIRepresentation(): void
    {
        $this->map->sort();
    }

    public function getItemsForUIRepresentation(): Generator
    {
        foreach ($this->map->getAllFromFilter() as $item) {
            yield $item;
        }
    }

    public function hasItems(): bool
    {
        return $this->map->has();
    }

    public function hasVisibleItems(): bool
    {
        if (!$this->hasItems()) {
            return false;
        }
        foreach ($this->getItemsForUIRepresentation() as $item) {
            return $item instanceof isItem;
        }
        return false;
    }



    private function getItemSorter(): callable
    {
        return static fn(isItem $a, isItem $b): int => $a->getPosition() - $b->getPosition();
    }

    private function getChildSorter(): callable
    {
        return function (isItem &$item): void {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                $this->sortItems($children);
                $item = $item->withChildren($children);
            }
        };
    }

    protected function getVisibleFilter(): callable
    {
        return static fn(isItem $item): bool => $item->isAvailable() && $item->isVisible();
    }
}
