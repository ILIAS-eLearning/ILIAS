<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* @ilCtrl_Calls ilObjWikiGUI: ilPublicUserProfileGUI
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
	
	function &executeCommand()
	{
  		global $ilUser, $ilCtrl, $tpl, $ilTabs;
  
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
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			case 'ilwikipagegui':
				include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
				$append = ($_GET["page"] != "")
					? "_".ilWikiUtil::makeUrlTitle($_GET["page"])
					: "";
				$perma_link = new ilPermanentLinkGUI("wiki", $_GET["ref_id"], $append);
				include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
				$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
					ilWikiUtil::makeDbTitle($_GET["page"]), $_GET["old_nr"]);
				$ret = $this->ctrl->forwardCommand($wpage_gui);
				if ($ret != "")
				{
					$tpl->setContent(
						$ret.
						"<br />".
						$perma_link->getHTML());
				}
				//if ($ilCtrl->getCmdClass() == "ilwikipagegui")
				//{
				//}
				break;

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				$tpl->setContent($ret);
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
			$tpl->setContent($this->form_gui->getHtml());
		}
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin, $tpl, $lng;

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
				$newObj->setIntroduction($this->form_gui->getInput("introduction"));
				$newObj->setStartPage($this->form_gui->getInput("startpage"));
				$newObj->setShortTitle($this->form_gui->getInput("shorttitle"));
				$newObj->setRating($this->form_gui->getInput("rating"));
				$newObj->setOnline($this->form_gui->getInput("online"));
				$newObj->update();
		
				// setup rolefolder & default local roles
				//$roles = $newObj->initDefaultRoles();
		
				// ...finally assign role to creator of object
				//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");
		
				// add first page
				
					
				// always send a message
				ilUtil::sendInfo($this->lng->txt("object_added"),true);
				
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
		global $ilAccess, $ilUser, $lng;

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
		
		$info->addButton($lng->txt("wiki_start_page"), ilObjWikiGUI::getGotoLink($this->object->getRefId()));
		
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
			$this->setSideBlock();
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
			$this->ctrl->getLinkTarget($this,
			"printView"), "printView");	
	}
	
	/**
	* Add Pages SubTabs
	*/
	function addPagesSubTabs()
	{
		global $ilTabs, $ilCtrl;
		
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
		global $ilCtrl, $ilAccess, $lng;
		
		// wiki tabs
		if (in_array($ilCtrl->getCmdClass(), array("", "ilobjwikigui",
			"ilinfoscreengui", "ilpermissiongui")))
		{
			if ($_GET["page"] != "")
			{
				$tabs_gui->setBackTarget($lng->txt("wiki_last_visited_page"),
					$this->getGotoLink($_GET["ref_id"],
						ilWikiUtil::makeDbTitle($_GET["page"])));
			}
			
			// info screen
			if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()))
			{
				$force_active = ($ilCtrl->getNextClass() == "ilinfoscreengui"
					|| $_GET["cmd"] == "infoScreen")
					? true
					: false;
				$tabs_gui->addTarget("info_short",
					$this->ctrl->getLinkTargetByClass(
					"ilinfoscreengui", "showSummary"),
					"showSummary",
					"", "", $force_active);
			}

			// settings
			if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
			{
				$tabs_gui->addTarget("settings",
					$this->ctrl->getLinkTarget($this, "editSettings"), array("editSettings"),
					array(strtolower(get_class($this)), ""));
			}

			// pages
			if ($ilAccess->checkAccess('read', "", $this->object->getRefId()))
			{
				$tabs_gui->addTarget("wiki_pages",
					$this->ctrl->getLinkTarget($this, "allPages"),
					"allPages");
			}
			
			// contributors
			if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
			{
				$tabs_gui->addTarget("wiki_contributors",
					$this->ctrl->getLinkTarget($this, "listContributors"), array("listContributors"),
					array(strtolower(get_class($this)), ""));
			}
	
			// edit permissions
			if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
			{
				$tabs_gui->addTarget("perm_settings",
					$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"), array("perm","info","owner"), 'ilpermissiongui');
			}
		}
	}

	/**
	* Edit settings
	*/
	function editSettingsObject()
	{
		global $tpl;
		
		$this->checkPermission("write");
		
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
		global $tpl, $lng, $ilCtrl;
		
		$lng->loadLanguageModule("wiki");
		
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
		global $lng;
		
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
		global $ilCtrl, $lng;
		
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
							
				ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
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
		global $tpl;
		
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
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		$users = (is_array($_POST["sel_user_id"]))
			? $_POST["sel_user_id"]
			: (is_array($_POST["user_id"])
				? $_POST["user_id"]
				: array());
		
		include_once("./Modules/Wiki/classes/class.ilWikiContributor.php");
		include_once("./Services/Tracking/classes/class.ilLPMarks.php");
		foreach($users as $user_id)
		{
			ilWikiContributor::_writeStatus($this->object->getId(), $user_id,
				ilUtil::stripSlashes($_POST["status"][$user_id]));
			$marks_obj = new ilLPMarks($this->object->getId(),$user_id);
			$marks_obj->setMark(ilUtil::stripSlashes($_POST['mark'][$user_id]));
			$marks_obj->setComment(ilUtil::stripSlashes($_POST['lcomment'][$user_id]));
			$marks_obj->update();
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
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
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
		
		$goto = "./goto.php?target=wiki_".$a_ref_id."_".
			ilWikiUtil::makeUrlTitle($a_page);
			
		return $goto;
	}
	
	/**
	* view wiki page
	*/
	function viewPageObject()
	{
		global $lng, $ilCtrl, $tpl, $ilTabs;
		
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
			ilUtil::sendInfo($lng->txt("wiki_no_start_page"));
			$this->infoScreen();
			return;
		}
		
		// page exists, show it !
		$ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($page));
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
			ilWikiUtil::makeDbTitle($page));
		//$wpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		
		//$wpage_gui->setSideBlock();
		$ilCtrl->setCmdClass("ilwikipagegui");
		$ilCtrl->setCmd("preview");
		$html = $ilCtrl->forwardCommand($wpage_gui);
		//$this->addPageTabs();
		
		// permanent link
		$append = ($_GET["page"] != "")
			? "_".ilWikiUtil::makeUrlTitle($_GET["page"])
			: "";
		include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
		$perma_link = new ilPermanentLinkGUI("wiki", $_GET["ref_id"], $append);
		
		$tpl->setContent($html.
			"<br />".
			$perma_link->getHTML());
	}
		
	/**
	* All pages of wiki
	*/
	function allPagesObject()
	{
		global $tpl;
		
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
		global $tpl, $ilTabs;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		$ilTabs->setTabActive("wiki_pages");
		
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
		global $tpl, $ilTabs;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		$ilTabs->setTabActive("wiki_pages");
		
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
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$page = ilWikiPage::getRandomPage($this->object->getId());
		$this->gotoPageObject($page);
	}

	/**
	* Recent Changes
	*/
	function recentChangesObject()
	{
		global $tpl, $ilTabs;
		
		include_once("./Modules/Wiki/classes/class.ilWikiRecentChangesTableGUI.php");
		
		$this->addPagesSubTabs();
		$ilTabs->setTabActive("wiki_pages");
		
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
		$tpl->setRightContent($wiki_side_block->getHTML());
	}

	/**
	* Latest pages
	*/
	function newPagesObject()
	{
		global $tpl, $ilTabs;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->addPagesSubTabs();
		$ilTabs->setTabActive("wiki_pages");
		
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
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$page_gui = new ilWikiPageGUI($_GET["wpg_id"]);
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		// determine target frames for internal links
		$page_gui->setOutputMode("print");
		$page_content = $page_gui->showPage();
		$tpl->setVariable("CONTENT", $page_content);
		$tpl->show(false);
		exit;
	}

}
?>
