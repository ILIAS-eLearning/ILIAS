<?php declare(strict_types=1);

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
    public function sort() : self
    {
        $mode = ilContainerSortingSettings::_lookupSortMode(
            $this->getWebrId()
        );

        if ($mode == ilContainer::SORT_TITLE) {
            $items_arr = [];

            foreach ($this->getItems() as $item) {
                $link_id = $item->getLinkId();
                $items_arr[$link_id]['title'] = $item->getTitle();
                $items_arr[$link_id]['item'] = $item;
            }

            $items_arr = ilArrayUtil::sortArray(
                $items_arr,
                'title',
                'asc',
                false,
                true
            );

            $result = [];
            foreach ($items_arr as $value) {
                $result[] = $value['item'];
            }
            $this->items = $result;
        }

        $sorted = $unsorted = [];
        if ($mode == ilContainer::SORT_MANUAL) {
            $pos = ilContainerSorting::lookupPositions(
                $this->getWebrId()
            );
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
            $sorted = ilArrayUtil::sortArray(
                $sorted,
                'position',
                'asc',
                true,
                true
            );
            $unsorted = ilArrayUtil::sortArray(
                $unsorted,
                'title',
                'asc',
                false,
                true
            );

            $result = [];
            foreach ($sorted + $unsorted as $value) {
                $result[] = $value['item'];
            }
            $this->items = $result;
        }

        return $this;
    }

    /**
     * @return ilWebLinkItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    public function getFirstItem() : ?ilWebLinkItem
    {
        return $this->items[0] ?? null;
    }

    public function getWebrId() : int
    {
        return $this->webr_id;
    }
}
