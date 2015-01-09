<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");
require_once 'Services/Mail/classes/class.ilMailFormCall.php';
include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';

/**
* BlockGUI class for Personal Desktop Users Online block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilUsersOnlineBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilUsersOnlineBlockGUI: ilPublicUserProfileGUI
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
		
		parent::ilBlockGUI();
		
		$this->setLimit(10);
		$this->setTitle($lng->txt("users_online"));
		$this->setAvailableDetailLevels(3);

        // mjansen: Used for mail referer link (@see fillRow). I don't want to create a new instance in each fillRow call.
        $this->topGuiObj = new ilPersonalDesktopGUI();
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

		if ($ilCtrl->getCmdClass() == "ilpublicuserprofilegui")
		{
			return IL_SCREEN_FULL;
		}

		switch($ilCtrl->getCmd())
		{
			case "showUserProfile":
				return IL_SCREEN_FULL;
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
		global $ilCtrl, $tpl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");
		
		switch($next_class)
		{
			// profile
			case "ilpublicuserprofilegui":
				include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$profile_gui->setBackUrl($ilCtrl->getParentReturn($this));
				return $ilCtrl->forwardCommand($profile_gui);
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
			$this->users = ilUtil::getAssociatedUsersOnline($ilUser->getId(), true);
		}
		else
		{
			$this->users = ilObjUser::_getUsersOnline(0, true);
		}
		
		$this->num_users = 0;
		
		// add current user always to list
		if ($ilUser->getId() != ANONYMOUS_USER_ID &&
			ilObjUser::_lookupPref($ilUser->getId(), "hide_own_online_status") != "y")
		{
			$this->users[$ilUser->getId()] =
				array("user_id" => $ilUser->getId(),
					"firstname" => $ilUser->getFirstname(),
					"lastname" => $ilUser->getLastname(),
					"title" => $ilUser->getUTitle(),
					"login" => $ilUser->getLogin());
		}

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
		
		$this->mail_allowed = ($ilUser->getId() != ANONYMOUS_USER_ID &&
			                   $rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()));
		
		foreach ($this->users as $user_id => $user)
		{
			$data[] = array(
				"id" => $user_id,
				"login" => $user["login"]
				);
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
		global $ilUser, $ilCtrl, $lng, $ilSetting, $rbacsetting, $rbacsystem;
		
		// mail link
		$a_set["mail_to"] = "";
		if($this->mail_allowed &&
		   $rbacsystem->checkAccessOfUser($a_set['id'],'internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			$a_set['mail_url'] = ilMailFormCall::getLinkTarget($this->topGuiObj, '', array(), array('type' => 'new', 'rcp_to' => urlencode($a_set['login'])));
		}
		
		// check for profile
		$a_set["profile"] = in_array(
			ilObjUser::_lookupPref($a_set["id"], "public_profile"),
			array("y", "g"));

		// user image
		if ($this->getCurrentDetailLevel() > 2)
		{
			if ($a_set["mail_url"] != "")
			{
				$this->tpl->setCurrentBlock("mailto_link");
				$this->tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
				$this->tpl->setVariable("MAIL_URL", $a_set["mail_url"]);
				$this->tpl->parseCurrentBlock();
			}
	
			$chatSettings = new ilSetting('chatroom');
			if(/*ilChatServerConfig::_isActive() && */$chatSettings->get('chat_enabled'))
			{
				if(!$this->__showActiveChatsOfUser($a_set["id"]))
				{
					// Show invite to chat
					$this->__showChatInvitation($a_set["id"]);
				}
				
				global $rbacsystem;
				
				include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';
				if($a_set["id"] == $ilUser->getId() &&
				   //$rbacsystem->checkAccess('read', ilObjChat::_getPublicChatRefId()))
					$rbacsystem->checkAccess('read', ilObjChatroom::_getPublicRefId()))
				{
					$this->tpl->setCurrentBlock('chat_link');
					$this->tpl->setVariable('TXT_CHAT_INVITE', $lng->txt('chat_enter_public_room'));
					$this->tpl->setVariable('TXT_CHAT_INVITE_TOOLTIP', $lng->txt('chat_enter_public_room_tooltip'));
//					$this->tpl->setVariable('CHAT_LINK','./ilias.php?baseClass=ilChatPresentationGUI&ref_id='.ilObjChat::_getPublicChatRefId());
					$this->tpl->setVariable('CHAT_LINK','./ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id='.ilObjChatroom::_getPublicRefId());
					$this->tpl->parseCurrentBlock();
				}
			}
			
			// user image
			$this->tpl->setCurrentBlock("usr_image");
			$this->tpl->setVariable("USR_IMAGE",
				ilObjUser::_getPersonalPicturePath($a_set["id"],"xxsmall"));
			$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("usr_image_space");
		
			$pd_set = new ilSetting("pd");
			$osi_server = $pd_set->get("osi_host");
			
