<?php
/**
* Class UserFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class UserFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function UserFolderObject()
	{
		$this->Object();
	}
	
	function viewObject()
	{
		global $rbacsystem, $tpl;

		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "name", "description", "last_change");
		
		if ($rbacsystem->checkAccess("read", $this->id, $this->parent))
		{
			if ($usr_data = getUserList($_GET["order"], $_GET["direction"]) )
			{
			
				foreach ($usr_data as $key => $val)
				{
					//visible data part
					$this->objectList["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_user_b.gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => $val["last_update"]
					);
	
					//control information
					$this->objectList["ctrl"][] = array(
						"type" => "usr",
						"obj_id" => $val["obj_id"],
						"parent" => $this->id,
						"parent_parent" => $this->parent,
					);
					
				}
				

			} //if userdata

			return $this->objectList;

		} //if rbac
		else
		{
			$ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
		}
	} //function

	/**
	* delete user
	* @access	public
	*/
	function deleteObject()
	{
		global $rbacadmin,$rbacsystem;
		
		// CHECK ACCESS
		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$rbacadmin->deleteUser($_POST["id"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_obj->WARNING);
		}

		return true;		
	}
	
	function getSubObjects()	
	{
		return false;
	}
} // class
?>