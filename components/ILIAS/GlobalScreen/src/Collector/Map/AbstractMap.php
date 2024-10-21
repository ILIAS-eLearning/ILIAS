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

namespace ILIAS\GlobalScreen\Collector\Map;

use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\isGlobalScreenItem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
abstract class AbstractMap implements Filterable, Walkable
{
    protected ArrayObject $raw;
    /**
     * @var Closure[]
     */
    protected array $filters = [];
    protected ArrayObject $filtered;

    public function __construct()
    {
        $this->raw = new ArrayObject();
    }

    protected function getTitleSorter(): Closure
    {
        return static fn(isGlobalScreenItem $item_one, isGlobalScreenItem $item_two): int => 0;
    }

    protected function getPositionSorter(): Closure
    {
        return static fn(isGlobalScreenItem $item_one, isGlobalScreenItem $item_two): int => 0;
    }

    public function add(isGlobalScreenItem $item): void
    {
        $serialize = $item->getProviderIdentification()->serialize();
        if (0 < strlen($serialize)) {
            $this->raw[$serialize] = $item;
        }
    }

    public function addMultiple(isGlobalScreenItem ...$items): void
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function getSingleItemFromRaw(IdentificationInterface $identification): isGlobalScreenItem
    {
        if ($this->raw->offsetExists($identification->serialize())) {
            $item = $this->raw->offsetGet($identification->serialize());

            return $item ?? $this->getLostItem($identification);
        }
        return $this->getLostItem($identification);
    }

    public function getSingleItemFromFilter(IdentificationInterface $identification): isGlobalScreenItem
    {
        $this->applyFilters();

        if ($this->filtered->offsetExists($identification->serialize())) {
            $item = $this->filtered->offsetGet($identification->serialize());
        }

        return $item ?? $this->getLostItem($identification);
    }

    public function remove(IdentificationInterface $identification): void
    {
        $this->raw->offsetUnset($identification->serialize());
    }

    public function existsInFilter(IdentificationInterface $identification): bool
    {
        $this->applyFilters();

        return $this->filtered->offsetExists($identification->serialize());
    }

    public function has(): bool
    {
        return $this->raw->count() > 0;
    }

    protected function applyFilters(): void
    {
        if (!isset($this->filtered)) {
            $this->filtered = new ArrayObject($this->raw->getArrayCopy());
        }
        if ($this->filters !== []) {
            $filter_copy = isset($this->filtered) ? $this->filtered->getArrayCopy() : $this->raw->getArrayCopy();
            foreach ($this->filters as $filter) {
                $filter_copy = array_filter($filter_copy, $filter);
            }
            $this->filtered->exchangeArray($filter_copy);
            $this->filters = [];
        }
    }

    /**
     * @return \Generator|isGlobalScreenItem[]
     */
    public function getAllFromRaw(): \Generator
    {
        yield from $this->raw;
    }

    /**
     * @return \Generator|isGlobalScreenItem[]
     */
    public function getAllFromFilter(): \Generator
    {
        $this->applyFilters();

        yield from $this->filtered;
    }

    public function walk(Closure $c): void
    {
        $this->applyFilters();
        $to_walk = (array) $this->filtered->getArrayCopy();
        array_walk($to_walk, $c);
        $this->filtered = new ArrayObject($to_walk);
    }

    public function filter(Closure $c): void
    {
        $this->filters[] = $c;
    }

    public function sort(): void
    {
        $this->applyFilters();

        $this->filtered->uasort($this->getTitleSorter());
        $this->filtered->uasort($this->getPositionSorter());
    }



    protected function getLostItem(IdentificationInterface $identification): ?isGlobalScreenItem
    {
        return null;
    }
}
