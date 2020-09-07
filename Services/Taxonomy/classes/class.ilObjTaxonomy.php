<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Taxonomy
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 */
class ilObjTaxonomy extends ilObject2
{
    const SORT_ALPHABETICAL = 0;
    const SORT_MANUAL = 1;
    protected $node_mapping = array();
    protected $item_sorting = false;
    
    /**
     * Constructor
     *
     * @param	integer	object id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "tax";
        parent::__construct($a_id, false);
    }

    /**
     * Init type
     *
     * @param
     * @return
     */
    public function initType()
    {
        $this->type = "tax";
    }
    
    /**
     * Set sorting mode
     *
     * @param int $a_val sorting mode
     */
    public function setSortingMode($a_val)
    {
        $this->sorting_mode = $a_val;
    }
    
    /**
     * Get sorting mode
     *
     * @return int sorting mode
     */
    public function getSortingMode()
    {
        return $this->sorting_mode;
    }
    
    /**
     * Set item sorting
     *
     * @param bool $a_val item sorting
     */
    public function setItemSorting($a_val)
    {
        $this->item_sorting = $a_val;
    }
    
    /**
     * Get item sorting
     *
     * @return bool item sorting
     */
    public function getItemSorting()
    {
        return $this->item_sorting;
    }
    
    /**
     * Get tree
     *
     * @param
     * @return
     */
    public function getTree()
    {
        if ($this->getId() > 0) {
            include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
            $tax_tree = new ilTaxonomyTree($this->getId());
            return $tax_tree;
        }
        return false;
    }
    
    /**
     * Get node mapping (used after cloning)
     *
     * @param
     * @return
     */
    public function getNodeMapping()
    {
        return $this->node_mapping;
    }
    
    
    /**
     * Create a new taxonomy
     */
    public function doCreate()
    {
        $ilDB = $this->db;
        
        // create tax data record
        $ilDB->manipulate("INSERT INTO tax_data " .
            "(id, sorting_mode, item_sorting) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote((int) $this->getSortingMode(), "integer") . "," .
            $ilDB->quote((int) $this->getItemSorting(), "integer") .
            ")");
        
        // create the taxonomy tree
        include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
        $node = new ilTaxonomyNode();
        $node->setType("");	// empty type
        $node->setTitle("Root node for taxonomy " . $this->getId());
        $node->setTaxonomyId($this->getId());
        $node->create();
        include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
        $tax_tree = new ilTaxonomyTree($this->getId());
        $tax_tree->addTree($this->getId(), $node->getId());
    }
    
    /**
     * clone taxonomy sheet (note: taxonomies have no ref ids and return an object id)
     *
     * @access	public
     * @return	integer		new obj id
     */
    public function doCloneObject($a_new_obj, $a_target_id, $a_copy_id = null)
    {
        $a_new_obj->setTitle($this->getTitle());
        $a_new_obj->setDescription($this->getDescription());
        $a_new_obj->setSortingMode($this->getSortingMode());

        $this->node_mapping = array();
        
        $this->cloneNodes(
            $a_new_obj,
            $a_new_obj->getTree()->readRootId(),
            $this->getTree()->readRootId()
        );
    }

    /**
     * Clone nodes
     *
     * @param
     * @return
     */
    public function cloneNodes($a_new_obj, $a_target_parent, $a_source_parent)
    {
        include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
        
        // get all childs
        $nodes = $this->getTree()->getChilds($a_source_parent);
        foreach ($nodes as $node) {
            switch ($node["type"]) {
                case "taxn":
                    $tax_node = new ilTaxonomyNode($node["child"]);
                    $new_node = $tax_node->copy($a_new_obj->getId());
                    break;
            }

            ilTaxonomyNode::putInTree(
                $a_new_obj->getId(),
                $new_node,
                $a_target_parent
            );
            
            $this->node_mapping[$node["child"]] = $new_node->getId();

            // handle childs
            $this->cloneNodes($a_new_obj, $new_node->getId(), $node["child"]);
        }
    }
    

