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
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* $Id$Id: class.ilObjGroupGUI.php,v 1.44 2003/10/26 18:04:29 mmaschke Exp $
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjGroupGUI extends ilObjectGUI
{	
	/**
	* Constructor
	* @access	public
	*/
	function ilObjGroupGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "grp";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}

	/**
	* create new object form
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
		$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];
		$data["group_status"] = $_SESSION["error_post_vars"]["group_status"];
		$data["password"] =  $_SESSION["error_post_vars"]["password"];
		$data["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];//$this->grp_object->getExpirationDateTime()[0];
		$data["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];//$this->grp_object->getExpirationDateTime()[1];
//		$data["registration_flag"] = $_SESSION["error_post_vars"]["registration_flag"];
		
		$this->getTemplateFile("edit",$new_type);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);

			if ($this->prepare_output)
			{
				$this->tpl->parseCurrentBlock();
			}
		}

		$stati 	= array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));
//		//build form
		$grp_status = $_SESSION["error_post_vars"]["group_status"];
		$opts = ilUtil::formSelect($grp_status,"group_status",$stati,false,true);
//		$checked = array(0=>0,1=>0,2=>0);
		switch($_SESSION["error_post_vars"]["enable_registration"])
		{
			case 0: $checked[0]=1;
				break;
			case 1: $checked[1]=1;
				break;
			case 2: $checked[2]=1;
				break;		
//			default:$checked[0]=1;
		}
		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);
//		
		//build form
		$opts 	= ilUtil::formSelect(0,"group_status",$stati,false,true);
		$cb_registration = ilUtil::formCheckbox(0, "enable_registration", 1, false);
		
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
	
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		$this->tpl->setVariable("TXT_DISABLEREGISTRATION", $this->lng->txt("disabled"));				
		$this->tpl->setVariable("RB_NOREGISTRATION", $cb_registration[0]);				
		$this->tpl->setVariable("TXT_ENABLEREGISTRATION", $this->lng->txt("enabled"));						
		$this->tpl->setVariable("RB_REGISTRATION", $cb_registration[1]);				
		$this->tpl->setVariable("TXT_PASSWORDREGISTRATION", $this->lng->txt("password"));						
		$this->tpl->setVariable("RB_PASSWORDREGISTRATION", $cb_registration[2]);						

		$this->tpl->setVariable("TXT_EXPIRATIONDATE", $this->lng->txt("expiration_date"));						
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("DD.MM.YYYY"));						
		$this->tpl->setVariable("TXT_TIME", $this->lng->txt("HH:MM"));								
		
		$this->tpl->setVariable("CB_KEYREGISTRATION", $cb_keyregistration);				
		$this->tpl->setVariable("TXT_KEYREGISTRATION", $this->lng->txt("group_keyregistration"));		
		$this->tpl->setVariable("TXT_PASSWORD", $this->lng->txt("password"));				
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));

		
/*		
		$this->tpl->setVariable("CB_REGISTRATION", $cb_registration);				
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration"));		
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
*/		
	}


	/**
	* save group object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;
		
		include_once "./classes/class.ilGroup.php";
		$grp = new ilGroup();

		// check groupname
		if ($grp->groupNameExists($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("grp_name_exists"),$this->ilias->error_obj->MESSAGE);
		}
		
		// create and insert forum in objecttree
		$groupObj = parent::saveObject();

		// setup rolefolder & default local roles (admin & member)
		$roles = $groupObj->initDefaultRoles();

		// ...finally assign groupadmin role to creator of group object
//		$rbacadmin->assignUser($roles[0], $groupObj->getOwner(), "n");
		$groupObj->join($this->ilias->account->getId(),1); //join as admin=1
		
		ilObjUser::updateActiveRoles($groupObj->getOwner());
	
/************ old
		$groupObj = parent::saveObject();

		$rfoldObj = $groupObj->initRoleFolder();
		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)

		$groupObj->createDefaultGroupRoles($rfoldObj->getRefId());
		$groupObj->join($this->ilias->account->getId(),1); //join as admin=1
*/
		//0=no registration, 1=registration enabled
		$groupObj->setRegistrationFlag($_POST["enable_registration"]);
		//0=public,1=private,2=closed
		$groupObj->setGroupStatus($_POST["group_status"]);
	
		//save new group in grp_tree table
		$groupObj->createNewGroupTree($groupObj->getRefId());
		
		// always send a message
		sendInfo($this->lng->txt("grp_added"),true);
		header("Location: ".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}

	/**
	* update GroupObject
	* @access public
	*/
	function updateObject()
	{
		include_once "./classes/class.ilGroup.php";
		$grp = new ilGroup();

		// check groupname
		if ($grp->groupNameExists($_POST["Fobject"]["title"],$this->object->getId()))
		{
			$this->ilias->raiseError($this->lng->txt("grp_name_exists"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->setTitle($_POST["Fobject"]["title"]);
		$this->object->setDescription($_POST["Fobject"]["desc"]);
		$this->object->setGroupStatus($_POST["group_status"]);
		$this->object->setRegistrationFlag($_POST["enable_registration"]);

		// update object data
		$this->update = $this->object->update();

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		header("Location: ".$this->getReturnLocation("update","adm_object.php?ref_id=".$this->object->getRefId()));
		exit();
	}

	/**
	* edit Group
	* @access public
	*/
	function editObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$data = array();
			
		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];			
			$data["registration"] = $_SESSION["error_post_vars"]["registration"];			
		}
		else
		{
			$data["title"] = $this->object->getTitle();
			$data["desc"] = $this->object->getDescription();			
			$data["registration"] = $this->object->getRegistrationFlag();
		}

		$this->getTemplateFile("edit");

		foreach ($data as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		
		$stati = array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));

		//build form
		$grp_status = $this->object->getGroupStatus();
		$opts = ilUtil::formSelect($grp_status,"group_status",$stati,false,true);
		$cb_registration = ilUtil::formCheckbox($this->object->getRegistrationFlag(), "enable_registration", 1, false);
	
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&ref_id=".$this->ref_id));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("CMD_CANCEL", "cancel");		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		$this->tpl->setVariable("CB_REGISTRATION", $cb_registration);				
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration"));		
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
	}

	/**
	* displays confirmation form
	* @access public
	*/
	function confirmationObject($user_id="", $confirm, $cancel, $info="", $status="")
	{
		$this->data["cols"] = array("type", "title", "description", "last_change");

		if(is_array($user_id))
		{
			foreach($user_id as $id)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

				$this->data["data"]["$id"] = array(
					"type"        => $obj_data->getType(),
					"title"       => $obj_data->getTitle(),
					"desc"        => $obj_data->getDescription(),
					"last_update" => $obj_data->getLastUpdateDate(),

					);
			}
		}
		else
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate(),
				);
		}

		//write  in sessionvariables
		if(is_array($user_id))
			$_SESSION["saved_post"]["user_id"] = $user_id;
		else
			$_SESSION["saved_post"]["user_id"][0] = $user_id;

		if(isset($status))
			$_SESSION["saved_post"]["status"] = $status;

		$this->data["buttons"] = array( $confirm  => $this->lng->txt("confirm"),
						$cancel  => $this->lng->txt("cancel"));

		$this->getTemplateFile("confirm");
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.obj_confirm.html");
		infoPanel();

		sendInfo($this->lng->txt($info));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* leave Group
	* @access public
	*/
	function leaveGrpObject()
	{
		$member = array($_GET["mem_id"]);
		//set methods that are called after confirmation
		$confirm = "confirmedDeleteMember";
		$cancel  = "canceled";
		$info	 = "info_delete_sure";
		$status  = "";
		$this->confirmationObject($member, $confirm, $cancel, $info, $status);
	}

	/**
	* displays confirmation formular with users that shall be assigned to gorup
	* @access public
	*/
	function assignMemberObject()
	{
		$user_ids = $_POST["user_id"];

		if(isset($user_ids))
		{
			$confirm = "confirmedAssignMember";
			$cancel  = "canceled";
			$info	 = "info_assign_sure";
			$status  = $_SESSION["post_vars"]["status"];

			$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status);
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: adm_object.php?".$this->link_params);
		}

	}

	/**
	* assign new member to group
	* @access public
	*/
	function confirmedAssignMemberObject()
	{
		if(isset($_SESSION["saved_post"]["user_id"]) && isset($_SESSION["saved_post"]["status"]) )
		{
			//let new members join the group
			$newGrp = new ilObjGroup($this->object->getRefId(), true);

			foreach($_SESSION["saved_post"]["user_id"] as $new_member)
			{
				if(!$newGrp->join($new_member, $_SESSION["saved_post"]["status"]) )
					$this->ilias->raiseError("An Error occured while assigning user to group !",$this->ilias->error_obj->MESSAGE);
			}

			unset($_SESSION["saved_post"]);
		}

		header("Location: adm_object.php?".$this->link_params);
	}

	/**
	* displays confirmation formular with users that shall be removed from group
	* @access public
	*/
	function removeMemberObject()
	{
		$user_ids = array();
		if(isset($_POST["user_id"]))
			$user_ids = $_POST["user_id"];
		else if(isset($_GET["mem_id"]))
			$user_ids = $_GET["mem_id"];
		if(isset($user_ids))
		{
			$confirm = "confirmedRemoveMember";
			$cancel  = "canceled";
			$info	 = "info_delete_sure";
			$status  = "";
			$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status);
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: adm_object.php?".$this->link_params."&cmd=members");
		}
	}

	/**
	* remove members from group
	* @access public
	*/
	function confirmedRemoveMemberObject()
	{
		global $rbacsystem,$ilias;

		if(isset($_SESSION["saved_post"]["user_id"]) )
		{
			foreach($_SESSION["saved_post"]["user_id"] as $mem_id)
			{
				$newGrp = new ilObjGroup($_GET["ref_id"],true);
				if($rbacsystem->checkAccess('leave',$_GET["ref_id"]))
				{
					//check ammount of members
					if(count($newGrp->getGroupMemberIds()) == 1)
					{
						if($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
						{
							//GROUP DELETE
							$this->ilias->raiseError("Gruppe loeschen, da letztes Mitglied!",$this->ilias->error_obj->MESSAGE);
						}
						else
							$this->ilias->raiseError("You do not have the permissions to delete this group!",$this->ilias->error_obj->MESSAGE);
					}
					else
					{
						//MEMBER LEAVES GROUP
						if($this->object->isMember($mem_id) && !$this->object->isAdmin($mem_id))
						{
							if(!$newGrp->leave($mem_id))
								$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
						}
						else	//ADMIN LEAVES GROUP
						if($this->object->isAdmin($mem_id))
						{
							if(count($this->object->getGroupAdminIds()) <= 1 )
							{
								$this->ilias->raiseError("At least one group administrator is required! Please entitle a new group administrator first ! ",$this->ilias->error_obj->WARNING);
							}
							else if(!$newGrp->leave($mem_id))
								$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
						}
					}
				}
				else
					$this->ilias->raiseError("You are not allowed to leave this group!",$this->ilias->error_obj->MESSAGE);
			}
		}
		unset($_SESSION["saved_post"]);
		header("Location: adm_object.php?".$this->link_params."&cmd=members");
	}


	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		global $ilias,$tpl;

		include_once "./classes/class.ilTableGUI.php";

		$member_ids = array();

		if(isset($_POST["user_id"]))
			$member_ids = $_POST["user_id"];
		else if(isset($_GET["mem_id"]))
			$member_ids[0] = $_GET["mem_id"];

		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$stati = array(0=>"grp_member_role",1=>"grp_admin_role");

		//build data structure
		foreach($member_ids as $member_id)
		{
			$member =& $ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $newGrp->getMemberStatus($member_id);

			$this->data["data"][$member->getId()]= array(
				"login"        => $member->getLogin(),
				"firstname"       => $member->getFirstname(),
				"lastname"        => $member->getLastname(),
				"grp_role" => ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."]",$stati,false,true)
				);
			unset($member);
		}

		$this->getTemplateFile("chooseuser","grp");
		infoPanel();

		$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		$this->data["buttons"] = array( "updateMemberStatus"  => $this->lng->txt("confirm"),
						"members"  => $this->lng->txt("cancel"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",4);
		$this->tpl->setVariable("TPLPATH",$this->ilias->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		// create table
		$tbl = new ilTableGUI($this->data["data"]);
		// title & header columns
		$tbl->setTitle($this->lng->txt("change member status"),"icon_usr_b.gif",$this->lng->txt("change member status"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("status")));
		$tbl->setHeaderVars(array("firstname","lastname","role","status"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(10);
		$tbl->setOffset(0);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
	}


	/**
	* displays search form for new users
	* @access public
	*/
	function newMembersObject()
	{
		$this->getTemplateFile("newmember","grp");

		$this->tpl->setVariable("TXT_MEMBER_NAME", $this->lng->txt("username"));
		$this->tpl->setVariable("TXT_STATUS", $this->lng->txt("member_status"));

		$radio_member = ilUtil::formRadioButton($_POST["status"] ? 0:1,"status",0);
		$radio_admin  = ilUtil::formRadioButton($_POST["status"] ? 1:0,"status",1);
		$this->tpl->setVariable("RADIO_MEMBER", $radio_member);
		$this->tpl->setVariable("RADIO_ADMIN", $radio_admin);
		$this->tpl->setVariable("TXT_MEMBER_STATUS", "Member");
		$this->tpl->setVariable("TXT_ADMIN_STATUS", "Admin");
		$this->tpl->setVariable("TXT_SEARCH", "Search");

		if(isset($_POST["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_POST["search_user"]);
		else if(isset($_GET["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_GET["search_user"]);

		$this->tpl->setVariable("FORMACTION_NEW_MEMBER", "adm_object.php?type=grp&cmd=newMembers&ref_id=".$_GET["ref_id"]);//"&search_user=".$_POST["search_user"]
		$this->tpl->parseCurrentBlock();

		//query already started ?
		if( (isset($_POST["search_user"]) && isset($_POST["status"]) ) || ( isset($_GET["search_user"]) && isset($_GET["status"]) ) )//&& isset($_GET["ref_id"]) )
		{
			$member_ids = ilObjUser::searchUsers($_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"]);

			foreach($member_ids as $member)
			{
				$this->data["data"][$member["usr_id"]]= array(
					"check"		=> ilUtil::formCheckBox(0,"user_id[]",$member["usr_id"]),
					"login"        => $member["login"],
					"firstname"       => $member["firstname"],
					"lastname"        => $member["lastname"]
					);

			}

			//display search results
			infoPanel();

			$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");
			// load template for table content data

			$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

			$this->data["buttons"] = array( "assignMember"  => $this->lng->txt("assign"),
							"canceled"  => $this->lng->txt("cancel"));

			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS",4);
			$this->tpl->setVariable("TPLPATH",$this->tplPath);

			foreach ($this->data["buttons"] as $name => $value)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME",$name);
				$this->tpl->setVariable("BTN_VALUE",$value);
				$this->tpl->parseCurrentBlock();
			}

			//sort data array
			include_once "./include/inc.sort.php";
			$this->data["data"] = sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
			$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

			// create table
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI($output);
			// title & header columns
			$tbl->setTitle($this->lng->txt("member list"),"icon_usr_b.gif",$this->lng->txt("member list"));
			$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
			$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname")));
			$tbl->setHeaderVars(array("check","login","firstname","lastname"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"],"search_user"=>$_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"],"status"=>$_POST["status"] ? $_POST["status"] : $_GET["status"]));

			$tbl->setColumnWidth(array("5%","25%","35%","35%"));

			// control
			$tbl->setOrderColumn($_GET["sort_by"]);
			$tbl->setOrderDirection($_GET["sort_order"]);
			$tbl->setLimit($_GET["limit"]);
			$tbl->setOffset($_GET["offset"]);
			$tbl->setMaxCount(count($this->data["data"]));

			$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

			// render table
			$tbl->render();
		}
	}

	/**
	* displays form with all members of group
	* @access public
	*/
	function membersObject()
	{
		global $rbacsystem;
		//check Access
  		if(!$rbacsystem->checkAccess("read,leave",$this->object->getRefId() ))
		{
			$this->ilias->raiseError("Permission denied !",$this->ilias->error_obj->MESSAGE);
		}

		$img_contact = "pencil";
		$img_change = "change";
		$img_leave = "group_out";
		$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
		$val_change = ilUtil::getImageTagByType($img_change, $this->tpl->tplPath);
		$val_leave  = ilUtil::getImageTagByType($img_leave, $this->tpl->tplPath);

		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$member_ids = $newGrp->getGroupMemberIds($_GET["ref_id"]);
		$admin_ids = $newGrp->getGroupAdminIds($_GET["ref_id"]);

		foreach($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);

			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
			$link_change = "adm_object.php?cmd=changeMember&ref_id=".$this->ref_id."&mem_id=".$member->getId();
			$link_leave = "adm_object.php?type=grp&cmd=removeMember&ref_id=".$_GET["ref_id"]."&mem_id=".$member->getId();

			//build function
			if(in_array($_SESSION["AccountId"], $admin_ids))
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}
			if(in_array($_SESSION["AccountId"], $admin_ids) || $member->getId() == $_SESSION["AccountId"])
			{
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";

			}



			$grp_role_id = $newGrp->getGroupRoleId($member->getId());
			$newObj	     = new ilObject($grp_role_id,false);

			$this->data["data"][$member->getId()]= array(
			        "check"		=> ilUtil::formCheckBox(0,"user_id[]",$member->getId()),
				"login"        => $member->getLogin(),
				"firstname"       => $member->getFirstname(),
				"lastname"        => $member->getLastname(),
				"grp_role" => $newObj->getTitle(),
				"functions" => "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions
				);
			unset($member_functions);
			unset($member);
			unset($newObj);
		}

		$this->getTemplateFile("chooseuser","grp");
		infoPanel();

		$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		$this->data["buttons"] = array( "removeMember"  => $this->lng->txt("remove"),
						"changeMember"  => $this->lng->txt("change"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",6);
		$this->tpl->setVariable("TPLPATH",$this->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		//sort data array
		include_once "./include/inc.sort.php";
		include_once "./classes/class.ilTableGUI.php";

		$this->data["data"] = sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// create table
		$tbl = new ilTableGUI($output);
		// title & header columns
		$tbl->setTitle($this->lng->txt("member list"),"icon_usr_b.gif",$this->lng->txt("member list"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("functions")));
		$tbl->setHeaderVars(array("check","login","firstname","lastname","role","functions"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));

		$tbl->setColumnWidth(array("5%","15%","30%","30%","10%","10%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$tbl->render();
	}

	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function updateMemberStatusObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write",$this->object->getRefId()) )
		{
			$this->ilias->raiseError("No permissions to change member status!",$this->ilias->error_obj->WARNING);
		}
		else
		{
			if(isset($_POST["member_status_select"]))
			{
				foreach($_POST["member_status_select"] as $key=>$value)
				{
					$this->object->setMemberStatus($key,$value);
				}
			}
		}
		//TODO: link back
		header("Location: adm_object.php?".$this->link_params."&cmd=members");
	}
} // END class.ilObjGroupGUI
?>
