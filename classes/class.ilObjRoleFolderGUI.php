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

	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}

} // END class.RoleFolderObjectOut
?>