//			if (trim($osi_server) != "")
//			{
				// instant messengers
				// 1 indicates to use online status check
				$im_arr = array("icq" => 0,
								"yahoo" => 1,
								"msn" => 0,
								"aim" => 0,
								"skype" => 1,
								"jabber" => 0,
								"voip" => 0);
											
				// use onlinestatus.org
				// when enabled all instant messengers are checked online and ignores settings above
				if (trim($osi_server) != "")
				{
					$osi_enable = true;
				}

				// removed calls to external servers due to
				// bug 10583, alex 8 Mar 2013
				
				foreach ($im_arr as $im_name => $im_check)
				{
					if ($im_id = ilObjUser::_lookupIm($a_set["id"], $im_name))
					{
						$im_url = "";

						switch ($im_name)
						{
							case "icq":
								$im_url = "http://http://www.icq.com/people/".$im_id;
								break;
							
							case "yahoo":
								$im_url = "http://edit.yahoo.com/config/send_webmesg?target=".$im_id."&src=pg";
								break;
								
							case "msn":
								$im_url = "http://messenger.live.com";
								break;
		
							case "aim":
								//$im_url = "aim:GoIM?screenname=".$im_id;
								$im_url = "http://aimexpress.aim.com";
								break;
		
							case "skype":
								$im_url = "skype:".$im_id."?call";
								break;
						}
						$im_img = ilUtil::getImagePath($im_name.'online.png');

						
						if ($im_url != "")
						{
							$this->tpl->setCurrentBlock("im_link_start");
							$this->tpl->setVariable("URL_IM",$im_url);
							$this->tpl->parseCurrentBlock();
							$this->tpl->touchBlock("im_link_end");
						}

						$this->tpl->setCurrentBlock("instant_messengers");
						$this->tpl->setVariable("IMG_IM_ICON", $im_img);
						$this->tpl->setVariable("TXT_IM_ICON", $lng->txt("im_".$im_name));
						$icon_id = "im_".$im_name."_usr_".$a_set["id"];
						$this->tpl->setVariable("ICON_ID", $icon_id);
						
						include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
						ilTooltipGUI::addTooltip($icon_id, $lng->txt("im_".$im_name).": ".$im_id, "",
							"top center", "bottom center");
						
						$this->tpl->parseCurrentBlock();
					}
				}
//			}
		}
					
		// username
		if(!$a_set["profile"])
		{
			$this->tpl->setVariable("USR_LOGIN", $a_set["login"]);
		}
		else if ($this->getCurrentDetailLevel() > 2)
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
			include_once "Services/User/classes/class.ilUserUtil.php";
			$user_name = ilUserUtil::getNamePresentation($a_set["id"], false, false, "", false, true, false);		
			
			$this->tpl->setCurrentBlock("profile_link");
			$this->tpl->setVariable("TXT_VIEW", $lng->txt("profile"));
			
			// see ilPersonalProfileGUI::getProfilePortfolio()		
			$has_prtf = false;
			if ($ilSetting->get('user_portfolios'))
			{
				include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
				$has_prtf = ilObjPortfolio::getDefaultPortfolio($a_set["id"]);
			}
			if(!$has_prtf)
			{
				// (simple) profile: center column
				$ilCtrl->setParameter($this, "user", $a_set["id"]);
				$this->tpl->setVariable("LINK_PROFILE",
					$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));	
			}
			else
			{				
				// portfolio: fullscreen
				include_once "Services/Link/classes/class.ilLink.php";
				$this->tpl->setVariable("LINK_PROFILE", ilLink::_getLink($a_set["id"], "usr"));
				$this->tpl->setVariable("LINK_TARGET", "_blank");
			}
			
			$this->tpl->setVariable("USR_ID", $a_set["id"]);
			$this->tpl->setVariable("LINK_FULLNAME", $user_name);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("USR_FULLNAME", "");
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
		/*
		$chat_id = ilChatRoom::_isActive($a_usr_id);
		foreach(ilObject::_getAllReferences($chat_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('read',$ref_id))
			{
				$this->tpl->setCurrentBlock("chat_info");
				$this->tpl->setVariable("CHAT_ACTIVE_IN",$lng->txt('chat_active_in'));
				$this->tpl->setVariable("CHAT_LINK","./ilias.php?baseClass=ilChatPresentationGUI&ref_id=".$ref_id."&room_id=0");
				$this->tpl->setVariable("CHAT_TITLE",ilObject::_lookupTitle($chat_id));
				$this->tpl->parseCurrentBlock();
				
				return true;
			}
		}*/
		return false;
	}
	
	function __showChatInvitation($a_usr_id)
	{
		global $rbacsystem,$ilUser,$lng;
		
		include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';
		
		if($a_usr_id == $ilUser->getId())
		{
			return false;
		}

		//if($rbacsystem->checkAccess('read',ilObjChat::_getPublicChatRefId())
		//and $rbacsystem->checkAccessOfUser($a_usr_id,'read',ilObjChat::_getPublicChatRefId()))
		if($rbacsystem->checkAccess('read',ilObjChatroom::_getPublicRefId())
		and $rbacsystem->checkAccessOfUser($a_usr_id,'read',ilObjChatroom::_getPublicRefId()))
		{
			$this->tpl->setCurrentBlock("chat_link");
			$this->tpl->setVariable("TXT_CHAT_INVITE",$lng->txt('chat_invite_public_room'));
			//$this->tpl->setVariable("CHAT_LINK",'./ilias.php?baseClass=ilChatPresentationGUI&ref_id='.ilObjChat::_getPublicChatRefId().
			//'&usr_id='.$a_usr_id.'&cmd=invitePD');
			$this->tpl->setVariable("CHAT_LINK",'./ilias.php?baseClass=ilRepositoryGUI&ref_id='.ilObjChatroom::_getPublicRefId().'&usr_id='.$a_usr_id.'&cmd=view-invitePD');
			$this->tpl->setVariable('TXT_CHAT_INVITE_TOOLTIP', $lng->txt('chat_invite_public_room_tooltip'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		return false;
	}
}

?>
