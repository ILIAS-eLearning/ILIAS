<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* search
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id: $Id$
* 
* @package application
*/
require_once "./classes/class.ilTree.php";


class ilSearchFolder
{
	// OBJECT VARIABLES
	var $s_tree;
	var $ilias;

	var $user_id;
	var $folder_id;
	var $parent_id;
	var $title;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearchFolder($a_user_id,$a_folder_id)
	{
		global $ilias;

		define("TABLE_SEARCH_TREE","search_tree");
		define("TABLE_SEARCH_DATA","search_data");
		define("ROOT_FOLDER_ID",1);

		$this->ilias =& $ilias;
		

		$this->user_id = $a_user_id;
		$this->folder_id = $a_folder_id;

		$this->__init();
		$this->__initTreeObject();
	}

	// SET/GET
	function setFolderId($a_folder_id)
	{
		$this->folder_id = $a_folder_id;
	}
	function getFolderId()
	{
		return $this->folder_id;
	}
	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
		
	}
	function getUserId()
	{
		return $this->user_id;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}

	function getChilds()
	{
		return $this->s_tree->getChilds($this->getFolderId());
	}
	function setParentId($a_parent_id)
	{
		$this->parent_id = $a_parent_id;
	}
	
	function getParentId()
	{
		return $this->parent_id;
	}

	function delete($a_folder_id)
	{
		$subtree = $this->s_tree->getSubTree($this->s_tree->getNodeData($a_folder_id));
		
		foreach($subtree as $node)
		{
			// DELETE DATA ENTRIES
			$query = "DELETE FROM ".TABLE_SEARCH_DATA." ".
				"WHERE user_id = '".$this->getUserId()."' ".
				"AND obj_id = '".$node["obj_id"]."'";

			$res = $this->ilias->db->query($query);
		}
		// FINALLY DELETE SUBTREE
		$this->s_tree->deleteTree($this->s_tree->getNodeData($a_folder_id));

		return true;
	}
	
	function assignResult(&$search_result)
	{
		if(!$this->__treeExists())
		{
			$this->__createNewTree();
		}
		// CREATE RESULT
		$query = "INSERT INTO ".TABLE_SEARCH_DATA ." ".
			"SET user_id = '".$this->getUserId()."', ".
			"title = '".$search_result->getTitle()."', ".
			"target = '".$search_result->getTarget()."', ".
			"type = 'sea'";
		
		$res = $this->ilias->db->query($query);

		$this->s_tree->insertNode(getLastInsertId(),$this->getFolderId());

		return true;
	}

	function &create($a_title)
	{
		// CHECK USER TREE IF HAS BEEN CREATED
		if(!$this->__treeExists())
		{
			$this->__createNewTree();
		}

		// CREATE FOLDER
		$query = "INSERT INTO ".TABLE_SEARCH_DATA ." ".
			"SET user_id = '".$this->getUserId()."', ".
			"title = '".addslashes($a_title)."', ".
			"type = 'seaf'";
		
		$res = $this->ilias->db->query($query);

		$this->s_tree->insertNode(getLastInsertId(),$this->getFolderId());

		$new_obj =& new ilSearchFolder($this->getUserId(),$this->getFolderId());
		$new_obj->setTitle($a_title);

		return $new_obj;
	}

	function getSubtree()
	{
		$subtree = $this->s_tree->getSubtree($this->s_tree->getNodeData($this->getFolderId()));

		// FILTER FOLDERS
		foreach($subtree as $node)
		{
			if($node["type"] == "seaf")
			{
				$filtered[] = $node;
			}
		}
		return count($filtered) ? $filtered : array();
	}
	// PRIVATE METHODS
	function __init()
	{
		$query = "SELECT * FROM ".TABLE_SEARCH_TREE.", ".TABLE_SEARCH_DATA." ".
			"WHERE child = obj_id ".
			"AND child = '".$this->getFolderId()."' ".
			"AND tree = '".$this->getUserId()."'";

		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setParentId($row->parent);
		}
	}

	function  __initTreeObject()
	{
		$this->s_tree = new ilTree($this->getUserId(),ROOT_FOLDER_ID);
		$this->s_tree->setTableNames(TABLE_SEARCH_TREE,TABLE_SEARCH_DATA);

		return true;
	}

	function __treeExists()
	{
		$query = "SELECT tree FROM ".TABLE_SEARCH_TREE." ".
			"WHERE tree = ".$this->getUserId();
		
		$res = $this->ilias->db->query($query);

		return $res->numRows() ? true : false;
	}

	function __createNewTree()
	{
		$this->s_tree->addTree($this->getUserId(),ROOT_FOLDER_ID);

		// ADD ENTRY search_data
		$query = "INSERT INTO ".TABLE_SEARCH_DATA." ".
			"SET obj_id = '1', ".
			"user_id = '".$this->getUserId()."', ".
			"type = 'seaf'";

		$res = $this->ilias->db->query($query);
	}
	
} // END class.Search
?>