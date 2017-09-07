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
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	static $block_type = "pdusers";
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->settings = $DIC->settings();
		$this->db = $DIC->database();
		$this->rbacsystem = $DIC->rbac()->system();
		$lng = $DIC->language();

		parent::__construct();
		
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
		global $DIC;

		$ilCtrl = $DIC->ctrl();

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
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

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
		$ilUser = $this->user;
		
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
		$ilUser = $this->user;
		
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
		$ilUser = $this->user;
		$rbacsystem = $this->rbacsystem;

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
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilSetting = $this->settings;
		$rbacsystem = $this->rbacsystem;

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

				include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';
				if($a_set["id"] == $ilUser->getId() &&
					$rbacsystem->checkAccess('read', ilObjChatroom::_getPublicRefId()))
				{
					$this->tpl->setCurrentBlock('chat_link');
					$this->tpl->setVariable('TXT_CHAT_INVITE', $lng->txt('chat_enter_public_room'));
					$this->tpl->setVariable('TXT_CHAT_INVITE_TOOLTIP', $lng->txt('chat_enter_public_room_tooltip'));
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
		$lng = $this->lng;

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
		return false;
	}
	
	function __showChatInvitation($a_usr_id)
	{
		$rbacsystem = $this->rbacsystem;
		$ilUser = $this->user;
		$lng = $this->lng;
		
		include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';
		
		if($a_usr_id == $ilUser->getId())
		{
			return false;
		}

		if($rbacsystem->checkAccess('read',ilObjChatroom::_getPublicRefId())
		and $rbacsystem->checkAccessOfUser($a_usr_id,'read',ilObjChatroom::_getPublicRefId()))
		{
			$this->tpl->setCurrentBlock("chat_link");
			$this->tpl->setVariable("TXT_CHAT_INVITE",$lng->txt('chat_invite_public_room'));
			$this->tpl->setVariable("CHAT_LINK",'./ilias.php?baseClass=ilRepositoryGUI&ref_id='.ilObjChatroom::_getPublicRefId().'&usr_id='.$a_usr_id.'&cmd=view-invitePD');
			$this->tpl->setVariable('TXT_CHAT_INVITE_TOOLTIP', $lng->txt('chat_invite_public_room_tooltip'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		return false;
	}
}

?>
