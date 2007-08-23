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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for Personal Desktop Users Online block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilUsersOnlineBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilUsersOnlineBlockGUI: ilObjUserGUI,
*/
class ilUsersOnlineBlockGUI extends ilBlockGUI
{
	static $block_type = "pdusers";
	
	/**
	* Constructor
	*/
	function ilUsersOnlineBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI($a_parent_class, $a_parent_cmd);
		
		$this->setLimit(10);
		$this->setImage(ilUtil::getImagePath("icon_grp_s.gif"));
		$this->setTitle($lng->txt("users_online"));
		$this->setAvailableDetailLevels(3);
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	* Is block used in repository object?
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}

	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $ilCtrl;

		switch($ilCtrl->getCmd())
		{
			case "showUserProfile":
				return IL_SCREEN_CENTER;
				break;
				
			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");
		
		switch($next_class)
		{
			// profile
			case "ilobjusergui":
				include_once('./Services/User/classes/class.ilObjUserGUI.php');
				$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
				$return = $ilCtrl->forwardCommand($user_gui);
				break;
				
			default:
				return $this->$cmd();
		}
	}

	function getHTML()
	{
		global $ilUser;
		
		$this->users_online_pref = $ilUser->getPref("show_users_online");
		
		if ($this->users_online_pref != "y" && $this->users_online_pref != "associated")
		{
			return "";
		}
		
		$this->getUsers();
		
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}
		else
		{
			return parent::getHTML();
		}
	}
	
	/**
	* Get online users
	*/
	function getUsers()
	{
		global $ilUser;
		
		if ($this->users_online_pref == "associated")
		{
			$this->users = ilUtil::getAssociatedUsersOnline($ilUser->getId());
		}
		else
		{
			$this->users = ilUtil::getUsersOnline();
		}
		
		$this->num_users = 0;
		
		$this->users[$ilUser->getId()] =
			array("user_id" => $ilUser->getId(),
				"firstname" => $ilUser->getFirstname(),
				"lastname" => $ilUser->getLastname(),
				"title" => $ilUser->getUTitle(),
				"login" => $ilUser->getLogin());

		foreach ($this->users as $user_id => $user)
		{
			if ($user_id != ANONYMOUS_USER_ID)
			{
				$this->num_users++;
			}
			else
			{
				$this->visitors = $user["num"];
			}
		}
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilUser, $ilSetting, $ilCtrl;
		
		$pd_set = new ilSetting("pd");
		
		include_once("Services/Notes/classes/class.ilNote.php");
		
		if ($this->getCurrentDetailLevel() > 1 && $this->num_users > 0)
		{
			$this->setRowTemplate("tpl.users_online_row.html", "Services/PersonalDesktop");
			$this->getListRowData();
			if ($this->getCurrentDetailLevel() > 2)
			{
				$this->setColSpan(2);
			}
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			$this->setDataSection($this->getOverview());
		}
	}
	

	/**
	* Get list data.
	*/
	function getListRowData()
	{
		global $ilUser, $lng, $ilCtrl, $ilDB, $rbacsystem;

		$data = array();
		
		$mail = new ilMail($ilUser->getId());
		$mail_settings_id = $mail->getMailObjectReferenceId();
		
		foreach ($this->users as $user_id => $user)
		{
			if ($user_id != ANONYMOUS_USER_ID &&
				ilObjUser::_lookupPref($user_id, "hide_own_online_status") != "y")
			{
				// hide mail-to icon for anonymous users
				// usability: we do show mail-to for the current user, because
				//            we often got requests by users that their own
				//            e-mail address doesn't appear 
				$mail_to = "";
				if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
				{
					// No mail for users that do have permissions to use the mail system
					if($rbacsystem->checkAccess('mail_visible',$mail_settings_id) and
					   $rbacsystem->checkAccessOfUser($user_id,'mail_visible',$mail_settings_id))
					{
						$mail_to = ilMail::_getUserInternalMailboxAddress(
							$user_id, $user['login'], $user['firstname'], $user['lastname']
						);
					}
				}
				
				// check for profile
				// todo: use user class!
				$q = "SELECT value FROM usr_pref WHERE usr_id = ".
					$ilDB->quote($user_id)." AND keyword='public_profile' AND value='y'";
				$r = $ilDB->query($q);
				$profile = false;
				if ($r->numRows())
				{
					$profile = true;
				}
											
				$data[] = array(
					"mail_to" => $mail_to,
					"id" => $user_id,
					"profile" => $profile,
					"login" => $user["login"]
					);
			}
		}
		
		$this->setData($data);
		
		// we do not have at least one (non hidden) active user
		if (count($data) == 0)
		{
			$this->setEnableNumInfo(false);
			$this->setCurrentDetailLevel(1);
			$this->enabledetailrow = false;
			$this->setDataSection($this->getOverview());
		}
	}
	
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($a_set)
	{
		global $ilUser, $ilCtrl, $lng, $ilSetting;
		
		$user_obj = new ilObjUser($a_set["id"]);
		
		// user image
		if ($this->getCurrentDetailLevel() > 2)
		{
			if ($a_set["mail_to"] != "")
			{
				$this->tpl->setCurrentBlock("mailto_link");
				$this->tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
				$this->tpl->setVariable("MAIL_USR_LOGIN", urlencode($a_set["mail_to"]));
				$this->tpl->parseCurrentBlock();
			}
	
			include_once './Modules/Chat/classes/class.ilChatServerConfig.php';
			if(ilChatServerConfig::_isActive())
			{
				if(!$this->__showActiveChatsOfUser($a_set["id"]))
				{
					// Show invite to chat
					$this->__showChatInvitation($a_set["id"]);
				}
			}
			
			// user image
			$this->tpl->setCurrentBlock("usr_image");
			$this->tpl->setVariable("USR_IMAGE",
				$user_obj->getPersonalPicturePath("xxsmall"));
			$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
			$this->tpl->parseCurrentBlock();
		
			$pd_set = new ilSetting("pd");
			$osi_server = $pd_set->get("osi_host");
			
			if (trim($osi_server) != "")
			{
				// instant messengers
				// 1 indicates to use online status check
				$im_arr = array("icq" => 1,
								"yahoo" => 1,
								"msn" => 0,
								"aim" => 0,
								"skype" => 1);
											
				// use onlinestatus.org
				// when enabled all instant messengers are checked online and ignores settings above
				$osi_enable = true;
		
				foreach ($im_arr as $im_name => $im_check)
				{
					if ($im_id = $user_obj->getInstantMessengerId($im_name))
					{
						switch ($im_name)
						{
							case "icq":
								//$im_url = "http://people.icq.com/people/webmsg.php?to=".$im_id;
								$im_url = "http://people.icq.com/people/about_me.php?uin=".$im_id;
								$im_img = "http://status.icq.com/online.gif?icq=".$im_id."&img=5";
								break;
							
							case "yahoo":
								$im_url = "http://edit.yahoo.com/config/send_webmesg?.target=".$im_id."&.src=pg";
								$im_img = "http://opi.yahoo.com/online?u=".$im_id."&m=g&t=5";
								break;
								
							case "msn":
								$im_url = "http://messenger.live.com";
								$im_img = ilUtil::getImagePath($im_name.'offline.gif'); // online check not possible
		
								break;
		
							case "aim":
								//$im_url = "aim:GoIM?screenname=".$im_id;
								$im_url = "http://aimexpress.aim.com";
								//$im_img = "http://api.oscar.aol.com/SOA/key=<put_your_key_here>/presence/".$im_id; // doesn't work. you need a key
								$im_img = ilUtil::getImagePath($im_name.'offline.gif'); // online check not possible
								break;
		
							case "skype":
								$im_url = "skype:".$im_id."?call";
								/* the link above needs this piece of js to work
								<script type="text/javascript" 
								src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js">
								</script>
								*/
								//$im_url = "http://www.skype.com/go/download";
								$im_img = "http://mystatus.skype.com/smallicon/".$im_id;
								break;
						}
		
						$this->tpl->setCurrentBlock("instant_messengers");
						
						if ($osi_enable)
						{
							$this->tpl->setVariable("URL_IM",$osi_server."/message/".$im_name."/".$im_id);
							$this->tpl->setVariable("IMG_IM_ICON",$osi_server."/".$im_name."/".$im_id);
						}
						else
						{
							$this->tpl->setVariable("URL_IM",$im_url);
							$this->tpl->setVariable("IMG_IM_ICON", $im_check ? $im_img : ilUtil::getImagePath($im_name.'offline.gif'));
						}
						
						$this->tpl->setVariable("TXT_IM_ICON", $lng->txt("im_".$im_name));
						$this->tpl->parseCurrentBlock();
					}
				}
			}
		}
					
		// username
		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setVariable("USR_LOGIN", "<br />".$a_set["login"]);
		}
		else
		{
			$this->tpl->setVariable("USR_LOGIN", " [".$a_set["login"]."]");
		}
		
		// profile link
		if ($a_set["profile"])
		{
			$this->tpl->setCurrentBlock("profile_link");
			$this->tpl->setVariable("TXT_VIEW", $lng->txt("profile"));
			$ilCtrl->setParameter($this, "user", $a_set["id"]);
			$this->tpl->setVariable("LINK_PROFILE",
			$ilCtrl->getLinkTarget($this, "showUserProfile"));
			$this->tpl->setVariable("USR_ID", $a_set["id"]);
			$this->tpl->setVariable("LINK_FULLNAME", $user_obj->getFullname());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("USR_FULLNAME", $user_obj->getFullname());
		}
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
		
		// parse visitors text
		if (empty($this->visitors) || $this->users_online_pref == "associated")
		{
			$visitor_text = "";
		}
		elseif ($this->visitors == "1")
		{
			$visitor_text = "1 ".$lng->txt("visitor");
		}
		else
		{
			$visitor_text = $visitors." ".$lng->txt("visitors");
		}
		
		// parse registered users text
		if ($this->num_users > 0)
		{
			$user_kind = ($this->users_online_pref == "associated") ? "associated_user" : "registered_user";
			if ($this->num_users == 1)
			{
				$user_list = $this->num_users." ".$lng->txt($user_kind);
			}
			
			else
			{
				$user_list = $this->num_users." ".$lng->txt($user_kind."s");
			}
						
			if (!empty($visitor_text))
			{
				$user_list .= " ".$lng->txt("and")." ".$visitor_text;
			}
		}
		else
		{
			$user_list = $visitor_text;
		}
		
		return '<div class="small">'.$user_list."</div>";
	}

	function __showActiveChatsOfUser($a_usr_id)
	{
		global $rbacsystem, $lng;
		
		// show chat info
		include_once './Modules/Chat/classes/class.ilChatRoom.php';
		
		$chat_id = ilChatRoom::_isActive($a_usr_id);
		foreach(ilObject::_getAllReferences($chat_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('read',$ref_id))
			{
				$this->tpl->setCurrentBlock("chat_info");
				$this->tpl->setVariable("CHAT_ACTIVE_IN",$lng->txt('chat_active_in'));
				$this->tpl->setVariable("CHAT_LINK","./chat.php?ref_id=".$ref_id."&room_id=0");
				$this->tpl->setVariable("CHAT_TITLE",ilObject::_lookupTitle($chat_id));
				$this->tpl->parseCurrentBlock();
				
				return true;
			}
		}
		return false;
	}
	
	function __showChatInvitation($a_usr_id)
	{
		global $rbacsystem,$ilUser,$lng;
		
		include_once './Modules/Chat/classes/class.ilObjChat.php';
		
		if($a_usr_id == $ilUser->getId())
		{
			return false;
		}
		
		if($rbacsystem->checkAccess('read',ilObjChat::_getPublicChatRefId())
		and $rbacsystem->checkAccessOfUser($a_usr_id,'read',ilObjChat::_getPublicChatRefId()))
		{
			$this->tpl->setCurrentBlock("chat_link");
			$this->tpl->setVariable("TXT_CHAT_INVITE",$lng->txt('chat_invite'));
			$this->tpl->setVariable("CHAT_LINK",'./chat.php?ref_id='.ilObjChat::_getPublicChatRefId().
			'&usr_id='.$a_usr_id.'&cmd=invitePD');
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		return false;
	}
	
	/**
	* show profile of other user
	*/
	function showUserProfile()
	{
		global $lng, $ilCtrl;
		include_once('./Services/User/classes/class.ilObjUserGUI.php');
		$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI("ilpersonaldesktopgui", "show");
		$content_block->setContent($user_gui->getPublicProfile("", false, true));
		$content_block->setTitle($lng->txt("profile_of")." ".
			$user_gui->object->getLogin());
		$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_usr.gif"));
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("selected_items_back"));
		
		return $content_block->getHTML();
	}

}

?>
