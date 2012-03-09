<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		$this->small = false;
		
		$this->mail = false;
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if($rbacsystem->checkAccess('mail_visible', ilMailGlobalServices::getMailObjectRefId()))
			{
				$this->mail = true;
			}
		}
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
		
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setFormSelectMode("change_lang_to", "ilLanguageSelection", true,
			"#", "ilNavHistory", "ilNavHistoryForm",
			"", $lng->txt("ok"), "ilLogin");
		//$selection->setListTitle($lng->txt("choose_language"));
		$selection->setListTitle($lng->txt("language"));
		$selection->setItemLinkClass("small");
		
		if($a_in_topbar)
		{
			$selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_TOPBAR);
		}
		
		$languages = $lng->getInstalledLanguages();
		if(sizeof($languages) > 0)
		{
			foreach ($languages as $lang_key)
			{
				$base = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
				$base = preg_replace("/&*lang=[a-z]{2}&*/", "", $base);
				$link = ilUtil::appendUrlParameterString($base,
					"lang=".$lang_key);
				$link = str_replace("?&", "?", $link);
				$selection->addItem($lng->_lookupEntry($lang_key, "meta", "meta_l_".$lang_key),
					$lang_key, $link, "", "", "");
			}
			return $selection->getHTML();
		}
	}

	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilPluginAdmin;

		// get user interface plugins
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

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
		$this->renderHelpButton();

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
	
				// language selection
				$selection = self::getLanguageSelection();
				if($selection)
				{
					$this->tpl->setVariable("LANG_SELECT", $selection);
				}
	
				$this->tpl->setCurrentBlock("userisanonymous");
				$this->tpl->setVariable("TXT_NOT_LOGGED_IN",$lng->txt("not_logged_in"));
				$this->tpl->setVariable("TXT_LOGIN",$lng->txt("log_in"));
				
				$target_str = "";
				if ($this->getLoginTargetPar() != "")
				{
					$target_str = $this->getLoginTargetPar();
				}
				else if ($_GET["ref_id"] != "")
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
				/**
				 * notifications
				 */
				$notificationSettings = new ilSetting('notifications');
				$chatSettings = new ilSetting('chatroom');

				global $tpl;
				if ($chatSettings->get('chat_enabled') && $notificationSettings->get('enable_osd')) {
					$this->tpl->touchBlock('osd_enabled');
					$this->tpl->touchBlock('osd_container');
					$tpl->addJavaScript('Services/Notifications/templates/default/notifications.js');
					$tpl->addCSS('Services/Notifications/templates/default/osd.css');
					
					include_once "Services/jQuery/classes/class.iljQueryUtil.php";
					iljQueryUtil::initjQuery();

					require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
					$notifications = ilNotificationOSDHandler::getNotificationsForUser($ilUser->getId());
					$this->tpl->setVariable('INITIAL_NOTIFICATIONS', json_encode($notifications));
					$this->tpl->setVariable('OSD_POLLING_INTERVALL', $notificationSettings->get('osd_polling_intervall') ? $notificationSettings->get('osd_polling_intervall') : '5');
					$this->tpl->setVariable('OSD_PLAY_SOUND', $chatSettings->get('play_invitation_sound') ? 'true' : 'false');
					foreach($notifications as $notification) {
						if ($notification['type'] == 'osd_maint') {
							#var_dump($notification);
							continue;
						}
						#var_dump($notification);
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
				
				$this->tpl->setCurrentBlock("userisloggedin");
				$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
				$this->tpl->setVariable("TXT_LOGOUT2",$lng->txt("logout"));
				$this->tpl->setVariable("LINK_LOGOUT2", $link_dir."logout.php?lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->setVariable("USERNAME",$ilias->account->getFullname());
				$this->tpl->parseCurrentBlock();
			}
	
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
			$header_top_title = ilObjSystemFolder::_getHeaderTitle();
			if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title"))
			{
				$this->tpl->setCurrentBlock("header_top_title");
				$this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
				$this->tpl->parseCurrentBlock();
			}
	
			$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
	
			$this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.png"));
			$this->tpl->setVariable("HEADER_BG_IMAGE", ilUtil::getImagePath("HeaderBackground.gif"));
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
	
			// set link to return to desktop, not depending on a specific position in the hierarchy
			//$this->tpl->setVariable("SCRIPT_START", $this->getScriptTarget("start.php"));
		}
		else
		{
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.png"));
		}

		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * Render status box
	 */
	function renderStatusBox($a_tpl)
	{
		global $ilUser;
		
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
			$a_tpl->setVariable('STATUS_IMG', ilUtil::getImagePath('icon_mail_s.gif'));
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
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting;

		// personal desktop
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			/*$this->renderEntry($a_tpl, "desktop",
				$lng->txt("personal_desktop"),
				$this->getScriptTarget("ilias.php?baseClass=ilPersonalDesktopGUI"),
				$this->target);*/
//			$this->renderDropDown($a_tpl, "desktop");
			$this->renderEntry($a_tpl, "desktop",
				$lng->txt("personal_desktop"), "#");
			
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI("mm_desk_ov");
			$ov->setTrigger("mm_desk_tr");
			$ov->setAnchor("mm_desk_tr");
			$ov->setAutoHide(false);
			$ov->add();

		}

		// repository
		include_once('./Services/Link/classes/class.ilLink.php');
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}
		//$this->renderEntry($a_tpl, "repository",
		//	$title,
		//	ilLink::_getStaticLink(1,'root',true),
		//	$this->target);
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			$this->renderEntry($a_tpl, "repository",
				$title, "#");
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI("mm_rep_ov");
			$ov->setTrigger("mm_rep_tr");
			$ov->setAnchor("mm_rep_tr");
			$ov->setAutoHide(false);
			$ov->add();
		}

		// search
		include_once 'Services/Search/classes/class.ilSearchSettings.php';
		if($rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
		{
/*			$this->renderEntry($a_tpl, "search",
				$lng->txt("search"),
				$this->getScriptTarget('ilias.php?baseClass=ilSearchController'),
				$this->target); */
		}

		// webshop
		if(IS_PAYMENT_ENABLED)
		{
			$title = $lng->txt("shop");
			$this->renderEntry($a_tpl, "shop", $title, "#" );
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI("mm_shop_ov");
			$ov->setTrigger("mm_shop_tr");
			$ov->setAnchor("mm_shop_tr");
			$ov->setAutoHide(false);
			$ov->add();
		}

		// administration
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			//$this->renderEntry($a_tpl, "administration",
			//	$lng->txt("administration"),
			//	$this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"),
			//	$this->target);
			$this->renderDropDown($a_tpl, "administration");
		}


		// navigation history
