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
* Class ilObjFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";
//require_once "class.ilGroupTree.php";

class ilObjFolder extends ilObject
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
		$this->ilObject($a_id,$a_call_by_reference);
	}

	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
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
//vd($this->withReferences());exit;
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

	function clone()
	{
		$new_obj = new ilObject();
		$new_obj->setTitle($this->getTitle());
		$new_obj->setType($this->getType());
		$new_obj->setDescription($this->getDescription());
		$new_obj->create();
		$new_ref_id = $new_obj->createReference();
		
		unset($new_obj);
	
		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}
	
	/**
	* statical function to get the group id where the folder is
	* 
	*/
	function __getGroupId($a_folder_ref_id)
	{
		global $ilias, $tree;
		
		$path = $tree->getPathFull($a_folder_ref_id);
		
		foreach ($path as $node)
		{
			if ($node["type"] == "grp")
			{
				return $node["child"];
			}
		}
		
		return false;
	}
} // END class.ilObjFolder
?>
