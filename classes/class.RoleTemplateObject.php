<?php
/**
* Class RoleTemplateObject
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class RoleTemplateObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function RoleTemplateObject($a_id)
	{
		$this->Object($a_id);
	}

	//
	// Overwritten methods:
	//

	/**
	* create a role template object 
	* @access	public
	*/
	function createObject($a_id, $a_new_type)
	{
		// Creates a child object
		global $tplContent, $rbacsystem;
		
		// TODO: get rif of $_GET var
		
		if ($rbacsystem->checkAccess("write",$a_id,$_GET["parent"]))
		{
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* save a new role template object
	* @access	public
	**/
	function saveObject($a_obj_id, $a_parent,$a_type, $a_new_type, $a_data)
	{
		global $rbacadmin, $rbacsystem; 


		// CHECK ACCESS 'write' to role folder
		if ($rbacsystem->checkAccess('write', $a_obj_id, $a_parent))
		{
			if ($rbacadmin->roleExists($a_data["title"]))
			{
				$this->ilias->raiseError("A role with the name '".
										 $a_data["title"]."' already exists! <br />Please choose another name.",
										 $this->ilias->error_obj->WARNING);
			}
			$new_obj_id = createNewObject($a_new_type, $a_data);
			$rbacadmin->assignRoleToFolder($new_obj_id, $a_obj_id, $a_parent,'n');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* delete a role template object 
	* @access	public
	**/
	function deleteObject($a_obj_id, $a_parent, $a_tree_id = 1)
	{
		global $rbacsystem, $rbacadmin;

		$rbacadmin->deleteTemplate($a_obj_id, $a_parent);
		return true;
	}

	/**
	* edit a role template object
	* @access	public
	* 
	**/
	function editObject($a_order, $a_direction)
	{
		global $tplContent, $rbacsystem;
		
		// TODO: get rif of $_GET vars

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		{
			$obj = getObject($this->id);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $obj["title"];
			$data["fields"]["desc"] = $obj["desc"];
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}			
	}

	/**
	* update a role template object
	* @access	public
	**/
	function updateObject($a_data)
	{
		global $rbacsystem;
		
		// TODD: get rid of $_GET vars

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		{
			updateObject($this->id, $this->type, $a_data);

			return true;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* show permission templates of role
	* @access public
	**/
	function permObject()
	{
		global $tree, $tpl, $rbacadmin, $rbacreview, $rbacsystem, $lng;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$obj_data = getTypeList();
			// BEGIN OBJECT_TYPES

			foreach ($obj_data as $data)
			{
				$output["obj_types"][] = $data["title"];
			}

			// END OBJECT TYPES
			$all_ops = getOperationList();

			// BEGIN TABLE_DATA_OUTER
			foreach ($all_ops as $key => $operations)
			{
				$operation_name = $operations["operation"];
				// BEGIN CHECK_PERM

				foreach ($obj_data as $data)
				{
					if (in_array($operations["ops_id"],$rbacadmin->getOperationsOnType($data["obj_id"])))
					{
						$selected = $rbacadmin->getRolePermission($this->id, $data["title"], $_GET["parent"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = TUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][] = "";
					}
				}

				// END CHECK_PERM
				// color changing
				$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$output["col_anz"] = count($obj_data);

			// ADOPT PERMISSIONS
			$output["message_middle"] = "Adopt Permissions from Role Template";
			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);

			// sort output for correct color changing
			ksort($parent_role_ids);

			foreach ($parent_role_ids as $key => $par)
			{
				$radio = TUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$output["adopt"][$key]["css_row_adopt"] = TUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["adopt"][$key]["check_adopt"] = $radio;
				$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
				$output["adopt"][$key]["role_name"] = $par["title"];
			}
			$output["formaction_adopt"] = "adm_object.php?cmd=adoptPermSave&obj_id="
				.$this->id."&parent_parent=".$this->parent_parent."&parent=".$this->parent;

			// END ADOPT_PERMISSIONS
			$output["formaction"] = "adm_object.php?cmd=permSave&obj_id=".
				$this->id."&parent_parent=".$this->parent_parent."&parent=".$this->parent;
			$role_data = $rbacadmin->getRoleData($this->id);
			$output["message_top"] = "Permission Template of Role: ".$role_data["title"];
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		return $output;
	}

	/**
	* save permission templates of role 
	* @access	public
	**/
	function permSaveObject($a_perm, $a_stop_inherit, $a_type, $a_template_perm, $a_recursive)
	{
		global $tree, $rbacadmin, $rbacsystem;
		
		// get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			// Alle Template Eintraege loeschen
			$rbacadmin->deleteRolePermission($this->id, $_GET["parent"]);

			foreach ($a_template_perm as $key => $ops_array)
			{
				// Setzen der neuen template permissions
				$rbacadmin->setRolePermission($this->id, $key,$ops_array,$_GET["parent"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
		}


		return true;
	}

	/**
	* copy permissions from role or template
	* @access	public
	**/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles["$_POST[adopt]"]["parent"],$_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}

		return true;
	}
} // END class.RoleTemplateObject
?>
