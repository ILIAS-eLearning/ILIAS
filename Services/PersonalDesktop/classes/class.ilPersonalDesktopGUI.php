<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/User/classes/class.ilObjUser.php';
include_once "Services/Mail/classes/class.ilMail.php";

/**
* GUI class for personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPersonalProfileGUI, ilBookmarkAdministrationGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilObjUserGUI, ilPDNotesGUI, ilLearningProgressGUI, ilFeedbackGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilColumnGUI, ilPDNewsGUI, ilCalendarPresentationGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilMailSearchGUI, ilMailAddressbookGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPersonalWorkspaceGUI, ilPersonalSettingsGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPortfolioGUI
*
*/
class ilPersonalDesktopGUI
{
	var $tpl;
	var $lng;
	var $ilias;
	
	var $cmdClass = '';

	/**
	* constructor
	*/
	function ilPersonalDesktopGUI()
	{
		global $ilias, $tpl, $lng, $rbacsystem, $ilCtrl, $ilMainMenu, $ilUser, $tree;
		
		
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		
		$ilCtrl->setContext($ilUser->getId(),
				"user");

		$ilMainMenu->setActive("desktop");
		$this->lng->loadLanguageModule("pdesk");
		
		// catch hack attempts
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_available_for_anon"),$this->ilias->error_obj->MESSAGE);
		}
		$this->cmdClass = $_GET['cmdClass'];
		
		//$tree->useCache(false);
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilSetting, $rbacsystem;

		$next_class = $this->ctrl->getNextClass();
		$this->ctrl->setReturn($this, "show");

		$this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem','delos.css','Services/Calendar'));
		
		
		// check whether personal profile of user is incomplete
		if ($ilUser->getProfileIncomplete() && $next_class != "ilpersonalprofilegui")
		{
			$this->ctrl->redirectByClass("ilpersonalprofilegui");
		}

		// check whether password of user have to be changed
		// due to first login or password of user is expired
		if( ($ilUser->isPasswordChangeDemanded() || $ilUser->isPasswordExpired())
				&& $next_class != "ilpersonalprofilegui"
		)
		{
			$this->ctrl->redirectByClass("ilpersonalprofilegui");
		}

		// read last active subsection
		if (isset($_GET['PDHistory']) && $_GET['PDHistory'])
		{
			$next_class = $this->__loadNextClass();
		}
		$this->__storeLastClass($next_class);


