<?php
/**
* Class RoleObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class RoleObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function RoleObject($a_id = 0,$a_call_by_reference = false)
	{
		$this->Object($a_id,$a_call_by_reference);
		$this->type = "role";
	}


	/**
	* delete a role object
	* @access	public
	*/
	function deleteObject($a_obj_id, $a_parent, $a_tree_id = 1)
	{
		global $tree, $rbacadmin;
		
		if($rbacadmin->isAssignable($a_obj_id,$a_parent))
		{
			// IT'S THE BASE ROLE
			$rbacadmin->deleteRole($a_obj_id,$a_parent);
			
			//remove role entry in object_data
			deleteObject($a_rol_id);
			
			//TODO: delete references	
		}
		else
		{
			// INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($a_obj_id,$a_parent);

			//TODO: delete references	
		}

		return true;
	}


	/**
	* update a role object
	* @access	public
	* @param	array	object data of role
	* @return	boolean
	*/
	function update()
	{
		global $rbacsystem, $rbacadmin;

		// check if role title is unique
		if ($rbacadmin->roleExists($this->getTitle()))
		{
			$this->ilias->raiseError("A role with the name '".$this->getTitle().
				 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
		}

		parent::update();
	}


	/**
	* save permission templates of a role object
	* @access	public
	*/
	function permSaveObject($a_perm, $a_stop_inherit, $a_type, $a_template_perm, $a_recursive)
	{
		global $tree, $rbacsystem, $rbacadmin;

		// SET TEMPLATE PERMISSIONS
		if ($rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{

			// delete all template entries
			$rbacadmin->deleteRolePermission($this->id,$_GET["ref_id"]);

			if (empty($a_template_perm))
			{
				$a_template_perm = array();
			}

			foreach ($a_template_perm as $key => $ops_array)
			{
				// sets new template permissions
				$rbacadmin->setRolePermission($this->id, $key,$ops_array, $_GET["ref_id"]);
			}

			// CHANGE ALL EXISTING OBJECT UNDER PARENT NODE OF ROLE FOLDER
			// BUT DON'T CHANGE PERMISSIONS OF SUBTREE OBJECTS IF INHERITANCE WAS STOPED
			if ($a_recursive)
			{
				$parent_obj = $_GET["parent_parent"];
				// IF PARENT NODE IS SYTEM FOLDER START AT ROOT FOLDER
				if ($parent_obj == SYSTEM_FOLDER_ID)
				{
					$object_id = ROOT_FOLDER_ID;
					$parent = 0;
				}
				else
				{
					$node_data = $tree->getParentNodeData($_GET["ref_id"]);
					$object_id = $node_data["obj_id"];
					$parent = $node_data["parent"];
				}
				// GET ALL SUBNODES
				$node_data = $tree->getNodeData($object_id);
				$subtree_nodes = $tree->getSubTree($node_data);

				// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDERS
				$all_rolf_obj = $rbacadmin->getObjectsWithStopedInheritance($this->id);

				// DELETE ACTUAL ROLE FOLDER FROM ARRAY
				$key = array_keys($all_rolf_obj,$object_id);
				unset($all_rolf_obj["$key[0]"]);

				$check = false;
				foreach($subtree_nodes as $node)
				{
					if(!$check)
					{
						if(in_array($node["obj_id"],$all_rolf_obj))
						{
							$lft = $node["lft"];
							$rgt = $node["rgt"];
							$check = true;
							continue;
						}
						$valid_nodes[] = $node;
					}
					else
					{
						if(($node["lft"] > $lft) && ($node["rgt"] < $rgt))
						{
							continue;
						}
						else
						{
							$check = false;
							$valid_nodes[] = $node;
						}
					}
				}
				// NOW SET ALL PERMISSIONS
				foreach($a_template_perm as $type => $a_perm)
				{
					foreach($valid_nodes as $node)
					{
						if($type == $node["type"])
						{
							$rbacadmin->revokePermission($node["obj_id"],$this->id);
							$rbacadmin->grantPermission($this->id,$a_perm,$node["obj_id"]);
						}
					}
				}
			}// END IF RECURSIVE
		}// END CHECK ACCESS
		else
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* copy permissions from role
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;
		
		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"], $_GET["parent"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* assign user to role
	* @access	public
	*/
	function assignSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $rbacreview;
		
		// TODO: get rid of $_GET variables
		 
		if ($rbacadmin->isAssignable($_GET["obj_id"],$_GET["parent"]))
		{
			if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
			{
				$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);
				$_POST["user"] = $_POST["user"] ? $_POST["user"] : array();

				foreach (array_diff($assigned_users,$_POST["user"]) as $user)
				{
					$rbacadmin->deassignUser($_GET["obj_id"],$user);
				}

				foreach (array_diff($_POST["user"],$assigned_users) as $user)
				{
					$rbacadmin->assignUser($_GET["obj_id"],$user,false);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
			}

			return true;
		}
		else
		{
			$this->ilias->raiseError("It's worth a try. ;-)",$this->ilias->error_obj->WARNING);
		}
	}
} // END class.RoleObject
?>
