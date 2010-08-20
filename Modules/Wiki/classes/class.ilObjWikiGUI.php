<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";
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
* @ilCtrl_Calls ilObjWikiGUI: ilExportGUI
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
		
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->checkPermission("visible");
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				$ilTabs->activateTab("perm_settings");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			case 'ilwikipagegui':
				include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
				$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
					ilWikiUtil::makeDbTitle($_GET["page"]), $_GET["old_nr"], $this->object->getRefId());
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$wpage_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "wiki"));
				$this->setContentStyleSheet();

				if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
					!$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()))
				{
					$wpage_gui->setEnableEditing(false);
				}
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
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;


			default:
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
	* Start page
	*/
	function viewObject()
	{
		$this->checkPermission("read");
		$this->gotoStartPageObject();
	}
	
	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			global $tpl;
			
			$this->initSettingsForm("create");
			$this->getSettingsFormValues("create");
			$html1 = $this->form_gui->getHtml();

			$this->initImportForm("wiki");
			$html2 = $this->form->getHTML();

			$tpl->setContent($html1."<br/><br/>".$html2);
		}
	}

	/**
	 * Init object import form
	 *
	 * @param        string        new type
	 */
	public function initImportForm($a_new_type = "")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");

		// Import file
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("import_file"), "importfile");
		$fi->setSuffixes(array("zip"));
		$this->form->addItem($fi);

		$this->form->addCommandButton("importFile", $lng->txt("import"));
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));
		$this->form->setTitle($lng->txt($a_new_type."_import"));

		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Import
	 *
	 * @access	public
	 */
	function importFileObject()
	{
		global $rbacsystem, $objDefinition, $tpl, $lng;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->initImportForm($new_type);
		if ($this->form->checkInput())
		{
			// todo: make some check on manifest file
			include_once("./Services/Export/classes/class.ilImport.php");
			$imp = new ilImport((int) $_GET['ref_id']);
			$new_id = $imp->importObject($newObj, $_FILES["importfile"]["tmp_name"],
				$_FILES["importfile"]["name"], $new_type);

			// put new object id into tree
			if ($new_id > 0)
			{
				$newObj = ilObjectFactory::getInstanceByObjId($new_id);
				$newObj->createReference();
				$newObj->putInTree($_GET["ref_id"]);
				$newObj->setPermissions($_GET["ref_id"]);
				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				$this->afterSave($newObj);
			}
			return;
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * save object
	 * @access	public
	 */
	function afterSave($newObj)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		ilUtil::redirect(ilObjWikiGUI::getGotoLink($newObj->getRefId()));
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin, $tpl, $lng, $rbacsystem;

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "wiki"))
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
				// 
				$_POST["Fobject"]["title"] = $this->form_gui->getInput("title");
				$_POST["Fobject"]["desc"] = $this->form_gui->getInput("description");
				
				// create and insert forum in objecttree
				$newObj = parent::saveObject();
				
				$newObj->setTitle($this->form_gui->getInput("title"));
				$newObj->setDescription($this->form_gui->getInput("description"));
				$newObj->setIntroduction($this->form_gui->getInput("intro"));
				$newObj->setStartPage($this->form_gui->getInput("startpage"));
				$newObj->setShortTitle($this->form_gui->getInput("shorttitle"));
				$newObj->setRating($this->form_gui->getInput("rating"));
				$newObj->setOnline($this->form_gui->getInput("online"));
				$newObj->update();
		
				// add first page
				
					
				// always send a message
				ilUtil::sendSuccess($this->lng->txt("object_added"),true);
				
				//ilUtil::redirect("ilias.php?baseClass=ilWikiHandlerGUI&ref_id=".$newObj->getRefId()."&cmd=editSettings");
				ilUtil::redirect(ilObjWikiGUI::getGotoLink($newObj->getRefId()));
			}
		}

		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHtml());
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
		//$ilTabs->addTarget("wiki_print_view",
		//	$this->ctrl->getLinkTarget($this,
		//	"printViewSelection"), "printViewSelection");
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
		global $ilCtrl, $ilAccess, $ilTabs, $lng;

		// wiki tabs
		if (in_array($ilCtrl->getCmdClass(), array("", "ilobjwikigui",
			"ilinfoscreengui", "ilpermissiongui", "ilexportgui")))
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
			array("general_settings", "style")))
		{
			// general properties
			$ilTabs->addSubTab("general_settings",
				$lng->txt("wiki_general_settings"),
				$ilCtrl->getLinkTarget($this, 'editSettings'));
				
			// style properties
			$ilTabs->addSubTab("style",
				$lng->txt("wiki_style"),
				$ilCtrl->getLinkTarget($this, 'editStyleProperties'));

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
		
		$tpl->setContent($this->form_gui->getHtml());
		$this->setSideBlock();
	}
	
	/**
	* Init Settings Form
	*/
	function initSettingsForm($a_mode = "edit")
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;
		
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
		$sp = new ilTextInputGUI($lng->txt("wiki_start_page"), "startpage");
		$sp->setMaxLength(200);
		$sp->setRequired(true);
		$this->form_gui->addItem($sp);

		// Online
		$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
		$this->form_gui->addItem($online);
		
		$rating = new ilCheckboxInputGUI($lng->txt("wiki_activate_rating"), "rating");
		$this->form_gui->addItem($rating);

		// Form action and save button
		$this->form_gui->setTitleIcon(ilUtil::getImagePath("icon_wiki.gif"));
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
			$values["startpage"] = $lng->txt("wiki_main_page");
			$this->form_gui->setValuesByArray($values);
		}
		else
		{
			$values["online"] = $this->object->getOnline();
			$values["title"] = $this->object->getTitle();
			$values["startpage"] = $this->object->getStartPage();
			$values["shorttitle"] = $this->object->getShortTitle();
			$values["description"] = $this->object->getDescription();
			$values["rating"] = $this->object->getRating();
			$values["intro"] = $this->object->getIntroduction();

			$this->form_gui->setValuesByArray($values);
		}
	}
	
	
	/**
	* Save Settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl, $lng, $ilUser;
		
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
				$this->object->setStartPage($this->form_gui->getInput("startpage"));
				$this->object->setShortTitle($this->form_gui->getInput("shorttitle"));
				$this->object->setRating($this->form_gui->getInput("rating"));
				$this->object->setIntroduction($this->form_gui->getInput("intro"));
				$this->object->update();
			
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
				$ilCtrl->redirect($this, "editSettings");
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
		
		$users = (is_array($_POST["sel_user_id"]))
			? $_POST["sel_user_id"]
			: (is_array($_POST["user_id"])
				? $_POST["user_id"]
				: array());
		
		include_once("./Modules/Wiki/classes/class.ilWikiContributor.php");
		include_once("./Services/Tracking/classes/class.ilLPMarks.php");
		$saved = false;
		foreach($users as $user_id)
		{
			if ($user_id != "")
			{
				ilWikiContributor::_writeStatus($this->object->getId(), $user_id,
					ilUtil::stripSlashes($_POST["status"][$user_id]));
				$marks_obj = new ilLPMarks($this->object->getId(),$user_id);
				$marks_obj->setMark(ilUtil::stripSlashes($_POST['mark'][$user_id]));
				$marks_obj->setComment(ilUtil::stripSlashes($_POST['lcomment'][$user_id]));
				$marks_obj->update();
				$saved = true;
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

	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng, $ilNavigationHistory;
		
		$i = strpos($a_target, "_");
		if ($i > 0)
		{
			$a_page = substr($a_target, $i+1);
			$a_target = substr($a_target, 0, $i);
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
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $tarr[0];
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($tarr[0]))), true);
			include("repository.php");
			exit;
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
				!$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()))
		{
			$wpage_gui->setEnableEditing(false);
		}

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
			// create the page
			$page = new ilWikiPage();
			$page->setWikiId($this->object->getId());
			$page->setTitle(ilWikiUtil::makeDbTitle($_GET["page"]));
			$page->create();

			// redirect to newly created page
			$ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($a_page)));
			$ilCtrl->redirectByClass("ilwikipagegui", "edit");
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

	function setSideBlock($a_wpg_id = 0)
	{
		global $tpl;
		
		// side block
		include_once("./Modules/Wiki/classes/class.ilWikiSideBlockGUI.php");
		$wiki_side_block = new ilWikiSideBlockGUI();
		if ($a_wpg_id > 0)
		{
			include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
			$wiki_side_block->setPageObject(new ilWikiPage($a_wpg_id));
		}

		// search block
		include_once("./Modules/Wiki/classes/class.ilWikiSearchBlockGUI.php");
		$wiki_search_block = new ilWikiSearchBlockGUI();
		$rcontent = $wiki_side_block->getHTML().$wiki_search_block->getHTML();
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


	/**
	 * Show printable view of a wiki page
	 */
	function printViewObject()
	{
		$this->checkPermission("read");

		switch ($_POST["sel_type"])
		{
			case "wiki":
				include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
				$pages = ilWikiPage::getAllPages($this->object->getId());
				foreach ($pages as $p)
				{
					$pg_ids[] = $p["id"];
				}
				break;

			case "selection":
				if (is_array($_POST["obj_id"]))
				{
					$pg_ids = $_POST["obj_id"];
				}
				else
				{
					$pg_ids[] = $_GET["wpg_id"];
				}
				break;

			default:
				$pg_ids[] = $_GET["wpg_id"];
				break;
		}

		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");

		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());
		$this->setContentStyleSheet($tpl);

		// syntax style
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();


		// determine target frames for internal links

		foreach ($pg_ids as $p_id)
		{
			$page_gui = new ilWikiPageGUI($p_id);
			$page_gui->setOutputMode("print");
			$page_content.= $page_gui->showPage();
		}
		$tpl->setVariable("CONTENT", '<div class="ilInvisibleBorder">'.$page_content.'</div>'.
		'<script type="text/javascript" language="javascript1.2">
		<!--
			// Do print the page
			if (typeof(window.print) != \'undefined\')
			{
				window.print();
			}
		//-->
		</script>');
		$tpl->show(false);
		exit;
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
			$this->object->getId(), $search_results);
			
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

}
?>
