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
		parent::__construct();

		if(!$a_root_id)
		{
			$this->root_id = $a_tree_id;
		}

		$this->table_tree     = 'tree_workspace';
		$this->table_obj_data = 'object_data';
		$this->table_obj_reference = 'object_reference_workspace';
		$this->ref_pk = 'wsp_id';
		$this->obj_pk = 'obj_id';
		$this->tree_pk = 'tree';
	}

	public static function lookupObjectId($a_node_id)
	{

		
	}

	public function addNode($a_parent_node_id, $a_object_id)
	{

		
	}
}

?>