<?php
/**
* Class RoleFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.RoleFolderObjectOut.php,v 1.3 2003/02/26 13:44:10 shofmann Exp $
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
	function RoleFolderObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolf";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
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
						"last_change" => Format::formatDate($val["last_update"])
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


	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&parent=".
			   $_GET["parent"]."&cmd=perm");
		exit();
	}

} // END class.RoleFolderObjectOut
?>