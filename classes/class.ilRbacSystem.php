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
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @package rbac
*/
class ilRbacSystem
{
	/**
	* ilias object
	* @var		object	ilias
	* @access	public
	*/
	var $ilias;

	/**
	* Constructor
	* @access	public
	*/
	function ilRbacSystem()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}
	
	/**	
	* checkAccess represents the main method of the RBAC-system in ILIAS3 developers want to use
	* With this method you check the permissions a use may have due to its roles
	* on an specific object.
	* The first parameter are the operation(s) the user must have
	* The second & third parameter specifies the object where the operation(s) may applie to
	* The last parameter is only required, if you ask for the 'create' operation. Here you specify
	* the object type which you want to create.
	* 
	* example: $rbacSystem->checkAccess("visible,read",23,5);
	* Here you ask if the user is allowed to see ('visible') and access the object by reading it ('read').
	* The object_id is 23 and it is located under object no. 5 under the tree structure.
	*  
	* @access	public
	* @param	string		one or more operations, separated by commas (i.e.: visible,read,join)
	* @param	integer		the child_id in tree (usually a reference_id, no object_id !!)
	* @param	string		the type definition abbreviation (i.e.: frm,grp,crs)
	* @return	boolean		returns true if ALL passed operations are given, otherwise false
	*/
	function checkAccess($a_operations,$a_ref_id,$a_type = "")
	{
		global $tree, $rbacadmin, $rbacreview, $objDefinition;

		if (!isset($a_operations) or !isset($a_ref_id))
		{
			$this->ilias->raiseError(get_class($this)."::checkAccess(): Missing parameter! ".
							"ref_id: ".$a_ref_id." operations: ".$a_operations,$this->ilias->error_obj->WARNING);
		}

		if (!is_string($a_operations))
		{
			$this->ilias->raiseError(get_class($this)."::checkAccess(): Wrong datatype for operations!",$this->ilias->error_obj->WARNING);
		}

		// temp. disabled
		$create = false;
		$operations = explode(",",$a_operations);
		$ops_arr = array();

		foreach ($operations as $operation)
		{
			// Abfrage der ops_id der gewünschten Operation
			$ops_id = getOperationId($operation);
		
			// Case 'create': naturally there is no rbac_pa entry
			// => looking for the next template and compare operation with template permission
			if ($operation == "create")
			{
				if (empty($a_type))
				{
					$this->ilias->raiseError(get_class($this)."::CheckAccess(): Expect a type definition for checking 'create' permission",
											 $this->ilias->error_obj->WARNING);
				}
				
				if ($objDefinition->getSubObjectsAsString($a_type) == "")
				{
					$this->ilias->raiseError(get_class($this)."::CheckAccess(): Unknown type definition given: '".$a_type."'",
											 $this->ilias->error_obj->WARNING);
				}

				// sometimes no tree-object was instated, therefore:
				// TODO: maybe deprecated
				if (!is_object($tree))
				{
					$tree = new ilTree(ROOT_FOLDER_ID);
				}

				$path_ids = $tree->getPathId($a_ref_id);
				array_unshift($path_ids,SYSTEM_FOLDER_ID);
				$parent_roles = $rbacadmin->getParentRoles($path_ids);

				foreach ($parent_roles as $par_rol)
				{
					if (in_array($par_rol["obj_id"],$_SESSION["RoleId"]))
					{
						$ops_arr = $rbacreview->getOperations($par_rol["obj_id"],$a_type,$par_rol["parent"]);

						if (in_array($ops_id,$ops_arr))
						{
							$create = true;
							break;
						}
					}
				}

				if ($create)
				{
					continue;
				}
				else
				{
					return false;
				}

			} // END CASE 'create'
	
			// Um nur eine Abfrage zu haben
			$in = " IN ('";
			$in .= implode("','",$_SESSION["RoleId"]);
			$in .= "')";
			$q = "SELECT * FROM rbac_pa ".
				 "WHERE rol_id ".$in." ".
				 "AND obj_id = '".$a_ref_id."' ";
			$r = $this->ilias->db->query($q);

			$ops = array();
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
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
	* DESCRIPTION MISSING
	* TODO: This method is only used in Object::permObject
	* @access	public
	* @param	integer		ObjectId,
	* @param	integer		RoleIds, 
	* @param	integer		das abzufragende Recht
	* @param	string
	* @return	boolean
	*/
	function checkPermission($Aobj_id,$Arol_id,$Aoperation)
	{
		$ops = array();

		// Abfrage der ops_id der gewünschten Operation
		$q = "SELECT ops_id FROM rbac_operations ".
				 "WHERE operation ='".$Aoperation."'";
		
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id = $row->ops_id;
		}
	
		$q = "SELECT * FROM rbac_pa ".
			 "WHERE rol_id = '".$Arol_id."' ".
			 "AND obj_id = '".$Aobj_id."' ";
		
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
		}
		
		return in_array($ops_id,$ops);
	}

	function getErrorMessage()
	{

	}
	
	function createSession()
	{

	}

	function deleteSession()
	{

	}
	
	/**
	* adds an active role in $_SESSION["RoleId"]
	* @access	public
	*/
	function addActiveRole()
	{
		
	}

	function dropActiveRole()
	{

	}
} // END class.RbacSystem
?>
