<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace LTI;
use LTI\ilMainMenuGUI;
use LTI\ilSearchSettings;
use LTI\ilMainMenuSearchGUI;
include_once("Services/Mail/classes/class.ilMailGlobalServices.php");
require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");

/**
* Handles display of the main menu for LTI
*
* @author Stefan Schneider
* @version $Id$
*/
class ilMainMenuGUI extends \ilMainMenuGUI
{
	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	function __construct($a_target = "_top", $a_use_start_template = false)
	{
		global $DIC;
		$DIC->logger()->root()->write("LTI\\ilMainMenuGUI");
		parent::__construct($a_target, $a_use_start_template);		
	}
	
	
	public function getSpacerClass()
	{
		return "ilFixedTopSpacerBarOnly";
	}
	
	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilPluginAdmin;
		
		if($this->logo_only)
		{		
			$this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
			
			// #15759
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
			$header_top_title = ilObjSystemFolder::_getHeaderTitle();
			if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title"))
			{
				$this->tpl->setCurrentBlock("header_top_title");
				$this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
				$this->tpl->parseCurrentBlock();
			}
			
			return;
		}

		// get user interface plugins
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

		if($this->getMode() != self::MODE_TOPBAR_REDUCED &&
			$this->getMode() != self::MODE_TOPBAR_MEMBERVIEW)
		{
			// search
			include_once 'Services/Search/classes/class.ilSearchSettings.php';
			if($rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
			{
				include_once './Services/Search/classes/class.ilMainMenuSearchGUI.php';
				$main_search = new ilMainMenuSearchGUI();
				$html = "";

				// user interface plugin slot + default rendering
				include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
				$uip = new ilUIHookProcessor("Services/MainMenu", "main_menu_search",
					array("main_menu_gui" => $this, "main_menu_search_gui" => $main_search));
				if (!$uip->replaced())
				{
					$html = $main_search->getHTML();
				}
				$html = $uip->getHTML($html);

				if (strlen($html))
				{
					$this->tpl->setVariable('SEARCHBOX',$html);
				}
			}

			$this->renderStatusBox($this->tpl);

			// online help
			$this->renderHelpButtons();

			$this->renderOnScreenChatMenu();
			$this->populateWithBuddySystem();
			$this->populateWithOnScreenChat();
			$this->renderAwareness();
		}
		// LTI
		/*
		if($this->getMode() == self::MODE_FULL)
		{
			$mmle_html = "";

			// user interface plugin slot + default rendering
			include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
			$uip = new ilUIHookProcessor("Services/MainMenu", "main_menu_list_entries",
				array("main_menu_gui" => $this));
			if (!$uip->replaced())
			{
				$mmle_tpl = new ilTemplate("tpl.main_menu_list_entries.html", true, true, "Services/MainMenu");
				$mmle_html = $this->renderMainMenuListEntries($mmle_tpl);
			}
			$mmle_html = $uip->getHTML($mmle_html);

			$this->tpl->setVariable("MAIN_MENU_LIST_ENTRIES", $mmle_html);
		}
		*/
		if($this->getMode() != self::MODE_TOPBAR_MEMBERVIEW)
		{					
			$link_dir = (defined("ILIAS_MODULE"))
				? "../"
				: "";
		
			// login stuff
			if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID)
			{
				include_once 'Services/Registration/classes/class.ilRegistrationSettingsGUI.php';
				if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED)
				{
					$this->tpl->setCurrentBlock("registration_link");
					$this->tpl->setVariable("TXT_REGISTER",$lng->txt("register"));
					$this->tpl->setVariable("LINK_REGISTER", $link_dir."register.php?client_id=".rawurlencode(CLIENT_ID)."&lang=".$ilias->account->getCurrentLanguage());
					$this->tpl->parseCurrentBlock();
				}

				// language selection
				$selection = self::getLanguageSelection();
				if($selection)
				{
					// bs-patch start
					global $ilUser, $lng;
					$this->tpl->setVariable("TXT_LANGSELECT", $lng->txt("language"));
					// bs-patch end
					$this->tpl->setVariable("LANG_SELECT", $selection);
				}

				$this->tpl->setCurrentBlock("userisanonymous");
				$this->tpl->setVariable("TXT_NOT_LOGGED_IN",$lng->txt("not_logged_in"));
				$this->tpl->setVariable("TXT_LOGIN",$lng->txt("log_in"));

				// #13058
				$target_str = ($this->getLoginTargetPar() != "")
					? $this->getLoginTargetPar()
					: ilTemplate::buildLoginTarget();				
				$this->tpl->setVariable("LINK_LOGIN",
					$link_dir."login.php?target=".$target_str."&client_id=".rawurlencode(CLIENT_ID)."&cmd=force_login&lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				if($this->getMode() != self::MODE_TOPBAR_REDUCED && !$ilUser->isAnonymous())
				{
					$notificationSettings = new ilSetting('notifications');
					$chatSettings = new ilSetting('chatroom');

					/**
					 * @var $tpl ilTemplate
					 */
					global $tpl;

					$this->tpl->touchBlock('osd_container');

					include_once "Services/jQuery/classes/class.iljQueryUtil.php";
					iljQueryUtil::initjQuery();

					include_once 'Services/MediaObjects/classes/class.ilPlayerUtil.php';
					ilPlayerUtil::initMediaElementJs();
					
					$tpl->addJavaScript('Services/Notifications/templates/default/notifications.js');
					$tpl->addCSS('Services/Notifications/templates/default/osd.css');

					require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
					require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

					$notifications = ilNotificationOSDHandler::getNotificationsForUser($ilUser->getId());
					$this->tpl->setVariable('NOTIFICATION_CLOSE_HTML', json_encode(ilGlyphGUI::get(ilGlyphGUI::CLOSE, $lng->txt('close'))));
					$this->tpl->setVariable('INITIAL_NOTIFICATIONS', json_encode($notifications));
					$this->tpl->setVariable('OSD_POLLING_INTERVALL', $notificationSettings->get('osd_polling_intervall') ? $notificationSettings->get('osd_polling_intervall') : '60');
					$this->tpl->setVariable('OSD_PLAY_SOUND', $chatSettings->get('play_invitation_sound') && $ilUser->getPref('chat_play_invitation_sound') ? 'true' : 'false');
				}

				$this->tpl->setCurrentBlock("userisloggedin");
				$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
				$user_img_src = $ilias->account->getPersonalPicturePath("small", true);
				$user_img_alt = $ilias->account->getFullname();
				$this->tpl->setVariable("USER_IMG", ilUtil::img($user_img_src, $user_img_alt));
				$this->tpl->setVariable("USR_LINK_PROFILE", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile");
				$this->tpl->setVariable("USR_TXT_PROFILE", $lng->txt("personal_profile"));
				$this->tpl->setVariable("USR_LINK_SETTINGS", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings");
				$this->tpl->setVariable("USR_TXT_SETTINGS", $lng->txt("personal_settings"));
				$this->tpl->setVariable("TXT_LOGOUT2",$lng->txt("logout"));
				$this->tpl->setVariable("LINK_LOGOUT2", $link_dir."logout.php?lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->setVariable("USERNAME",$ilias->account->getFullname());
				$this->tpl->setVariable("LOGIN",$ilias->account->getLogin());
				$this->tpl->setVariable("MATRICULATION",$ilias->account->getMatriculation());
				$this->tpl->setVariable("EMAIL",$ilias->account->getEmail());
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			// member view info
			$this->tpl->setVariable("TOPBAR_CLASS", " ilMemberViewMainHeader");
			$this->tpl->setVariable("MEMBER_VIEW_INFO", $lng->txt("mem_view_long"));
		}

		if(!$this->topbar_back_url)
		{
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
			$header_top_title = ilObjSystemFolder::_getHeaderTitle();
			if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title"))
			{
				$this->tpl->setCurrentBlock("header_top_title");
				// php7-workaround alex: added phpversion() to help during development of php7 compatibility
				$this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("header_back_bl");
			$this->tpl->setVariable("URL_HEADER_BACK", $this->topbar_back_url);
			$this->tpl->setVariable("TXT_HEADER_BACK", $this->topbar_back_caption
				? $this->topbar_back_caption
				: $lng->txt("back"));
			$this->tpl->parseCurrentBlock();			
		}

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		if($this->getMode() == self::MODE_FULL)
		{
			// $this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
			$this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
		}
		
		include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");

		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
	}
}
