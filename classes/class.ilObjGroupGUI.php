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
* @version	$Id$
*
* @ilCtrl_Calls ilObjGroupGUI: ilRegisterGUI, ilConditionHandlerInterface
*
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "class.ilContainerGUI.php";
include_once "class.ilRegisterGUI.php";

class ilObjGroupGUI extends ilContainerGUI
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

	function viewObject()
	{
		global $tree;

		if($this->ctrl->getTargetScript() == "adm_object.php")
		{
			parent::viewObject();
			return true;
		}
		else if(!$tree->checkForParentType($this->ref_id,'crs'))
		{
			$this->renderObject();
			//$this->ctrl->returnToParent($this);
		}
		else
		{
			$this->initCourseContentInterface();
			$this->cci_obj->cci_view();
		}
		return true;
	}


	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			case "ilregistergui":
				$this->ctrl->setReturn($this, "");   // ###
				$reg_gui = new ilRegisterGUI();
				//$reg_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			default:
				if ($this->object->requireRegistration() and !$this->object->isUserRegistered())
				{
					$this->ctrl->redirectByClass("ilRegisterGUI", "showRegistrationForm");
				}

				if (empty($cmd))
				{
					#$this->ctrl->returnToParent($this);
					// NOT ACCESSIBLE SINCE returnToParent() starts a redirect
					$cmd = "view";
				}

				// NOT ACCESSIBLE SINCE returnToParent() starts a redirect
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
		$this->tpl->setVariable("TXT_GROUP_STATUS_DESC", $this->lng->txt("group_status_desc"));
	}


	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function canceledObject()
	{
		$return_location = $_GET["cmd_return_location"];
		if (strcmp($return_location, "") == 0)
		{
			$return_location = "members";
		}
				
		sendInfo($this->lng->txt("action_aborted"),true);
		$this->ctrl->redirect($this, $return_location);
	}

	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		$return_location = "members";
				
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
		
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilErr->raiseError("No permissions to change group status!",$this->ilErr->MESSAGE);
		}

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

		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));

		if ($_POST["enable_registration"] == 2 && !ilUtil::isPassword($_POST["password"]))
		{
			$this->ilErr->raiseError($this->lng->txt("passwd_invalid"),$this->ilErr->MESSAGE);
		}

		$this->object->setRegistrationFlag($_POST["enable_registration"]);
		$this->object->setPassword($_POST["password"]);
		$this->object->setExpirationDateTime($_POST["expirationdate"]." ".$_POST["expirationtime"].":00");

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

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));//$this->getFormAction("update",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("grp_edit"));
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
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("spacer.gif"));
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
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function addUserObject()
	{
		$user_ids = $_POST["user"];

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

		$stati = array_flip($this->object->getLocalGroupRoles());

		//build data structure
		foreach ($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $this->object->getMemberRoles($member_id);

			$this->data["data"][$member->getId()]= array(
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"last_visit"=> ilFormat::formatDate($member->getLastLogin()),
					"grp_role"	=> ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."][]",$stati,true,true,3)
				);
		}
		
		unset($member);
		
		infoPanel();

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->data["buttons"] = array( "members"  => $this->lng->txt("back"),
										"updateMemberStatus"  => $this->lng->txt("confirm"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",5);
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
		$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role")));
		$tbl->setHeaderVars(array("login","firstname","lastname","last_visit","role"),$this->ctrl->getParameterArray($this,"",false));

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
		global $rbacsystem,$ilBench,$ilDB;

		$ilBench->start("GroupGUI", "membersObject");
		
		//if current user is admin he is able to add new members to group
		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_send_mail")."\" title=\"".$this->lng->txt("grp_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";
		$val_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("grp_mem_change_status")."\" border=\"0\" vspace=\"0\"/>";
		$val_leave = "<img src=\"".ilUtil::getImagePath("icon_group_out_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_leave")."\" title=\"".$this->lng->txt("grp_mem_leave")."\" border=\"0\" vspace=\"0\"/>";

		// store access checks to improve performance
		$access_delete = $rbacsystem->checkAccess("delete",$this->object->getRefId());
		$access_leave = $rbacsystem->checkAccess("leave",$this->object->getRefId());
		$access_write = $rbacsystem->checkAccess("write",$this->object->getRefId());

		$member_ids = $this->object->getGroupMemberIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		
		$account_id = $this->ilias->account->getId();
		$counter = 0;

		foreach ($members as $mem)
		{
			$link_contact = "mail_new.php?type=new&rcp_to=".$mem["login"];
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$mem["id"];
		
			if (($mem["id"] == $account_id && $access_leave) || $access_delete)
			{
				$link_leave = $this->ctrl->getLinkTarget($this,"RemoveMember")."&mem_id=".$mem["id"];
			}

			//build function
			if ($access_delete && $access_write)
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}

			if (($mem["id"] == $account_id && $access_leave) || $access_delete)
			{
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";
			}

			// this is twice as fast than the code above
			$str_member_roles = $this->object->getMemberRolesTitle($mem["id"]);

			if ($access_delete && $access_write)
			{
				$result_set[$counter][] = ilUtil::formCheckBox(0,"user_id[]",$mem["id"]);
			}
			
			$user_ids[$counter] = $mem["id"];
            
            //discarding the checkboxes
			$result_set[$counter][] = $mem["login"];
			$result_set[$counter][] = $mem["firstname"];
			$result_set[$counter][] = $mem["lastname"];
			$result_set[$counter][] = ilFormat::formatDate($mem["last_login"]);
			$result_set[$counter][] = $str_member_roles;
			$result_set[$counter][] = "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions;

			++$counter;

			unset($member_functions);
		}

		$ilBench->stop("GroupGUI", "membersObject");

		return $this->__showMembersTable($result_set,$user_ids);
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

		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_app_send_mail")."\" title=\"".$this->lng->txt("grp_app_send_mail")."\" border=\"0\" vspace=\"0\"/>";

		foreach ($applications as $applicant)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($applicant->user_id);

			$link_contact = "mail_new.php?mobj_id=3&type=new&rcp_to=".$user->getLogin();
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$user->getId();
			$member_functions = "<a href=\"$link_change\">$val_change</a>";
			if (strcmp($_GET["check"], "all") == 0)
			{
				$checked = 1;
			}
			else
			{
				$checked = 0;
			}
			$this->data["data"][$user->getId()]= array(
				"check"		=> ilUtil::formCheckBox($checked,"user_id[]",$user->getId()),
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
		
		$this->tpl->setCurrentBlock("tbl_action_plain_select");
		$this->tpl->setVariable("SELECT_ACTION", "<a href=\"" . $this->ctrl->getLinkTarget($this,"ShownewRegistrations") . "&check=all\">" . $this->lng->txt("check_all") . "</a>" . " / " . "<a href=\"" . $this->ctrl->getLinkTarget($this,"ShownewRegistrations") . "&check=none\">" . $this->lng->txt("uncheck_all") . "</a>");
		$this->tpl->parseCurrentBlock();

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
		$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("fullname"),$this->lng->txt("subject"),$this->lng->txt("application_date"),$this->lng->txt("grp_options")));
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

	function searchUserFormObject()
	{
		global $rbacsystem;

		$this->lng->loadLanguageModule('search');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.grp_members_search.html");
		
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("grp_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["grp_search_str"] ? $_SESSION["grp_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
		
        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));

		$this->__unsetSessionVariables();
	}
	
	function searchObject()
	{
		global $rbacsystem,$tree;

		$_SESSION["grp_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["grp_search_str"];
		$_SESSION["grp_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["grp_search_for"];
		
		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			sendInfo($this->lng->txt("grp_search_enter_search_string"));
			$this->searchUserFormObject();
			
			return false;
		}

		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			sendInfo($this->lng->txt("grp_no_results_found"));
			$this->searchUserFormObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));
		
		$counter = 0;
		$f_result = array();

		switch($_POST["search_for"])
		{
        	case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
					{
						continue;
					}
					
					$user_ids[$counter] = $user["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();
					$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}
					
				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }
                    
                    $role_ids[$counter] = $role["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;
				
			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}
					
					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}
					
                    // exclude myself :-)
                    if ($tmp_obj->getId() == $this->object->getId())
                    {
                        continue;
                    }
                    
                    $grp_ids[$counter] = $group["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				if(!count($f_result))
				{
					sendInfo($this->lng->txt("grp_no_results_found"));
					$this->searchUserFormObject();

					return false;
				}
				
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
		}
	}

	function searchCancelledObject ()
	{
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
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
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
//  Export tab to export group members to an excel file. Only available for group admins
//  commented out for following reason: clearance needed with developer list
//			$tabs_gui->addTarget("export",
//				$this->ctrl->getLinkTarget($this, "export"), "export", get_class($this));
		}

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("group_members",
				$this->ctrl->getLinkTarget($this, "members"), "members", get_class($this));
		}
		
		$applications = $this->object->getNewRegistrations();

		if (is_array($applications) and $this->object->isAdmin($this->ilias->account->getId()))
		{
			$tabs_gui->addTarget("group_new_registrations",
				$this->ctrl->getLinkTarget($this, "ShownewRegistrations"), "ShownewRegistrations", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}
		
		// show clipboard in repository
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION['il_rep_clipboard']))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
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
			sendInfo($this->lng->txt("import_grp_finished"),true);
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
	
	// METHODS FOR COURSE CONTENT INTERFACE
	function initCourseContentInterface()
	{
		global $ilCtrl;

		include_once "./course/classes/class.ilCourseContentInterface.php";
		
		$this->object->ctrl =& $ilCtrl;
		$this->cci_obj =& new ilCourseContentInterface($this,$this->object->getRefId());
	}

	function cciEditObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_edit();

		return true;;
	}

	function cciUpdateObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_update();

		return true;;
	}
	function cciMoveObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_move();

		return true;;
	}

	function __unsetSessionVariables()
	{
		unset($_SESSION["grp_delete_member_ids"]);
		unset($_SESSION["grp_delete_subscriber_ids"]);
		unset($_SESSION["grp_search_str"]);
		unset($_SESSION["grp_search_for"]);
		unset($_SESSION["grp_role"]);
		unset($_SESSION["grp_group"]);
		unset($_SESSION["grp_archives"]);
	}
	
	function __search($a_search_string,$a_search_for)
	{
		include_once("class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUserForm");
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL, $a_cmd = "search")
	{
        $return_to  = "searchUserForm";
	
    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",$return_to);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("last_visit")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "last_visit"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set,$a_role_ids = NULL)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_role"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
	
	function __showMembersTable($a_result_set,$a_user_ids = NULL)
	{
        global $rbacsystem,$ilBench;
        
		$ilBench->start("GroupGUI", "__showMembersTable");

		$actions = array("RemoveMember"  => $this->lng->txt("remove"),"changeMember"  => $this->lng->txt("change"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		
		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write,delete",$this->object->getRefId()))
		{			//user is administrator
            $tpl->setCurrentBlock("plain_button");
		    $tpl->setVariable("PBTN_NAME","searchUserForm");
		    $tpl->setVariable("PBTN_VALUE",$this->lng->txt("grp_add_member"));
		    $tpl->parseCurrentBlock();
		    $tpl->setCurrentBlock("plain_buttons");
		    $tpl->parseCurrentBlock();
		
			$tpl->setVariable("COLUMN_COUNTS",7);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

            foreach ($actions as $name => $value)
			{
				$tpl->setCurrentBlock("tbl_action_btn");
				$tpl->setVariable("BTN_NAME",$name);
				$tpl->setVariable("BTN_VALUE",$value);
				$tpl->parseCurrentBlock();
			}
			
			if (!empty($a_user_ids))
			{
				// set checkbox toggles
				$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
				$tpl->setVariable("JS_VARNAME","user_id");			
				$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
				$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
				$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
				$tpl->parseCurrentBlock();
			}
			
            $tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		}

		$this->ctrl->setParameter($this,"cmd","members");


		// title & header columns
		$tbl->setTitle($this->lng->txt("members"),"icon_usr_b.gif",$this->lng->txt("group_members"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("delete,write",$this->object->getRefId()))
		{
			//user must be administrator
			$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("","login","firstname","lastname","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","22%","22%","22%","22%","10%"));
		}
		else
		{
			//user must be member
			$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("login","firstname","lastname","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("22%","22%","22%","22%","10%"));
		}

		$this->__setTableGUIBasicData($tbl,$a_result_set,"members");
		$tbl->render();
		$this->tpl->setVariable("ADM_CONTENT",$tbl->tpl->get());
		
		$ilBench->stop("GroupGUI", "__showMembersTable");

		return true;
	}

	function &__initTableGUI()
	{
		include_once "class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'login';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;

			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;
				
			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;

			default:
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		//$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
	
	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["grp_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["grp_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["role"]))
		{
			sendInfo($this->lng->txt("grp_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;

			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}
	
	function listUsersGroupObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["grp_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["grp_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["group"]))
		{
			sendInfo($this->lng->txt("grp_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["group"] as $group_id)
		{
			if (!$tree->isInTree($group_id))
			{
				continue;
			}
			if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}
	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './classes/class.ilConditionHandlerInterface.php';

		if(!is_object($this->chi_obj))
		{
			if($_GET['item_id'])
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this,$item_id);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
			}
			else
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this);
			}
		}
		return true;
	}

	function chi_updateObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_update();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}		
	function chi_deleteObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_delete();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}

	function chi_selectorObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_selector();
	}		

	function chi_assignObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_assign();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}
	
