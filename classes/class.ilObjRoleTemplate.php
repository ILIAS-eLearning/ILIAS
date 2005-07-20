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
* Class ilObjRoleTemplate
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRoleTemplate extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRoleTemplate($a_id = 0,$a_call_by_reference = false)
	{
		$this->type = "rolt";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* copy all properties and subobjects of a role template.
	* DISABLED
	* @access	public
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		// DISABLED
		return false;

		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// put here role template specific stuff
		
		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete role template and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// put here role template specific stuff
		global $rbacadmin;

		// delete rbac permissions
		$rbacadmin->deleteTemplate($this->getId(),$_GET["ref_id"]);

		// always call parent delete function at the end!!
		return (parent::delete()) ? true : false;
	}

	function isInternalTemplate()
	{
		if (substr($this->getTitle(),0,3) == "il_")
		{
			return true;
		}
		
		return false;
	}
	
	function getFilterOfInternalTemplate()
	{
		global $objDefinition;
		
		$filter = array();

		switch($this->getTitle())
		{
			case "il_icrs_admin":
			case "il_icrs_member":
				$filter = array_keys($objDefinition->getSubObjects('icrs',false));
				$filter[] = 'icrs';
				break;

			case "il_grp_admin":
			case "il_grp_member":
			case "il_grp_status_closed":
			case "il_grp_status_open":
				$filter = array_keys($objDefinition->getSubObjects('grp',false));
				$filter[] = 'grp';
				break;
				
			case "il_crs_admin":
			case "il_crs_tutor":
			case "il_crs_member":
				$filter = array_keys($objDefinition->getSubObjects('crs',false));
				$filter[] = 'crs';
				break;
		}
		
		return $filter;
	}
} // END class.ilObjRoleTemplate
?>
