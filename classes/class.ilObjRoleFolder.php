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
* Class ilObjRoleFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRoleFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRoleFolder($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "rolf";
		$this->ilObject($a_id,$a_call_by_reference);
	}


	/**
	* delete role folder
	*/
	function delete()
	{
		global $rbacadmin, $rbacreview;

		$roles = $rbacreview->getRolesOfRoleFolder($this->getId());

		// FIRST DELETE ALL LOCAL/BASE ROLES OF FOLDER
		require_once("classes/class.ilObjRole.php");
		$obj = new ilObjRole();
		
		foreach ($roles as $role)
		{
			$role_obj =& $this->ilias->obj_factory->getInstanceByObjId($role);
			$role_obj->delete();
		}

		// DELETE ROLE FOLDER
		parent::delete();
		return true;
	}

	function clone($a_parent_ref)
	{
		// DO NOTHING ROLE FOLDERS AREN'T COPIED
		//	$new_id = parent::clone($a_parent_ref);
		return true;
	}

	function getSubObjects()	
	{
		return false;
	} //function

} // class
?>
