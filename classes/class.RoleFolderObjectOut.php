<?php
/**
* Class RoleFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.RoleFolderObjectOut.php,v 1.2 2003/02/25 17:36:49 akill Exp $
* 
* @extends Object
* @package ilias-core
*/

class RoleFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function RoleFolderObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}

	function viewObject()
	{
		global $rbacsystem, $rbacadmin, $tpl;

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "description", "last_change");
		if ($rbacsystem->checkAccess("read", $_GET["obj_id"], $_GET["parent"]))
		{
			if ($list = $rbacadmin->getRoleAndTemplateListByObject($_GET["obj_id"], $_GET["order"], $_GET["direction"]))
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
						"last_change" => Format::formatDate($val["last_update"])
					);
					//control information
					$this->data["ctrl"][] = array(
						"type" => $val["type"],
						"obj_id" => $val["obj_id"],
						// DEFAULT ACTION IS 'permObject()'
						"cmd"    => "perm",
						"parent" => $_GET["obj_id"],
						"parent_parent" => $_GET["parent"],
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


	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=perm");
		exit();
	}

} // END class.RoleFolderObjectOut
?>