<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once "./Modules/Wiki/classes/class.ilObjWiki.php";


/**
* Class ilObjWikiGUI
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjWikiGUI: ilPermissionGUI, ilInfoScreenGUI, ilWikiPageGUI
* @ilCtrl_IsCalledBy ilObjWikiGUI: ilRepositoryGUI, ilAdministrationGUI
* @ilCtrl_Calls ilObjWikiGUI: ilPublicUserProfileGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjWikiGUI: ilExportGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjWikiGUI: ilRatingGUI, ilWikiPageTemplateGUI, ilWikiStatGUI
*/
class ilObjWikiGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjWikiGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $lng;
		
		$this->type = "wiki";
		
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$lng->loadLanguageModule("wiki");
		
		if ($_GET["page"] != "")
		{
			$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($_GET["page"]));
		}
	}
	
	function executeCommand()
	{
  		global $ilUser, $ilCtrl, $tpl, $ilTabs, $ilAccess;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		
		// see ilWikiPageGUI::printViewOrderList()
		// printView() and pdfExport() cannot be in ilWikiPageGUI because of stylesheet confusion
		if($cmd == "printView" || $cmd == "pdfExport")
		{
			$next_class = null;
		}
	
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->checkPermission("visible");
				$this->addHeaderAction();
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				$this->addHeaderAction();
				$ilTabs->activateTab("perm_settings");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			case 'ilwikipagegui':				
				$this->checkPermission("read");
				include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
				$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
					ilWikiUtil::makeDbTitle($_GET["page"]), $_GET["old_nr"], $this->object->getRefId());
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$wpage_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "wiki"));
				$this->setContentStyleSheet();
				if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
					(!$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()) ||
						$wpage_gui->getPageObject()->getBlocked()
					))
				{
					$wpage_gui->setEnableEditing(false);
				}
				
				// alter title and description
