<?php
/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjGroupGUI.php,v 1.4 2003/04/28 15:02:27 mrus Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";
require_once "class.ilObjGroup.php";

class ilObjGroupGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjGroupGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "grp";
		//$this->lng =& $lng;
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
	
	/**
	* create new object form
	*/
	function createObject()
	{
		//TODO: check the acces rights; compare class.ilObjectGUI.php
		
		global $rbacsystem;

			$data = array();
			$data["fields"] = array();
			$data["fields"]["group_name"] = "";
			$data["fields"]["desc"] = "";
			

			$this->getTemplateFile("new","group");

			foreach ($data["fields"] as $key => $val)
			{  
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}
			
			$stati = array("group_status_public","group_status_private","group_status_closed");
			
			//build form
			$opts = ilUtil::formSelect(0,"group_status_select",$stati);

			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"].
				"&new_type=".$_POST["new_type"]);
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	}
	
	
	/**
	* save Object
	* @access public
	*/
	function saveObject()
	{	
		//TODO: check the acces rights; compare class.ilObjectGUI.php
		global $rbacadmin,$ilias;
		
		$newObj = new ilObject();
		$newObj->setType("grp");
		$newObj->setTitle($_POST["Fobject"]["title"]);
		$newObj->setDescription($_POST["Fobject"]["desc"]);
		$newObj->create();
		$newObj->createReference();

		$refGrpId = $newObj->getRefId();
		$GrpId = $newObj->getId();

		$newObj->putInTree($_GET["ref_id"]);
		unset($newObj);
		//rolefolder

		//create new rolefolder-object
		$newObj = new ilObject();
		$newObj->setType("rolf");
		$newObj->setTitle("Rolefolder:".$_POST["Fobject"]["title"]);
		$newObj->setDescription($_POST["Fobject"]["desc"]);

		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($refGrpId);		//assign rolefolder to group
		$refRolf = $newObj->getRefId();
		unset($newObj);
		
		// create new role objects
		$newGrp = new ilObjGroup($GrpId,false);

		//0=public,1=private,2=closed
		$newGrp->setGroupStatus($_POST["group_status_select"]);

		//create standard group roles:member,admin,request(!),depending on group status(public,private,closed)
		$newGrp->createGroupRoles($refRolf); 		
		//creator becomes admin of group
		$newGrp->joinGroup($ilias->account->getId(),"admin");
		
		
		header("Location: adm_object.php?".$this->link_params);
		exit();

	}
	
	/**
	* leave Group
	* @access public
	*/
	function leaveGrpObject()
	{
		global $rbacsystem;

//		if($rbacsystem->checkAccess('write',$_GET["ref_id"]))
		{
			$newGrp = new ilObjGroup($_GET["ref_id"],true);
			$newGrp->leaveGroup($_GET["mem_id"]);			
		}
/*
		TODO: site that is displayed after leaving
		function ilTree($a_tree_id, $a_root_id = 0)
		var_dump(ilTree::getParentId($_GET["ref_id"]));	
			
		if($this->ilias->account->getId() == $_GET["mem_id"])
		{
//			header("Location: adm_object.php?".$this->link_params);
			header("Location: adm_object.php?ref_id=".ilTree::getParentId($_GET["ref_id"]));
			var_dump($this->link_params);
		}
*/
	
	}
	
	/**
	* show members of the group object
	* @access public
	*/
	function membersObject()
	{
		$num = 0;

		$newGrp = new ilObjGroup($this->ref_id,true);
		$member_ids = $newGrp->getGroupMemberIds();
		
		$member_arr = array();
		foreach ($member_ids as $member_id)
		{
			array_push($member_arr, new ilObjUser($member_id));
		}

		// output data
		$this->getTemplateFile("members");
		$this->tpl->setCurrentBlock("HEADER_MEMBERS");
		$this->tpl->setVariable("TXT_USER", "User");
		$this->tpl->setVariable("TXT_FIRSTNAME", "Firstname");
		$this->tpl->setVariable("TXT_LASTNAME", "Lastname");
		$this->tpl->setVariable("TXT_JOINDATE", "Join date");
		$this->tpl->setVariable("TXT_ROLE", "Role");
		$this->tpl->setVariable("TXT_FUNCTIONS", "Functions");

		$this->tpl->parseCurrentBlock();
		
		foreach($member_arr as $member)
		{	
			$grp_role_id = $newGrp->getGroupRoleId($member->getId());
			$newObj 	 = new ilObject($grp_role_id,false);
					
			//todo: chechAccess, each user sees only the symbols belonging to his rigths
			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
			$link_change = "adm_object.php?cmd=editMembership&mem_id=".$member->getId();		
			$link_leave = "adm_object.php?type=grp&cmd=leaveGrp&ref_id=".$this->ref_id."&mem_id=".$member->getId();					
			$img_contact = "pencil";
			$img_change = "change";
			$img_leave = "group_out";						
			$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
			$val_change = ilUtil::getImageTagByType($img_change, $this->tpl->tplPath);
			$val_leave  = ilUtil::getImageTagByType($img_leave, $this->tpl->tplPath);
	
			// BEGIN TABLE MEMBERS
			$this->tpl->setCurrentBlock("TABLE_MEMBERS");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("LOGIN",$member->getLogin());
			$this->tpl->setVariable("FIRSTNAME", $member->getFirstname());
			$this->tpl->setVariable("LASTNAME", $member->getLastname());
			$this->tpl->setVariable("ANNOUNCEMENT_DATE", "Announcement Date");
			$this->tpl->setVariable("ROLENAME", $newObj->getTitle());
			
			$this->tpl->setVariable("LINK_CONTACT", $link_contact);
			$this->tpl->setVariable("CONTACT", $val_contact);
			$this->tpl->setVariable("LINK_CHANGE", $link_change);
			$this->tpl->setVariable("CHANGE", $val_change);
			$this->tpl->setVariable("LINK_LEAVE", $link_leave);
			$this->tpl->setVariable("LEAVE", $val_leave);						
			$this->tpl->parseCurrentBlock();
			// END TABLE MEMBERS
		}
		/*
		$num = 0;
		foreach($data["rolenames"] as $name)
		{
			// BLOCK ROLENAMES
			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$name);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			$this->tpl->setCurrentBLock("CHECK_INHERIT");
			$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num++]);
			$this->tpl->parseCurrentBlock();
		}*/
	}	


	function editObject()
	{
		global $rbacsystem;

		$data = array();
		$data["fields"] = array();
		$data["fields"]["group_name"] = "";
		$data["fields"]["desc"] = "";
		
		$this->getTemplateFile("new","group");
		foreach ($data["fields"] as $key => $val)
		{  
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		
		$stati = array("group_status_public","group_status_private","group_status_closed");
		
		//build form
		$opts = ilUtil::formSelect(0,"group_status_select",$stati);
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=update"."&ref_id=".$_GET["ref_id"].
			"&new_type=".$_POST["new_type"]);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("update"));

		
	}
} // END class.GroupObjectOut
?>
