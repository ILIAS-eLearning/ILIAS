<?php
/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjForumGUI.php,v 1.3 2003/03/31 10:21:34 smeyer Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjForumGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjForumGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "frm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree, $objDefinition;

		if ($rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			// create and insert forum in objecttree
			require_once("classes/class.ilObjForum.php");
			$forumObj = new ilObjForum();
			$forumObj->setType($_GET["new_type"]);
			$forumObj->setTitle($_POST["Fobject"]["title"]);
			$forumObj->setDescription($_POST["Fobject"]["desc"]);
			$forumObj->create();
			$forumObj->createReference();
			$forumObj->putInTree($_GET["ref_id"]);
			$forumObj->setPermissions($_GET["ref_id"]);

			// create a local role folder
			require_once ("classes/class.ilObjRoleFolder.php");
			$rfoldObj = new ilObjRoleFolder();
			$rfoldObj->setTitle("Local roles");
			$rfoldObj->setDescription("Role Folder of forum ref_no.".$forumObj->getRefId());
			$rfoldObj->create();
			$rfoldObj->createReference();
			$rfoldObj->putInTree($forumObj->getRefId());
			$rfoldObj->setPermissions($forumObj->getRefId());
	
			// create moderator role...
			require_once ("classes/class.ilObjRole.php");
			$roleObj = new ilObjRole();
			$roleObj->setTitle("moderator_".$forumObj->getRefId());
			$roleObj->setDescription("moderator of forum ref_no.".$forumObj->getRefId());
			$roleObj->create();
			
			// ...and put the role into local role folder...
			$rbacadmin->assignRoleToFolder($roleObj->getId(),$rfoldObj->getRefId(),$forumObj->getRefId(),"y");
			
			// ...finally assign moderator role to creator of forum object
			$rbacadmin->assignUser($roleObj->getId(), $forumObj->getOwner(), "n");
			
			// insert new forum as new topic into frm_data
			$top_data = array(
	            "top_frm_fk"   		=> $forumObj->getId(),
				"top_name"   		=> addslashes($forumObj->getTitle()),
	            "top_description" 	=> addslashes($forumObj->getDescription()),
	            "top_num_posts"     => 0,
	            "top_num_threads"   => 0,
	            "top_last_post"     => "",
				"top_mods"      	=> $roleObj->getId(),
				"top_usr_id"      	=> $_SESSION["AccountId"],
	            "top_date" 			=> date("Y-m-d H:i:s")
	        );
	
			$q = "INSERT INTO frm_data ";
			$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_date,top_usr_id) ";
			$q .= "VALUES ";
			$q .= "('".$top_data["top_frm_fk"]."','".$top_data["top_name"]."','".$top_data["top_description"]."','".$top_data["top_num_posts"]."','".$top_data["top_num_threads"]."','".$top_data["top_last_post"]."','".$top_data["top_mods"]."','".$top_data["top_date"]."','".$top_data["top_usr_id"]."')";
			$this->ilias->db->query($q);
		}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}

		sendInfo($this->lng->txt("forum_added"),true);		
		header("Location: adm_object.php?".$this->link_params);
		exit();
	}
}
?>