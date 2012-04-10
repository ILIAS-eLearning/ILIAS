<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Tree/classes/class.ilTree.php";

/**
 * Tree handler for personal workspace
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
 */
class ilWorkspaceTree extends ilTree
{
	public function __construct($a_tree_id, $a_root_id = 0)
	{
		parent::__construct($a_tree_id, $a_root_id);

		$this->table_tree = 'tree_workspace';
		$this->table_obj_data = 'object_data';
		$this->table_obj_reference = 'object_reference_ws';
		$this->ref_pk = 'wsp_id';
		$this->obj_pk = 'obj_id';
		$this->tree_pk = 'tree';

		// ilTree sets it to ROOT_FOLDER_ID if not given...
		if(!$a_root_id)
		{
			$this->root_id = $this->readRootId();
		}
	}

	/**
	 * Create workspace reference for object
	 *
	 * @param int $a_object_id
	 * @return int node id
	 */
	public function createReference($a_object_id)
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId($this->table_obj_reference);

		$fields = array($this->ref_pk => array("integer", $next_id),
			$this->obj_pk => array("integer", $a_object_id));

		$ilDB->insert($this->table_obj_reference, $fields);
		
		return $next_id;
	}

	/**
	 * Get object id for node id
	 *
	 * @param int $a_node_id
	 * @return int object id
	 */
	public function lookupObjectId($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$this->obj_pk.
			" FROM ".$this->table_obj_reference.
			" WHERE ".$this->ref_pk." = ".$ilDB->quote($a_node_id, "integer"));
		$res = $ilDB->fetchAssoc($set);

		return $res[$this->obj_pk];
	}
	
	
	/**
	 * Get node id for object id
	 * 
	 * As we do not allow references in workspace this should not be ambigious
	 *
	 * @param int $a_obj_id
	 * @return int node id
	 */
	public function lookupNodeId($a_obj_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT ".$this->ref_pk.
			" FROM ".$this->table_obj_reference.
			" WHERE ".$this->obj_pk." = ".$ilDB->quote($a_obj_id, "integer"));
		$res = $ilDB->fetchAssoc($set);

		return $res[$this->ref_pk];
	}
	
	/**
	 * Get owner for node id
	 *
	 * @param int $a_node_id
	 * @return int object id
	 */
	public function lookupOwner($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT tree".
			" FROM ".$this->table_obj_reference.
			" JOIN ".$this->table_tree." ON (".$this->table_obj_reference.".".$this->ref_pk." = ".$this->table_tree.".child)".
			" WHERE ".$this->ref_pk." = ".$ilDB->quote($a_node_id, "integer"));
		$res = $ilDB->fetchAssoc($set);

		return $res["tree"];
	}

	/**
	 * Add object to tree
	 *
	 * @param int $a_parent_node_id
	 * @param int $a_object_id
	 * @return int node id
	 */
	public function insertObject($a_parent_node_id, $a_object_id)
	{
		$node_id = $this->createReference($a_object_id);
		$this->insertNode($node_id, $a_parent_node_id);
		return $node_id;
	}

	/**
	 * Delete object from reference table
	 * 
	 * @param int $a_node_id
	 * @return bool
	 */
	public function deleteReference($a_node_id)
	{
		global $ilDB;

		$query = "DELETE FROM ".$this->table_obj_reference.
			" WHERE ".$this->ref_pk." = ".$ilDB->quote($a_node_id, "integer");
		return $ilDB->manipulate($query);
	}
		
	/**
	 * Remove all tree and node data 
	 */
	public function cascadingDelete()
	{		
		$root_id = $this->readRootId();		
		if(!$root_id)
		{
			return;
		}
		
		$root = $this->getNodeData($root_id);
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		$access_handler = new ilWorkspaceAccessHandler($this);
		
		// delete node data
		$nodes = $this->getSubTree($root);
		foreach($nodes as $node)
		{			
			$access_handler->removePermission($node["wsp_id"]);

			$object = ilObjectFactory::getInstanceByObjId($node["obj_id"], false);
			if($object)
			{
				$object->delete();
			}
		
			$this->deleteReference($node["wsp_id"]);					 
		}
		
	    $this->deleteTree($root);
	}
}

?>