//				$tpl->setTitle($wpage_gui->getPageObject()->getTitle());
//				$tpl->setDescription($this->object->getTitle());

				$ret = $this->ctrl->forwardCommand($wpage_gui);
				if ($ret != "")
				{
					$tpl->setContent($ret);
				}
				break;

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				$tpl->setContent($ret);
				break;
				
			case "ilobjstylesheetgui":
				include_once ("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
				$this->ctrl->setReturn($this, "editStyleProperties");
				$style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
				$style_gui->omitLocator();
				if ($cmd == "create" || $_GET["new_type"]=="sty")
				{
					$style_gui->setCreationMode(true);
				}

				if ($cmd == "confirmedDelete")
				{
					$this->object->setStyleSheetId(0);
					$this->object->update();
				}

				$ret = $this->ctrl->forwardCommand($style_gui);

				if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle")
				{
					$style_id = $ret;
					$this->object->setStyleSheetId($style_id);
					$this->object->update();
					$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
				}
				break;
				
			case "ilexportgui":
//				$this->prepareOutput();
				$this->addHeaderAction();
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$exp_gui->addFormat("html", "", $this, "exportHTML");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilratinggui":				
				// for rating category editing
				$this->checkPermission("write");
				$this->addHeaderAction();
				$ilTabs->activateTab("settings");
				$this->setSettingsSubTabs("rating_categories");
				include_once("Services/Rating/classes/class.ilRatingGUI.php");
				$gui = new ilRatingGUI();				
				$gui->setObject($this->object->getId(), $this->object->getType());
				$gui->setExportCallback(array($this, "getSubObjectTitle"), $this->lng->txt("page"));
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilwikistatgui":
				$this->checkPermission("statistics_read");
				
				$this->addHeaderAction();
				$ilTabs->activateTab("statistics");
				
				include_once "Modules/Wiki/classes/class.ilWikiStatGUI.php";
				$gui = new ilWikiStatGUI($this->object->getId());
				$this->ctrl->forwardCommand($gui);
				break;			

			case "ilwikipagetemplategui":
				$this->addHeaderAction();
				$ilTabs->activateTab("settings");
				$this->setSettingsSubTabs("page_templates");
				include_once("./Modules/Wiki/classes/class.ilWikiPageTemplateGUI.php");
				$wptgui = new ilWikiPageTemplateGUI($this);
				$this->ctrl->forwardCommand($wptgui);
				break;

			default:
				$this->addHeaderAction();
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
				$cmd .= "Object";
				if ($cmd != "infoScreenObject")
				{
					$this->checkPermission("read");
				}
				else
				{
					$this->checkPermission("visible");
				}
				$this->$cmd();				
				break;
		}
  
  		return $ret;
	}
	
	/**
	 * Is wiki an online help wiki?
	 *
	 * @return boolean true, if current wiki is an online help wiki
	 */
	function isOnlineHelpWiki()
	{
		if (is_object($this->object))
		{
			return ilObjWiki::isOnlineHelpWiki($this->object->getRefId());
		}
		return false;
	}
	
	/**
	* Start page
	*/
	function viewObject()
	{
		$this->checkPermission("read");
		$this->gotoStartPageObject();
	}

	protected function initCreationForms($a_new_type)
	{
		$this->initSettingsForm("create");
		$this->getSettingsFormValues("create");

		$forms = array(self::CFORM_NEW => $this->form_gui,
			self::CFORM_IMPORT => $this->initImportForm($a_new_type));

		return $forms;
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $tpl, $lng, $rbacsystem;

		if (!$this->checkPermissionBool("create", "", "wiki", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initSettingsForm("create");
		if ($this->form_gui->checkInput())
		{
			if (!ilObjWiki::checkShortTitleAvailability($this->form_gui->getInput("shorttitle")))
			{
				$short_item = $this->form_gui->getItemByPostVar("shorttitle");
				$short_item->setAlert($lng->txt("wiki_short_title_already_in_use"));
			}
			else
			{
				// create and insert forum in objecttree
				$_POST["title"] = $this->form_gui->getInput("title");
				$_POST["desc"] = $this->form_gui->getInput("description");
				return parent::saveObject();
			}
		}
	
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHtml());
	}

	/**
	 * save object
	 * @access	public
	 */
	function afterSave($newObj)
	{
		global $ilSetting;
		
		$newObj->setTitle($this->form_gui->getInput("title"));
		$newObj->setDescription($this->form_gui->getInput("description"));
		$newObj->setIntroduction($this->form_gui->getInput("intro"));
		$newObj->setStartPage($this->form_gui->getInput("startpage"));
		$newObj->setShortTitle($this->form_gui->getInput("shorttitle"));
		$newObj->setRating($this->form_gui->getInput("rating"));
		// $newObj->setRatingAsBlock($this->form_gui->getInput("rating_side"));
		$newObj->setRatingForNewPages($this->form_gui->getInput("rating_new"));
		$newObj->setRatingCategories($this->form_gui->getInput("rating_ext"));

		$newObj->setRatingOverall($this->form_gui->getInput("rating_overall"));
		$newObj->setPageToc($this->form_gui->getInput("page_toc"));



		if (!$ilSetting->get("disable_comments"))
		{
			$newObj->setPublicNotes($this->form_gui->getInput("public_notes"));
		}
		$newObj->setOnline($this->form_gui->getInput("online"));
		$newObj->update();

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect(ilObjWikiGUI::getGotoLink($newObj->getRefId()));
	}

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->checkPermission("visible");
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $ilTabs, $lng;
		
		$ilTabs->activateTab("info_short");

		if (!$ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		if (trim($this->object->getIntroduction()) != "")
		{
			$info->addSection($lng->txt("wiki_introduction"));
			$info->addProperty("", nl2br($this->object->getIntroduction()));
		}
		
		// feedback from tutor; mark, status, comment 
		include_once("./Modules/Wiki/classes/class.ilWikiContributor.php");
		include_once("./Services/Tracking/classes/class.ilLPMarks.php");
		$lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
		$mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
		$status = ilWikiContributor::_lookupStatus($this->object->getId(), $ilUser->getId());
		if ($lpcomment != "" || $mark != "" || $status != ilWikiContributor::STATUS_NOT_GRADED)
		{
			$info->addSection($this->lng->txt("wiki_feedback_from_tutor"));
			if ($lpcomment != "")
			{
				$info->addProperty($this->lng->txt("wiki_comment"),
					$lpcomment);
			}
			if ($mark != "")
			{
				$info->addProperty($this->lng->txt("wiki_mark"),
					$mark);
			}

			if ($status == ilWikiContributor::STATUS_PASSED) 
			{
				$info->addProperty($this->lng->txt("status"),
					$this->lng->txt("wiki_passed"));
			}
			if ($status == ilWikiContributor::STATUS_FAILED) 
			{
				$info->addProperty($this->lng->txt("status"),
					$this->lng->txt("wiki_failed"));
			}
		}
		
		/*
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			//$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}*/
		
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$info->addButton($lng->txt("wiki_start_page"), ilObjWikiGUI::getGotoLink($this->object->getRefId()));
		}
		
		// general information
		$this->lng->loadLanguageModule("meta");
		$this->lng->loadLanguageModule("wiki");

		//$info->addSection($this->lng->txt("meta_general"));
		//$info->addProperty($this->lng->txt("mcst_nr_items"),
		//	(int) count($med_items));

		// forward the command
		$this->ctrl->forwardCommand($info);

		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
//			$this->setSideBlock();
		}
	}
	
	/**
	* Go to start page
	*/
	function gotoStartPageObject()
	{
		global $ilCtrl;
		
		ilUtil::redirect(ilObjWikiGUI::getGotoLink($this->object->getRefId()));
	}

	/**
	* Add Page Tabs
	*/
	function addPageTabs()
	{
		global $ilTabs, $ilCtrl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$ilCtrl->setParameter($this, "wpg_id",
			ilWikiPage::getPageIdForTitle($this->object->getId(), ilWikiUtil::makeDbTitle($_GET["page"])));
		$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($_GET["page"]));
		$ilTabs->addTarget("wiki_what_links_here",
			$this->ctrl->getLinkTargetByClass("ilwikipagegui",
			"whatLinksHere"), "whatLinksHere");
		$ilTabs->addTarget("wiki_print_view",
			$this->ctrl->getLinkTargetByClass("ilwikipagegui",
			"printViewSelection"), "printViewSelection");
	}
	
	/**
	* Add Pages SubTabs
	*/
	function addPagesSubTabs()
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->activateTab("wiki_pages");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$ilCtrl->setParameter($this, "wpg_id",
			ilWikiPage::getPageIdForTitle($this->object->getId(),
				ilWikiUtil::makeDbTitle($_GET["page"])));
		$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($_GET["page"]));
		$ilTabs->addSubTabTarget("wiki_all_pages",
			$this->ctrl->getLinkTarget($this, "allPages"), "allPages");
		$ilTabs->addSubTabTarget("wiki_recent_changes",
			$this->ctrl->getLinkTarget($this, "recentChanges"), "recentChanges");
		$ilTabs->addSubTabTarget("wiki_new_pages",
			$this->ctrl->getLinkTarget($this, "newPages"), "newPages");
		$ilTabs->addSubTabTarget("wiki_popular_pages",
			$this->ctrl->getLinkTarget($this, "popularPages"), "popularPages");
		$ilTabs->addSubTabTarget("wiki_orphaned_pages",
			$this->ctrl->getLinkTarget($this, "orphanedPages"), "orphanedPages");
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs($tabs_gui)
	{
		global $ilCtrl, $ilAccess, $ilTabs, $lng, $ilHelp;
		
		$ilHelp->setScreenIdComponent("wiki");

		
		// wiki tabs
		if (in_array($ilCtrl->getCmdClass(), array("", "ilobjwikigui",
			"ilinfoscreengui", "ilpermissiongui", "ilexportgui", "ilratingcategorygui",
			"ilwikistatgui", "ilwikipagetemplategui"
			)))
		{	
			if ($_GET["page"] != "")
			{
				$tabs_gui->setBackTarget($lng->txt("wiki_last_visited_page"),
					$this->getGotoLink($_GET["ref_id"],
						ilWikiUtil::makeDbTitle($_GET["page"])));
			}
			
			// pages
			if ($ilAccess->checkAccess('read', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("wiki_pages",
					$lng->txt("wiki_pages"),
					$this->ctrl->getLinkTarget($this, "allPages"));
			}
			
			// info screen
			if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("info_short",
					$lng->txt("info_short"),
					$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
			}
	
			// settings
			if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("settings",
					$lng->txt("settings"),
					$this->ctrl->getLinkTarget($this, "editSettings"));
			}			

			// contributors
			if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("wiki_contributors",
					$lng->txt("wiki_contributors"),
					$this->ctrl->getLinkTarget($this, "listContributors"));
			}

			// statistics
			if ($ilAccess->checkAccess('statistics_read', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("statistics",
					$lng->txt("statistics"),
					$this->ctrl->getLinkTargetByClass("ilWikiStatGUI", "initial"));
			}

			if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
			{
				$ilTabs->addTab("export",
					$lng->txt("export"),
					$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
			}
		
			// edit permissions
			if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
			{
				$ilTabs->addTab("perm_settings",
					$lng->txt("perm_settings"),
					$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
			}
		}
	}

	/**
	* Set sub tabs
	*/
	function setSettingsSubTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;

		if (in_array($a_active,
			array("general_settings", "style", "imp_pages", "rating_categories",
			"page_templates")))
		{
			// general properties
			$ilTabs->addSubTab("general_settings",
				$lng->txt("wiki_general_settings"),
				$ilCtrl->getLinkTarget($this, 'editSettings'));
				
			// style properties
			$ilTabs->addSubTab("style",
				$lng->txt("wiki_style"),
				$ilCtrl->getLinkTarget($this, 'editStyleProperties'));

			// important pages
			$ilTabs->addSubTab("imp_pages",
				$lng->txt("wiki_navigation"),
				$ilCtrl->getLinkTarget($this, 'editImportantPages'));

			// page templates
			$ilTabs->addSubTab("page_templates",
				$lng->txt("wiki_page_templates"),
				$ilCtrl->getLinkTargetByClass("ilwikipagetemplategui", ""));

			// rating categories
			if($this->object->getRating() && $this->object->getRatingCategories())
			{
				$lng->loadLanguageModule("rating");
				$ilTabs->addSubTab("rating_categories",
					$lng->txt("rating_categories"),
					$ilCtrl->getLinkTargetByClass(array('ilratinggui', 'ilratingcategorygui'), ''));				
			}
			
			$ilTabs->activateSubTab($a_active);
		}
	}

	/**
	* Edit settings
	*/
	function editSettingsObject()
	{
		global $tpl;
		
		$this->checkPermission("write");
		
		$this->setSettingsSubTabs("general_settings");
		
		$this->initSettingsForm();
		$this->getSettingsFormValues();
		
		// Edit ecs export settings
		include_once 'Modules/Wiki/classes/class.ilECSWikiSettings.php';
		$ecs = new ilECSWikiSettings($this->object);		
		$ecs->addSettingsToForm($this->form_gui, 'wiki');			
		
		$tpl->setContent($this->form_gui->getHtml());
		$this->setSideBlock();
	}
	
	/**
	* Init Settings Form
	*/
	function initSettingsForm($a_mode = "edit")
	{
		global $lng, $ilCtrl, $ilTabs, $ilSetting;
		
		$lng->loadLanguageModule("wiki");
		$ilTabs->activateTab("settings");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		// Title
		$tit = new ilTextInputGUI($lng->txt("title"), "title");
		$tit->setRequired(true);
		$this->form_gui->addItem($tit);

		// Short Title
		// The problem with the short title is, that it is per object
		// and can't be a substitute for a ref id in the permanent link
/*		
		$stit = new ilRegExpInputGUI($lng->txt("wiki_short_title"), "shorttitle");
		$stit->setPattern("/^[^0-9][^ _\&]+$/");
		$stit->setRequired(false);
		$stit->setNoMatchMessage($lng->txt("wiki_msg_short_name_regexp")." &amp; _");
		$stit->setSize(20);
		$stit->setMaxLength(20);
		$stit->setInfo($lng->txt("wiki_short_title_desc2"));
		$this->form_gui->addItem($stit);
*/

		// Description
		$des = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$this->form_gui->addItem($des);

		// Introduction
		$intro = new ilTextAreaInputGUI($lng->txt("wiki_introduction"), "intro");
		$intro->setCols(40);
		$intro->setRows(4);
		$this->form_gui->addItem($intro);

		// Start Page
		if ($a_mode == "edit")
		{
			$pages = ilWikiPage::getAllPages($this->object->getId());
			foreach ($pages as $p)
			{
				$options[$p["id"]] = ilUtil::shortenText($p["title"], 60, true);
			}
			$si = new ilSelectInputGUI($lng->txt("wiki_start_page"), "startpage_id");
			$si->setOptions($options);
			$this->form_gui->addItem($si);
		}
		else
		{
			$sp = new ilTextInputGUI($lng->txt("wiki_start_page"), "startpage");
			if ($a_mode == "edit")
			{
				$sp->setInfo($lng->txt("wiki_start_page_info"));
			}
			$sp->setMaxLength(200);
			$sp->setRequired(true);
			$this->form_gui->addItem($sp);
		}

		// Online
		$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
		$this->form_gui->addItem($online);

		
		// rating		
		
		$lng->loadLanguageModule('rating');
		$rate = new ilCheckboxInputGUI($lng->txt('rating_activate_rating'), 'rating_overall');
		$rate->setInfo($lng->txt('rating_activate_rating_info'));
		$this->form_gui->addItem($rate);
				
		$rating = new ilCheckboxInputGUI($lng->txt("wiki_activate_rating"), "rating");
		$this->form_gui->addItem($rating);
		
		/* always active 
		$side = new ilCheckboxInputGUI($lng->txt("wiki_activate_sideblock_rating"), "rating_side");
		$rating->addSubItem($side);
		*/ 
		
		$new = new ilCheckboxInputGUI($lng->txt("wiki_activate_new_page_rating"), "rating_new");
		$rating->addSubItem($new);
		
		$extended = new ilCheckboxInputGUI($lng->txt("wiki_activate_extended_rating"), "rating_ext");
		$rating->addSubItem($extended);
		

		// public comments
		if (!$ilSetting->get("disable_comments"))
		{
			$comments = new ilCheckboxInputGUI($lng->txt("wiki_public_comments"), "public_notes");
			$this->form_gui->addItem($comments);
		}

		// important pages
//		$imp_pages = new ilCheckboxInputGUI($lng->txt("wiki_important_pages"), "imp_pages");
//		$this->form_gui->addItem($imp_pages);

		// page toc
		$page_toc = new ilCheckboxInputGUI($lng->txt("wiki_page_toc"), "page_toc");
		$page_toc->setInfo($lng->txt("wiki_page_toc_info"));
		$this->form_gui->addItem($page_toc);
		
		if($a_mode == "edit")
		{
			// advanced metadata
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
			$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_REC_SELECTION,'wiki',$this->object->getId(), "wpg");
			$record_gui->setPropertyForm($this->form_gui);
			$record_gui->parseRecordSelection($this->lng->txt("wiki_add_page_properties"));
		}
		
		// :TODO: sorting

		// Form action and save button
		$this->form_gui->setTitleIcon(ilUtil::getImagePath("icon_wiki.svg"));
		if ($a_mode != "create")
		{			
			$this->form_gui->setTitle($lng->txt("wiki_settings"));
			$this->form_gui->addCommandButton("saveSettings", $lng->txt("save"));
		}
		else
		{
			$this->form_gui->setTitle($lng->txt("wiki_new"));
			$this->form_gui->addCommandButton("save", $lng->txt("wiki_add"));
			$this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		}
		
		// set values
		if ($a_mode == "create")
		{
			$ilCtrl->setParameter($this, "new_type", "wiki");
		}

		$this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveSettings"));
	}
	
	function getSettingsFormValues($a_mode = "edit")
	{
		global $lng, $ilUser;
		
		// set values
		if ($a_mode == "create")
		{
			//$values["startpage"] = $lng->txt("wiki_main_page");
			$values["rating_new"] = true;
			
			$values["rating_overall"] = ilObject::hasAutoRating("wiki", $_GET["ref_id"]);
			
			$this->form_gui->setValuesByArray($values);
		}
		else
		{
			$values["online"] = $this->object->getOnline();
			$values["title"] = $this->object->getTitle();
			//$values["startpage"] = $this->object->getStartPage();
			$values["startpage_id"] = ilWikiPage::_getPageIdForWikiTitle($this->object->getId(), $this->object->getStartPage());
			$values["shorttitle"] = $this->object->getShortTitle();
			$values["description"] = $this->object->getLongDescription();
			$values["rating_overall"] = $this->object->getRatingOverall();
			$values["rating"] = $this->object->getRating();
			// $values["rating_side"] = $this->object->getRatingAsBlock();
			$values["rating_new"] = $this->object->getRatingForNewPages();
			$values["rating_ext"] = $this->object->getRatingCategories();
			$values["public_notes"] = $this->object->getPublicNotes();
			$values["intro"] = $this->object->getIntroduction();
//			$values["imp_pages"] = $this->object->getImportantPages();
			$values["page_toc"] = $this->object->getPageToc();
						
			// only set given values (because of adv. metadata)
			$this->form_gui->setValuesByArray($values, true);
		}
	}
	
	
	/**
	* Save Settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl, $lng, $ilUser, $ilSetting;
		
		$this->checkPermission("write");
		
		$this->initSettingsForm();
		
		if ($this->form_gui->checkInput())
		{
			if (!ilObjWiki::checkShortTitleAvailability($this->form_gui->getInput("shorttitle")) &&
				$this->form_gui->getInput("shorttitle") != $this->object->getShortTitle())
			{
				$short_item = $this->form_gui->getItemByPostVar("shorttitle");
				$short_item->setAlert($lng->txt("wiki_short_title_already_in_use"));
			}
			else
			{
				$this->object->setTitle($this->form_gui->getInput("title"));
				$this->object->setDescription($this->form_gui->getInput("description"));
				$this->object->setOnline($this->form_gui->getInput("online"));
				$this->object->setStartPage(ilWikiPage::lookupTitle($this->form_gui->getInput("startpage_id")));
				$this->object->setShortTitle($this->form_gui->getInput("shorttitle"));
				$this->object->setRatingOverall($this->form_gui->getInput("rating_overall"));
				$this->object->setRating($this->form_gui->getInput("rating"));
				// $this->object->setRatingAsBlock($this->form_gui->getInput("rating_side"));
				$this->object->setRatingForNewPages($this->form_gui->getInput("rating_new"));
				$this->object->setRatingCategories($this->form_gui->getInput("rating_ext"));
				
				if (!$ilSetting->get("disable_comments"))
				{
					$this->object->setPublicNotes($this->form_gui->getInput("public_notes"));
				}
				$this->object->setIntroduction($this->form_gui->getInput("intro"));
//				$this->object->setImportantPages($this->form_gui->getInput("imp_pages"));
				$this->object->setPageToc($this->form_gui->getInput("page_toc"));
				$this->object->update();
				
				// update metadata record selection
				include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
				$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_REC_SELECTION,'wiki',$this->object->getId(), "wpg");
				$record_gui->saveSelection();
				
				// Update ecs export settings
				include_once 'Modules/Wiki/classes/class.ilECSWikiSettings.php';	
				$ecs = new ilECSWikiSettings($this->object);			
				if($ecs->handleSettingsUpdate())
				{
					ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
					$ilCtrl->redirect($this, "editSettings");
				}											
			}
		}
		
		$this->form_gui->setValuesByPost();
		$this->tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* List all contributors
	*/
	function listContributorsObject()
	{
		global $tpl, $ilTabs;
		
		$this->checkPermission("write");
		$ilTabs->activateTab("wiki_contributors");
		
		include_once("./Modules/Wiki/classes/class.ilWikiContributorsTableGUI.php");
		
		$table_gui = new ilWikiContributorsTableGUI($this, "listContributors",
			$this->object->getId());

		$tpl->setContent($table_gui->getHTML());
		
		$this->setSideBlock();
	}
	
	/**
	* Save grading
	*/
	function saveGradingObject()
	{
		global $ilCtrl, $lng;
		
		$this->checkPermission("write");
		
		$users = (is_array($_POST["user_id"])
				? $_POST["user_id"]
				: array());
		
		include_once("./Modules/Wiki/classes/class.ilWikiContributor.php");
		include_once("./Services/Tracking/classes/class.ilLPMarks.php");
		$saved = false;
		foreach($users as $user_id)
		{
			if ($user_id != "")
			{
				$marks_obj = new ilLPMarks($this->object->getId(),$user_id);
				$new_mark = ilUtil::stripSlashes($_POST['mark'][$user_id]);
				$new_comment = ilUtil::stripSlashes($_POST['lcomment'][$user_id]);
				$new_status = ilUtil::stripSlashes($_POST["status"][$user_id]);

				if ($marks_obj->getMark() != $new_mark ||
					$marks_obj->getComment() != $new_comment ||
					ilWikiContributor::_lookupStatus($this->object->getId(), $user_id) != $new_status)
				{
					ilWikiContributor::_writeStatus($this->object->getId(), $user_id, $new_status);
					$marks_obj->setMark($new_mark);
					$marks_obj->setComment($new_comment);
					$marks_obj->update();
					$saved = true;
				}
			}
		}
		if ($saved)
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		
		$ilCtrl->redirect($this, "listContributors");
	}
	
	// add wiki to locator
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(),
				$this->getGotoLink($this->object->getRefId()), "", $_GET["ref_id"]);
		}
	}

	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng, $ilNavigationHistory;
		
		$i = strpos($a_target, "_");
		if ($i > 0)
		{
			$a_page = substr($a_target, $i+1);
			$a_target = substr($a_target, 0, $i);
		}
		
		if ($a_target == "wpage")
		{
			$a_page_arr = explode("_", $a_page);
			$wpg_id = (int) $a_page_arr[0];
			$ref_id = (int) $a_page_arr[1];
			include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
			$w_id = ilWikiPage::lookupWikiId($wpg_id);
			if ($ref_id > 0)
			{
				$refs = array($ref_id);
			}
			else
			{
				$refs = ilObject::_getAllReferences($w_id);
			}
			foreach ($refs as $r)
			{
				if ($ilAccess->checkAccess("read", "", $r))
				{
					$a_target = $r;
					$a_page = ilWikiPage::lookupTitle($wpg_id);
				}
			}
		}
			
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "viewPage";
			$_GET["ref_id"] = $a_target;
			$_GET["page"] = $a_page;
			$_GET["baseClass"] = "ilwikihandlergui";
			$_GET["cmdClass"] = "ilobjwikigui";
