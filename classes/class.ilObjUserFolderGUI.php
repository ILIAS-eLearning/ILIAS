<?php
/**
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjUserFolderGUI.php,v 1.4 2003/04/01 09:11:09 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjUserFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjUserFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "usrf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* list users
	*/
	function viewObject()
	{
		global $rbacsystem, $tpl, $ilias;

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "description", "last_change");
		if ($rbacsystem->checkAccess("read", $_GET["ref_id"]))
		{
			if ($usr_data = getObjectList("usr",$_GET["order"], $_GET["direction"]))
			{
				foreach ($usr_data as $key => $val)
				{
					//visible data part
					$this->data["data"][] = array(
						"type" => ilUtil::getImageTagByType("usr",$this->tpl->tplPath),
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => ilFormat::formatDate($val["last_update"])
					);

					//control information
					$this->data["ctrl"][] = array(
						"ref_id"	=> $this->id,
						"obj_id"	=> $val["obj_id"],
						"type"		=> "usr"
					);
				}
			} //if userdata

			parent::displayList();

		} //if rbac
		else
		{
			$ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
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
			require_once("class.ilObjUser.php");
			
			// FOR ALL SELECTED OBJECTS
			foreach ($_SESSION["saved_post"] as $id)
			{
				$user = new ilObjUser($id);
				$user->delete();
			}
			
			// Feedback
			sendInfo($this->lng->txt("info_deleted"),true);	
		}
	}
} // END class.UserFolderObjectOut
?>
