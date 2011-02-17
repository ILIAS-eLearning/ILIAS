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

		if(!$a_root_id)
		{
			$this->root_id = $a_tree_id;
		}

		$this->table_tree = 'tree_workspace';
		$this->table_obj_data = 'object_data';
		$this->table_obj_reference = 'object_reference_ws';
		$this->ref_pk = 'wsp_id';
		$this->obj_pk = 'obj_id';
		$this->tree_pk = 'tree';
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
	 */
	public static function lookupObjectId($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$this->obj_pk.
			" FROM ".$this->table_obj_reference.
			" WHERE ".$this->ref_pk." = ".$ilDB->quote($a_node_id, "integer"));
		$res = $ilDB->fetchAssoc($set);

		return $res[$this->obj_pk];
	}
}

?>