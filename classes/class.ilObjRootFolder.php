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
* Class ilObjRootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.ilObjRootFolder.php,v 1.4 2003/05/16 13:39:22 smeyer Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRootFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRootFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "root";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/*
	* deletion (no deletion of root folder)
	*/
	function delete()
	{
		global $rbacadmin, $rbacreview;
		
		$this->ilias->raiseError("ilObjRootFolder::delete(): Can't delete root folder", $this->ilias->error_obj->MESSAGE);

		// GET ALL ROLES OF ROLE FOLDER
		/*
		$all_roles = $rbacreview->getRolesOfRoleFolder($this->getId());
		
		// FIRST DELETE THIS ROLES
		foreach($all_roles as $role_id)
		{
			$role_obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
			$role_obj->delete();
		}
		// NOW DELETE ROLE FOLDER
		parent::delete();*/
	}
} // END class.RootFolder
?>
