<?php
/**
* Class RoleTemplateObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.RoleTemplateObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends Object
* @package ilias-core
*/

class RoleTemplateObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function RoleTemplateObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolt";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}

	function updateObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["parent"]."&parent=".
			   $_GET["parent_parent"]."&cmd=view");
		exit();
	}


	/**
	* save a new role template object
	* @access	public
	**/
	function saveObject()
	{
		global $rbacadmin, $rbacsystem; 


		// CHECK ACCESS 'write' to role folder
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"], $_GET["parent"]))
		{
			if ($rbacadmin->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError("A role with the name '".
										 $_POST["Fobject"]["title"]."' already exists! <br />Please choose another name.",
										 $this->ilias->error_obj->WARNING);
			}
			require_once("./classes/class.RoleTemplateObject.php");
			$roltObj = new RoleTemplateObject();
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
		return true;
	}


	function permObject()
	{
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

	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=perm");
		exit();
	}
		
} // END class.RoleTemplateObjectOut
?>
