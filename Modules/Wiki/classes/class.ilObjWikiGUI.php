<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
		
		$ilCtrl->saveParameter($this, "page");
	}
	
	function &executeCommand()
	{
  		global $ilUser, $ilCtrl, $tpl;
  
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
				//$ilCtrl->setReturn($this, "editPage");
				include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
				$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
					$_GET["page"], $_GET["old_nr"]);
				$ret = $this->ctrl->forwardCommand($wpage_gui);
				$tpl->setContent($ret);
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
		global $rbacadmin, $tpl;

		$this->initSettingsForm("create");
		if ($this->form_gui->checkInput())
		{
			// 
			$_POST["Fobject"]["title"] = $this->form_gui->getInput("title");
			$_POST["Fobject"]["desc"] = $this->form_gui->getInput("description");
			
			// create and insert forum in objecttree
			$newObj = parent::saveObject();
			
			$newObj->setTitle($this->form_gui->getInput("title"));
			$newObj->setDescription($this->form_gui->getInput("description"));
			$newObj->setStartPage($this->form_gui->getInput("startpage"));
			$newObj->setShortTitle($this->form_gui->getInput("shorttitle"));
			$newObj->setRating($this->form_gui->getInput("rating"));
			$newObj->update();
	
			// setup rolefolder & default local roles
			//$roles = $newObj->initDefaultRoles();
	
			// ...finally assign role to creator of object
			//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");
	
			// add first page
			
				
			// always send a message
			ilUtil::sendInfo($this->lng->txt("object_added"),true);
			
			ilUtil::redirect("ilias.php?baseClass=ilWikiHandlerGUI&ref_id=".$newObj->getRefId()."&cmd=editSettings");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHtml());
		}
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
		
		/*
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			//$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}*/
		
		$info->addButton($lng->txt("wiki_start_page"), $this->getGotoLink());
		
		// general information
		$this->lng->loadLanguageModule("meta");
		$this->lng->loadLanguageModule("wiki");

		//$info->addSection($this->lng->txt("meta_general"));
		//$info->addProperty($this->lng->txt("mcst_nr_items"),
		//	(int) count($med_items));

		// forward the command
		$this->ctrl->forwardCommand($info);
	}
	
	/**
	* Go to start page
	*/
	function gotoStartPageObject()
	{
		global $ilCtrl;
		
		ilUtil::redirect($this->getGotoLink());
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs($tabs_gui)
	{
		global $ilCtrl, $ilAccess;
		
		// wiki page tabs
//echo "-".$ilCtrl->getNextClass()."-";
		if (in_array($ilCtrl->getCmdClass(), array("", "ilobjwikigui",
			"ilinfoscreengui", "ilpermissiongui")))
		{
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
		$stit = new ilTextInputGUI($lng->txt("wiki_short_title"), "shorttitle");
		$stit->setRequired(true);
		$stit->setSize(20);
		$stit->setMaxLength(20);
		$stit->setInfo($lng->txt("wiki_short_title_desc"));
		$this->form_gui->addItem($stit);

		// Description
		$des = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$this->form_gui->addItem($des);

		// Start Page
		$sp = new ilTextInputGUI($lng->txt("wiki_start_page"), "startpage");
		$sp->setMaxLength(200);
		$sp->setRequired(true);
		$this->form_gui->addItem($sp);

		// Online
		if ($a_mode != "create")
		{
			$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
			$this->form_gui->addItem($online);
		}
		
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
			$this->form_gui->setValuesByArray($values);
		}
	}
	
	
	/**
	* Save Settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		$this->initSettingsForm();
		if ($this->form_gui->checkInput())
		{
			
			$this->object->setTitle($this->form_gui->getInput("title"));
			$this->object->setDescription($this->form_gui->getInput("description"));
			$this->object->setOnline($this->form_gui->getInput("online"));
			$this->object->setStartPage($this->form_gui->getInput("startpage"));
			$this->object->setShortTitle($this->form_gui->getInput("shorttitle"));
			$this->object->setRating($this->form_gui->getInput("rating"));
			$this->object->update();
						
			ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
			$ilCtrl->redirect($this, "editSettings");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	// add wiki to locator
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
		}
	}

	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;
		
		$tarr = explode("_", $a_target);
		$a_target = (int)$tarr[0];
		$a_page = $tarr[1];

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "viewPage";
			$_GET["ref_id"] = $a_target;
			$_GET["page"] = $a_page;
			$_GET["baseClass"] = "ilwikihandlergui";
			$_GET["cmdClass"] = "ilobjwikigui";
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

	function getGotoLink($a_page = "")
	{
		if ($a_page == "")
		{
			$a_page = $this->object->getStartPage();
		}
		$goto = "./goto.php?target=wiki_".$this->object->getRefId()."_".
			rawurlencode($a_page);
			
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
		$ilCtrl->setParameter($this, "page", rawurlencode($page));
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
			$page);
		//$wpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		
		//$wpage_gui->setSideBlock();
		$ilCtrl->setCmdClass("ilwikipagegui");
		$ilCtrl->setCmd("preview");
		$html = $ilCtrl->forwardCommand($wpage_gui);
		
		$tpl->setContent($html);
	}
	
	/**
	* edit wiki page
	*/
/*
	function editPageObject()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->checkPermission("write");
		
		$page = $_GET["page"];
			
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		if (!ilWikiPage::exists($this->object->getId(), $page))
		{
			ilUtil::sendInfo($lng->txt("wiki_not_existing"));
			$this->infoScreen();
			return;
		}
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
			$page);
		$wpage_gui->setMode(IL_WIKI_PAGE_EDIT);
			
		$html = $ilCtrl->getHTML($wpage_gui);
		
		$tpl->setContent($html);
	}
*/
	
	/**
	* View Page History
	*/
/*
	function viewHistoryObject()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->checkPermission("write");
		
		$page = $_GET["page"];
			
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		if (!ilWikiPage::exists($this->object->getId(), $page))
		{
			ilUtil::sendInfo($lng->txt("wiki_not_existing"));
			$this->infoScreen();
			return;
		}
		
		include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
		$wpage_gui = ilWikiPageGUI::getGUIForTitle($this->object->getId(),
			$page);
		$wpage_gui->setMode(IL_WIKI_PAGE_HISTORY);
			
		$html = $ilCtrl->getHTML($wpage_gui);
		
		$tpl->setContent($html);
	}
*/
	
	/**
	* All pages of wiki
	*/
	function allPagesObject()
	{
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$table_gui = new ilWikiPagesTableGUI($this, "allPages",
			$this->object->getId(), IL_WIKI_ALL_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

	/**
	* All links to a specific page
	*/
	function whatLinksHereObject()
	{
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->setSideBlock($_GET["wpg_id"]);
		$table_gui = new ilWikiPagesTableGUI($this, "",
			$this->object->getId(), IL_WIKI_WHAT_LINKS_HERE, $_GET["wpg_id"]);
			
		$tpl->setContent($table_gui->getHTML());
	}
	
	/**
	* Popular pages
	*/
	function popularPagesObject()
	{
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$table_gui = new ilWikiPagesTableGUI($this, "popularPages",
			$this->object->getId(), IL_WIKI_POPULAR_PAGES);
			
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
			$a_page))
		{
			// to do: get rid of this redirect
			ilUtil::redirect($this->getGotoLink($a_page));
		}
		else
		{
			// create the page
			$start_page = new ilWikiPage();
			$start_page->setWikiId($this->object->getId());
			$start_page->setTitle($_GET["page"]);
			$start_page->create();

			// redirect to newly created page
			$ilCtrl->setParameterByClass("ilwikipagegui", "page", rawurlencode($a_page));
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
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiRecentChangesTableGUI.php");
		
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
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$table_gui = new ilWikiPagesTableGUI($this, "newPages",
			$this->object->getId(), IL_WIKI_NEW_PAGES);
			
		$this->setSideBlock();
		$tpl->setContent($table_gui->getHTML());
	}

}
?>
