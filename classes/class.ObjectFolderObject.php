<?php
/**
* Class ObjectFolderObject
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class ObjectFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	**/
	function ObjectFolderObject($a_id)
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
			if ($list = getTypeList($a_order, $a_direction))
			{
				foreach ($list as $key => $val)
				{
					//visible data part
					$this->objectList["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_type_b".".gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => $val["last_update"]
					);
	
					//control information
					$this->objectList["ctrl"][] = array(
						"type" => "typ",
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
	
	function getSubObjects()	
	{
		return false;
	} //function
	
} // END class.ObjectFolderObject
?>
