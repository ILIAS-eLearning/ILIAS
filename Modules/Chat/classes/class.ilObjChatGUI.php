<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Class ilObjChatGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
* 
* @ilCtrl_Calls ilObjChatGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/

require_once "classes/class.ilObjectGUI.php";
require_once "Modules/Chat/classes/class.ilChatRecording.php";

class ilObjChatGUI extends ilObjectGUI
{
	var $target_script = "adm_object.php";
	var $in_module = false;

	/**
	* Constructor
	* @access public
	*/
	function ilObjChatGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $ilCtrl;

		#define("ILIAS_MODULE","chat");
		
		$this->type = "chat";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));


		if(is_object($this->object->chat_user))
		{
			$this->object->chat_user->setUserId($_SESSION["AccountId"]);
		}
	}

	function &executeCommand()
	{
		global $rbacsystem;

		if($this->ctrl->getTargetScript() == 'chat.php')
		{
			$this->__prepareOutput();
		}
		else
		{
			$this->prepareOutput();
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}

	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator,$tree,$ilObjDataCache;
		
		if ($_GET["admin_mode"] == "settings")	// system settings
		{
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));

			// add chat settings
			$chat_settings_ref_id = $tree->getParentId($this->object->getRefId());
			$chat_settings_obj_id = $ilObjDataCache->lookupObjId($chat_settings_ref_id);

			$this->ctrl->setParameterByClass('ilobjchatservergui','ref_id',$chat_settings_ref_id);
			$ilLocator->addItem($ilObjDataCache->lookupTitle($chat_settings_obj_id),
								$this->ctrl->getLinkTargetByClass(array('iladministrationgui','ilobjchatservergui'),
																  'view'));


			if ($this->object->getRefId() != SYSTEM_FOLDER_ID)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			return parent::addAdminLocatorItems();
		}

	}


	function setTargetScript($a_script)
	{
		$this->target_script = $a_script;
	}
	function getTargetScript($a_params)
	{
		return $this->target_script."?".$a_params;
	}
	function setInModule($in_module)
	{
		$this->in_module = $in_module;
	}
	function inModule()
	{
		return $this->in_module;
	}

	function saveObject()
	{
		global $ilUser,$rbacadmin;

		$new_obj =& parent::saveObject();
		
		// Add new moderator role
		$roles = $new_obj->initDefaultRoles();

		// Assign current user.
		$rbacadmin->assignUser($roles[0],$ilUser->getId());
		

		//$this->ctrl->setParameter($this, "ref_id", $new_obj->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			"chat.php?ref_id=".$new_obj->getRefId()."&amp;cmd=view"));
		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?ref_id=".$new_obj->getRefId()));
	}

	// Methods for blocked users (administration)
	function blockedUsersObject()
	{
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.chat_blocked_users.html","Modules/Chat");
		$blocked_obj = new ilChatBlockedUsers($this->object->getId());

		if(!count($blocked = $blocked_obj->getBlockedUsers()))
		{
			$this->tpl->setVariable("MESSAGE_NO_BLOCKED",$this->lng->txt('chat_no_blocked'));
		}
		else
		{
			$this->tpl->setCurrentBlock("delete_blocked");
			$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('chat_blocked_unlocked'));
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
			$this->tpl->parseCurrentBlock();
		}

		foreach($blocked as $usr_id)
		{
			$tmp_user =& new ilObjUser($usr_id);

			$this->tpl->setCurrentBlock("blocked_users");
			$this->tpl->setVariable("FULLNAME",$tmp_user->getFullname());
			$this->tpl->setVariable("LOGIN",$tmp_user->getLogin());
			$this->tpl->setVariable("BLOCK_CHECK",ilUtil::formCheckbox(0,'blocked_check[]',$tmp_user->getId()));
			$this->tpl->parseCurrentBlock();
		}

		// Fill table
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('chat_blocked_users'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('chat_blocked_users'));
		$this->tpl->setVariable("HEADER_NAME",$this->lng->txt('chat_user_name'));

		$this->tpl->setVariable("BTN_BLOCK",$this->lng->txt('chat_block_user'));
		
	}

	function blockUserObject()
	{
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$blocked_obj = new ilChatBlockedUsers($this->object->getId());

		if(!$_POST['block'] or !($usr_id = ilObjUser::getUserIdByLogin($_POST['block'])))
		{
			ilUtil::sendInfo($this->lng->txt('chat_enter_valid_username'));
			$this->blockedUsersObject();

			return false;
		}
		if($blocked_obj->isBlocked($usr_id))
		{
			ilUtil::sendInfo($this->lng->txt('chat_user_already_blocked'));
			$this->blockedUsersObject();

			return false;
		}			

		$blocked_obj->block($usr_id);
		ilUtil::sendInfo($this->lng->txt('chat_user_blocked'));
		$this->blockedUsersObject();

		return true;
		
	}

	function unblockUsersObject()
	{
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$blocked_obj = new ilChatBlockedUsers($this->object->getId());
		
		if(!is_array($_POST['blocked_check']))
		{
			ilUtil::sendInfo($this->lng->txt('chat_no_users_selected'));

			return $this->blockedUsersObject();
		}

		foreach($_POST['blocked_check'] as $usr_id)
		{
			$blocked_obj->unblock($usr_id);
		}

		ilUtil::sendInfo($this->lng->txt('chat_unblocked_user'));
		return $this->blockedUsersObject();
	}


	function kickUser()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if($_GET["kick_id"])
		{
			$tmp_user = new ilObjUser($_GET['kick_id']);

			$this->object->server_comm->setKickedUser($tmp_user->getLogin());
			$this->object->server_comm->setType("kick");
			$this->object->server_comm->send();

			$this->object->chat_room->setKicked((int)$_GET['kick_id']);

			#ilUtil::sendInfo($this->lng->txt("chat_user_dropped"),true);
			$this->showFrames();
		}
	}

	function unkickUser()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if($_GET["kick_id"])
		{
			$this->object->chat_room->setUnkicked((int)$_GET['kick_id']);

			#ilUtil::sendInfo($this->lng->txt("chat_user_dropped"),true);
			$this->showFrames();
		}
	}



	function cancelObject()
	{
		unset($_SESSION["room_id_rename"]);
		unset($_SESSION["room_id_delete"]);
		parent::cancelObject();
	}


	function viewObject()
	{
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}

		// Check blocked
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		global $rbacsystem,$ilUser;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
 		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.chat_view.html","Modules/Chat");

		// CHAT SERVER NOT ACTIVE
		if(!$this->object->server_comm->isAlive() or !$this->ilias->getSetting("chat_active"))
		{
			ilUtil::sendInfo($this->lng->txt("chat_server_not_active"));
		}
		if(ilChatBlockedUsers::_isBlocked($this->object->getId(),$ilUser->getId()))
		{
			ilUtil::sendInfo($this->lng->txt('chat_access_blocked'));

			return true;
		}


		// DELETE ROOMS CONFIRM
		$checked = array();
		if($_SESSION["room_id_delete"])
		{
			$checked = $_SESSION["room_id_delete"];
			ilUtil::sendInfo($this->lng->txt("chat_delete_sure"));
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("TXT_DELETE_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_DELETE_CONFIRM",$this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}

		// SHOW ROOMS TABLE
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_chat.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_chat'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('chat_rooms'));
								

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CHATROOMS",$this->lng->txt("chat_chatrooms"));
		$this->tpl->setVariable("ACTIONS",$this->lng->txt('actions'));

		$counter = 0;

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$rooms = $this->object->chat_room->getAllRoomsOfObject();
		}
		else
		{
			$this->object->chat_room->setOwnerId($_SESSION["AccountId"]);
			$rooms = $this->object->chat_room->getRoomsOfObject();
		}

		$script = './chat.php';

		// ADD PUBLIC ROOM
		// CHAT SERVER  ACTIVE
		

		if(ilChatBlockedUsers::_isBlocked($this->object->getId(),$ilUser->getId()))
		{
			$this->tpl->setCurrentBlock("blocked");
			$this->tpl->setVariable("MESSAGE_BLOCKED",$this->lng->txt('chat_blocked'));
			$this->tpl->parseCurrentBlock();
		}
		elseif($this->object->server_comm->isAlive() and $this->ilias->getSetting("chat_active"))
		{
			$this->tpl->setCurrentBlock("active");
			$this->tpl->setVariable("ROOM_LINK",$script."?ref_id=".$this->ref_id."&room_id=0");
			$this->tpl->setVariable("ROOM_TARGET","_blank");
			$this->tpl->setVariable("ROOM_TXT_LINK",$this->lng->txt("show"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->touchBlock("not_active");
		}
		$this->tpl->setCurrentBlock("tbl_rooms_row");
		$this->tpl->setVariable("ROWCOL",++$counter % 2 ? "tblrow1" : "tblrow2");
		$this->tpl->setVariable("ROOM_CHECK",
								ilUtil::formCheckbox(in_array(0,$checked) ? 1 : 0,
													 "del_id[]",
													 0));
		$this->tpl->setVariable("ROOM_NAME",$this->object->getTitle()." ".$this->lng->txt("chat_public_room"));
		$this->tpl->setVariable("USERS_ONLINE",
								$this->lng->txt('chat_users_active').': '.
								ilChatRoom::_getCountActiveUsers($this->object->getId()));
		$this->tpl->parseCurrentBlock();

		foreach($rooms as $room)
		{
			// CHAT SERVER  ACTIVE
			if($this->object->server_comm->isAlive() and $this->ilias->getSetting("chat_active"))
			{
				$this->tpl->setCurrentBlock("active");
				$this->tpl->setVariable("ROOM_LINK",$script."?ref_id=".$this->ref_id."&room_id=".$room["room_id"]);
				$this->tpl->setVariable("ROOM_TARGET","_blank");
				$this->tpl->setVariable("ROOM_TXT_LINK",$this->lng->txt("show"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->touchBlock("not_active");
			}
			$this->tpl->setCurrentBlock("tbl_rooms_row");
			$this->tpl->setVariable("ROWCOL",++$counter % 2 ? "tblrow1" : "tblrow2");
			$this->tpl->setVariable("ROOM_CHECK",
									ilUtil::formCheckbox(in_array($room["room_id"],$checked) ? 1 : 0,
									"del_id[]",
									$room["room_id"]));

			$this->tpl->setVariable("ROOM_NAME",$room["title"]);
			$this->tpl->setVariable("USERS_ONLINE",
									$this->lng->txt('chat_users_active').': '.
									ilChatRoom::_getCountActiveUsers($this->object->getId(),$room['room_id']));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("has_rooms");
		$this->tpl->setVariable("TBL_FOOTER_IMG_SRC", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("TBL_FOOTER_SELECT",$this->__showAdminRoomSelect(count($rooms)));
		$this->tpl->setVariable("FOOTER_HAS_ROOMS_OK",$this->lng->txt("ok"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TBL_FOOTER_ADD_SELECT",$this->__showAdminAddRoomSelect());
		$this->tpl->setVariable("FOOTER_OK",$this->lng->txt("add"));
		
		// permanent link
		$this->tpl->setCurrentBlock("perma_link");
		$this->tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			$this->object->getType().
			"_".$this->object->getRefId()."&client_id=".CLIENT_ID);
		$this->tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$this->tpl->setVariable("PERMA_TARGET", "_top");
		$this->tpl->parseCurrentBlock();

	}

	function adminRoomsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		if(!isset($_POST["del_id"]))
		{
			ilUtil::sendInfo($this->lng->txt("chat_select_one_room"));
			$this->viewObject();
			
			return false;
		}

		switch($_POST["action"])
		{
			case "renameRoom":
				if(count($_POST["del_id"]) > 1)
				{
					ilUtil::sendInfo($this->lng->txt("chat_select_one_room"));
					$this->viewObject();

					return false;
				}
				if(in_array(0,$_POST["del_id"]))
				{
					ilUtil::sendInfo($this->lng->txt("chat_no_rename_public"));
					$this->viewObject();

					return false;
				}

				// STORE ID IN SESSION
				$_SESSION["room_id_rename"] = (int) $_POST["del_id"][0];

				$room =& new ilChatRoom($this->object->getId());
				$room->setRoomId($_SESSION["room_id_rename"]);

				$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.chat_edit_room.html","Modules/Chat");
				$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
				$this->tpl->setVariable("TXT_ROOM_NAME",$this->lng->txt("chat_room_name"));
				$this->tpl->setVariable("ROOM_CANCEL",$this->lng->txt("cancel"));
				$this->tpl->setVariable("TXT_EDIT_CHATROOMS",$this->lng->txt("chat_chatroom_rename"));
				$this->tpl->setVariable("ROOM_NAME",$room->getTitle());
				$this->tpl->setVariable("CMD","renameRoom");
				$this->tpl->setVariable("ROOM_EDIT",$this->lng->txt("rename"));
				break;

			case "deleteRoom":
				if(in_array(0,$_POST["del_id"]))
				{
					ilUtil::sendInfo($this->lng->txt("chat_no_delete_public"));
					$this->viewObject();

					return false;
				}
				$_SESSION["room_id_delete"] = $_POST["del_id"];
				$this->viewObject();

				return true;


			case "exportRoom":
				$this->__exportRooms();
				break;

			case "refreshRoom":
				if(in_array(0,$_POST["del_id"]) and !$rbacsystem->checkAccess('write',$this->object->getRefId()))
				{
					ilUtil::sendInfo($this->lng->txt("chat_no_refresh_public"));
					$this->viewObject();

					return true;
				}
				foreach($_POST["del_id"] as $room_id)
				{
					$this->object->chat_room->setRoomId($room_id);
					$this->object->server_comm->setType("delete");
					$this->object->server_comm->send();
					$this->object->chat_room->deleteAllMessages();
				}
				ilUtil::sendInfo($this->lng->txt('chat_refreshed'));
				$this->viewObject();

				return true;
		}	
		
	}

	function emptyRoom()
	{
		global	$rbacsystem;

		if ($rbacsystem->checkAccess("moderate", $this->object->getRefId()) &&
			$this->object->chat_room->checkWriteAccess())
		{
			$this->object->server_comm->setType('delete');
			$message = $this->__formatMessage();
			$this->object->server_comm->setMessage($message);
			$this->object->server_comm->send();

			$this->object->chat_room->deleteAllMessages();
		}
		unset($_GET["room_id_empty"]);
		$this->showFrames();
	}

	function deleteRoom()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_GET["room_id_delete"])
		{
			$this->ilias->raiseError($this->lng->txt("chat_select_one_room"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->chat_room->setOwnerId($_SESSION["AccountId"]);
		$rooms = array($_GET["room_id_delete"]);
		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$delResult = $this->object->chat_room->deleteRooms($rooms, $this->object->chat_room->getOwnerId());
		}
		else
		{
			$delResult = $this->object->chat_room->deleteRooms($rooms);
		}
		if(!$delResult)
		{
			$this->ilias->raiseError($this->object->chat_room->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		unset($_GET["room_id_delete"]);
		ilUtil::sendInfo($this->lng->txt("chat_rooms_deleted"),true);
		$this->showFrames();
	}

	function confirmedDeleteRoomObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_SESSION["room_id_delete"])
		{
			$this->ilias->raiseError($this->lng->txt("chat_select_one_room"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->chat_room->setOwnerId($_SESSION["AccountId"]);
		if(!$this->object->chat_room->deleteRooms($_SESSION["room_id_delete"]))
		{
			$this->ilias->raiseError($this->object->chat_room->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		unset($_SESSION["room_id_delete"]);
		ilUtil::sendInfo($this->lng->txt("chat_rooms_deleted"));

		$this->viewObject();
		return true;
	}

	function addRoom()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$room =& new ilChatRoom($this->object->getId());
		$room->setTitle(ilUtil::stripSlashes($_POST["room_name"]));
		$room->setOwnerId($_SESSION["AccountId"]);

		if(!$room->validate())
		{
			$this->ilias->raiseError($room->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		$room->add();

		#$this->input_message = $this->lng->txt('chat_room_added');
		ilUtil::sendInfo($this->lng->txt("chat_room_added"),true);
		$this->showFrames();
	}

	function addRoomObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$room =& new ilChatRoom($this->object->getId());
		$room->setTitle(ilUtil::stripSlashes($_POST["room_name"]));
		$room->setOwnerId($_SESSION["AccountId"]);

		if(!$room->validate())
		{
			$this->ilias->raiseError($room->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		$room->add();
		ilUtil::sendInfo($this->lng->txt("chat_room_added"));
		$this->viewObject();

		return true;
		#header("location: ".$this->getTargetScript("cmd=gateway&ref_id=".$this->ref_id));
		#exit;
	}

	function renameRoomObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		$room =& new ilChatRoom($this->object->getId());
		$room->setRoomId($_SESSION["room_id_rename"]);
		$room->setTitle(ilUtil::stripSlashes($_POST["room_name"]));
		if(!$room->validate())
		{
			$this->ilias->raiseError($room->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		$room->rename();

		unset($_SESSION["room_id_rename"]);
		ilUtil::sendInfo($this->lng->txt("chat_room_renamed"));
		$this->viewObject();

		return true;
	}		
		


	function adminAddRoomObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.chat_edit_room.html","Modules/Chat");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ROOM_NAME",$this->lng->txt("chat_room_name"));
		$this->tpl->setVariable("ROOM_CANCEL",$this->lng->txt("cancel"));
	
		$this->tpl->setVariable("TXT_EDIT_CHATROOMS",$this->lng->txt("chat_chatroom_rename"));
		$this->tpl->setVariable("ROOM_NAME","");
		$this->tpl->setVariable("CMD","addRoom");
		$this->tpl->setVariable("ROOM_EDIT",$this->lng->txt("add"));

	}	

	function recordingsObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.chat_recordings.html","Modules/Chat");

		$this->object->__initChatRecording();

		if (!is_array($data = $this->object->chat_recording->getRecordings()))
		{
			ilUtil::sendInfo($this->lng->txt('chat_no_recordings_available'));
			return true;
		}

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('chat_recordings'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('chat_recording_description'));
		$this->tpl->setVariable("HEADER_MOD",$this->lng->txt('chat_recording_moderator'));
		$this->tpl->setVariable("HEADER_TIME",$this->lng->txt('chat_recording_time_frame'));
		$this->tpl->setVariable("HEADER_ACTION",$this->lng->txt('chat_recording_action'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));

		$counter = 0;
		for ($i = 0; $i < count($data); $i++)
		{
			$this->tpl->setCurrentBlock("recording_row");
			$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckbox(0,'recordings[]',$data[$i]["record_id"]));
			if($data[$i]["title"] != "")
			{
				$this->tpl->setVariable("RECORDING_TITLE", $data[$i]["title"]);
			}
			if ($data[$i]["description"] != "")
			{
				$this->tpl->setVariable("RECORDING_DESCRIPTION", $data[$i]["description"]);
			}
			if (is_array($moderator = $this->object->chat_recording->getModerator($data[$i]["moderator_id"])))
			{
				$this->tpl->setVariable("MODERATOR", $moderator);
			}
			$this->tpl->setVariable("START_TIME", date("Y-m-d H:i:s", $data[$i]["start_time"]));
			if ($data[$i]["end_time"] > 0)
			{
				$this->tpl->setVariable("END_TIME", date("Y-m-d H:i:s", $data[$i]["end_time"]));
				$this->ctrl->setParameter($this,'record_id',$data[$i]["record_id"]);
				$this->tpl->setVariable("LINK_EXPORT",$this->ctrl->getLinkTarget($this,'exportRecording'));
				$this->tpl->setVariable("TXT_EXPORT",$this->lng->txt('export'));
			}
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
	}

	function askDeleteRecordingsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST['recordings']))
		{
			ilUtil::sendInfo($this->lng->txt('chat_recordings_select_one'));
			$this->recordingsObject();
			
			return false;
		}

		$this->object->__initChatRecording();

		ilUtil::sendInfo($this->lng->txt('chat_recordings_delete_sure'));
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.chat_ask_delete_recordings.html","Modules/Chat");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('chat_recordings'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('chat_recording_description'));
		$this->tpl->setVariable("HEADER_MOD",$this->lng->txt('chat_recording_moderator'));
		$this->tpl->setVariable("HEADER_TIME",$this->lng->txt('chat_recording_time_frame'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		$counter = 0;
		for ($i = 0; $i < count($_POST["recordings"]); $i++)
		{
			$this->object->chat_recording->getRecord($_POST["recordings"][$i]);
			$this->tpl->setCurrentBlock("recordings_row");
			if($this->object->chat_recording->getTitle() != "")
			{
				$this->tpl->setVariable("RECORDING_TITLE", $this->object->chat_recording->getTitle());
			}
			if($this->object->chat_recording->getDescription() != "")
			{
				$this->tpl->setVariable("RECORDING_DESCRIPTION", $this->object->chat_recording->getDescription());
			}
			if ($moderator = $this->object->chat_recording->getModerator())
			{
				$this->tpl->setVariable("MODERATOR", $moderator);
			}
			$this->tpl->setVariable("START_TIME", date("Y-m-d H:i:s", $this->object->chat_recording->getStartTime()));
			if ($this->object->chat_recording->getEndTime() > 0)
			{
				$this->tpl->setVariable("END_TIME", date("Y-m-d H:i:s", $this->object->chat_recording->getEndTime()));
			}
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
		$_SESSION['chat_recordings_del'] = $_POST['recordings'];
	}

	function deleteRecordingsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_SESSION['chat_recordings_del']))
		{
			ilUtil::sendInfo($this->lng->txt('chat_recordings_none_selected'));
			$this->recordingsObject();

			return false;
		}

		$this->object->__initChatRecording();

		foreach($_SESSION['chat_recordings_del'] as $record_id)
		{
			$this->object->chat_recording->delete($record_id);
		}
		ilUtil::sendInfo($this->lng->txt('chat_recordings_deleted'));
		$this->recordingsObject();
		
		unset($_SESSION['chat_recordings_del']);
		return true;
	}

	function exportRecordingObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->__initChatRecording();

		if (!$this->object->chat_recording->getRecord($_GET["record_id"]) ||
			$this->object->chat_recording->getEndTime() == 0)
		{
			ilUtil::sendInfo($this->lng->txt('chat_recording_not_found'));
			$this->recordingsObject();

			return false;
		}

		$tmp_tpl =& new ilTemplate("tpl.chat_export_recording.html",true,true,"Modules/Chat");

		if($this->object->chat_recording->getTitle())
		{
			$tmp_tpl->setVariable("TITLE",$this->object->chat_recording->getTitle());
		}
		$tmp_tpl->setVariable("START_TIME",date("Y-m-d H:i:s", $this->object->chat_recording->getStartTime()));
		$tmp_tpl->setVariable("END_TIME",date("Y-m-d H:i:s", $this->object->chat_recording->getEndTime()));
		$tmp_tpl->setVariable("CONTENT",$this->object->chat_recording->exportMessages());

		ilUtil::deliverData($tmp_tpl->get(), "chat_recording_" . $_GET["record_id"] . ".html");
		exit;
	}

	function startRecording()
	{
		global $rbacsystem,$ilUser;

		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->__initChatRecording();
		if($_GET["room_id"])
		{
			$this->object->chat_recording->setRoomId($_GET["room_id"]);
		}
		if (!$this->object->chat_recording->isRecording())
		{
			$this->object->chat_recording->setModeratorId($ilUser->getId());
			$this->object->chat_recording->startRecording($_POST["title"]);
		}
		ilUtil::sendInfo($this->lng->txt("chat_recording_started"),true);
		$this->showFrames();
	}

	function stopRecording()
	{
		global $rbacsystem,$ilUser;

		if (!$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->__initChatRecording();
		if($_GET["room_id"])
		{
			$this->object->chat_recording->setRoomId($_GET["room_id"]);
		}
		if ($this->object->chat_recording->isRecording())
		{
			$this->object->chat_recording->stopRecording($ilUser->getId());
		}
		ilUtil::sendInfo($this->lng->txt("chat_recording_stopped"),true);
		$this->showFrames();
	}

	function showFrames()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		// LOAD FRAMESET
		$this->tpl = new ilTemplate("tpl.chat_start.html",false,false,'Modules/Chat');
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		if ($_GET["p_id"])
		{
			$this->tpl->setVariable("USER_TARGET","chat.php?cmd=showUserFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&p_id=".$_GET["p_id"]);
			$this->tpl->setVariable("TOP_TARGET","chat.php?cmd=showTopFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&p_id=".$_GET["p_id"]);
			$this->tpl->setVariable("INPUT_TARGET","chat.php?cmd=showInputFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&p_id=".$_GET["p_id"]);
			$this->tpl->setVariable("RIGHT_TARGET","chat.php?cmd=showRightFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&p_id=".$_GET["p_id"]);
		}
		else if ($_GET["a_id"])
		{
			$this->tpl->setVariable("USER_TARGET","chat.php?cmd=showUserFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&pa_id=".$_GET["a_id"]);
			$this->tpl->setVariable("TOP_TARGET","chat.php?cmd=showTopFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&a_id=".$_GET["a_id"]);
			$this->tpl->setVariable("INPUT_TARGET","chat.php?cmd=showInputFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&a_id=".$_GET["a_id"]);
			$this->tpl->setVariable("RIGHT_TARGET","chat.php?cmd=showRightFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&a_id=".$_GET["a_id"]);
		}
		else
		{
			$this->tpl->setVariable("USER_TARGET","chat.php?cmd=showUserFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]);
			$this->tpl->setVariable("TOP_TARGET","chat.php?cmd=showTopFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]);
			$this->tpl->setVariable("INPUT_TARGET","chat.php?cmd=showInputFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]);
			$this->tpl->setVariable("RIGHT_TARGET","chat.php?cmd=showRightFrame&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]);
		}
		$this->tpl->setVariable("SERVER_TARGET",$this->object->server_comm->getServerFrameSource());
	}
	function showUserFrame()
	{
		$this->object->chat_room->setUserId($_SESSION["AccountId"]);
		$this->object->chat_room->updateLastVisit();

		$this->__loadStylesheet(true);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.chat_user_frame.html",'Modules/Chat');

		if($_REQUEST["room_id"])
		{
			$this->tpl->setVariable("TITLE",$this->object->chat_room->getTitle());
		}
		else
		{
			$this->tpl->setVariable("TITLE",$this->object->getTitle());
		}

		$this->__showRooms();

		$this->tpl->setVariable("ADD_FORMACTION","chat.php?cmd=gateway&room_id=".
								$this->object->chat_room->getRoomId()."&ref_id=".$this->object->getRefId());
		$this->tpl->setVariable("TXT_ADD_PRIVATE_CHATROOM", $this->lng->txt("chat_add_private_chatroom"));
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));

		$this->__showActiveUsers();
		if($this->object->chat_room->isOwner())
		{
			$this->__showOnlineUsers();
		}
	}
	function showTopFrame()
	{
		$this->__loadStylesheet();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.chat_top_frame.html",'Modules/Chat');
		//$this->tpl->setVariable("TXT_NEW_MESSAGE",'Current discussion');
	}
	function showInputFrame()
	{
		global $rbacsystem;

		$this->__loadStylesheet();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.chat_input_frame.html",'Modules/Chat');

		if($this->error)
		{
			ilUtil::sendInfo($this->error);
		}
		if($_GET["p_id"])
		{
			$user_obj =& new ilObjUser((int) $_GET["p_id"]);
			$message = $this->lng->txt("chat_private_message")." ";
			$message .= $this->object->chat_user->getLogin()." -> ".$user_obj->getLogin();
			ilUtil::sendInfo($message);
		}
		else if($_GET["a_id"])
		{
			$user_obj =& new ilObjUser((int) $_GET["a_id"]);
			$message = $this->lng->txt("chat_address_user")." ".$user_obj->getLogin();
			ilUtil::sendInfo($message);
		}
		
		if ($_GET["p_id"])
		{
			$this->tpl->setVariable("FORMACTION","chat.php?cmd=gateway&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&p_id=".$_GET["p_id"]);
		}
		else if ($_GET["a_id"])
		{
			$this->tpl->setVariable("FORMACTION","chat.php?cmd=gateway&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]."&a_id=".$_GET["a_id"]);
		}
		else
		{
			$this->tpl->setVariable("FORMACTION","chat.php?cmd=gateway&ref_id=".
									$this->object->getRefId()."&room_id=".
									$_REQUEST["room_id"]);
		}

		$this->tpl->setVariable("TXT_NEW_MESSAGE",$this->lng->txt('chat_new_message'));
		$this->tpl->setVariable("TXT_COLOR",$this->lng->txt("chat_color"));
		$this->tpl->setVariable("TXT_TYPE",$this->lng->txt("chat_type"));
		$this->tpl->setVariable("TXT_FACE",$this->lng->txt("chat_face"));
		$this->tpl->setVariable("TXT_INPUT",$this->lng->txt("chat_input"));

		if ($_GET["p_id"])
		{
			$this->tpl->setCurrentBlock("cancel");
			$this->tpl->setVariable("TXT_SUBMIT_CANCEL",$this->lng->txt("cancel_whisper"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TXT_SUBMIT_OK",$this->lng->txt("ok"));
		}
		elseif($_GET["a_id"])
		{
			$this->tpl->setCurrentBlock("cancel");
			$this->tpl->setVariable("TXT_SUBMIT_CANCEL",$this->lng->txt("cancel_talk"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TXT_SUBMIT_OK",$this->lng->txt("ok"));
		}
		else
		{
			$this->tpl->setVariable("TXT_SUBMIT_OK",$this->lng->txt("ok"));
		}
		$this->tpl->setVariable("TXT_HTML_EXPORT",$this->lng->txt('exp_html'));
		$this->tpl->setVariable("SELECT_COLOR",$this->__getColorSelect());
		$this->tpl->setVariable("RADIO_TYPE",$this->__getFontType());
		$this->tpl->setVariable("CHECK_FACE",$this->__getFontFace());

		if ($rbacsystem->checkAccess("moderate", $this->object->getRefId()))
		{
			$this->object->__initChatRecording();
			$this->tpl->setCurrentBlock("moderator");
			$this->object->chat_recording->setRoomId($this->object->chat_room->getRoomId());
			if ($this->object->chat_recording->isRecording())
			{
				if ($this->object->chat_recording->getTitle() != "")
				{
					$this->tpl->setVariable("TXT_TITLE_STOP_RECORDING", $this->lng->txt("chat_recording_title"));
					$this->tpl->setVariable("VAL_TITLE_STOP_RECORDING", $this->object->chat_recording->getTitle());
				}
				if ($this->object->chat_recording->getDescription() != "")
				{
					$this->tpl->setVariable("TXT_DESCRIPTION_STOP_RECORDING", $this->lng->txt("chat_recording_description"));
					$this->tpl->setVariable("VAL_DESCRIPTION_STOP_RECORDING", $this->object->chat_recording->getDescription());
				}
				$this->tpl->setVariable("TXT_SUBMIT_STOP_RECORDING", $this->lng->txt("chat_stop_recording"));
			}
			else
			{
				$this->tpl->setVariable("TXT_TITLE_START_RECORDING", $this->lng->txt("chat_recording_title"));
				$this->tpl->setVariable("TXT_DESCRIPTION_START_RECORDING", $this->lng->txt("chat_recording_description"));
				$this->tpl->setVariable("TXT_SUBMIT_START_RECORDING", $this->lng->txt("chat_start_recording"));
			}
			$this->tpl->setVariable("MODERATOR_FORMACTION","chat.php?cmd=gateway&ref_id=".
									$this->object->getRefId()."&room_id=".
									$this->object->chat_room->getRoomId());
			$this->tpl->setVariable("TXT_RECORDINGS",$this->lng->txt('chat_recordings'));
			$this->tpl->setVariable("MODERATOR_TARGET","_top");
			$this->tpl->parseCurrentBlock("moderator");
		}
	}

	function showRightFrame()
	{
		$this->__loadStylesheet();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.chat_right_frame.html",'Modules/Chat');
	}

	function cancel()
	{
		unset($_GET["p_id"]);
		unset($_GET["a_id"]);

		$this->showInputFrame();
	}


	function input()
	{
		$this->object->chat_room->setUserId($_SESSION["AccountId"]);
		$this->object->chat_room->updateLastVisit();


		if(!$_POST["message"])
		{
			ilUtil::sendInfo($this->lng->txt("chat_insert_message"),true);

			return $this->showInputFrame();
		}
		if($_POST["message"] and $this->object->chat_room->checkWriteAccess())
		{
			// FORMAT MESSAGE
			$message = $this->__formatMessage();
			
			// SET MESSAGE AND SEND IT
			$this->object->server_comm->setMessage($message);
			if((int) $_GET["p_id"])
			{
				$this->object->server_comm->setType('private');
			}
			else if((int) $_GET["a_id"])
			{
				$this->object->server_comm->setType('address');
			}
			if(!$this->object->server_comm->send())
			{
				$this->error = $this->lng->txt("chat_no_connection");
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('chat_kicked_from_room'),true);
		}
		$this->showInputFrame();
	}

	function invite()
	{
		if($_GET["i_id"])
		{
			$this->object->chat_room->invite((int) $_GET["i_id"]);
			$this->object->sendMessage((int) $_GET["i_id"]);
			if ($this->object->chat_room->getRoomId() > 0)
			{
				ilUtil::sendInfo($this->lng->txt("chat_user_invited_private"),true);
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("chat_user_invited_public"),true);
			}
			$this->showFrames();
		}
	}

	// Direct invitations from personal desktop
	function invitePD()
	{
		global $ilUser;

		if(!$_GET['usr_id'])
		{
			ilUtil::sendInfo($this->lng->txt('chat_no_user_selected',true));
			$this->showFrames();
		}
		// Create room
		$this->object->chat_room->setOwnerId($ilUser->getId());
		$this->object->chat_room->setTitle(ilObjUser::_lookupLogin($ilUser->getId()). 
										   ' : '.
										   ilObjUser::_lookupLogin($_GET['usr_id']));

		// only add room if it doesn't exist
		if(!$id = $this->object->chat_room->lookupRoomId())
		{
			$id = $this->object->chat_room->add();
			ilUtil::sendInfo($this->lng->txt("chat_user_invited_private"),true);
		}
			

		// Send message
		$this->object->chat_room->setRoomId($id);
		$this->object->chat_room->invite((int) $_GET["usr_id"]);
		$this->object->sendMessage((int) $_GET['usr_id']);

		ilUtil::redirect('chat.php?ref_id='.$this->object->getRefId().'&room_id='.$id);
	}




	function drop()
	{
		if($_GET["i_id"])
		{
			$this->object->chat_room->drop((int) $_GET["i_id"]);

			$tmp_user =& new ilObjUser($_GET["i_id"]);
			$this->object->server_comm->setKickedUser($tmp_user->getLogin());
			$this->object->server_comm->setType("kick");
			$this->object->server_comm->send();
			ilUtil::sendInfo($this->lng->txt("chat_user_dropped_private"),true);
			$this->showFrames();
		}
	}
	function closeFrame()
	{
		$this->__loadStylesheet(true);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.chat_close.html",'Modules/Chat');
		ilUtil::sendInfo("Your session is expired please login to ILIAS.");
		$this->tpl->touchBlock("content");
	}

	function export()
	{
		$tmp_tpl =& new ilTemplate("tpl.chat_export.html",true,true,"Modules/Chat");

		if($this->object->chat_room->getRoomId())
		{
			$tmp_tpl->setVariable("CHAT_NAME",$this->object->chat_room->getTitle());
		}
		else
		{
			$tmp_tpl->setVariable("CHAT_NAME",$this->object->getTitle());
		}
		$tmp_tpl->setVariable("CHAT_DATE",strftime("%c",time()));
		$tmp_tpl->setVariable("CONTENT",$this->object->chat_room->getAllMessages());
		ilUtil::deliverData($tmp_tpl->get(),"1.html");
		exit;
	}

	// PRIVATE
	function __showOnlineUsers()
	{
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		$all_users = $this->object->chat_room->getOnlineUsers();
		// filter blocked users 
		$users = array();
		foreach($all_users as $user)
		{
			if(!ilChatBlockedUsers::_isBlocked($this->object->getId(),$user['user_id']))
			{
				$users[] = $user;
			}
		}

		if(count($users) <= 1)
		{
			$this->tpl->setCurrentBlock("no_actice");
			$this->tpl->setVariable("NO_ONLINE_USERS",$this->lng->txt("chat_no_online_users"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$counter = 0;

			foreach($users as $user)
			{
				if($user["user_id"] == $_SESSION["AccountId"])
				{
					continue;
				}

				if ($counter > 0 &&
					$counter == count($users)-2 &&
					($counter%2) == 0)
				{
					$this->tpl->touchBlock("online_empty_col");
				}
	
				if(!($counter%2))
				{
					$this->tpl->touchBlock("online_row_start");
				}
				else
				{
					$this->tpl->touchBlock("online_row_end");
				}

				$this->tpl->setCurrentBlock("online");
				if ($_GET["p_id"] == $user["user_id"] ||
					$_GET["a_id"] == $user["user_id"])
				{
					$this->tpl->setVariable("ONLINE_FONT_A","smallred");
				}
				else
				{
					$this->tpl->setVariable("ONLINE_FONT_A","small");
				}
				
				if($this->object->chat_room->isInvited($user["user_id"]))
				{
					$img = "minus.gif";
					$cmd = "drop";
				}
				else
				{
					$img = "plus.gif";
					$cmd = "invite";
				}
				$this->tpl->setVariable("ONLINE_LINK_A","chat.php?cmd=".$cmd.
										"&ref_id=".$this->ref_id."&room_id=".
										$_REQUEST["room_id"]."&i_id=".$user["user_id"]);
				$this->tpl->setVariable("TXT_INVITE_USER",$cmd == "invite" ? $this->lng->txt("chat_invite_user") :
										$this->lng->txt("chat_disinvite_user"));
        		$this->tpl->setVariable("ONLINE_USER_NAME_A", $user["firstname"]." ".$user["lastname"]." (".$user["login"].")");										
				$this->tpl->setVariable("INVITE_IMG_SRC",ilUtil::getImagePath($img,'Modules/Chat'));
				
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		$this->tpl->setCurrentBlock("show_online");
		$this->tpl->setVariable("ONLINE_USERS",$this->lng->txt("chat_online_users"));
		$this->tpl->parseCurrentBlock();
	}
	function __showActiveUsers()
	{
		global $rbacsystem;

		if(isset($_GET["a_users"]))
		{
			if($_GET["a_users"])
			{
				$_SESSION["a_users"] = true;
			}
			else
			{
				$_SESSION["a_users"] = 0;
				unset($_SESSION["a_users"]);
			}
		}

		$hide = $_SESSION["a_users"] ? true : false;

		$this->tpl->setVariable("ACTIVE_USERS",$this->lng->txt("chat_active_users"));
		$this->tpl->setVariable("DETAILS_B_TXT",$hide ? $this->lng->txt("chat_show_details") : $this->lng->txt("chat_hide_details"));
		$this->tpl->setVariable("DETAILS_B","chat.php?ref_id=".$this->object->getRefId().
								"&room_id=".$this->object->chat_room->getRoomId().
								"&a_users=".($hide ? 0 : 1)."&cmd=showUserFrame");

		if($hide)
		{
			return true;
		}
		$users = $this->object->chat_room->getActiveUsers();
		if(count($users) <= 1)
		{
			$this->tpl->setCurrentBlock("no_actice");
			$this->tpl->setVariable("NO_ACTIVE_USERS",$this->lng->txt("chat_no_active_users"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$user_obj =& new ilObjUser();
			foreach($users as $user)
			{
				if($user == $_SESSION["AccountId"])
				{
					continue;
				}
				$user_obj->setId($user);
				$user_obj->read();

				if($rbacsystem->checkAccess('moderate',$this->object->getRefId()) and !$_REQUEST['room_id'])
				{
					$this->tpl->setCurrentBlock("moderate");
					if($this->object->chat_room->isKicked($user_obj->getId()))
					{
						$this->tpl->setVariable("MOD_INVITE_IMG_SRC",ilUtil::getImagePath('plus.gif','Modules/Chat'));
						$this->tpl->setVariable("MOD_TXT_INVITE_USER",$this->lng->txt('chat_unkick_user_session'));
						$this->tpl->setVariable("MOD_ONLINE_LINK_A","chat.php?cmd=unkickUser&ref_id=".$this->ref_id.
												"&kick_id=".$user_obj->getId());
					}
					else
					{
						$this->tpl->setVariable("MOD_INVITE_IMG_SRC",ilUtil::getImagePath('minus.gif','Modules/Chat'));
						$this->tpl->setVariable("MOD_TXT_INVITE_USER",$this->lng->txt('chat_kick_user_session'));
						$this->tpl->setVariable("MOD_ONLINE_LINK_A","chat.php?cmd=kickUser&ref_id=".$this->ref_id.
												"&kick_id=".$user_obj->getId());
					}

					$this->tpl->setVariable("MOD_ONLINE_USER_NAME_A",$user_obj->getLogin());
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock("non_moderate");
					if ($_GET["p_id"] == $user ||
						$_GET["a_id"] == $user)
					{
						$this->tpl->setVariable("ACTIVE_FONT_A","smallred");
					}
					else
					{
						$this->tpl->setVariable("ACTIVE_FONT_A","small");
					}
					$this->tpl->setVariable("ACTIVE_USER_NAME_A",$user_obj->getLogin());
					$this->tpl->parseCurrentBlock();
				}

				if($user_obj->getPref('public_profile') == 'y')
				{
					$this->tpl->setVariable("ACTIVE_ROW_TXT_PROFILE",$this->lng->txt("chat_profile"));
					$this->tpl->setVariable("ACTIVE_ROW_PROFILE_ID",$user_obj->getId());
				}
				/*
				if(!$_REQUEST['room_id'] and $rbacsystem->checkAccess('moderate',$this->object->getRefId()))
				{
					$this->tpl->setCurrentBlock("kick_user");
					$this->tpl->setVariable("KICK_LINK","chat.php?cmd=kickUser&ref_id=".$this->ref_id."&kick_id=".$user);
					$this->tpl->setVariable("TXT_KICK",$this->lng->txt('chat_kick'));
					$this->tpl->parseCurrentBlock();
				}
				*/
				$this->tpl->setCurrentBlock("active");

				$this->tpl->setVariable("ACTIVE_ADDRESS_A","chat.php?cmd=showInputFrame&".
										"ref_id=".$this->ref_id."&room_id=".
										$_REQUEST["room_id"]."&a_id=".$user);
				$this->tpl->setVariable("ACTIVE_TXT_ADDRESS_A",$this->lng->txt("chat_address"));

				$this->tpl->setVariable("ACTIVE_LINK_A","chat.php?cmd=showInputFrame&".
										"ref_id=".$this->ref_id."&room_id=".
										$_REQUEST["room_id"]."&p_id=".$user);
				$this->tpl->setVariable("ACTIVE_TXT_WHISPER_A",$this->lng->txt("chat_whisper"));

				$this->tpl->parseCurrentBlock();
			}
		}
	}
	function __showAdminAddRoomSelect()
	{
		$opt = array("createRoom" => $this->lng->txt("chat_room_select"));

		return ilUtil::formSelect("","action_b",$opt,false,true);
	}

	function __showAdminRoomSelect()
	{
		global $rbacsystem;

		$opt["exportRoom"] = $this->lng->txt("chat_html_export");

		if($rbacsystem->checkAccess('write',$this->object->getRefId()) or
		   count($this->object->chat_room->getRoomsOfObject()))
		{
			$opt["refreshRoom"] = $this->lng->txt("chat_refresh");
		}
		
		if(count($this->object->chat_room->getRoomsOfObject()))
		{
			$opt["renameRoom"] = $this->lng->txt("rename");
			$opt["deleteRoom"] = $this->lng->txt("delete");
		}
		return ilUtil::formSelect(isset($_SESSION["room_id_delete"]) ? "deleteRoom" : "",
								  "action",
								  $opt,
								  false,
								  true);
	}

	function __showRooms()
	{
		include_once 'Modules/Chat/classes/class.ilChatBlockedUsers.php';

		global $rbacsystem,$ilUser;

		$public_rooms = $this->object->chat_room->getAllRooms();
		$private_rooms = $this->object->chat_room->getRooms();

		if(isset($_GET["h_rooms"]))
		{
			if($_GET["h_rooms"])
			{
				$_SESSION["h_rooms"] = true;
			}
			else
			{
				$_SESSION["h_rooms"] = 0;
				unset($_SESSION["h_rooms"]);
			}
		}
		$hide = $_SESSION["h_rooms"] ? true : false;

		$this->tpl->setVariable("ROOMS_ROOMS",$this->lng->txt("chat_rooms"));
		$this->tpl->setVariable("DETAILS_TXT",$hide ? $this->lng->txt("chat_show_details") : $this->lng->txt("chat_hide_details"));
		$this->tpl->setVariable("ROOMS_COUNT",count($public_rooms) + count($private_rooms));
		$this->tpl->setVariable("DETAILS_A","chat.php?ref_id=".$this->object->getRefId().
								"&room_id=".$this->object->chat_room->getRoomId().
								"&h_rooms=".($hide ? 0 : 1)."&cmd=showUserFrame");

		if($hide)
		{
			return true;
		}

		$this->object->__initChatRecording();

		$user_obj =& new ilObjUser();
		foreach($public_rooms as $room)
		{
			$tblrow = ($room['child'] == $this->object->getRefId()) ? 'tblrowmarked' : 'tblrow1';

			if(ilChatBlockedUsers::_isBlocked($room['obj_id'],$ilUser->getId()))
			{
				continue;
			}

			$this->tpl->setCurrentBlock("room_row");
			$this->tpl->setVariable("ROOM_ROW_CSS",$tblrow);
			$this->tpl->setVariable("ROOM_LINK","chat.php?ref_id=".$room["child"]);
			$this->tpl->setVariable("ROOM_TARGET","_top");
			$this->tpl->setVariable("ROOM_NAME",$room["title"]);
			$this->tpl->setVariable("ROOM_ONLINE",$this->object->chat_room->getCountActiveUser($room["obj_id"],0));
			$this->object->chat_recording->setObjId($room["obj_id"]);
			if ($room["child"] == $this->object->getRefId() &&
				$this->object->chat_room->getRoomId() == 0 &&
				$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
			{
				$this->tpl->setVariable("TXT_EMPTY_ROOM", $this->lng->txt("chat_empty"));
				$this->tpl->setVariable("LINK_EMPTY_ROOM", "chat.php?cmd=emptyRoom&ref_id=".$room["child"]);
			}

			$this->object->chat_recording->setObjId($room["obj_id"]);
			$this->object->chat_recording->setRoomId(0);
			if ($this->object->chat_recording->isRecording())
			{
				$this->tpl->setVariable("TXT_RECORDING", $this->lng->txt("chat_recording_running"));
			}
			$this->tpl->parseCurrentBlock();

			reset($private_rooms);
			foreach($private_rooms as $priv_room)
			{
				if ($priv_room["chat_id"] == $room["obj_id"])
				{
					$this->tpl->touchBlock("room_row_indent");
					$this->tpl->setCurrentBlock("room_row");
					$this->tpl->setVariable("ROOM_ROW_CSS",$tblrow);
					$this->tpl->setVariable("ROOM_LINK","chat.php?ref_id=".$room["child"].
																				 "&room_id=".$priv_room["room_id"]);
					$this->tpl->setVariable("ROOM_TARGET","_top");
					$this->tpl->setVariable("ROOM_NAME",$priv_room["title"]);
					$this->tpl->setVariable("ROOM_ONLINE",
											$this->object->chat_room->getCountActiveUser($priv_room["chat_id"],$priv_room["room_id"])); 
		
					if ($priv_room["owner"] != $_SESSION["AccountId"])
					{
						if($user_obj =& ilObjectFactory::getInstanceByObjId($priv_room['owner'],false))
						{
							$this->tpl->setVariable("TXT_ROOM_INVITATION", $this->lng->txt("chat_invited_by"));
							$this->tpl->setVariable("ROOM_INVITATION_USER", $user_obj->getLogin());
						}
					}
					else
					{
						$this->tpl->setVariable("TXT_DELETE_ROOM", $this->lng->txt("delete"));
						$this->tpl->setVariable("LINK_DELETE_ROOM", "chat.php?cmd=deleteRoom&ref_id=".
												$this->object->getRefId()."&room_id=".$this->object->chat_room->getRoomId().
												"&room_id_delete=".$priv_room["room_id"]);
					}
		
					$this->object->chat_recording->setObjId($priv_room["chat_id"]);
					$this->object->chat_recording->setRoomId($priv_room["room_id"]);
					if ($this->object->chat_recording->isRecording())
					{
						$this->tpl->setVariable("TXT_RECORDING", $this->lng->txt("chat_recording_running"));
					}
		
					if ($priv_room["chat_id"] == $this->object->getRefId() &&
						$priv_room["room_id"] == $this->object->chat_room->getRoomId() &&
						$rbacsystem->checkAccess("moderate", $this->object->getRefId()))
					{
						$this->tpl->setVariable("TXT_EMPTY_ROOM", $this->lng->txt("chat_empty"));
						$this->tpl->setVariable("LINK_EMPTY_ROOM", 
												"chat.php?cmd=emptyRoom&ref_id=".$this->object->getRefId().
												"&room_id=".$this->object->chat_room->getRoomId());
					}
		
					$this->tpl->parseCurrentBlock();
				}
			}
			
		}

	}
	function __loadStylesheet($expires = false)
	{
		$this->tpl->setCurrentBlock("ChatStyle");
		$this->tpl->setVariable("LOCATION_CHAT_STYLESHEET",ilUtil::getStyleSheetLocation());
		if($expires)
		{
			$this->tpl->setVariable("EXPIRES","<meta http-equiv=\"expires\" content=\"now\">".
									"<meta http-equiv=\"refresh\" content=\"30\">");
		}
		$this->tpl->parseCurrentBlock();
	}

	function __getColorSelect()
	{
		$colors = array("black" => $this->lng->txt("chat_black"),
						"red" => $this->lng->txt("chat_red"),
						"green" => $this->lng->txt("chat_green"),
						"maroon" => $this->lng->txt("chat_maroon"),
						"olive" => $this->lng->txt("chat_olive"),
						"navy" => $this->lng->txt("chat_navy"),
						"purple" => $this->lng->txt("chat_purple"),
						"teal" => $this->lng->txt("chat_teal"),
						"silver" => $this->lng->txt("chat_silver"),
						"gray" => $this->lng->txt("chat_gray"),
						"lime" => $this->lng->txt("chat_lime"),
						"yellow" => $this->lng->txt("chat_yellow"),
						"fuchsia" => $this->lng->txt("chat_fuchsia"),
						"aqua" => $this->lng->txt("chat_aqua"),
						"blue" => $this->lng->txt("chat_blue"));

		return ilUtil::formSelect($_POST["color"],"color",$colors,false,true);
	}

	function __getFontType()
	{
		$types = array("times" => $this->lng->txt("chat_times"),
					   "tahoma" => $this->lng->txt("chat_tahoma"),
					   "arial" => $this->lng->txt("chat_arial"));

		$_POST["type"] = $_POST["type"] ? $_POST["type"] : "times";

		foreach($types as $name => $type)
		{
			$this->tpl->setCurrentBlock("FONT_TYPES");
			$this->tpl->setVariable("BL_TXT_TYPE",$type);
			$this->tpl->setVariable("FONT_TYPE",$name);
			$this->tpl->setVariable("TYPE_CHECKED",$_POST["type"] == $name ? "checked=\"checked\"" : "");
			$this->tpl->parseCurrentBlock();
		}
	}

	function __getFontFace()
	{
		$_POST["face"] = is_array($_POST["face"]) ? $_POST["face"] : array();

		$types = array("bold" => $this->lng->txt("chat_bold"),
					   "italic" => $this->lng->txt("chat_italic"),
					   "underlined" => $this->lng->txt("chat_underlined"));

		$this->tpl->setCurrentBlock("FONT_FACES");
		$this->tpl->setVariable("BL_TXT_FACE","<b>".$this->lng->txt("chat_bold")."</b>");
		$this->tpl->setVariable("FONT_FACE","bold");
		$this->tpl->setVariable("FACE_CHECKED",in_array("bold",$_POST["face"]) ? "checked=\"checked\"" : "");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("FONT_FACES");
		$this->tpl->setVariable("BL_TXT_FACE","<i>".$this->lng->txt("chat_italic")."</i>");
		$this->tpl->setVariable("FONT_FACE","italic");
		$this->tpl->setVariable("FACE_CHECKED",in_array("italic",$_POST["face"]) ? "checked=\"checked\"" : "");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("FONT_FACES");
		$this->tpl->setVariable("BL_TXT_FACE","<u>".$this->lng->txt("chat_underlined")."</u>");
		$this->tpl->setVariable("FONT_FACE","underlined");
		$this->tpl->setVariable("FACE_CHECKED",in_array("underlined",$_POST["face"]) ? "checked=\"checked\"" : "");
		$this->tpl->parseCurrentBlock();
	}

	function __formatMessage()
	{
		$tpl = new ilTemplate("tpl.chat_message.html",true,true,'Modules/Chat');

		$_POST['message'] = htmlentities(trim($_POST['message']),ENT_QUOTES,'utf-8');
		$_POST['message'] = ilUtil::stripSlashes($_POST['message']);

		$tpl->setVariable("MESSAGE",$_POST["message"]);
		$tpl->setVariable("FONT_COLOR",$_POST["color"]);
		$tpl->setVariable("FONT_FACE",$_POST["type"]);

		if($_GET["p_id"])
		{
			$user_obj =& new ilObjUser((int) $_SESSION["AccountId"]);
			$user_obj->read();

			$tpl->setCurrentBlock("private");
			$tpl->setVariable("PRIVATE_U_COLOR","red");
			$tpl->setVariable("PRIVATE_FROM",$user_obj->getLogin());

			$user_obj =& new ilObjUser((int) $_GET["p_id"]);
			$user_obj->read();
			$tpl->setVariable("PRIVATE_TO",$user_obj->getLogin());
			$tpl->parseCurrentBlock();
		}
		else if($_GET["a_id"])
		{
			$tpl->setCurrentBlock("address");
			$tpl->setVariable("ADDRESS_FROM_COLOR","navy");
			$user_obj =& new ilObjUser((int) $_SESSION["AccountId"]);
			$user_obj->read();
			$tpl->setVariable("ADDRESS_FROM",$user_obj->getLogin());

			$tpl->setVariable("ADDRESS_TO_COLOR","red");
			$user_obj =& new ilObjUser((int) $_GET["a_id"]);
			$user_obj->read();
			$tpl->setVariable("ADDRESS_TO",$user_obj->getLogin());
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("normal");
			$tpl->setVariable("NORMAL_U_COLOR","navy");
			$tpl->setVariable("NORMAL_UNAME",$this->object->chat_user->getLogin());
			$tpl->parseCurrentBlock();
		}
		// OPEN TAGS
		if($_POST["face"])
		{
			foreach($_POST["face"] as $face)
			{
				$tpl->setCurrentBlock("type_open");
				switch($face)
				{
					case "bold":
						$tpl->setVariable("TYPE_TYPE_O","b");
						break;
					case "italic":
						$tpl->setVariable("TYPE_TYPE_O","i");
						break;

					case "underlined":
						$tpl->setVariable("TYPE_TYPE_O","u");
						break;
				}
				$tpl->parseCurrentBlock();
			}
			$_POST["face"] = array_reverse($_POST["face"]);
			foreach($_POST["face"] as $face)
			{
				$tpl->setCurrentBlock("type_close");
				switch($face)
				{
					case "bold":
						$tpl->setVariable("TYPE_TYPE_C","b");
						break;
					case "italic":
						$tpl->setVariable("TYPE_TYPE_C","i");
						break;

					case "underlined":
						$tpl->setVariable("TYPE_TYPE_C","u");
						break;
				}
				$tpl->parseCurrentBlock();
			}
		}

		$message = preg_replace("/\r/","",$tpl->get());
		$message = preg_replace("/\n/","",$message);

		return $message;
	}
	function __exportRooms()
	{
		include_once "Modules/Chat/classes/class.ilFileDataChat.php";

		if(count($_POST["del_id"]) == 1)
		{
			$this->object->chat_room->setRoomId($_POST["del_id"][0]);
			$this->export();
		}

		$file_obj =& new ilFileDataChat($this->object);

		foreach($_POST["del_id"] as $id)
		{
			$this->object->chat_room->setRoomId((int) $id);

			$tmp_tpl =& new ilTemplate("tpl.chat_export.html",true,true,"Modules/Chat");
			
			if($id)
			{
				$tmp_tpl->setVariable("CHAT_NAME",$this->object->chat_room->getTitle());
			}
			else
			{
				$tmp_tpl->setVariable("CHAT_NAME",$this->object->getTitle());
			}
			$tmp_tpl->setVariable("CHAT_DATE",strftime("%c",time()));
			$tmp_tpl->setVariable("CONTENT",$this->object->chat_room->getAllMessages());

			$file_obj->addFile("chat_".$this->object->chat_room->getRoomId().".html",$tmp_tpl->get());
		}
		$fname = $file_obj->zip();
		ilUtil::deliverFile($fname,"ilias_chat.zip");
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

//echo "-".$this->ctrl->getCmd()."-";

		if($rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "view")
				? true
				: false;
			$tabs_gui->addTarget("chat_rooms",
				$this->ctrl->getLinkTarget($this, "view"), array("view", ""), get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("chat_recordings",
								 $this->ctrl->getLinkTarget($this, "recordings"), "recordings", get_class($this));
		}
		if($rbacsystem->checkAccess('moderate',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("chat_blocked_users",
				$this->ctrl->getLinkTarget($this, "blockedUsers"),
				array("blockedUsers", "unBlockUsers", "blockUser"), get_class($this));
		}

		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}



	function __prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		$this->__setLocator();

		// output message
		if ($this->message)
		{
			ilUtil::sendInfo($this->message);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		include_once './classes/class.ilTabsGUI.php';

		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_chat_b.gif"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("HEADER",$this->object->getTitle());
		$this->tpl->setVariable("H_DESCRIPTION",$this->object->getDescription());

		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);

		// output tabs
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}

	function __setLocator()
	{
		global $tree;
		global $ilias_locator;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$counter = 0;
		foreach ($tree->getPathFull($this->object->getRefId()) as $key => $row)
		{
			if($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			$this->tpl->setCurrentBlock("locator_item");

			if($row["type"] == 'chat')
			{
				$this->tpl->setVariable("ITEM",$this->object->getTitle());
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTarget($this));
			}
			elseif ($row["child"] != $tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM","repository.php?ref_id=".$row["child"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM","repository.php?ref_id=".$row["child"]);
			}

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
	
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "view";
			$_GET["baseClass"] = "ilChatHandlerGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

}
// END class.ilObjChatGUI
?>
