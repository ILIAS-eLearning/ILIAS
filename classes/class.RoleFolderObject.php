<?php
/**
* Class RoleFolderObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/
class RoleFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function RoleFolderObject($a_id)
	{
		$this->Object($a_id);
	}

	function viewObject($a_order, $a_direction)
	{
		global $rbacsystem, $rbacadmin, $tpl;

		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "name", "description", "last_change");
		
		if ($rbacsystem->checkAccess("read", $this->id, $this->parent))
		{
			if ($list = $rbacadmin->getRoleAndTemplateListByObject($this->id, $a_order, $a_direction))
			{
				foreach ($list as $key => $val)
				{
					// determine image (role object or role template?)
					$image = $val["type"] == "rolt" ? "icon_rtpl_b" : "icon_role_b";

					//visible data part
					$this->objectList["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/".$image.".gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => $val["last_update"]
					);
					//control information
					$this->objectList["ctrl"][] = array(
						"type" => $val["type"],
						"obj_id" => $val["obj_id"],
						// DEFAULT ACTION IS 'permObject()'
						"cmd"    => "perm",
						"parent" => $this->id,
						"parent_parent" => $this->parent,
					);
				}
			} //if userdata

			return $this->objectList;

		} //if rbac
		else
		{
			$this->ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
		}
	} //function

	function deleteObject($a_obj_id,$a_parent)
	{
		global $rbacadmin;

		
		$roles = $rbacadmin->getRolesAssignedToFolder($a_obj_id);

		// FIRST DELETE ALL LOCAL/BASE ROLES OF FOLDER
		require_once("./classes/class.RoleObject.php");
		$obj = new RoleObject();
		
		foreach($roles as $role)
		{
			$obj->deleteObject($role,$a_obj_id);
		}

		// DELETE ROLE FOLDER
		parent::deleteObject($a_obj_id,$a_parent);
		return true;
	}

	function cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent)
	{
		// DO NOTHING ROLE FOLDERS AREN'T COPIED
		//	$new_id = parent::cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent);
		return true;
	}

	function getSubObjects()	
	{
		return false;
	} //function

} // class
?>