    /**
     * Delete taxonomy object
     */
    public function doDelete()
    {
        $ilDB = $this->db;

        // delete usages
        self::deleteUsagesOfTaxonomy($this->getId());

        // get all nodes
        $tree = $this->getTree();
        $subtree = $tree->getSubTreeIds($tree->readRootId());
        $subtree[] = $tree->readRootId();
        
        // get root node data (important: must happen before we
        // delete the nodes
        $root_node_data = $tree->getNodeData($tree->readRootId());
        
        // delete all nodes
        include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
        foreach ($subtree as $node_id) {
            // delete node (this also deletes its assignments)
            $node = new ilTaxonomyNode($node_id);
            $node->delete();
        }
        
        // delete the tree
        $tree->deleteTree($root_node_data);

        // delete taxonoymy properties record
        $ilDB->manipulate(
            "DELETE FROM tax_data WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }


    /**
     * Read taxonomy properties
     */
    public function doRead()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM tax_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setSortingMode($rec["sorting_mode"]);
        $this->setItemSorting($rec["item_sorting"]);
    }

    /**
     * Upate taxonomy properties
     */
    public function doUpdate()
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate(
            $t = "UPDATE tax_data SET " .
            " sorting_mode = " . $ilDB->quote((int) $this->getSortingMode(), "integer") . ", " .
            " item_sorting = " . $ilDB->quote((int) $this->getItemSorting(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
    
    /**
     * Load language module
     *
     * @param
     * @return
     */
    public static function loadLanguageModule()
    {
        global $DIC;

        $lng = $DIC->language();
        
        $lng->loadLanguageModule("tax");
    }
    
    
    /**
     * Save Usage
     *
     * @param
     * @return
     */
    public static function saveUsage($a_tax_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_tax_id > 0 && $a_obj_id > 0) {
            $ilDB->replace(
                "tax_usage",
                array("tax_id" => array("integer", $a_tax_id),
                    "obj_id" => array("integer", $a_obj_id)
                    ),
                array()
            );
        }
    }
    
    /**
     * Get usage of object
     *
     * @param int $a_obj_id object id
     * @return array array of taxonomies
     */
    public static function getUsageOfObject($a_obj_id, $a_include_titles = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT tax_id FROM tax_usage " .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        $tax = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!$a_include_titles) {
                $tax[] = $rec["tax_id"];
            } else {
                $tax[] = array("tax_id" => $rec["tax_id"],
                    "title" => ilObject::_lookupTitle($rec["tax_id"])
                    );
            }
        }
        return $tax;
    }
    
    /**
     * Delete all usages of a taxonomy
     *
     * @param
     * @return
     */
    public static function deleteUsagesOfTaxonomy($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM tax_usage WHERE " .
            " tax_id = " . $ilDB->quote($a_id, "integer")
        );
    }
    
    
    /**
     * Get all assigned items under a node
     *
     * @param
     * @return
     */
    public static function getSubTreeItems($a_comp, $a_obj_id, $a_item_type, $a_tax_id, $a_node)
    {
        include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
        $tree = new ilTaxonomyTree($a_tax_id);

        $sub_nodes = $tree->getSubTreeIds($a_node);
        $sub_nodes[] = $a_node;
        include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
        
        $tn_ass = new ilTaxNodeAssignment($a_comp, $a_obj_id, $a_item_type, $a_tax_id);
        $items = $tn_ass->getAssignmentsOfNode($sub_nodes);
        
        return $items;
    }
    
    /**
     * Lookup
     *
     * @param
     * @return
     */
    protected static function lookup($a_field, $a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM tax_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return $rec[$a_field];
    }
    
    /**
     * Lookup sorting mode
     *
     * @param int $a_id taxonomy id
     * @return int sorting mode
     */
    public static function lookupSortingMode($a_id)
    {
        return self::lookup("sorting_mode", $a_id);
    }
}
