<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjGroupGUI.php,v 1.8 2003/06/04 12:05:49 neiken Exp $
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
		parent::ilObjectGUI($a_data,$a_id,$a_call_by_reference);
		//var_dump ($this->type);
		global $tree;
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
	*  Object
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
		$newObj->setPermissions($_GET["ref_id"]);
		
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
		$newObj->setPermissions($refGrpId);
		
		$refRolf = $newObj->getRefId();
		unset($newObj);
		
		// create new role objects
		$newGrp = new ilObjGroup($refGrpId,true);

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
	* update GroupObject
	* @access public
	*/
	function updateObject()
	{
		global $rbacadmin,$rbacsystem;
		
		if($rbacsystem->checkAccess("write",$this->object->getRefId()) )
		{
			$this->object->setGroupStatus($_POST["group_status_select"]);
			parent::updateObject();
		}
		
	}
	/**
	* edit Group
	* @access public
	*/
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
		$selected = $this->object->getGroupStatus();
		$opts = ilUtil::formSelect($selected,"group_status_select",$stati);
		
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=update"."&ref_id=".$_GET["ref_id"].
			"&new_type=".$_POST["new_type"]);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TITLE",$this->object->getTitle() );
		$this->tpl->setVariable("DESC",$this->object->getDescription() );
		
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("update"));
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
			$link_change = "adm_object.php?cmd=editMember&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
