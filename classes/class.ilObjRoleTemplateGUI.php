<?php
/**
* Class ilObjRoleTemplateGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjRoleTemplateGUI.php,v 1.2 2003/03/28 10:30:36 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleTemplateGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRoleTemplateGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}


	/**
	* save a new role template object
	* @access	public
	**/
	function saveObject()
	{
		global $rbacadmin, $rbacsystem;


		// CHECK ACCESS 'write' to role folder
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			if ($rbacadmin->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError("A role with the name '".
										 $_POST["Fobject"]["title"]."' already exists! <br />Please choose another name.",
										 $this->ilias->error_obj->WARNING);
			}
			require_once("./classes/class.ilObjRoleTemplate.php");
			$roltObj = new ilObjRoleTemplate();
			$roltObj->setTitle($_POST["Fobject"]["title"]);
			$roltObj->setDescription($_POST["Fobject"]["desc"]);
			$roltObj->create();
			//$rbacadmin->assignRoleToFolder($new_obj_id, $a_obj_id, $a_parent,'n');
			$rbacadmin->assignRoleToFolder($roltObj->getId(), $_GET["ref_id"], $_GET["parent"], 'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}


	/**
	* display permissions
	*/
	function permObject()
	{
		global $tree, $tpl, $rbacadmin, $rbacreview, $rbacsystem, $lng;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$obj_data = getObjectList("typ","title","ASC");

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
						$selected = $rbacadmin->getRolePermission($this->object->getId(), $data["title"], $_GET["ref_id"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = ilUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][] = "";
					}
				}

				// END CHECK_PERM
				// color changing
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$output["col_anz"] = count($obj_data);

			// ADOPT PERMISSIONS
			$output["message_middle"] = "Adopt Permissions from Role Template";
			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacadmin->getParentRoleIds($_GET["ref_id"],true);

			// sort output for correct color changing
			ksort($parent_role_ids);

			foreach ($parent_role_ids as $key => $par)
			{
				$radio = ilUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$output["adopt"][$key]["css_row_adopt"] = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["adopt"][$key]["check_adopt"] = $radio;
				$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
				$output["adopt"][$key]["role_name"] = $par["title"];
			}
			$output["formaction_adopt"] = "adm_object.php?cmd=adoptPermSave&obj_id="
				.$this->object->getId()."&ref_id=".$_GET["ref_id"];

			// END ADOPT_PERMISSIONS
			$output["formaction"] = "adm_object.php?cmd=permSave&obj_id=".
				$this->object->getId()."&ref_id=".$_GET["ref_id"];
			$role_data = getObject($this->id);
			$output["message_top"] = "Permission Template of Role: ".$role_data["title"];
		}

		$this->data = $output;


		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_perm_role.html");


		// BEGIN BLOCK OBJECT TYPES
		$this->tpl->setCurrentBlock("OBJECT_TYPES");

		foreach ($this->data["obj_types"] as $type)
		{
			$this->tpl->setVariable("OBJ_TYPES",$type);
			$this->tpl->parseCurrentBlock();
		}
		// END BLOCK OBJECT TYPES

		// BEGIN TABLE DATA OUTER
		foreach($this->data["perm"] as $name => $operations)
		{
			// BEGIN CHECK PERMISSION
			$this->tpl->setCurrentBlock("CHECK_PERM");
			for($i = 0;$i < count($operations)-1;++$i)
			{
				$this->tpl->setVariable("CHECK_PERMISSION",$operations[$i]);
				$this->tpl->parseCurrentBlock();
			}
			// END CHECK PERMISSION
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$this->tpl->setVariable("CSS_ROW",$operations["color"]);
			$this->tpl->setVariable("PERMISSION",$name);
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE DATA OUTER

		// BEGIN ADOPT PERMISSIONS

		foreach($this->data["adopt"] as $key => $value)
		{
			$this->tpl->setCurrentBlock("ADOPT_PERMISSIONS");
			$this->tpl->setVariable("CSS_ROW_ADOPT",$value["css_row_adopt"]);
			$this->tpl->setVariable("CHECK_ADOPT",$value["check_adopt"]);
			$this->tpl->setVariable("TYPE",$value["type"]);
			$this->tpl->setVariable("ROLE_NAME",$value["role_name"]);
			$this->tpl->parseCurrentBlock();
		}
		// END ADOPT PERMISSIONS

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("COL_ANZ",$this->data["col_anz"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
		$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);


		$this->tpl->parseCurrentBlock("adm_content");
	}


	/**
	* save permission templates of role
	* @access	public
	**/
	function permSaveObject()
	{
		global $tree, $rbacadmin, $rbacsystem;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		else
		{
			// Alle Template Eintraege loeschen
			$rbacadmin->deleteRolePermission($this->object->getId(), $_GET["ref_id"]);

			foreach ($_POST["template_perm"] as $key => $ops_array)
			{
				// Setzen der neuen template permissions
				$rbacadmin->setRolePermission($this->object->getId(), $key,$ops_array,$_GET["ref_id"]);
			}
		}
		header("Location: adm_object.php?obj_id=".$this->object->getId()."&ref_id=".
			$_GET["ref_id"]."&cmd=perm");

	}


	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&ref_id=".
			   $_GET["ref_id"]."&cmd=perm");
		exit();
	}

} // END class.RoleTemplateObjectOut
?>
