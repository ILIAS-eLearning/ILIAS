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
	
	var $name;
	var $target;
	var $id;
	var $parent;
	
	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmark($bm_id = 0, $tree_id = 0)
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

	}

	function create()
	{
		$q = 	"INSERT INTO bookmark_data (user_id, title, target, type) ".
				"VALUES ('".$_SESSION["AccountId"]."','".$this->getName().
				"','".$this->getTarget()."','bm')";
echo $q."<br>";
		$this->ilias->db->query($q);

		$this->setId(getLastInsertId());

		$this->tree->insertNode($this->getId(), $this->getParent());
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
	function setName($a_str)
	{
		$this->name = $a_str;
	}

	function getName()
	{
		return $this->name;
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
