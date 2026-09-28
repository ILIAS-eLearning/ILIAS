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

use ILIAS\Container\Sorting\Service\DomainService as SortingDomainService;

/**
 * @deprecated Please use the sorting domain service from the Container services. Will be removed with ILIAS 13.
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerSorting
{
    protected const int ORDER_DEFAULT = 999999;

    protected SortingDomainService $sorting_domain;

    /** @var array<int, self>  */
    protected static array $instances = [];
    protected int $obj_id;
    protected ?ilContainerSortingSettings $sorting_settings = null;
    protected array $sorting = [];

    private function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->sorting_domain = $DIC->container()->internal()->domain()->sorting();

        $this->obj_id = $a_obj_id;

        $this->read();
    }

    public function getSortingSettings(): ?ilContainerSortingSettings
    {
        return $this->sorting_settings;
    }

    public static function _getInstance(int $a_obj_id): self
    {
        return self::$instances[$a_obj_id] ?? (self::$instances[$a_obj_id] = new ilContainerSorting($a_obj_id));
    }

    /**
     * @param int $a_obj_id
     * @return array<int, int>
     */
    public static function lookupPositions(int $a_obj_id): array
    {
        global $DIC;

        $groupings = $DIC->container()->internal()->domain()->sorting()->positions()->getPositionsInObject($a_obj_id);
        $sorted = [];
        foreach ($groupings as $grouping) {
            foreach ($grouping->getPositions() as $position) {
                $sorted[$position->getChildID()] = $position->getPosition();
            }
        }
        return $sorted;
    }

    public function cloneSorting(
        int $a_target_id,
        int $a_copy_id
    ): void {
        $this->sorting_domain->positions()->clonePositions(
            $this->obj_id,
            $a_target_id,
            $a_copy_id
        );
    }

    /**
     * TODO move to a new Sorter
     */
    public function sortItems(array $a_items): array
    {
        if (!is_array($a_items)) {
            return [];
        }

        $sorted = [];
        if ($this->getSortingSettings()->getSortMode() !== ilContainer::SORT_MANUAL) {
            switch ($this->getSortingSettings()->getSortMode()) {
                case ilContainer::SORT_TITLE:
                    foreach ($a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type === 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }

                        // this line used until #4389 has been fixed (3.10.6)
                        // reanimated with 4.4.0
                        $sorted[$type] = ilArrayUtil::sortArray(
                            (array) $data,
                            'title',
                            ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            false
                        );

                        // the next line tried to use db sorting and has replaced sortArray due to bug #4389
                        // but leads to bug #12165. PHP should be able to do a proper sorting, if the locale
                        // is set correctly, so we witch back to sortArray (with 4.4.0) and see what
                        // feedback we get
                        // (next line has been used from 3.10.6 to 4.3.x)
                        //						$sorted[$type] = $data;
                    }
                    return $sorted ?: [];

                case ilContainer::SORT_ACTIVATION:
                    foreach ($a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type === 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }

                        $sorted[$type] = ilArrayUtil::sortArray(
                            (array) $data,
                            'start',
                            ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            true
                        );
                    }
                    return $sorted ?: [];


                case ilContainer::SORT_CREATION:
                    foreach ($a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type === 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }

                        $sorted[$type] = ilArrayUtil::sortArray(
                            (array) $data,
                            'create_date',
                            ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            true
                        );
                    }
                    return $sorted ?: [];
            }
            return $a_items;
        }
        if (!is_array($a_items) || !count($a_items)) {
            return $a_items;
        }
        $sorted = [];
        foreach ($a_items as $type => $data) {
            if ($type === 'sess_link') {
                $sorted[$type] = $data;
                continue;
            }

            // Add position
            $items = [];
            foreach ((array) $data as $key => $item) {
                $items[$key] = $item;
                if (isset($item['child'], $this->sorting['all'][$item['child']])) {
                    $items[$key]['position'] = $this->sorting['all'][$item['child']];
                } else {
                    $items[$key]['position'] = self::ORDER_DEFAULT;
                }
            }

            $items = $this->sortOrderDefault($items);

            switch ($type) {
                case '_non_sess':
                case '_all':
                default:
                    $sorted[$type] = ilArrayUtil::sortArray($items, 'position', 'asc', true);
                    break;
            }
        }
        return $sorted ?: [];
    }

    /**
     * TODO move to a new Sorter
     * sort subitems (items of sessions or learning objectives)
     */
    public function sortSubItems(
        string $a_parent_type,
        int $a_parent_id,
        array $a_items
    ): array {
        switch ($this->getSortingSettings()->getSortMode()) {
            case ilContainer::SORT_MANUAL:
                $items = [];
                foreach ($a_items as $key => $item) {
                    $items[$key] = $item;
                    $items[$key]['position'] = $this->sorting[$a_parent_type][$a_parent_id][$item['child']] ?? self::ORDER_DEFAULT;
                }

                $items = $this->sortOrderDefault($items);
                return ilArrayUtil::sortArray($items, 'position', 'asc', true);


            case ilContainer::SORT_ACTIVATION:
                return ilArrayUtil::sortArray(
                    $a_items,
                    'start',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );

            case ilContainer::SORT_CREATION:
                return ilArrayUtil::sortArray(
                    $a_items,
                    'create_date',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );

            default:
            case ilContainer::SORT_TITLE:
                return ilArrayUtil::sortArray(
                    $a_items,
                    'title',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    false
                );
        }
    }

    /**
     * @param array $a_type_positions positions e.g array(crs => array(1,2,3),'lres' => array(3,5,6))
     */
    public function savePost(array $a_type_positions): void
    {
        $this->sorting_domain->positions()->saveFromPost($this->obj_id, $a_type_positions);
    }

    /**
     * Not in use anymore.
     * Read block custom positions (for current object id)
     */
    public function getBlockPositions(): array
    {
        return [];
    }

    private function read(): void
    {
        if (!$this->obj_id) {
            $this->sorting_settings = new ilContainerSortingSettings();
        }

        $sorting_settings = ilContainerSortingSettings::getInstanceByObjId($this->obj_id);
        $this->sorting_settings = $sorting_settings->loadEffectiveSettings();

        $groupings = $this->sorting_domain->positions()->getPositionsInObject($this->obj_id);

        foreach ($groupings as $grouping) {
            foreach ($grouping->getPositions() as $position) {
                if ($grouping->getParentID()) {
                    $this->sorting[$grouping->getParentType()][$grouping->getParentID()][$position->getChildID()] = $position->getPosition();
                } else {
                    $this->sorting['all'][$position->getChildID()] = $position->getPosition();
                }
            }
        }
    }

    /**
     * TODO move to a new Sorter
     * Position and order sort order for new object without position in manual sorting type
     */
    private function sortOrderDefault(array $items): array
    {
        $no_position = [];

        foreach ($items as $key => $item) {
            if ($item["position"] == self::ORDER_DEFAULT) {
                $no_position[] = [
                    "key" => $key,
                    "title" => $item["title"] ?? "",
                    "create_date" => $item["create_date"] ?? "",
                    "start" => $item["start"] ?? ""
                ];
            }
        }

        if (!count($no_position)) {
            return $items;
        }

        switch ($this->getSortingSettings()->getSortNewItemsOrder()) {
            case ilContainer::SORT_NEW_ITEMS_ORDER_TITLE:
                $no_position = ilArrayUtil::sortArray(
                    $no_position,
                    'title',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    false
                );
                break;
            case ilContainer::SORT_NEW_ITEMS_ORDER_CREATION:
                $no_position = ilArrayUtil::sortArray(
                    $no_position,
                    'create_date',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );
                break;
            case ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION:
                $no_position = ilArrayUtil::sortArray(
                    $no_position,
                    'start',
                    ($this->getSortingSettings()->getSortDirection() === ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );
        }
        $count = (
            $this->getSortingSettings()->getSortNewItemsPosition() === ilContainer::SORT_NEW_ITEMS_POSITION_TOP
                ? -900000 :
                900000
        );

        foreach ($no_position as $values) {
            $items[$values["key"]]["position"] = $count;
            $count++;
        }
        return $items;
    }
}
