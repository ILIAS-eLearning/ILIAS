<?php
/**
* bookmark folder
* (note: this class handles personal bookmarks folders only)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
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
		global $log, $ilias;

		$q = "SELECT * FROM bookmark_data WHERE obj_id = '".$this->id."'";
		$bmf_set = $this->ilias->db->query($q);
		if ($bmf_set->numRows() == 0)
		{
			$message = "ilBookmarkFolder::read(): Bookmark Folder with id ".$this->id." not found!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}
		else
		{
			$bmf = $bmf_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setTitle($bmf["title"]);
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

	/**
	* create personal bookmark tree
	*/
	function createNewBookmarkTree()
	{
		$this->tree->addTree($this->tree->getTreeId(), 1);
	}

	/**
	* creates new bookmark folder in db
	*
	* note: parent and title must be set
	*/
	function create()
	{
		$q = 	"INSERT INTO bookmark_data (user_id, title, target, type) ".
				"VALUES ('".$_SESSION["AccountId"]."','".$this->getTitle()."','','bmf')";
		$this->ilias->db->query($q);

		$this->setId(getLastInsertId());

		$this->tree->insertNode($this->getId(), $this->getParent());
	}

	function update()
	{
		$q = "UPDATE bookmark_data SET title = '".$this->getTitle()."' WHERE obj_id = '".$this->getId()."'";
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

}
?>
