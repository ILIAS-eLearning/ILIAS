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
		
		$act_type = new ilCheckboxInputGUI($this->lng->txt('rep_visibility_until'),'access_type');
		// $act_type->setInfo($this->lng->txt('poll_availability_until_info'));
		
			$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
			include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
			$dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "access_period");
			$dur->setShowTime(true);						
			$date = $this->object->getAccessBegin();				
			$dur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setStartText($this->lng->txt('rep_activation_limited_start'));				
			$date = $this->object->getAccessEnd();
			$dur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setEndText($this->lng->txt('rep_activation_limited_end'));				
			$act_type->addSubItem($dur);

		$a_form->addItem($act_type);				
		
		
		// period/results
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('poll_voting_period_and_results'));
		$a_form->addItem($section);
		
		$prd = new ilCheckboxInputGUI($this->lng->txt('poll_voting_period_limited'),'period');
		
			$vdur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "voting_period");
			$vdur->setShowTime(true);						
			$date = $this->object->getVotingPeriodBegin();				
			$vdur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$vdur->setStartText($this->lng->txt('poll_voting_period_start'));				
			$date = $this->object->getVotingPeriodEnd();
			$vdur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$vdur->setEndText($this->lng->txt('poll_voting_period_end'));				
			$prd->addSubItem($vdur);
			
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
		
		$show_result_as = new ilRadioGroupInputGUI($lng->txt("poll_show_results_as"), "show_results_as");
		$show_result_as->setRequired(true);
		$result_bar = new ilRadioOption($lng->txt("poll_barchart"),
			ilObjPoll::SHOW_RESULTS_AS_BARCHART);
		$show_result_as->addOption($result_bar);
		$show_result_as->addOption(new ilRadioOption($lng->txt("poll_piechart"),
			ilObjPoll::SHOW_RESULTS_AS_PIECHART));
		$a_form->addItem($show_result_as);

		$sort = new ilRadioGroupInputGUI($lng->txt("poll_result_sorting"), "sort");
		$sort->setRequired(true);
		$sort->addOption(new ilRadioOption($lng->txt("poll_result_sorting_answers"), 0));
		$sort->addOption(new ilRadioOption($lng->txt("poll_result_sorting_votes"), 1));
		$a_form->addItem($sort);

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('poll_comments'));
		$a_form->addItem($section);

		$comment = new ilCheckboxInputGUI($this->lng->txt('poll_comments'), 'comment');
		//$comment->setInfo($this->lng->txt('poll_comments_info'));
		$a_form->addItem($comment);
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		include_once "Services/Object/classes/class.ilObjectActivation.php";	
		
		$a_values["online"] = $this->object->IsOnline();	
		$a_values["results"] = $this->object->getViewResults();
		$a_values["access_type"] = ($this->object->getAccessType() == ilObjectActivation::TIMINGS_ACTIVATION);	
		$a_values["period"] = $this->object->getVotingPeriod();		
		$a_values["sort"] = $this->object->getSortResultByVotes();
		$a_values["comment"] = $this->object->getShowComments();
		$a_values["show_results_as"] = $this->object->getShowResultsAs();
	}
	
	protected function validateCustom(ilPropertyFormGUI $a_form)
	{
		// #14606
		if(!$a_form->getInput("period") &&
			$a_form->getInput("results") == ilObjPoll::VIEW_RESULTS_AFTER_PERIOD)
		{		
			ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
			$a_form->getItemByPostVar("results")->setAlert($this->lng->txt("poll_view_results_after_period_impossible"));
			return false;
		}
		return parent::validateCustom($a_form);		
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{		
		$this->object->setViewResults($a_form->getInput("results"));
		$this->object->setOnline($a_form->getInput("online"));
		$this->object->setSortResultByVotes($a_form->getInput("sort"));
		$this->object->setShowComments($a_form->getInput("comment"));
		$this->object->setShowResultsAs($a_form->getInput("show_results_as"));
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		if($a_form->getInput("access_type"))
		{
			$this->object->setAccessType(ilObjectActivation::TIMINGS_ACTIVATION);
			
			$period = $a_form->getItemByPostVar("access_period");													
			$this->object->setAccessBegin($period->getStart()->get(IL_CAL_UNIX));	
			$this->object->setAccessEnd($period->getEnd()->get(IL_CAL_UNIX));			
		}		
		else
		{
			$this->object->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
		}
				
		if($a_form->getInput("period"))
		{		
			$this->object->setVotingPeriod(1);
			
			$period = $a_form->getItemByPostVar("voting_period");
			$this->object->setVotingPeriodBegin($period->getStart()->get(IL_CAL_UNIX));			
			$this->object->setVotingPeriodEnd($period->getEnd()->get(IL_CAL_UNIX));
		}
		else
		{
			$this->object->setVotingPeriod(0);
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
					" <a href=\"".$url."\">&raquo;".$lng->txt("poll_result")."</a>");				
			}
			
			$a_form = $this->initQuestionForm($this->object->countVotes());
		}
			
		$tpl->setPermanentLink('poll', $this->node_id);
		
		$tpl->setContent($a_form->getHTML());		
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
		
		$anonymous = new ilRadioGroupInputGUI($lng->txt("poll_mode"), "mode");
		$anonymous->setRequired(true);
		$option = new ilRadioOption($lng->txt("poll_mode_anonymous"), 0);
		$option->setInfo($lng->txt("poll_mode_anonymous_info"));
		$anonymous->addOption($option);
		$option = new ilRadioOption($lng->txt("poll_mode_personal"), 1);
		$option->setInfo($lng->txt("poll_mode_personal_info"));
		$anonymous->addOption($option);
		$anonymous->setValue($this->object->getNonAnonymous());
		$anonymous->setDisabled($a_read_only);
		$form->addItem($anonymous);
		
		$nanswers = new ilNumberInputGUI($lng->txt("poll_max_number_of_answers"), "nanswers");
		$nanswers->setRequired(true);
		$nanswers->setMinValue(1);
		$nanswers->setSize(3);
		$nanswers->setValue($this->object->getMaxNumberOfAnswers());
		$nanswers->setDisabled($a_read_only);
		$form->addItem($nanswers);	
		
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
			$this->object->setNonAnonymous($form->getInput("mode"));
						
			$image = $form->getItemByPostVar("image");				
			if($_FILES["image"]["tmp_name"]) 
			{
				$this->object->uploadImage($_FILES["image"]);
			}
			else if($image->getDeletionFlag())
			{
				$this->object->deleteImage();
			}
			 
			$nr_of_anwers = $this->object->saveAnswers($form->getInput("answers"));
			
			// #15073
			$this->object->setMaxNumberOfAnswers(min($form->getInput("nanswers"), $nr_of_anwers));
			
			if($this->object->update())
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				$this->ctrl->redirect($this, "render");
			}
		}
		
		$form->setValuesByPost();
		$this->render($form);
	}
	
	protected function setParticipantsSubTabs($a_active)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		if(!$this->object->getNonAnonymous())
		{
			return;
		}
		
		$ilTabs->addSubTab("result_answers", $lng->txt("poll_result_answers"),
			$ilCtrl->getLinkTarget($this, "showParticipants"));
		$ilTabs->addSubTab("result_users", $lng->txt("poll_result_users"),
			$ilCtrl->getLinkTarget($this, "showParticipantVotes"));
		
		$ilTabs->activateSubTab($a_active);
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
		$this->setParticipantsSubTabs("result_answers");
	
		include_once "Modules/Poll/classes/class.ilPollAnswerTableGUI.php";
		$tbl = new ilPollAnswerTableGUI($this, "showParticipants");	
		$tpl->setContent($tbl->getHTML());		
	}
	
	function showParticipantVotes()
	{
		global $ilTabs, $lng, $tpl;
		
		if(!$this->checkPermissionBool("write") || 
			!$this->object->getNonAnonymous())
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}
		
		$ilTabs->activateTab("participants");
		$this->setParticipantsSubTabs("result_users");
		
		include_once "Modules/Poll/classes/class.ilPollUserTableGUI.php";
		$tbl = new ilPollUserTableGUI($this, "showParticipantVotes");	
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
		
		$valid = true;
		if($this->object->getMaxNumberOfAnswers() > 1)
		{
			if(sizeof($_POST["aw"]) > $this->object->getMaxNumberOfAnswers())
			{
				$valid = false;
			}		
			if(!sizeof($_POST["aw"]))
			{
				$valid = false;
			}
		}
		else
		{
			if((int)!$_POST["aw"])
			{
				$valid = false;
			}
		}
		
		if($valid)
		{			
			unset($_SESSION["last_poll_vote"][$this->object->getId()]);		
			$this->object->saveVote($ilUser->getId(), $_POST["aw"]);	
			
			$this->sendNotifications();
		}
		else
		{			
			$_SESSION["last_poll_vote"][$this->object->getId()] = $_POST["aw"];		
		}
		
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink::_getLink($tree->getParentId($this->ref_id)));
	}		
	
	function subscribe()
	{
		global $ilUser, $tree, $lng;
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::setNotification(ilNotification::TYPE_POLL, $ilUser->getId(), $this->object->getId(), true);
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink::_getLink($tree->getParentId($this->ref_id)));
	}
	
	function unsubscribe()
	{
		global $ilUser, $tree, $lng;
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::setNotification(ilNotification::TYPE_POLL, $ilUser->getId(), $this->object->getId(), false);
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink::_getLink($tree->getParentId($this->ref_id)));
	}
	
	protected function sendNotifications()
	{
		global $ilUser;
		
		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_POLL, 
			$this->object->getId(), null, true);		
		if(!sizeof($users))
		{
			return;
		}			
											
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();		
		$ntf->setLangModules(array("poll"));
		$ntf->setRefId($this->ref_id);
		
		if($this->object->getNonAnonymous())
		{
			$ntf->setChangedByUserId($ilUser->getId());
		}
		
		$ntf->setSubjectLangId('poll_vote_notification_subject');
		$ntf->setIntroductionLangId('poll_vote_notification_body');		
		$ntf->setGotoLangId('poll_vote_notification_link');				
		$ntf->setReasonLangId('poll_vote_notification_reason');				
				
		$notified = $ntf->sendMail($users, null, "read");								

		ilNotification::updateNotificationTime(ilNotification::TYPE_POLL,  $this->object->getId(), $notified);				
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
	public static function _goto($a_target)
	{						
		global $tree, $ilAccess;
		
		$id = explode("_", $a_target);		
		$ref_id = $id[0];
					
		// #13728 - used in notification mostly
		if ($ilAccess->checkAccess("write", "", $ref_id))
		{
			$_GET["baseClass"] = "ilRepositoryGUI";	
			$_GET["ref_id"] = $ref_id;		
			$_GET["cmd"] = "showParticipants";		
			include("ilias.php");
			exit;		 
		}
		else
		{
			// is sideblock: so show parent instead
			$container_id = $tree->getParentId($ref_id);

			// #11810
			include_once "Services/Link/classes/class.ilLink.php";
			ilUtil::redirect(ilLink::_getLink($container_id).
				"#poll".ilObject::_lookupObjId($id[0]));
		}		
	}
}

?>