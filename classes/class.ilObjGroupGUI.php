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
* $Id$Id: class.ilObjGroupGUI.php,v 1.79 2004/04/28 14:59:49 smeyer Exp $
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";
require_once "class.ilRegisterGUI.php";

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
	
	function _forwards()
	{
		return array("ilRegisterGUI");
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			case "ilregistergui":
				$this->ctrl->setReturn($this, "");   // ###
				$reg_gui = new ilRegisterGUI();
				$reg_gui->executeCommand();
				break;
	
			default:
				if ($this->object->requireRegistration() and !$this->object->isUserRegistered())
				{
					$this->ctrl->redirectByClass("ilRegisterGUI", "showRegistrationForm");
				}

				if (empty($cmd))
				{
					$this->ctrl->returnToParent($this);
					$cmd = "view";
				}

				$cmd .= "Object";
				$this->$cmd();
				break;
		}		
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
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$data["fields"]["password"] = $_SESSION["error_post_vars"]["password"];
			$data["fields"]["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];
			$data["fields"]["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];
		}
		else
		{
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";
			$data["fields"]["password"] = "";
			$data["fields"]["expirationdate"] = ilFormat::getDateDE();
			$data["fields"]["expirationtime"] = "";
		}

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

		$grp_status = $_SESSION["error_post_vars"]["group_status"];

		$checked = array(0=>0,1=>0,2=>0);

		switch ($_SESSION["error_post_vars"]["enable_registration"])
		{
			case 0:
				$checked[0]=1;
				break;

			case 1:
				$checked[1]=1;
				break;

			case 2:
				$checked[2]=1;
				break;

			default:
				$checked[0]=1;
				break;
		}

		//build form
		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);

		$opts 	= ilUtil::formSelect(0,"group_status",$stati,false,true);

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));

		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration_mode"));

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

		$this->tpl->setVariable("TXT_EXPIRATIONDATE", $this->lng->txt("group_registration_expiration_date"));
		$this->tpl->setVariable("TXT_EXPIRATIONTIME", $this->lng->txt("group_registration_expiration_time"));
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("DD.MM.YYYY"));
		$this->tpl->setVariable("TXT_TIME", $this->lng->txt("HH:MM"));

		$this->tpl->setVariable("CB_KEYREGISTRATION", $cb_keyregistration);
		$this->tpl->setVariable("TXT_KEYREGISTRATION", $this->lng->txt("group_keyregistration"));
		$this->tpl->setVariable("TXT_PASSWORD", $this->lng->txt("password"));
		$this->tpl->setVariable("SELECT_GROUPSTATUS", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));

	}


	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function canceledObject()
	{
		$return_location = $_GET["cmd_return_location"];
		//$return_location = "members";
				
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
	}

	/**
	* save group object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		// check registration & password
		if ($_POST["enable_registration"] == 2 and empty($_POST["password"]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_password"),$this->ilErr->MESSAGE);
		}

		// check groupname
		if (ilUtil::groupNameExists($_POST["Fobject"]["title"]))

		{
			$this->ilErr->raiseError($this->lng->txt("grp_name_exists"),$this->ilErr->MESSAGE);
		}

		// create and insert forum in objecttree
		$groupObj = parent::saveObject();

		// setup rolefolder & default local roles (admin & member)
		$roles = $groupObj->initDefaultRoles();

		// ...finally assign groupadmin role to creator of group object
		$groupObj->addMember($this->ilias->account->getId(),$groupObj->getDefaultAdminRole());

		$groupObj->setRegistrationFlag($_POST["enable_registration"]);//0=no registration, 1=registration enabled 2=passwordregistration
		$groupObj->setPassword($_POST["password"]);
		$groupObj->setExpirationDateTime($_POST["expirationdate"]." ".$_POST["expirationtime"].":00");
		$groupObj->setGroupStatus($_POST["group_status"]);		//0=public,1=private,2=closed

		$this->ilias->account->addDesktopItem($groupObj->getRefId(),"grp");		
		
		// always send a message
		sendInfo($this->lng->txt("grp_added"),true);
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}

	/**
	* update GroupObject
	* @access public
	*/
	function updateObject()
	{
		global $rbacsystem;
		
		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		if ($_POST["enable_registration"] == 2 && empty($_POST["password"]) || empty($_POST["expirationdate"]) || empty($_POST["expirationtime"]) )//Password-Registration Mode
		{
			$this->ilErr->raiseError($this->lng->txt("grp_err_registration_data"),$this->ilErr->MESSAGE);
		}
		// check groupname
		if (ilUtil::groupNameExists(ilUtil::stripSlashes($_POST["Fobject"]["title"]),$this->object->getId()))
		{
			$this->ilErr->raiseError($this->lng->txt("grp_name_exists"),$this->ilErr->MESSAGE);
		}

		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilErr->raiseError("No permissions to change group status!",$this->ilErr->MESSAGE);
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));

		if ($_POST["enable_registration"] == 2 && !ilUtil::isPassword($_POST["password"]))
		{
			$this->ilErr->raiseError($this->lng->txt("passwd_invalid"),$this->ilErr->MESSAGE);
		}

		$this->object->setRegistrationFlag($_POST["enable_registration"]);
		$this->object->setPassword($_POST["password"]);
		$this->object->setExpirationDateTime($_POST["expirationdate"]." ".$_POST["expirationtime"].":00");

		if ($_POST["group_reset"] == 1)
		{
			$this->object->setGroupStatus($_POST["group_status"]);
		}

		$this->update = $this->object->update();

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this,"members")));
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
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$data["registration"] = $_SESSION["error_post_vars"]["registration"];
			$data["password"] = $_SESSION["error_post_vars"]["password"];
			$data["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];//$datetime[0];//$this->grp_object->getExpirationDateTime()[0];
			$data["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];//$datetime[1];//$this->grp_object->getExpirationDateTime()[1];

		}
		else
		{
			$data["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$data["desc"] = $this->object->getDescription();
			$data["registration"] = $this->object->getRegistrationFlag();
			$data["password"] = $this->object->getPassword();
			$datetime = $this->object->getExpirationDateTime();

			$data["expirationdate"] = $datetime[0];//$this->grp_object->getExpirationDateTime()[0];
			$data["expirationtime"] =  substr($datetime[1],0,5);//$this->grp_object->getExpirationDateTime()[1];

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

		$grp_status_options = ilUtil::formSelect(0,"group_status",$stati,false,true);
		$checked = array(0=>0,1=>0,2=>0);

		switch ($this->object->getRegistrationFlag())
		{
			case 0:
				$checked[0]=1;
				break;

			case 1:
				$checked[1]=1;
				break;

			case 2:
				$checked[2]=1;
				break;
		}

		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);
		$cb_reset		    = ilUtil::formCheckBox(0,"group_reset",1,false);

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("grp_edit"));
		//$this->tpl->setVariable("TARGET",$this->getTargetFrame("save","content"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "canceled");
		$this->tpl->setVariable("CMD_SUBMIT", "update");

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration_mode"));

		$this->tpl->setVariable("TXT_DISABLEREGISTRATION", $this->lng->txt("disabled"));
		$this->tpl->setVariable("RB_NOREGISTRATION", $cb_registration[0]);
		$this->tpl->setVariable("TXT_ENABLEREGISTRATION", $this->lng->txt("enabled"));
		$this->tpl->setVariable("RB_REGISTRATION", $cb_registration[1]);
		$this->tpl->setVariable("TXT_PASSWORDREGISTRATION", $this->lng->txt("password"));
		$this->tpl->setVariable("RB_PASSWORDREGISTRATION", $cb_registration[2]);

		$this->tpl->setVariable("TXT_EXPIRATIONDATE", $this->lng->txt("group_registration_expiration_date"));
		$this->tpl->setVariable("TXT_EXPIRATIONTIME", $this->lng->txt("group_registration_expiration_time"));		
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("DD.MM.YYYY"));
		$this->tpl->setVariable("TXT_TIME", $this->lng->txt("HH:MM"));

		$this->tpl->setVariable("CB_KEYREGISTRATION", $cb_keyregistration);
		$this->tpl->setVariable("TXT_KEYREGISTRATION", $this->lng->txt("group_keyregistration"));
		$this->tpl->setVariable("TXT_PASSWORD", $this->lng->txt("password"));
		$this->tpl->setVariable("SELECT_GROUPSTATUS", $grp_status_options);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("TXT_RESET", $this->lng->txt("group_reset"));
		$this->tpl->setVariable("CB_RESET", $cb_reset);
	}

	/**
	* displays confirmation form
	* @access public
	*/
	function confirmationObject($user_id="", $confirm, $cancel, $info="", $status="",$a_cmd_return_location = "")
	{
		$this->data["cols"] = array("type", "title", "description", "last_change");

		if (is_array($user_id))
		{
			foreach ($user_id as $id)
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
		{
			$_SESSION["saved_post"]["user_id"] = $user_id;
		}
		else
		{
			$_SESSION["saved_post"]["user_id"][0] = $user_id;
		}

		if (isset($status))
		{
			$_SESSION["saved_post"]["status"] = $status;
		}

		$this->data["buttons"] = array( $cancel  => $this->lng->txt("cancel"),
						$confirm  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		infoPanel();

		sendInfo($this->lng->txt($info));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this)."&cmd_return_location=".$a_cmd_return_location);

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
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
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
		$return  = "";
		$this->confirmationObject($member, $confirm, $cancel, $info, $status, $return);
	}

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function assignMemberObject()
	{
		$user_ids = $_POST["id"];

		if (empty($user_ids[0]))
		{
			// TODO: jumps back to grp content. go back to last search result
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		foreach ($user_ids as $new_member)
		{
			if (!$this->object->addMember($new_member,$this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}
		}

		unset($_SESSION["saved_post"]);

		sendInfo($this->lng->txt("grp_msg_member_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* displays confirmation formular with users that shall be removed from group
	* @access public
	*/
	function removeMemberObject()
	{
		$user_ids = array();

		if (isset($_POST["user_id"]))
		{
			$user_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$user_ids[] = $_GET["mem_id"];
		}

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}
		
		if (count($user_ids) == 1 and $this->ilias->account->getId() != $user_ids[0])
		{
			if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]) 
				and !in_array($this->ilias->account->getId(),$this->object->getGroupAdminIds()))
			{
				$this->ilErr->raiseError($this->lng->txt("grp_err_no_permission"),$this->ilErr->MESSAGE);
			}
		}
		//bool value: says if $users_ids contains current user id
		$is_dismiss_me = array_search($this->ilias->account->getId(),$user_ids);
		
		$confirm = "confirmedRemoveMember";
		$cancel  = "canceled";
		$info	 = ($is_dismiss_me !== false) ? "grp_dismiss_myself" : "grp_dismiss_member";
		$status  = "";
		$return  = "members";
		$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status, $return);
	}

	/**
	* remove members from group
	* TODO: set return location to parent object if user removes himself
	* TODO: allow user to remove himself when he is not group admin
	* @access public
	*/
	function confirmedRemoveMemberObject()
	{
		//User needs to have administrative rights to remove members...
		foreach($_SESSION["saved_post"]["user_id"] as $member_id)
		{
			$err_msg = $this->object->removeMember($member_id);

			if (strlen($err_msg) > 0)
			{
				$this->ilErr->raiseError($this->lng->txt($err_msg),$this->ilErr->MESSAGE);
			}
		}

		unset($_SESSION["saved_post"]);

		sendInfo($this->lng->txt("grp_msg_membership_annulled"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}


	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}

		$member_ids = array();

		if (isset($_POST["user_id"]))
		{
			$member_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$member_ids[0] = $_GET["mem_id"];
		}

		if (empty($member_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]) 
			and !in_array($this->ilias->account->getId(),$this->object->getGroupAdminIds()))
		{
			$this->ilErr->raiseError($this->lng->txt("grp_err_no_permission"),$this->ilErr->MESSAGE);
		}

		$local_roles = $this->object->getLocalGroupRoles();

		$flipped_local_roles = array_flip($local_roles);
		$stati = array();
		$stati = $flipped_local_roles;

		//build data structure
		foreach ($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $this->object->getMemberRoles($member_id);

			$this->data["data"][$member->getId()]= array(
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"grp_role"	=> ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."][]",$stati,true,true,3)
				);
		}
		
		unset($member);
		
		infoPanel();

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->data["buttons"] = array( "canceled"  => $this->lng->txt("cancel"),
										"updateMemberStatus"  => $this->lng->txt("confirm"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",4);
		//$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		//sort data array
		$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		
		// create table
		include_once "./classes/class.ilTableGUI.php";

		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("grp_mem_change_status"),"icon_usr_b.gif",$this->lng->txt("grp_mem_change_status"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role")));
		$tbl->setHeaderVars(array("login","firstname","lastname","role"),$this->ctrl->getParameterArray($this,"",false));

		$tbl->setColumnWidth(array("20%","20%","20%","40%"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();

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
	
	/**
	* display group members
	*/
	function membersObject()
	{
		global $rbacsystem;

		$admin_ids = $this->object->getGroupAdminIds();
		$member_ids = $this->object->getGroupMemberIds($this->object->getRefId());

		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}
		// TODO: search order is incorrect

		//if current user is admin he is able to add new members to group
		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_send_mail")."\" title=\"".$this->lng->txt("grp_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";
		$val_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("grp_mem_change_status")."\" border=\"0\" vspace=\"0\"/>";
		$val_leave = "<img src=\"".ilUtil::getImagePath("icon_group_out_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_leave")."\" title=\"".$this->lng->txt("grp_mem_leave")."\" border=\"0\" vspace=\"0\"/>";

		$account_id = $this->ilias->account->getId();

		foreach ($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$link_contact = "mail_new.php?type=new&mail_data[rcp_to]=".$member->getLogin();
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$member->getId();

			if (($member_id == $account_id && $rbacsystem->checkAccess('leave',$this->ref_id,'usr')) || $rbacsystem->checkAccess("delete",$this->object->getRefId() ))
			{
				$link_leave = $this->ctrl->getLinkTarget($this,"RemoveMember")."&mem_id=".$member->getId();
			}

			//build function
			if ($rbacsystem->checkAccess("delete,write",$this->object->getRefId() ) )
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}

			if (($member_id == $account_id && $rbacsystem->checkAccess('leave',$this->ref_id,'usr')) || $rbacsystem->checkAccess("delete",$this->object->getRefId() ) )
			{
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";
			}

			$grp_role_id = $this->object->getMemberRoles($member->getId());
			$str_member_roles ="";

			if (is_array($grp_role_id))
			{
				$count = count($grp_role_id);

				foreach ($grp_role_id as $role_id)
				{
					$count--;
					$newObj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
					$str_member_roles .= $newObj->getTitle();

					if ($count > 0)
					{
						$str_member_roles .= ",";
					}
				}
			}
			else
			{
				$newObj =& $this->ilias->obj_factory->getInstanceByObjId($grp_role_id);
				$str_member_roles = $newObj->getTitle();
			}

			if ($rbacsystem->checkAccess("delete,write",$this->object->getRefId()))
			{
				$this->data["data"][$member->getId()]= array(
					"check"		=> ilUtil::formCheckBox(0,"user_id[]",$member->getId()),
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"grp_role"	=> $str_member_roles,
					"functions"	=> "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions
					);
			}
			else
			{
				//discarding the checkboxes
				$this->data["data"][$member->getId()]= array(
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"grp_role"	=> $newObj->getTitle(),
					"functions"	=> "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions
					);
			}

			unset($member_functions);
			unset($member);
			unset($newObj);
		}

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this,"post"));

		$this->data["buttons"] = array("RemoveMember"  => $this->lng->txt("remove"),
									   "changeMember"  => $this->lng->txt("change"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write,delete",$this->object->getRefId()))
		{
			//user is administrator
			$this->tpl->setVariable("COLUMN_COUNTS",6);

			foreach ($this->data["buttons"] as $name => $value)
			{
				$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME",$name);
				$this->tpl->setVariable("BTN_VALUE",$value);
				$this->tpl->parseCurrentBlock();
			}

			$subobj[0] = $this->lng->txt("member");
			$opts = ilUtil::formSelect(12,"new_type", $subobj, false, true);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "searchUserForm");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}

		$maxcount = count($this->data["data"]);
		
		//sort data array
		$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		
		// create table
		include_once "./classes/class.ilTableGUI.php";

		$tbl = new ilTableGUI($output);
		
		$this->ctrl->setParameter($this,"cmd","members");

		// title & header columns
		$tbl->setTitle($this->lng->txt("members"),"icon_usr_b.gif",$this->lng->txt("group_members"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("delete,write",$this->object->getRefId()))
		{
			//user must be administrator
			$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("functions")));
			$tbl->setHeaderVars(array("","login","firstname","lastname","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","15%","30%","30%","10%","10%"));
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			//user must be member
			$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("functions")));
			$tbl->setHeaderVars(array("login","firstname","lastname","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("20%","30%","30%","10%","10%"));
		}

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->render();
	}


	function showNewRegistrationsObject()
	{
		global $rbacsystem;

		//get new applicants
		$applications = $this->object->getNewRegistrations();
		
		if (!$applications)
		{
			$this->ilErr->raiseError($this->lng->txt("no_applications"),$this->ilErr->MESSAGE);
		}
		
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}

		$img_contact = "pencil";
		$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);

		foreach ($applications as $applicant)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($applicant->user_id);

			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$user->getLogin();
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$user->getId();
			$member_functions = "<a href=\"$link_change\">$val_change</a>";

			$this->data["data"][$user->getId()]= array(
				"check"		=> ilUtil::formCheckBox(0,"user_id[]",$user->getId()),
				"username"	=> $user->getLogin(),
				"fullname"	=> $user->getFullname(),
				"subject"	=> $applicant->subject,
				"date" 		=> $applicant->application_date,
				"functions"	=> "<a href=\"$link_contact\">".$val_contact."</a>"
				);

				unset($member_functions);
				unset($user);
		}

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this,"post"));

		$this->data["buttons"] = array( "refuseApplicants"  => $this->lng->txt("refuse"),
										"assignApplicants"  => $this->lng->txt("assign"));

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		//prepare buttons [cancel|assign]
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		if (isset($this->data["data"]))
		{
			//sort data array
			$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
			$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		}

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("COLUMN_COUNTS",6);
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		// create table
		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("group_new_registrations"),"icon_usr_b.gif",$this->lng->txt("group_applicants"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("fullname"),$this->lng->txt("subject"),$this->lng->txt("application_date"),$this->lng->txt("functions")));
		$tbl->setHeaderVars(array("","login","fullname","subject","application_date","functions"),$this->ctrl->getParameterArray($this,"",false));
		$tbl->setColumnWidth(array("","20%","20%","35%","20%","5%"));

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
	* adds applicant to group as member
	* @access	public
	*/
	function assignApplicantsObject()
	{
		$user_ids = $_POST["user_id"];

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		$mail = new ilMail($_SESSION["AccountId"]);

		foreach ($user_ids as $new_member)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($new_member);

			if (!$this->object->addMember($new_member, $this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}

			$this->object->deleteApplicationListEntry($new_member);
			$mail->sendMail($user->getLogin(),"","","New Membership in Group: ".$this->object->getTitle(),"You have been assigned to the group as a member. You can now access all group specific objects like forums, learningmodules,etc..",array(),array('normal'));
		}

		sendInfo($this->lng->txt("grp_msg_applicants_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* adds applicant to group as member
	* @access	public
	*/
	function refuseApplicantsObject()
	{
		$user_ids = $_POST["user_id"];

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		$mail = new ilMail($_SESSION["AccountId"]);

		foreach ($user_ids as $new_member)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($new_member);

			$this->object->deleteApplicationListEntry($new_member);
			$mail->sendMail($user->getLogin(),"","","Membership application refused: Group ".$this->object->getTitle(),"Your application has been refused.",array(),array('normal'));
		}

		sendInfo($this->lng->txt("grp_msg_applicants_removed"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function updateMemberStatusObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()) )
		{
			$this->ilErr->raiseError("permission_denied",$this->ilErr->MESSAGE);
		}

		if (isset($_POST["member_status_select"]))
		{
			foreach ($_POST["member_status_select"] as $key=>$value)
			{
				$this->object->setMemberStatus($key,$value);
			}
		}

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* displays user search form
	*
	*/
	function searchUserFormObject ()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_search_form.html");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this,"post"));
		
		//$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("TXT_SEARCH_USER",$this->lng->txt("add_members"));
		$this->tpl->setVariable("TXT_SEARCH_IN",$this->lng->txt("search_in"));
		$this->tpl->setVariable("TXT_SEARCH_USERNAME",$this->lng->txt("username"));
		$this->tpl->setVariable("TXT_SEARCH_FIRSTNAME",$this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_SEARCH_LASTNAME",$this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_SEARCH_EMAIL",$this->lng->txt("email"));
		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
	}

	function searchCancelledObject ()
	{
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	function searchUserObject ()
	{
		global $rbacreview;

		$_POST["search_string"] = $_POST["search_string"] ? $_POST["search_string"] : urldecode($_GET["search_string"]);

		if (empty($_POST["search_string"]))
		{
			sendInfo($this->lng->txt("msg_no_search_string"),true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this,"searchUserForm"));
		}

		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"])) == 0)
		{
			sendInfo($this->lng->txt("msg_no_search_result")." ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'",true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this,"searchUserForm"));
		}
		
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,"searchUserForm"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_new"));
		$this->tpl->parseCurrentBlock();

		$this->data["cols"] = array("", "login", "firstname", "lastname", "email");

		foreach ($search_result as $key => $val)
		{
			//visible data part
			$this->data["data"][] = array(
							"login"			=> $val["login"],
							"firstname"		=> $val["firstname"],
							"lastname"		=> $val["lastname"],
							"email"			=> $val["email"],
							"obj_id"		=> $val["usr_id"]
						);
		}

		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}
		
		$this->data["buttons"] = array("assignMember"  => $this->lng->txt("assign"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("COLUMN_COUNTS",5);
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
												"ref_id"	=> $this->id,
												"obj_id"	=> $val["obj_id"]
											);
			$tmp[] = $val["obj_id"];
			unset($this->data["data"][$key]["obj_id"]);
		}

		// remember filtered users
		$_SESSION["user_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this,"post")."&sort_by=login&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

		// create table
		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("search_result"),"icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array(
							"ref_id"		=> $this->ref_id,
							"cmdClass"		=> "ilObjGroupGUI",
							"cmd"			=> "searchUser",
							"search_string" => urlencode($_POST["search_string"])
					  		);

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				//$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		}
	}
	
	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""), "", get_class($this));
			
			$tabs_gui->addTarget("group_members",
				$this->ctrl->getLinkTarget($this, "members"), "members", get_class($this));
		}
		
		$applications = $this->object->getNewRegistrations();

		if (is_array($applications) and $this->object->isAdmin($this->ilias->account->getId()))
		{
			$tabs_gui->addTarget("group_new_registrations",
				$this->ctrl->getLinkTarget($this, "ShownewRegistrations"), "ShownewRegistrations", get_class($this));
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$tabs_gui->addTarget("show_owner",
				$this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
					$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
	}


	// IMPORT FUNCTIONS

	function importObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"],"grp"))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("import","grp");

		$this->tpl->setVariable("FORMACTION","adm_object.php?ref_id=".$this->ref_id."&cmd=gateway&new_type=grp");
		$this->tpl->setVariable("TXT_IMPORT_GROUP",$this->lng->txt("group_import"));
		$this->tpl->setVariable("TXT_IMPORT_FILE",$this->lng->txt("group_import_file"));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_IMPORT",$this->lng->txt("import"));

		return true;
	}

	function performImportObject()
	{

		$this->__initFileObject();

		if(!$this->file_obj->storeUploadedFile($_FILES["importFile"]))	// STEP 1 save file in ...import/mail
		{
			$this->message = $this->lng->txt("import_file_not_valid"); 
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->unzip())
		{
			$this->message = $this->lng->txt("cannot_unzip_file");			// STEP 2 unzip uplaoded file
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->findXMLFile())						// STEP 3 getXMLFile
		{
			$this->message = $this->lng->txt("cannot_find_xml");
			$this->file_obj->unlinkLast();
		}
		else if(!$this->__initParserObject($this->file_obj->getXMLFile()) or !$this->parser_obj->startParsing())
		{
			$this->message = $this->lng->txt("import_parse_error").":<br/>"; // STEP 5 start parsing
		}

		// FINALLY CHECK ERROR
		if(!$this->message)
		{
			sendInfo($this->lng->txt("import_forum_finished"),true);
			ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]);
		}
		else
		{
			sendInfo($this->message);
			$this->importObject();
		}
	}


	// PRIVATE IMPORT METHODS
	function __initFileObject()
	{
		include_once "classes/class.ilFileDataImportGroup.php";

		$this->file_obj =& new ilFileDataImportGroup();

		return true;
	}

	function __initParserObject($a_xml_file)
	{
		include_once "classes/class.ilGroupImportParser.php";

		$this->parser_obj =& new ilGroupImportParser($a_xml_file,$this->ref_id);

		return true;
	}
	


} // END class.ilObjGroupGUI
?>
