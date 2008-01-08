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
* class ilRbacSystem
* system function like checkAccess, addActiveRole ...
*  Supporting system functions are required for session management and in making access control decisions.
*  This class depends on the session since we offer the possiblility to add or delete active roles during one session.
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @ingroup ServicesAccessControl
*/
class ilRbacSystem
{
	protected static $user_role_cache = array();
	var $ilias;

	/**
	* Constructor
	* @access	public
	*/
	function ilRbacSystem()
	{
		global $ilDB,$ilErr,$ilias;

		$this->ilias =& $ilias;

		// set db & error handler
		(isset($ilDB)) ? $this->ilDB =& $ilDB : $this->ilDB =& $ilias->db;
		
		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}
	}
	
	/**	
	* checkAccess represents the main method of the RBAC-system in ILIAS3 developers want to use
	*  With this method you check the permissions a use may have due to its roles
	*  on an specific object.
	*  The first parameter are the operation(s) the user must have
	*  The second & third parameter specifies the object where the operation(s) may apply to
	*  The last parameter is only required, if you ask for the 'create' operation. Here you specify
	*  the object type which you want to create.
	* 
	*  example: $rbacSystem->checkAccess("visible,read",23);
	*  Here you ask if the user is allowed to see ('visible') and access the object by reading it ('read').
	*  The reference_id is 23 in the tree structure.
	*  
	* @access	public
	* @param	string		one or more operations, separated by commas (i.e.: visible,read,join)
	* @param	integer		the child_id in tree (usually a reference_id, no object_id !!)
	* @param	string		the type definition abbreviation (i.e.: frm,grp,crs)
	* @return	boolean		returns true if ALL passed operations are given, otherwise false
	*/
	function checkAccess($a_operations,$a_ref_id,$a_type = "")
	{
		global $ilUser,$ilBench;
		
		$ilBench->start("RBAC", "system_checkAccess");

		$result = $this->checkAccessOfUser($ilUser->getId(), $a_operations, $a_ref_id, $a_type);

		$ilBench->stop("RBAC", "system_checkAccess");
		
		return $result;
	}
	
	function checkAccessOfUser($a_user_id, $a_operations, $a_ref_id, $a_type = "")
	{
		global $ilUser, $rbacreview,$ilObjDataCache,$ilDB;

		#echo ++$counter;

		// DISABLED 
		// Check For owner
		// Owners do always have full access to their objects
		// Excluded are the permissions create and perm
		// This method call return all operations that are NOT granted by the owner status 
		if(!$a_operations = $this->__filterOwnerPermissions($a_user_id,$a_operations,$a_ref_id))
		{
			return true;
		}

		
		// get roles using role cache
		$roles = $this->fetchAssignedRoles($a_user_id);
		
		
		// exclude system role from rbac
		if (in_array(SYSTEM_ROLE_ID, $roles))
		{
			return true;		
		}

		if (!isset($a_operations) or !isset($a_ref_id))
		{
			$this->ilErr->raiseError(get_class($this)."::checkAccess(): Missing parameter! ".
							"ref_id: ".$a_ref_id." operations: ".$a_operations,$this->ilErr->WARNING);
		}

		if (!is_string($a_operations))
		{
			$this->ilErr->raiseError(get_class($this)."::checkAccess(): Wrong datatype for operations!",$this->ilErr->WARNING);
		}

		$operations = explode(",",$a_operations);


		foreach ($operations as $operation)
		{
			if ($operation == "create")
			{
				if (empty($a_type))
				{
					$this->ilErr->raiseError(get_class($this)."::CheckAccess(): Expect a type definition for checking a 'create' permission",
											 $this->ilErr->WARNING);
				}
				
				$ops_id = ilRbacReview::_getOperationIdByName($operation."_".$a_type);
			}
			else
			{
				$ops_id = ilRbacReview::_getOperationIdByName($operation);
			}
			
			// Um nur eine Abfrage zu haben
			$in = " IN (";
			$in .= implode(",",ilUtil::quoteArray($roles));
			$in .= ")";

			$q = "SELECT * FROM rbac_pa ".
				 "WHERE rol_id ".$in." ".
				 "AND ref_id = ".$ilDB->quote($a_ref_id)." ";
			$r = $this->ilDB->query($q);

			$ops = array();

			while ($row = $r->fetchRow(MDB2_FETCHMODE_OBJECT))
			{
				$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
			}
			if (in_array($ops_id,$ops))
			{
				continue;
			}
			else
			{
				return false;
			}
		}
		
		return true;
    }
	
	/**
	* check if a specific role has the permission '$a_operation' of an object
	* @access	public
	* @param	integer		reference id of object
	* @param	integer		role id 
	* @param	string		the permission to check
	* @return	boolean
	*/
	function checkPermission($a_ref_id,$a_rol_id,$a_operation)
	{
		global $ilDB;
		
		$ops = array();

		$q = "SELECT ops_id FROM rbac_operations ".
				 "WHERE operation = ".$ilDB->quote($a_operation)." ";
		
		$r = $this->ilDB->query($q);

		while($row = $r->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$ops_id = $row->ops_id;
		}
	
		$q = "SELECT * FROM rbac_pa ".
			 "WHERE rol_id = ".$ilDB->quote($a_rol_id)." ".
			 "AND ref_id = ".$ilDB->quote($a_ref_id)." ";
		
		$r = $this->ilDB->query($q);

		while ($row = $r->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
		}
		return in_array($ops_id,$ops);
	}

	function __filterOwnerPermissions($a_user_id,$a_operations,$a_ref_id)
	{
		global $ilObjDataCache;

		if($a_user_id != $ilObjDataCache->lookupOwner($ilObjDataCache->lookupObjId($a_ref_id)))
		{
			return $a_operations;
		}
		// Is owner
		foreach(explode(",",$a_operations) as $operation)
		{
			if($operation != 'edit_permission' and !preg_match('/^create/',$operation))
			{
				continue;
			}
			if(!strlen($new_ops))
			{
				$new_ops = $operation;
			}
			else
			{
				$new_ops .= (','.$operation);
			}
		}
		return $new_ops;
	}
	
	/**
	 * Fetch assigned roles
	 * This method caches the assigned roles per user  
	 *
	 * @access private
	 * @param int user id
	 * 
	 */
	private function fetchAssignedRoles($a_usr_id)
	{
	 	global $ilUser,$rbacreview;
	 	
		if(isset(self::$user_role_cache[$a_usr_id]) and is_array(self::$user_role_cache))
		{
			return self::$user_role_cache[$a_usr_id];
		}
		return self::$user_role_cache[$a_usr_id] = $rbacreview->assignedRoles($a_usr_id);
	}

} // END class.RbacSystem
?>