/*		require_once("Services/Navigation/classes/class.ilNavigationHistoryGUI.php");
		$nav_hist = new ilNavigationHistoryGUI();
		$nav_html = $nav_hist->getHTML();
		if ($nav_html != "")
		{

			$a_tpl->setCurrentBlock("nav_history");
			$a_tpl->setVariable("TXT_LAST_VISITED", $lng->txt("last_visited"));
			$a_tpl->setVariable("NAVIGATION_HISTORY", $nav_html);
			$a_tpl->parseCurrentBlock();
		}*/


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
		global $lng, $ilNavigationHistory, $ilSetting, $rbacsystem;
	
		$id = strtolower($a_id);
		$id_up = strtoupper($a_id);
		$a_tpl->setCurrentBlock("entry_".$id);

		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");

		// repository
		if ($a_id == "repository")
		{
			$gl = new ilGroupedListGUI();
			
			include_once("./Services/Link/classes/class.ilLink.php");
			$a_tpl->setVariable("ARROW_IMG", ilUtil::getImagePath("mm_down_arrow.gif"));
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
						$gl->addGroupHeader($lng->txt("last_visited"));
					}
					$obj_id = ilObject::_lookupObjId($item["ref_id"]);
					$cnt ++;
					$icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
					$gl->addEntry($icon." ".ilUtil::shortenText($item["title"], 50, true), $item["link"],
						"_top");

				}
				$first = false;
			}

			$a_tpl->setVariable("REP_EN_OV", $gl->getHTML());
		}
		
		// desktop
		if ($a_id == "desktop")
		{
			$gl = new ilGroupedListGUI();
			
			$a_tpl->setVariable("ARROW_IMG", ilUtil::getImagePath("mm_down_arrow.gif"));
			
			// overview
			$gl->addEntry($lng->txt("overview"),
				"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSelectedItems",
				"_top");
			
			// my groups and courses, if both is available
			if($ilSetting->get('disable_my_offers') == 0 &&
				$ilSetting->get('disable_my_memberships') == 0)
			{
				$gl->addEntry($lng->txt("my_courses_groups"),
					"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToMemberships",
					"_top");
			}
			
			// bookmarks
			if (!$this->ilias->getSetting("disable_bookmarks"))
			{
				$gl->addEntry($lng->txt("bookmarks"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToBookmarks",
					"_top");
			}
			
			// private notes
			if (!$this->ilias->getSetting("disable_notes"))
			{
				$gl->addEntry($lng->txt("notes_and_comments"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNotes",
					"_top");
			}

			// news
			if ($ilSetting->get("block_activated_news"))
			{
				$gl->addEntry($lng->txt("news"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNews",
					"_top");
			}

			// overview is always active
			$gl->addSeparator();
			
			$separator = false;
			
			if(!$ilSetting->get("disable_personal_workspace"))
			{
				// workspace
				$gl->addEntry($lng->txt("personal_workspace"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToWorkspace",
					"_top");
				
				$separator = true;
			}
			
			// portfolio
			if ($ilSetting->get('user_portfolios'))
			{
				$gl->addEntry($lng->txt("portfolio"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToPortfolio",
					"_top");
				
				$separator = true;
			}
			
			// skills
			$skmg_set = new ilSetting("skmg");
			if ($skmg_set->get("enable_skmg"))
			{
				$gl->addEntry($lng->txt("skills"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSkills",
					"_top");
				
				$separator = true;
			}

			// Learning Progress
			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if (ilObjUserTracking::_enabledLearningProgress())
			{
				//$ilTabs->addTarget("learning_progress", $this->ctrl->getLinkTargetByClass("ilLearningProgressGUI"));
				$gl->addEntry($lng->txt("learning_progress"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToLP",
					"_top");
				
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
					"_top");
				
				$separator = true;
			}

			// mail
			if($this->mail)
			{
				$gl->addEntry($lng->txt('mail'), 'ilias.php?baseClass=ilMailGUI', '_top');
				
				$separator = true;
			}

			// contacts
			if(!$this->ilias->getSetting('disable_contacts') &&
				($this->ilias->getSetting('disable_contacts_require_mail') ||
				$rbacsystem->checkAccess('mail_visible', ilMailGlobalServices::getMailObjectRefId())))
			{
				$gl->addEntry($lng->txt('mail_addressbook'), 'ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToContacts', '_top');
				
				$separator = true;
			}
			
			if($separator)
			{
				$gl->addSeparator();
			}
			
			// profile
			$gl->addEntry($lng->txt("personal_profile"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToProfile",
				"_top");

			// settings
			$gl->addEntry($lng->txt("personal_settings"), "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToSettings",
				"_top");
			
			$a_tpl->setVariable("DESK_CONT_OV", $gl->getHTML());
		}

		if(IS_PAYMENT_ENABLED)
		{
			// shop
			if ($a_id == "shop")
			{
				$gl = new ilGroupedListGUI();
				$a_tpl->setVariable("ARROW_IMG", ilUtil::getImagePath("mm_down_arrow.gif"));

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
			$a_tpl->setVariable("MM_CLASS", "MMActive");
			$a_tpl->setVariable("SEL", '<span class="ilAccHidden">('.$lng->txt("stat_selected").')</span>');
		}
		else
		{
			$a_tpl->setVariable("MM_CLASS", "MMInactive");
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
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		$set = ilMemberViewSettings::getInstance();
		
		if($set->isActive())
		{
			return $this->getMemberViewHTML();
		}
		
		
		$this->setTemplateVars();

		return $this->tpl->get();
	}
	
	protected function getMemberViewHTML()
	{
		global $lng;
		
		$this->tpl = new ilTemplate('tpl.member_view_main_menu.html',true,true,'Services/MainMenu');
		
		$this->tpl->setVariable('TXT_MM_HEADER',$lng->txt('mem_view_long'));
		$this->tpl->setVariable('TXT_MM_CLOSE_PREVIEW',$lng->txt('mem_view_close'));
		$this->tpl->setVariable('MM_CLOSE_IMG',ilUtil::getImagePath('cancel.gif'));

		include_once './Services/Link/classes/class.ilLink.php';
		
		$this->tpl->setVariable(
			'HREF_CLOSE_MM',
			ilLink::_getLink(
				(int) $_GET['ref_id'],
				ilObject::_lookupType(ilObject::_lookupObjId((int) $_GET['ref_id'])),
				array('mv' => 0)));
		
		return $this->tpl->get();
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

		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_LIGHT);
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
					$rbacsystem->checkAccess('mail_visible', ilMailGlobalServices::getMailObjectRefId())))
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
				$selection->setAsynchUrl("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown");
							//$this->renderEntry($a_tpl, "administration",
			//	$lng->txt("administration"),
			//	$this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"),
			//	$this->target);

				break;

		}

//		$selection->setTriggerEvent("mouseover");
//		$selection->setAutoHide(true);

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
	function renderHelpButton()
	{
		global $ilHelp, $lng, $ilCtrl, $tpl;
		
		// screen id
		if ((defined("OH_REF_ID") && OH_REF_ID > 0) || (DEVMODE))
		{
			if ($ilHelp->getScreenId() != "")
			{
				$this->tpl->setCurrentBlock("screen_id");
				$this->tpl->setVariable("SCREEN_ID", $ilHelp->getScreenId());
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($ilHelp->hasSections())
		{
			$this->tpl->setCurrentBlock("help_icon");

			// add javascript needed by help (to do: move to help class)
			$tpl->addJavascript("./Services/Help/js/ilHelp.js");
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			$acc = new ilAccordionGUI();
			$acc->addJavascript();
			$acc->addCss();

			$ts = $ilCtrl->getTargetScript();
			$ilCtrl->setTargetScript("ilias.php");
			
			$ilHelp->setCtrlPar();
			$tpl->addOnLoadCode("ilHelp.setAjaxUrl('".
				$ilCtrl->getLinkTargetByClass("ilhelpgui", "", "", true)
				."');");
			$ilCtrl->setTargetScript($ts);
			$this->tpl->setVariable("TXT_HELP", $lng->txt("help"));

			$this->tpl->parseCurrentBlock();
		}
	}
	
}
?>
