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

/**
 * Immutable container class for Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkItemsContainer extends ilWebLinkBaseItemsContainer
{
    protected int $webr_id;
    /**
     * @var ilWebLinkItem[]
     */
    protected array $items;

    /**
     * @param int               $webr_id
     * @param ilWebLinkItem[]   $items
     */
    public function __construct(int $webr_id, array $items = [])
    {
        $this->webr_id = $webr_id;
        parent::__construct($items);
    }

    /**
     * Sorts the items in this container according to the settings of
     * this web link object.
     */
    public function sort(): self
    {
        $mode = $this->lookupSortMode($this->getWebrId());

        if ($mode == ilContainer::SORT_TITLE) {
            $items_arr = [];

            foreach ($this->getItems() as $item) {
                $link_id = $item->getLinkId();
                $items_arr[$link_id]['title'] = $item->getTitle();
                $items_arr[$link_id]['item'] = $item;
            }

            $items_arr = $this->sortArray(
                $items_arr,
                'title',
                false
            );

            $result = [];
            foreach ($items_arr as $value) {
                $result[] = $value['item'];
            }
            $this->items = $result;
        }

        $sorted = $unsorted = [];
        if ($mode == ilContainer::SORT_MANUAL) {
            $pos = $this->lookupManualPositions($this->getWebrId());
            foreach ($this->getItems() as $item) {
                $link_id = $item->getLinkId();
                if (isset($pos[$link_id])) {
                    $sorted[$link_id]['title'] = $item->getTitle();
                    $sorted[$link_id]['position'] = (int) $pos[$link_id];
                    $sorted[$link_id]['item'] = $item;
                } else {
                    $unsorted[$link_id]['title'] = $item->getTitle();
                    $unsorted[$link_id]['item'] = $item;
                }
            }
            $sorted = $this->sortArray(
                $sorted,
                'position',
                true
            );
            $unsorted = $this->sortArray(
                $unsorted,
                'title',
                false
            );

            $result = [];
            foreach ($sorted + $unsorted as $value) {
                $result[] = $value['item'];
            }
            $this->items = $result;
        }

        return $this;
    }

    protected function lookupSortMode(int $webr_id): int
    {
        return ilContainerSortingSettings::_lookupSortMode($webr_id);
    }

    /**
     * @return int[]
     */
    protected function lookupManualPositions(int $webr_id): array
    {
        return ilContainerSorting::lookupPositions($webr_id);
    }

    protected function sortArray(array $array, string $sort_by_key, bool $numeric): array
    {
        return ilArrayUtil::sortArray(
            $array,
            $sort_by_key,
            'asc',
            $numeric,
            true
        );
    }

    /**
     * @return ilWebLinkItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getFirstItem(): ?ilWebLinkItem
    {
        return $this->items[0] ?? null;
    }

    public function getWebrId(): int
    {
        return $this->webr_id;
    }
}
