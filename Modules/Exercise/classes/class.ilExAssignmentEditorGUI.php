<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

/**
* Class ilExAssignmentEditorGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExAssignmentEditorGUI: ilFileSystemGUI, ilExPeerReviewGUI
* 
* @ingroup ModulesExercise
*/
class ilExAssignmentEditorGUI 
{
	protected $exercise_id; // [int]
	protected $assignment; // [ilExAssignment]
	protected $enable_peer_review_completion; // [bool]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_exercise_id
	 * @param bool  $a_enable_peer_review_completion_settings
	 * @param ilExAssignment $a_ass
	 * @return object
	 */
	public function __construct($a_exercise_id, $a_enable_peer_review_completion_settings, ilExAssignment $a_ass = null)
	{
		$this->exercise_id = $a_exercise_id;
		$this->assignment = $a_ass;
		$this->enable_peer_review_completion = (bool)$a_enable_peer_review_completion_settings;
	}
	
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listAssignments");		
		
		switch($class)
		{		
			// instruction files
			case "ilfilesystemgui":				
				$this->setAssignmentHeader();
				$ilTabs->activateTab("ass_files");
				
				include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
				$fstorage = new ilFSStorageExercise($this->exercise_id, $this->assignment->getId());
				$fstorage->create();
				
				include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
				$fs_gui = new ilFileSystemGUI($fstorage->getPath());
				$fs_gui->setTitle($lng->txt("exc_instruction_files"));
				$fs_gui->setTableId("excassfil".$this->assignment->getId());
				$fs_gui->setAllowDirectories(false);
				$ilCtrl->forwardCommand($fs_gui);				
				break;
					
			case "ilexpeerreviewgui":							
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "listAssignments"));
		
				include_once("./Modules/Exercise/classes/class.ilExPeerReviewGUI.php");
				$peer_gui = new ilExPeerReviewGUI($this->assignment);
				$ilCtrl->forwardCommand($peer_gui);
				break;
			
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}
	
	/**
	 * List assignments
	 */
	function listAssignmentsObject()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilSelectInputGUI.php";		
		$ilToolbar->addInputItem($this->getTypeDropdown());		
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addAssignment"));				
		$ilToolbar->addFormButton($lng->txt("exc_add_assignment"), "addAssignment");
		
		include_once("./Modules/Exercise/classes/class.ilAssignmentsTableGUI.php");
		$t = new ilAssignmentsTableGUI($this, "listAssignments", $this->exercise_id);
		$tpl->setContent($t->getHTML());
	}
	
	/**
	 * Create assignment
	 */
	function addAssignmentObject()
	{
		global $tpl, $ilCtrl;
		
		if(!(int)$_POST["type"])
		{
			$ilCtrl->redirect($this, "listAssignments");
		}
		
		$form = $this->initAssignmentForm((int)$_POST["type"], "create");
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Get type selection dropdown
	 * 
	 * @return ilSelectInputGUI
	 */
	protected function getTypeDropdown()
	{
		global $ilSetting, $lng;
		
		$types = array(
			ilExAssignment::TYPE_UPLOAD => $lng->txt("exc_type_upload"),
			ilExAssignment::TYPE_UPLOAD_TEAM => $lng->txt("exc_type_upload_team"),
			ilExAssignment::TYPE_TEXT => $lng->txt("exc_type_text")
		);
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$types[ilExAssignment::TYPE_BLOG] = $lng->txt("exc_type_blog");
		}
		if($ilSetting->get('user_portfolios'))
		{
			$types[ilExAssignment::TYPE_PORTFOLIO] = $lng->txt("exc_type_portfolio");
		}		
		$ty = new ilSelectInputGUI($lng->txt("exc_assignment_type"), "type");
		$ty->setOptions($types);
		$ty->setRequired(true);
		return $ty;				
	}
	
	/**
	* Init assignment form.
	*
	* @param int $a_type
	* @param int $a_mode "create"/"edit"
	*/
	protected function initAssignmentForm($a_type, $a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("form");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTableWidth("600px");
		if ($a_mode == "edit")
		{
			$form->setTitle($lng->txt("exc_edit_assignment"));
		}
		else
		{
			$form->setTitle($lng->txt("exc_new_assignment"));
		}
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		// type
		$ty = $this->getTypeDropdown();
		$ty->setValue($a_type);
		$ty->setDisabled(true);
		$form->addItem($ty);
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setRequired(true);
		$form->addItem($ti);
		
		// start time y/n
		$cb = new ilCheckboxInputGUI($lng->txt("exc_start_time"), "start_time_cb");
		$form->addItem($cb);
		
			// start time
			$edit_date = new ilDateTimeInputGUI("", "start_time");
			$edit_date->setShowTime(true);
			$cb->addSubItem($edit_date);
		
			
		// deadline y/n
		$dcb = new ilCheckboxInputGUI($lng->txt("exc_deadline"), "deadline_cb");
		$dcb->setChecked(true);
		$form->addItem($dcb);

			// Deadline
			$edit_date = new ilDateTimeInputGUI($lng->txt(""), "deadline");
			$edit_date->setShowTime(true);
			$dcb->addSubItem($edit_date);
			
			// extended deadline y/n
			$edcb = new ilCheckboxInputGUI($lng->txt("exc_deadline_extended"), "deadline2_cb");
			$dcb->addSubItem($edcb);
			
				// extended Deadline
				$deadline2 = new ilDateTimeInputGUI($lng->txt(""), "deadline2");
				$deadline2->setInfo($lng->txt("exc_deadline_extended_info"));
				$deadline2->setShowTime(true);
				$edcb->addSubItem($deadline2);

		// mandatory
		$cb = new ilCheckboxInputGUI($lng->txt("exc_mandatory"), "mandatory");
		$cb->setInfo($lng->txt("exc_mandatory_info"));
		$cb->setChecked(true);
		$form->addItem($cb);

		// Work Instructions
		$desc_input = new ilTextAreaInputGUI($lng->txt("exc_instruction"), "instruction");
		$desc_input->setRows(20);
		$desc_input->setUseRte(true);				
		$desc_input->setRteTagSet("mini");		
		$form->addItem($desc_input);		
				
		// files		
		if ($a_mode == "create")
		{
			$files = new ilFileWizardInputGUI($lng->txt('objs_file'),'files');
			$files->setFilenames(array(0 => ''));
			$form->addItem($files);						
		}
		
		// max number of files
		if($a_type == ilExAssignment::TYPE_UPLOAD ||
			$a_type == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$max_file_tgl = new ilCheckboxInputGUI($lng->txt("exc_max_file_tgl"), "max_file_tgl");
			$form->addItem($max_file_tgl);
		
				$max_file = new ilNumberInputGUI($lng->txt("exc_max_file"), "max_file");
				$max_file->setInfo($lng->txt("exc_max_file_info"));
				$max_file->setRequired(true);
				$max_file->setSize(3);
				$max_file->setMinValue(1);
				$max_file_tgl->addSubItem($max_file);			
		}		
				
		if($a_type == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$cbtut = new ilCheckboxInputGUI($lng->txt("exc_team_management_tutor"), "team_tutor");
			$cbtut->setInfo($lng->txt("exc_team_management_tutor_info"));
			$cbtut->setChecked(false);
			$form->addItem($cbtut);
		}
		else
		{
			// peer review

			$peer = new ilCheckboxInputGUI($lng->txt("exc_peer_review"), "peer");		
			$peer->setInfo($lng->txt("exc_peer_review_ass_setting_info"));
			$form->addItem($peer);

				$peer_min = new ilNumberInputGUI($lng->txt("exc_peer_review_min_number"), "peer_min");
				// $peer_min->setInfo($lng->txt("exc_peer_review_min_number_info"));
				$peer_min->setRequired(true);
				$peer_min->setSize(3);
				$peer_min->setValue(2);
				$peer->addSubItem($peer_min);

				$peer_unlock = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_simple_unlock"), "peer_unlock");
				$peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_active"), 1));
				$peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_inactive"), 0));
				$peer_unlock->setRequired(true);		
				$peer_unlock->setValue(0);
				$peer->addSubItem($peer_unlock);

				if($this->enable_peer_review_completion)
				{
					$peer_cmpl = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_completion"), "peer_valid");
					$peer_cmpl->addOption(new ilRadioOption($lng->txt("exc_peer_review_completion_none"), ilExAssignment::PEER_REVIEW_VALID_NONE));
					$peer_cmpl->addOption(new ilRadioOption($lng->txt("exc_peer_review_completion_one"), ilExAssignment::PEER_REVIEW_VALID_ONE));
					$peer_cmpl->addOption(new ilRadioOption($lng->txt("exc_peer_review_completion_all"), ilExAssignment::PEER_REVIEW_VALID_ALL));
					$peer_cmpl->setRequired(true);		
					$peer_cmpl->setValue(ilExAssignment::PEER_REVIEW_VALID_NONE);
					$peer->addSubItem($peer_cmpl);
				}

				$peer_dl = new ilDateTimeInputGUI($lng->txt("exc_peer_review_deadline"), "peer_dl");
				$peer_dl->setInfo($lng->txt("exc_peer_review_deadline_info"));
				$peer_dl->enableDateActivation("", "peer_dl_tgl");
				$peer_dl->setShowTime(true);
				$peer->addSubItem($peer_dl);

				$peer_char_tgl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_min_chars_tgl"), "peer_char_tgl");
				$peer->addSubItem($peer_char_tgl);

					$peer_char = new ilNumberInputGUI($lng->txt("exc_peer_review_min_chars"), "peer_char");
					$peer_char->setInfo($lng->txt("exc_peer_review_min_chars_info"));
					$peer_char->setRequired(true);
					$peer_char->setSize(3);
					$peer_char_tgl->addSubItem($peer_char);

				$peer_file = new ilCheckboxInputGUI($lng->txt("exc_peer_review_file"), "peer_file");				
				$peer_file->setInfo($lng->txt("exc_peer_review_file_info"));
				$peer->addSubItem($peer_file);

				$peer_prsl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_personal"), "peer_prsl");				
				$peer_prsl->setInfo($lng->txt("exc_peer_review_personal_info"));
				$peer->addSubItem($peer_prsl);
		}
		
		
		// global feedback
		
		$fb = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file"), "fb");				
		$form->addItem($fb);
		
			$fb_file = new ilFileInputGUI($lng->txt("file"), "fb_file");
			$fb_file->setRequired(true); // will be disabled on update if file exists - see getAssignmentValues()
			// $fb_file->setAllowDeletion(true); makes no sense if required (overwrite or keep)
			$fb->addSubItem($fb_file);
		
			$fb_date = new ilRadioGroupInputGUI($lng->txt("exc_global_feedback_file_date"), "fb_date");
			$fb_date->setRequired(true);
			$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_deadline"), ilExAssignment::FEEDBACK_DATE_DEADLINE));
			$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_upload"), ilExAssignment::FEEDBACK_DATE_SUBMISSION));
			$fb->addSubItem($fb_date);

			$fb_cron = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file_cron"), "fb_cron");
			$fb_cron->setInfo($lng->txt("exc_global_feedback_file_cron_info"));
			$fb->addSubItem($fb_cron);
		
			
		if ($a_mode == "create")
		{
			$form->addCommandButton("saveAssignment", $lng->txt("save"));
			$form->addCommandButton("listAssignments", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("updateAssignment", $lng->txt("save"));
			$form->addCommandButton("listAssignments", $lng->txt("cancel"));
		}
		
		return $form;
	}
	
	/**
	 * Custom form validation
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @return array
	 */
	protected function processForm(ilPropertyFormGUI $a_form)
	{
		global $lng;
		
		if($a_form->checkInput())
		{			
			$valid = true;
			
			// dates
			
			$time_start = $a_form->getInput("start_time_cb")
				? $a_form->getItemByPostVar("start_time")->getDate()->get(IL_CAL_UNIX)
				: null;
			$time_deadline = $a_form->getInput("deadline_cb")
				? $a_form->getItemByPostVar("deadline")->getDate()->get(IL_CAL_UNIX)
				: null;
			$time_deadline_ext = $a_form->getInput("deadline2_cb")
				? $a_form->getItemByPostVar("deadline2")->getDate()->get(IL_CAL_UNIX)
				: null;			
			$time_peer =  ($a_form->getInput("peer") && $a_form->getInput("peer_dl_tgl"))
				? $a_form->getItemByPostVar("peer_dl")->getDate()->get(IL_CAL_UNIX)
				: null;
			
			// no deadline?
			if(!$time_deadline)			
			{
				// peer review
				if($a_form->getInput("peer"))
				{
					$a_form->getItemByPostVar("peer")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}			
				// global feedback
				if($a_form->getInput("fb"))
				{
					$a_form->getItemByPostVar("fb")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}				 
			}
			else
			{			
				if($time_deadline_ext && $time_deadline_ext < $time_deadline)
				{
					$a_form->getItemByPostVar("deadline2")
						->setAlert($lng->txt("exc_deadline_ext_mismatch"));
					$valid = false;		
				}
					
				$time_deadline_min = $time_deadline_ext 
					? min($time_deadline, $time_deadline_ext)
					: $time_deadline;
				$time_deadline_max = max($time_deadline, $time_deadline_ext);				
			
				// start > any deadline ?
				if($time_start && $time_deadline_min && $time_start > $time_deadline_min)
				{											
					$a_form->getItemByPostVar("start_time")
						->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));					
					$valid = false;						
				}
				
				// peer < any deadline?							
				if($time_peer && $time_deadline_max && $time_peer < $time_deadline_max)
				{					
					$a_form->getItemByPostVar("peer_dl")
						->setAlert($lng->txt("exc_peer_deadline_mismatch"));
					$valid = false;					
				}			
			}
			
			if($valid)
			{
				$res = array(
					// core
					"type" => $a_form->getInput("type")
					,"title" => trim($a_form->getInput("title"))
					,"instruction" => trim($a_form->getInput("instruction"))
					,"mandatory" => $a_form->getInput("mandatory")					
					// dates
					,"start" => $time_start
					,"deadline" => $time_deadline
					,"deadline_ext" => $time_deadline_ext
					,"max_file" => $a_form->getInput("max_file_tgl")
						? $a_form->getInput("max_file")
						: null
					,"team_tutor" => $a_form->getInput("team_tutor")							
				);				
			
				// peer
				if($a_form->getInput("peer"))
				{
					$res["peer"] = true;
					$res["peer_min"] = $a_form->getInput("peer_min");
					$res["peer_file"] = $a_form->getInput("peer_file");
					$res["peer_char"] = $a_form->getInput("peer_char");
					$res["peer_unlock"] = $a_form->getInput("peer_unlock");
					$res["peer_dl"] = $time_peer;
					$res["peer_prsl"] = $a_form->getInput("peer_prsl");
					$res["peer_valid"] = $this->enable_peer_review_completion
						? $a_form->getInput("peer_valid")
						: null;				
				}
				
				// files
				if(is_array($_FILES["files"]))
				{
					foreach($_FILES["files"] as $file)
					{
						if($file["tmp_name"])
						{
							$res["files"][] = $file;
						}
					}					
				}
				
				// global feedback				
				if($a_form->getInput("fb"))
				{
					$res["fb"] = true;
					$res["fb_cron"] = $a_form->getInput("fb_cron");
					$res["fb_date"] = $a_form->getInput("fb_date");	
					if($_FILES["fb_file"]["tmp_name"])
					{
						$res["fb_file"] = $_FILES["fb_file"];
					}						
				}
				
				return $res;
			}
			else
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
		}
	}
	
	/**
	 * Import form values to assignment
	 * 
	 * @param ilExAssignment $a_ass
	 * @param array $a_input
	 */
	protected function importFormToAssignment(ilExAssignment $a_ass, array $a_input)
	{			
		$is_create = !(bool)$a_ass->getId();
		
		$a_ass->setTitle($a_input["title"]);
		$a_ass->setInstruction($a_input["instruction"]);			
		$a_ass->setMandatory($a_input["mandatory"]);	

		$a_ass->setStartTime($a_input["start"]);
		$a_ass->setDeadline($a_input["deadline"]);
		$a_ass->setExtendedDeadline($a_input["deadline_ext"]);
									
		$a_ass->setMaxFile($a_input["max_file"]);		
		$a_ass->setTeamTutor($a_input["team_tutor"]);			
									
		if(!$a_input["peer"])
		{
			$a_ass->setPeerReview(false);
		}
		else
		{	
			$protected_peer_review_groups = false;	
			if(!$is_create)
			{
				include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
				$peer_review = new ilExPeerReview($a_ass);
				if($peer_review->hasPeerReviewGroups())
				{
					$protected_peer_review_groups = true;
				}
			}			
			if(!$protected_peer_review_groups)
			{
				$a_ass->setPeerReview(true);
				$a_ass->setPeerReviewMin($a_input["peer_min"]);
				$a_ass->setPeerReviewFileUpload($a_input["peer_file"]);
				$a_ass->setPeerReviewChars($a_input["peer_char"]);
				$a_ass->setPeerReviewSimpleUnlock($a_input["peer_unlock"]);
				$a_ass->setPeerReviewValid($a_input["peer_valid"]);						
				$a_ass->setPeerReviewPersonalized($a_input["peer_prsl"]);	
				
				// :TODO:
				$a_ass->setPeerReviewDeadline($a_input["peer_dl"]);	
			}
		}	
		
		if($a_input["fb"])
		{
			$a_ass->setFeedbackCron($a_input["fb_cron"]); // #13380
			$a_ass->setFeedbackDate($a_input["fb_date"]);
		}
		
		// id needed for file handling
		if($is_create)
		{					
			// assignment files
			if(is_array($a_input["files"]))
			{
				$a_ass->uploadAssignmentFiles($a_input["files"]);
			}
			
			$a_ass->save();					
		}
		else
		{			
			// remove global feedback file?
			if(!$a_input["fb"])
			{
				$a_ass->deleteGlobalFeedbackFile();
				$a_ass->setFeedbackFile(null);
			}
			
			$a_ass->update();		
		}
							
		// add global feedback file?
		if($a_input["fb"])
		{
			if(is_array($a_input["fb_file"]))
			{
				$a_ass->handleGlobalFeedbackFileUpload($a_input["fb_file"]);
				$a_ass->update();
			}	
		}		
	}
	
	/**
	* Save assignment
	*
	*/
	public function saveAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initAssignmentForm((int)$_POST["type"], "create");
		$input = $this->processForm($form);
		if(is_array($input))
		{								
			$ass = new ilExAssignment();
			$ass->setExerciseId($this->exercise_id);
			$ass->setType($input["type"]);	
			
			$this->importFormToAssignment($ass, $input);			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
						
			// adopt teams for team upload?
			if($ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{				
				include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
				if(sizeof(ilExAssignmentTeam::getAdoptableTeamAssignments($this->exercise_id, $ass->getId())))
				{
					$ilCtrl->setParameter($this, "ass_id", $ass->getId());
					$ilCtrl->redirect($this, "adoptTeamAssignmentsForm");
				}
			}			
			
			$ilCtrl->redirect($this, "listAssignments");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

	/**
	 * Edit assignment
	 */
	function editAssignmentObject()
	{
		global $tpl, $ilTabs, $tpl;
		
		$this->setAssignmentHeader();
		$ilTabs->activateTab("ass_settings");
		
		$form = $this->initAssignmentForm($this->assignment->getType(), "edit");
		$this->getAssignmentValues($form);
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Get current values for assignment from 	 
	 */
	public function getAssignmentValues(ilPropertyFormGUI $a_form)
	{
		$values = array();	
		$values["type"] = $this->assignment->getType();
		$values["title"] = $this->assignment->getTitle();
		$values["mandatory"] = $this->assignment->getMandatory();
		$values["instruction"] = $this->assignment->getInstruction();
				
		if ($this->assignment->getStartTime())
		{
			$values["start_time_cb"] = true;
			$edit_date = new ilDateTime($this->assignment->getStartTime(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("start_time");
			$ed_item->setDate($edit_date);
		}
						
		if ($this->assignment->getDeadline() > 0)
		{
			$values["deadline_cb"] = true;
			$edit_date = new ilDateTime($this->assignment->getDeadline(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("deadline");
			$ed_item->setDate($edit_date);
			
			if ($this->assignment->getExtendedDeadline() > 0)
			{
				$values["deadline2_cb"] = true;
				$edit_date = new ilDateTime($this->assignment->getExtendedDeadline(), IL_CAL_UNIX);
				$ed_item = $a_form->getItemByPostVar("deadline2");
				$ed_item->setDate($edit_date);
			}
		}
				
		if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD ||
			$this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			if ($this->assignment->getMaxFile())
			{
				$values["max_file_tgl"] = true;
				$values["max_file"] = $this->assignment->getMaxFile();
			}
		}
					
		if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{		
			$values["team_tutor"] = $this->assignment->getTeamTutor();
		}
		else
		{
			// peer
			
			$values["peer"] = $this->assignment->getPeerReview();
			$values["peer_min"] = $this->assignment->getPeerReviewMin();
			$values["peer_file"] = $this->assignment->hasPeerReviewFileUpload();
			$values["peer_prsl"] = $this->assignment->hasPeerReviewPersonalized();
			$values["peer_unlock"] = $this->assignment->getPeerReviewSimpleUnlock();
			$values["peer_valid"] = $this->assignment->getPeerReviewValid();
				
			if ($this->assignment->getPeerReviewDeadline() > 0)
			{
				$values["peer_dl_tgl"] = true;
				$peer_dl_date = new ilDateTime($this->assignment->getPeerReviewDeadline(), IL_CAL_UNIX);
				$peer_dl = $a_form->getItemByPostVar("peer_dl");
				$peer_dl->setDate($peer_dl_date);
			}		
			
			if ($this->assignment->getPeerReviewChars() > 0)
			{
				$values["peer_char_tgl"] = true;				
				$values["peer_char"] = $this->assignment->getPeerReviewChars();		
			}
			
			include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
			$peer_review = new ilExPeerReview($this->assignment);
			
			// #14450
			if ($values["peer"] && 
				$peer_review->hasPeerReviewGroups())
			{
				// deadline(s) are past and must not change
				$a_form->getItemByPostVar("deadline_cb")->setDisabled(true);			
				$a_form->getItemByPostVar("deadline")->setDisabled(true);	
				$a_form->getItemByPostVar("deadline2_cb")->setDisabled(true);	
				$a_form->getItemByPostVar("deadline2")->setDisabled(true);	
				
				// JF, 2015-05-11 - editable again
				// $a_form->getItemByPostVar("peer_dl")->setDisabled(true);
				
				$a_form->getItemByPostVar("peer")->setDisabled(true);			   
				$a_form->getItemByPostVar("peer_min")->setDisabled(true);				
				$a_form->getItemByPostVar("peer_file")->setDisabled(true);
				$a_form->getItemByPostVar("peer_prsl")->setDisabled(true);									
				$a_form->getItemByPostVar("peer_char_tgl")->setDisabled(true);									
				$a_form->getItemByPostVar("peer_char")->setDisabled(true);									
				$a_form->getItemByPostVar("peer_unlock")->setDisabled(true);
				
				if($this->enable_peer_review_completion)
				{
					$a_form->getItemByPostVar("peer_valid")->setDisabled(true);									
				}				
			}			 
		}		
		
		// global feedback		
		if($this->assignment->getFeedbackFile())
		{						
			$a_form->getItemByPostVar("fb")->setChecked(true);			
			$a_form->getItemByPostVar("fb_file")->setValue(basename($this->assignment->getGlobalFeedbackFilePath()));	
			$a_form->getItemByPostVar("fb_file")->setRequired(false); // #15467
		}
		$a_form->getItemByPostVar("fb_cron")->setChecked($this->assignment->hasFeedbackCron());			
		$a_form->getItemByPostVar("fb_date")->setValue($this->assignment->getFeedbackDate());					
		
		$a_form->setValuesByArray($values);
	}

	/**
	 * Update assignment
	 *
	 */
	public function updateAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initAssignmentForm($this->assignment->getType(), "edit");
		$input = $this->processForm($form);
		if(is_array($input))
		{							
			$old_deadline = $this->assignment->getDeadline();
			$old_ext_deadline = $this->assignment->getExtendedDeadline();
			
			$this->importFormToAssignment($this->assignment, $input);
			
			$new_deadline = $this->assignment->getDeadline();
			$new_ext_deadline = $this->assignment->getExtendedDeadline();
			
			// if deadlines were changed
			if($old_deadline != $new_deadline ||
				$old_ext_deadline != $new_ext_deadline)
			{
				$this->assignment->recalculateLateSubmissions();								
			}

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editAssignment");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	* Confirm assignments deletion
	*/
	function confirmAssignmentsDeletionObject()
	{
		global $ilCtrl, $tpl, $lng;
		
		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listAssignments");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("exc_conf_del_assignments"));
			$cgui->setCancel($lng->txt("cancel"), "listAssignments");
			$cgui->setConfirm($lng->txt("delete"), "deleteAssignments");
			
			foreach ($_POST["id"] as $i)
			{
				$cgui->addItem("id[]", $i, ilExAssignment::lookupTitle($i));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete assignments
	 */
	function deleteAssignmentsObject()
	{
		global $ilCtrl, $lng;
		
		$delete = false;
		if (is_array($_POST["id"]))
		{			
			foreach($_POST["id"] as $id)
			{
				$ass = new ilExAssignment(ilUtil::stripSlashes($id));
				$ass->delete();
				$delete = true;
			}
		}
		
		if ($delete)
		{
			ilUtil::sendSuccess($lng->txt("exc_assignments_deleted"), true);
		}
		$ilCtrl->setParameter($this, "ass_id", "");
		$ilCtrl->redirect($this, "listAssignments");
	}
	
	/**
	 * Save assignments order
	 */
	function saveAssignmentOrderObject()
	{
		global $lng, $ilCtrl;
				
		ilExAssignment::saveAssOrderOfExercise($this->exercise_id, $_POST["order"]);
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}
	
	/**
	 * Order by deadline
	 */
	function orderAssignmentsByDeadlineObject()
	{
		global $lng, $ilCtrl;
				
		ilExAssignment::orderAssByDeadline($this->exercise_id);
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}

	/**
	 * Set assignment header
	 */
	function setAssignmentHeader()
	{
		global $ilTabs, $lng, $ilCtrl, $tpl, $ilHelp;
				
		$tpl->setTitle($this->assignment->getTitle());
		$tpl->setDescription("");
		
		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("exc");
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listAssignments"));

		$ilTabs->addTab("ass_settings",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "editAssignment"));

		$ilTabs->addTab("ass_files",
			$lng->txt("exc_instruction_files"),
			$ilCtrl->getLinkTargetByClass(array("ilexassignmenteditorgui", "ilfilesystemgui"), "listFiles"));
	}
	
	
	//
	// TEAM
	// 
	
	public function adoptTeamAssignmentsFormObject()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		if(!$this->assignment)
		{
			$ilCtrl->redirect($this, "listAssignments");
		}
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listAssignments"));
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();		         
		$form->setTitle($lng->txt("exc_team_assignment_adopt"));
		$form->setFormAction($ilCtrl->getFormAction($this, "adoptTeamAssignments"));
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
		$options = ilExAssignmentTeam::getAdoptableTeamAssignments($this->assignment->getExerciseId());
		
		// we must not have existing teams in assignment
		if(array_key_exists($this->assignment->getId(), $options))
		{
			$ilCtrl->redirect($this, "listAssignments");
		}
		
		$teams = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
		$teams->setValue(-1);
		
		$teams->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none"), -1));
		
		foreach($options as $id => $item)
		{
			$option = new ilRadioOption($item["title"], $id);
			$option->setInfo($lng->txt("exc_team_assignment_adopt_teams").": ".$item["teams"]);
			$teams->addOption($option);
		}
		
		$form->addItem($teams);
	
		$form->addCommandButton("adoptTeamAssignments", $lng->txt("save"));
		$form->addCommandButton("listAssignments", $lng->txt("cancel"));

		$tpl->setContent($form->getHTML());
	}
	
	public function adoptTeamAssignmentsObject()
	{
		global $ilCtrl, $lng;
		
		$src_ass_id = (int)$_POST["ass_adpt"];
		
		if($this->assignment && 
			$src_ass_id > 0)
		{
			include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
			ilExAssignmentTeam::adoptTeams($src_ass_id, $this->assignment->getId());			
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
							
		$ilCtrl->redirect($this, "listAssignments");		
	}
}