		// check for permission to view contacts
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail($_SESSION["AccountId"]);
		if (
			$next_class == 'ilmailaddressbookgui' && ($this->ilias->getSetting("disable_contacts") || 
			(
				!$this->ilias->getSetting("disable_contacts_require_mail") &&
				!$rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId())
			))
		) // if
		{
			$next_class = '';
			ilUtil::sendFailure($this->lng->txt('no_permission'));
		}

		switch($next_class)
		{
			//Feedback
			case "ilfeedbackgui":
				$this->getStandardTemplates();
				$this->setTabs();
				$this->tpl->setTitle($this->lng->txt("personal_desktop"));
				//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
				//	$this->lng->txt("personal_desktop"));
				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
					"");

				include_once("Services/Feedback/classes/class.ilFeedbackGUI.php");
				$feedback_gui = new ilFeedbackGUI();
				$ret =& $this->ctrl->forwardCommand($feedback_gui);
				break;
				// bookmarks
			case "ilbookmarkadministrationgui":
				if ($ilSetting->get('disable_bookmarks'))
				{
					ilUtil::sendFailure($this->lng->txt('permission_denied'), true);					
					ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
					return;
				}				
				include_once("./Services/PersonalDesktop/classes/class.ilBookmarkAdministrationGUI.php");
				$bookmark_gui = new ilBookmarkAdministrationGUI();
				if ($bookmark_gui->getMode() == 'tree') {
					$this->getTreeModeTemplates();
				} else {
					$this->getStandardTemplates();
				}
				$this->setTabs();
				$ret =& $this->ctrl->forwardCommand($bookmark_gui);
				break;
			
				// profile
			case "ilpersonalprofilegui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./Services/User/classes/class.ilPersonalProfileGUI.php");
				$profile_gui = new ilPersonalProfileGUI();
				$ret =& $this->ctrl->forwardCommand($profile_gui);
				break;
				
			// settings
			case "ilpersonalsettingsgui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./Services/User/classes/class.ilPersonalSettingsGUI.php");
				$settings_gui = new ilPersonalSettingsGUI();
				$ret =& $this->ctrl->forwardCommand($settings_gui);
				break;
			
				// profile
			case "ilobjusergui":
				include_once('./Services/User/classes/class.ilObjUserGUI.php');
				$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
				$ret =& $this->ctrl->forwardCommand($user_gui);
				break;
			
			case 'ilcalendarpresentationgui':
				$this->getStandardTemplates();
				$this->displayHeader();
				$this->tpl->setTitle($this->lng->txt("calendar"));
				$this->setTabs();
				include_once('./Services/Calendar/classes/class.ilCalendarPresentationGUI.php');
				$cal = new ilCalendarPresentationGUI();
				$ret = $this->ctrl->forwardCommand($cal);
				$this->tpl->show();
				break;
			
				// pd notes
			case "ilpdnotesgui":
				if ($ilSetting->get('disable_notes'))
				{
					ilUtil::sendFailure($this->lng->txt('permission_denied'), true);					
					ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
					return;
				}
				
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./Services/Notes/classes/class.ilPDNotesGUI.php");
				$pd_notes_gui = new ilPDNotesGUI();
				$ret =& $this->ctrl->forwardCommand($pd_notes_gui);
				break;
			
			// pd news
			case "ilpdnewsgui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./Services/News/classes/class.ilPDNewsGUI.php");
				$pd_news_gui = new ilPDNewsGUI();
				$ret =& $this->ctrl->forwardCommand($pd_news_gui);
				break;

			case "illearningprogressgui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui = new ilLearningProgressGUI(LP_MODE_PERSONAL_DESKTOP,0);
				$ret =& $this->ctrl->forwardCommand($new_gui);
				
				break;		

			case "ilcolumngui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./Services/Block/classes/class.ilColumnGUI.php");
				$column_gui = new ilColumnGUI("pd");
				$this->initColumn($column_gui);
				$this->show();
				break;

			// contacts
			case 'ilmailaddressbookgui':
				$this->getStandardTemplates();
				$this->setTabs();
				$this->tpl->setTitle($this->lng->txt("mail_addressbook"));
				//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
				//	$this->lng->txt("personal_desktop"));
