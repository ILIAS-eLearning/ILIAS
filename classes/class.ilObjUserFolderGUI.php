<?php
/**
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserFolderObjectOut.php,v 1.4 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

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
						"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_user_b.gif\" border=\"0\">",
						"name" => $val["title"],
						"description" => $val["desc"],
						"last_change" => Format::formatDate($val["last_update"])
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
} // END class.UserFolderObjectOut
?>