//			$link_change = "adm_object.php?cmd=perm&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
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


	function editMemberObject()
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
	
	function listGroups()
	{
	
		$this->getTemplateFile("overview", "grp");
	
		
		//$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		
		$this->tpl->setCurrentBlock("content");
		
		$this->tpl->setVariable("TXT_GROUPS",  $this->lng->txt("groups"));
		$this->tpl->setCurrentBlock("tblheader");
		$this->tpl->setVariable("TXT_NAME",  $this->lng->txt("name"));
		$this->tpl->setVariable("TXT_DESC",  $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_ROLE_IN_GROUP",  $this->lng->txt("role"));
		$this->tpl->setVariable("TXT_OWNER",  $this->lng->txt("owner"));
		$this->tpl->setVariable("TXT_CONTEXT",  $this->lng->txt("context"));
		
		$lr_arr = ilUtil::getObjectsByOperations('grp','visible');

		
		      
		usort($lr_arr,"sortObjectsByTitle");

		$lr_num = count($lr_arr);

		if ($lr_num > 0)
		{
			// counter for rowcolor change
		
			$num = 0;
			//var_dump ($lr_arr);
			foreach ($lr_arr as $grp_data)
			{
				$this->tpl->setCurrentBlock("tblcontent");

				// change row color
				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$newuser = new ilObjUser($grp_data["owner"]);
				$obj_link = "grp_details.php?ref_id=".$grp_data["ref_id"];
				$obj_icon = "icon_".$grp_data["type"]."_b.gif";

				$this->tpl->setVariable("GRP_NAME", $grp_data["title"]);
				$this->tpl->setVariable("GRP_LINK", $obj_link);
				/*if($lgrp_data["type"] == "grp")		// Test
				{
					//$this->tpl->setVariable("EDIT_LINK","content/lm_edit.php?lm_id=".$lr_data["obj_id"]);
					$this->tpl->setVariable("TXT_EDIT", "(".$this->lng->txt("edit").")");
					$this->tpl->setVariable("VIEW_LINK","content/lm_presentation.php?lm_id=".$grp_data["obj_id"]);
					$this->tpl->setVariable("TXT_VIEW", "(".$this->lng->txt("view").")");
				}*/
				//$this->tpl->setVariable("IMG", $obj_icon);
				//$this->tpl->setVariable("ALT_IMG", $lng->txt("obj_".$lr_data["type"]));
				$this->tpl->setVariable("GRP_DESC", $grp_data["desc"]);
				$this->tpl->setVariable("GRP_OWNER", $newuser->getFullname() );
				//$this->tpl->setVariable("STATUS", "N/A");
				//$this->tpl->setVariable("LAST_VISIT", "N/A");
				//$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$this->tpl->setVariable("GRP_CONTEXT", ilObjGroup::getContextPath2($grp_data["ref_id"]));

				$this->tpl->parseCurrentBlock("tblcontent");
			}
		
		}

	}
	
	
	function showDetails()
	{
		$this->getTemplateFile("details", "grp");
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.grp_details.html");
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		
		/*$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","groups.php");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("group_summary"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","groups.php");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("treeview"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock("btn_row");
		$this->tpl->setCurrentBlock("content");
		//$this->tpl->setVariable("TXT_GROUP_DETAILS", $this->lng->txt("group_details"));
		//$this->tpl->parseCurrentBlock();*/
		$this->tpl->setVariable("TXT_GRP_TITLE", $this->lng->txt("group_members"));
		$this->tpl->setCurrentBlock("groupheader");
		
		$this->tpl->setVariable("TXT_NAME", $this->lng->txt("name"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_OWNER", $this->lng->txt("owner"));
		$this->tpl->setVariable("TXT_ROLE_IN_GROUP", $this->lng->txt("role"));
		$this->tpl->parseCurrentBlock("grouphesder");
		//echo ("getID: ".$this->object->getId());
		//echo("getRefID: ".$this->object->getRefId());
		//$newGrp = new ilObjGroup($this->object->getId());
		//$member_ids = $newGrp->object->getGroupMemberIds($this->object->getId());
		//var_dump (member_ids);
		//$member_arr = array();
		/*foreach ($member_ids as $member_arr)
		{
			array_push($member_arr, new ilObjUser($member_id));
		}
		$i=0;
		/*$this->tpl->setCurrentBlock("group_row");
		$this->tpl->setVariable("Grp_USER", "User");
		$this->tpl->setVariable("TXT_FIRSTNAME", "Firstname");
		$this->tpl->setVariable("TXT_LASTNAME", "Lastname");
		//$this->tpl->setVariable("TXT_JOINDATE", "Join date");
		$this->tpl->setVariable("TXT_ROLE", "Role");
		//$this->tpl->setVariable("TXT_FUNCTIONS", "Functions");

		$this->tpl->parseCurrentBlock();*/
		
		/*foreach($member_arr as $member)
		{	
			$grp_role_id = $this->object->getGroupRoleId($member->getId());
			$newObj 	 = new ilObject($grp_role_id,false);
					
			//todo: chechAccess, each user sees only the symbols belonging to his rigths
			//$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
			/*$link_change = "adm_object.php?cmd=editMembership&mem_id=".$member->getId();		
			$link_leave = "adm_object.php?type=grp&cmd=leaveGrp&ref_id=".$this->ref_id."&mem_id=".$member->getId();					
			$img_contact = "pencil";
			$img_change = "change";
			$img_leave = "group_out";						
			$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
			$val_change = ilUtil::getImageTagByType($img_change, $this->tpl->tplPath);
			$val_leave  = ilUtil::getImageTagByType($img_leave,
			$this->tpl->tplPath);*/
	/*
			// BEGIN TABLE MEMBERS
			$this->tpl->setCurrentBlock("member_row");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			//$this->tpl->setVariable("CSS_ROW",$css_row);
			//$this->tpl->setVariable("LOGIN",$member->getLogin());
			$this->tpl->setVariable("MEMBER_NAME",$member->getFullName());
			//$this->tpl->setVariable("LASTNAME", $member->getLastname());
			//$this->tpl->setVariable("ANNOUNCEMENT_DATE", "Announcement Date");
			//$this->tpl->setVariable("ROLENAME", $newObj->getTitle());
			
			/*$this->tpl->setVariable("LINK_CONTACT", $link_contact);
			$this->tpl->setVariable("CONTACT", $val_contact);
			$this->tpl->setVariable("LINK_CHANGE", $link_change);
			$this->tpl->setVariable("CHANGE", $val_change);
			$this->tpl->setVariable("LINK_LEAVE", $link_leave);
			$this->tpl->setVariable("LEAVE", $val_leave);						
			$this->tpl->parseCurrentBlock();
			// END TABLE MEMBERS
		}*/
		$lr_arr = array();
		$objects = $this->tree->getChilds($this->object->getId(),"title");
		//var_dump ($objects);
		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				//var_dump ($object);
				if ($object["type"] == "le")// && $rbacsystem->checkAccess('visible',$objects["child"]))
				{
					
					$lr_arr[$key] = $object;
					//var_dump ($lr_arr);
				}
			}
		}
		//var_dump ($lr_arr);
		$maxcount = count($lr_arr);
		//echo ($maxcount);		// for numinfo in table footer
		require_once "./include/inc.sort.php";
		$lr_arr = sortArray($lr_arr,$_GET["sort_by"],$_GET["sort_order"]);
		//$lr_arr = array_slice($lr_arr,$offset,$limit);
		
		
			$this->tpl->setCurrentBlock("loheader");
			$this->tpl->setVariable("TXT_LO_TITLE", $this->lng->txt("lo"));
			$this->tpl->setVariable("TXT_LO_NAME", $this->lng->txt("name"));
			$this->tpl->setVariable("TXT_LO_DESC", $this->lng->txt("description"));
			$this->tpl->setVariable("TXT_LO_OWNER", $this->lng->txt("owner"));
			$this->tpl->setVariable("TXT_LO_LAST_CHANGE", $this->lng->txt("last_change"));
		
		//var_dump ($lr_arr);
		$num = 0;
		foreach ($lr_arr as $lr_data)
		{
			$this->tpl->setCurrentBlock("locontent");
			//var_dump ($lr_data);
			// change row color
			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
			$num++;

			//$obj_link = "lo_view.php?lm_id=".$lr_data["ref_id"];
			$obj_icon = "icon_".$lr_data["type"]."_b.gif";

			//$tpl->setVariable("TITLE", $lr_data["title"]);
			//$tpl->setVariable("LO_LINK", $obj_link);

			/*if ($lr_data["type"] == "le")		// Test
			{
				$tpl->setVariable("EDIT_LINK","content/lm_edit.php?lm_id=".$lr_data["obj_id"]);
				$tpl->setVariable("TXT_EDIT", "(".$lng->txt("edit").")");
				$tpl->setVariable("VIEW_LINK","content/lm_presentation.php?lm_id=".$lr_data["obj_id"]);
				$tpl->setVariable("TXT_VIEW", "(".$lng->txt("view").")");
			}	*/

			//$tpl->setVariable("IMG", $obj_icon);
			//$tpl->setVariable("ALT_IMG", $lng->txt("obj_".$lr_data["type"]));
			$this->tpl->setVariable("LO_DESC", $lr_data["description"]);
			$this->tpl->setVariable("LO_NAME", $lr_data["title"]);
			//$tpl->setVariable("LO_OWNER", $lr_data["title"]);
			//$tpl->setVariable("STATUS", "N/A");
			//$tpl->setVariable("LAST_VISIT", "N/A");
			$this->tpl->setVariable("LO_LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
			//echo (ilObjGroup::getContextPath2($lr_data["ref_id"]));
			$this->tpl->setVariable("LO_CONTEXTPATH", ilObjGroup::getContextPath2($lr_data["ref_id"]));
			$this->tpl->parseCurrentBlock("locontent");
		}	
		
		
		
		//$this->tpl->parseCurrentBlock();

		//$this->tpl->setCurrentBlock("content");
		
		//$this->tpl->show();
		
	}
} // END class.GroupObjectOut
?>
