<?php
/**
* Class Bookmarks
* Bookmark management
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
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
		global $log, $ilias;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = '".$this->id."'";
		$bm_set = $this->ilias->db->query($q);
		if ($bm_set->numRows() == 0)
		{
			$message = "ilBookmark::read(): Bookmark with id ".$this->id." not found!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}
		else
		{
			$bm = $bm_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setTitle($bm["title"]);
			$this->setTarget($bm["target"]);
			$this->setParent($this->tree->getParentId($this->id));
		}
	}

	/**
	* delete object data
	*/
	function delete()
	{
		$q = "DELETE FROM bookmark_data WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}


	function create()
	{
		$q = 	"INSERT INTO bookmark_data (user_id, title, target, type) ".
				"VALUES ('".$_SESSION["AccountId"]."','".$this->getTitle().
				"','".$this->getTarget()."','bm')";

		$this->ilias->db->query($q);

		$this->setId(getLastInsertId());

		$this->tree->insertNode($this->getId(), $this->getParent());
	}

	function update()
	{
		$q = "UPDATE bookmark_data SET title = '".$this->getTitle().
			"', target = '".$this->getTarget()."' WHERE obj_id = '".$this->getId()."'";
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
	* set description
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
