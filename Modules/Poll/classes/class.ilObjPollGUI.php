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
* @ilCtrl_Calls ilObjPollGUI: ilPermissionGUI, ilObjectCopyGUI, ilExportGUI
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
	
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl;
		
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);		
		$ilCtrl->redirect($this, "render");
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $lng;					
		
		// activation
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$a_form->addItem($section);
		
		// additional info only with multiple references
		$act_obj_info = $act_ref_info = "";
		if(sizeof(ilObject::_getAllReferences($this->object->getId())) > 1)
		{
			$act_obj_info = ' '.$this->lng->txt('rep_activation_online_object_info');
			$act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
		}
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'online');		
		$online->setInfo($this->lng->txt('poll_activation_online_info').$act_obj_info);
		$a_form->addItem($online);				
		
		$act_type = new ilRadioGroupInputGUI($this->lng->txt('rep_activation_access'),'access_type');
		$act_type->setInfo($act_ref_info);
		
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
				
				/*
				$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
				$visible->setInfo($this->lng->txt('poll_activation_limited_visibility_info'));
				$opt->addSubItem($visible);
				*/
				
			$act_type->addOption($opt);
		
		$a_form->addItem($act_type);				
		
		
		// period/results
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('poll_voting_period_and_results'));
		$a_form->addItem($section);
		
		$prd = new ilRadioGroupInputGUI($this->lng->txt('poll_voting_period'),'period');
		
			$opt = new ilRadioOption($this->lng->txt('poll_voting_period_unlimited'), 0);		
			$prd->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('poll_voting_period_limited'), 1);			
			$prd->addOption($opt);
		
			$date = $this->object->getVotingPeriodBegin();

			$start = new ilDateTimeInputGUI($this->lng->txt('poll_voting_period_start'),'period_begin');
			$start->setShowTime(true);		
			$start->setDate(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$opt->addSubItem($start);

			$date = $this->object->getVotingPeriodEnd();

			$end = new ilDateTimeInputGUI($this->lng->txt('poll_voting_period_end'),'period_end');			
			$end->setShowTime(true);			
			$end->setDate(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$opt->addSubItem($end);
			
		$a_form->addItem($prd);	
						
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
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = $this->object->IsOnline();
		// $a_values["question"] = $this->object->getQuestion();
		// $a_values["image"] = $this->object->getImage();
		$a_values["results"] = $this->object->getViewResults();
		$a_values["access_type"] = $this->object->getAccessType();
		// $a_values["access_begin"] = $this->object->getAccessBegin();
		// $a_values["access_end"] = $this->object->getAccessEnd();
		// $a_values["access_visiblity"] = $this->object->getAccessVisibility();
		$a_values["period"] = $this->object->getVotingPeriod();
		$a_values["period_begin"] = $this->object->getVotingPeriodBegin();
		$a_values["period_end"] = $this->object->getVotingPeriodEnd();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{		
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
			// $this->object->setAccessVisibility($a_form->getInput("access_visiblity"));			
		}		
		$this->object->setVotingPeriod($a_form->getInput("period"));
		if($this->object->getVotingPeriod())
		{		
			$date = new ilDateTime($_POST['period_begin']['date'] . ' ' . $_POST['period_begin']['time'], IL_CAL_DATETIME);
			$this->object->setVotingPeriodBegin($date->get(IL_CAL_UNIX));
			$date = new ilDateTime($_POST['period_end']['date'] . ' ' . $_POST['period_end']['time'], IL_CAL_DATETIME);			
			$this->object->setVotingPeriodEnd($date->get(IL_CAL_UNIX));
		}
	}

	function setTabs()
	{
		global $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("poll");

		if ($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("content",
				$lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		
		if ($this->checkPermissionBool("write"))
		{			
			$this->tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));			
			
			$this->tabs_gui->addTab("participants",
				$lng->txt("poll_result"),
				$this->ctrl->getLinkTarget($this, "showParticipants"));		
			
			$this->tabs_gui->addTab("export",
					$lng->txt("export"),
					$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
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
			
			case 'ilexportgui':
				$this->prepareOutput();
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this); 
				$exp_gui->addFormat("xml");
				$ilCtrl->forwardCommand($exp_gui);
				break;

			default:			
				return parent::executeCommand();			
		}
		
		return true;
	}
	
	
	// --- ObjectGUI End
	
	
	/**
	 * Render object context
	 */
	function render($a_form = null)
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar, $ilUser;
		
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("content");
		
		if(!$a_form)
		{
			if($this->object->countVotes())
			{
				$url = $ilCtrl->getLinkTarget($this, "showParticipants");
				ilUtil::sendInfo($lng->txt("poll_votes_no_edit").
					" <a href=\"".$url."\">&raquo;".$lng->txt("poll_participants")."</a>");				
			}
			
			$a_form = $this->initQuestionForm($this->object->countVotes());
		}
		
		include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
		$plink = new ilPermanentLinkGUI('poll', $this->node_id);
		
		$tpl->setContent($a_form->getHTML().$plink->getHTML());		
	}
	
	protected function initQuestionForm($a_read_only = false)
	{
		global $lng, $ilCtrl;				
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveQuestion"));
		$form->setTitle($lng->txt("obj_poll"));
		
		$question = new ilTextAreaInputGUI($lng->txt("poll_question"), "question");
		$question->setRequired(true);
		$question->setCols(40);
		$question->setRows(2);
		$question->setValue($this->object->getQuestion());
		$question->setDisabled($a_read_only);
		$form->addItem($question);
		
		$dimensions = " (".ilObjPoll::getImageSize()."px)";		
		$img = new ilImageFileInputGUI($lng->txt("poll_image").$dimensions, "image");
		$img->setDisabled($a_read_only);
		$form->addItem($img);
			
		// show existing file
		$file = $this->object->getImageFullPath(true);
		if($file)
		{
			$img->setImage($file);
		}					
		
		$answers = new ilTextInputGUI($lng->txt("poll_answers"), "answers");
		$answers->setRequired(true);
		$answers->setMulti(true, true);
		$answers->setDisabled($a_read_only);
		$form->addItem($answers);			
				
		$multi_answers = array();		
		foreach($this->object->getAnswers() as $idx => $item)
		{
			if(!$idx)
			{
				$answers->setValue($item["answer"]);
			}
			$multi_answers[] = $item["answer"];
		}
		$answers->setMultiValues($multi_answers);
		
		if(!$a_read_only)
		{
			$form->addCommandButton("saveQuestion", $lng->txt("save"));
		}
		
		return $form;
	}
	
	function saveQuestion()
	{
		$form = $this->initQuestionForm();
		if($form->checkInput())
		{			
			$this->object->setQuestion($form->getInput("question"));
						
			$image = $form->getItemByPostVar("image");				
			if($_FILES["image"]["tmp_name"]) 
			{
				$this->object->uploadImage($_FILES["image"]);
			}
			else if($image->getDeletionFlag())
			{
				$this->object->deleteImage();
			}
			 
			$this->object->saveAnswers($form->getInput("answers"));
			
			if($this->object->update())
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				$this->ctrl->redirect($this, "render");
			}
		}
		
		$form->setValuesByPost();
		$this->render($form);
	}
	
	function showParticipants()
	{
		global $lng, $ilTabs, $tpl;
		
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("participants");
	
		include_once "Modules/Poll/classes/class.ilPollAnswerTableGUI.php";
		$tbl = new ilPollAnswerTableGUI($this, "showParticipants");	
		$tpl->setContent($tbl->getHTML());		
	}
	
	function confirmDeleteAllVotes()
	{
		global $lng, $tpl, $ilTabs;
		
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("participants");
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("poll_delete_votes_sure"));

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($lng->txt("cancel"), "showParticipants");
		$cgui->setConfirm($lng->txt("confirm"), "deleteAllVotes");

		$tpl->setContent($cgui->getHTML());
	}
	
	function deleteAllVotes()
	{
		global $ilCtrl, $lng;
		
		if(!$this->checkPermissionBool("write"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$this->object->deleteAllVotes();
			
		$ilCtrl->redirect($this, "showParticipants");
	}
				
	function vote()
	{
		global $tree, $ilUser;
		
		if($_POST["aw"])
		{
			$this->object->saveVote($ilUser->getId(), $_POST["aw"]);
		}
		
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink:: _getLink($tree->getParentId($this->ref_id)));
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
		global $tree;
		
		$id = explode("_", $a_target);		

		// is sideblock: so show parent instead
		$container_id = $tree->getParentId($id[0]);
		
		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $container_id;		
		$_GET["cmd"] = "render";
		
		include("ilias.php");
		exit;
	}
}

?>