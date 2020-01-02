<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Taxonomy/exceptions/class.ilTaxonomyException.php");

/**
 * Taxonomy node <-> item assignment
 *
 * This class allows to assign items to taxonomy nodes.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services/Taxonomy
 */
class ilTaxNodeAssignment
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * Constructor
     *
     * @param string $a_component_id component id (e.g. "glo" for Modules/Glossary)
     * @param int $a_obj_id repository object id of the object that is responsible for the assignment
     * @param string $a_item_type item type (e.g. "term", must be unique component wide) [use "obj" if repository object wide taxonomies!]
     * @param int $a_tax_id taxonomy id
     * @throws ilTaxonomyException
     */
    public function __construct($a_component_id, $a_obj_id, $a_item_type, $a_tax_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_component_id == "") {
            throw new ilTaxonomyException('No component ID passed to ilTaxNodeAssignment.');
        }
        
        if ($a_item_type == "") {
            throw new ilTaxonomyException('No item type passed to ilTaxNodeAssignment.');
        }

        if ((int) $a_tax_id == 0) {
            throw new ilTaxonomyException('No taxonomy ID passed to ilTaxNodeAssignment.');
        }

        $this->setComponentId($a_component_id);
        $this->setItemType($a_item_type);
        $this->setTaxonomyId($a_tax_id);
        $this->setObjectId($a_obj_id);
    }
    
    /**
     * Set component id
     *
     * @param string $a_val component id
     */
    protected function setComponentId($a_val)
    {
        $this->component_id = $a_val;
    }
    
    /**
     * Get component id
     *
     * @return string component id
     */
    public function getComponentId()
    {
        return $this->component_id;
    }
    
    /**
     * Set item type
     *
     * @param string $a_val item type
     */
    protected function setItemType($a_val)
    {
        $this->item_type = $a_val;
    }
    
    /**
     * Get item type
     *
     * @return string item type
     */
    public function getItemType()
    {
        return $this->item_type;
    }
    
    /**
     * Set taxonomy id
     *
     * @param int $a_val taxonomy id
     */
    protected function setTaxonomyId($a_val)
    {
        $this->taxonomy_id = $a_val;
    }
    
    /**
     * Get taxonomy id
     *
     * @return int taxonomy id
     */
    public function getTaxonomyId()
    {
        return $this->taxonomy_id;
    }
    
    /**
     * Set object id
     *
     * @param int $a_val object id
     */
    public function setObjectId($a_val)
    {
        $this->obj_id = $a_val;
    }

    /**
     * Get object id
     *
     * @return int object id
     */
    public function getObjectId()
    {
        return $this->obj_id;
    }
    
    /**
     * Get assignments of node
     *
     * @param int $a_node_id node id
     * @return array array of tax node assignments arrays
     */
    final public function getAssignmentsOfNode($a_node_id)
    {
        $ilDB = $this->db;

        if (is_array($a_node_id)) {
            $set = $ilDB->query(
                "SELECT * FROM tax_node_assignment " .
                " WHERE " . $ilDB->in("node_id", $a_node_id, false, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " ORDER BY order_nr ASC"
            );
        } else {
            $set = $ilDB->query(
                "SELECT * FROM tax_node_assignment " .
                " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " ORDER BY order_nr ASC"
            );
        }
        $ass = array();
        while ($rec  = $ilDB->fetchAssoc($set)) {
            $ass[] = $rec;
        }
        
        return $ass;
    }
    
    /**
     * Get assignments for item
     *
     * @param int $a_item_id item id
     * @return array array of tax node assignments arrays
     */
    final public function getAssignmentsOfItem($a_item_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment" .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
        $ass = array();
        while ($rec  = $ilDB->fetchAssoc($set)) {
            $ass[] = $rec;
        }
        return $ass;
    }
    
    /**
     * Add assignment
     *
     * @param int $a_node_id node id
     * @param int $a_item_id item id
     * @param int $a_order_nr order nr
     * @throws ilTaxonomyException
     */
    public function addAssignment($a_node_id, $a_item_id, $a_order_nr = 0)
    {
        $ilDB = $this->db;
        
        // nothing to do, if not both IDs are greater 0
        if ((int) $a_node_id == 0 || (int) $a_item_id == 0) {
            return;
        }
        
        // sanity check: does the node belong to the given taxonomy?
        $set = $ilDB->query(
            "SELECT tax_tree_id FROM tax_tree " .
            " WHERE child = " . $ilDB->quote($a_node_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["tax_tree_id"] != $this->getTaxonomyId()) {
            throw new ilTaxonomyException('addAssignment: Node ID does not belong to current taxonomy.');
        }

        // do not re-assign, if assignment already exists
        // order number should be kept in this case
        $set2 = $ilDB->query(
            $q = "SELECT item_id FROM tax_node_assignment " .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer")
        );
        if ($rec2 = $ilDB->fetchAssoc($set2)) {
            return;
        }

        if ($a_order_nr == 0) {
            $a_order_nr = $this->getMaxOrderNr($a_node_id) + 10;
        }
        
        $ilDB->replace(
            "tax_node_assignment",
            array(
                "node_id" => array("integer", $a_node_id),
                "component" => array("text", $this->getComponentId()),
                "item_type" => array("text", $this->getItemType()),
                "obj_id" => array("integer", $this->getObjectId()),
                "item_id" => array("integer", $a_item_id)
                ),
            array(
                "tax_id" => array("integer", $this->getTaxonomyId()),
                "order_nr" => array("integer", $a_order_nr)
                )
        );
    }

    /**
     * Delete assignment
     *
     * @param int $a_node_id node id
     * @param int $a_item_id item id
     * @throws ilTaxonomyException
     */
    public function deleteAssignment($a_node_id, $a_item_id)
    {
        $ilDB = $this->db;

        // nothing to do, if not both IDs are greater 0
        if ((int) $a_node_id == 0 || (int) $a_item_id == 0) {
            return;
        }

        // sanity check: does the node belong to the given taxonomy?
        $set = $ilDB->query(
            "SELECT tax_tree_id FROM tax_tree " .
            " WHERE child = " . $ilDB->quote($a_node_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["tax_tree_id"] != $this->getTaxonomyId()) {
            throw new ilTaxonomyException('addAssignment: Node ID does not belong to current taxonomy.');
        }

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }

    /**
     * Get maximum order number
     *
     * @param
     * @return
     */
    public function getMaxOrderNr($a_node_id)
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT max(order_nr) mnr FROM tax_node_assignment " .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        
        return (int) $rec["mnr"];
    }
    
    /**
     * Set order nr
     *
     * @param
     * @return
     */
    public function setOrderNr($a_node_id, $a_item_id, $a_order_nr)
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate(
            "UPDATE tax_node_assignment SET " .
            " order_nr = " . $ilDB->quote($a_order_nr, "integer") .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }
    
    
    /**
     * Delete assignments of item
     *
     * @param int $a_item_id item id
     */
    public function deleteAssignmentsOfItem($a_item_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }

    /**
     * Delete assignments of node
     *
     * @param int $a_node_id node id
     */
    public function deleteAssignmentsOfNode($a_node_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text")
        );
    }
    
    /**
     * Delete assignments of node
     *
     * @param int $a_node_id node id
     */
    public static function deleteAllAssignmentsOfNode($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " node_id = " . $ilDB->quote($a_node_id, "integer")
        );
    }

    /**
     * Fix Order Nr
     */
    public function fixOrderNr($a_node_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment " .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
            " ORDER BY order_nr ASC"
        );
        $cnt = 10;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE tax_node_assignment SET " .
                " order_nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND item_id = " . $ilDB->quote($rec["item_id"], "integer")
            );
            $cnt+= 10;
        }
    }



    /**
     * Find object which have assigned nodes
     *
     * @param int $a_item_type
     * @param int $a_tax_id
     * @param array $a_node_ids
     * @return array
     */
    public static function findObjectsByNode($a_tax_id, array $a_node_ids, $a_item_type)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
    
        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment" .
            " WHERE " . $ilDB->in("node_id", $a_node_ids, "", "integer") .
            " AND tax_id = " . $ilDB->quote($a_tax_id, "integer") .
            " AND item_type = " . $ilDB->quote($a_item_type, "text") .
            " ORDER BY order_nr ASC"
        );
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["obj_id"];
        }
        
        return $res;
    }
}
