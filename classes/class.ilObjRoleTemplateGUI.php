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
* Class ilObjRoleTemplateGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjRoleTemplateGUI.php,v 1.15 2003/07/07 17:46:57 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleTemplateGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjRoleTemplateGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}


	/**
	* save a new role template object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin, $rbacreview;

		// CHECK ACCESS 'write' to role folder
		// TODO: check for create role permission should be better
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// check if rolt title is unique
			if ($rbacreview->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".$_POST["Fobject"]["title"]."' ".
										 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
			}

			// create new rolt object
			include_once("./classes/class.ilObjRoleTemplate.php");
			$roltObj = new ilObjRoleTemplate();
			$roltObj->setTitle($_POST["Fobject"]["title"]);
			$roltObj->setDescription($_POST["Fobject"]["desc"]);
			$roltObj->create();
			$parent_id = $this->tree->getParentId($_GET["ref_id"]);
			$rbacadmin->assignRoleToFolder($roltObj->getId(), $_GET["ref_id"],$parent_id,'y');
		}
		
		sendInfo($this->lng->txt("rolt_added"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}

	/**
	* display permissions
	* 
	* @access	public
	*/
	function permObject()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// get all object type definitions
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

				$num = 0;

				// BEGIN CHECK_PERM
				foreach ($obj_data as $data)
				{
					if (in_array($operations["ops_id"],$rbacreview->getOperationsOnType($data["obj_id"])))
					{
						$selected = $rbacreview->getOperationsOfRole($this->object->getId(), $data["title"], $_GET["ref_id"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = ilUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][$num] = "";
					}

					$num++;
				}
				// END CHECK_PERM

				// color changing
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$output["col_anz"] = count($obj_data);
			$output["txt_save"] = $this->lng->txt("save");
			$output["txt_permission"] = $this->lng->txt("permission");
			$output["txt_obj_type"] = $this->lng->txt("obj_type");
	
			// ADOPT PERMISSIONS
			$output["message_middle"] = $this->lng->txt("adopt_perm_from_template");

			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacreview->getParentRoleIds($_GET["ref_id"],true);

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

			$output["formaction"] = "adm_object.php?cmd=permSave&obj_id=".$this->object->getId()."&ref_id=".$_GET["ref_id"];
			$output["message_top"] = "Permission Template of Role: ".$this->object->getTitle();
		}

		$this->data = $output;

		// generate output
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
		foreach ($this->data["perm"] as $name => $operations)
		{
			// BEGIN CHECK PERMISSION
			$this->tpl->setCurrentBlock("CHECK_PERM");

			for ($i = 0;$i < count($operations)-1;++$i)
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
		foreach ($this->data["adopt"] as $key => $value)
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

		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_rolt_b.gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));
		$this->tpl->setVariable("TBL_TITLE",$this->object->getTitle());

		$this->tpl->setVariable("COL_ANZ",$this->data["col_anz"]);
		$this->tpl->setVariable("COL_ANZ_PLUS",$this->data["col_anz"]+1);
		$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("TXT_OBJ_TYPE",$this->data["txt_obj_type"]);
		$this->tpl->setVariable("MESSAGE_TABLE",$this->data["message_table"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
		$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);
		$this->tpl->parseCurrentBlock();
	}


	/**
	* save permission templates of role
	*
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
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

		sendinfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?obj_id=".$this->object->getId()."&ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}

	/**
	* adopting permission setting from other roles/role templates
	*
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		elseif ($_GET["obj_id"] == $_POST["adopt"])
		{
			sendInfo($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"], $_GET["ref_id"]);
			$parentRoles = $rbacreview->getParentRoleIds($_GET["ref_id"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["ref_id"],$_GET["obj_id"]);		
			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			sendInfo($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		exit();
	}

	/**
	* update role template object
	*
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// check write access
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_rolt"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// check if role title is unique
			if ($rbacreview->roleExists($_POST["Fobject"]["title"],$this->object->getId()))
			{
				$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".$_POST["Fobject"]["title"]."' ".
										 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
			}

			// update
			$this->object->setTitle($_POST["Fobject"]["title"]);
			$this->object->setDescription($_POST["Fobject"]["desc"]);
			$this->object->update();
		}
		
		sendInfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}
} // END class.ilObjRoleTemplate
?>
