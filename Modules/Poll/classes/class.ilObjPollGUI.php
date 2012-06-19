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
		global $lng;
	
		$question = new ilTextAreaInputGUI($lng->txt("poll_question"), "question");
		$question->setRequired(true);
		$question->setCols(40);
		$question->setRows(2);
		$a_form->addItem($question);
		
		$dimensions = " (".ilObjPoll::getImageSize()."px)";		
		$img = new ilImageFileInputGUI($lng->txt("poll_image").$dimensions, "image");
		$a_form->addItem($img);
			
		// show existing file
		$file = $this->object->getImageFullPath(true);
		if($file)
		{
			$img->setImage($file);
		}					
		
		$results = new ilRadioGroupInputGUI($lng->txt("poll_view_results"), "results");
		$results->setRequired(true);
		$results->addOption(new ilRadioOption($lng->txt("poll_view_results_always"), 
			ilObjPoll::VIEW_RESULTS_ALWAYS));
		$results->addOption(new ilRadioOption($lng->txt("poll_view_results_never"), 
			ilObjPoll::VIEW_RESULTS_NEVER));
		$results->addOption(new ilRadioOption($lng->txt("poll_view_results_after_vote"), 
			ilObjPoll::VIEW_RESULTS_AFTER_VOTE));
		$results->addOption(new ilRadioOption($lng->txt("poll_view_results_after_period"), 
			ilObjPoll::VIEW_RESULTS_AFTER_PERIOD));
		$a_form->addItem($results);				
		
			
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$a_form->addItem($section);
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'online');		
		$online->setInfo($this->lng->txt('poll_activation_online_info'));
		$a_form->addItem($online);				
		
		$act_type = new ilRadioGroupInputGUI($this->lng->txt('rep_activation_access'),'access_type');
		
			$opt = new ilRadioOption($this->lng->txt('rep_visibility_limitless'), ilObjectActivation::TIMINGS_DEACTIVATED);
			$opt->setInfo($this->lng->txt('poll_availability_limitless_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('rep_visibility_until'), ilObjectActivation::TIMINGS_ACTIVATION);
			$opt->setInfo($this->lng->txt('poll_availability_until_info'));

				$date = $this->object->getAccessBegin();
				
				$start = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_start'),'access_begin');
				$start->setShowTime(true);		
				$start->setDate(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
				$opt->addSubItem($start);
				
				$date = $this->object->getAccessEnd();
				
				$end = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_end'),'access_end');			
				$end->setShowTime(true);			
				$end->setDate(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
				$opt->addSubItem($end);
				
				$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
				$visible->setInfo($this->lng->txt('poll_activation_limited_visibility_info'));
				$opt->addSubItem($visible);
				
			$act_type->addOption($opt);
		
		$a_form->addItem($act_type);				
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = $this->object->IsOnline();
		$a_values["question"] = $this->object->getQuestion();
		$a_values["image"] = $this->object->getImage();
		$a_values["results"] = $this->object->getViewResults();
		$a_values["access_type"] = $this->object->getAccessType();
		// $a_values["access_begin"] = $this->object->getAccessBegin();
		// $a_values["access_end"] = $this->object->getAccessEnd();
		$a_values["access_visiblity"] = $this->object->getAccessVisibility();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->setQuestion($a_form->getInput("question"));
		$this->object->setViewResults($a_form->getInput("results"));
		$this->object->setOnline($a_form->getInput("online"));
						
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->object->setAccessType($a_form->getInput("access_type"));
		if($this->object->getAccessType() == ilObjectActivation::TIMINGS_ACTIVATION)
		{
			$date = new ilDateTime($_POST['access_begin']['date'] . ' ' . $_POST['access_begin']['time'], IL_CAL_DATETIME);
			$this->object->setAccessBegin($date->get(IL_CAL_UNIX));
			$date = new ilDateTime($_POST['access_end']['date'] . ' ' . $_POST['access_end']['time'], IL_CAL_DATETIME);			
			$this->object->setAccessEnd($date->get(IL_CAL_UNIX));
			$this->object->setAccessVisibility($a_form->getInput("access_visiblity"));
		}
		
		$image = $a_form->getItemByPostVar("image");				
		if($_FILES["image"]["tmp_name"]) 
		{
			$this->object->uploadImage($_FILES["image"]);
		}
		else if($image->getDeletionFlag())
		{
			$this->object->deleteImage();
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
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar, $ilUser;
		
		if(!$this->checkPermissionBool("read"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("content");
		
		$ilToolbar->addButton($lng->txt("poll_add_answer"), 
			$ilCtrl->getLinkTarget($this, "addAnswer"));

		// table gui
		include_once "Modules/Poll/classes/class.ilPollAnswerTableGUI.php";
		$tbl = new ilPollAnswerTableGUI($this, "render");
		
		$tpl->setContent($tbl->getHTML());		
	}
	
	/**
	 * return user view
	 * 
	 * @return string 
	 */	
	function getHTML()
	{		
		
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
	
	
	//
	// Answers
	// 
	
	function addAnswer(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $ilTabs, $lng;
	
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
			
		$ilTabs->activateTab("content");
		
		if(!$a_form)
		{
			$a_form = $this->initAnswerForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function editAnswer(ilPropertyFormGUI $a_form = null)
	{		
		global $tpl, $ilTabs, $lng, $ilCtrl;
		
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("content");
		
		$ilCtrl->setParameter($this, "pa_id", $_REQUEST["pa_id"]);
		
		if(!$a_form)
		{
			$a_form = $this->initAnswerForm($_REQUEST["pa_id"]);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function initAnswerForm($a_answer_id = null)
	{		
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();		

		$ta = new ilTextAreaInputGUI($lng->txt("poll_answer"), "answer");
		$ta->setRequired(true);
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		if(!$a_answer_id)
		{
			$form->setFormAction($ilCtrl->getFormAction($this, "saveAnswer"));
			$form->setTitle($lng->txt("poll_add_answer"));		
			$form->addCommandButton("saveAnswer", $lng->txt("save"));
		}
		else
		{
			$answer = $this->object->getAnswer($a_answer_id);			
			$ta->setValue($answer["answer"]);
			
			$form->setFormAction($ilCtrl->getFormAction($this, "updateAnswer"));
			$form->setTitle($lng->txt("poll_edit_answer"));		
			$form->addCommandButton("updateAnswer", $lng->txt("save"));
		}
		
		$form->addCommandButton("render", $lng->txt("cancel"));

		return $form;
	}
	
	function saveAnswer()
	{
		global $lng, $ilCtrl;
		
		$form = $this->initAnswerForm();
		if($form->checkInput())
		{
			$this->object->saveAnswer($form->getInput("answer"));
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "render");								
		}
		
		$form->setValuesByPost();
		$this->addAnswer($form);
	}
	
	function updateAnswer()
	{
		global $lng, $ilCtrl;
		
		$form = $this->initAnswerForm($_REQUEST["pa_id"]);
		if($form->checkInput())
		{
			$this->object->updateAnswer($_REQUEST["pa_id"], $form->getInput("answer"));
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "render");								
		}
		
		$form->setValuesByPost();
		$this->editAnswer($form);
	}
	
	function confirmDeleteAnswers()
	{
		
	}
	
	function deleteAnswers()
	{
		
	}
	
	function updateAnswerOrder()
	{
		global $ilCtrl, $lng;
		
		$this->object->updateAnswerPositions($_POST["pos"]);
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "render");
	}	
}

?>