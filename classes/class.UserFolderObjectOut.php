<?php
/**
* Class UserFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserFolderObjectOut.php,v 1.2 2003/02/25 17:36:49 akill Exp $
* 
* @extends Object
* @package ilias-core
*/

class UserFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function UserFolderObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
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
		if ($rbacsystem->checkAccess("read", $_GET["obj_id"], $_GET["parent"]))
		{
			if ($usr_data = getObjectList("usr",$_GET["order"], $_GET["direction"]))
			{
				foreach ($usr_data as $key => $val)
				{
					//visible data part
					$this->data["data"][] = array(
						"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_user_b.gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => Format::formatDate($val["last_update"])
					);

					//control information
					$this->data["ctrl"][] = array(
						"type" => "usr",
						"obj_id" => $val["obj_id"],
						"parent" => $_GET["obj_id"],
						"parent_parent" => $_GET["parent"],
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

} // END class.UserFolderObjectOut
?>