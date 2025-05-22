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

namespace ILIAS\GlobalScreen\Scope\Footer\Collector;

use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Collector\ItemCollector;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use Generator;
use ILIAS\GlobalScreen\Scope\Footer\Collector\Map\Map;
use ILIAS\GlobalScreen\Scope\Footer\Provider\StaticFooterProvider;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Footer\Factory\canHaveParent;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isGroup;
use ILIAS\GlobalScreen\Scope\Footer\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\Footer\Factory\hasTitle;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FooterMainCollector extends AbstractBaseCollector implements ItemCollector
{
    private readonly Map $map;

    public function __construct(
        private readonly array $providers,
        private readonly ItemInformation $item_information
    ) {
        $this->map = new Map();
    }

    /**
     * @return Generator|StaticFooterProvider[]
     */
    private function getProvidersFromList(): Generator
    {
        yield from $this->providers;
    }

    public function collectStructure(): void
    {
        foreach ($this->getProvidersFromList() as $provider) {
            $this->map->addMultiple(...$provider->getGroups());
            $this->map->addMultiple(...$provider->getEntries());
            $this->map->addMultiple(...$provider->getAdditionalTexts());
            if (($permanent = $provider->getPermanentURI()) !== null) {
                $this->map->add($permanent);
            }
        }
    }

    public function filterItemsByVisibilty(bool $async_only = false): void
    {
        // apply filter
        $this->map->filter(fn(isItem $item): bool => $item->isAvailable() && $item->isVisible());
    }

    public function prepareItemsForUIRepresentation(): void
    {
        $this->map->filter(fn(isItem $item): bool => $this->item_information->isItemActive($item));

        $this->map->walk(function (isItem &$item): isItem {
            if ($item instanceof hasTitle) {
                $item = $this->item_information->customTranslationForUser($item);
            }

            $item = $this->item_information->customPosition($item);

            return $item;
        });

        // Override parent from configuration
        $this->map->walk(function (isItem $item): isItem {
            if ($item instanceof canHaveParent && $item->hasParent()) {
                $parent_id = $this->item_information->getParent($item);

                $parent = $this->map->getSingleItemFromFilter($parent_id); // $item->getParentIdentification()

                if ($parent instanceof isGroup) {
                    $parent->addEntry($item);
                    $this->map->add($parent);
                }
            }

            return $item;
        });
    }

    public function sortItemsForUIRepresentation(): void
    {
        $this->map->sort();
    }

    public function cleanupItemsForUIRepresentation(): void
    {
        // remove empty groups
        $this->map->filter(function (isItem $item): bool {
            if (!$item instanceof isGroup) {
                return true;
            }

            return $item->getEntries() !== [];
        });
    }

    public function getItemsForUIRepresentation(): Generator
    {
        foreach ($this->map->getAllFromFilter() as $item) {
            yield $item;
        }
    }

    public function getRawItems(): Generator
    {
        yield from $this->map->getAllFromFilter();
    }

    public function getRawUnfilteredItems(): Generator
    {
        yield from $this->map->getAllFromRaw();
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

    /**
     * @deprecated
     */
    public function getSingleItemFromFilter(IdentificationInterface $identification): isItem
    {
        return $this->map->getSingleItemFromFilter($identification);
    }

    /**
     * @deprecated
     */
    public function getSingleItemFromRaw(IdentificationInterface $identification): isItem
    {
        return $this->map->getSingleItemFromRaw($identification);
    }

}
