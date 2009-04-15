<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class Bookmarks
* Bookmark management
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
*
*/
class ilBookmark
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_Id;

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tree;

	var $title;
	var $description;
	var $target;
	var $id;
	var $parent;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmark($a_bm_id = 0, $a_tree_id = 0)
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

		$this->id = $a_bm_id;

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
		global $ilias, $ilDB;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = ".
			$ilDB->quote($this->getId(), "integer");
		$bm_set = $ilDB->query($q);
		if ($ilDB->numRows($bm_set) == 0)
		{
			$message = "ilBookmark::read(): Bookmark with id ".$this->id." not found!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}
		else
		{
			$bm = $ilDB->fetchAssoc($bm_set);
			$this->setTitle($bm["title"]);
			$this->setDescription($bm["description"]);
			$this->setTarget($bm["target"]);
			$this->setParent($this->tree->getParentId($this->id));
		}
	}

	/**
	* Delete bookmark data
	*/
	function delete()
	{
		global $ilDB;
		
		$q = "DELETE FROM bookmark_data WHERE obj_id = ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);
	}


	/**
	* Create new bookmark item
	*/
	function create()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId("bookmark_data"));
		$q = sprintf(
				"INSERT INTO bookmark_data (obj_id, user_id, title,description, target, type) ".
				"VALUES (%s,%s,%s,%s,%s,%s)",
				$ilDB->quote($this->getId(), "integer"),
				$ilDB->quote($_SESSION["AccountId"], "integer"),
				$ilDB->quote($this->getTitle(), "text"),
				$ilDB->quote($this->getDescription(), "text"),
				$ilDB->quote($this->getTarget(), "text"),
				$ilDB->quote('bm', "text")
			);

		$ilDB->manipulate($q);
		$this->tree->insertNode($this->getId(), $this->getParent());
	}

	/**
	* Update bookmark item
	*/
	function update()
	{
		global $ilDB;
		
		$q = sprintf(
				"UPDATE bookmark_data SET title=%s,description=%s,target=%s ".
				"WHERE obj_id=%s",
				$ilDB->quote($this->getTitle(), "text"),
				$ilDB->quote($this->getDescription(), "text"),
				$ilDB->quote($this->getTarget(), "text"),
				$ilDB->quote($this->getId(), "integer")
			);
		$ilDB->manipulate($q);
	}


	/*
	* set id
	* @access	public
	* @param	integer
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	/**
	* set title
	* @access	public
 	* @param	string
	*/
	function setTitle($a_str)
	{
		$this->title = $a_str;
	}

	function getTitle()
	{
		return $this->title;
	}
	/**
	* set description
	* @access	public
 	* @param	string
	*/
	function setDescription($a_str)
	{
		$this->description = $a_str;
	}

	function getDescription()
	{
		return $this->description;
	}

	/**
	* set target
	* @access	public
	* @param	string
	*/
	function setTarget($a_target)
	{
		$this->target = $a_target;
	}


	function getTarget()
	{
		return $this->target;
	}

	function setParent($a_parent_id)
	{
		$this->parent = $a_parent_id;
	}

	function getParent()
	{
		return $this->parent;
	}
	
	/**
	* get type of a given id
	* @param number id
	*/
	public static function _getTypeOfId($a_id)
	{
		global $ilias, $ilDB;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = ".
			$ilDB->quote($a_id, "integer");
		$bm_set = $ilDB->query($q);
		if ($ilDB->numRows($bm_set) == 0)
		{
			return null;
		}
		else
		{
			$bm = $ilDB->fetchAssoc($bm_set);
			return $bm["type"];
		}
	}
}
?>
