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
* @package ilias-search
*/
include_once "./Services/Tree/classes/class.ilTree.php";


class ilSearchFolder
{
	// OBJECT VARIABLES
	var $s_tree;
	var $ilias;

	var $user_id;
	var $root_id;
	var $folder_id;
	var $parent_id;
	var $title;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearchFolder($a_user_id,$a_folder_id = 0)
	{
		global $ilias;

		define("TABLE_SEARCH_TREE","search_tree");
		define("TABLE_SEARCH_DATA","search_data");

		$this->ilias =& $ilias;

		$this->user_id = $a_user_id;
		
		$this->__readRootId();

		// IF NO FOLDER ID IS GIVEN DEFAULT TO ROOT ID
		$this->setFolderId($a_folder_id ? $a_folder_id : $this->getRootId());

		$this->__initTreeObject();

		if(!$this->__treeExists())
		{
			$this->__createNewTree();
		}
		$this->__init();

		// CHECK USER TREE IF HAS BEEN CREATED
	}

	// SET/GET
	function getType()
	{
		return "seaf";
	}
	
	function getRootId()
	{
		return $this->root_id;
	}
	function setRootId($a_root_id)
	{
		$this->root_id = $a_root_id;
	}
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
		return $this->s_tree->getChilds($this->getFolderId(),"type","DESC");
	}
	function hasSubfolders()
	{
		$childs = $this->getChilds();

		return $childs[0]["type"] == "seaf" ? true : false;
	}
	function hasResults()
	{
		$childs = $this->getChilds();

		return $childs[count($childs)-1]["type"] == "sea" ? true : false;
	}
	function countFolders()
	{
		$childs = $this->s_tree->getChilds($this->getRootId(),"type","DESC");

		$counter = 0;
		while(true)
		{
			if($childs[$counter]["type"] != "seaf")
			{
				break;
			}
			++$counter;
		}
		return $counter;
	}

	function getPath()
	{
		return $this->s_tree->getPathFull($this->getFolderId(),$this->getRootId());
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
		global $ilDB;
		
		$subtree = $this->s_tree->getSubTree($this->s_tree->getNodeData($a_folder_id));
		
		foreach($subtree as $node)
		{
			// DELETE DATA ENTRIES
			$query = "DELETE FROM ".TABLE_SEARCH_DATA." ".
				"WHERE user_id = ".$ilDB->quote($this->getUserId() ,'integer')." ".
				"AND obj_id = ".$ilDB->quote($node['obj_id']);
			$res = $ilDB->manipulate($query);
		}
		// FINALLY DELETE SUBTREE
		$this->s_tree->deleteTree($this->s_tree->getNodeData($a_folder_id));

		return true;
	}
	
	function assignResult(&$search_result)
	{
		global $ilDB;

		if(!$this->__treeExists())
		{
			$this->__createNewTree();
		}
		$next_id = $ilDB->nextId(TABLE_SEARCH_DATA);
		// CREATE RESULT
		$query = "INSERT INTO ".TABLE_SEARCH_DATA ." (obj_id,user_id,title,target,type) ".
			"VALUES( ".
			$ilDB->quote($next_id, 'integer').", ".
			$ilDB->quote($this->getUserId() ,'integer').", ".
			$ilDB->quote($search_result->getTitle() ,'text').", ".
			$ilDB->quote($search_result->getTarget() ,'text').", ".
			$ilDB->quote('sea' ,'text').
			")";
		$res = $ilDB->manipulate($query);

		$this->s_tree->insertNode($next_id,$this->getFolderId());

		return true;
	}

	function updateTitle($a_title)
	{
		global $ilDB;
		
		$query = "UPDATE ".TABLE_SEARCH_DATA." ".
			"SET title = ".$ilDB->quote($a_title ,'text')." ".
			"WHERE obj_id = ".$ilDB->quote($this->getFolderId() ,'integer')." ".
			"AND user_id = ".$ilDB->quote($this->getUserId() ,'integer')." ";

		$res = $ilDB->manipulate($query);

		return true;
	}

	function &create($a_title)
	{
		global $ilDB;
		
		// CREATE FOLDER
		$next_id = $ilDB->nextId(TABLE_SEARCH_DATA);

		$query = "INSERT INTO ".TABLE_SEARCH_DATA ." (obj_id,user_id,title,type) ".
			"VALUES( ".
			$ilDB->quote($next_id, 'integer').", ".
			$ilDB->quote($this->getUserId() ,'integer').", ".
			$ilDB->quote($a_title ,'text').", ".
			$ilDB->quote('seaf' ,'text').
			")";
		$res = $ilDB->manipulate($query);
				
		$this->s_tree->insertNode($next_id,$this->getFolderId());

		$new_obj =& new ilSearchFolder($this->getUserId(),$this->getFolderId());
		$new_obj->setTitle($a_title);

		return $new_obj;
	}
	function getTree()
	{
		$tmp_folder_id = $this->getFolderId();

		$this->setFolderId($this->getRootId());
		
		$tree_data = $this->getSubtree();
		$this->setFolderId($tmp_folder_id);

		return $tree_data;
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
		global $ilDB;
		
		$query = "SELECT * FROM ".TABLE_SEARCH_TREE.", ".TABLE_SEARCH_DATA." ".
			"WHERE child = obj_id ".
			"AND child = ".$ilDB->quote($this->getFolderId() ,'integer')." ".
			"AND tree = ".$ilDB->quote($this->getUserId() ,'integer')." ";
		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setParentId($row->parent);
		}
	}

	function  __initTreeObject()
	{
		$this->s_tree = new ilTree($this->getUserId(),$this->getRootId());
		$this->s_tree->setTableNames(TABLE_SEARCH_TREE,TABLE_SEARCH_DATA);

		return true;
	}

	function __treeExists()
	{
		global $ilDB;
		
		$query = "SELECT tree FROM ".TABLE_SEARCH_TREE." ".
			"WHERE tree = ".$ilDB->quote($this->getUserId() ,'integer');
		
		$res = $this->ilias->db->query($query);

		return $res->numRows() ? true : false;
	}

	function __createNewTree()
	{
		global $ilDB;
		
		// ADD ENTRY search_data
		$next_id = $ilDB->nextId(TABLE_SEARCH_DATA);
		$query = "INSERT INTO ".TABLE_SEARCH_DATA." (obj_id,user_id,type) ".
			"VALUES( ".
			$ilDB->quote($next_id ,'integer').", ".
			$ilDB->quote($this->getUserId() ,'integer').", ".
			$ilDB->quote('seaf' ,'text').
			")";
		$res = $ilDB->manipulate($query);

		$root_id = $next_id;

		$this->s_tree->addTree($this->getUserId(),$root_id);

		// SET MEMBER VARIABLES
		$this->setFolderId($root_id);
		$this->setRootId($root_id);

		return true;
	}

	function __readRootId()
	{
		global $ilDB;
		
		$query = "SELECT child FROM ".TABLE_SEARCH_TREE." ".
			"WHERE tree = ".$ilDB->quote($this->getUserId() ,'integer')." ".
			"AND parent = 0";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setRootId($row->child);
		}
		return true;
	}

} // END class.Search
?>
