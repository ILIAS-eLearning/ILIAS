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
 * Item group items.
 * This class is used to store the materials (items) that are assigned
 * to an item group. Main table used is item_group_item
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupItems
{
    protected ilDBInterface $db;
    protected ilObjectDefinition $obj_def;
    protected ilObjectDataCache $obj_data_cache;
    protected ilLogger $log;
    public ilTree $tree;
    public ilLanguage $lng;
    public int $item_group_id = 0;
    public int $item_group_ref_id = 0;
    public array $items = array();

    public function __construct(
        int $a_item_group_ref_id = 0
    ) {
        global $DIC;

        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->log = $DIC["ilLog"];
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->obj_def = $DIC["objDefinition"];

        $this->setItemGroupRefId($a_item_group_ref_id);
        if ($this->getItemGroupRefId() > 0) {
            $this->setItemGroupId(ilObject::_lookupObjId($a_item_group_ref_id));
        }

        if ($this->getItemGroupId() > 0) {
            $this->read();
        }
    }

    public function setItemGroupId(int $a_val): void
    {
        $this->item_group_id = $a_val;
    }

    public function getItemGroupId(): int
    {
        return $this->item_group_id;
    }

    public function setItemGroupRefId(int $a_val): void
    {
        $this->item_group_ref_id = $a_val;
    }

    public function getItemGroupRefId(): int
    {
        return $this->item_group_ref_id;
    }

    /**
     * @param int[] $a_val items (array of ref ids)
     */
    public function setItems(array $a_val): void
    {
        $this->items = $a_val;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(int $a_item_ref_id): void
    {
        if (!in_array($a_item_ref_id, $this->items)) {
            $this->items[] = $a_item_ref_id;
        }
    }

    public function delete(): void
    {
        $query = "DELETE FROM item_group_item " .
            "WHERE item_group_id = " . $this->db->quote($this->getItemGroupId(), 'integer');
        $this->db->manipulate($query);
    }

    public function update(): void
    {
        $this->delete();

        foreach ($this->items as $item) {
            $query = "INSERT INTO item_group_item (item_group_id,item_ref_id) " .
                "VALUES( " .
                $this->db->quote($this->getItemGroupId(), 'integer') . ", " .
                $this->db->quote($item, 'integer') . " " .
                ")";
            $this->db->manipulate($query);
        }
    }

    public function read(): void
    {
        $this->items = array();
        $set = $this->db->query(
            "SELECT * FROM item_group_item " .
            " WHERE item_group_id = " . $this->db->quote($this->getItemGroupId(), "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->items[] = $rec["item_ref_id"];
        }
    }

    public function getAssignableItems(): array
    {
        $objDefinition = $this->obj_def;

        if ($this->getItemGroupRefId() <= 0) {
            return array();
        }

        $parent_node = $this->tree->getNodeData(
            $this->tree->getParentId($this->getItemGroupRefId())
        );

        $materials = array();
        $nodes = $this->tree->getChilds($parent_node["child"]);

        foreach ($nodes as $node) {
            // filter side blocks and session, item groups and role folder
            if ($node['child'] == $parent_node["child"] ||
                $this->obj_def->isSideBlock($node['type']) ||
                in_array($node['type'], array('sess', 'itgr', 'rolf', 'adm'))) {
                continue;
            }

            // filter hidden files
            // see http://www.ilias.de/mantis/view.php?id=10269
            if ($node['type'] == "file" &&
                ilObjFileAccess::_isFileHidden($node['title'])) {
                continue;
            }

            if ($objDefinition->isInactivePlugin($node['type'])) {
                continue;
            }

            $materials[] = $node;
        }

        $materials = ilArrayUtil::sortArray($materials, "title", "asc");

        return $materials;
    }

    public function getValidItems(): array
    {
        $items = $this->getItems();
        $ass_items = $this->getAssignableItems();
        $valid_items = array();
        foreach ($ass_items as $aitem) {
            if (in_array($aitem["ref_id"], $items)) {
                $valid_items[] = $aitem["ref_id"];
            }
        }
        return $valid_items;
    }

    public function cloneItems(
        int $a_source_id,
        int $a_copy_id
    ): void {
        $ilLog = $this->log;

        $ilLog->write(__METHOD__ . ': Begin cloning item group materials ... -' . $a_source_id . '-');

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $new_items = array();
        // check: is this a ref id!?
        $source_ig = new ilItemGroupItems($a_source_id);
        foreach ($source_ig->getItems() as $item_ref_id) {
            if (isset($mappings[$item_ref_id]) and $mappings[$item_ref_id]) {
                $ilLog->write(__METHOD__ . ': Clone item group item nr. ' . $item_ref_id);
                $new_items[] = $mappings[$item_ref_id];
            } else {
                $ilLog->write(__METHOD__ . ': No mapping found for item group item nr. ' . $item_ref_id);
            }
        }
        $this->setItems($new_items);
        $this->update();
        $ilLog->write(__METHOD__ . ': Finished cloning item group items ...');
    }

    public static function _getItemsOfContainer(int $a_ref_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        $itgr_ids = [];
        $itgr_nodes = $tree->getChildsByType($a_ref_id, 'itgr');
        foreach ($itgr_nodes as $node) {
            $itgr_ids[] = $node['obj_id'];
        }
        $query = "SELECT item_ref_id FROM item_group_item " .
            "WHERE " . $ilDB->in('item_group_id', $itgr_ids, false, 'integer');


        $res = $ilDB->query($query);
        $items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_ref_id;
        }
        return $items;
    }
}
