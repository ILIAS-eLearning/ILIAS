<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjWorkspaceFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjWorkspaceFolder extends ilObject2
{
	var $folder_tree;

	/**
	 * @var ilObjUser
	 */
	protected $current_user;

	/**
	 * Constructor
	 * @access	public
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function __construct($a_id = 0, $a_reference = true)
	{
		global $DIC;

		parent::__construct($a_id, $a_reference);

		$this->current_user = $DIC->user();
	}

	function initType()
	{
		$this->type = "wfld";
	}

	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}
	
	/**
	 * Clone folder
	 *
	 * @access public
	 * @param object clone
	 * @param int target id
	 * @param int copy id
	 */
	public function doCloneObject($a_new_object, $a_target_id, $a_copy_id = 0)
	{
		
	}

	/**
	 * Clone object dependencies (crs items, preconditions)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		
	}

	/**
	* Get container view mode
	*/
	function getViewMode()
	{		
		return ilContainer::VIEW_BY_TYPE;
	}

	/**
	* Add additional information to sub item, e.g. used in
	* courses for timings information etc.
	*/
	function addAdditionalSubItemInformation(&$a_item_data)
	{
		
	}

	/**
	 * @return bool
	 */
	function gotItems($node_id)
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($this->current_user->getId());
		$nodes = $tree->getChilds($node_id, "title");

		if(sizeof($nodes))
		{
			return true;
		}
		return false;
	}

} 

?>