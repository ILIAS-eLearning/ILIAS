<?php
/**
* Class ilObjRoleFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRoleFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	function viewObject()
	{
		global $rbacsystem, $rbacadmin, $tpl;

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "description", "last_change");
		if ($rbacsystem->checkAccess("read", $_GET["ref_id"]))
		{
			if ($list = $rbacadmin->getRoleListByObject($_GET["ref_id"],true,$_GET["order"],$_GET["direction"]))
			{
				foreach ($list as $key => $val)
				{
					// determine image (role object or role template?)
					$image = $val["type"] == "rolt" ? "icon_rtpl_b" : "icon_role_b";

					//visible data part
					$this->data["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/".$image.".gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => ilFormat::formatDate($val["last_update"])
					);
					//control information
					$this->data["ctrl"][] = array(
						"ref_id"	=> $this->id,
						"obj_id"	=> $val["obj_id"],
						"type"		=> $val["type"],
						// DEFAULT ACTION IS 'permObject()'
						"cmd"		=> "perm"
					);
				}
			} //if userdata

			parent::displayList();

		} //if rbac
		else
		{
			$this->ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
		}
	} //function

	/**
	* confirmObject
	* 
	* 
	*/
	function confirmObject()
	{
		global $rbacsystem;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$perform_delete = false;
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
						 $not_deletable,$this->ilias->error_obj->WARNING);
		}
		else
		{
			require_once("class.ilObjRole.php");
			
			// FOR ALL SELECTED OBJECTS
			foreach ($_SESSION["saved_post"] as $id)
			{
				$role = new ilObjRole($id);
				$role->delete();
			}
			
			// Feedback
			sendInfo($this->lng->txt("info_deleted"),true);	
		}
	}
	
	/**
	* display deletion confirmation screen
	*/
	function deleteObject()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancel"  => $this->lng->txt("cancel"),
								  "confirm"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}

} // END class.RoleFolderObjectOut
?>
