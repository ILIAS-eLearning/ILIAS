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

}
?>