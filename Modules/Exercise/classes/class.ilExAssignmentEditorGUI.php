<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

/**
* Class ilExAssignmentEditorGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExAssignmentEditorGUI: ilExAssignmentFileSystemGUI, ilExPeerReviewGUI, ilPropertyFormGUI
 *
* @ingroup ModulesExercise
*/
class ilExAssignmentEditorGUI 
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

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
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tabs = $DIC->tabs();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];
		$this->toolbar = $DIC->toolbar();
		$this->settings = $DIC->settings();
		$this->help = $DIC["ilHelp"];
		$this->exercise_id = $a_exercise_id;
		$this->assignment = $a_ass;
		$this->enable_peer_review_completion = (bool)$a_enable_peer_review_completion_settings;
	}
	
	public function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listAssignments");		
		
		switch($class)
		{
			case "ilpropertyformgui":
				$form = $this->initAssignmentForm(ilExAssignment::TYPE_PORTFOLIO);
				$ilCtrl->forwardCommand($form);
				break;

			// instruction files
			case "ilexassignmentfilesystemgui":
				$this->setAssignmentHeader();
				$ilTabs->activateTab("ass_files");
				
				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fstorage = new ilFSWebStorageExercise($this->exercise_id, $this->assignment->getId());
				$fstorage->create();

				include_once("./Modules/Exercise/classes/class.ilExAssignmentFileSystemGUI.php");
				$fs_gui = new ilExAssignmentFileSystemGUI($fstorage->getPath());
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
		$tpl = $this->tpl;
		$ilToolbar = $this->toolbar;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addAssignment"));		
		
		include_once "Services/Form/classes/class.ilSelectInputGUI.php";		
		$ilToolbar->addStickyItem($this->getTypeDropdown());		
		
		include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
		$button = ilSubmitButton::getInstance();
		$button->setCaption("exc_add_assignment");
		$button->setCommand("addAssignment");			
		$ilToolbar->addStickyItem($button);
		
		
		include_once("./Modules/Exercise/classes/class.ilAssignmentsTableGUI.php");
		$t = new ilAssignmentsTableGUI($this, "listAssignments", $this->exercise_id);
		$tpl->setContent($t->getHTML());
	}
	
	/**
	 * Create assignment
	 */
	function addAssignmentObject()
	{
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		
		// #16163 - ignore ass id from request
		$this->assignment = null;

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
		$ilSetting = $this->settings;
		$lng = $this->lng;
		
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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
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

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setRequired(true);
		$form->addItem($ti);

		// type
		$ty = $this->getTypeDropdown();
		$ty->setValue($a_type);
		$ty->setDisabled(true);
		$form->addItem($ty);

		if($a_type == ilExAssignment::TYPE_TEXT)
		{
			$rb_limit_chars = new ilCheckboxInputGUI($lng->txt("exc_limit_characters"),"limit_characters");

			$min_char_limit = new ilNumberInputGUI($lng->txt("exc_min_char_limit"), "min_char_limit");
			$min_char_limit->allowDecimals(false);
			$min_char_limit->setMinValue(0);
			$min_char_limit->setSize(3);

			$max_char_limit = new ilNumberInputGUI($lng->txt("exc_max_char_limit"), "max_char_limit");
			$max_char_limit->allowDecimals(false);
			$max_char_limit->setMinValue($_POST['min_char_limit'] + 1);

			$max_char_limit->setSize(3);

			$rb_limit_chars->addSubItem($min_char_limit);
			$rb_limit_chars->addSubItem($max_char_limit);

			$form->addItem($rb_limit_chars);
		}

		// portfolio template
		if($a_type == ilExAssignment::TYPE_PORTFOLIO)
		{
			$rd_template = new ilRadioGroupInputGUI($lng->txt("exc_template"), "template");
			$rd_template->setRequired(true);
			$radio_no_template = new ilRadioOption($lng->txt("exc_without_template"), 0, $lng->txt("exc_without_template_info", "without_template_info"));
			$radio_with_template = new ilRadioOption($lng->txt("exc_with_template"), 1 , $lng->txt("exc_with_template_info", "with_template_info"));

			include_once "Services/Form/classes/class.ilRepositorySelector2InputGUI.php";
			$repo = new ilRepositorySelector2InputGUI($lng->txt("exc_portfolio_template"), "template_id");
			$repo->setRequired(true);
			if($this->assignment)
			{
				$repo->setValue($this->assignment->getPortfolioTemplateId());
			}
			$repo->getExplorerGUI()->setSelectableTypes(array("prtt"));
			$repo->getExplorerGUI()->setTypeWhiteList(array("root", "prtt", "cat", "crs", "grp"));
			$radio_with_template->addSubItem($repo);

			$rd_template->addOption($radio_no_template);
			$rd_template->addOption($radio_with_template);
			$form->addItem($rd_template);
		}

		// mandatory
		$cb = new ilCheckboxInputGUI($lng->txt("exc_mandatory"), "mandatory");
		$cb->setInfo($lng->txt("exc_mandatory_info"));
		$cb->setChecked(true);
		$form->addItem($cb);

		// Work Instructions
		$sub_header = new ilFormSectionHeaderGUI();
		$sub_header->setTitle($lng->txt("exc_work_instructions"), "work_instructions");
		$form->addItem($sub_header);

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

		// Schedule
		$sub_header = new ilFormSectionHeaderGUI();
		$sub_header->setTitle($lng->txt("exc_schedule"), "schedule");
		$form->addItem($sub_header);

		// start time
		$start_date = new ilDateTimeInputGUI($lng->txt("exc_start_time"), "start_time");
		$start_date->setShowTime(true);
		$form->addItem($start_date);
		
		// Deadline
		$deadline = new ilDateTimeInputGUI($lng->txt("exc_deadline"), "deadline");
		$deadline->setShowTime(true);
		$form->addItem($deadline);
		
		// extended Deadline
		$deadline2 = new ilDateTimeInputGUI($lng->txt("exc_deadline_extended"), "deadline2");				
		$deadline2->setInfo($lng->txt("exc_deadline_extended_info"));
		$deadline2->setShowTime(true);
		$deadline->addSubItem($deadline2);


		// max number of files
		if($a_type == ilExAssignment::TYPE_UPLOAD ||
			$a_type == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			if($a_type == ilExAssignment::TYPE_UPLOAD)
			{
				$type_name = $lng->txt("exc_type_upload");
			}
			if($a_type == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$type_name = $lng->txt("exc_type_upload_team");
			}

			// custom section depending of assignment type
			$sub_header = new ilFormSectionHeaderGUI();
			$sub_header->setTitle($type_name);
			$form->addItem($sub_header);
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
		// after submission
		$sub_header = new ilFormSectionHeaderGUI();
		$sub_header->setTitle($lng->txt("exc_after_submission"), "after_submission");
		$form->addItem($sub_header);
		if($a_type != ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			// peer review
			$peer = new ilCheckboxInputGUI($lng->txt("exc_peer_review"), "peer");		
			$peer->setInfo($lng->txt("exc_peer_review_ass_setting_info"));
			$form->addItem($peer);
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
		$lng = $this->lng;
				
		$protected_peer_review_groups = false;
		
		if($this->assignment)
		{
			if($this->assignment->getPeerReview())
			{
				include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
				$peer_review = new ilExPeerReview($this->assignment);	
				if($peer_review->hasPeerReviewGroups())
				{
					$protected_peer_review_groups = true;
				}
			}
			
			if($this->assignment->getFeedbackFile())
			{									
				$a_form->getItemByPostVar("fb_file")->setRequired(false); // #15467
			}
		}
		
		$valid = $a_form->checkInput();	
		
		if($protected_peer_review_groups)
		{
			// checkInput() will add alert to disabled fields
			$a_form->getItemByPostVar("deadline")->setAlert(null);
			$a_form->getItemByPostVar("deadline2")->setAlert(null);
		}	
		
		if($valid)
		{
			// dates
			
			$time_start = $a_form->getItemByPostVar("start_time")->getDate();
			$time_start = $time_start
				? $time_start->get(IL_CAL_UNIX)
				: null;
			$time_deadline = $a_form->getItemByPostVar("deadline")->getDate();
			$time_deadline = $time_deadline
				? $time_deadline->get(IL_CAL_UNIX)
				: null;
			$time_deadline_ext = $a_form->getItemByPostVar("deadline2")->getDate();
			$time_deadline_ext = $time_deadline_ext
				? $time_deadline_ext->get(IL_CAL_UNIX)
				: null;			
			
			// handle disabled elements
			if($protected_peer_review_groups)
			{									
				$time_deadline = $this->assignment->getDeadline();		
				$time_deadline_ext = $this->assignment->getExtendedDeadline();					
			}			
					
			// no deadline?
			if(!$time_deadline)			
			{
				// peer review
				if(!$protected_peer_review_groups &&
					$a_form->getInput("peer"))
				{
					$a_form->getItemByPostVar("peer")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}			
				// global feedback
				if($a_form->getInput("fb") &&
					$a_form->getInput("fb_date") == ilExAssignment::FEEDBACK_DATE_DEADLINE)
				{
					$a_form->getItemByPostVar("fb")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}				 
			}
			else
			{			
				// #18269
				if($a_form->getInput("peer"))
				{
					$time_deadline_max = max($time_deadline, $time_deadline_ext);					
					$peer_dl = $this->assignment // #18380
						? $this->assignment->getPeerReviewDeadline()
						: null;		
					if($peer_dl && $peer_dl < $time_deadline_max)
					{
						$a_form->getItemByPostVar($peer_dl < $time_deadline_ext
							? "deadline2" 
							: "deadline")
							->setAlert($lng->txt("exc_peer_deadline_mismatch"));
						$valid = false;		
					}					
				}
				
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
				// portfolio template
				if($a_form->getInput("template_id") && $a_form->getInput("template"))
				{
					$res['template_id'] = $a_form->getInput("template_id");
				}

				// text limitations
				if($a_form->getInput("limit_characters"))
				{
					$res['limit_characters'] = $a_form->getInput("limit_characters");
				}
				if($a_form->getInput("limit_characters") && $a_form->getInput("max_char_limit"))
				{
					$res['max_char_limit'] = $a_form->getInput("max_char_limit");
				}
				if($a_form->getInput("limit_characters") && $a_form->getInput("min_char_limit"))
				{
					$res['min_char_limit'] = $a_form->getInput("min_char_limit");

				}

				// peer
				if($a_form->getInput("peer") ||
					$protected_peer_review_groups)
				{
					$res["peer"] = true;					
				}
				
				// files
				if(is_array($_FILES["files"]))
				{					
					// #15994 - we are keeping the upload files array structure
					// see ilFSStorageExercise::uploadAssignmentFiles()
					$res["files"] = $_FILES["files"];								
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

		$a_ass->setPortfolioTemplateId($a_input['template_id']);

		$a_ass->setMinCharLimit($a_input['min_char_limit']);
		$a_ass->setMaxCharLimit($a_input['max_char_limit']);

		$a_ass->setPeerReview((bool)$a_input["peer"]);
		
		// peer review default values (on separate form)
		if($is_create)
		{
			$a_ass->setPeerReviewMin(2);
			$a_ass->setPeerReviewSimpleUnlock(false);
			$a_ass->setPeerReviewValid(ilExAssignment::PEER_REVIEW_VALID_NONE);
			$a_ass->setPeerReviewPersonalized(false);
			$a_ass->setPeerReviewFileUpload(false);
			$a_ass->setPeerReviewText(true);
			$a_ass->setPeerReviewRating(true);
		}	
		
		if($a_input["fb"])
		{
			$a_ass->setFeedbackCron($a_input["fb_cron"]); // #13380
			$a_ass->setFeedbackDate($a_input["fb_date"]);
		}
		
		// id needed for file handling
		if($is_create)
		{								
			$a_ass->save();		
			
			// #15994 - assignment files
			if(is_array($a_input["files"]))
			{
				$a_ass->uploadAssignmentFiles($a_input["files"]);
			}			
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
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
		// #16163 - ignore ass id from request
		$this->assignment = null;
		
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
			
			// because of sub-tabs we stay on settings screen
			$ilCtrl->setParameter($this, "ass_id", $ass->getId());
			$ilCtrl->redirect($this, "editAssignment");
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
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$tpl = $this->tpl;
		
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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
		$values = array();	
		$values["type"] = $this->assignment->getType();
		$values["title"] = $this->assignment->getTitle();
		$values["mandatory"] = $this->assignment->getMandatory();
		$values["instruction"] = $this->assignment->getInstruction();
		$values['template_id'] = $this->assignment->getPortfolioTemplateId();

		if($this->assignment->getPortfolioTemplateId())
		{
			$values["template"] = 1;
		}

		if($this->assignment->getMinCharLimit())
		{
			$values['limit_characters'] = 1;
			$values['min_char_limit'] = $this->assignment->getMinCharLimit();
		}
		if($this->assignment->getMaxCharLimit())
		{
			$values['limit_characters'] = 1;
			$values['max_char_limit'] = $this->assignment->getMaxCharLimit();
		}

		if ($this->assignment->getStartTime())
		{			
			$values["start_time"] = new ilDateTime($this->assignment->getStartTime(), IL_CAL_UNIX);		
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
		
		$a_form->setValuesByArray($values);
		
		// global feedback		
		if($this->assignment->getFeedbackFile())
		{													
			$a_form->getItemByPostVar("fb")->setChecked(true);			
			$a_form->getItemByPostVar("fb_file")->setValue(basename($this->assignment->getGlobalFeedbackFilePath()));	
			$a_form->getItemByPostVar("fb_file")->setRequired(false); // #15467
			$a_form->getItemByPostVar("fb_file")->setInfo(
				// #16400
				'<a href="'.$ilCtrl->getLinkTarget($this, "downloadGlobalFeedbackFile").'">'.
				$lng->txt("download").'</a>' 
			); 
		}
		$a_form->getItemByPostVar("fb_cron")->setChecked($this->assignment->hasFeedbackCron());			
		$a_form->getItemByPostVar("fb_date")->setValue($this->assignment->getFeedbackDate());	
						
		$this->handleDisabledFields($a_form, true);
	}
	
	protected function setDisabledFieldValues(ilPropertyFormGUI $a_form)
	{				
		// dates		
		if($this->assignment->getDeadline() > 0)
		{			
			$edit_date = new ilDateTime($this->assignment->getDeadline(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("deadline");
			$ed_item->setDate($edit_date);
			
			if($this->assignment->getExtendedDeadline() > 0)
			{			
				$edit_date = new ilDateTime($this->assignment->getExtendedDeadline(), IL_CAL_UNIX);
				$ed_item = $a_form->getItemByPostVar("deadline2");
				$ed_item->setDate($edit_date);
			}
		}
			
		if($this->assignment->getPeerReview())
		{
			$a_form->getItemByPostVar("peer")->setChecked($this->assignment->getPeerReview());			
		}
	}
	
	protected function handleDisabledFields(ilPropertyFormGUI $a_form, $a_force_set_values = false)
	{					
		// potentially disabled elements are initialized here to re-use this 
		// method after setValuesByPost() - see updateAssignmentObject()
		
		// team assignments do not support peer review			
		// with no active peer review there is nothing to protect		
		if($this->assignment->getType() != ilExAssignment::TYPE_UPLOAD_TEAM &&
			$this->assignment->getPeerReview())
		{		
			// #14450 
			include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
			$peer_review = new ilExPeerReview($this->assignment);	
			if($peer_review->hasPeerReviewGroups())
			{
				// deadline(s) are past and must not change					
				$a_form->getItemByPostVar("deadline")->setDisabled(true);				
				$a_form->getItemByPostVar("deadline2")->setDisabled(true);	

				$a_form->getItemByPostVar("peer")->setDisabled(true);			   
			}			 	
		}
		
		if($a_force_set_values ||
			($peer_review && $peer_review->hasPeerReviewGroups()))
		{
			$this->setDisabledFieldValues($a_form);		
		}
	}

	/**
	 * Update assignment
	 *
	 */
	public function updateAssignmentObject()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;
		
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
			$this->setAssignmentHeader();
			$ilTabs->activateTab("ass_settings");
			
			$form->setValuesByPost();
			$this->handleDisabledFields($form);
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	* Confirm assignments deletion
	*/
	function confirmAssignmentsDeletionObject()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;
		
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
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
				
		ilExAssignment::saveAssOrderOfExercise($this->exercise_id, $_POST["order"]);
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}
	
	/**
	 * Order by deadline
	 */
	function orderAssignmentsByDeadlineObject()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
				
		ilExAssignment::orderAssByDeadline($this->exercise_id);
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}

	/**
	 * Set assignment header
	 */
	function setAssignmentHeader()
	{
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$ilHelp = $this->help;
				
		$tpl->setTitle($this->assignment->getTitle());
		$tpl->setDescription("");
		
		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("exc");
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listAssignments"));

		$ilTabs->addTab("ass_settings",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "editAssignment"));

		if($this->assignment->getType() != ilExAssignment::TYPE_UPLOAD_TEAM &&
			$this->assignment->getPeerReview())
		{
			$ilTabs->addTab("peer_settings",
				$lng->txt("exc_peer_review"),
				$ilCtrl->getLinkTarget($this, "editPeerReview"));		
		}
		
		$ilTabs->addTab("ass_files",
			$lng->txt("exc_instruction_files"),
			$ilCtrl->getLinkTargetByClass(array("ilexassignmenteditorgui", "ilexassignmentfilesystemgui"), "listFiles"));
	}
	
	public function downloadGlobalFeedbackFileObject()
	{
		$ilCtrl = $this->ctrl;
		
		if(!$this->assignment || 
			!$this->assignment->getFeedbackFile())
		{
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		ilUtil::deliverFile($this->assignment->getGlobalFeedbackFilePath(), $this->assignment->getFeedbackFile());
	}
	
	
	//
	// PEER REVIEW
	//
	
	protected function initPeerReviewForm()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();			
		$form->setTitle($lng->txt("exc_peer_review"));		
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$peer_min = new ilNumberInputGUI($lng->txt("exc_peer_review_min_number"), "peer_min");
		$peer_min->setInfo($lng->txt("exc_peer_review_min_number_info")); // #16161
		$peer_min->setRequired(true);
		$peer_min->setSize(3);
		$peer_min->setValue(2);
		$form->addItem($peer_min);

		$peer_unlock = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_simple_unlock"), "peer_unlock");
		$peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_active"), 1));
		$peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_inactive"), 0));
		$peer_unlock->setRequired(true);		
		$peer_unlock->setValue(0);
		$form->addItem($peer_unlock);

		if($this->enable_peer_review_completion)
		{
			$peer_cmpl = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_completion"), "peer_valid");
			$option = new ilRadioOption($lng->txt("exc_peer_review_completion_none"), ilExAssignment::PEER_REVIEW_VALID_NONE);
			$option->setInfo($lng->txt("exc_peer_review_completion_none_info"));
			$peer_cmpl->addOption($option);
			$option = new ilRadioOption($lng->txt("exc_peer_review_completion_one"), ilExAssignment::PEER_REVIEW_VALID_ONE);
			$option->setInfo($lng->txt("exc_peer_review_completion_one_info"));
			$peer_cmpl->addOption($option);
			$option = new ilRadioOption($lng->txt("exc_peer_review_completion_all"), ilExAssignment::PEER_REVIEW_VALID_ALL);
			$option->setInfo($lng->txt("exc_peer_review_completion_all_info"));
			$peer_cmpl->addOption($option);
			$peer_cmpl->setRequired(true);		
			$peer_cmpl->setValue(ilExAssignment::PEER_REVIEW_VALID_NONE);
			$form->addItem($peer_cmpl);
		}

		$peer_dl = new ilDateTimeInputGUI($lng->txt("exc_peer_review_deadline"), "peer_dl");
		$peer_dl->setInfo($lng->txt("exc_peer_review_deadline_info"));		
		$peer_dl->setShowTime(true);
		$form->addItem($peer_dl);

		$peer_prsl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_personal"), "peer_prsl");				
		$peer_prsl->setInfo($lng->txt("exc_peer_review_personal_info"));
		$form->addItem($peer_prsl);
		
		
		// criteria
		
		$cats = new ilRadioGroupInputGUI($lng->txt("exc_criteria_catalogues"), "crit_cat");
		$form->addItem($cats);
		
		// default (no catalogue)
		
		$def = new ilRadioOption($lng->txt("exc_criteria_catalogue_default"), -1);
		$cats->addOption($def);
				
		$peer_text = new ilCheckboxInputGUI($lng->txt("exc_peer_review_text"), "peer_text");
		$def->addSubItem($peer_text);
		
		$peer_char = new ilNumberInputGUI($lng->txt("exc_peer_review_min_chars"), "peer_char");
		$peer_char->setInfo($lng->txt("exc_peer_review_min_chars_info"));
		$peer_char->setSize(3);
		$peer_text->addSubItem($peer_char);
		
		$peer_rating = new ilCheckboxInputGUI($lng->txt("exc_peer_review_rating"), "peer_rating");						
		$def->addSubItem($peer_rating);
					
		$peer_file = new ilCheckboxInputGUI($lng->txt("exc_peer_review_file"), "peer_file");				
		$peer_file->setInfo($lng->txt("exc_peer_review_file_info"));
		$def->addSubItem($peer_file);
						
		// catalogues
		
		include_once "Modules/Exercise/classes/class.ilExcCriteriaCatalogue.php";
		$cat_objs = ilExcCriteriaCatalogue::getInstancesByParentId($this->exercise_id);		
		if(sizeof($cat_objs))
		{
			include_once "Modules/Exercise/classes/class.ilExcCriteria.php";
			foreach($cat_objs as $cat_obj)
			{
				$crits = ilExcCriteria::getInstancesByParentId($cat_obj->getId());
				
				// only non-empty catalogues
				if(sizeof($crits))
				{			
					$titles = array();
					foreach($crits as $crit)
					{
						$titles[] = $crit->getTitle();
					}
					$opt = new ilRadioOption($cat_obj->getTitle(), $cat_obj->getId());		
					$opt->setInfo(implode(", ", $titles));
					$cats->addOption($opt);
				}
			}			
		}
		else
		{
			// see ilExcCriteriaCatalogueGUI::view()
			$url = $ilCtrl->getLinkTargetByClass("ilexccriteriacataloguegui", "");
			$def->setInfo('<a href="'.$url.'">[+] '.
				$lng->txt("exc_add_criteria_catalogue").
				'</a>');
		}
		
		
		$form->addCommandButton("updatePeerReview", $lng->txt("save"));
		$form->addCommandButton("editAssignment", $lng->txt("cancel"));
		
		return $form;
	}
			
	public function editPeerReviewObject(ilPropertyFormGUI $a_form = null)
	{
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$tpl = $this->tpl;
		
		$this->setAssignmentHeader();
		$ilTabs->activateTab("peer_settings");
		
		if($a_form === null)
		{
			$a_form = $this->initPeerReviewForm();		
			$this->getPeerReviewValues($a_form);
		}
		$tpl->setContent($a_form->getHTML());		
	}
	
	protected function getPeerReviewValues($a_form)
	{	
		$values = array();
		
		if($this->assignment->getPeerReviewDeadline() > 0)
		{
			$values["peer_dl"] = new ilDateTime($this->assignment->getPeerReviewDeadline(), IL_CAL_UNIX);		
		}				

		$a_form->setValuesByArray($values);
		
		$this->handleDisabledPeerFields($a_form, true);
	}
	
	protected function setDisabledPeerReviewFieldValues(ilPropertyFormGUI $a_form)
	{
		$a_form->getItemByPostVar("peer_min")->setValue($this->assignment->getPeerReviewMin());
		$a_form->getItemByPostVar("peer_prsl")->setChecked($this->assignment->hasPeerReviewPersonalized());
		$a_form->getItemByPostVar("peer_unlock")->setValue((int)$this->assignment->getPeerReviewSimpleUnlock());

		if($this->enable_peer_review_completion)
		{
			$a_form->getItemByPostVar("peer_valid")->setValue($this->assignment->getPeerReviewValid());
		}

		$cat = $this->assignment->getPeerReviewCriteriaCatalogue();
		if($cat < 1)
		{		
			$cat = -1;						
			
			// default / no catalogue
			$a_form->getItemByPostVar("peer_text")->setChecked($this->assignment->hasPeerReviewText());				
			$a_form->getItemByPostVar("peer_rating")->setChecked($this->assignment->hasPeerReviewRating());				
			$a_form->getItemByPostVar("peer_file")->setChecked($this->assignment->hasPeerReviewFileUpload());		
			if ($this->assignment->getPeerReviewChars() > 0)
			{				
				$a_form->getItemByPostVar("peer_char")->setValue($this->assignment->getPeerReviewChars());		
			}	
		}
		$a_form->getItemByPostVar("crit_cat")->setValue($cat);
	}
	
	protected function handleDisabledPeerFields(ilPropertyFormGUI $a_form, $a_force_set_values = false)
	{																	
		// #14450 
		include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
		$peer_review = new ilExPeerReview($this->assignment);	
		if($peer_review->hasPeerReviewGroups())
		{			
			// JourFixe, 2015-05-11 - editable again
			// $a_form->getItemByPostVar("peer_dl")->setDisabled(true);
			
			$a_form->getItemByPostVar("peer_min")->setDisabled(true);							
			$a_form->getItemByPostVar("peer_prsl")->setDisabled(true);												
			$a_form->getItemByPostVar("peer_unlock")->setDisabled(true);
			
			if($this->enable_peer_review_completion)
			{
				$a_form->getItemByPostVar("peer_valid")->setDisabled(true);									
			}		
			
			$a_form->getItemByPostVar("crit_cat")->setDisabled(true);									
			$a_form->getItemByPostVar("peer_text")->setDisabled(true);									
			$a_form->getItemByPostVar("peer_char")->setDisabled(true);				
			$a_form->getItemByPostVar("peer_rating")->setDisabled(true);
			$a_form->getItemByPostVar("peer_file")->setDisabled(true);	
			
			// required number input is a problem
			$min = new ilHiddenInputGUI("peer_min");
			$min->setValue($this->assignment->getPeerReviewMin());
			$a_form->addItem($min);
		}			 
		
		if($a_force_set_values ||
			$peer_review->hasPeerReviewGroups())
		{
			$this->setDisabledPeerReviewFieldValues($a_form);		
		}
	}
	
	protected function processPeerReviewForm(ilPropertyFormGUI $a_form)
	{
		$lng = $this->lng;
		
		$protected_peer_review_groups = false;		
		include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
		$peer_review = new ilExPeerReview($this->assignment);	
		if($peer_review->hasPeerReviewGroups())
		{
			$protected_peer_review_groups = true;
		}
		
		$valid = $a_form->checkInput();					
		if($valid)
		{									
			// dates
			$time_deadline = $this->assignment->getDeadline();		
			$time_deadline_ext = $this->assignment->getExtendedDeadline();	
			$time_deadline_max = max($time_deadline, $time_deadline_ext);
			
			$date = $a_form->getItemByPostVar("peer_dl")->getDate();
			$time_peer = $date
				? $date->get(IL_CAL_UNIX)
				: null;		
			
			// peer < any deadline?							
			if($time_peer && $time_deadline_max && $time_peer < $time_deadline_max)
			{					
				$a_form->getItemByPostVar("peer_dl")
					->setAlert($lng->txt("exc_peer_deadline_mismatch"));
				$valid = false;				
			}		
			
			if(!$protected_peer_review_groups)
			{	
				if($a_form->getInput("crit_cat") < 0 &&
					!$a_form->getInput("peer_text") &&
					!$a_form->getInput("peer_rating") &&
					!$a_form->getInput("peer_file"))
				{
					$a_form->getItemByPostVar("peer_file")
						->setAlert($lng->txt("select_one"));
					$valid = false;		
				}
			}
			
			if($valid)
			{	
				$res = array();
				$res["peer_dl"] = $time_peer;

				if($protected_peer_review_groups)
				{
					$res["peer_min"] = $this->assignment->getPeerReviewMin();
					$res["peer_unlock"] = $this->assignment->getPeerReviewSimpleUnlock();
					$res["peer_prsl"] = $this->assignment->hasPeerReviewPersonalized();
					$res["peer_valid"] = $this->assignment->getPeerReviewValid();		
					
					$res["peer_text"] = $this->assignment->hasPeerReviewText();
					$res["peer_rating"] = $this->assignment->hasPeerReviewRating();
					$res["peer_file"] = $this->assignment->hasPeerReviewFileUpload();
					$res["peer_char"] = $this->assignment->getPeerReviewChars();
					$res["crit_cat"] = $this->assignment->getPeerReviewCriteriaCatalogue();										
					
					$res["peer_valid"] = $this->enable_peer_review_completion
							? $res["peer_valid"]
							: null;		
				}
				else
				{										
					$res["peer_min"] = $a_form->getInput("peer_min");					
					$res["peer_unlock"] = $a_form->getInput("peer_unlock");						
					$res["peer_prsl"] = $a_form->getInput("peer_prsl");
					$res["peer_valid"] = $a_form->getInput("peer_valid");		
					
					$res["peer_text"] = $a_form->getInput("peer_text");
					$res["peer_rating"] = $a_form->getInput("peer_rating");					
					$res["peer_file"] = $a_form->getInput("peer_file");
					$res["peer_char"] = $a_form->getInput("peer_char");
					$res["crit_cat"] = $a_form->getInput("crit_cat");	
				}

				return $res;
			}
			else
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
		}
	}
	
	protected function importPeerReviewFormToAssignment(ilExAssignment $a_ass, array $a_input)
	{					
		$a_ass->setPeerReviewMin($a_input["peer_min"]);
		$a_ass->setPeerReviewDeadline($a_input["peer_dl"]);			
		$a_ass->setPeerReviewSimpleUnlock($a_input["peer_unlock"]);		
		$a_ass->setPeerReviewPersonalized($a_input["peer_prsl"]);	
		
		// #18964
		$a_ass->setPeerReviewValid($a_input["peer_valid"]
			? $a_input["peer_valid"]
			: ilExAssignment::PEER_REVIEW_VALID_NONE);
		
		$a_ass->setPeerReviewFileUpload($a_input["peer_file"]);
		$a_ass->setPeerReviewChars($a_input["peer_char"]);
		$a_ass->setPeerReviewText($a_input["peer_text"]);
		$a_ass->setPeerReviewRating($a_input["peer_rating"]);
		$a_ass->setPeerReviewCriteriaCatalogue($a_input["crit_cat"] > 0 
			? $a_input["crit_cat"]
			: null);
	
		$a_ass->update();				
	}
	
	protected function updatePeerReviewObject()
	{				
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;
		
		$form = $this->initPeerReviewForm();
		$input = $this->processPeerReviewForm($form);
		if(is_array($input))
		{										
			$this->importPeerReviewFormToAssignment($this->assignment, $input);
						
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editPeerReview");
		}
		else
		{
			$this->setAssignmentHeader();
			$ilTabs->activateTab("peer_settings");
			
			$form->setValuesByPost();
			$this->handleDisabledPeerFields($form);
			$tpl->setContent($form->getHtml());
		}		
	}
	
	
	//
	// TEAM
	// 
	
	public function adoptTeamAssignmentsFormObject()
	{
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$tpl = $this->tpl;
		
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
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
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
