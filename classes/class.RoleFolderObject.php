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
	function RoleFolderObject()
	{
		$this->Object();
	}

	function viewObject()
	{
		global $rbacsystem, $rbacadmin, $tpl;

		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "name", "description", "last_change");
		
		if ($rbacsystem->checkAccess("read", $this->id, $this->parent))
		{
			if ($list = $rbacadmin->getRoleAndTemplateListByObject($this->id, $_GET["order"], $_GET["direction"]))
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
	
	function getSubObjects()	
	{
		return false;
	} //function

} // class
?>