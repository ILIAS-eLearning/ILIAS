<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjPollGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjPollGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjPollGUI: ilPermissionGUI, ilObjectCopyGUI
*
* @extends ilObject2GUI
*/
class ilObjPollGUI extends ilObject2GUI
{	
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $lng;
		
	    parent::__construct($a_id, $a_id_type, $a_parent_node_id);		
		
		$lng->loadLanguageModule("poll");
	}

	function getType()
	{
		return "poll";
	}
	
	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);

		unset($forms[self::CFORM_IMPORT]);		
		// unset($forms[self::CFORM_CLONE]);
		
		return $forms;
	}
	
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl;
		
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);		
		$ilCtrl->redirect($this, "");
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $lng, $ilSetting;
		
		$notes = new ilCheckboxInputGUI($lng->txt("blog_enable_notes"), "notes");
		$a_form->addItem($notes);
				
		if($ilSetting->get('enable_global_profiles'))
		{
			$rss = new ilCheckboxInputGUI($lng->txt("blog_enable_rss"), "rss");
			$rss->setInfo($lng->txt("blog_enable_rss_info"));
			$a_form->addItem($rss);
		}
	
		$ppic = new ilCheckboxInputGUI($lng->txt("blog_profile_picture"), "ppic");
		$a_form->addItem($ppic);
		
		$blga_set = new ilSetting("blga");
		if($blga_set->get("banner"))
		{		
			$dimensions = " (".$blga_set->get("banner_width")."x".
				$blga_set->get("banner_height").")";
			
			$img = new ilImageFileInputGUI($lng->txt("blog_banner").$dimensions, "banner");
			$a_form->addItem($img);
			
			// show existing file
			$file = $this->object->getImageFullPath(true);
			if($file)
			{
				$img->setImage($file);
			}
		}		
		
		$bg_color = new ilColorPickerInputGUI($lng->txt("blog_background_color"), "bg_color");
		$a_form->addItem($bg_color);

		$font_color = new ilColorPickerInputGUI($lng->txt("blog_font_color"), "font_color");
		$a_form->addItem($font_color);	
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["notes"] = $this->object->getNotesStatus();
		$a_values["ppic"] = $this->object->hasProfilePicture();
		$a_values["bg_color"] = $this->object->getBackgroundColor();
		$a_values["font_color"] = $this->object->getFontColor();
		$a_values["banner"] = $this->object->getImage();
		$a_values["rss"] = $this->object->hasRSS();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->setNotesStatus($a_form->getInput("notes"));
		$this->object->setProfilePicture($a_form->getInput("ppic"));
		$this->object->setBackgroundColor($a_form->getInput("bg_color"));
		$this->object->setFontColor($a_form->getInput("font_color"));
		$this->object->setRSS($a_form->getInput("rss"));
		
		// banner field is optional
		$banner = $a_form->getItemByPostVar("banner");
		if($banner)
		{				
			if($_FILES["banner"]["tmp_name"]) 
			{
				$this->object->uploadImage($_FILES["banner"]);
			}
			else if($banner->getDeletionFlag())
			{
				$this->object->deleteImage();
			}
		}
	}

	function setTabs()
	{
		global $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("poll");

		if ($this->checkPermissionBool("read"))
		{
			$this->tabs_gui->addTab("content",
				$lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		
			$this->tabs_gui->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjpollgui", "ilinfoscreengui"), "showSummary"));
		}

		if ($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));			
		}

		// will add permissions if needed
		parent::setTabs();
	}

	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs, $ilNavigationHistory;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
						
		$tpl->getStandardTemplate();

		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset");				
			$ilNavigationHistory->addItem($this->node_id, $link, "poll");
		}
		
		switch($next_class)
		{								
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreenForward();	
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("poll");
				$this->ctrl->forwardCommand($cp);
				break;

			default:				
				if($cmd != "gethtml")
				{
					$this->addHeaderAction($cmd);
				}
				return parent::executeCommand();			
		}
		
		return true;
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $ilTabs, $ilErr;
		
		$ilTabs->activateTab("id_info");

		if (!$this->checkPermissionBool("visible"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($this->checkPermissionBool("read"))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($this->checkPermissionBool("write"))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}
		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$this->ctrl->forwardCommand($info);
	}
	
	// --- ObjectGUI End
	
	
	/**
	 * Render object context
	 */
	function render()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar, $ilUser, $tree;
		
		if(!$this->checkPermissionBool("read"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}

		$ilTabs->activateTab("content");
		
		// toolbar
		if($this->checkPermissionBool("write"))
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "createPosting"));

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$title = new ilTextInputGUI($lng->txt("title"), "title");
			$ilToolbar->addInputItem($title, $lng->txt("title"));
			
			$ilToolbar->addFormButton($lng->txt("blog_add_posting"), "createPosting");
						
			// exercise blog?			
			include_once "Modules/Exercise/classes/class.ilObjExercise.php";			
			$exercises = ilObjExercise::findUserFiles($ilUser->getId(), $this->node_id);
			if($exercises)
			{
				$info = array();
				foreach($exercises as $exercise)
				{
					$part = $this->getExerciseInfo($exercise["ass_id"]);
					if($part)
					{
						$info[] = $part;
					}
				}
				ilUtil::sendInfo(implode("<br />", $info));										
			}
		}
		
		$list = $nav = "";		
		if($this->items[$this->month])
		{						
			$is_owner = ($this->object->getOwner() == $ilUser->getId());
			$list = $this->renderList($this->items[$this->month], $this->month, "preview", null, $is_owner);
			$nav = $this->renderNavigation($this->items, "render", "preview", null, $is_owner);		
		}
					
		$tpl->setContent($list);
		$tpl->setRightContent($nav);
	}
	
	/**
	 * Return embeddable HTML chunk
	 * 
	 * @return string 
	 */	
	function getHTML()
	{		
		// getHTML() is called by ilRepositoryGUI::show()
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			return;
		}
		
		// there is no way to do a permissions check here, we have no wsp
		
		$this->filterInactivePostings();
		
		$list = $nav = "";
		if($this->items[$this->month])
		{				
			$list = $this->renderList($this->items[$this->month], $this->month, "previewEmbedded");
			$nav = $this->renderNavigation($this->items, "gethtml", "previewEmbedded");
		}		
		
		return $this->buildEmbedded($list, $nav);
	}	
	
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
		}
	}
	
	/**
	 * Deep link
	 * 
	 * @param string $a_target 
	 */
	function _goto($a_target)
	{									
		$id = explode("_", $a_target);		

		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $id[0];		
		$_GET["cmd"] = "render";
		
		include("ilias.php");
		exit;
	}
}

?>