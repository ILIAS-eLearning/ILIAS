<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Tree handler for personal workspace
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilWorkspaceTree extends ilTree
{
    public function __construct(
        int $a_tree_id,
        int $a_root_id = 0
    ) {
        global $DIC;

        parent::__construct($a_tree_id, $a_root_id);
        $this->setTableNames('tree_workspace', 'object_data', 'object_reference_ws');
        $this->setTreeTablePK('tree');
        $this->setObjectTablePK('obj_id');
        $this->setReferenceTablePK('wsp_id');

        if (!$this->exists()) {
            $this->createTreeForUser($a_tree_id);
        }

        // ilTree sets it to ROOT_FOLDER_ID if not given...
        if (!$a_root_id) {
            $this->readRootId();
        }
    }

    protected function exists() : bool
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM tree_workspace " .
            " WHERE tree = %s ",
            ["integer"],
            [$this->getTreeId()]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Create workspace reference for object
     * @param int $a_object_id
     * @return int node id
     */
    public function createReference(int $a_object_id) : int
    {
        $ilDB = $this->db;
        
        $next_id = $ilDB->nextId($this->table_obj_reference);

        $fields = array($this->ref_pk => array("integer", $next_id),
            $this->obj_pk => array("integer", $a_object_id));

        $ilDB->insert($this->table_obj_reference, $fields);
        
        return $next_id;
    }

    /**
     * Get object id for node id
     * @param int $a_node_id
     * @return int object id
     */
    public function lookupObjectId(int $a_node_id) : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT " . $this->obj_pk .
            " FROM " . $this->table_obj_reference .
            " WHERE " . $this->ref_pk . " = " . $ilDB->quote($a_node_id, "integer"));
        $res = $ilDB->fetchAssoc($set);

        return (int) $res[$this->obj_pk];
    }
    
    
    /**
     * Get node id for object id
     * As we do not allow references in workspace this should not be ambigious
     * @param int $a_obj_id
     * @return int node id
     */
    public function lookupNodeId(int $a_obj_id) : int
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT " . $this->ref_pk .
            " FROM " . $this->table_obj_reference .
            " WHERE " . $this->obj_pk . " = " . $ilDB->quote($a_obj_id, "integer"));
        $res = $ilDB->fetchAssoc($set);

        return (int) $res[$this->ref_pk];
    }
    
    /**
     * Get owner for node id
     * @param int $a_node_id
     * @return int object id
     */
    public function lookupOwner(int $a_node_id) : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT tree" .
            " FROM " . $this->table_obj_reference .
            " JOIN " . $this->table_tree . " ON (" . $this->table_obj_reference . "." . $this->ref_pk . " = " . $this->table_tree . ".child)" .
            " WHERE " . $this->ref_pk . " = " . $ilDB->quote($a_node_id, "integer"));
        $res = $ilDB->fetchAssoc($set);

        return (int) $res["tree"];
    }

    /**
     * Add object to tree
     *
     * @param int $a_parent_node_id
     * @param int $a_object_id
     * @return int node id
     */
    public function insertObject(
        int $a_parent_node_id,
        int $a_object_id
    ) : int {
        $node_id = $this->createReference($a_object_id);
        $this->insertNode($node_id, $a_parent_node_id);
        return $node_id;
    }

    /**
     * Delete object from reference table
     */
    public function deleteReference(int $a_node_id) : int
    {
        $ilDB = $this->db;

        $query = "DELETE FROM " . $this->table_obj_reference .
            " WHERE " . $this->ref_pk . " = " . $ilDB->quote($a_node_id, "integer");
        return $ilDB->manipulate($query);
    }
        
    /**
     * Remove all tree and node data
     */
    public function cascadingDelete() : void
    {
        $root_id = $this->readRootId();
        if (!$root_id) {
            return;
        }
        
        $root = $this->getNodeData($root_id);
        
        $access_handler = new ilWorkspaceAccessHandler($this);
        
        // delete node data
        $nodes = $this->getSubTree($root);
        foreach ($nodes as $node) {
            $access_handler->removePermission($node["wsp_id"]);

            $object = ilObjectFactory::getInstanceByObjId($node["obj_id"], false);
            if ($object) {
                $object->delete();
            }
        
            $this->deleteReference($node["wsp_id"]);
        }
        
        $this->deleteTree($root);
    }
    
    /**
     * Get all workspace objects of specific type
     * @return array[]
     */
    public function getObjectsFromType(
        string $a_type,
        bool $a_with_data = false
    ) : array {
        return $this->getSubTree(
            $this->getNodeData($this->getRootId()),
            $a_with_data,
            [$a_type]
        );
    }
    
    /**
     * Create personal workspace tree for user
     * @param int $a_user_id
     */
    public function createTreeForUser(int $a_user_id) : void
    {
        $root = ilObjectFactory::getClassByType("wsrt");
        $root = new $root(0);
        $root->create();

        $root_id = $this->createReference($root->getId());
        $this->addTree($a_user_id, $root_id);
        $this->setRootId($root_id);
    }
}
