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
	var $name;
	var $parent;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmarkFolder($bmf_id = 0, $tree_id = 0)
	{
		global $ilias;

		// Initiate variables
		$this->ilias =& $ilias;
		if ($tree_id == 0)
		{
			$tree_id = $_SESSION["AccountId"];
		}

		$this->tree = new ilTree($tree_id);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
		//$this->root_id = $this->tree->readRootId();
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
	* note: parent and name must be set
	*/
	function create()
	{
		$q = 	"INSERT INTO bookmark_data (user_id, title, target, type) ".
				"VALUES ('".$_SESSION["AccountId"]."','".$this->getName()."','','bmf')";
		$this->ilias->db->query($q);

		$this->setId(getLastInsertId());

		$this->tree->insertNode($this->getId(), $this->getParent());
	}

	function getId()
	{
		return $this->id;
	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getName()
	{
		return $this->name;
	}

	function setName($a_name)
	{
		$this->name = $a_name;
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