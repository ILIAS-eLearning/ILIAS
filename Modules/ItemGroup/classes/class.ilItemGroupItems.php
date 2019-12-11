<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Item group items.
 *
 * This class is used to store the materials (items) that are assigned
 * to an item group. Main table used is item_group_item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilItemGroupItems
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_def;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var Logger
     */
    protected $log;

    public $ilDB;
    public $tree;
    public $lng;

    public $item_group_id = 0;
    public $item_group_ref_id = 0;
    public $items = array();

    /**
     * Constructor
     *
     * @param int $a_item_group_ref_id ref id of item group
     */
    public function __construct($a_item_group_ref_id = 0)
    {
        global $DIC;

        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->log = $DIC["ilLog"];
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];

        $this->db  = $ilDB;
        $this->lng = $lng;
        $this->tree = $tree;
        $this->obj_def = $objDefinition;

        $this->setItemGroupRefId((int) $a_item_group_ref_id);
        if ($this->getItemGroupRefId() > 0) {
            $this->setItemGroupId((int) ilObject::_lookupObjId($a_item_group_ref_id));
        }

        if ($this->getItemGroupId() > 0) {
            $this->read();
        }
    }

    /**
     * Set item group id
     *
     * @param int $a_val item group id
     */
    public function setItemGroupId($a_val)
    {
        $this->item_group_id = $a_val;
    }
    
    /**
     * Get item group id
     *
     * @return int item group id
     */
    public function getItemGroupId()
    {
        return $this->item_group_id;
    }
    
    /**
     * Set item group ref id
     *
     * @param int $a_val item group ref id
     */
    public function setItemGroupRefId($a_val)
    {
        $this->item_group_ref_id = $a_val;
    }
    
    /**
     * Get item group ref id
     *
     * @return int item group ref id
     */
    public function getItemGroupRefId()
    {
        return $this->item_group_ref_id;
    }
    
    /**
     * Set items
     *
     * @param array $a_val items (array of ref ids)
     */
    public function setItems($a_val)
    {
        $this->items = $a_val;
    }
    
    /**
     * Get items
     *
     * @return array items (array of ref ids)
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Add one item
     *
     * @param int $a_item_ref_id item ref id
     */
    public function addItem($a_item_ref_id)
    {
        if (!in_array($a_item_ref_id, $this->items)) {
            $this->items[] = (int) $a_item_ref_id;
        }
    }
    
    /**
     * Delete items of item group
     */
    public function delete()
    {
        $query = "DELETE FROM item_group_item " .
            "WHERE item_group_id = " . $this->db->quote($this->getItemGroupId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Update item group items
     */
    public function update()
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

    /**
     * Read item group items
     */
    public function read()
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

    /**
     * Get assignable items
     *
     * @param
     * @return
     */
    public function getAssignableItems()
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

        include_once("./Modules/File/classes/class.ilObjFileAccess.php");
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
        
        $materials = ilUtil::sortArray($materials, "title", "asc");
        
        return $materials;
    }

    
    /**
     * Get valid items
     *
     * @param
     * @return
     */
    public function getValidItems()
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

    /**
     * Clone items
     *
     * @access public
     *
     * @param int source event id
     * @param int copy id
     */
    public function cloneItems($a_source_id, $a_copy_id)
    {
        $ilObjDataCache = $this->obj_data_cache;
        $ilLog = $this->log;
        
        $ilLog->write(__METHOD__ . ': Begin cloning item group materials ... -' . $a_source_id . '-');
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
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
        return true;
    }
    
    public static function _getItemsOfContainer($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        
        $itgr_nodes = $tree->getChildsByType($a_ref_id, 'itgr');
        foreach ($itgr_nodes as $node) {
            $itgr_ids[] = $node['obj_id'];
        }
        $query = "SELECT item_ref_id FROM item_group_item " .
            "WHERE " . $ilDB->in('item_group_id', $itgr_ids, false, 'integer');
            

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_ref_id;
        }
        return $items ? $items : array();
    }
}
