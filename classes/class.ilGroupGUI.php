<?php
/**
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
require_once("include/inc.header.php");
require_once("classes/class.ilObjGroupGUI.php");
require_once("classes/class.ilGroupExplorer.php");
require_once("classes/class.ilTableGUI.php");
require_once("classes/class.ilObjGroup.php");

/**
* Class ilGroupGUI
*
* GUI class for Group management
*
* @author	Martin Rus <mrus@smail.uni-koeln.de>
* @author	Sascha Hofmann <shofmann@databay.de>

* @version	$Id$

* @package	ilias-core
*/

class ilGroupGUI extends ilObjGroupGUI
{
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $ilias;
	var $object;
	var $grp_object;
	var $grp_tree;
	var $grp_id;
	var $grp_title;
	var $rbacsystem;

	/**
	* Constructor
	* @access	public
	*/

	function ilGroupGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $tpl, $ilias, $lng, $tree, $rbacsystem, $objDefinition, $offset, $limit;

		$this->type ="grp";
		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->formaction = array();
		$this->return_location = array();
		$this->rbacsystem =& $rbacsystem;

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;

		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		if ($_GET["offset"] == "")
		{
			$_GET["offset"] = 0;
		}
		$lng->loadlanguageModule("search");
		$_GET["offset"] = intval($_GET["offset"]);
		$_GET["limit"] = intval($_GET["limit"]);
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);
		if ($_GET["limit"] == 0)
		{
			$_GET["limit"] = 10;	// TODO: move to user settings
		}
		if (empty($_GET["sort_by"]))
		{
			$_GET["sort_by"]= "title";
		}

		// get the object
		$this->assignObject();
		$this->object =& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		$this->lng =& $this->object->lng;

		// get group object ($this->object and $this->grp_object are not always the same)
		if($this->object->getType()=="fold")
		{
			$this->grp_object =& $ilias->obj_factory->getInstanceByRefId(ilUtil::getGroupId($_GET["ref_id"]));
		}
		else
		{
			$this->grp_object = $this->object;
		}


		$this->grp_id = ilUtil::getGroupId($_GET["ref_id"]);

		$this->grp_tree = new ilGroupTree($this->grp_id);
		//$this->grp_tree->setTableNames("grp_tree","object_data","object_reference");

		//return to the same place , where the action was executed
		$this->setReturnLocation("cut","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("clear","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("copy","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("link","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("paste","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancelDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancel","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("confirmedDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("removeFromSystem","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("undelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("permSave","group.php?cmd=perm&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("addrole","group.php?cmd=perm&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("newmembers","group.php?cmd=newmembers&ref_id=".$_GET["ref_id"]);


		$cmd = $_GET["cmd"];
		//var_dump ($cmd);
		if($cmd == "")
		{
			$cmd = "view";
		}
		
		if (isset($_POST["cmd"]) )
		{
			$cmd = key($_POST["cmd"]);
			$fullcmd = $cmd;//."Object";
			//echo $fullcmd;
			//var_dump ($_POST);
			// only createObject!!
			$this->$fullcmd();
			exit();
		}

		$this->$cmd();
	}



	function accessDenied()
	{
		global $ilias, $rbacsystem;
		$grpObj = new ilObjGroup($_GET["ref_id"],true);
		$grp	=& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		$owner  = new ilObjUser($grp->getOwner());

		$_SESSION["saved_post"]["user_id"][0] = $this->ilias->account->getId();
		$_SESSION["status"] 	= $this->object->getDefaultMemberRole();

		switch($grpObj->getRegistrationFlag())
		{
			case 0:
				$stat = $this->lng->txt("group_no_registration");
				$msg  = $this->lng->txt("group_no_registration_msg");
				$readonly ="readonly";
				$subject ="";
				$cmd_submit = "joinGroup";
				break;
			case 1:
				$stat = $this->lng->txt("group_req_registration");
				$msg  = $this->lng->txt("group_req_registration_msg");
				$cmd_submit = "applyForMembership";
				$txt_subject =$this->lng->txt("subject").":";
				$textfield = "<textarea name=\"subject\" value=\"{SUBJECT}\" cols=\"50\" rows=\"5\" size=\"255\"></textarea>";
				break;
			case 2:
				if($this->object->registrationPossible() == true)
				{
					$stat = $this->lng->txt("group_req_password");//"Registrierungpasswort erforderlich";
					$msg = $this->lng->txt("group_password_registration_msg");
					$txt_subject =$this->lng->txt("password").":";
					$textfield = "<input name=\"subject\" value=\"{SUBJECT}\" type=\"password\" size=\"40\" maxlength=\"70\" style=\"width:300px;\"/>";
					$cmd_submit = "applyForMembership";
				}
				else
				{
					$msg = $this->lng->txt("group_password_registration_expired_msg");
					$msg_send = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=root";
					$cmd_submit = "groupList";
					$readonly ="readonly";
					$stat = $this->lng->txt("group_registration_expired");
					sendInfo($this->lng->txt("registration_expired"),true);
				}
				break;
		}


		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_access"));
		$this->tpl->addBlockFile("CONTENT", "tbldesc", "tpl.grp_accessdenied.html");
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("group_access_denied"));
		$this->tpl->setVariable("TXT_MESSAGE",$msg);
		$this->tpl->setVariable("TXT_GRP_NAME", $this->lng->txt("group_name").":");
		$this->tpl->setVariable("GRP_NAME",$grp->getTitle());
		$this->tpl->setVariable("TXT_GRP_DESC",$this->lng->txt("group_desc").":");
		$this->tpl->setVariable("GRP_DESC",$grp->getDescription());
		$this->tpl->setVariable("TXT_GRP_OWNER",$this->lng->txt("owner").":");
		$this->tpl->setVariable("GRP_OWNER",$owner->getLogin());
		$this->tpl->setVariable("TXT_GRP_STATUS",$this->lng->txt("group_status").":");
		$this->tpl->setVariable("GRP_STATUS", $stat);
		$this->tpl->setVariable("TXT_SUBJECT",$txt_subject);
		$this->tpl->setVariable("SUBJECT",$textfield);
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("grp_register"));
		$this->tpl->setVariable("CMD_CANCEL","groupList");
		$this->tpl->setVariable("CMD_SUBMIT",$cmd_submit);
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&user_id=".$this->ilias->account->getId());
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	/**
	* adds a local role
	* This method is only called when choose the option 'you may add local roles'. This option
	* is displayed in the permission settings dialogue for an object
	* TODO: this will be changed
	* @access	public
	*/
	function addRole()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		// first check if role title is unique
		if ($rbacreview->roleExists($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".ilUtil::stripSlashes($_POST["Fobject"]["title"])."' ".
									 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
		}

		// if the current object is no role folder, create one
		if ($this->object->getType() != "rolf")
		{
			$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);

			// is there already a rolefolder?
			if (!($rolf_id = $rolf_data["child"]))
			{
				// can the current object contain a rolefolder?
				$subobjects = $this->objDefinition->getSubObjects($this->object->getType());

				if (!isset($subobjects["rolf"]))
				{
					$this->ilias->raiseError($this->lng->txt("msg_no_rolf_allowed1")." '".$this->object->getTitle()."' ".
											$this->lng->txt("msg_no_rolf_allowed2"),$this->ilias->error_obj->WARNING);
				}

				// CHECK ACCESS 'create' rolefolder
				if (!$rbacsystem->checkAccess('create',$this->ref_id,'rolf'))
				{
					$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolf"),$this->ilias->error_obj->WARNING);
				}

				// create a rolefolder
				$rolfObj = $this->object->createRoleFolder();
				$rolf_id = $rolfObj->getRefId();

// TODO: this is done by object->createRoleFolder
				// Suche aller Parent Rollen im Baum
				/*
				$parentRoles = $rbacreview->getParentRoleIds($this->object->getRefId());

				foreach ($parentRoles as $parRol)
				{
					// Es werden die im Baum am 'n�hsten liegenden' Templates ausgelesen
					$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"],'rolf',$parRol["parent"]);

					//$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id);
				}*/
			}
		}
		else
		{
			// Current object is already a rolefolder. To create the role we take its reference id
			$rolf_id = $this->object->getRefId();
		}

		// CHECK ACCESS 'write' of role folder
		if (!$rbacsystem->checkAccess('write',$rolf_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		else	// create role
		{
			if ($this->object->getType() == "rolf")
			{
				$this->object->createRole($_POST["Fobject"]["title"],$_POST["Fobject"]["desc"]);
			}
			else
			{
				$rfoldObj = $this->ilias->obj_factory->getInstanceByRefId($rolf_id);
				$role_title = strtolower($_POST["Fobject"]["title"]);
//				$rfoldObj->createRole("il_grp_".$role_title."_".$this->grp_id,$_POST["Fobject"]["desc"]);
				$rfoldObj->createRole($role_title,$_POST["Fobject"]["desc"]);
			}
		}

		sendInfo($this->lng->txt("role_added"),true);

		header("Location: ".$this->getReturnLocation("addRole","group.php?ref_id=".$_GET["ref_id"]."&cmd=perm"));
		exit();
	}

	function applyForMembership()
	{
		global $ilias;

		if($this->object->getRegistrationFlag() == 1)
		{
			$q = "SELECT * FROM grp_registration WHERE grp_id=".$this->object->getId()." AND user_id=".$this->ilias->account->getId();
			$res = $this->ilias->db->query($q);
			if($res->numRows() > 0)
			{
				sendInfo($this->lng->txt("already_applied"),true);
			}
			else
			{
				$q = "INSERT INTO grp_registration VALUES (".$this->object->getId().",".$this->ilias->account->getId().",'".$_POST["subject"]."','".date("Y-m-d H:i:s")."')";
				$res = $this->ilias->db->query($q);
				sendInfo($this->lng->txt("application_completed"),true);
			}
		}
		else if($this->object->getRegistrationFlag() == 2)	//PASSWORD REGISTRATION
		{
			if(strcmp($this->object->getPassword(),$_POST["subject"]) == 0 && $this->object->registrationPossible()==true)
			{
//				$this->joinGroupObject();
				$this->object->addMember($this->ilias->account->getId(),$this->object->getDefaultMemberRole());
				sendInfo($this->lng->txt("grp_registration_completed"),true);

			}
			else if(strcmp($this->object->getPassword(),$_POST["subject"]) != 0 && $this->object->registrationPossible()==true)
			{
				sendInfo($this->lng->txt("err_wrong_password"),true);
			}
			else
				sendInfo($this->lng->txt("registration_not_possible"),true);
			
		}
		header("location: repository.php");
	}
	
	function assignApplicants()
	{
		global $ilias;

		$user_ids = $_POST["user_id"];
		if(isset($user_ids))
		{
			$confirm = "confirmedAssignApplicants";
			$cancel  = "cancel_assignment";
			$info	 = "info_assign_sure";
			$status  = 0;
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function canceldelete()
	{
		sendInfo($this->lng->txt("action_aborted"),true);

		header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		exit();
	}

	/**
	* create new group object form
	*/
	function create_new()
	{
		global $rbacsystem;
		
		if (isset($_POST["new_type"]))
		{
			$new_type =  $_POST["new_type"];
		}
		else
		{
			$new_type =	 $_GET["type"];
		}

		// CHECK ACCESS 'write' of role folder
		if (!$rbacsystem->checkAccess('create',$this->grp_id,$new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->WARNING);
		}

		$data = array();
		$data["fields"] = array();
		$data["fields"]["group_name"] = "";
		$data["fields"]["desc"] = "";

		$this->prepareOutput();
		$this->tpl->addBlockFile("CONTENT", "newgroup", "tpl.grp_edit.html");
		$this->tpl->setVariable("HEADER", $this->lng->txt("grp_new"));
		$this->tpl->setVariable("TARGET","target=\"bottom\"");

		$node = $this->tree->getNodeData($_GET["parent_ref_id"]);
		$this->tpl->setVariable("TXT_PAGEHEADLINE", $node["title"]);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
		}

		$stati = array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));

		//build form
		$opts = ilUtil::formSelect(0,"group_status_select",$stati,false,true);

		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&cmd=save"."&ref_id=".$_GET["parent_ref_id"]."&new_type=".$new_type);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMember()
	{
		include_once "./classes/class.ilTableGUI.php";

		$member_ids = array();
		if(isset($_POST["user_id"]))
		{
			$member_ids = $_POST["user_id"];
		}
		else
		{
			if(isset($_GET["mem_id"]))
			{
				$member_ids[0] = $_GET["mem_id"];
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("user_not_chosen"),$this->ilias->error_obj->MESSAGE);
			}
		}


		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$local_roles = $newGrp->getLocalGroupRoles();

		$flipped_local_roles = array_flip($local_roles);
		$stati = array();
		$stati = $flipped_local_roles;

		//build data structure
		foreach($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $newGrp->getMemberRoles($member_id);

			$this->data["data"][$member->getId()]= array(
				"login"        => $member->getLogin(),
				"firstname"       => $member->getFirstname(),
				"lastname"        => $member->getLastname(),
				"grp_role" => ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."][]",$stati,true,true,3) //displays selectionbox with multiple selection !!!
				);
			unset($member);
		}

		$this->prepareOutput(false,99);
		$this->tpl->setVariable("HEADER", $this->lng->txt("grp_mem_change_status"));

		$this->tpl->addBlockfile("CONTENT", "member_table", "tpl.table.html");

		//load template for table content data

		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&tree_id=".$this->grp_tree->getTreeId()."&tree_table=grp_tree");
		$this->tpl->setVariable("ACTIONTARGET", "bottom");
		$this->data["buttons"] = array( "showgroupmembers"  => $this->lng->txt("cancel"),
						"updateMemberStatus"  => $this->lng->txt("confirm"));

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

		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}

		if ($offset == "")
		{
			$offset = 0;	// TODO: move to user settings
		}

		// create table
		$tbl = new ilTableGUI($this->data["data"]);
		// title & header columns
		$tbl->setTitle($this->lng->txt("grp_mem_change_status"),"icon_usr_b.gif",$this->lng->txt("grp_mem_change_status"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("status")));
		$tbl->setHeaderVars(array("firstname","lastname","role","status"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

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
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
		//sendInfo($this->lng->txt("msg_obj_modified"), true);
	}

	/**
	* displays confirmation form that is used from several methods(deleteObject,newMembers, removeMember, ChangeMemberStatus)
	* works like a gateway
	* @access public
	* @param	id of the displayed objects
	* @param	confirm = name of method that is called after confirmation
	* @param	cancel  = name of method that is called when canceled
	* @param	info	= message
	* @param	status  = userstatus of new members[member=0|admin=1]
	* @param	call_by_ref = message
	*
	*/
	function confirmation($user_id="", $confirm, $cancel, $info="", $status="",$ref_IDs="n")
	{
		$num = 0;
		$this->prepareOutput(false,99);
		$this->tpl->setVariable("HEADER", $this->lng->txt("objs_confirm"));
		sendInfo ($this->lng->txt($info));
		$this->tpl->addBlockFile("CONTENT", "confirmation", "tpl.table.html");
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&parent_on_rbac_id=".$_GET["parent_non_rbac_id"]."&gateway=true");
		$this->tpl->addBlockFile("TBL_CONTENT", "confirmcontent","tpl.grp_tbl_confirm.html" );

		$this->tpl->setCurrentBlock("confirmcontent");
		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit  = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}
		if ($offset == "")
		{
			$offset = 0;	// TODO: move to user settings
		}

		if (is_array($user_id))
		{
			$maxcount = count ($user_id);
			foreach ($user_id as $id)
			{
				if($ref_IDs == "y")
				{
					$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
				}
				else
				{
					$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);
				}

				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
				$this->tpl->setVariable("TITLE", $obj_data->getTitle());
				$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType($obj_data->getType(),$this->tpl->tplPath));
				$this->tpl->setVariable("LAST_UPDATE", $obj_data->getLastUpdateDate());
				$this->tpl->parseCurrentBlock();
				unset($obj_data);
				$num++;
			}
		}
		else
		{
			$maxcount = 1;
			if($ref_IDs == "y")
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($user_id);
			}
			else
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_id);
			}

			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
			$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
			$this->tpl->setVariable("TITLE", $obj_data->getTitle());
			$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType($obj_data->getType(),$this->tpl->tplPath));
			$this->tpl->setVariable("LAST_UPDATE", $obj_data->getLastUpdateDate());
			$this->tpl->parseCurrentBlock();
		}

		if ($confirm != "confirmedDelete")
		{

			if (is_array($user_id))
			{
				$_SESSION["saved_post"]["user_id"] = $user_id;
			}
			else
			{
				$_SESSION["saved_post"]["user_id"][0] = $user_id;
			}
		}

		$this->tpl->setVariable("COLUMN_COUNTS", "4");

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", $cancel);
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", $confirm);
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("confirm"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();

		$tbl = new ilTableGUI();
		$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("last_change")));
		$tbl->setHeaderVars(array("type","title","description","last_change"));
		$tbl->setColumnWidth(array("3%","16%","22%","*"));
		$tbl->setMaxcount($maxcount);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setTitle($this->lng->txt("objs_confirm"),"icon_grp_b.gif",$this->lng->txt("group_details"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->render();

	}

	/**
	* assign applicants object calls the confirmation method with correct parameter
	* @access	public
	*/
	function confirmedAssignApplicants()
	{

		if($_SESSION["saved_post"])
		{
			$newGrp = new ilObjGroup($this->object->getRefId(), true);
			$mail  = new ilMail($_SESSION["AccountId"]);
			foreach ($_SESSION["saved_post"]["user_id"] as $new_member)
			{
				$user =& $this->ilias->obj_factory->getInstanceByObjId($new_member);
				if (!$this->object->addMember($new_member, $newGrp->getDefaultMemberRole()))
				{
					$this->ilias->raiseError("An Error occured while assigning user to group !",$this->ilias->error_obj->MESSAGE);
				}
				else
				{
					$this->object->deleteApplicationListEntry($new_member);
					$mail->sendMail($user->getLogin(),"","","New Membership in Group: ".$newGrp->getTitle(),"You have been assigned to the group as a member. You can now access all group specific objects like forums, learningmodules,etc..",array(),array('normal'));
				}
			}
			unset($_SESSION["user_id"]);
		}
		header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
	}

	/**
	* adds member to group as member	
	* @access	public
	*/
	function confirmedAssignMember($a_userIds="")
	{

		if (isset($_SESSION["saved_post"]) && isset($_SESSION["status"]))
		{
			foreach ($_SESSION["saved_post"]["user_id"] as $new_member)
			{
				if (!$this->object->addMember($new_member, $_SESSION["status"]))
				{
					$this->ilias->raiseError("An Error occured while assigning user to group !",$this->ilias->error_obj->MESSAGE);
				}
			}
			unset($_SESSION["saved_post"]);
			unset($_SESSION["status"]);
			sendInfo($this->lng->txt("usr_added"),true);
		}
		header("Location: group.php?cmd=view&".$this->link_params);
	}

	/**
	* @access	public
	*/
	function confirmedLeaveGroup()
	{
		switch($this->object->leaveGroup())
		{
			case 0: sendInfo($this->lng->txt("grp_msg_membership_annulled"),true);
				header("Location: repository.php?ref_id=".$_SESSION["il_rep_ref_id"]);
				break;
			case 1: sendInfo($this->lng->txt("grp_err_at_least_one_groupadministrator_is_needed"),true);
				header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
				break;
//			case 2: sendInfo($this->lng->txt("grp_errgroup_needs_to_be_deleted"),true);
			case 2: sendInfo($this->lng->txt("grp_err_last_member"),true);
				header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
				break;
			default:sendInfo($this->lng->txt("grp_err_error"),true);
				header("Location: group.php?cmd=view&ref_id=".$this->ref_id);

		}

		if(!$this->object->leaveGroup())
			$this->ilias->raiseError(get_class($this)."::confirmedleaveGroupObject(): Core Method ".get_class($this->object)."::leaveGroup() returned false!",$this->ilias->error_obj->WARNING);

	}

	/**
	* remove members from group
	* @access public
	*/
	function confirmedRemoveMember()
	{
		global $rbacsystem;

		if (isset($_SESSION["saved_post"]["user_id"]) && in_array($this->ilias->account->getId(),$this->object->getGroupAdminIds()) || $rbacsystem->checkAccess('delete',$this->ref_id,'usr') || $_SESSION["saved_post"]["user_id"])
		{
			//User needs to have administrative rights to remove members...

			foreach($_SESSION["saved_post"]["user_id"] as $member_id)
			{
				$err_msg = $this->object->removeMember($member_id);
				if(strlen($err_msg) > 0)
					sendInfo($this->lng->txt($err_msg),true);
			}
		}
		else
		{
			sendInfo($this->lng->txt("grp_err_no_permission"),true);
		}
		
		if ($_SESSION["saved_post"]["user_id"][0] == $this->ilias->account->getId())
		{
			header("Location: repository.php?getlast=true");
			exit();
		}
		unset($_SESSION["saved_post"]);
		header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
	}


	/**
	* @access	public
	*/
	function permSave()
	{
		global $rbacsystem, $rbacreview, $rbacadmin;

		// first save the new permission settings for all roles
		$rbacadmin->revokePermission($this->ref_id);

		foreach ($_POST["perm"] as $key => $new_role_perms)
		{
			// $key enthaelt die aktuelle Role_Id
			$rbacadmin->grantPermission($key,$new_role_perms,$this->ref_id);
		}

		// update object data entry (to update last modification date)
		$this->object->update();

		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des n�hst hher gelegenen Permission Templates angepasst

		// get rolefolder data if a rolefolder already exists
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);
		$rolf_id = $rolf_data["child"];

		if ($_POST["stop_inherit"])
		{
			// rolefolder doesn't exists, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			// CHECK ACCESS 'write' of role folder
			if (!$rbacsystem->checkAccess('write',$rolf_id))
			{
				
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
			}

			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_id);

				// create role entries for roles with stopped inheritance
				if (!in_array($stop_inherit,$roles_of_folder))
				{
					$parentRoles = $rbacreview->getParentRoleIds($rolf_id);
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_id,$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
			}// END FOREACH
		}// END STOP INHERIT
		elseif 	(!empty($rolf_id))
		{
			// TODO: this feature doesn't work at the moment
			// ok. if the rolefolder is not empty, delete the local roles
			//if (!empty($roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_data["ref_id"])));
			//{
				//foreach ($roles_of_folder as $obj_id)
				//{
					//$rolfObj =& $this->ilias->obj_factory->getInstanceByRefId($rolf_data["child"]);
					//$rolfObj->delete();
					//unset($rolfObj);
				//}
			//}
		}

		sendinfo($this->lng->txt("saved_successfully"),true);

		header("Location: ".$this->getReturnLocation("permSave","group.php?ref_id=".$_GET["ref_id"]."&cmd=perm"));
		exit();

	}



	/**
	* create new object form
	*
	* @access	public
	*/
	function create()
	{
		//TODO: check the
		// creates a child object
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		//if new obj type is a folder or file then the right to create a group is checked
		// because grp object is the the first RBAC-parent object
		if ($new_type == "fold" or $new_type == "file")
		{
			$this->prepareOutput(false, 99);
			$this->tpl->setVariable("HEADER", $this->lng->txt($new_type."_new"));
			if (!$rbacsystem->checkAccess("create_".$new_type, $this->grp_id))
			{
				$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{
			$this->prepareOutput(false, 99);
			$this->tpl->setVariable("HEADER", $this->lng->txt($new_type."_new"));

			// TODO: get rid of $_GET variable
			if (!$rbacsystem->checkAccess("create", $this->grp_id, $new_type))
			{
				$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			}
		}

		// temp. switch for file upload
		if ($new_type == "slm")
		{
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["scorml_m"] = "";

			$this->tpl->addBlockFile("CONTENT", "create_table" ,"tpl.slm_import.html");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("BTN_NAME", "upload");
			$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
			$this->tpl->setVariable("TXT_IMPORT_SLM", $this->lng->txt("import_slm"));
			$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));

		}
		elseif ($new_type == "file")
		{
			// fill in saved values in case of error
			
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];
			$data["fields"]["file"] = $_SESSION["error_post_vars"]["Fobject"]["file"];

			$this->tpl->addBlockFile("CONTENT", "create_table" ,"tpl.file_new.html");
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			
			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
				//$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
		else
		{
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";

			$this->tpl->addBlockFile("CONTENT", "create_table" ,"tpl.obj_edit.html");
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION","group.php?gateway=false&cmd=save&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$_GET["parent_non_rbac_id"]."&new_type=".$new_type);
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}

		$this->tpl->show();
	}

	/**
	* delete Object
	* @access public
	*/
	function delete()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["saved_post"] = $_POST["id"];
		unset($this->data);
		$confirm = "confirmedDelete";
		$cancel  = "canceldelete";
		$info	 = "info_delete_sure";
		$status  = "";

		$this->confirmation($_POST["id"], $confirm, $cancel, $info,"","y");
		$this->tpl->show();
	}
	
	/**
	* edit Group
	* @access public
	*/
	function editGroup()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->prepareOutput(true,4);
		$this->tpl->setVariable("HEADER",  $this->lng->txt("grp")." \"".$this->object->getTitle()."\"");
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];
			$data["password"] = $_SESSION["error_post_vars"]["password"];
			$data["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];
			$data["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];
		}
		else if(strlen($this->grp_object->getTitle()) > 0)
		{
			$data["title"] = $this->grp_object->getTitle();
			$data["desc"] = $this->grp_object->getDescription();
			$data["password"] = $this->grp_object->getPassword();
			$datetime = $this->grp_object->getExpirationDateTime();
			$data["expirationdate"] = $datetime[0];//$this->grp_object->getExpirationDateTime()[0];
			$data["expirationtime"] = substr($datetime[1],0,5);//$this->grp_object->getExpirationDateTime()[1];
		}
		else
		{
			$data["title"] = "";
			$data["desc"] = "";
			$data["password"] = "";
			$data["expirationdate"] = "";
			$data["expirationtime"] = "";
		}

		$this->tpl->addBlockFile("CONTENT", "edit", "tpl.grp_edit.html");

		foreach ($data as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
			$this->tpl->parseCurrentBlock();
		}

		$stati = array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));
		//build form
		$grp_status = $this->grp_object->getGroupStatus();
		$opts = ilUtil::formSelect($grp_status,"group_status",$stati,false,true);

		$checked = array(0=>0,1=>0,2=>0);
		switch($this->object->getRegistrationFlag())
		{
			case 0: $checked[0]=1;
				break;
			case 1: $checked[1]=1;
				break;
			case 2: $checked[2]=1;
				break;
		}
		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);
		$cb_reset	    = ilUtil::formCheckBox(0,"group_reset",1,false);

		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$this->object->getRefId());

		$this->tpl->setVariable("TARGET",$this->getTargetFrame("save","bottom"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "view" );
		$this->tpl->setVariable("CMD_SUBMIT", "updateGroupStatus");
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("grp_edit"));
		
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
//		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("SELECT_GROUPSTATUS", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("TXT_RESET", $this->lng->txt("group_reset"));
		$this->tpl->setVariable("CB_RESET", $cb_reset);
		$this->tpl->show();
	}



	/*
	* function returns specific link-url depending on object-type
	*
	* @access	public
	*/
	function getURLbyType($cont_data, $mode = "view")
	{
		//result of the function
		$URL;
		
		if ($mode == "view")
		{
			switch ($cont_data["type"])
			{
				case "frm":

					require_once "classes/class.ilForum.php";

					$frm = new ilForum();
					$frm->setWhereCondition("top_frm_fk = ".$cont_data["obj_id"]);
					$topicData = $frm->getOneTopic();

					if ($topicData["top_num_threads"] > 0)
					{
						$thr_page = "liste";
					}
					else
					{
						$thr_page = "new";
					}

					$URL = "forums_threads_".$thr_page.".php?ref_id=".$cont_data["ref_id"];
					break;



				case "lm":
					$URL = "content/lm_presentation.php?ref_id=".$cont_data["ref_id"];
					break;

				case "slm":
					$URL = "content/scorm_presentation.php?ref_id=".$cont_data["ref_id"];
					break;

				case "fold":
					$URL = "group.php?ref_id=".$cont_data["ref_id"]."&cmd=view";
					break;

				case "glo":
					$URL = "./content/glossary_presentation.php?ref_id=".$cont_data["ref_id"]."&cmd=listTerms";
					break;

				case "file":
					$URL = "group.php?cmd=get_file&ref_id=".$cont_data["ref_id"];
					break;
			}
		}
		else if ($mode == "edit")
		{
			switch ($cont_data["type"])
			{
				case "frm":
					break;

				case "lm":
					$URL = "content/lm_edit.php?ref_id=".$cont_data["ref_id"];
					break;

				case "slm":
					break;

				case "fold":
					break;

				case "glo":
					$URL = "./content/glossary_edit.php?ref_id=".$cont_data["ref_id"]."&cmd=listTerms";
					break;

				case "file":
					break;
			}
		}
		else if ($mode == "abo")
		{
			switch ($cont_data["type"])
			{
				case "frm":
				    $URL = "group.php?cmd=addToDesk&ref_id=".$cont_data["parent"]."&item_ref_id=".$cont_data["ref_id"]."&type=".$cont_data["type"].
					"&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"]."&sort_by=".$_GET["sort_by"];
					break;

				case "lm":
					$URL = "group.php?cmd=addToDesk&ref_id=".$cont_data["parent"]."&item_ref_id=".$cont_data["ref_id"]."&type=".$cont_data["type"].
					"&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"]."&sort_by=".$_GET["sort_by"];
					break;

				case "slm":
					$URL = "group.php?cmd=addToDesk&ref_id=".$cont_data["parent"]."&item_ref_id=".$cont_data["ref_id"]."&type=".$cont_data["type"].
					"&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"]."&sort_by=".$_GET["sort_by"];
					break;
				
				case "glo":
					$URL = "group.php?cmd=addToDesk&ref_id=".$cont_data["parent"]."&item_ref_id=".$cont_data["ref_id"]."&type=".$cont_data["type"].
					"&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"]."&sort_by=".$_GET["sort_by"];
					break;
			}
		}

		return $URL;
	}

	// add the chosen object to the user desktop
	function addToDesk()
	{
		$this->ilias->account->addDesktopItem($_GET["item_ref_id"], $_GET["type"]);
		$this->view();
		
		
	}
	/**
	* get
	* @access	public
	* @param	integer	group id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function getContextPath ($a_endnode_id, $a_startnode_id = 0)
	{
		$path = "";

		if (!$a_startnode_id)
		{
			$a_startnode_id = $this->grp_id;
		}

		$tmpPath = $this->grp_tree->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}

	/**
	* @access	public
	*/
	function groupList()
	{
		header("Location: repository.php?ref_id=".$_SESSION["il_rep_ref_id"]);
	}

	/**
	* @access	public
	*/
	function joinGroup()
	{
		
		if ($this->object->addMember($this->ilias->account->getId(), $this->grp_object->getDefaultMemberRole()))
		{
			sendInfo($this->lng->txt("grp_registration_completed"),true);
		}
		header("location: group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
	}
	/**
	* displays search form for new users
	* @access public
	*/
	function newMembers()
	{
		//create additional tabs for tab-bar
		
		$this->prepareOutput(true,99);

		$this->tpl->setVariable("HEADER", $this->lng->txt("add_member"));

		$this->tpl->addBlockFile("CONTENT", "newmember","tpl.grp_newmember.html");

		$this->tpl->setVariable("TXT_MEMBER_NAME", $this->lng->txt("username"));
		$this->tpl->setVariable("TXT_STATUS", $this->lng->txt("group_memstat"));

		if($_POST["status"] == $this->object->getDefaultMemberRole() || !isset($_POST["status"]))
		{
			$checked = 0;
		}
		else
		{
			$checked = 1;
		}
		$radio_member = ilUtil::formRadioButton($checked ? 0:1,"status",$this->object->getDefaultMemberRole());
		$radio_admin = ilUtil::formRadioButton($checked ? 1:0,"status",$this->object->getDefaultAdminRole());

		$this->tpl->setVariable("RADIO_MEMBER", $radio_member);
		$this->tpl->setVariable("RADIO_ADMIN", $radio_admin);
		$this->tpl->setVariable("TXT_MEMBER_STATUS", $this->lng->txt("group_memstat_member"));
		$this->tpl->setVariable("TXT_ADMIN_STATUS", $this->lng->txt("group_memstat_admin"));
		$this->tpl->setVariable("TXT_SEARCH", $this->lng->txt("search"));

		if(isset($_POST["search_user"]) )
		{
			$this->tpl->setVariable("SEARCH_STRING", $_POST["search_user"]);
		}
		else if(isset($_GET["search_user"]) )
		{
			$this->tpl->setVariable("SEARCH_STRING", $_GET["search_user"]);
		}

		$this->tpl->setVariable("FORMACTION_NEW_MEMBER", "group.php?type=grp&cmd=newMembers&ref_id=".$_GET["ref_id"]."&search_user=".$_POST["search_user"]);

		//$this->tpl->parseCurrentBlock();



		//query already started ?
		if ((isset($_POST["search_user"]) && isset($_POST["status"])) || ( isset($_GET["search_user"]) && isset($_GET["status"])))//&& isset($_GET["ref_id"]) )
		{
			if(!isset($_POST["search_user"]))
			{
				$_POST["search_user"]= $_GET["search_user"];
			}

			$member_ids = ilObjUser::searchUsers($_POST["search_user"]);


			if(count($member_ids) == 0)
			{
				sendInfo($this->lng->txt("search_no_match"),true);
				header ("Location: group.php?cmd=newmembers&ref_id=".$_GET["ref_id"]);
				exit();
			}
			else
			{
				//INTERIMS SOLUTION
				$_SESSION["status"] = $_POST["status"];

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
				$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&tree_id=".$this->grp_tree->getTreeId()."&tree_table=grp_tree");
				$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

				$this->data["buttons"] = array(	"canceldelete"  => $this->lng->txt("cancel"),
								"assignMember"  => $this->lng->txt("assign"));

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
				$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

				$offset = intval($_GET["offset"]);
				$limit = intval($_GET["limit"]);

				if ($limit == 0)
				{
					$limit = 10;	// TODO: move to user settings
				}

				if ($offset == "")
				{
					$offset = 0;	// TODO: move to user settings
				}

				$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

				// create table
				include_once "./classes/class.ilTableGUI.php";
				$tbl = new ilTableGUI($output);
				// title & header columns
				$tbl->setTitle($this->lng->txt("search_result"),"icon_usr_b.gif",$this->lng->txt("member list"));
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


		$this->tpl->show();
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function perm()
	{
		global $rbacsystem, $rbacreview;

		static $num = 0;

		if (!$rbacsystem->checkAccess("edit_permission", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
			exit();
		}

		// only display superordinate roles; local roles with other scope are not displayed
		$parentRoles = $rbacreview->getParentRoleIds($this->object->getRefId());

		$data = array();

		// GET ALL LOCAL ROLE IDS
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());

		$local_roles = array();

		if ($role_folder)
		{
			$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
		}

		foreach ($parentRoles as $r)
		{
			$data["rolenames"][] = $r["title"];

			if (!in_array($r["obj_id"],$local_roles) and $r["obj_id"] != SYSTEM_ROLE_ID)
			{
				$data["check_inherit"][] = ilUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
			}
			else
			{
				// don't display a checkbox for local roles AND system role
				if ($rbacreview->isAssignable($r["obj_id"],$role_folder["ref_id"]))
				{
					$data["check_inherit"][] = "&nbsp;";
				}
				else
				{
					// linked local roles with stopped inheritance
					$data["check_inherit"][] = ilUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}
		}

		$ope_list = getOperationList($this->object->getType());

		// BEGIN TABLE_DATA_OUTER
		foreach ($ope_list as $key => $operation)
		{
			$opdata = array();

			// skip 'create' permission because an object permission 'create' makes no sense
			if ($operation["operation"] != "create")
			{
				$opdata["name"] = $operation["operation"];

				$colspan = count($parentRoles) + 1;

				foreach ($parentRoles as $role)
				{
					if ($role["obj_id"] == SYSTEM_ROLE_ID)
					{
						$checked = true;
						$disabled = true;
					}
					else
					{
						$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
						$disabled = false;
					}

					// Es wird eine 2-dim Post Variable uebergeben: perm[rol_id][ops_id]
					$box = ilUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"],$disabled);
					$opdata["values"][] = $box;
				}

				$data["permission"][] = $opdata;
			}
		}

		/////////////////////
		// START DATA OUTPUT
		/////////////////////

		$this->prepareOutput(true, 7);
		$this->tpl->setVariable("HEADER",  $this->lng->txt("grp")." \"".$this->object->getTitle()."\"");
		$this->tpl->addBlockFile("CONTENT","permission", "tpl.obj_perm.html");

		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("permission_settings"));
		$this->tpl->setVariable("COLSPAN", $colspan);
		$this->tpl->setVariable("TXT_OPERATION", $this->lng->txt("operation"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;

		foreach ($data["rolenames"] as $name)
		{
			// BLOCK ROLENAMES
			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$name);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			if ($this->objDefinition->stopInheritance($this->type))
			{
				$this->tpl->setCurrentBLock("CHECK_INHERIT");
				$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num]);
				$this->tpl->parseCurrentBlock();
			}

			$num++;
		}

		// save num for required column span and the end of parsing
		$colspan = $num + 1;
		$num = 0;

		// offer option 'stop inheritance' only to those objects where this option is permitted
		if ($this->objDefinition->stopInheritance($this->type))
		{
			$this->tpl->setCurrentBLock("STOP_INHERIT");
			$this->tpl->setVariable("TXT_STOP_INHERITANCE", $this->lng->txt("stop_inheritance"));
			$this->tpl->parseCurrentBlock();
		}

		foreach ($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $this->lng->txt($this->object->getType()."_".$ar_perm["name"]));
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}
		
		$rolf = $rbacreview->getRoleFolderOfObject($this->object->getRefId());

		// ADD LOCAL ROLE
		if ($rbacsystem->checkAccess('create_role',$rolf["child"]))
		{
			$this->tpl->setCurrentBlock("LOCAL_ROLE");

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", "group.php?ref_id=".$_GET["ref_id"]."&cmd=addRole"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("addRole"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();
		}

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION",$this->getFormAction("permSave","group.php?".$this->link_params."&cmd=permSave"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("COL_ANZ",$colspan);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}



	/**
	* loads basic template file for group-enviroment
	*
	* @param	access public
	* @param	boolean variable, if not set or set true tabs are displayed
	* @param	multidimensional array for additional tabs; is only passed on;optional
	* @param	script that is used for linking in loacator;optional; default: "group.php"
	**/
	function prepareOutput($tabs=true, $active="")
	{
		global $rbacsystem;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_basic.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$this->tpl->setVariable("LINK_FLAT", "group.php?viewmode=flat&ref_id=".$this->ref_id);
		$this->tpl->setVariable("IMG_FLAT",ilUtil::getImagePath("ic_flatview.gif"));

		$this->tpl->setVariable("LINK_TREE", "group.php?viewmode=tree&ref_id=".$this->ref_id);
		$this->tpl->setVariable("IMG_TREE",ilUtil::getImagePath("ic_treeview.gif"));

		infoPanel();
		sendInfo();
		$counter = 3;
		$tab = array();

		$tab[0] = array ();
		$tab[0]["tab_cmd"] = "cmd=show_content&ref_id=".$this->grp_id;//link for tab
		$tab[0]["ftabtype"] = 'tabinactive';  					//tab is marked
		$tab[0]["target"] = "bottom";  						//target-frame of tab_cmd
		$tab[0]["tab_text"] ='resources';

		$tab[1] = array ();
		$tab[1]["tab_cmd"]  = 'cmd=showgroupmembers&ref_id='.$this->grp_id;			//link for tab
		$tab[1]["ftabtype"] = 'tabinactive';						//tab is marked
		$tab[1]["target"]   = "bottom";							//target-frame of tab_cmd
		$tab[1]["tab_text"] = 'group_members';						//tab -text

		/*$tab[2] = array ();
		$tab[2]["tab_cmd"]  = $_GET["tree"] ? 'cmd=show_content&ref_id='.$this->grp_id : 'cmd=show_content&tree=true&ref_id='.$this->grp_id;			//link for tab
		$tab[2]["ftabtype"] = 'tabinactive';						//tab is marked
		$tab[2]["target"]   = "bottom";							//target-frame of tab_cmd
		$tab[2]["tab_text"] = $_GET["tree"] ? 'hide_structure' : 'show_structure';						//tab -text
		*/
		if( $rbacsystem->checkAccess('delete',ilUtil::getGroupId($_GET["ref_id"])) && (count($applications = $this->grp_object->getNewRegistrations())!= 0) )
		{
			$tab[5] = array ();
			$tab[5]["tab_cmd"]  = 'cmd=showApplicationList&ref_id='.$this->grp_id;			//link for tab
			$tab[5]["ftabtype"] = 'tabinactive';						//tab is marked
			$tab[5]["target"]   = "bottom";							//target-frame of tab_cmd
			$tab[5]["tab_text"] = 'group_new_registrations';						//tab -text
			$counter ++;
		}

		//check if trash is filled
		$objects = $this->grp_tree->getSavedNodeData($_GET["ref_id"]);
		if (count($objects) > 0 /*and  $rbacsystem->checkAccess('delete',ilUtil::getGroupId($_GET["ref_id"])) */)
		{
			$tab[4] = array ();
			$tab[4]["tab_cmd"]  = 'cmd=trash&ref_id='.$_GET["ref_id"];		//link for tab
			$tab[4]["ftabtype"] = 'tabinactive';					//tab is marked
			$tab[4]["target"]   = "bottom";						//target-frame of tab_cmd
			$tab[4]["tab_text"] = 'trash';						//tab -text
			$counter ++;
		}

		if( $rbacsystem->checkAccess('edit',ilUtil::getGroupId($_GET["ref_id"])))
		{
			$tab[3] = array ();
			$tab[3]["tab_cmd"]  = 'cmd=editGroup&ref_id='.$this->grp_id;	//link for tab
			$tab[3]["ftabtype"] = 'tabinactive';					//tab is marked
			$tab[3]["target"]   = "_self";						//target-frame of tab_cmd
			$tab[3]["tab_text"] = "properties";				//tab -text
			$counter++;
		}

		if ($rbacsystem->checkAccess('edit_permission', ilUtil::getGroupId($_GET["ref_id"])) )
		{
			$tab[6] = array ();
			$tab[6]["tab_cmd"]  = 'cmd=perm&ref_id='.$_GET["ref_id"];		//link for tab
			$tab[6]["ftabtype"] = 'tabinactive';					//tab is marked
			$tab[6]["target"]   = "_self";						//target-frame of tab_cmd
			$tab[6]["tab_text"] = "perm_settings";				//tab -text
			$counter++;
		}


		if ($active != 99)
		{
			$tab[$active-1]["ftabtype"] = "tabactive";
		}


		$this->setAdminTabs($tabs, $tab);
		$this->setLocator();

	}


	/**
	* remove member object from group preparation(messages,link)
	* @access	public
	*/
	function leaveGroup()
	{
		$user_ids = array();
		$user_ids = $_GET["mem_id"];
		if(isset($user_ids))
		{
			$confirm = "confirmedLeaveGroup";
			$cancel  = "canceldelete";
			$info	 = "info_delete_sure";
			$status  = "";
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("location: group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* remove member object from group preparation(messages,link)
	* @access	public
	*/
	function removeMember()
	{
		$user_ids = array();

		if(isset($_POST["user_id"]))
		{
			$user_ids = $_POST["user_id"];
		}
		else
		{
			if(isset($_GET["mem_id"]))
			{
				$user_ids = $_GET["mem_id"];
			}
			else
			{
				sendInfo($this->lng->txt("user_not_chosen"),true);
				header("location: group.php?cmd=showgroupmembers&ref_id=".$_GET["ref_id"]);
				exit;
			}
		}
		if(isset($user_ids))
		{
			$confirm = "confirmedRemoveMember";
			$cancel  = "showgroupmembers";
			$info	 = "info_delete_sure";
			$status  = "";
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();
		}
		else
		{
			sendInfo($this->lng->txt("user_not_chosen"),true);
			header("location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* set admin tabs
	* @access	public
	* @param	boolean; whether standard tabs are set or not
	* @param	multdimensional array for additional tabs; optional
	*/
	function setAdminTabs($settabs=false, $addtabs="")
	{

		if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
		{
			$ftabtype = "tabactive";
			$ttabtype = "tabinactive";
		}
		else
		{
			$ftabtype = "tabinactive";
			$ttabtype = "tabactive";
		}
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

		if (!empty($addtabs))
		{
			foreach($addtabs as $addtab)
			{
				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE", $addtab["ftabtype"]);
				$this->tpl->setVariable("TAB_TARGET", $addtab["target"]);
				$this->tpl->setVariable("TAB_LINK", "group.php?".$addtab["tab_cmd"]);
				$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($addtab["tab_text"]));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	/**
	* @access	public
	*/
	function showApplicationList()
	{
		global $rbacsystem;

		$this->prepareOutput(false, 6);
		//get new applicants
		$applications = $this->object->getNewRegistrations();

		$img_contact = "pencil";
		$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
//		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		foreach($applications as $applicant)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($applicant->user_id);

			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$user->getLogin();
			$link_change = "group.php?cmd=changeMember&ref_id=".$this->ref_id."&mem_id=".$user->getId();
			$member_functions = "<a href=\"$link_change\">$val_change</a>";

			$this->data["data"][$user->getId()]= array(
				"check"		=> ilUtil::formCheckBox(0,"user_id[]",$user->getId()),
				"username"        => $user->getLogin(),
				"fullname"       => $user->getFullname(),
				"subject"        => $applicant->subject,
				"date" 		 => $applicant->application_date,
				"functions" => "<a href=\"$link_contact\">".$val_contact."</a>"
				);

				unset($member_functions);
				unset($user);
		}

		$this->tpl->setVariable("HEADER",  $this->lng->txt("grp")."&nbsp;&nbsp;\"".$this->object->getTitle()."\"");
		$this->tpl->addBlockfile("CONTENT", "member_table", "tpl.table.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&gateway=true");

		$this->data["buttons"] = array( "Cancel"  => $this->lng->txt("cancel"),
						"AssignApplicants"  => $this->lng->txt("assign"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("TPLPATH",$this->tplPath);

		$this->tpl->setVariable("COLUMN_COUNTS",6);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0) $limit = 10;	// TODO: move to user settings
		if ($offset == "") $offset = 0;	// TODO: move to user settings

		if (isset($this->data["data"]))
		{
			//sort data array
			$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
			$output = array_slice($this->data["data"],$offset,$limit);
		}

		// create table
		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("group_new_registrations"),"icon_usr_b.gif",$this->lng->txt("group_applicants"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("fullname"),$this->lng->txt("subject"),$this->lng->txt("application_date"),$this->lng->txt("functions")));
		$tbl->setHeaderVars(array("check","login","fullname","subject","application_date","functions"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));
		$tbl->setColumnWidth(array("5%","20%","20%","40%","15%","5%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();
		$tbl->render();
		$this->tpl->show();
	}

	/**
	* show possible action (form buttons)
	*
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";

			// temp. disabled
			//$notoperations[] = "cut";
			//$notoperations[] = "copy";
			$notoperations[] = "link";
			$notoperations[] = "move";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
			//$notoperations[] = "move";

			//temp. disabled
			$notoperations[] = "paste";
			//$notoperations[] = "clear";
		}
		
		$operations = array();

		$d = $this->objDefinition->getActions("grp");

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($with_subobjects == true)
		{
			$this->showPossibleSubObjects();
		}

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects("grp");

		$import = false;
		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						//echo "yxc".$this->data["ctrl"][$i]["type"].$row["name"];
						if ($this->data["ctrl"][$i]["type"] == $row["name"] )
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					//special case: if type is folder or file then the 'create' right of the group (first RBAC-PARENT object)is checked
					if (($row["name"] == "fold") || ($row["name"] == "file"  ))
					{
						//show only creatable objects for current user
						if($rbacsystem->checkAccess('create_'.$row["name"],$this->grp_id))
						{
							$subobj[] = $row["name"];
						}
					}
					else
					{
						//show only creatable objects for current user
						if($rbacsystem->checkAccess('create',$this->grp_id,$row["name"]))
						{
							$subobj[] = $row["name"];
						}

					}
					if($row["import"] == "1")	// import allowed?
					{
						$import = true;
					}
				}
			}
		}

		$import = false;

		if (is_array($subobj))
		{
			// show import button if at least one
			// object type can be imported
			if ($import)
			{
				$this->tpl->setCurrentBlock("import_object");
				$this->tpl->setVariable("BTN_IMP", "import");
				$this->tpl->setVariable("TXT_IMP", $this->lng->txt("import"));
				$this->tpl->parseCurrentBlock();
			}

			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/*
	*function displays content of a group given by its ref_id
	*
	* @access	public
	*/
	function show_content()
	{
		global $rbacsystem;

		$this->prepareOutput(false,1);
		$my_grp = new ilObjGroup($this->grp_id);	//a bit circumstancial, but thats the way it is
		$this->tpl->setVariable("HEADER",  $this->lng->txt("grp")."&nbsp;&nbsp;\"".$my_grp->getTitle()."\"");
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$this->object->getRefId());
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

		// set offset & limit

		$objects = $this->grp_tree->getChilds($this->object->getRefId(),"title"); //provides variable with objects located under given node
		/* count objects for statements in footer of table */
		$maxcount = count($objects);
		/* sort array and slice it to handle groups containing more than 10 objects */
		$objects = ilUtil::sortArray($objects, $_GET["sort_by"], $_GET["sort_order"]);
		$objects = array_slice($objects,$_GET["offset"],$_GET["limit"]);

		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($rbacsystem->checkAccess('visible',$object["ref_id"]) or $object["type"] == "fold" or $object["type"] == "file")
				{
					$cont_arr[$key] = $object;
					//var_dump($cont_arr[$key]);
				}
			}
		}

		$this->tpl->addBlockfile("CONTENT", "group_content", "tpl.grp_view.html");
		$this->tpl->addBlockfile("TABLE", "group_table", "tpl.table.html");
		$this->tpl->setCurrentBlock("group_table");

		// load template for table content data
		$access = false;

		//check if user got "write" permissions; if so $access is set true to prevent further database queries in this function
		//if ($rbacsystem->checkAccess("write", $this->grp_object->getRefId()))
		//{
			 $access = true;
			 $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows_checkbox.html");
		/*}
		else
		{
			 $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
		}*/

		$cont_num = count($cont_arr);
		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				//temporary solution later rolf should be viewablle for grp admin
				if ($cont_data["type"] != "rolf")
				{
					$this->tpl->setCurrentBlock("tbl_content");
					$newuser = new ilObjUser($cont_data["owner"]);
					// change row color
					$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
					$num++;

					if ($cont_data["type"] == "lm")
					{
						$link_target = "_top";
					}
					elseif ($cont_data["type"] == "frm" or $cont_data["type"] == "glo" or $cont_data["type"] == "slm")
					{

						$link_target = "bottom";

					}
					else
					{
						$link_target = "_self";
					}

					$obj_link = $this->getURLbyType($cont_data);

					$obj_icon = "icon_".$cont_data["type"]."_b.gif";
					$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckbox(0, "id[]", $cont_data["ref_id"]));
					$this->tpl->setVariable("TITLE", $cont_data["title"]);
					$this->tpl->setVariable("LINK", $obj_link);
					$this->tpl->setVariable("LINK_TARGET", $link_target);
					$this->tpl->setVariable("IMG", $obj_icon);
					$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
					$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
					$this->tpl->setVariable("OWNER", $newuser->getFullName());
					$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cont_data["last_update"]));

					if ($this->rbacsystem->checkAccess("write", $cont_data["ref_id"]))
					{
						$obj_link = $this->getURLbyType($cont_data, "edit");
						if (!empty($obj_link))
						{
							$this->tpl->setVariable("EDIT_LINK", $obj_link);
							$this->tpl->setVariable("EDIT_TARGET", "bottom");
							$this->tpl->setVariable("TXT_EDIT", "[".$this->lng->txt("edit")."]");
						}
					}
                    
					// add to desktop link
					if(!$this->ilias->account->isDesktopItem($cont_data["ref_id"], $cont_data["type"]) )
					{
						$obj_link = $this->getURLbyType($cont_data, "abo");
						if (!empty($obj_link))
						{
							$this->tpl->setVariable("TO_DESK_LINK", $obj_link);
          					$this->tpl->setVariable("TO_DESK_TARGET", "bottom");
							$this->tpl->setVariable("TXT_TO_DESK", "[".$this->lng->txt("to_desktop")."]");
						}
					}
					
					//TODO
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_any_objects"));
			$this->tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();
		// buttons in bottom-bar
		if ($access)
		{
			$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("owner"),$this->lng->txt("last_change")));
			$tbl->setHeaderVars(array("checkbox","title","status","last_change"), array("cmd"=>"show_content", "ref_id"=>$_GET["ref_id"]));
			$tbl->setColumnWidth(array("15","70%","15%","15%"));
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->SetVariable("COLUMN_COUNTS", "5");
			$this->showActions(true);
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner"),$this->lng->txt("last_change")));
			$tbl->setHeaderVars(array("title","status","last_change"), array("cmd"=>"show_content", "ref_id"=>$_GET["ref_id"]));
			$tbl->setColumnWidth(array("7%","10%","15%","15%","22%"));
		}

		// title & header columns
		$tbl->setTitle($this->lng->txt("resources"),"icon_grp_b.gif", $this->lng->txt("resources"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));// footer
		$tbl->render(); // render table
		
		$this->tpl->show();
	}
	
	
	/**
	* calls current view mode (tree frame or list)
	*/
	function view()
	{
		global $rbacsystem,$ilias;

		$obj_grp = & $this->ilias->obj_factory->getInstanceByRefId($this->grp_id);
		if (isset($_GET["viewmode"]))
		{
			$_SESSION["viewmode"] = $_GET["viewmode"];
		}
		else if(!isset($_SESSION["viewmode"]))
			$_SESSION["viewmode"] = "flat";	//default viewmode

		//any suggestions to check SystemSuperUser-Permissions ?
		if($_SESSION["viewmode"] == 'flat' && ($obj_grp->isMember() == true || $rbacsystem->checkAccess("create,delete,read,write,join,leave,visible",$this->grp_id,$this->object->getType())))
		{
			$this->show_content();
		}
		elseif($_SESSION["viewmode"] == 'tree' && ($obj_grp->isMember() == true || $rbacsystem->checkAccess("create,delete,read,write,join,leave,visible",$this->grp_id,$this->object->getType())))
		{
			$this->show_tree();
		}
		elseif ($obj_grp->isMember()==false && $rbacsystem->checkAccess("join", $this->ref_id))
		{
			header("location: group.php?cmd=AccessDenied&ref_id=".$_GET["ref_id"]);
		}
		else
		{
			sendInfo($this->lng->txt("no_permission_to_join"),true);
			header("Location: repository.php?ref_id=".$_SESSION["il_rep_ref_id"]);
		}


		// tree frame
/*		if ($_SESSION["viewmode"] == "tree")
		{
			$this->tpl = new ilTemplate("tpl.group.html", false, false);
			$this->tpl->setVariable ("EXP", "group.php?cmd=explorer&ref_id=".$_GET["ref_id"]."&expand=".$_GET["expand"]);
			$this->tpl->setVariable ("SOURCE", "group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
			$this->tpl->show();
		}
		else	// list
		{
			$this->show_content();
		}
*/
	}
	/**
	* display tree view
	**/
	function show_tree()
	{
		$this->prepareOutput(false,1);

		$this->tpl->setVariable("HEADER",  $this->lng->txt("grp")."&nbsp;&nbsp;\"".$this->object->getTitle()."\"");
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$this->object->getRefId());
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

		$this->tpl->addBlockfile("CONTENT", "group_content", "tpl.grp_view.html");
		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_explorer.html");


		$exp = new ilGroupExplorer("group.php?cmd=show_tree",$this->grp_id);

		$expanded = array();

		if ($_GET["grp_expand"] == "")
		{
			$expanded = $this->grp_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["grp_expand"];
		}

		$exp->setExpand($expanded);

			// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("objects");
		
		$this->tpl->setVariable("EXPLORER", $output);
		$this->tpl->parseCurrentBlock();
		

		$this->tpl->show();
	}
	

	/**
	* show trash content of object
	*
	* @access	public
 	*/
	function trash()
	{
		$this->prepareOutput(false, 5);

		$objects = $this->grp_tree->getSavedNodeData($_GET["ref_id"]);
	
		if (count($objects) == 0)
		{
			sendInfo($this->lng->txt("msg_trash_empty"));
			$this->data["empty"] = true;
		}
		else
		{
			$this->data["empty"] = false;
			$this->data["cols"] = array("","type", "title", "description", "last_change");

			foreach ($objects as $obj_data)
			{
				$this->data["data"]["$obj_data[child]"] = array(
					"checkbox"    => "",
					"type"        => $obj_data["type"],
					"title"       => $obj_data["title"],
					"desc"        => $obj_data["desc"],
					"last_update" => $obj_data["last_update"]);
			}

			$this->data["buttons"] = array( "undelete"  => $this->lng->txt("btn_undelete"),
									  "removeFromSystem"  => $this->lng->txt("btn_remove_system"));
		}

		// load template for table
		$this->tpl->addBlockfile("CONTENT", "group_table", "tpl.obj_confirm.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.obj_confirm.html");

		$this->tpl->setVariable("HEADER",  $this->object->getTitle());

		if ($this->data["empty"] == true)
		{
			return;
		}

		/* TODO: fix message display in conjunction with sendIfno & raiseError functionality
		$this->tpl->addBlockfile("MESSAGE", "adm_trash", "tpl.message.html");
		$this->tpl->setCurrentBlock("adm_trash");
		$this->tpl->setVariable("MSG",$this->lng->txt("info_trash"));
		$this->tpl->parseCurrentBlock();
		*/
		//sendInfo($this->lng->txt("info_trash"));

		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]);

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

		foreach ($this->data["data"] as $key1 => $value)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			// BEGIN TABLE CELL
			foreach ($value as $key2 => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");
				// CREATE CHECKBOX
				if ($key2 == "checkbox")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::formCheckBox(0,"trash_id[]",$key1));
				}

				// CREATE TEXT STRING
				elseif ($key2 == "type")
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
		
		$this->tpl->show();
	}


	/**
	* @access	public
	*/
	function updateMemberStatus()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilias->raiseError("No permissions to change member status!",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$grp = new ilObjGroup($_GET["ref_id"]);
			if(isset($_POST["member_status_select"]))
			{
				foreach ($_POST["member_status_select"] as $key=>$value)
				{
					$grp->setMemberStatus($key,$value);
				}
			}
		}
		//TODO: link back
		sendInfo($this->lng->txt("msg_obj_modified"), true);
		header("location: group.php?cmd=showgroupmembers&ref_id=".$_GET["ref_id"]);
	}


	/**
	* method checks fields filled correctly and calls core methods
	* @access	public
	*/
	function updateGroupStatus()
	{
		global $rbacsystem;
		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		if($_POST["enable_registration"] == 2 && empty($_POST["password"]) || empty($_POST["expirationdate"]) || empty($_POST["expirationtime"]) )//Password-Registration Mode
		{
			$this->ilias->raiseError($this->lng->txt("grp_err_registration_data"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilias->raiseError("No permissions to change group status!",$this->ilias->error_obj->WARNING);
		}
		else
		{
			if (isset($_POST["group_status"]))
			{
				$this->grp_object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
				$this->grp_object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
				if($_POST["group_reset"] == 1)
				{
					$this->grp_object->setGroupStatus($_POST["group_status"]);
				}
				$this->grp_object->setRegistrationFlag($_POST["enable_registration"]);
				if($_POST["enable_registration"] == 2 && !ilUtil::isPassword($_POST["password"]))
				{
					$this->ilias->raiseError($this->lng->txt("passwd_invalid"),$this->ilias->error_obj->MESSAGE);
				}
				$this->grp_object->setPassword($_POST["password"]);
				$this->grp_object->setExpirationDateTime($_POST["expirationdate"]." ".$_POST["expirationtime"].":00");
				$this->update = $this->grp_object->update();
			}
		}

		header("Location: group.php?".$this->link_params);
	}

	/**
	* @access	public
	*/
	function viewObject()
	{
		//necessary for gateway calls
		$this->view();
	}


	/**
	* displays form with all members of group
	* @access public
	*/
	function showGroupmembers()
	{
		global $rbacsystem;

		$this->prepareOutput(false, 2);
		$this->membersobject("group.php?", "CONTENT", "&gateway=true");
		$this->tpl->show();
	}

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function assignMember()
	{

		$user_ids = $_POST["user_id"];

		if(isset($user_ids))
		{
			$confirm = "confirmedAssignMember";
			$cancel  = "canceldelete";
			$info	 = "info_assign_sure";
			$status  = $_SESSION["status"];
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* set Locator
	* @access	public
	*/
	function setLocator()
	{

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		$path = $this->grp_tree->getPathFull($_GET["ref_id"]);
		$path2 = $this->tree->getPathFull($path[0]["child"]);
		//$this->tpl->touchBlock("locator_separator");

		// ### AA 03.11.10 added new locator GUI class ###
		// navigate locator
		//TODO: grp_list existiert nicht mehr !!!
//		$ilias_locator->navigate($i++,$this->lng->txt("groups"),"grp_list.php","bottom");

		foreach ($path2 as $key => $row)
		{
			if ($key < count($path2)-1)
			{
				$this->tpl->touchBlock("locator_separator");
			}
			if (strcmp($row["type"],"grp") == 0)
			{
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM", "group.php?ref_id=".$row["child"]);
				$this->tpl->setVariable("LINK_TARGET", "target=\"bottom\"");
				$this->tpl->parseCurrentBlock();

				// ### AA 03.11.10 added new locator GUI class ###
				// navigate locator
//				$ilias_locator->navigate($i++,$row["title"],"group.php?ref_id=".$row["child"],"bottom");
			}
			else
			{
				$this->tpl->setCurrentBlock("locator_item");
				if ($this->tree->getRootId() == $row["child"])
				{
					$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				}
				else
				{
					$this->tpl->setVariable("ITEM", $row["title"]);
				}
				$this->tpl->setVariable("LINK_ITEM", "repository.php?ref_id=".$row["child"]);
				$this->tpl->setVariable("LINK_TARGET", "target=\"bottom\"");
				$this->tpl->parseCurrentBlock();

				// ### AA 03.11.10 added new locator GUI class ###
				// navigate locator
				if ($this->tree->getRootId() == $row["child"])
				{
//					$ilias_locator->navigate($i++,$this->lng->txt("repository"),"group.php?ref_id=".$row["child"],"bottom");
				}
				else
				{
//					$ilias_locator->navigate($i++,$row["title"],"group.php?ref_id=".$row["child"],"bottom");
				}
			}

		}

		//$modifier= 0;
		array_shift ($path);

		if (count ($path) > 0)
		{

			$this->tpl->touchBlock("locator_separator");
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->parseCurrentBlock();
		}

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-1)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			$this->tpl->setVariable("LINK_ITEM", "group.php?ref_id=".$row["child"]);
			$this->tpl->setVariable("LINK_TARGET", "target=\"bottom\"");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");



		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}

		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* @access	public
	*/
	function get_file()
	{
		global $rbacsystem;

		$fileObj =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		$file_name = $fileObj->getFilePath()."/".$fileObj->getFileName();

		// security check: does requested file exists? (is in a group)
		$grp_ref_id = ilUtil::getGroupId($fileObj->getrefId());

		if ($grp_ref_id === false)
		{
			// does not exists; abort
			return false;
		}

		// security check: is current user allowed to download the file?
		if (!$rbacsystem->checkAccess("read",$grp_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

	    // Create download file name to be displayed to user
	    $save_as_name = basename($file_name);
	    // Send binary filetype HTTP header
	    header("Content-Type: ".$fileObj->getFileType());
	    // Send content-length HTTP header
	    header('Content-Length: '.filesize($file_name));
	    // Send content-disposition with save file name HTTP header
	    header('Content-Disposition: attachment; filename="'.$save_as_name.'"');
	    // Output file
	    readfile($file_name);
	    // Done
	    return true;
	}


	/**
	* confirmed deletion if object -> objects are moved to trash
	*
	* However objects are only removed from tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* Trash Bin Feature: Objects can be refreshed in trash
	*
	* @access	public
	*/
	function confirmedDelete()
	{
		global $rbacsystem, $rbacadmin;

		// TODO: move checkings to deleteObject
		// TODO: cannot distinguish between obj_id from ref_id with the posted IDs.
		// change the form field and use instead of 'id' 'ref_id' and 'obj_id'. Then switch with varname

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}


		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{

		 	// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES FROM TREE TABLE
			$tree_subtree_nodes = $this->tree->getSubTree($this->tree->getNodeData($id));

			$tree_all_node_data[] = $tree_node_data;
			$tree_all_subtree_nodes[] = $tree_subtree_nodes;


			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES FROM GRP_TREE TABLE
			$grp_tree_subtree_nodes = $this->grp_tree->getSubTree($this->grp_tree->getNodeData($id));

			//$grp_tree_all_node_data[] = $grp_tree_node_data;
			$grp_tree_all_subtree_nodes[] = $grp_tree_subtree_nodes;



			//GET ALL NODES THAT SHOUD BE DELETED (from grp_tree and from tree)
			$all_subnodes = array();

			$all_subnodes = $this->grp_tree->getSubtree($this->grp_tree->getNodeData($id));

			if($all_subnodes[0]["perm"] == 0)
			{
				foreach($all_subnodes as $node)
				{
					if($node["perm"] != 0)
					{
						$tree_subnodes = $this->tree->getSubtree($this->tree->getNodeData($node["child"]));
						foreach($tree_subnodes as $tree_node)
						{
							$tree_node_in_all = false;
							foreach($all_subnodes as $all_node)
							{
								if( $tree_node["child"] == $all_node["child"])
								{
									$tree_node_in_all = true;
								}
							}
							if(!$tree_node_in_all)
								array_push($all_subnodes,$tree_node);
						}
					}
				}
			}




			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($all_subnodes as $node)
			{
				$check_id = $node["child"];

				if($node["perm"] == 0)
				{
					$check_id == $this->grp_id;
				}

				if (!$rbacsystem->checkAccess('delete',$check_id))
				{
					$not_deletable[] = $node["child"];
					$perform_delete = false;
				}
			}



		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if (count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
									 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}

		// DELETE THEM
		/*if (!$tree_all_node_data[0]["type"])
		{
			// OBJECTS ARE NO 'TREE OBJECTS'
			if ($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
			{
				foreach($_SESSION["saved_post"] as $id)
				{
					$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
					$obj->delete();
				}
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("no_perm_delete"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{*/


			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			foreach ($_SESSION["saved_post"] as $id)
			{

				// GET ALL RBAC-OBJECTS
				//variable $tree_subnodes contains all subnodes of variable $id in tree_table
				//variable $grp_tree_subnodes contains all subnodes of variable $id in grp_table
				$tree_subnodes = array();
				$grp_tree_subnodes = $this->grp_tree->getSubtree($this->grp_tree->getNodeData($id));

				if ($grp_tree_subnodes[0]["perm"] == 1)
				{
					$tree_subnodes = $this->tree->getSubtree($this->tree->getNodeData($id));
				}
				else
				{

					foreach($grp_tree_subnodes as $grp_tree_subnode)
					{
						if($grp_tree_subnode["perm"] == 1 )
						{
							$tree_subnodes = $this->tree->getSubtree($this->tree->getNodeData($grp_tree_subnode["child"]));
						}
					}
				}


				// DELETE OLD PERMISSION ENTRIES
				foreach ($tree_subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["child"]);
					// remove item from all user desktops
					$affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);

					// TODO: inform users by mail that object $id was deleted
					//$mail->sendMail($id,$msg,$affected_users);
				}

				//SET DELETED NODES IN TREE TABLE
				if ($grp_tree_subnodes[0]["perm"] == 1)
				{
					$this->tree->saveSubTree($id);
					$this->tree->deleteTree($this->tree->getNodeData($id));
				}
				else
				{
					//node that are deleted in tree table ( and their subnodes)
					foreach($grp_tree_subnodes as $grp_tree_subnode)
					{
						if($grp_tree_subnode["perm"] == 1 )
						{
							$this->tree->saveSubTree($grp_tree_subnode["child"]);
							$this->tree->deleteTree($this->tree->getNodeData($grp_tree_subnode["child"]));
						}
					}
				}

				//SET DELETED NODES IN GRP_TREE TABLE
				$this->grp_tree->saveSubTree($id);
				$this->grp_tree->deleteTree($this->grp_tree->getNodeData($id));



				// remove item from all user desktops
				foreach($grp_tree_subnodes as $grp_tree_subnode)
				{
					if($grp_tree_subnode["perm"] == 1 )
					{
						$affected_users = ilUtil::removeItemFromDesktops($grp_tree_subnode["child"]);
					}
				}

				// TODO: inform users by mail that object $id was deleted
				//$mail->sendMail($id,$msg,$affected_users);
			}
			// inform other objects in hierarchy about paste operation
			//$this->object->notify("confirmedDelete", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_SESSION["saved_post"]);
		//}

		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		header("Location:".$this->getReturnLocation("confirmedDelete","adm_object.php?ref_id=".$_GET["ref_id"]));
		exit();
	}

	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	*
	* @access	public
	*/
	function removeFromSystem()
	{
		global $rbacsystem;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);


			if ($obj_data->getType() == "fold" or $obj_data->getType() == "file")
			{
				if (!$rbacsystem->checkAccess('create',ilUtil::getGroupId($_GET["ref_id"]),"grp"))
				{
					$no_create[] = $id;
				}
			}
			else
			{
				if (!$rbacsystem->checkAccess('create',ilUtil::getGroupId($_GET["ref_id"]),$obj_data->getType()))
				{
					$no_create[] = $id;
				}
			}
		}

		if (count($no_create))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
									 implode(',',$no_create),$this->ilias->error_obj->MESSAGE);
		}
		//$this->object->notify("removeFromSystem", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_POST["trash_id"]);

		// DELETE THEM
		foreach ($_POST["trash_id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES FROM GRP_TREE TABLE
			$saved_grp_tree = new ilGroupTree(-(int)$id);
			$grp_node_data = $saved_grp_tree->getNodeData($id);
			$grp_subtree_nodes = $saved_grp_tree->getSubTree($grp_node_data);
		
			
			if( $grp_subtree_nodes[0]["perm"] == 1 ) 
			{
				// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES FROM TREE TABLE
				$saved_tree = new ilTree(-(int)$id);
				$node_data = $saved_tree->getNodeData($id);
				$subtree_nodes = $saved_tree->getSubTree($node_data);
			}
			else
			{
				$subtree_nodes = array();
			}
			

			// remember already checked deleted node_ids
			$checked[] = -(int) $id;

			// dive in recursive manner in each already deleted subtrees and remove these objects too
			$this->removeDeletedNodes($id,$checked,"tree");
			$this->removeDeletedNodes($id,$checked,"grp_tree");

			foreach ($subtree_nodes as $node)
			{
				$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
				$node_obj->delete();
			}
			
			// DELETE ALL ENTRIES IN TREES
			if( $grp_subtree_nodes[0]["perm"] == 1 ) 
			{
				$this->tree->deleteTree($node_data);
			}
			$this->grp_tree->deleteTree($grp_node_data);
			
		}
		
		sendInfo($this->lng->txt("msg_removed"),true);
		
		header("Location:".$this->getReturnLocation("removeFromSystem","adm_object.php?ref_id=".$_GET["ref_id"]));
		exit();
	}
	
	/**
	* remove already deleted objects within the objects in trash
	* recursive function
	*
	* @access	public
	* @param	integer ref_id of source object
	* @param    boolean 
	* @param	string database table
	*/
	function removeDeletedNodes($a_node_id,$a_checked,$a_db_table)
	{
		$q = "SELECT tree FROM ".$a_db_table." WHERE parent='".$a_node_id."' AND tree < 0";
		
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// only continue recursion if fetched node wasn't touched already!
			if (!in_array($row->tree,$a_checked))
			{
				
				if ($a_db_table=="grp_tree")
				{
					$deleted_tree = new ilGroupTree($row->tree);
				}
				else
				{
					$deleted_tree = new ilTree($row->tree);
				}
				$a_checked[] = $row->tree;
			
				$row->tree = $row->tree * (-1);
				$del_node_data = $deleted_tree->getNodeData($row->tree);
				$del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);

				if ($a_db_table=="grp_tree")
				{
					$this->removeDeletedNodes($row->tree,$a_checked,$a_db_table);
				}
				else
				{
					$this->removeDeletedNodes($row->tree,$a_checked,$a_db_table);
				}	
				
				if ($a_db_table=="tree")
				{				
					foreach ($del_subtree_nodes as $node)
					{
						$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
						$node_obj->delete();
					}
				}
				if ($a_db_table=="grp_tree")
				{
					$this->grp_tree->deleteTree($del_node_data);
				}
				else
				{
					$this->tree->deleteTree($del_node_data);
				}
				
			}
		}
		
		return true;
	}
	
	/**
	* get object back from trash
	*
	* @access	public
	*/
	function undelete()
	{
		global $rbacsystem;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);


			if ($obj_data->getType() == "fold" or $obj_data->getType() == "file")
			{
				if (!$rbacsystem->checkAccess('create',ilUtil::getGroupId($_GET["ref_id"]),"grp"))
				{
					$no_create[] = $id;
				}
			}
			else
			{
				if (!$rbacsystem->checkAccess('create',ilUtil::getGroupId($_GET["ref_id"]),$obj_data->getType()))
				{
					$no_create[] = $id;
				}
			}
		}

		if (count($no_create))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_undelete")." ".
									 implode(',',$no_create),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			$tree_subnodes = array();
			$grp_tree_subnodes = $this->grp_tree->getSubtree($this->grp_tree->getNodeData($id));

			if ($grp_tree_subnodes[0]["perm"] == 1)
			{
				$tree_subnodes = $this->tree->getSubtree($this->tree->getNodeData($id));
			}
			else
			{
				foreach($grp_tree_subnodes as $grp_tree_subnode)
				{
					if($grp_tree_subnode["perm"] == 1 )
					{
						$tree_subnodes = $this->tree->getSubtree($this->tree->getNodeData($grp_tree_subnode["child"]));
					}
				}
			}

			foreach ($tree_subnodes as $subnode_id)
			{
				// INSERT AND SET PERMISSIONS
				$this->insertSavedNodes($subnode_id,$_GET["ref_id"],-(int) $subnode_id,"tree");

				// DELETE SAVED TREE
				$saved_tree = new ilTree(-(int)$subnode_id);
				$saved_tree->deleteTree($saved_tree->getNodeData($subnode_id));

			}

			// INSERT
			$this->insertSavedNodes($id,$_GET["ref_id"],-(int) $id,"grp_tree");

			// DELETE SAVED TREE
			$grp_saved_tree = new ilGroupTree(-(int)$id);
			$grp_saved_tree->deleteTree($grp_saved_tree->getNodeData($id));
		}

		//$this->object->notify("undelete", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_POST["trash_id"]);

		sendInfo($this->lng->txt("msg_undeleted"),true);

		header("Location:".$this->getReturnLocation("undelete","adm_object.php?ref_id=".$_GET["ref_id"]));
		exit();
	}

	/**
	* recursive method to insert all saved nodes of the clipboard
	* (maybe this function could be moved to a rbac class ?)
	*
	* @access	private
	* @param	integer
	* @param	integer
	* @param	integer
	* @param	string database table
	*/
	function insertSavedNodes($a_source_id,$a_dest_id,$a_tree_id,$a_db_table)
	{
		global $rbacadmin, $rbacreview;

		if($a_db_table=="grp_tree")
		{
			$this->grp_object->insertGroupNode($a_source_id,$a_dest_id,$this->grp_id);
		}
		else
		{
			$this->tree->insertNode($a_source_id,$a_dest_id);
		}

		if($a_db_table=="tree")
		{
			// SET PERMISSIONS
			$parentRoles = $rbacreview->getParentRoleIds($a_dest_id);
			$obj =& $this->ilias->obj_factory->getInstanceByRefId($a_source_id);

			foreach ($parentRoles as $parRol)
			{
				$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"], $obj->getType(), $parRol["parent"]);
				$rbacadmin->grantPermission($parRol["obj_id"],$ops,$a_source_id);
			}
		}
		if($a_db_table=="grp_tree")
		{
			$grp_saved_tree = new ilGroupTree($a_tree_id);
			$childs = $grp_saved_tree->getChilds($a_source_id);
		}
		else
		{
			$saved_tree = new ilTree($a_tree_id);
			$childs = $saved_tree->getChilds($a_source_id);
		}

		foreach ($childs as $child)
		{
			$this->insertSavedNodes($child["child"],$a_source_id,$a_tree_id,$a_db_table);
		}
	}
}
?>
