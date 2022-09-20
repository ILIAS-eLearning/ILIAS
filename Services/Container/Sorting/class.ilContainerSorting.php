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

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerSorting
{
    protected const ORDER_DEFAULT = 999999;

    protected ilLogger $log;
    protected ilTree $tree;
    /** @var array<int, self>  */
    protected static array $instances = [];
    protected int $obj_id;
    protected ilDBInterface $db;
    protected ?ilContainerSortingSettings $sorting_settings = null;
    protected array $sorting = [];

    private function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->log = $DIC["ilLog"];
        $this->tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();

        $this->db = $ilDB;
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

        $ilDB = $DIC->database();
        $sorted = [];

        $query = "SELECT child_id, position FROM container_sorting WHERE " .
            "obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sorted[(int) $row->child_id] = (int) $row->position;
        }

        return $sorted;
    }

    public function cloneSorting(
        int $a_target_id,
        int $a_copy_id
    ): void {
        $ilDB = $this->db;

        $ilLog = ilLoggerFactory::getLogger("cont");
        $ilLog->debug("Cloning container sorting.");

        $target_obj_id = ilObject::_lookupObjId($a_target_id);

        $mappings = ilCopyWizardOptions::_getInstance($a_copy_id)->getMappings();


        // copy blocks sorting
        $set = $ilDB->queryF(
            "SELECT * FROM container_sorting_bl " .
            " WHERE obj_id = %s ",
            ["integer"],
            [$this->obj_id]
        );
        if (($rec = $ilDB->fetchAssoc($set)) && $rec["block_ids"] != "") {
            $ilLog->debug("Got block sorting for obj_id = " . $this->obj_id . ": " . $rec["block_ids"]);
            $new_ids = implode(";", array_map(static function ($block_id) use ($mappings) {
                if (is_numeric($block_id)) {
                    $block_id = $mappings[$block_id];
                }
                return $block_id;
            }, explode(";", $rec["block_ids"])));

            $ilDB->replace(
                "container_sorting_bl",
                ["obj_id" => ["integer", $target_obj_id]],
                ["block_ids" => ["text", $new_ids]]
            );

            $ilLog->debug("Write block sorting for obj_id = " . $target_obj_id . ": " . $new_ids);
        }


        $ilLog->debug("Read container_sorting for obj_id = " . $this->obj_id);

        $query = "SELECT * FROM container_sorting " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer');

        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!isset($mappings[$row->child_id]) || !$mappings[$row->child_id]) {
                $ilLog->debug("No mapping found for child id:" . $row->child_id);
                continue;
            }


            $new_parent_id = 0;
            if ($row->parent_id) {
                // see bug #20347
                // at least in the case of sessions and item groups parent_ids in container sorting are object IDs but $mappings store references
                if (in_array($row->parent_type, ["sess", "itgr"])) {
                    $par_refs = ilObject::_getAllReferences($row->parent_id);
                    $par_ref_id = current($par_refs);			// should be only one
                    $ilLog->debug("Got ref id: " . $par_ref_id . " for obj_id " . $row->parent_id . " map ref id: " . $mappings[$par_ref_id] . ".");
                    if (isset($mappings[$par_ref_id])) {
                        $new_parent_ref_id = $mappings[$par_ref_id];
                        $new_parent_id = ilObject::_lookupObjectId($new_parent_ref_id);
                    }
                } else {		// not sure if this is still used for other cases that expect ref ids
                    $new_parent_id = $mappings[$row->parent_id];
                }
                if ((int) $new_parent_id === 0) {
                    $ilLog->debug("No mapping found for parent id:" . $row->parent_id . ", child_id: " . $row->child_id);
                    continue;
                }
            }

            $query = "DELETE FROM container_sorting " .
                "WHERE obj_id = " . $ilDB->quote($target_obj_id, 'integer') . " " .
                "AND child_id = " . $ilDB->quote($mappings[$row->child_id], 'integer') . " " .
                "AND parent_type = " . $ilDB->quote($row->parent_type, 'text') . ' ' .
                "AND parent_id = " . $ilDB->quote((int) $new_parent_id, 'integer');
            $ilLog->debug($query);
            $ilDB->manipulate($query);

            // Add new value
            $query = "INSERT INTO container_sorting (obj_id,child_id,position,parent_type,parent_id) " .
                "VALUES( " .
                $ilDB->quote($target_obj_id, 'integer') . ", " .
                $ilDB->quote($mappings[$row->child_id], 'integer') . ", " .
                $ilDB->quote($row->position, 'integer') . ", " .
                $ilDB->quote($row->parent_type, 'text') . ", " .
                $ilDB->quote((int) $new_parent_id, 'integer') .
                ")";
            $ilLog->debug($query);
            $ilDB->manipulate($query);
        }
    }

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
        if (!is_array($a_type_positions)) {
            return;
        }
        $items = [];
        foreach ($a_type_positions as $key => $position) {
            if ($key === "blocks") {
                $this->saveBlockPositions($position);
            } elseif (!is_array($position)) {
                $items[$key] = ((float) $position) * 100;
            } else {
                foreach ($position as $parent_id => $sub_items) {
                    $this->saveSubItems($key, $parent_id, $sub_items ?: []);
                }
            }
        }

        if (!count($items)) {
            $this->saveItems([]);
            return;
        }

        asort($items);
        $new_indexed = [];
        $position = 0;
        foreach ($items as $key => $null) {
            $new_indexed[$key] = ++$position;
        }

        $this->saveItems($new_indexed);
    }

    protected function saveItems(array $a_items): void
    {
        $ilDB = $this->db;

        foreach ($a_items as $child_id => $position) {
            $ilDB->replace(
                'container_sorting',
                [
                    'obj_id' => ['integer', $this->obj_id],
                    'child_id' => ['integer', $child_id],
                    'parent_id' => ['integer', 0]
                ],
                [
                    'parent_type' => ['text', ''],
                    'position' => ['integer', $position]
                ]
            );
        }
    }

    protected function saveSubItems(
        string $a_parent_type,
        int $a_parent_id,
        array $a_items
    ): void {
        $ilDB = $this->db;

        foreach ($a_items as $child_id => $position) {
            $ilDB->replace(
                'container_sorting',
                [
                    'obj_id' => ['integer', $this->obj_id],
                    'child_id' => ['integer', $child_id],
                    'parent_id' => ['integer', $a_parent_id]
                ],
                [
                    'parent_type' => ['text', $a_parent_type],
                    'position' => ['integer', $position]
                ]
            );
        }
    }

    /**
     * Save block custom positions (for current object id)
     */
    protected function saveBlockPositions(array $a_values): void
    {
        $ilDB = $this->db;
        asort($a_values);
        $ilDB->replace(
            'container_sorting_bl',
            [
                'obj_id' => ['integer', $this->obj_id]
            ],
            [
                'block_ids' => ['text', implode(";", array_keys($a_values))]
            ]
        );
    }

    /**
     * Read block custom positions (for current object id)
     */
    public function getBlockPositions(): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT block_ids" .
            " FROM container_sorting_bl" .
            " WHERE obj_id = " . $ilDB->quote($this->obj_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if (isset($row["block_ids"])) {
            return explode(";", $row["block_ids"]);
        }

        return [];
    }

    private function read(): void
    {
        if (!$this->obj_id) {
            $this->sorting_settings = new ilContainerSortingSettings();
        }

        $sorting_settings = ilContainerSortingSettings::getInstanceByObjId($this->obj_id);
        $this->sorting_settings = $sorting_settings->loadEffectiveSettings();
        $query = "SELECT * FROM container_sorting " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ORDER BY position";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->parent_id) {
                $this->sorting[$row->parent_type][$row->parent_id][$row->child_id] = $row->position;
            } else {
                $this->sorting['all'][$row->child_id] = $row->position;
            }
        }
    }

    /**
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
                    true
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