/*			if ($a_page != "")
			{
				$add = "&amp;page=".rawurlencode($_GET["page"]);
				$ilNavigationHistory->addItem($_GET["ref_id"],
					"./goto.php?target=wiki_".$_GET["ref_id"].$add, "wiki");
			}*/
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($tarr[0], "infoScreen");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($tarr[0]))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	/**
	* Get goto link
	*/
	static function getGotoLink($a_ref_id, $a_page = "")
	{
		if ($a_page == "")
		{
			$a_page = ilObjWiki::_lookupStartPage(ilObject::_lookupObjId($a_ref_id));
		}
		
		$goto = "goto.php?target=wiki_".$a_ref_id."_".
			ilWikiUtil::makeUrlTitle($a_page);
			
		return $goto;
	}
	
	/**
	* view wiki page
	*/
	function viewPageObject()
	{
		global $lng, $ilCtrl, $tpl, $ilTabs, $ilAccess;
		
		$this->checkPermission("read");

		$ilTabs->clearTargets();
		$tpl->setHeaderActionMenu(null);

		$page = ($_GET["page"] != "")
			? $_GET["page"]
			: $this->object->getStartPage();
		$_GET["page"] = $page;
			
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		if (!ilWikiPage::exists($this->object->getId(), $page))
		{
			$page = $this->object->getStartPage();
		}
		
		if (!ilWikiPage::exists($this->object->getId(), $page))
		{
			ilUtil::sendInfo($lng->txt("wiki_no_start_page"), true);
			$ilCtrl->redirect($this, "infoScreen");
			return;
		}
		
		// page exists, show it !
		$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($page));
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
			ilWikiUtil::makeDbTitle($page), 0, $this->object->getRefId());
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$wpage_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
			$this->object->getStyleSheetId(), "wiki"));

		$this->setContentStyleSheet();
		//$wpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		
		//$wpage_gui->setSideBlock();
		$ilCtrl->setCmdClass("ilwikipagegui");
		$ilCtrl->setCmd("preview");
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
			(!$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()) ||
				$wpage_gui->getPageObject()->getBlocked()
			))
		{
			$wpage_gui->setEnableEditing(false);
		}

		// alter title and description
		//$tpl->setTitle($wpage_gui->getPageObject()->getTitle());
		//$tpl->setDescription($this->object->getTitle());

		
		$html = $ilCtrl->forwardCommand($wpage_gui);
		//$this->addPageTabs();
		
		$tpl->setContent($html);
	}
		
	/**
	* All pages of wiki
	*/
	function allPagesObject()
	{
		global $tpl;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		
		$table_gui = new ilWikiPagesTableGUI($this, "allPages",
			$this->object->getId(), IL_WIKI_ALL_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}
	
	/**
	* Popular pages
	*/
	function popularPagesObject()
	{
		global $tpl;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		
		$table_gui = new ilWikiPagesTableGUI($this, "popularPages",
			$this->object->getId(), IL_WIKI_POPULAR_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

	/**
	* Orphaned pages
	*/
	function orphanedPagesObject()
	{
		global $tpl;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		
		$table_gui = new ilWikiPagesTableGUI($this, "orphanedPages",
			$this->object->getId(), IL_WIKI_ORPHANED_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

	/**
	* Go to specific page
	*
	* @param	string	$a_page		page title
	*/
	function gotoPageObject($a_page = "")
	{
		global $ilCtrl;
		
		if ($a_page == "")
		{
			$a_page = $_GET["page"];
		}
		
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		if (ilWikiPage::_wikiPageExists($this->object->getId(),
			ilWikiUtil::makeDbTitle($a_page)))
		{
			// to do: get rid of this redirect
			ilUtil::redirect(ilObjWikiGUI::getGotoLink($this->object->getRefId(), $a_page));
		}
		else
		{
			if (!$this->object->getTemplateSelectionOnCreation())
			{
				$this->object->createWikiPage($a_page);

				// redirect to newly created page
				$ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($a_page)));
				$ilCtrl->redirectByClass("ilwikipagegui", "edit");
			}
			else
			{
				$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($_GET["page"]));
				$ilCtrl->setParameter($this, "from_page", ilWikiUtil::makeUrlTitle($_GET["from_page"]));
				$ilCtrl->redirect($this, "showTemplateSelection");
			}
		}
	}

	/**
	* Go to random page
	*
	* @param	string	$a_page		page title
	*/
	function randomPageObject()
	{
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$page = ilWikiPage::getRandomPage($this->object->getId());
		$this->gotoPageObject($page);
	}

	/**
	* Recent Changes
	*/
	function recentChangesObject()
	{
		global $tpl;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiRecentChangesTableGUI.php");
		
		$this->addPagesSubTabs();
		
		$table_gui = new ilWikiRecentChangesTableGUI($this, "recentChanges",
			$this->object->getId());
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

	/**
	 * Side column
	 */
	function setSideBlock($a_wpg_id = 0)
	{
		ilObjWikiGUI::renderSideBlock($a_wpg_id, $this->object->getRefId());
	}


	/**
	 * Side column
	 */
	static function renderSideBlock($a_wpg_id, $a_wiki_ref_id, $a_wp = null)
	{
		global $tpl, $lng;

		if ($a_wpg_id > 0 && !$a_wp)
		{
			include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
			$a_wp = ilWikiPage($a_wpg_id);
		}

		// search block
		include_once("./Modules/Wiki/classes/class.ilWikiSearchBlockGUI.php");
		$wiki_search_block = new ilWikiSearchBlockGUI();
		$rcontent = $wiki_search_block->getHTML();

		// quick navigation
		if ($a_wpg_id > 0)
		{
//			include_once("./Modules/Wiki/classes/class.ilWikiSideBlockGUI.php");
//			$wiki_side_block = new ilWikiSideBlockGUI();
//			$wiki_side_block->setPageObject($a_wp);
//			$rcontent.= $wiki_side_block->getHTML();
			
			// rating
			$wiki_id =ilObject::_lookupObjId($a_wiki_ref_id);			
			if(ilObjWiki::_lookupRating($wiki_id) && 
				// ilObjWiki::_lookupRatingAsBlock($wiki_id) &&
				$a_wp->getRating())
			{
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rgui = new ilRatingGUI();
				$rgui->setObject($wiki_id, "wiki", $a_wpg_id, "wpg");
				$rgui->enableCategories(ilObjWiki::_lookupRatingCategories($wiki_id));
				$rgui->setYourRatingText("#");
				$rcontent .= $rgui->getBlockHTML($lng->txt("wiki_rate_page"));
			}
		
			// advanced metadata
			include_once("./Modules/Wiki/classes/class.ilWikiAdvMetaDataBlockGUI.php");			
			if(ilWikiAdvMetaDataBlockGUI::isActive($wiki_id))
			{				
				$rec_ids = ilWikiAdvMetaDataBlockGUI::getRecords($wiki_id);
				foreach($rec_ids as $record)
				{				
					$advmd_pages_block = new ilWikiAdvMetaDataBlockGUI($record);
					$advmd_pages_block->setObject($a_wiki_ref_id, $wiki_id, $a_wpg_id);
					$rcontent.= $advmd_pages_block->getHTML();
				}
			}
		}
			
		// important pages
//		if (ilObjWiki::_lookupImportantPages(ilObject::_lookupObjId($a_wiki_ref_id)))
//		{
			include_once("./Modules/Wiki/classes/class.ilWikiImportantPagesBlockGUI.php");
			$imp_pages_block = new ilWikiImportantPagesBlockGUI();
			$rcontent.= $imp_pages_block->getHTML();
//		}

		// wiki functions block
		if ($a_wpg_id > 0)
		{
			include_once("./Modules/Wiki/classes/class.ilWikiFunctionsBlockGUI.php");
			$wiki_functions_block = new ilWikiFunctionsBlockGUI();
			$wiki_functions_block->setPageObject($a_wp);
			$rcontent .= $wiki_functions_block->getHTML();			
		}

		$tpl->setRightContent($rcontent);
	}

	/**
	* Latest pages
	*/
	function newPagesObject()
	{
		global $tpl;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		
		$table_gui = new ilWikiPagesTableGUI($this, "newPages",
			$this->object->getId(), IL_WIKI_NEW_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}
	
	protected function getPrintPageIds()
	{						
		// multiple ordered page ids
		if(is_array($_POST["wordr"]))
		{
			asort($_POST["wordr"]);			
			$page_ids = array_keys($_POST["wordr"]);	
		}
		// single page
		else if((int)$_GET["wpg_id"])
		{
			$page_ids = array((int)$_GET["wpg_id"]);
		}
		
		return $page_ids;
	}
	
	public function printViewObject($a_pdf_export = false)
	{
		global $tpl;
		
		$page_ids = $this->getPrintPageIds();
		if(!$page_ids)
		{
			$this->ctrl->redirect($this, "");
		}		
								
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());				
		$this->setContentStyleSheet($tpl);

		// syntax style
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();


		// determine target frames for internal links
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		
		$page_content = "";

		foreach ($page_ids as $p_id)
		{
			$page_gui = new ilWikiPageGUI($p_id);
			$page_gui->setOutputMode("print");
			$page_content.= $page_gui->showPage();
			
			if($a_pdf_export)
			{
				$page_content .= '<p style="page-break-after:always;"></p>';
			}
		}
		
		$page_content = '<div class="ilInvisibleBorder">'.$page_content.'</div>';
		
		if(!$a_pdf_export)
		{
			$page_content .= '<script type="text/javascript" language="javascript1.2">
				<!--
					il.Util.addOnLoad(function () {
						il.Util.print();
					});
				//-->
				</script>';
		}
		
		$tpl->setVariable("CONTENT", $page_content);
		
		if(!$a_pdf_export)
		{
			$tpl->show(false);
			exit;		
		}
		else
		{			
			return $tpl->get("DEFAULT", false, false, false, true, false, false);
		}
	}
	
	public function pdfExportObject()
	{
		$html = $this->printViewObject(true);	
		
		// :TODO: fixing css dummy parameters
		$html = preg_replace("/\?dummy\=[0-9]+/", "", $html);
		$html = preg_replace("/\?vers\=[0-9A-Za-z\-]+/", "", $html);
		
		include_once "Services/PDFGeneration/classes/class.ilPDFGeneration.php";
		include_once "Services/PDFGeneration/classes/class.ilPDFGenerationJob.php";
		
		$job = new ilPDFGenerationJob();
		$job->setAutoPageBreak(true)
			->setMarginLeft("10")
			->setMarginRight("10")
			->setMarginTop("10")
			->setMarginBottom("10")
			->setOutputMode("D") // download
			->setFilename("wiki.pdf") // :TODO:
			->setCreator("ILIAS Wiki") // :TODO:
			->setImageScale(1.25) // complete content scaling ?!
			->addPage($html);
		
		ilPDFGeneration::doJob($job);
	}	

	/**
	* Search
	*/
	function performSearchObject()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng;
		
		$this->checkPermission("read");
		
		include_once("./Modules/Wiki/classes/class.ilWikiSearchResultsTableGUI.php");
		
		$ilTabs->setTabActive("wiki_search_results");
		
		if (trim($_POST["search_term"]) == "")
		{
			ilUtil::sendFailure($lng->txt("wiki_please_enter_search_term"), true);
			$ilCtrl->redirectByClass("ilwikipagegui", "preview");
		}
		
		$search_results = ilObjWiki::_performSearch($this->object->getId(),
			ilUtil::stripSlashes($_POST["search_term"]));
		$table_gui = new ilWikiSearchResultsTableGUI($this, "performSearch",
			$this->object->getId(), $search_results, $_POST["search_term"]);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

	/**
	* Set content style sheet
	*/
	function setContentStyleSheet($a_tpl = null)
	{
		global $tpl;

		if ($a_tpl != null)
		{
			$ctpl = $a_tpl;
		}
		else
		{
			$ctpl = $tpl;
		}

		$ctpl->setCurrentBlock("ContentStyle");
		$ctpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
		$ctpl->parseCurrentBlock();

	}
	
	
	/**
	* Edit style properties
	*/
	function editStylePropertiesObject()
	{
		global $ilTabs, $tpl;
		
		$this->checkPermission("write");
		
		$this->initStylePropertiesForm();
		$tpl->setContent($this->form->getHTML());
		
		$ilTabs->activateTab("settings");
		$this->setSettingsSubTabs("style");
		
		$this->setSideBlock();
	}
	
	/**
	* Init style properties form
	*/
	function initStylePropertiesForm()
	{
		global $ilCtrl, $lng, $ilTabs, $ilSetting;
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$lng->loadLanguageModule("style");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$fixed_style = $ilSetting->get("fixed_content_style_id");
		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
			$st->setValue(ilObject::_lookupTitle($fixed_style)." (".
				$this->lng->txt("global_fixed").")");
			$this->form->addItem($st);
		}
		else
		{
			$st_styles = ilObjStyleSheet::_getStandardStyles(true, false,
				$_GET["ref_id"]);

			$st_styles[0] = $this->lng->txt("default");
			ksort($st_styles);

			if ($style_id > 0)
			{
				// individual style
				if (!ilObjStyleSheet::_lookupStandard($style_id))
				{
					$st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$this->form->addItem($st);

//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));

					// delete command
					$this->form->addCommandButton("editStyle",
						$lng->txt("style_edit_style"));
					$this->form->addCommandButton("deleteStyle",
						$lng->txt("style_delete_style"));
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
					$st_styles, false, true);
				$style_sel = new ilSelectInputGUI($lng->txt("style_current_style"), "style_id");
				$style_sel->setOptions($st_styles);
				$style_sel->setValue($style_id);
				$this->form->addItem($style_sel);
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "create"));
				$this->form->addCommandButton("saveStyleSettings",
						$lng->txt("save"));
				$this->form->addCommandButton("createStyle",
					$lng->txt("sty_create_ind_style"));
			}
		}
		$this->form->setTitle($lng->txt("wiki_style"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Create Style
	*/
	function createStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
	}
	
	/**
	* Edit Style
	*/
	function editStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
	}

	/**
	* Delete Style
	*/
	function deleteStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "delete");
	}

	/**
	* Save style settings
	*/
	function saveStyleSettingsObject()
	{
		global $ilSetting;
	
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		if ($ilSetting->get("fixed_content_style_id") <= 0 &&
			(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
			|| $this->object->getStyleSheetId() == 0))
		{
			$this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "editStyleProperties");
	}

	//
	// Important pages
	//

	/**
	 * List important pages
	 */
	function editImportantPagesObject()
	{
		global $tpl, $ilToolbar, $ilTabs, $lng, $ilCtrl;

		$this->checkPermission("write");

		ilUtil::sendInfo($lng->txt("wiki_navigation_info"));
		
		$ipages = ilObjWiki::_lookupImportantPagesList($this->object->getId());
		$ipages_ids = array();
		foreach ($ipages as $i)
		{
			$ipages_ids[] = $i["page_id"];
		}

		// list pages
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$pages = ilWikiPage::getAllPages($this->object->getId());
		$options = array("" => $lng->txt("please_select"));
		foreach ($pages as $p)
		{
			if (!in_array($p["id"], $ipages_ids))
			{
				$options[$p["id"]] = ilUtil::shortenText($p["title"], 60, true);
			}
		}
		if (count($options) > 0)
		{
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($lng->txt("wiki_pages"), "imp_page_id");
			$si->setOptions($options);
			$si->setInfo($lng->txt(""));
			$ilToolbar->addInputItem($si);
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
			$ilToolbar->addFormButton($lng->txt("add"), "addImportantPage");
		}


		$ilTabs->activateTab("settings");
		$this->setSettingsSubTabs("imp_pages");

		include_once("./Modules/Wiki/classes/class.ilImportantPagesTableGUI.php");
		$imp_table = new ilImportantPagesTableGUI($this, "editImportantPages");

		$tpl->setContent($imp_table->getHTML());
	}

	/**
	 * Add important pages
	 *
	 * @param
	 * @return
	 */
	function addImportantPageObject()
	{
		global $ilCtrl, $lng;

		$this->checkPermission("write");

		if ($_POST["imp_page_id"] > 0)
		{
			$this->object->addImportantPage((int) $_POST["imp_page_id"]);
			ilUtil::sendSuccess($lng->txt("wiki_imp_page_added"), true);
		}
		$ilCtrl->redirect($this, "editImportantPages");
	}

	/**
	 * Confirm important pages deletion
	 */
	function confirmRemoveImportantPagesObject()
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["imp_page_id"]) || count($_POST["imp_page_id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editImportantPages");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("wiki_sure_remove_imp_pages"));
			$cgui->setCancel($lng->txt("cancel"), "editImportantPages");
			$cgui->setConfirm($lng->txt("remove"), "removeImportantPages");

			foreach ($_POST["imp_page_id"] as $i)
			{
				$cgui->addItem("imp_page_id[]", $i, ilWikiPage::lookupTitle((int) $i));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Remove important pages
	 *
	 * @param
	 * @return
	 */
	function removeImportantPagesObject()
	{
		global $ilCtrl, $lng;

		$this->checkPermission("write");

		if (is_array($_POST["imp_page_id"]))
		{
			foreach ($_POST["imp_page_id"] as $i)
			{
				$this->object->removeImportantPage((int) $i);
			}
		}
		ilUtil::sendSuccess($lng->txt("wiki_removed_imp_pages"), true);
		$ilCtrl->redirect($this, "editImportantPages");
	}

	/**
	 * Save important pages ordering and indentation
	 */
	function saveOrderingAndIndentObject()
	{
		global $ilCtrl, $lng;

		$this->checkPermission("write");

		$this->object->saveOrderingAndIndentation($_POST["ord"], $_POST["indent"]);
		ilUtil::sendSuccess($lng->txt("wiki_ordering_and_indent_saved"), true);
		$ilCtrl->redirect($this, "editImportantPages");
	}

	/**
	 * Confirm important pages deletion
	 */
	function setAsStartPageObject()
	{
		global $ilCtrl, $lng;

		$this->checkPermission("write");

		if (!is_array($_POST["imp_page_id"]) || count($_POST["imp_page_id"]) != 1)
		{
			ilUtil::sendInfo($lng->txt("wiki_select_one_item"), true);
		}
		else
		{
			$this->object->removeImportantPage((int) $_POST["imp_page_id"][0]);
			$this->object->setStartPage(ilWikiPage::lookupTitle((int) $_POST["imp_page_id"][0]));
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		}
		$ilCtrl->redirect($this, "editImportantPages");
	}


	/**
	 * Create html package
	 */
	function exportHTML()
	{
		require_once("./Modules/Wiki/classes/class.ilWikiHTMLExport.php");
		$cont_exp = new ilWikiHTMLExport($this);
		$cont_exp->buildExportFile();
	}
	
	/**
	 * Get title for wiki page (used in ilNotesGUI)
	 * 
	 * @param int $a_wiki_id
	 * @param int $a_page_id 
	 * @return string
	 */
	static function lookupSubObjectTitle($a_wiki_id, $a_page_id)
	{
		include_once "Modules/Wiki/classes/class.ilWikiPage.php";
		$page = new ilWikiPage($a_page_id);
		if($page->getWikiId() == $a_wiki_id)
		{
			return $page->getTitle();
		}
	}
	
	/**
	 * Used for rating export
	 * 
	 * @param int $a_id
	 * @param string $a_type
	 * @return string
	 */
	function getSubObjectTitle($a_id, $a_type)
	{
		include_once "Modules/Wiki/classes/class.ilWikiPage.php";
		return ilWikiPage::lookupTitle($a_id);		
	}

	/**
	 * Show template selection
	 */
	function showTemplateSelectionObject()
	{
		global $lng, $tpl, $ilTabs, $ilCtrl;


		$ilCtrl->setParameterByClass("ilobjwikigui", "from_page", ilWikiUtil::makeUrlTitle($_GET["from_page"]));
		$ilTabs->clearTargets();
		ilUtil::sendInfo($lng->txt("wiki_page_not_exist_select_templ"));

		$form = $this->initTemplateSelectionForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init template selection form.
	 */
	public function initTemplateSelectionForm()
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// page name
		$hi = new ilHiddenInputGUI("page");
		$hi->setValue($_GET["page"]);
		$form->addItem($hi);

		// page template
		$radg = new ilRadioGroupInputGUI($lng->txt("wiki_page_template"), "page_templ");
		$radg->setRequired(true);

		if ($this->object->getEmptyPageTemplate())
		{
			$op1 = new ilRadioOption($lng->txt("wiki_empty_page"), 0);
			$radg->addOption($op1);
		}

		include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
		$wt = new ilWikiPageTemplate($this->object->getId());
		$ts = $wt->getAllInfo(ilWikiPageTemplate::TYPE_NEW_PAGES);
		foreach ($ts as $t)
		{
			$op = new ilRadioOption($t["title"], $t["wpage_id"]);
			$radg->addOption($op);
		}

		$form->addItem($radg);

		// save and cancel commands
		$form->addCommandButton("createPageUsingTemplate", $lng->txt("wiki_create_page"));
		$form->addCommandButton("cancelCreationPageUsingTemplate", $lng->txt("cancel"));

		$form->setTitle($lng->txt("wiki_new_page").": ".$_GET["page"]);
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save creation with template form
	 */
	public function createPageUsingTemplateObject()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initTemplateSelectionForm();
		if ($form->checkInput())
		{
			$a_page = $_POST["page"];
			$this->object->createWikiPage($a_page, (int) $_POST["page_templ"]);

			// redirect to newly created page
			$ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($a_page)));
			$ilCtrl->redirectByClass("ilwikipagegui", "edit");

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

	/**
	 * Cancel page creation using a template
	 */
	function cancelCreationPageUsingTemplateObject()
	{
		global $ilCtrl;

		// redirect to newly created page
		$ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($_GET["from_page"])));
		$ilCtrl->redirectByClass("ilwikipagegui", "preview");
	}


}

?>