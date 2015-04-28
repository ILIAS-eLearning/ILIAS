<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		
		$ilToolbar->addButton($lng->txt("exc_add_assignment"),
			$ilCtrl->getLinkTarget($this, "addAssignment"));
		
		include_once("./Modules/Exercise/classes/class.ilAssignmentsTableGUI.php");
		$t = new ilAssignmentsTableGUI($this, "listAssignments", $this->exercise_id);
		$tpl->setContent($t->getHTML());
	}
	
	/**
	 * Create assignment
	 */
	function addAssignmentObject()
	{
		global $tpl;
		
		$form = $this->initAssignmentForm("create");
		$tpl->setContent($form->getHTML());
	}
	
	/**
	* Init assignment form.
	*
	* @param int $a_mode "create"/"edit"
	*/
	public function initAssignmentForm($a_mode = "create")
	{
		global $lng, $ilCtrl, $ilSetting;

		// init form
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
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$types = array(ilExAssignment::TYPE_UPLOAD => $lng->txt("exc_type_upload"),
			ilExAssignment::TYPE_UPLOAD_TEAM => $lng->txt("exc_type_upload_team"),
			ilExAssignment::TYPE_TEXT => $lng->txt("exc_type_text"));
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$types[ilExAssignment::TYPE_BLOG] = $lng->txt("exc_type_blog");
		}
		if($ilSetting->get('user_portfolios'))
		{
			$types[ilExAssignment::TYPE_PORTFOLIO] = $lng->txt("exc_type_portfolio");
		}
		if(sizeof($types) > 1)
		{
			$ty = new ilSelectInputGUI($lng->txt("exc_assignment_type"), "type");
			$ty->setOptions($types);
			$ty->setRequired(true);
		}
		else
		{
			$ty = new ilHiddenInputGUI("type");
			$ty->setValue(ilExAssignment::TYPE_UPLOAD);			
		}
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
		else if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD ||
			$this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$max_file_tgl = new ilCheckboxInputGUI($lng->txt("exc_max_file_tgl"), "max_file_tgl");
			$form->addItem($max_file_tgl);
		
				$max_file = new ilNumberInputGUI($lng->txt("exc_max_file"), "max_file");
				$max_file->setInfo($lng->txt("exc_max_file_info"));
				$max_file->setRequired(true);
				$max_file->setSize(3);
				$max_file->setMinValue(1);
				$max_file_tgl->addSubItem($max_file);
			
			if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$cbtut = new ilCheckboxInputGUI($lng->txt("exc_team_management_tutor"), "team_tutor");
				$cbtut->setInfo($lng->txt("exc_team_management_tutor_info"));
				$cbtut->setChecked(false);
				$form->addItem($cbtut);
			}
		}
				
		// peer review
		$peer = new ilCheckboxInputGUI($lng->txt("exc_peer_review"), "peer");		
		$peer->setInfo($lng->txt("exc_peer_review_ass_setting_info"));
		$form->addItem($peer);
		
		if ($a_mode == "create")
		{
			$peer->setInfo($lng->txt("exc_peer_review_info"));
		}
		
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

			if($a_mode != "create" && // #13745
				$this->assignment && 
				$this->assignment->getDeadline() && $this->assignment->getDeadline() < time())
			{
				$peer_prsl->setDisabled(true);
			}
		
		
		// global feedback
		
		$fb = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file"), "fb");				
		$form->addItem($fb);
		
			$fb_file = new ilFileInputGUI($lng->txt("file"), "fb_file");
			$fb_file->setRequired(true); // will be disabled on update if file exists (see below)
			// $fb_file->setAllowDeletion(true); makes no sense if required (overwrite or keep)
			$fb->addSubItem($fb_file);
		
			// #15467
			if($a_mode != "create" && 
				$this->assignment && 
				$this->assignment->getFeedbackFile())
			{
				$fb_file->setRequired(false); 
			}

			$fb_date = new ilRadioGroupInputGUI($lng->txt("exc_global_feedback_file_date"), "fb_date");
			$fb_date->setRequired(true);
			$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_deadline"), ilExAssignment::FEEDBACK_DATE_DEADLINE));
			$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_upload"), ilExAssignment::FEEDBACK_DATE_SUBMISSION));
			$fb->addSubItem($fb_date);

			$fb_cron = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file_cron"), "fb_cron");
			$fb_cron->setInfo($lng->txt("exc_global_feedback_file_cron_info"));
			$fb->addSubItem($fb_cron);
		
		// save and cancel commands
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
	* Save assignment
	*
	*/
	public function saveAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initAssignmentForm("create");
		if ($form->checkInput())
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			
			// additional checks
			
			$valid = true;
			
			if ($_POST["start_time_cb"] && $_POST["deadline_cb"])
			{
				// check whether start date is before end date
				$start_date =
					$form->getItemByPostVar("start_time")->getDate();
				$end_date =
					$form->getItemByPostVar("deadline")->getDate();
				if ($start_date->get(IL_CAL_UNIX) >=
					$end_date->get(IL_CAL_UNIX))
				{					
					$form->getItemByPostVar("start_time")
						->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
					$form->getItemByPostVar("deadline")
						->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
					$valid = false;		
				}
			}
			
			if($_POST["type"] == ilExAssignment::TYPE_UPLOAD_TEAM && $_POST["peer"])
			{				
				$form->getItemByPostVar("peer")
					->setAlert($lng->txt("exc_team_upload_not_supported"));
				$valid = false;
			}
			
			if(!$_POST["deadline_cb"])
			{
				if($_POST["peer"])
				{
					$form->getItemByPostVar("peer")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}					
				if($_POST["fb"])
				{
					$form->getItemByPostVar("fb")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}				 
			}
			else
			{
				if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM &&
					$_POST["peer"] && 
					$_POST["peer_dl_tgl"])
				{
					$peer_dl =	$form->getItemByPostVar("peer_dl")->getDate();					
					$peer_dl = $peer_dl->get(IL_CAL_UNIX);										
					$end_date = $form->getItemByPostVar("deadline")->getDate();
					$end_date = $end_date->get(IL_CAL_UNIX);
					
					// #13877
					if ($peer_dl < $end_date)
					{
						$form->getItemByPostVar("peer_dl")
							->setAlert($lng->txt("exc_peer_deadline_mismatch"));
						$valid = false;
					}
				}			
			}
			
			if(!$valid)
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
				$form->setValuesByPost();		
				$tpl->setContent($form->getHtml());
				return;
			}
			
			$ass = new ilExAssignment();
			$ass->setTitle($_POST["title"]);
			$ass->setInstruction($_POST["instruction"]);
			$ass->setExerciseId($this->exercise_id);
			$ass->setMandatory($_POST["mandatory"]);
			$ass->setType($_POST["type"]);
			
			if ($_POST["start_time_cb"])
			{
				$date =
					$form->getItemByPostVar("start_time")->getDate();
				$ass->setStartTime($date->get(IL_CAL_UNIX));
			}
			else
			{
				$ass->setStartTime(null);
			}
			
			// deadline
			if ($_POST["deadline_cb"])
			{
				$date =	$form->getItemByPostVar("deadline")->getDate();
				$date = $date->get(IL_CAL_UNIX);
				$ass->setDeadline($date);
				
				// extended deadline
				$date2 = $form->getItemByPostVar("deadline2")->getDate();
				$date2 = $date2->get(IL_CAL_UNIX);
				if ($_POST2["deadline_cb"] &&
					$date2 > $date) 
				{					
					$ass->setExtendedDeadline($date2);
				}
				else
				{
					$ass->setExtendedDeadline(null);
				}
			}
			else
			{
				$ass->setDeadline(null);
				$ass->setExtendedDeadline(null);
			}
			
			if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$ass->setPeerReview($_POST["peer"]);
				$ass->setPeerReviewMin($_POST["peer_min"]);
				$ass->setPeerReviewFileUpload($_POST["peer_file"]);
				$ass->setPeerReviewChars($_POST["peer_char"]);
				$ass->setPeerReviewSimpleUnlock($_POST["peer_unlock"]);
				
				if($this->enable_peer_review_completion)
				{
					$ass->setPeerReviewValid($_POST["peer_valid"]);
				}
				
				if($ass->getDeadline() && $ass->getDeadline() > time())
				{
					$ass->setPeerReviewPersonalized($_POST["peer_prsl"]);
				}
										
				if($_POST["peer_dl_tgl"])
				{
					$peer_dl =	$form->getItemByPostVar("peer_dl")->getDate();
					$ass->setPeerReviewDeadline($peer_dl->get(IL_CAL_UNIX));
				}
				else
				{
					$ass->setPeerReviewDeadline(null);
				}		
			}
						
			$ass->setFeedbackCron($_POST["fb_cron"]); // #13380
			$ass->setFeedbackDate($_POST["fb_date"]);

			$ass->save();
			
			// save files
			$ass->uploadAssignmentFiles($_FILES["files"]);
									
			if($_FILES["fb_file"]["tmp_name"])
			{
				$ass->handleGlobalFeedbackFileUpload($_FILES["fb_file"]);
				$ass->update();
			}
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
						
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
		
		$form = $this->initAssignmentForm("edit");
		$this->getAssignmentValues($form);
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Get current values for assignment from 
	 *
	 */
	public function getAssignmentValues(ilPropertyFormGUI $a_form)
	{
		$values = array();
	
		$values["title"] = $this->assignment->getTitle();
		if ($this->assignment->getStartTime() > 0)
		{
			$values["start_time_cb"] = true;
		}
		$values["mandatory"] = $this->assignment->getMandatory();
		$values["instruction"] = $this->assignment->getInstruction();
		$values["type"] = $this->assignment->getType();
		$values["max_file"] = $this->assignment->getMaxFile();
		if($values["max_file"])
		{
			$values["max_file_tgl"] = true;
		}
		if ($this->assignment->getDeadline() > 0)
		{
			$values["deadline_cb"] = true;
			
			if ($this->assignment->getExtendedDeadline() > 0)
			{
				$values["deadline2_cb"] = true;
			}	
		}			
		if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$a_form->removeItemByPostVar("peer");
			$a_form->removeItemByPostVar("peer_min");			
			$a_form->removeItemByPostVar("peer_file");			
			$a_form->removeItemByPostVar("peer_prsl");			
			$a_form->removeItemByPostVar("peer_unlock");			
			$a_form->removeItemByPostVar("peer_valid");			
			$a_form->removeItemByPostVar("peer_dl_tgl");			
			$a_form->removeItemByPostVar("peer_dl");			
			$a_form->removeItemByPostVar("peer_char_tgl");			
			$a_form->removeItemByPostVar("peer_char");	
			
			$values["team_tutor"] = $this->assignment->getTeamTutor();
		}
		else
		{
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
				$a_form->getItemByPostVar("deadline_cb")->setDisabled(true);			
				$a_form->getItemByPostVar("deadline")->setDisabled(true);			
				$a_form->getItemByPostVar("peer")->setDisabled(true);			   
				$a_form->getItemByPostVar("peer_min")->setDisabled(true);
				$a_form->getItemByPostVar("peer_dl")->setDisabled(true);
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
		$a_form->setValuesByArray($values);

		if ($this->assignment->getDeadline() > 0)
		{
			$edit_date = new ilDateTime($this->assignment->getDeadline(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("deadline");
			$ed_item->setDate($edit_date);
			
			if ($this->assignment->getExtendedDeadline() > 0)
			{
				$edit_date = new ilDateTime($this->assignment->getExtendedDeadline(), IL_CAL_UNIX);
				$ed_item = $a_form->getItemByPostVar("deadline2");
				$ed_item->setDate($edit_date);
			}
		}
		
		if ($this->assignment->getStartTime() > 0)
		{
			$edit_date = new ilDateTime($this->assignment->getStartTime(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("start_time");
			$ed_item->setDate($edit_date);
		}
		
		if($this->assignment->getFeedbackFile())
		{						
			$a_form->getItemByPostVar("fb")->setChecked(true);			
			$a_form->getItemByPostVar("fb_file")->setValue(basename($this->assignment->getGlobalFeedbackFilePath()));			
		}
		$a_form->getItemByPostVar("fb_cron")->setChecked($this->assignment->hasFeedbackCron());			
		$a_form->getItemByPostVar("fb_date")->setValue($this->assignment->getFeedbackDate());			
		
		// if there are any submissions we cannot change type anymore
		include_once "Modules/Exercise/classes/class.ilExSubmission.php";
		if(ilExSubmission::hasAnySubmissions($this->assignment->getId()) ||
			$this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$a_form->getItemByPostVar("type")->setDisabled(true);
		}
	}

	/**
	 * Update assignment
	 *
	 */
	public function updateAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initAssignmentForm("edit");
		if ($form->checkInput())
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
						include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
			$peer_review = new ilExPeerReview($this->assignment);
								
			// #14450
			$protected_peer_review_groups = false;			
			if($this->assignment->getPeerReview() &&
				$peer_review->hasPeerReviewGroups())
			{
				$protected_peer_review_groups = true;
			}
			
			// additional checks
			
			$valid = true;	
			
			if(!$protected_peer_review_groups)
			{
				if ($_POST["start_time_cb"] && $_POST["deadline_cb"])
				{
					// check whether start date is before end date
					$start_date =
						$form->getItemByPostVar("start_time")->getDate();
					$end_date =
						$form->getItemByPostVar("deadline")->getDate();
					if ($start_date->get(IL_CAL_UNIX) >=
						$end_date->get(IL_CAL_UNIX))
					{					
						$form->getItemByPostVar("start_time")
							->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
						$form->getItemByPostVar("deadline")
							->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
						$valid = false;					
					}
				}

				if(!$_POST["deadline_cb"])
				{
					if($_POST["peer"])
					{
						$form->getItemByPostVar("peer")
							->setAlert($lng->txt("exc_needs_deadline"));
						$valid = false;
					}	
					if($_POST["fb"] && $_POST["fb_date"] == ilExAssignment::FEEDBACK_DATE_DEADLINE)
					{
						$form->getItemByPostVar("fb")
							->setAlert($lng->txt("exc_needs_deadline"));
						$valid = false;
					}	
				}
				else
				{
					if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM &&
						$_POST["peer"] && 
						$_POST["peer_dl_tgl"])
					{
						$peer_dl =	$form->getItemByPostVar("peer_dl")->getDate();					
						$peer_dl = $peer_dl->get(IL_CAL_UNIX);										
						$end_date = $form->getItemByPostVar("deadline")->getDate();
						$end_date = $end_date->get(IL_CAL_UNIX);

						// #13877
						if ($peer_dl < $end_date)
						{
							$form->getItemByPostVar("peer_dl")
								->setAlert($lng->txt("exc_peer_deadline_mismatch"));
							$valid = false;
						}
					}			
				}					
			}
			
			if(!$valid)
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
				$form->setValuesByPost();		
				$tpl->setContent($form->getHtml());
				return;
			}
						
			$this->assignment->setTitle($_POST["title"]);
			$this->assignment->setInstruction($_POST["instruction"]);
			$this->assignment->setExerciseId($this->exercise_id);
			$this->assignment->setMandatory($_POST["mandatory"]);
			$this->assignment->setType($_POST["type"]);
			
			$this->assignment->setMaxFile($_POST["max_file_tgl"]
				? $_POST["max_file"]
				: null);			
			
			if ($_POST["start_time_cb"])
			{
				$date =
					$form->getItemByPostVar("start_time")->getDate();
				$this->assignment->setStartTime($date->get(IL_CAL_UNIX));
			}
			else
			{
				$this->assignment->setStartTime(null);
			}
			
			if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$this->assignment->setTeamTutor($_POST["team_tutor"]);
			}
			
			if(!$protected_peer_review_groups)
			{
				// deadline
				if ($_POST["deadline_cb"])
				{
					$date =	$form->getItemByPostVar("deadline")->getDate();
					$date = $date->get(IL_CAL_UNIX);
					$this->assignment->setDeadline($date);
					
					// extended deadline
					$date2 = $form->getItemByPostVar("deadline2")->getDate();
					$date2 = $date2->get(IL_CAL_UNIX);
					if ($_POST["deadline2_cb"] && 
						$date2 > $date)
					{						
						$this->assignment->setExtendedDeadline($date2);
					}
					else
					{
						$this->assignment->setExtendedDeadline(null);
					}
				}
				else
				{
					$this->assignment->setDeadline(null);
					$this->assignment->setExtendedDeadline(null);
				}

				if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$this->assignment->setPeerReview($_POST["peer"]);
					$this->assignment->setPeerReviewMin($_POST["peer_min"]);
					$this->assignment->setPeerReviewFileUpload($_POST["peer_file"]);
					$this->assignment->setPeerReviewChars($_POST["peer_char"]);
					$this->assignment->setPeerReviewSimpleUnlock($_POST["peer_unlock"]);
					
					if($this->enable_peer_review_completion)
					{
						$this->assignment->setPeerReviewValid($_POST["peer_valid"]);
					}
					
					if($this->assignment->getDeadline() && $this->assignment->getDeadline() > time())
					{
						$this->assignment->setPeerReviewPersonalized($_POST["peer_prsl"]);
					}

					if($_POST["peer_dl_tgl"])
					{
						$peer_dl = $form->getItemByPostVar("peer_dl")->getDate();				
						$this->assignment->setPeerReviewDeadline($peer_dl->get(IL_CAL_UNIX));					
					}
					else
					{
						$this->assignment->setPeerReviewDeadline(null);
					}
				}
			}
			
			if(!$_POST["fb"] ||
				$form->getItemByPostVar("fb_file")->getDeletionFlag())
			{
				$this->assignment->deleteGlobalFeedbackFile();
				$this->assignment->setFeedbackFile(null);
			}
			else if($_FILES["fb_file"]["tmp_name"]) // #15189
			{
				$this->assignment->handleGlobalFeedbackFileUpload($_FILES["fb_file"]);
			}
						
			$this->assignment->setFeedbackCron($_POST["fb_cron"]); // #13380
			$this->assignment->setFeedbackDate($_POST["fb_date"]);
			
			$this->assignment->update();
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
			
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			
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
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
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
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
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
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
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
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
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
