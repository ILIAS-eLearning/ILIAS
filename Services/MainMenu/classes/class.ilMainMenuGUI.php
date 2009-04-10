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
* Handles display of the main menu
*
* @author Alex Killing
* @version $Id$
*/
class ilMainMenuGUI
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;
	var $tpl;
	var $target;
	var $start_template;


	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	function ilMainMenuGUI($a_target = "_top", $a_use_start_template = false)
	{
		global $ilias;
		
		
		$this->tpl = new ilTemplate("tpl.main_menu.html", true, true,
			"Services/MainMenu");
		$this->ilias =& $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;
		$this->small = false;
	}
	
	function setSmallMode($a_small)
	{
		$this->small = $a_small;
	}
	
	/**
	* @param	string	$a_active	"desktop"|"repository"|"search"|"mail"|"chat_invitation"|"administration"
	*/
	function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* set output template
	*/
	function setTemplate(&$tpl)
	{
		echo "ilMainMenu->setTemplate is deprecated. Use getHTML instead.";
		return;
		$this->tpl =& $tpl;
	}

	/**
	* get output template
	*/
	function getTemplate()
	{
		echo "ilMainMenu->getTemplate is deprecated. Use getHTML instead.";
		return;
	}

	/**
	* add menu template as block
	*/
	function addMenuBlock($a_var = "CONTENT", $a_block = "navigation")
	{
		echo "ilMainMenu->addMenuBlick is deprecated. Use getHTML instead.";
		return;
		$this->tpl->addBlockFile($a_var, $a_block, "tpl.main_buttons.html");
	}

	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting;

		
		// navigation history
		require_once("Services/Navigation/classes/class.ilNavigationHistoryGUI.php");
		$nav_hist = new ilNavigationHistoryGUI();
		$nav_html = $nav_hist->getHTML();

		if ($nav_html != "")
		{

			$this->tpl->setCurrentBlock("nav_history");
			$this->tpl->setVariable("TXT_LAST_VISITED", $lng->txt("last_visited"));
			$this->tpl->setVariable("NAVIGATION_HISTORY", $nav_html);
			$this->tpl->parseCurrentBlock();
		}

		// administration button
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			$this->tpl->setCurrentBlock("userisadmin");
			$this->tpl->setVariable("IMG_ADMIN", ilUtil::getImagePath("navbar/admin.gif", false));
			$this->tpl->setVariable("IMG_SPACE_ADMIN", ilUtil::getImagePath("spacer.gif", false));
			$this->tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
			$this->tpl->setVariable("SCRIPT_ADMIN", $this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"));
			$this->tpl->setVariable("TARGET_ADMIN", $this->target);
			if ($this->active == "administration")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();
		}

		// search
		include_once 'Services/Search/classes/class.ilSearchSettings.php';
		if($rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
		{
			$this->tpl->setCurrentBlock("searchbutton");
			$this->tpl->setVariable("SCRIPT_SEARCH",$this->getScriptTarget('ilias.php?baseClass=ilSearchController'));
			$this->tpl->setVariable("TARGET_SEARCH",$this->target);
			$this->tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
			if ($this->active == "search")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();
			
			include_once './Services/Search/classes/class.ilMainMenuSearchGUI.php';
			$main_search = new ilMainMenuSearchGUI();
			if(strlen($html = $main_search->getHTML()))
			{
				$this->tpl->setVariable('SEARCHBOX',$html);
			}
		}
		
		
		// webshop
		include_once 'payment/classes/class.ilGeneralSettings.php';
		if((bool)ilGeneralSettings::_getInstance()->get('shop_enabled'))
		{
			$this->tpl->setCurrentBlock('shopbutton');
			$this->tpl->setVariable('SCRIPT_SHOP', $this->getScriptTarget('ilias.php?baseClass=ilShopController&cmd=clearFilter'));
			$this->tpl->setVariable('TARGET_SHOP', $this->target);			
			
			include_once 'payment/classes/class.ilPaymentShoppingCart.php';
			$objShoppingCart = new ilPaymentShoppingCart($ilUser);
			$items = $objShoppingCart->getEntries();
			
			$this->tpl->setVariable('TXT_SHOP', $lng->txt('shop').(count($items) > 0 ? ' ('.count($items).')' : ''));			
			
			if($this->active == 'shop')
			{
				$this->tpl->setVariable('MM_CLASS', 'MMActive');
			}
			else
			{
				$this->tpl->setVariable('MM_CLASS', 'MMInactive');
			}
			$this->tpl->parseCurrentBlock();
		}

		// help button
		//$this->tpl->setCurrentBlock("userhelp");
		//$this->tpl->setVariable("TXT_HELP", $lng->txt("help"));
		//$this->tpl->setVariable("SCRIPT_HELP", "ilias.php?baseClass=ilHelpGUI");
		//$this->tpl->setVariable("TARGET_HELP", "ilias_help");
		//$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		// mail & desktop button
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			$this->tpl->setCurrentBlock("desktopbutton");
			$this->tpl->setVariable("IMG_DESK", ilUtil::getImagePath("navbar/desk.gif", false));
			$this->tpl->setVariable("IMG_SPACE_DESK", ilUtil::getImagePath("spacer.gif", false));
			$this->tpl->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
			$this->tpl->setVariable("SCRIPT_DESK", $this->getScriptTarget("ilias.php?baseClass=ilPersonalDesktopGUI&PDHistory=1"));
			$this->tpl->setVariable("TARGET_DESK", $this->target);
			if ($this->active == "desktop")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();

			include_once "Services/Mail/classes/class.ilMail.php";
			
			$mail =& new ilMail($_SESSION["AccountId"]);

			if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
			{
				#$link = "mail_frameset.php";
				$link = "ilias.php?baseClass=ilMailGUI";
				
				if ($mail_id = ilMailbox::hasNewMail($_SESSION["AccountId"]))
				{
					$mbox = new ilMailbox($_SESSION["AccountId"]);
					$mail =& new ilMail($_SESSION['AccountId']);
					$folder_id = $mbox->getInboxFolder();
				
					//$link = "mail_frameset.php?target=".
					//	htmlentities(urlencode("mail_read.php?mobj_id=".
					//	$folder_id."&mail_id=".$mail_id));
					$add = " ".sprintf($lng->txt("cnt_new"),
						ilMailbox::_countNewMails($_SESSION["AccountId"]));
				}
				
				$this->tpl->setCurrentBlock("mailbutton");
				$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("navbar/mail.gif", false));
				$this->tpl->setVariable("IMG_SPACE_MAIL", ilUtil::getImagePath("spacer.gif", false));
				$this->tpl->setVariable("TXT_MAIL", $lng->txt("mail").$add);
				$this->tpl->setVariable("SCRIPT_MAIL", $this->getScriptTarget($link));
				$this->tpl->setVariable("TARGET_MAIL", $this->target);
				if ($this->active == "mail")
				{
					$this->tpl->setVariable("MM_CLASS", "MMActive");
				}
				else
				{
					$this->tpl->setVariable("MM_CLASS", "MMInactive");
				}
				$this->tpl->parseCurrentBlock();				
			}
		}
		
		// chat messages
		if ($ilSetting->get('chat_message_notify_status') == 1 && $_REQUEST['baseClass'] != 'ilChatPresentationGUI' && $ilUser->getPref('chat_message_notify_status') == 1) {
			include_once 'Modules/Chat/classes/class.ilChatMessageNotifyGUI.php';
			$msg_notify = new ilChatMessageNotifyGUI();
			$html = $msg_notify->getHtml();
			if ($html) {
				$this->tpl->setCurrentBlock("chat_lastmsg");
				$this->tpl->setVariable('CHAT_LAST_MESSAGE', $html);
				$this->tpl->parseCurrentBlock();
			}
			
		}
		
		// chat invitations
		include_once 'Modules/Chat/classes/class.ilChatInvitationGUI.php';
		$chat_invitation_gui = new ilChatInvitationGUI();
		$chat_invitation_html = $chat_invitation_gui->getHTML();
		if(trim($chat_invitation_html) != '')
		{
			$this->tpl->setCurrentBlock('chatbutton');
			$this->tpl->setVariable('CHAT_INVITATIONS', $chat_invitation_html);
			$this->tpl->parseCurrentBlock();
		}		

		// repository link		
		$this->tpl->setCurrentBlock("rep_button");
