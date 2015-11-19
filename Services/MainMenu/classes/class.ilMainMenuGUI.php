<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';

/**
* Handles display of the main menu
*
* @author Alex Killing
* @version $Id$
*/
class ilMainMenuGUI
{
	/**
	* ilias objectm
	* @var		object ilias
	* @access	private
	*/
	var $ilias;
	var $tpl;
	var $target;
	var $start_template;
	var $mail; // [bool]
	
	protected $mode; // [int]
	protected $topbar_back_url; // [stringt]
	protected $topbar_back_caption; // [string]
	
	const MODE_FULL = 1;
	const MODE_TOPBAR_ONLY = 2;
	const MODE_TOPBAR_REDUCED = 3;
	const MODE_TOPBAR_MEMBERVIEW = 4;

	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	function ilMainMenuGUI($a_target = "_top", $a_use_start_template = false)
	{
		global $ilias, $rbacsystem, $ilUser;
		
		$this->tpl = new ilTemplate("tpl.main_menu.html", true, true,
			"Services/MainMenu");
		$this->ilias =& $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;
		
		$this->mail = false;
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if($rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()))
			{
				$this->mail = true;
			}
		}
		
		$this->setMode(self::MODE_FULL);		
		
		// member view
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		$set = ilMemberViewSettings::getInstance();		
		if($set->isActive())
		{
			$this->initMemberView();
		}		
	}
	
	public function setMode($a_value)
	{
		$this->mode = (int)$a_value;
	}
	
	public function getMode()
	{
		return $this->mode;
	}
	
	public function setTopBarBack($a_url, $a_caption = null)
	{
		$this->topbar_back_url = $a_url;
		$this->topbar_back_caption = trim($a_caption);
	}
	
	public function getSpacerClass()
	{
		switch($this->getMode())
		{
			case self::MODE_TOPBAR_ONLY:
			case self::MODE_TOPBAR_REDUCED:
			case self::MODE_TOPBAR_MEMBERVIEW:
				return "ilFixedTopSpacerBarOnly";
				
			case self::MODE_FULL:
				return "ilFixedTopSpacer";
		}
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
	 * Set target parameter for login (public sector).
	 * This is used by the main menu
	 */
	public function setLoginTargetPar($a_val)
	{
		$this->login_target_par = $a_val;
	}

	/**
	 * Get target parameter for login
	 */
	public function getLoginTargetPar()
	{
		return $this->login_target_par;
	}
	
	static function getLanguageSelection($a_in_topbar = false)
	{
		global $lng;

		global $ilUser;
		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
		$gr_list = new ilGroupedListGUI();
		$gr_list->setAsDropDown(true);

		$languages = $lng->getInstalledLanguages();
		if(sizeof($languages) > 1) // #11237
		{
			foreach ($languages as $lang_key)
			{
				$base = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
				$base = preg_replace("/&*lang=[a-z]{2}&*/", "", $base);
				$link = ilUtil::appendUrlParameterString($base,
					"lang=".$lang_key);
				$link = str_replace("?&", "?", $link);

				$gr_list->addEntry($lng->_lookupEntry($lang_key, "meta", "meta_l_".$lang_key), $link);
			}
			return $gr_list->getHTML();
		}
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
		}

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

		if($this->getMode() != self::MODE_TOPBAR_MEMBERVIEW)
		{					
			$link_dir = (defined("ILIAS_MODULE"))
				? "../"
				: "";
		
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
				if($this->getMode() != self::MODE_TOPBAR_REDUCED)
				{
					$notificationSettings = new ilSetting('notifications');
					$chatSettings = new ilSetting('chatroom');

					/**
					 * @var $tpl ilTemplate
					 */
					global $tpl;

					if($chatSettings->get('chat_enabled') && $notificationSettings->get('enable_osd'))
					{
						$this->tpl->touchBlock('osd_enabled');
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
						$this->tpl->setVariable('OSD_POLLING_INTERVALL', $notificationSettings->get('osd_polling_intervall') ? $notificationSettings->get('osd_polling_intervall') : '5');
						$this->tpl->setVariable(
							'OSD_PLAY_SOUND',
							$chatSettings->get('play_invitation_sound') && $ilUser->getPref('chat_play_invitation_sound') ? 'true' : 'false');
						foreach($notifications as $notification)
						{
							if($notification['type'] == 'osd_maint')
							{
								continue;
							}
							$this->tpl->setCurrentBlock('osd_notification_item');

							$this->tpl->setVariable('NOTIFICATION_ICON_PATH', $notification['data']->iconPath);
							$this->tpl->setVariable('NOTIFICATION_TITLE', $notification['data']->title);
							$this->tpl->setVariable('NOTIFICATION_LINK', $notification['data']->link);
							$this->tpl->setVariable('NOTIFICATION_LINKTARGET', $notification['data']->linktarget);
							$this->tpl->setVariable('NOTIFICATION_ID', $notification['notification_osd_id']);
							$this->tpl->setVariable('NOTIFICATION_SHORT_DESCRIPTION', $notification['data']->shortDescription);
							$this->tpl->parseCurrentBlock();
						}
					}
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

		// set link to return to desktop, not depending on a specific position in the hierarchy
		//$this->tpl->setVariable("SCRIPT_START", $this->getScriptTarget("start.php"));
		
		/*
		else
		{
			$this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
		}
		*/
		
		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * Render status box
	 */
	function renderStatusBox($a_tpl)
	{
		global $ilUser, $lng;
		
		$box = false;
		
		// new mails?
		if($this->mail)
		{
			$new_mails = ilMailGlobalServices::getNumberOfNewMailsByUserId($ilUser->getId());
			if($new_mails > 0)
			{
				$a_tpl->setCurrentBlock('status_text');
				$a_tpl->setVariable('STATUS_TXT', $new_mails);
				$a_tpl->parseCurrentBlock();
			}
			$a_tpl->setCurrentBlock('status_item');
			$a_tpl->setVariable('STATUS_IMG', ilUtil::getImagePath('icon_mail.svg'));
			$a_tpl->setVariable('STATUS_IMG_ALT', $lng->txt("mail"));
			$a_tpl->setVariable('STATUS_HREF', 'ilias.php?baseClass=ilMailGUI');
			$a_tpl->parseCurrentBlock();
			$box = true;
		}
		
		if ($box)
		{
			$a_tpl->setCurrentBlock("status_box");
			$a_tpl->parseCurrentBlock();
		}
	}
	

	/**
	 * desc
	 *
	 * @param
	 * @return
	 */
	function renderMainMenuListEntries($a_tpl, $a_call_get = true)
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilAccess;

		// personal desktop
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			$this->renderEntry($a_tpl, "desktop",
				$lng->txt("personal_desktop"), "#");
		}

		// repository
		if($ilAccess->checkAccess('visible','',ROOT_FOLDER_ID))
		{
			include_once('./Services/Link/classes/class.ilLink.php');
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID || IS_PAYMENT_ENABLED)
			{
				$this->renderEntry($a_tpl, "repository",
					$title, "#");
			}
		}


		// webshop
		if(IS_PAYMENT_ENABLED)
		{
			$title = $lng->txt("shop");
			$this->renderEntry($a_tpl, "shop", $title, "#" );
		}

		// administration
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			$this->renderDropDown($a_tpl, "administration");
		}

		if ($a_call_get)
		{
			return $a_tpl->get();
		}

		return "";
	}

	/**
	 * Render main menu entry
	 *
	 * @param
	 * @return
	 */
	function renderEntry($a_tpl, $a_id, $a_txt, $a_script, $a_target = "_top")
	{
		global $lng, $ilNavigationHistory, $ilSetting, $rbacsystem, $ilCtrl;
	
		$id = strtolower($a_id);
		$id_up = strtoupper($a_id);
		$a_tpl->setCurrentBlock("entry_".$id);

		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");

		// repository
		if ($a_id == "repository")
		{
			$gl = new ilGroupedListGUI();
			$gl->setAsDropDown(true);
			
			include_once("./Services/Link/classes/class.ilLink.php");
			$icon = ilUtil::img(ilObject::_getIcon(ilObject::_lookupObjId(1), "tiny"));
			
			$gl->addEntry($icon." ".$a_txt." - ".$lng->txt("rep_main_page"), ilLink::_getStaticLink(1,'root',true),
				"_top");
			
			$items = $ilNavigationHistory->getItems();
			reset($items);
			$cnt = 0;
			$first = true;

			foreach($items as $k => $item)
			{
				if ($cnt >= 10) break;
				
				if (!isset($item["ref_id"]) || !isset($_GET["ref_id"]) ||
					($item["ref_id"] != $_GET["ref_id"] || !$first))			// do not list current item
				{
					if ($cnt == 0)
					{
						$gl->addGroupHeader($lng->txt("last_visited"), "ilLVNavEnt");
					}
					$obj_id = ilObject::_lookupObjId($item["ref_id"]);
					$cnt ++;
					$icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
					$ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
					$gl->addEntry($icon." ".$ititle, $item["link"],	"_top", "", "ilLVNavEnt");

				}
				$first = false;
			}
			
			if ($cnt > 0)
			{
				$gl->addEntry("» ".$lng->txt("remove_entries"), "#", "",
					"return il.MainMenu.removeLastVisitedItems('".
					$ilCtrl->getLinkTargetByClass("ilnavigationhistorygui", "removeEntries", "", true)."');",
					"ilLVNavEnt");
			}

			$a_tpl->setVariable("REP_EN_OV", $gl->getHTML());
		}
		
		// desktop
		if ($a_id == "desktop")
		{
			$gl = new ilGroupedListGUI();
			$gl->setAsDropDown(true);
			
			// overview
			$gl->addEntry($lng->txt("overview"),
				"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSelectedItems",
				"_top", "", "", "mm_pd_sel_items", ilHelp::getMainMenuTooltip("mm_pd_sel_items"),
					"left center", "right center", false);
			
			// my groups and courses, if both is available
			if($ilSetting->get('disable_my_offers') == 0 &&
				$ilSetting->get('disable_my_memberships') == 0)
			{
				$gl->addEntry($lng->txt("my_courses_groups"),
					"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToMemberships",
					"_top", "", "", "mm_pd_crs_grp", ilHelp::getMainMenuTooltip("mm_pd_crs_grp"),
					"left center", "right center", false);
			}
			
			// bookmarks
			if (!$this->ilias->getSetting("disable_bookmarks"))
			{
				$gl->addEntry($lng->txt("bookmarks"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToBookmarks",
					"_top", "", "", "mm_pd_bookm", ilHelp::getMainMenuTooltip("mm_pd_bookm"),
					"left center", "right center", false);
			}
			
			// private notes
			if (!$this->ilias->getSetting("disable_notes"))
			{
				$gl->addEntry($lng->txt("notes_and_comments"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNotes",
					"_top", "", "", "mm_pd_notes", ilHelp::getMainMenuTooltip("mm_pd_notes"),
					"left center", "right center", false);
			}

			// news
			if ($ilSetting->get("block_activated_news"))
			{
				$gl->addEntry($lng->txt("news"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNews",
					"_top", "", "", "mm_pd_news", ilHelp::getMainMenuTooltip("mm_pd_news"),
					"left center", "right center", false);
			}

			// overview is always active
			$gl->addSeparator();
			
			$separator = false;
			
			if(!$ilSetting->get("disable_personal_workspace"))
			{
				// workspace
				$gl->addEntry($lng->txt("personal_workspace"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToWorkspace",
					"_top", "", "", "mm_pd_wsp", ilHelp::getMainMenuTooltip("mm_pd_wsp"),
					"left center", "right center", false);
				
				$separator = true;
			}
			
			// portfolio
			if ($ilSetting->get('user_portfolios'))
			{
				$gl->addEntry($lng->txt("portfolio"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToPortfolio",
					"_top", "", "", "mm_pd_port", ilHelp::getMainMenuTooltip("mm_pd_port"),
					"left center", "right center", false);
				
				$separator = true;
			}
			
			// skills
			$skmg_set = new ilSetting("skmg");
			if ($skmg_set->get("enable_skmg"))
			{
				$gl->addEntry($lng->txt("skills"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSkills",
					"_top", "", "", "mm_pd_skill", ilHelp::getMainMenuTooltip("mm_pd_skill"),
					"left center", "right center", false);
				
				$separator = true;
			}

			// Learning Progress
			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if (ilObjUserTracking::_enabledLearningProgress() && 
				(ilObjUserTracking::_hasLearningProgressOtherUsers() ||
				ilObjUserTracking::_hasLearningProgressLearner()))
			{
				//$ilTabs->addTarget("learning_progress", $this->ctrl->getLinkTargetByClass("ilLearningProgressGUI"));
				$gl->addEntry($lng->txt("learning_progress"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToLP",
					"_top", "", "", "mm_pd_lp", ilHelp::getMainMenuTooltip("mm_pd_lp"),
					"left center", "right center", false);
				
				$separator = true;
			}

			if($separator)
			{
				$gl->addSeparator();
			}
			
			$separator = false;
			
			// calendar
			include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
			$settings = ilCalendarSettings::_getInstance();
			if($settings->isEnabled())
			{
				$gl->addEntry($lng->txt("calendar"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToCalendar",
					"_top", "", "", "mm_pd_cal", ilHelp::getMainMenuTooltip("mm_pd_cal"),
					"left center", "right center", false);
				
				$separator = true;
			}

			// mail
			if($this->mail)
			{
				$gl->addEntry($lng->txt('mail'), 'ilias.php?baseClass=ilMailGUI', '_top',
					"", "", "mm_pd_mail", ilHelp::getMainMenuTooltip("mm_pd_mail"),
					"left center", "right center", false);
				
				$separator = true;
			}

			// contacts
			if(!$this->ilias->getSetting('disable_contacts') &&
				($this->ilias->getSetting('disable_contacts_require_mail') ||
				$rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())))
			{
				$gl->addEntry($lng->txt('mail_addressbook'),
					'ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToContacts', '_top'
					, "", "", "mm_pd_contacts", ilHelp::getMainMenuTooltip("mm_pd_contacts"),
					"left center", "right center", false);
				
				$separator = true;
			}
			
			if($separator)
			{
				$gl->addSeparator();
			}
			
			// profile
			$gl->addEntry($lng->txt("personal_profile"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToProfile",
				"_top", "", "", "mm_pd_profile", ilHelp::getMainMenuTooltip("mm_pd_profile"),
					"left center", "right center", false);

			// settings
			$gl->addEntry($lng->txt("personal_settings"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSettings",
				"_top", "", "", "mm_pd_sett", ilHelp::getMainMenuTooltip("mm_pd_sett"),
					"left center", "right center", false);

			$a_tpl->setVariable("DESK_CONT_OV", $gl->getHTML());
		}

		if(IS_PAYMENT_ENABLED)
		{
			// shop
			if ($a_id == "shop")
			{
				$gl = new ilGroupedListGUI();
				$gl->setAsDropDown(true);

				// shop_content
				$gl->addEntry($lng->txt("content"),
					"ilias.php?baseClass=ilShopController&amp;cmd=firstpage",
					"_top");

				// shoppingcart
				include_once 'Services/Payment/classes/class.ilPaymentShoppingCart.php';
				global $ilUser;
				$objShoppingCart = new ilPaymentShoppingCart($ilUser);
				$items = $objShoppingCart->getEntries();

				if(count($items) > 0 )
				{
					$gl->addEntry($lng->txt("shoppingcart").' ('.count($items).')',
						"ilias.php?baseClass=ilShopController&amp;cmdClass=ilshopshoppingcartgui",
						"_top");
				}
				$a_tpl->setVariable("SHOP_CONT_OV", $gl->getHTML());
			}
		}
		$a_tpl->setVariable("TXT_".$id_up, $a_txt);
		$a_tpl->setVariable("SCRIPT_".$id_up, $a_script);
		$a_tpl->setVariable("TARGET_".$id_up, $a_target);
		if ($this->active == $a_id || ($this->active == "" && $a_id == "repository"))
		{
			//$a_tpl->setVariable("MM_CLASS", "MMActive");
			$a_tpl->setVariable("SEL", '<span class="ilAccHidden">('.$lng->txt("stat_selected").')</span>');
		}
		else
		{
			//$a_tpl->setVariable("MM_CLASS", "MMInactive");
		}
		
		if($a_id == "repository")
		{
			include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
			if (ilAccessKey::getKey(ilAccessKey::LAST_VISITED) != "")
			{
				$a_tpl->setVariable("ACC_KEY_REPOSITORY", 'accesskey="'.
					ilAccessKey::getKey(ilAccessKey::LAST_VISITED).'"');
			}
		}
		if($a_id == "desktop")
		{
			include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
			if (ilAccessKey::getKey(ilAccessKey::PERSONAL_DESKTOP) != "")
			{
				$a_tpl->setVariable("ACC_KEY_DESKTOP", 'accesskey="'.
					ilAccessKey::getKey(ilAccessKey::PERSONAL_DESKTOP).'"');
			}
		}

		
		$a_tpl->parseCurrentBlock();
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

	function _checkAdministrationPermission()
	{
		global $rbacsystem;

		//if($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		if($rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID))
		{
			return true;
		}
		return false;
	}
	
	function getHTML()
	{
		// this is a workaround for bugs like 14016
		// the main menu does not need the YUI connection, but many other
		// features since they rely on il.Util.sendAjaxGetRequestToUrl (see Services/Javascript)
		// which still uses YUI. This should be migrated to jQuery with a future major release
		include_once "Services/YUI/classes/class.ilYuiUtil.php";
		ilYUIUtil::initConnection();

		$this->setTemplateVars();

		return $this->tpl->get();
	}
	
	/**
	 * Init member view
	 * @global type $lng
	 */
	protected function initMemberView()
	{
		global $lng;

		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		$ref_id = ilMemberViewSettings::getInstance()->getCurrentRefId();

		if(!$ref_id)
		{
			return FALSE;
		}	
			
		include_once './Services/Link/classes/class.ilLink.php';
		$url = ilLink::_getLink(
			$ref_id,
			ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
			array('mv' => 0));

		$this->setMode(self::MODE_TOPBAR_MEMBERVIEW);
		$this->setTopBarBack($url, $lng->txt('mem_view_close'));
	}

	/**
	 * GetDropDownHTML
	 *
	 * @param
	 * @return
	 */
	function renderDropDown($a_tpl, $a_id)
	{
		global $lng, $ilSetting, $rbacsystem;

		$id = strtolower($a_id);
		$id_up = strtoupper($a_id);
		$a_tpl->setCurrentBlock("entry_".$id);
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		if ($this->active == $a_id || ($this->active == "" && $a_id == "repository"))
		{
			$selection->setSelectionHeaderClass("MMActive");
			$a_tpl->setVariable("SEL", '<span class="ilAccHidden">('.$lng->txt("stat_selected").')</span>');
		}
		else
		{
			$selection->setSelectionHeaderClass("MMInactive");
		}
		
		$selection->setSelectionHeaderSpanClass("MMSpan");

		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::ICON_ARROW);
		$selection->setItemLinkClass("small");
		$selection->setUseImages(false);

		switch ($id)
		{
			// desktop drop down
			case "desktop":
				$selection->setListTitle($lng->txt("personal_desktop"));
				$selection->setId("dd_pd");

				// overview
				$selection->addItem($lng->txt("overview"), "", "ilias.php?baseClass=ilPersonalDesktopGUI",
					"", "", "_top");

				if(!$ilSetting->get("disable_personal_workspace"))
				{
					// workspace
					$selection->addItem($lng->txt("personal_workspace"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToWorkspace",
						"", "", "_top");
				}
				
				// profile
				$selection->addItem($lng->txt("personal_profile"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToProfile",
					"", "", "_top");
						
				// skills
				$skmg_set = new ilSetting("skmg");
				if ($skmg_set->get("enable_skmg"))
				{
					$selection->addItem($lng->txt("skills"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSkills",
						"", "", "_top");
				}
				
				// portfolio
				if ($ilSetting->get('user_portfolios'))
				{
					$selection->addItem($lng->txt("portfolio"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToPortfolio",
						"", "", "_top");
				}
				
				// news
				if ($ilSetting->get("block_activated_news"))
				{
					$selection->addItem($lng->txt("news"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNews",
						"", "", "_top");
				}

				// Learning Progress
				include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
				if (ilObjUserTracking::_enabledLearningProgress())
				{
					//$ilTabs->addTarget("learning_progress", $this->ctrl->getLinkTargetByClass("ilLearningProgressGUI"));
					$selection->addItem($lng->txt("learning_progress"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToLP",
						"", "", "_top");
				}

				// calendar
				include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
				$settings = ilCalendarSettings::_getInstance();
				if($settings->isEnabled())
				{
					$selection->addItem($lng->txt("calendar"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToCalendar",
						"", "", "_top");
				}

				// mail
				if($this->mail)
				{
					$selection->addItem($lng->txt('mail'), '', 'ilias.php?baseClass=ilMailGUI',	'', '', '_top');
				}

				// contacts
				if (!$this->ilias->getSetting('disable_contacts') &&
					($this->ilias->getSetting('disable_contacts_require_mail') ||
					$rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())))
				{
					$selection->addItem($lng->txt('mail_addressbook'), '', 'ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToContacts', '', '', '_top');
				}

				// private notes
				if (!$this->ilias->getSetting("disable_notes"))
				{
					$selection->addItem($lng->txt("notes_and_comments"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNotes",
						"", "", "_top");
				}

				// bookmarks
				if (!$this->ilias->getSetting("disable_bookmarks"))
				{
					$selection->addItem($lng->txt("bookmarks"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToBookmarks",
						"", "", "_top");
				}
				
				// settings
				$selection->addItem($lng->txt("personal_settings"), "", "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSettings",
					"", "", "_top");

				break;

			// shop
			case 'shop':
				$selection->setListTitle($lng->txt("shop"));
				$selection->setId("dd_shp");
				$selection->addItem($lng->txt("shop"), "", "ilias.php?baseClass=ilShopController&cmd=firstpage",
					"", "", "_top");
				break;

			// administration
			case "administration":
				$selection->setListTitle($lng->txt("administration"));
				$selection->setId("dd_adm");
				$selection->setAsynch(true);
				$selection->setAsynchUrl("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch");
							//$this->renderEntry($a_tpl, "administration",
			//	$lng->txt("administration"),
			//	$this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"),
			//	$this->target);

				break;

		}

//		$selection->setTriggerEvent("mouseover");
//		$selection->setAutoHide(true);

		// bs-patch start
		global $ilUser;
		$a_tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
		$a_tpl->parseCurrentBlock();
		return;
		// bs-patch end

		$html = $selection->getHTML();
		$a_tpl->setVariable($id_up."_DROP_DOWN", $html);
		$a_tpl->parseCurrentBlock();
	}


	/**
	 * Render help button
	 *
	 * @param
	 * @return
	 */
	function renderHelpButtons()
	{
		global $ilHelp, $lng, $ilCtrl, $tpl, $ilSetting, $ilUser;

		// screen id
		if (defined("OH_REF_ID") && OH_REF_ID > 0)
		{
			if ($ilHelp->getScreenId() != "")
			{
				if($this->getMode() == self::MODE_FULL)
				{
					$this->tpl->setCurrentBlock("screen_id");
					$this->tpl->setVariable("SCREEN_ID", $ilHelp->getScreenId());
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		
		$help_active = false;

		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
		$helpl = new ilGroupedListGUI();
		$helpl->setAsDropDown(true, true);

		if ($ilHelp->hasSections())
		{
			$help_active = true;
			
			$lng->loadLanguageModule("help");
			//$this->tpl->setCurrentBlock("help_icon");

			// add javascript needed by help (to do: move to help class)
			$tpl->addJavascript("./Services/Help/js/ilHelp.js");
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			$acc = new ilAccordionGUI();
			$acc->addJavascript();
			$acc->addCss();

			include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
			ilTooltipGUI::addTooltip("help_tr", $lng->txt("help_open_online_help"), "",
				"bottom center", "top center", false);
			$helpl->addEntry("<span>&nbsp;</span> ".$lng->txt("help_topcis"), "#", "", "il.Help.listHelp(event, false);");
		}
				
		$module_id = (int) $ilSetting->get("help_module");
		if ((OH_REF_ID > 0 || $module_id > 0) && $ilUser->getLanguage() == "de" &&
			$ilSetting->get("help_mode") != "1")
		{
			$help_active = true;
			
			$lng->loadLanguageModule("help");
			$tpl->addJavascript("./Services/Help/js/ilHelp.js");

			include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
			ilTooltipGUI::addTooltip("help_tt", $lng->txt("help_toggle_tooltips"), "",
				"bottom center", "top center", false);
			$helpl->addEntry('<span id="help_tt_switch_on" class="glyphicon glyphicon-ok"></span> '.$lng->txt("help_tooltips"), "#", "", "return il.Help.switchTooltips(event);");
		}
		
		if($help_active)
		{
			$this->tpl->setCurrentBlock("help");
			$this->tpl->setVariable("TXT_HELP", $lng->txt("help"));
			$this->tpl->setVariable("HELP_SELECT", $helpl->getHTML());
			$this->tpl->parseCurrentBlock();

			// always set ajax url
			$ts = $ilCtrl->getTargetScript();
			$ilCtrl->setTargetScript("ilias.php");

			$ilHelp->setCtrlPar();
			$tpl->addOnLoadCode("il.Help.setAjaxUrl('".
				$ilCtrl->getLinkTargetByClass("ilhelpgui", "", "", true)
				."');");
			$ilCtrl->setTargetScript($ts);
		}
	}
	
	
	/**
	 * Toggle rendering of main menu, search, user info
	 * 
	 * @see ilImprintGUI
	 * 
	 * @param bool $a_value 
	 */
	function showLogoOnly($a_value)
	{
		$this->logo_only = (bool)$a_value;
	}
	
	protected function getHeaderURL()
	{						
		include_once './Services/User/classes/class.ilUserUtil.php';						
		$url = ilUserUtil::getStartingPointAsUrl();
		
		if(!$url)
		{
			$url = "./goto.php?target=root_1";
		}
		
		return $url;
	}	
}

?>