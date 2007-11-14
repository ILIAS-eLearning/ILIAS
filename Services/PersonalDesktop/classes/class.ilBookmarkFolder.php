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
* bookmark folder
* (note: this class handles personal bookmarks folders only)
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
*
*/
class ilBookmarkFolder
{
	/**
	* tree
	* @var object
	* @access private
	*/
	var $tree;

	/**
	* ilias object
	* @var object ilias
	* @access private
	*/
	var $ilias;

	var $id;
	var $title;
	var $parent;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmarkFolder($a_bmf_id = 0, $a_tree_id = 0)
	{
		global $ilias;

		// Initiate variables
		$this->ilias =& $ilias;
		if ($a_tree_id == 0)
		{
			$a_tree_id = $_SESSION["AccountId"];
		}

		$this->tree = new ilTree($a_tree_id);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
		$this->id = $a_bmf_id;

		if(!empty($this->id))
		{
			$this->read();
		}
	}

	/**
	* read bookmark folder data from db
	*/
	function read()
	{
		global $ilias;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = ".$this->ilias->db->quote($this->getId());
		$bmf_set = $this->ilias->db->query($q);
		if ($bmf_set->numRows() == 0)
		{
			$message = "ilBookmarkFolder::read(): Bookmark Folder with id ".$this->getId()." not found!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}
		else
		{
			$bmf = $bmf_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setTitle($bmf["title"]);
			$this->setParent($this->tree->getParentId($this->getId()));
		}
	}

	/**
	* delete object data
	*/
	function delete()
	{
		$q = "DELETE FROM bookmark_data WHERE obj_id = ".$this->ilias->db->quote($this->getId());
		$this->ilias->db->query($q);
	}

	/**
	* create personal bookmark tree
	*/
	function createNewBookmarkTree()
	{
		global $ilDB;

		/*
		$q = "INSERT INTO bookmark_data (user_id, title, target, type) ".
			"VALUES ('".$this->tree->getTreeId()."','dummy_folder','','bmf')";
		$ilDB->query($q);*/
		//$this->tree->addTree($this->tree->getTreeId(), $ilDB->getLastInsertId());
		$this->tree->addTree($this->tree->getTreeId(), 1);
	}

	/**
	* creates new bookmark folder in db
	*
	* note: parent and title must be set
	*/
	function create()
	{
		$q = sprintf(
				"INSERT INTO bookmark_data (user_id, title, type) ".
				"VALUES (%s,%s,%s)",
				$this->ilias->db->quote($_SESSION["AccountId"]),
				$this->ilias->db->quote($this->getTitle()),
				$this->ilias->db->quote('bmf')
			);

		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());
		$this->tree->insertNode($this->getId(), $this->getParent());
	}
	function update()
	{
		$q = sprintf(
				"UPDATE bookmark_data SET title=%s ".
				"WHERE obj_id=%s",
				$this->ilias->db->quote($this->getTitle()),
				$this->ilias->db->quote($this->getId())
			);
		$this->ilias->db->query($q);
	}


	function getId()
	{
		return $this->id;
	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getTitle()
	{
		return $this->title;
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	function getParent()
	{
		return $this->parent;
	}

	function setParent($a_parent_id)
	{
		$this->parent = $a_parent_id;
	}

	/**
	* lookup bookmark folder title
	*/
	function _lookupTitle($a_bmf_id)
	{
		global $ilDB;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = ".$ilDB->quote($a_bmf_id);
		$bmf_set = $ilDB->query($q);
		$bmf = $bmf_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $bmf["title"];
	}

	/**
	* static
	*/
	function getObjects($a_id)
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');

		if(empty($a_id))
		{
			$a_id = $tree->getRootId();
		}

		$childs = $tree->getChilds($a_id, "title");

		$objects = array();
		$bookmarks = array();

		foreach ($childs as $key => $child)
		{
			switch ($child["type"])
			{
				case "bmf":
					$objects[] = $child;
					break;

				case "bm":
					$bookmarks[] = $child;
					break;
			}
		}
		foreach ($bookmarks as $key => $bookmark)
		{
			$objects[] = $bookmark;
		}
		return $objects;
	}
	
	/**
	* Get number of folders and bookmarks for current user.
	*/
	function _getNumberOfObjects()
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');

		$root_node = $tree->getNodeData($tree->getRootId());
		
		if ($root_node["lft"] != "")
		{
			$bmf = $tree->getSubTree($root_node, false, "bmf");
			$bm = $tree->getSubTree($root_node, false, "bm");
		}
		else
		{
			$bmf = array("dummy");
			$bm = array();
		}
		
		return array("folders" => (int) count($bmf) - 1, "bookmarks" => (int) count($bm));
	}

	
	/**
	* static
	*/
	function getObject($a_id)
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');

		if(empty($a_id))
		{
			$a_id = $tree->getRootId();
		}

		$object = $tree->getNodeData($a_id);
		return $object;
	}

	function isRootFolder($a_id)
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');

		if ($a_id == $tree->getRootId())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function getRootFolder()
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');

		return $tree->getRootId();
	}

	function _getParentId($a_id)
	{
		$a_tree_id = $_SESSION["AccountId"];
		$tree = new ilTree($a_tree_id);
		$tree->setTableNames('bookmark_tree','bookmark_data');
		return $tree->getParentId($a_id);
	}

}
?>
