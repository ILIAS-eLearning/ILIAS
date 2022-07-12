<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP collection of repository objects
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCollectionOfRepositoryObjects extends ilLPCollection
{
    protected static array $possible_items = array();

    protected ilTree $tree;
    protected ilObjectDefinition $objDefinition;

    public function __construct(int $a_obj_id, int $a_mode)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];

        parent::__construct($a_obj_id, $a_mode);
    }

    public function getPossibleItems(
        int $a_ref_id,
        bool $a_full_data = false
    ) : array {
        global $DIC;

        $cache_idx = $a_ref_id . "__" . $a_full_data;
        if (!isset(self::$possible_items[$cache_idx])) {
            $all_possible = array();

            if (!$this->tree->isDeleted($a_ref_id)) {
                if (!$a_full_data) {
                    $data = $this->tree->getRbacSubtreeInfo($a_ref_id);
                } else {
                    $node = $this->tree->getNodeData($a_ref_id);
                    $data = $this->tree->getSubTree($node);
                }
                foreach ($data as $node) {
                    if (!$a_full_data) {
                        $item_ref_id = (int) $node['child'];
                    } else {
                        $item_ref_id = (int) $node['ref_id'];
                    }

                    // avoid recursion
                    if ($item_ref_id == $a_ref_id || !$this->validateEntry(
                        $item_ref_id
                    )) {
                        continue;
                    }

                    switch ($node['type']) {
                        case 'sess':
                        case 'exc':
                        case 'fold':
                        case 'grp':
                        case 'sahs':
                        case 'lm':
                        case 'tst':
                        case 'file':
                        case 'mcst':
                        case 'htlm':
                        case 'svy':
                        case "prg":
                        case 'iass':
                        case 'copa':
                        case 'frm':
                        case 'cmix':
                        case 'lti':
                        case 'lso':
                        case 'crsr':
                            if (!$a_full_data) {
                                $all_possible[] = $item_ref_id;
                            } else {
                                $all_possible[$item_ref_id] = array(
                                    'ref_id' => (int) $item_ref_id,
                                    'obj_id' => (int) $node['obj_id'],
                                    'title' => (string) $node['title'],
                                    'description' => (string) $node['description'],
                                    'type' => (string) $node['type']
                                );
                            }
                            break;

                        // repository plugin object?
                        case $this->objDefinition->isPluginTypeName(
                            $node['type']
                        ):
                            $only_active = false;
                            if (!$this->isAssignedEntry($item_ref_id)) {
                                $only_active = true;
                            }
                            if (ilRepositoryObjectPluginSlot::isTypePluginWithLP(
                                $node['type'],
                                $only_active
                            )) {
                                if (!$a_full_data) {
                                    $all_possible[] = $item_ref_id;
                                } else {
                                    $all_possible[$item_ref_id] = array(
                                        'ref_id' => (int) $item_ref_id,
                                        'obj_id' => (int) $node['obj_id'],
                                        'title' => (string) $node['title'],
                                        'description' => (string) $node['description'],
                                        'type' => (string) $node['type']
                                    );
                                }
                            }
                            break;
                    }
                }
            }

            self::$possible_items[$cache_idx] = $all_possible;
        }

        return self::$possible_items[$cache_idx];
    }

    protected function validateEntry(int $a_item_id) : bool
    {
        $a_item_type = ilObject::_lookupType($a_item_id, true);
        // this is hardcoded so we do not need to call all ObjectLP types
        if ($a_item_type == 'tst') {
            // Check anonymized
            $item_obj_id = ilObject::_lookupObjId($a_item_id);
            $olp = ilObjectLP::getInstance($item_obj_id);
            if ($olp->isAnonymized()) {
                return false;
            }
        }
        return true;
    }

    public function cloneCollection(int $a_target_id, int $a_copy_id) : void
    {
        parent::cloneCollection($a_target_id, $a_copy_id);

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $target_collection = new static($target_obj_id, $this->mode);

        // clone (active) groupings
        foreach ($this->getGroupedItemsForLPStatus(
        ) as $grouping_id => $group) {
            $target_item_ids = array();
            foreach ($group["items"] as $item) {
                if (!isset($mappings[$item]) or !$mappings[$item]) {
                    continue;
                }

                $target_item_ids[] = $mappings[$item];
            }

            // grouping - if not only single item left after copy?
            if ($grouping_id && sizeof($target_item_ids) > 1) {
                // should not be larger than group
                $num_obligatory = min(
                    sizeof($target_item_ids),
                    $group["num_obligatory"]
                );

                $target_collection->createNewGrouping(
                    $target_item_ids,
                    $num_obligatory
                );
            } else {
                // #15487 - single items
                foreach ($target_item_ids as $item_id) {
                    $this->addEntry($item_id);
                }
            }
        }
    }

    protected function read(int $a_obj_id) : void
    {
        $items = array();

        $ref_ids = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($ref_ids);
        $possible = $this->getPossibleItems($ref_id);

        $res = $this->db->query(
            "SELECT utc.item_id, obd.type" .
            " FROM ut_lp_collections utc" .
            " JOIN object_reference obr ON item_id = ref_id" .
            " JOIN object_data obd ON obr.obj_id = obd.obj_id" .
            " WHERE utc.obj_id = " . $this->db->quote($a_obj_id, "integer") .
            " AND active = " . $this->db->quote(1, "integer") .
            " ORDER BY title"
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($row->item_id, $possible) &&
                $this->validateEntry((int) $row->item_id)) {
                $items[] = $row->item_id;
            } else {
                $this->deleteEntry((int) $row->item_id);
            }
        }

        $this->items = $items;
    }

    protected function addEntry(int $a_item_id) : bool
    {
        // only active entries are assigned!
        if (!$this->isAssignedEntry($a_item_id)) {
            // #13278 - because of grouping inactive items may exist
            $this->deleteEntry($a_item_id);

            $query = "INSERT INTO ut_lp_collections" .
                " (obj_id, lpmode, item_id, grouping_id, num_obligatory, active)" .
                " VALUES (" . $this->db->quote($this->obj_id, "integer") .
                ", " . $this->db->quote($this->mode, "integer") .
                ", " . $this->db->quote($a_item_id, "integer") .
                ", " . $this->db->quote(0, "integer") .
                ", " . $this->db->quote(0, "integer") .
                ", " . $this->db->quote(1, "integer") .
                ")";
            $this->db->manipulate($query);
            $this->items[] = $a_item_id;
        }
        return true;
    }

    protected function deleteEntry(int $a_item_id) : bool
    {
        $query = "DELETE FROM ut_lp_collections " .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND item_id = " . $this->db->quote($a_item_id, "integer") .
            " AND grouping_id = " . $this->db->quote(0, "integer");
        $this->db->manipulate($query);
        return true;
    }

    public static function hasGroupedItems(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT item_id FROM ut_lp_collections" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND grouping_id > " . $ilDB->quote(0, "integer");
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }

    protected function getGroupingIds(array $a_item_ids) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $grouping_ids = array();

        $query = "SELECT grouping_id FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND " . $this->db->in("item_id", $a_item_ids, false, "integer") .
            " AND grouping_id > " . $this->db->quote(0, "integer");
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $grouping_ids[] = $row->grouping_id;
        }

        return $grouping_ids;
    }

    public function deactivateEntries(array $a_item_ids) : void
    {
        parent::deactivateEntries($a_item_ids);

        $grouping_ids = $this->getGroupingIds($a_item_ids);
        if ($grouping_ids) {
            $query = "UPDATE ut_lp_collections" .
                " SET active = " . $this->db->quote(0, "integer") .
                " WHERE " . $this->db->in(
                    "grouping_id",
                    $grouping_ids,
                    false,
                    "integer"
                ) .
                " AND obj_id = " . $this->db->quote($this->obj_id, "integer");
            $this->db->manipulate($query);
        }
    }

    public function activateEntries(array $a_item_ids) : void
    {
        parent::activateEntries($a_item_ids);

        $grouping_ids = $this->getGroupingIds($a_item_ids);
        if ($grouping_ids) {
            $query = "UPDATE ut_lp_collections" .
                " SET active = " . $this->db->quote(1, "integer") .
                " WHERE " . $this->db->in(
                    "grouping_id",
                    $grouping_ids,
                    false,
                    "integer"
                ) .
                " AND obj_id = " . $this->db->quote($this->obj_id, "integer");
            $this->db->manipulate($query);
        }
    }

    public function createNewGrouping(
        array $a_item_ids,
        int $a_num_obligatory = 1
    ) : void {
        $this->activateEntries($a_item_ids);

        $all_item_ids = array();
        $grouping_ids = $this->getGroupingIds($a_item_ids);
        $query = "SELECT item_id FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND " . $this->db->in(
                "grouping_id",
                $grouping_ids,
                false,
                "integer"
            );
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $all_item_ids[] = $row->item_id;
        }

        $all_item_ids = array_unique(array_merge($all_item_ids, $a_item_ids));

        $this->releaseGrouping($a_item_ids);

        // Create new grouping
        $query = "SELECT MAX(grouping_id) grp FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " GROUP BY obj_id";
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $grp_id = $row->grp;
        ++$grp_id;

        $query = "UPDATE ut_lp_collections SET" .
            " grouping_id = " . $this->db->quote($grp_id, "integer") .
            ", num_obligatory = " . $this->db->quote(
                $a_num_obligatory,
                "integer"
            ) .
            ", active = " . $this->db->quote(1, "integer") .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND " . $this->db->in("item_id", $all_item_ids, false, "integer");
        $this->db->manipulate($query);
    }

    public function releaseGrouping(array $a_item_ids) : void
    {
        $grouping_ids = $this->getGroupingIds($a_item_ids);

        $query = "UPDATE ut_lp_collections" .
            " SET grouping_id = " . $this->db->quote(0, "integer") .
            ", num_obligatory = " . $this->db->quote(0, "integer") .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND " . $this->db->in(
                "grouping_id",
                $grouping_ids,
                false,
                "integer"
            );
        $this->db->manipulate($query);
    }

    public function saveObligatoryMaterials(array $a_obl) : void
    {
        foreach ($a_obl as $grouping_id => $num) {
            $query = "SELECT count(obj_id) num FROM ut_lp_collections" .
                " WHERE obj_id = " . $this->db->quote(
                    $this->obj_id,
                    "integer"
                ) .
                " AND grouping_id = " . $this->db->quote(
                    $grouping_id,
                    'integer'
                ) .
                " GROUP BY obj_id";
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if ($num <= 0 || $num >= $row->num) {
                    throw new UnexpectedValueException();
                }
            }
        }
        foreach ($a_obl as $grouping_id => $num) {
            $query = "UPDATE ut_lp_collections" .
                " SET num_obligatory = " . $this->db->quote($num, "integer") .
                " WHERE obj_id = " . $this->db->quote(
                    $this->obj_id,
                    "integer"
                ) .
                " AND grouping_id = " . $this->db->quote(
                    $grouping_id,
                    "integer"
                );
            $this->db->manipulate($query);
        }
    }

    public function getTableGUIData(int $a_parent_ref_id) : array
    {
        $items = $this->getPossibleItems($a_parent_ref_id, true);

        $data = array();
        $done = array();
        foreach ($items as $item_id => $item) {
            if (in_array($item_id, $done)) {
                continue;
            }

            $table_item = $this->parseTableGUIItem($item_id, $item);

            // grouping
            $table_item['grouped'] = array();
            $grouped_items = $this->getTableGUItemGroup($item_id);
            if (count((array) ($grouped_items['items'] ?? [])) > 1) {
                foreach ($grouped_items['items'] as $grouped_item_id) {
                    if ($grouped_item_id == $item_id ||
                        !is_array($items[$grouped_item_id])) { // #15498
                        continue;
                    }

                    $table_item['grouped'][] = $this->parseTableGUIItem(
                        $grouped_item_id,
                        $items[$grouped_item_id]
                    );
                    $table_item['num_obligatory'] = $grouped_items['num_obligatory'];
                    $table_item['grouping_id'] = $grouped_items['grouping_id'];

                    $done[] = $grouped_item_id;
                }
            }
            $data[] = $table_item;
        }
        return $data;
    }

    protected function parseTableGUIItem(int $a_id, array $a_item) : array
    {
        $table_item = $a_item;
        $table_item['id'] = $a_id;
        $table_item['status'] = $this->isAssignedEntry($a_id);

        $olp = ilObjectLP::getInstance($a_item['obj_id']);
        $table_item['mode_id'] = $olp->getCurrentMode();
        $table_item['mode'] = $olp->getModeText($table_item['mode_id']);
        $table_item['anonymized'] = $olp->isAnonymized();

        return $table_item;
    }

    protected function getTableGUItemGroup(int $item_id) : array
    {
        $items = array();
        $query = "SELECT grouping_id FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND item_id = " . $this->db->quote($item_id, "integer");
        $res = $this->db->query($query);
        $grouping_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $grouping_id = (int) $row->grouping_id;
        }
        if ($grouping_id > 0) {
            $query = "SELECT item_id, num_obligatory FROM ut_lp_collections" .
                " WHERE obj_id = " . $this->db->quote(
                    $this->obj_id,
                    "integer"
                ) .
                " AND grouping_id = " . $this->db->quote(
                    $grouping_id,
                    "integer"
                );
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $items['items'][] = (int) $row->item_id;
                $items['num_obligatory'] = (int) $row->num_obligatory;
                $items['grouping_id'] = (int) $grouping_id;
            }
        }
        return $items;
    }

    public function getGroupedItemsForLPStatus() : array
    {
        $items = $this->getItems();
        $query = " SELECT * FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND active = " . $this->db->quote(1, "integer");
        $res = $this->db->query($query);

        $grouped = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($row->item_id, $items)) {
                $grouped[$row->grouping_id]['items'][] = (int) $row->item_id;
                $grouped[$row->grouping_id]['num_obligatory'] = (int) $row->num_obligatory;
            }
        }
        return $grouped;
    }
}
