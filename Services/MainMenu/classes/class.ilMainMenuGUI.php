<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilPluginAdmin;

		// user interface hook [uihk]
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		$plugin_html = false;
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			$resp = $gui_class->getHTML("Services/MainMenu", "main_menu_list_entries",
				array("main_menu_gui" => $this));
			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$plugin_html = true;
				break;		// first one wins
			}
		}

		// default html
		if (!$plugin_html || $resp["mode"] != ilUIHookPluginGUI::REPLACE)
		{
			$mmle_tpl = new ilTemplate("tpl.main_menu_list_entries.html", true, true, "Services/MainMenu");
			$mmle_html = $this->renderMainMenuListEntries($mmle_tpl);
		}

		// combine plugin and default html
		if ($plugin_html)
		{
			$mmle_html = $gui_class->modifyHTML($mme_html, $resp);
		}

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
				include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
				$selection = new ilAdvancedSelectionListGUI();
				$selection->setFormSelectMode("change_lang_to", "ilLanguageSelection", true,
					"#", "ilNavHistory", "ilNavHistoryForm",
					"_top", $lng->txt("ok"), "ilLogin");
				//$selection->setListTitle($lng->txt("choose_language"));
				$selection->setListTitle($lng->txt("language"));
				$selection->setItemLinkClass("small");
				$languages = $lng->getInstalledLanguages();
				foreach ($languages as $lang_key)
				{
					$selection->addItem(ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key),
						$lang_key, "?lang=".$lang_key, "", "", "_top");
				}
				if (count($languages) > 0)
				{
					$this->tpl->setVariable("LANG_SELECT", $selection->getHTML());
				}
	
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
		else
		{
			$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.png"));
		}

		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
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
			$this->renderEntry($a_tpl, "desktop",
				$lng->txt("personal_desktop"),
				$this->getScriptTarget("ilias.php?baseClass=ilPersonalDesktopGUI"),
				$this->target);
		}

		// repository
		include_once('classes/class.ilLink.php');
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}
		$this->renderEntry($a_tpl, "repository",
			$title,
			ilLink::_getStaticLink(1,'root',true),
			$this->target);

		// search
		include_once 'Services/Search/classes/class.ilSearchSettings.php';
		if($rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
		{
			$this->renderEntry($a_tpl, "search",
				$lng->txt("search"),
				$this->getScriptTarget('ilias.php?baseClass=ilSearchController'),
				$this->target);

			include_once './Services/Search/classes/class.ilMainMenuSearchGUI.php';
			$main_search = new ilMainMenuSearchGUI();
			if(strlen($html = $main_search->getHTML()))
			{
				$a_tpl->setVariable('SEARCHBOX',$html);
			}
		}

		// mail
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			include_once "Services/Mail/classes/class.ilMail.php";

			$mail = new ilMail($_SESSION["AccountId"]);
			if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
			{
				if ($mail_id = ilMailbox::hasNewMail($_SESSION["AccountId"]))
				{
					$mbox = new ilMailbox($_SESSION["AccountId"]);
					$mail = new ilMail($_SESSION['AccountId']);
					$folder_id = $mbox->getInboxFolder();
					$add = " ".sprintf($lng->txt("cnt_new"),
						ilMailbox::_countNewMails($_SESSION["AccountId"]));
				}

				$this->renderEntry($a_tpl, "mail",
					$lng->txt("mail").$add,
					$this->getScriptTarget("ilias.php?baseClass=ilMailGUI"),
					$this->target);
			}
		}


		// webshop
		if(IS_PAYMENT_ENABLED)
		{
			$a_tpl->setCurrentBlock('shopbutton');
			$a_tpl->setVariable('SCRIPT_SHOP', $this->getScriptTarget('ilias.php?baseClass=ilShopController&cmd=clearFilter'));
			$a_tpl->setVariable('TARGET_SHOP', $this->target);

			include_once 'Services/Payment/classes/class.ilPaymentShoppingCart.php';
			$objShoppingCart = new ilPaymentShoppingCart($ilUser);
			$items = $objShoppingCart->getEntries();

			$a_tpl->setVariable('TXT_SHOP', $lng->txt('shop'));

			// shoppingcart
			if(count($items) > 0 )
			{

				$a_tpl->setVariable('SCRIPT_SHOPPINGCART', $this->getScriptTarget('ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilshopshoppingcartgui'));
				$a_tpl->setVariable('TARGET_SHOPPINGCART', $this->target);
				$a_tpl->setVariable('TXT_SHOPPINGCART', '('.count($items).')');
				if($this->active == 'shop')
				{
					$a_tpl->setVariable('MM_CLASS_SHOPPINGCART', 'MMActive');
				}
				else
				{
					$a_tpl->setVariable('MM_CLASS_SHOPPINGCART', 'MMInactive');
				}
			}

			if($this->active == 'shop')
			{
				$a_tpl->setVariable('MM_CLASS', 'MMActive');
				$a_tpl->setVariable("SEL", '<span class="ilAccHidden">('.$lng->txt("stat_selected").')</span>');
				if(count($items) > 0 )
				{
					$a_tpl->setVariable('STYLE_SHOP', 'style="margin-right: 5px;"');
				}
			}
			else
			{
				$a_tpl->setVariable('MM_CLASS', 'MMInactive');
				if(count($items) > 0 )
				{
					$a_tpl->setVariable('STYLE_SHOP', 'style="margin-right: 5px;"');
				}
			}
			$a_tpl->parseCurrentBlock();

		}

		// administration
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			$this->renderEntry($a_tpl, "administration",
				$lng->txt("administration"),
				$this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"),
				$this->target);
		}


		// navigation history
		require_once("Services/Navigation/classes/class.ilNavigationHistoryGUI.php");
		$nav_hist = new ilNavigationHistoryGUI();
		$nav_html = $nav_hist->getHTML();
		if ($nav_html != "")
		{

			$a_tpl->setCurrentBlock("nav_history");
			$a_tpl->setVariable("TXT_LAST_VISITED", $lng->txt("last_visited"));
			$a_tpl->setVariable("NAVIGATION_HISTORY", $nav_html);
			$a_tpl->parseCurrentBlock();
		}


		// chat messages
		if ($ilSetting->get('chat_message_notify_status') == 1 && $_REQUEST['baseClass'] != 'ilChatPresentationGUI' && $ilUser->getPref('chat_message_notify_status') == 1) {
			include_once 'Modules/Chat/classes/class.ilChatMessageNotifyGUI.php';
			$msg_notify = new ilChatMessageNotifyGUI();
			$html = $msg_notify->getHtml();
			if ($html) {
				$a_tpl->setCurrentBlock("chat_lastmsg");
				$a_tpl->setVariable('CHAT_LAST_MESSAGE', $html);
				$a_tpl->parseCurrentBlock();
			}

		}

		// chat invitations
		include_once 'Modules/Chat/classes/class.ilChatInvitationGUI.php';
		$chat_invitation_gui = new ilChatInvitationGUI();
		$chat_invitation_html = $chat_invitation_gui->getHTML();
		if(trim($chat_invitation_html) != '')
		{
			$a_tpl->setCurrentBlock('chatbutton');
			$a_tpl->setVariable('CHAT_INVITATIONS', $chat_invitation_html);
			$a_tpl->parseCurrentBlock();
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
		global $lng;

		$id = strtolower($a_id);
		$id_up = strtoupper($a_id);
		$a_tpl->setCurrentBlock("entry_".$id);
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

		include_once './classes/class.ilLink.php';
		
		$this->tpl->setVariable(
			'HREF_CLOSE_MM',
			ilLink::_getLink(
				(int) $_GET['ref_id'],
				ilObject::_lookupType(ilObject::_lookupObjId((int) $_GET['ref_id'])),
				array('mv' => 0)));
		
		return $this->tpl->get();
	}
}
?>
