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
		global $ilias;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = ".$this->ilias->db->quote($this->getId());
		$bm_set = $this->ilias->db->query($q);
		if ($bm_set->numRows() == 0)
		{
			$message = "ilBookmark::read(): Bookmark with id ".$this->id." not found!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}
		else
		{
			$bm = $bm_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setTitle($bm["title"]);
			$this->setDescription($bm["description"]);
			$this->setTarget($bm["target"]);
			$this->setParent($this->tree->getParentId($this->id));
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


	function create()
	{
		$q = sprintf(
				"INSERT INTO bookmark_data (user_id, title,description, target, type) ".
				"VALUES (%s,%s,%s,%s,%s)",
				$this->ilias->db->quote($_SESSION["AccountId"]),
				$this->ilias->db->quote($this->getTitle()),
				$this->ilias->db->quote($this->getDescription()),
				$this->ilias->db->quote($this->getTarget()),
				$this->ilias->db->quote('bm')
			);

		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());
		$this->tree->insertNode($this->getId(), $this->getParent());
	}

	function update()
	{
		$q = sprintf(
				"UPDATE bookmark_data SET title=%s,description=%s,target=%s ".
				"WHERE obj_id=%s",
				$this->ilias->db->quote($this->getTitle()),
				$this->ilias->db->quote($this->getDescription()),
				$this->ilias->db->quote($this->getTarget()),
				$this->ilias->db->quote($this->getId())
			);
		$this->ilias->db->query($q);
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
}
?>
