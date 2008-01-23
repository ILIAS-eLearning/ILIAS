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

require_once "./classes/class.ilContainer.php";

/**
* Class ilObjFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @extends ilObject
*/
class ilObjFolder extends ilContainer
{
	var $folder_tree;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjFolder($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "fold";
		$this->ilObjContainer($a_id,$a_call_by_reference);
	}

	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}
	
	/**
	 * Clone folder
	 *
	 * @access public
	 * @param int target id
	 * @param int copy id
	 * 
	 */
	public function cloneObject($a_target_id,$a_copy_id)
	{
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
		return $new_obj;
	}

	/**
	* insert folder into grp_tree
	*
	*/
	function putInTree($a_parent)
	{
		global $tree;
		
		if (!is_object($this->folder_tree))
		{
			$this->folder_tree =& $tree; 
		}

		if ($this->withReferences())
		{
			// put reference id into tree
			$this->folder_tree->insertNode($this->getRefId(), $a_parent);
		}
		else
		{
			// put object id into tree
			$this->folder_tree->insertNode($this->getId(), $a_parent);
		}
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
		global $tree;
		
		if($course_ref_id = $tree->checkForParentType($this->getRefId(),'crs') and
			$new_course_ref_id = $tree->checkForParentType($a_target_id,'crs'))
		{
			include_once('Modules/Course/classes/class.ilCourseItems.php');
			$course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id,false);
			$course_items = new ilCourseItems($course_obj,$this->getRefId());
			$course_items->cloneDependencies($a_target_id,$a_copy_id);			
		}
		
		include_once('Services/Tracking/classes/class.ilLPCollections.php');
		$lp_collection = new ilLPCollections($this->getId());
		$lp_collection->cloneCollections($a_target_id,$a_copy_id);		
		
	 	return true;
	}
	
} // END class.ilObjFolder
?>