//				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
//					"");
				include_once 'Services/Contact/classes/class.ilMailAddressbookGUI.php';
				$mailgui = new ilMailAddressbookGUI();
				$ret = $this->ctrl->forwardCommand($mailgui);
				break;

			case 'ilpersonalworkspacegui':
				$this->getStandardTemplates();
				$this->setTabs();
				include_once 'Services/PersonalWorkspace/classes/class.ilPersonalWorkspaceGUI.php';
				$wsgui = new ilPersonalWorkspaceGUI();
				$ret = $this->ctrl->forwardCommand($wsgui);				
				$this->tpl->show();
				break;
			
			case 'ilportfoliogui':
				$this->getStandardTemplates();
				$this->setTabs();
				include_once 'Services/Portfolio/classes/class.ilPortfolioGUI.php';
				$pfgui = new ilPortfolioGUI($ilUser->getId());
				$ret = $this->ctrl->forwardCommand($pfgui);				
				$this->tpl->show();
				break;

			case 'redirect':
				$this->redirect();
				break;

			default:
				$this->getStandardTemplates();
				$this->setTabs();
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		$ret = null;
		return $ret;
	}
	
	/**
	 * directly redirects a call
	 */
	public function redirect()
	{
		if(is_array($_GET))
		{
			foreach($_GET as $key => $val)
			{				
				if(substr($key, 0, strlen('param_')) == 'param_')
				{
					$this->ctrl->setParameterByClass($_GET['redirectClass'], substr($key, strlen('param_')), $val);
				}
			}
		}
		ilUtil::redirect($this->ctrl->getLinkTargetByClass($_GET['redirectClass'], $_GET['redirectCmd'], '', true));
	}	
	
	/**
	* get standard templates
	*/
	function getStandardTemplates()
	{
		$this->tpl->getStandardTemplate();
		// add template for content
//		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
//		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}
	
	/**
	* get tree mode templates
	*/
	function getTreeModeTemplates()
	{
		// add template for content
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_tree_content.html");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}
	
	/**
	* show desktop
	*/
	function show()
	{
		
		// preload block settings
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		ilBlockSetting::preloadPDBlockSettings();

		// add template for content
		$this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true);
		$this->tpl->getStandardTemplate();

		// display infopanel if something happened
		ilUtil::infoPanel();
		
		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
//		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
//			"");
		$this->tpl->setTitle($this->lng->txt("overview"));
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		$this->tpl->setContent($this->getCenterColumnHTML());
		$this->tpl->setRightContent($this->getRightColumnHTML());
		$this->tpl->setLeftContent($this->getLeftColumnHTML());
		$this->tpl->show();
	}
	
	
	/**
	* Display center column
	*/
	function getCenterColumnHTML()
	{
		global $ilCtrl;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("pd", IL_COL_CENTER);
		$this->initColumn($column_gui);

		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_CENTER)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
				{
					// right column wants center
					if ($column_gui->getCmdSide() == IL_COL_RIGHT)
					{
						$column_gui = new ilColumnGUI("pd", IL_COL_RIGHT);
						$this->initColumn($column_gui);
						$html = $ilCtrl->forwardCommand($column_gui);
					}
					// left column wants center
					if ($column_gui->getCmdSide() == IL_COL_LEFT)
					{
						$column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
						$this->initColumn($column_gui);
						$html = $ilCtrl->forwardCommand($column_gui);
					}
				}
				else
				{
					$html = $ilCtrl->getHTML($column_gui);
				}
			}
		}
		return $html;
	}

	/**
	* Display right column
	*/
	function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl, $ilPluginAdmin;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("pd", IL_COL_RIGHT);
		$this->initColumn($column_gui);

		if ($column_gui->getScreenMode() == IL_SCREEN_FULL)
		{
			return "";
		}

		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_RIGHT &&
			$column_gui->getScreenMode() == IL_SCREEN_SIDE)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				$html = $ilCtrl->getHTML($column_gui);
				
				// user interface hook [uihk]
				$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
				$plugin_html = false;
				foreach ($pl_names as $pl)
				{
					$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
					$gui_class = $ui_plugin->getUIClassInstance();
					$resp = $gui_class->getHTML("Services/PersonalDesktop", "right_column",
						array("main_menu_gui" => $this));
					if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
					{
						$plugin_html = true;
						break;		// first one wins
					}
				}

				// combine plugin and default html
				if ($plugin_html)
				{
					$html = $gui_class->modifyHTML($html, $resp);
				}

			}
		}

		return $html;
	}

	/**
	* Display left column
	*/
	function getLeftColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl, $ilPluginAdmin;

		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
		$this->initColumn($column_gui);

		if ($column_gui->getScreenMode() == IL_SCREEN_FULL)
		{
			return "";
		}

		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_LEFT &&
			$column_gui->getScreenMode() == IL_SCREEN_SIDE)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				$html = $ilCtrl->getHTML($column_gui);
				
				// user interface hook [uihk]
				$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
				$plugin_html = false;
				foreach ($pl_names as $pl)
				{
					$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
					$gui_class = $ui_plugin->getUIClassInstance();
					$resp = $gui_class->getHTML("Services/PersonalDesktop", "left_column",
						array("main_menu_gui" => $this));
					if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
					{
						$plugin_html = true;
						break;		// first one wins
					}
				}

				// combine plugin and default html
				if ($plugin_html)
				{
					$html = $gui_class->modifyHTML($html, $resp);
				}
			}
		}

		return $html;
	}

	function prepareContentView()
	{
		// add template for content
		$this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true);
		$this->tpl->getStandardTemplate();
				
		// display infopanel if something happened
		ilUtil::infoPanel();

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
	}


	/**
	* copied from usr_personaldesktop.php
	*/
	function removeMember()
	{
		global $err_msg;
		if (strlen($err_msg) > 0)
		{
			$this->ilias->raiseError($this->lng->txt($err_msg),$this->ilias->error_obj->MESSAGE);
		}
		$this->show();
	}
	
	/**
	* Display system messages.
	*/
	function displaySystemMessages()
	{
		include_once("Services/Mail/classes/class.ilPDSysMessageBlockGUI.php");
		$sys_block = new ilPDSysMessageBlockGUI("ilpersonaldesktopgui", "show");
		return $sys_block->getHTML();
	}
	
	
	/**
	* Display Links for Feedback
	*/
	function displayFeedback()
	{
		include_once("./Services/Feedback/classes/class.ilPDFeedbackBlockGUI.php");
		$fb_block = new ilPDFeedbackBlockGUI("ilpersonaldesktopgui", "show");
		return $fb_block->getHTML();

		include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
		$feedback_gui = new ilFeedbackGUI();
		return $feedback_gui->getPDFeedbackListHTML();
	}
	
	/**
	* Returns the multidimenstional sorted array
	*
	* Returns the multidimenstional sorted array
	*
	* @author       Muzaffar Altaf <maltaf@tzi.de>
	* @param array $arrays The array to be sorted
	* @param string $key_sort The keys on which array must be sorted
	* @access public
	*/
	function multiarray_sort ($array, $key_sort)
	{
		if ($array) {
			$key_sorta = explode(";", $key_sort);
			
			$multikeys = array_keys($array);
			$keys = array_keys($array[$multikeys[0]]);
			
			for($m=0; $m < count($key_sorta); $m++) {
				$nkeys[$m] = trim($key_sorta[$m]);
			}
			$n += count($key_sorta);
			
			for($i=0; $i < count($keys); $i++){
				if(!in_array($keys[$i], $key_sorta)) {
					$nkeys[$n] = $keys[$i];
					$n += "1";
				}
			}
			
			for($u=0;$u<count($array); $u++) {
				$arr = $array[$multikeys[$u]];
				for($s=0; $s<count($nkeys); $s++) {
					$k = $nkeys[$s];
					$output[$multikeys[$u]][$k] = $array[$multikeys[$u]][$k];
				}
			}
			sort($output);
			return $output;
		}
	}
	
	/**
	* set personal desktop tabs
	*/
	function setTabs()
	{
	
	}
	
	/**
	 * workaround for menu in calendar only
	 */
	function jumpToProfile()
	{
		$this->ctrl->redirectByClass("ilpersonalprofilegui");
	}
	
	/**
	 * workaround for menu in calendar only
	 */
	function jumpToPortfolio()
	{
		$this->ctrl->redirectByClass("ilportfoliogui");
	}
	
	/**
	 * workaround for menu in calendar only
	 */
	function jumpToSettings()
	{
		$this->ctrl->redirectByClass("ilpersonalsettingsgui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToBookmarks()
	{
		if ($this->ilias->getSetting("disable_bookmarks"))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);					
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
			return;
		}
		
		$this->ctrl->redirectByClass("ilbookmarkadministrationgui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToNotes()
	{
		if ($this->ilias->getSetting('disable_notes'))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);					
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
			return;
		}		
		
		$this->ctrl->redirectByClass("ilpdnotesgui");			
	}

	/**
	* workaround for menu in calendar only
	*/
	function jumpToNews()
	{
		$this->ctrl->redirectByClass("ilpdnewsgui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToLP()
	{
		$this->ctrl->redirectByClass("illearningprogressgui");
	}

	/**
	 * Jump to calendar
	 */
	function jumpToCalendar()
	{
		$this->ctrl->redirectByClass("ilcalendarpresentationgui");
	}

	/**
	 * Jump to contacts
	 */
	function jumpToContacts()
	{
		$this->ctrl->redirectByClass("ilmailaddressbookgui");
	}

	/**
	 * Jump to personal workspace
	 */
	function jumpToWorkspace()
	{
		$this->ctrl->redirectByClass("ilpersonalworkspacegui");
	}

	function __loadNextClass()
	{
		$stored_classes = array('ilpersonaldesktopgui',
								'ilpersonalprofilegui',
								'ilpdnotesgui',
								'ilcalendarpresentationgui',
								'ilbookmarkadministrationgui',
								'illearningprogressgui');

		if(isset($_SESSION['il_pd_history']) and in_array($_SESSION['il_pd_history'],$stored_classes))
		{
			return $_SESSION['il_pd_history'];
		}
		else
		{
			$this->ctrl->getNextClass($this);
		}
	}
	function __storeLastClass($a_class)
	{
		$_SESSION['il_pd_history'] = $a_class;
		$this->cmdClass = $a_class;
	}

	/**
	* Init ilColumnGUI
	*/
	function initColumn($a_column_gui)
	{
		$pd_set = new ilSetting("pd");
		if ($pd_set->get("enable_block_moving"))
		{
			$a_column_gui->setEnableMovement(true);
		}
	}
	
	/**
	* display header and locator
	*/
	function displayHeader()
	{
		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
//		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
//			"");
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
	}
	

}
?>