#		$this->tpl->setVariable("SCRIPT_CATALOG",'goto__target__root_1__client__ilias38.html');
#			#$this->getScriptTarget("repository.php?cmd=frameset&getlast=true"));

		include_once('classes/class.ilLink.php');
		$this->tpl->setVariable('SCRIPT_CATALOG',ilLink::_getStaticLink(1,'root',true));
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}
		$this->tpl->setVariable("TXT_CATALOG", $title);
		if ($this->active == "repository" || $this->active == "")
		{
			$this->tpl->setVariable("MM_CLASS", "MMActive");
		}
		else
		{
			$this->tpl->setVariable("MM_CLASS", "MMInactive");
		}
		// set target frame
		$this->tpl->setVariable("TARGET", $this->target);
		$this->tpl->parseCurrentBlock();
		

		$link_dir = (defined("ILIAS_MODULE"))
			? "../"
			: "";

		if (!$this->small)
		{
	
			// login stuff
			if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
			{
				include_once 'Services/Registration/classes/class.ilRegistrationSettingsGUI.php';
				if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED)
				{
					$this->tpl->setCurrentBlock("registration_link");
					$this->tpl->setVariable("TXT_REGISTER",$lng->txt("register"));
					$this->tpl->setVariable("LINK_REGISTER", $link_dir."register.php?client_id=".rawurlencode(CLIENT_ID)."&lang=".$ilias->account->getCurrentLanguage());
					$this->tpl->parseCurrentBlock();
				}
	
				$languages = $lng->getInstalledLanguages();
				
				foreach ($languages as $lang_key)
				{
					$this->tpl->setCurrentBlock("languages");
					$this->tpl->setVariable("LANG_KEY", $lang_key);
					$this->tpl->setVariable("LANG_NAME",
						ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
					$this->tpl->parseCurrentBlock();
				}
	
				$this->tpl->setVariable("TXT_OK", $lng->txt("ok"));
				//$this->tpl->setVariable("LANG_FORM_ACTION", "repository.php?ref_id=".$_GET["ref_id"]);
				$this->tpl->setVariable("LANG_FORM_ACTION", "#");
				$this->tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));
	
				$this->tpl->setCurrentBlock("userisanonymous");
				$this->tpl->setVariable("TXT_NOT_LOGGED_IN",$lng->txt("not_logged_in"));
				$this->tpl->setVariable("TXT_LOGIN",$lng->txt("log_in"));
				
				$target_str = "";
				if ($_GET["ref_id"] != "")
				{
					if ($tree->isInTree($_GET["ref_id"]) && $_GET["ref_id"] != $tree->getRootId())
					{
						$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
						$type = ilObject::_lookupType($obj_id);
						$target_str = $type."_".$_GET["ref_id"];
					}
				}
				$this->tpl->setVariable("LINK_LOGIN",
					$link_dir."login.php?target=".$target_str."&client_id=".rawurlencode(CLIENT_ID)."&cmd=force_login&lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("userisloggedin");
				$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
				$this->tpl->setVariable("TXT_LOGOUT2",$lng->txt("logout"));
				$this->tpl->setVariable("LINK_LOGOUT2", $link_dir."logout.php?lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->setVariable("USERNAME",$ilias->account->getFullname());
				$this->tpl->parseCurrentBlock();
			}
	
	
			$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
	
			$this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
	
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.png"));
			$this->tpl->setVariable("HEADER_BG_IMAGE", ilUtil::getImagePath("HeaderBackground.gif"));
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
			$this->tpl->setVariable("TXT_HEADER_TITLE", ilObjSystemFolder::_getHeaderTitle());
	
			// set link to return to desktop, not depending on a specific position in the hierarchy
			//$this->tpl->setVariable("SCRIPT_START", $this->getScriptTarget("start.php"));
		}

		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
	}

	/**
	* generates complete script target (private)
	*/
	function getScriptTarget($a_script)
	{
		global $ilias;

		$script = "./".$a_script;

		//if ($this->start_template == true)
		//{
			//if(is_file("./templates/".$ilias->account->skin."/tpl.start.html"))
			//{
	//			$script = "./start.php?script=".rawurlencode($script);
			//}
		//}
		if (defined("ILIAS_MODULE"))
		{
			$script = ".".$script;
		}
		return $script;
	}
	// STATIC
	function _checkAdministrationPermission()
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		{
			return true;
		}
		return false;
		// Allow all local admins to use the administration
		return count(ilUtil::_getObjectsByOperations('cat','cat_administrate_users')) ? true : false;
	}
	
	function getHTML()
	{
		$this->setTemplateVars();
		return $this->tpl->get();
	}
}
?>