/**
* Creates the output form for group member export
*
* Creates the output form for group member export
*
*/
	function exportObject()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.grp_members_export.html");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("export",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("BUTTON_EXPORT", $this->lng->txt("export_group_members"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Exports group members to Microsoft Excel file
*
* Exports group members to Microsoft Excel file
*
*/
	function exportMembersObject()
	{
		require_once './classes/Spreadsheet/Excel/Writer.php';
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		// sending HTTP headers
		$title = preg_replace("/\s/", "_", $this->object->getTitle());
		$workbook->send("export_" . $title . ".xls");

		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$column = 0;
		$profile_data = array("email", "gender", "firstname", "lastname", "person_title", "institution", 
			"department", "street", "zipcode","city", "country", "phone_office", "phone_home", "phone_mobile",
			"fax", "matriculation");
		foreach ($profile_data as $data)
		{
			$worksheet->writeString(0, $column++, $this->cleanString($this->lng->txt($data)), $format_title);
		}
		$member_ids = $this->object->getGroupMemberIds();
		$row = 1;
		foreach ($member_ids as $member_id)
		{
			$column = 0;
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			if ($member->getPref("public_email")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getEmail()));
			}
			else
			{
				$column++;
			}
			$worksheet->writeString($row, $column++, $this->cleanString($this->lng->txt("gender_" . $member->getGender())));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getFirstname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getLastname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getUTitle()));
			if ($member->getPref("public_institution")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getInstitution()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_department")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getDepartment()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_street")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getStreet()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_zip")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getZipcode()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_city")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCity()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_country")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCountry()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_office")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneOffice()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_home")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneHome()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_mobile")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneMobile()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_fax")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getFax()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_matriculation")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getMatriculation()));
			}
			else
			{
				$column++;
			}
			$row++;
		}
		$workbook->close();
	}
	
/**
* Clean output string from german umlauts
*
* Clean output string from german umlauts. Replaces ä -> ae etc.
*
* @param string $str String to clean
* @return string Cleaned string
*/
	function cleanString($str)
	{
		return str_replace(array("ä","ö","ü","ß","Ä","Ö","Ü"), array("ae","oe","ue","ss","Ae","Oe","Ue"), $str);
	}
	
} // END class.ilObjGroupGUI
?>
