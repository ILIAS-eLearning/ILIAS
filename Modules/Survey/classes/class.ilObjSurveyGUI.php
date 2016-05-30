<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjSurveyGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
*
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyEvaluationGUI, ilSurveyExecutionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilMDEditorGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveySkillDeterminationGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilCommonActionDispatcherGUI, ilSurveySkillGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyEditorGUI, ilSurveyConstraintsGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyParticipantsGUI
*
* @ingroup ModulesSurvey
*/
class ilObjSurveyGUI extends ilObjectGUI
{		
	public function __construct()
	{
		global $lng, $ilCtrl;

		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id");
	
		parent::__construct("", (int)$_GET["ref_id"], true, false);
	}
	
	public function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory, $ilErr, $ilTabs;

		$this->external_rater_360 = false;
		if(!$this->creation_mode &&
			$this->object->get360Mode() &&
			$_SESSION["anonymous_id"][$this->object->getId()] && 
			ilObjSurvey::validateExternalRaterCode($this->object->getRefId(), 
				$_SESSION["anonymous_id"][$this->object->getId()]))
		{
			$this->external_rater_360 = true;
		}
		
		if(!$this->external_rater_360)
		{
			if (!$ilAccess->checkAccess("read", "", $this->ref_id) && 
				!$ilAccess->checkAccess("visible", "", $this->ref_id))
			{
				$ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
			}

			// add entry to navigation history
			if (!$this->getCreationMode() &&
				$ilAccess->checkAccess("read", "", $this->ref_id))
			{
				$this->ctrl->setParameterByClass("ilobjsurveygui", "ref_id", $this->ref_id);
				$link = $this->ctrl->getLinkTargetByClass("ilobjsurveygui", "");
				$ilNavigationHistory->addItem($this->ref_id, $link, "svy");
			}
		}

		$cmd = $this->ctrl->getCmd("properties");						
		
		// workaround for bug #6288, needs better solution
		if ($cmd == "saveTags")
		{
			$this->ctrl->setCmdClass("ilinfoscreengui");
		}
		
		// deep link from repository - "redirect" to page view
		if(!$this->ctrl->getCmdClass() && $cmd == "questionsrepo")
		{
			$_REQUEST["pgov"] = 1;
			$this->ctrl->setCmd("questions");
			$this->ctrl->setCmdClass("ilsurveyeditorgui");
		}		
		
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "survey.css", "Modules/Survey"), "screen");
		$this->prepareOutput();

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->addHeaderAction();
				$this->infoScreen();	// forwards command
				break;
			
			case 'ilmdeditorgui':
				$this->handleWriteAccess();			
				$ilTabs->activateTab("meta_data");
				$this->addHeaderAction();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
			
			case "ilsurveyevaluationgui":
				$ilTabs->activateTab("svy_results");
				$this->addHeaderAction();
				include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
				$eval_gui = new ilSurveyEvaluationGUI($this->object);
				$this->ctrl->forwardCommand($eval_gui);
				break;

			case "ilsurveyexecutiongui":
				$ilTabs->clearTargets();
				include_once("./Modules/Survey/classes/class.ilSurveyExecutionGUI.php");
				$exec_gui = new ilSurveyExecutionGUI($this->object);
				$this->ctrl->forwardCommand($exec_gui);
				break;
				
			case 'ilpermissiongui':
				$ilTabs->activateTab("perm_settings");
				$this->addHeaderAction();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('svy');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
				
			// 360, skill service
			case 'ilsurveyskillgui':
				$ilTabs->activateTab("survey_competences");
				include_once("./Modules/Survey/classes/class.ilSurveySkillGUI.php");
				$gui = new ilSurveySkillGUI($this->object);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilsurveyskilldeterminationgui':
				$ilTabs->activateTab("maintenance");
				include_once("./Modules/Survey/classes/class.ilSurveySkillDeterminationGUI.php");
				$gui = new ilSurveySkillDeterminationGUI($this->object);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilsurveyeditorgui':
				$this->handleWriteAccess();					
				$ilTabs->activateTab("survey_questions");
				include_once("./Modules/Survey/classes/class.ilSurveyEditorGUI.php");
				$gui = new ilSurveyEditorGUI($this);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilsurveyconstraintsgui':
				$this->handleWriteAccess();					
				$ilTabs->activateTab("constraints");
				include_once("./Modules/Survey/classes/class.ilSurveyConstraintsGUI.php");
				$gui = new ilSurveyConstraintsGUI($this);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilsurveyparticipantsgui':		
				if(!$this->object->get360Mode())
				{
					$ilTabs->activateTab("maintenance");				
				}
				else
				{
					$ilTabs->activateTab("survey_360_appraisees");
				}
				include_once("./Modules/Survey/classes/class.ilSurveyParticipantsGUI.php");
				$gui = new ilSurveyParticipantsGUI($this);
				$this->ctrl->forwardCommand($gui);
				break;

			default:
				$this->addHeaderAction();
				$cmd.= "Object";
				$this->$cmd();
				break;
		}

		if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}
				
	/**
	* Redirects the evaluation object call to the ilSurveyEvaluationGUI class
	*
	* Coming from ListGUI...
	*
	* @access	private
	*/
	public function evaluationObject()
	{
		include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
		$eval_gui = new ilSurveyEvaluationGUI($this->object);
		$this->ctrl->setCmdClass(get_class($eval_gui));
		$this->ctrl->redirect($eval_gui, "evaluation");
	}		
	
	protected function addDidacticTemplateOptions(array &$a_options)
	{
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$templates = ilSettingsTemplate::getAllSettingsTemplates("svy");
		if($templates)
		{
			foreach($templates as $item)
			{
				$a_options["svytpl_".$item["id"]] = array($item["title"],
					nl2br(trim($item["description"])));
			}
		}
		
		// JF, 2013-06-10
		$a_options["svy360_1"] = array($this->lng->txt("survey_360_mode"),
			$this->lng->txt("survey_360_mode_info"));
	}

	/**
	* save object
	* @access	public
	*/
	function afterSave(ilObject $a_new_object)
	{	
		// #16446
		$a_new_object->loadFromDb();
		
		$tpl = $this->getDidacticTemplateVar("svytpl");
		if($tpl)
		{
			$a_new_object->applySettingsTemplate($tpl);
		}
		
		$a_new_object->set360Mode((bool)$this->getDidacticTemplateVar("svy360"));
		if($a_new_object->get360Mode())
		{
			$a_new_object->setAnonymize(ilObjSurvey::ANONYMIZE_CODE_ALL);
			$a_new_object->setEvaluationAccess(ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS);
		}
		$a_new_object->saveToDB();

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=".
			$a_new_object->getRefId()."&cmd=properties");
	}		
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser, $ilHelp;
		
		if($this->object instanceof ilObjSurveyQuestionPool)
		{
			return true;
		}
		
		$ilHelp->setScreenIdComponent("svy");

		$hidden_tabs = array();
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);
			$hidden_tabs = $template->getHiddenTabs();
		}
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{		
			$tabs_gui->addTab("survey_questions",
				$this->lng->txt("survey_questions"),
				$this->ctrl->getLinkTargetByClass(array("ilsurveyeditorgui", "ilsurveypagegui"), "renderPage"));
		}
		
		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$tabs_gui->addTab("info_short",
				$this->lng->txt("info_short"),
				$this->ctrl->getLinkTarget($this,'infoScreen'));
		}
							
		// properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{			
			$tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this,'properties'));
		}
		else if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			if($this->object->get360Mode() && 
				$this->object->get360SelfRaters() &&
				$this->object->isAppraisee($ilUser->getId()) &&
				!$this->object->isAppraiseeClosed($ilUser->getId()))
			{
				$tabs_gui->addTab("survey_360_edit_raters",
					$this->lng->txt("survey_360_edit_raters"),
					$this->ctrl->getLinkTargetByClass('ilsurveyparticipantsgui','editRaters'));	
				
				// :TODO: mail to raters
			}
		}

		// questions
		if ($ilAccess->checkAccess("write", "", $this->ref_id) &&
			!in_array("constraints", $hidden_tabs) &&
			!$this->object->get360Mode())
		{
			// constraints
			$tabs_gui->addTab("constraints",
				$this->lng->txt("constraints"),
				 $this->ctrl->getLinkTargetByClass("ilsurveyconstraintsgui", "constraints"));
		}

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// 360° 
			if($this->object->get360Mode())
			{
				// 360 mode + competence service
				include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
				$skmg_set = new ilSkillManagementSettings();
				if ($this->object->get360SkillService() && $skmg_set->isActivated())
				{
					$tabs_gui->addTab("survey_competences",
						$this->lng->txt("survey_competences"),
						$this->ctrl->getLinkTargetByClass("ilsurveyskillgui", "listQuestionAssignment"));
				}
				
				$tabs_gui->addTab("survey_360_appraisees",
					$this->lng->txt("survey_360_appraisees"),
					$this->ctrl->getLinkTargetByClass('ilsurveyparticipantsgui', 'listAppraisees'));						
			}
			else
			{
				// maintenance
				$tabs_gui->addTab("maintenance",
					$this->lng->txt("maintenance"),
					$this->ctrl->getLinkTargetByClass('ilsurveyparticipantsgui', 'maintenance'));
			}
		}
			
		include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if ($ilAccess->checkAccess("write", "", $this->ref_id) || 
			ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
		{
			// evaluation
			$tabs_gui->addTab("svy_results",
				$this->lng->txt("svy_results"),
				$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", ""));
		}

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			if(!in_array("meta_data", $hidden_tabs))
			{
				// meta data
				$tabs_gui->addTab("meta_data",
					$this->lng->txt("meta_data"),
					$this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'));
			}

			if(!in_array("export", $hidden_tabs))
			{
				// export
				$tabs_gui->addTab("export",
					$this->lng->txt("export"),
					$this->ctrl->getLinkTarget($this,'export'));
			}
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			// permissions
			$tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
		}
	}
		
	/**
	* Checks for write access and returns to the parent object
	*
	* Checks for write access and returns to the parent object
	*
	* @access public
	*/
	public function handleWriteAccess()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), TRUE);
			$this->ctrl->redirect($this, "infoScreen");
		}
	}
	
	
	//
	// SETTINGS
	//
			
	/**
	* Save the survey properties
	*
	* Save the survey properties
	*
	* @access private
	*/
	function savePropertiesObject()
	{		
		global $rbacsystem;
		
		$form = $this->initPropertiesForm();
		if ($form->checkInput())
		{					
			$valid = true;
						
			if(!$this->object->get360Mode())
			{
				if($form->getInput("tut"))
				{				
					// check if given "tutors" have write permission
					$tut_ids =array();
					$tut_logins = $form->getInput("tut_ids");
					foreach($tut_logins as $tut_login)
					{
						$tut_id = ilObjUser::_lookupId($tut_login);
						if($tut_id && $rbacsystem->checkAccessOfUser($tut_id, "write", $this->object->getRefId()))
						{					
							$tut_ids[] = $tut_id;
						}				
					}
					if(!$tut_ids)
					{
						$tut_ids = $form->getItemByPostVar("tut_ids");
						$tut_ids->setAlert($this->lng->txt("survey_notification_tutor_recipients_invalid"));					
						$valid = false;
					}													
				}			
			}
			
			if($valid)
			{			
				if(!$this->object->get360Mode())
				{
					if($form->getInput("rmd"))
					{
						$rmd_start = $form->getInput("rmd_start");
						$rmd_start = $rmd_start["date"];
						$rmd_end = null;
						if($form->getInput("rmd_end_tgl"))
						{
							$rmd_end = $form->getInput("rmd_end");
							$rmd_end = $rmd_end["date"];
							if($rmd_start > $rmd_end)
							{
								$tmp = $rmd_start;
								$rmd_start = $rmd_end;
								$rmd_end = $tmp;
							}
							$rmd_end = new ilDate($rmd_end, IL_CAL_DATE);
						}
						$rmd_start = new ilDate($rmd_start, IL_CAL_DATE);

						$this->object->setReminderStatus(true);
						$this->object->setReminderStart($rmd_start);
						$this->object->setReminderEnd($rmd_end);
						$this->object->setReminderFrequency($form->getInput("rmd_freq"));
						$this->object->setReminderTarget($form->getInput("rmd_grp"));
					}		
					else
					{
						$this->object->setReminderStatus(false);
					}

					if($form->getInput("tut"))
					{
						$this->object->setTutorNotificationStatus(true);		
						$this->object->setTutorNotificationRecipients($tut_ids); // see above
						$this->object->setTutorNotificationTarget($form->getInput("tut_grp"));
					}		
					else
					{
						$this->object->setTutorNotificationStatus(false);
					}
				}
			
				// #10055
				if ($_POST['online'] && count($this->object->questions) == 0)
				{
					$_POST['online'] = null;
					ilUtil::sendFailure($this->lng->txt("cannot_switch_to_online_no_questions"), true);			
				}

				$template_settings = null;
				$template = $this->object->getTemplate();
				if($template)
				{
					include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
					$template = new ilSettingsTemplate($template);
					$template_settings = $template->getSettings();
				}

				include_once 'Services/MetaData/classes/class.ilMD.php';
				$md_obj =& new ilMD($this->object->getId(), 0, "svy");
				$md_section = $md_obj->getGeneral();

				// title
				$md_section->setTitle(ilUtil::stripSlashes($_POST['title']));
				$md_section->update();

				// Description
				$md_desc_ids = $md_section->getDescriptionIds();
				if($md_desc_ids)
				{
					$md_desc = $md_section->getDescription(array_pop($md_desc_ids));
					$md_desc->setDescription(ilUtil::stripSlashes($_POST['description']));
					$md_desc->update();
				}

				// both are saved in object, too
				$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
				$this->object->setDescription(ilUtil::stripSlashes($_POST['description']));
				$this->object->update();

				$this->object->setStatus($_POST['online']);

				// activation
				if($_POST["access_type"])
				{	
					$this->object->setActivationLimited(true);								    			
					$this->object->setActivationVisibility($_POST["access_visiblity"]);	
					
					$period = $form->getItemByPostVar("access_period");										
					$this->object->setActivationStartDate($period->getStart()->get(IL_CAL_UNIX));
					$this->object->setActivationEndDate($period->getEnd()->get(IL_CAL_UNIX));							
				}
				else
				{
					$this->object->setActivationLimited(false);
				}
				
				
				if(!$template_settings["enabled_start_date"]["hide"])
				{
					if ($_POST["enabled_start_date"])
					{
						$this->object->setStartDateAndTime($_POST["start_date"]['date'], $_POST["start_date"]['time']);
					}
					else
					{
						$this->object->setStartDate(null);
					}
				}

				if(!$template_settings["enabled_end_date"]["hide"])
				{
					if ($_POST["enabled_end_date"])
					{
						$this->object->setEndDateAndTime($_POST["end_date"]['date'], $_POST["end_date"]['time']);
					}
					else
					{
						$this->object->setEndDate(null);
					}
				}

				
				include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
				$introduction = $_POST["introduction"];
				$this->object->setIntroduction($introduction);
				$outro = $_POST["outro"];
				$this->object->setOutro($outro);

				if(!$template_settings["show_question_titles"]["hide"])
				{
					$this->object->setShowQuestionTitles($_POST["show_question_titles"]);
				}

				if(!$template_settings["use_pool"]["hide"])
				{
					$this->object->setPoolUsage($_POST["use_pool"]);
				}

				$this->object->setMailNotification($_POST['mailnotification']);
				$this->object->setMailAddresses($_POST['mailaddresses']);
				$this->object->setMailParticipantData($_POST['mailparticipantdata']);

				// 360°
				if($this->object->get360Mode())
				{
					$this->object->set360SelfEvaluation((bool)$_POST["self_eval"]);
					$this->object->set360SelfAppraisee((bool)$_POST["self_appr"]);
					$this->object->set360SelfRaters((bool)$_POST["self_rate"]);
					$this->object->set360Results((int)$_POST["ts_res"]);;
					$this->object->set360SkillService((int)$_POST["skill_service"]);
				}
				else
				{				
					$this->object->setEvaluationAccess($_POST["evaluation_access"]);

					$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
					if (!$hasDatasets)
					{
						$anon_map = array('personalized' => ilObjSurvey::ANONYMIZE_OFF,
							'anonymize_with_code' => ilObjSurvey::ANONYMIZE_ON,
							'anonymize_without_code' => ilObjSurvey::ANONYMIZE_FREEACCESS);
						if(array_key_exists($_POST["anonymization_options"], $anon_map))
						{
							$this->object->setAnonymize($anon_map[$_POST["anonymization_options"]]);
							if (strcmp($_POST['anonymization_options'], 'anonymize_with_code') == 0) $anonymize = ilObjSurvey::ANONYMIZE_ON;
							if (strcmp($_POST['anonymization_options'], 'anonymize_with_code_all') == 0) $anonymize = ilObjSurvey::ANONYMIZE_CODE_ALL;
						}
					}
				}

				$this->object->saveToDb();

				if (strcmp($_SESSION["info"], "") != 0)
				{
					ilUtil::sendSuccess($_SESSION["info"] . "<br />" . $this->lng->txt("settings_saved"), true);
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				}
				$this->ctrl->redirect($this, "properties");
			}
		}
		
		$form->setValuesByPost();
		$this->propertiesObject($form);
	}
	
	/**
	 * Init survey settings form
	 * 
	 * @return ilPropertyFormGUI
	 */
	function initPropertiesForm()
	{		
		$template_settings = $hide_rte_switch = null;
		$template = $this->object->getTemplate();
		if($template)
		{			
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);

			$template_settings = $template->getSettings();
			$hide_rte_switch = $template_settings["rte_switch"]["hide"];
		}
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("survey_properties");

		// general properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("settings"));
		$form->addItem($header);
		
		// title & description (meta data)
		
		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj = new ilMD($this->object->getId(), 0, "svy");
		$md_section = $md_obj->getGeneral();

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setValue($md_section->getTitle());
		$form->addItem($title);

		$ids = $md_section->getDescriptionIds();
		if($ids)
		{
			$desc_obj = $md_section->getDescription(array_pop($ids));

			$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
			$desc->setCols(50);
			$desc->setRows(4);
			$desc->setValue($desc_obj->getDescription());
			$form->addItem($desc);
		}
				
		// anonymization
		if(!$this->object->get360Mode())
		{
			$anonymization_options = new ilRadioGroupInputGUI($this->lng->txt("survey_auth_mode"), "anonymization_options");
			$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
			if ($hasDatasets)
			{
				$anonymization_options->setDisabled(true);
			}
			$anonymization_options->addOption(new ilCheckboxOption($this->lng->txt("anonymize_personalized"),
					'personalized', ''));
			$anonymization_options->addOption(new ilCheckboxOption(
					$this->lng->txt("anonymize_without_code"), 'anonymize_without_code', ''));
			$anonymization_options->addOption(new ilCheckboxOption(
					$this->lng->txt("anonymize_with_code"), 'anonymize_with_code', ''));
			if(!$this->object->getAnonymize())
			{
				$anonymization_options->setValue('personalized');
			}
			else
			{
				$anonymization_options->setValue(($this->object->isAccessibleWithoutCode()) ?
						'anonymize_without_code' : 'anonymize_with_code');
			}
			$anonymization_options->setInfo($this->lng->txt("anonymize_survey_description"));
			$form->addItem($anonymization_options);
		}
		// 360° 
		else
		{						
			$self_eval = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_evaluation"), "self_eval");
			$self_eval->setChecked($this->object->get360SelfEvaluation());
			$form->addItem($self_eval);

			$self_rate = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_raters"), "self_rate");
			$self_rate->setChecked($this->object->get360SelfRaters());
			$form->addItem($self_rate);

			$self_appr = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_appraisee"), "self_appr");
			$self_appr->setChecked($this->object->get360SelfAppraisee());
			$form->addItem($self_appr);
		}
				
		// pool usage
		$pool_usage = new ilRadioGroupInputGUI($this->lng->txt("survey_question_pool_usage"), "use_pool");		
		$opt = new ilRadioOption($this->lng->txt("survey_question_pool_usage_active"), 1);
		$opt->setInfo($this->lng->txt("survey_question_pool_usage_active_info"));
		$pool_usage->addOption($opt);
		$opt = new ilRadioOption($this->lng->txt("survey_question_pool_usage_inactive"), 0);
		$opt->setInfo($this->lng->txt("survey_question_pool_usage_inactive_info"));
		$pool_usage->addOption($opt);
		$pool_usage->setValue($this->object->getPoolUsage());
		$form->addItem($pool_usage);
		
		
		// activation
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$form->addItem($section);
		
		// additional info only with multiple references
		$act_obj_info = $act_ref_info = "";
		if(sizeof(ilObject::_getAllReferences($this->object->getId())) > 1)
		{
			$act_obj_info = ' '.$this->lng->txt('rep_activation_online_object_info');
			$act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
		}
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'online');		
		$online->setInfo($this->lng->txt('svy_activation_online_info').$act_obj_info);
		$online->setChecked($this->object->isOnline());
		$form->addItem($online);				
		
		$act_type = new ilCheckboxInputGUI($this->lng->txt('rep_visibility_until'),'access_type');
		// $act_type->setInfo($this->lng->txt('svy_availability_until_info'));
		$act_type->setChecked($this->object->isActivationLimited());		
		
			$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
			include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
			$dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "access_period");
			$dur->setShowTime(true);						
			$date = $this->object->getActivationStartDate();				
			$dur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setStartText($this->lng->txt('rep_activation_limited_start'));				
			$date = $this->object->getActivationEndDate();
			$dur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setEndText($this->lng->txt('rep_activation_limited_end'));				
			$act_type->addSubItem($dur);

			$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
			$visible->setInfo($this->lng->txt('svy_activation_limited_visibility_info'));
			$visible->setChecked($this->object->getActivationVisibility());
			$act_type->addSubItem($visible);
			
		$form->addItem($act_type);									
				
		
		// enable start date
		$start = $this->object->getStartDate();
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("start_date"), "enabled_start_date");
		$enablestartingtime->setValue(1);
		// $enablestartingtime->setOptionTitle($this->lng->txt("enabled"));
		$enablestartingtime->setChecked($start);
		// start date
		$startingtime = new ilDateTimeInputGUI('', 'start_date');
		$startingtime->setShowDate(true);
		$startingtime->setShowTime(true);				
		if ($start)
		{
			$startingtime->setDate(new ilDate($start, IL_CAL_TIMESTAMP));
		}
		$enablestartingtime->addSubItem($startingtime);
		$form->addItem($enablestartingtime);

		// enable end date		
		$end = $this->object->getEndDate();
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("end_date"), "enabled_end_date");
		$enableendingtime->setValue(1);
		// $enableendingtime->setOptionTitle($this->lng->txt("enabled"));
		$enableendingtime->setChecked($end);
		// end date
		$endingtime = new ilDateTimeInputGUI('', 'end_date');
		$endingtime->setShowDate(true);
		$endingtime->setShowTime(true);		
		if ($end)
		{
			$endingtime->setDate(new ilDate($end, IL_CAL_TIMESTAMP));
		}
		$enableendingtime->addSubItem($endingtime);
		$form->addItem($enableendingtime);
		
		
		// presentation properties
		$info = new ilFormSectionHeaderGUI();
		$info->setTitle($this->lng->txt("svy_presentation_properties"));
		$form->addItem($info);
		
		// show question titles
		$show_question_titles = new ilCheckboxInputGUI($this->lng->txt("svy_show_questiontitles"), "show_question_titles");
		$show_question_titles->setValue(1);
		$show_question_titles->setChecked($this->object->getShowQuestionTitles());
		$form->addItem($show_question_titles);
		
		// introduction
		$intro = new ilTextAreaInputGUI($this->lng->txt("introduction"), "introduction");
		$intro->setValue($this->object->prepareTextareaOutput($this->object->getIntroduction()));
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->setInfo($this->lng->txt("survey_introduction_info"));
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$intro->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
		$intro->addPlugin("latex");
		$intro->addButton("latex");
	    $intro->addButton("pastelatex");
		$intro->setRTESupport($this->object->getId(), "svy", "survey", null, $hide_rte_switch);
		$form->addItem($intro);

		// final statement
		$finalstatement = new ilTextAreaInputGUI($this->lng->txt("outro"), "outro");
		$finalstatement->setValue($this->object->prepareTextareaOutput($this->object->getOutro()));
		$finalstatement->setRows(10);
		$finalstatement->setCols(80);
		$finalstatement->setUseRte(TRUE);
		$finalstatement->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
		$finalstatement->addPlugin("latex");
		$finalstatement->addButton("latex");
		$finalstatement->addButton("pastelatex");
		$finalstatement->setRTESupport($this->object->getId(), "svy", "survey", null, $hide_rte_switch);
		$form->addItem($finalstatement);

		
		// results properties
		$results = new ilFormSectionHeaderGUI();
		$results->setTitle($this->lng->txt("results"));
		$form->addItem($results);

		// evaluation access
		if(!$this->object->get360Mode())
		{
			$evaluation_access = new ilRadioGroupInputGUI($this->lng->txt('evaluation_access'), "evaluation_access");
			$evaluation_access->setInfo($this->lng->txt('evaluation_access_description'));
			$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_off"), ilObjSurvey::EVALUATION_ACCESS_OFF, ''));
			$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_all"), ilObjSurvey::EVALUATION_ACCESS_ALL, ''));
			$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_participants"), ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS, ''));
			$evaluation_access->setValue($this->object->getEvaluationAccess());
			$form->addItem($evaluation_access);
		}
		// 360°
		else
		{			
			$ts_results = new ilRadioGroupInputGUI($this->lng->txt("survey_360_results"), "ts_res");
			$ts_results->setValue($this->object->get360Results());
			$ts_results->addOption(new ilRadioOption($this->lng->txt("survey_360_results_none"), ilObjSurvey::RESULTS_360_NONE));
			$ts_results->addOption(new ilRadioOption($this->lng->txt("survey_360_results_own"), ilObjSurvey::RESULTS_360_OWN));
			$ts_results->addOption(new ilRadioOption($this->lng->txt("survey_360_results_all"), ilObjSurvey::RESULTS_360_ALL));
			$form->addItem($ts_results);		
		}

		// mail notification
		$mailnotification = new ilCheckboxInputGUI($this->lng->txt("mailnotification"), "mailnotification");
		// $mailnotification->setOptionTitle($this->lng->txt("activate"));
		$mailnotification->setInfo($this->lng->txt("svy_result_mail_notification_info")); // #11762
		$mailnotification->setValue(1);
		$mailnotification->setChecked($this->object->getMailNotification());

		// addresses
		$mailaddresses = new ilTextInputGUI($this->lng->txt("mailaddresses"), "mailaddresses");
		$mailaddresses->setValue($this->object->getMailAddresses());
		$mailaddresses->setSize(80);
		$mailaddresses->setInfo($this->lng->txt('mailaddresses_info'));
		$mailaddresses->setRequired(true);

		// participant data
		$participantdata = new ilTextAreaInputGUI($this->lng->txt("mailparticipantdata"), "mailparticipantdata");
		$participantdata->setValue($this->object->getMailParticipantData());
		$participantdata->setRows(6);
		$participantdata->setCols(80);
		$participantdata->setUseRte(false);
		$participantdata->setInfo($this->lng->txt('mailparticipantdata_info'));
		
		// #12755 - because of privacy concerns we restrict user data to a minimum
		$placeholders = array(
			"FIRST_NAME" => "firstname",
			"LAST_NAME" => "lastname",			
			"LOGIN" => "login"
		);
		$txt = array();
		foreach($placeholders as $placeholder => $caption)
		{
			$txt[] = "[".strtoupper($placeholder)."]: ".$this->lng->txt($caption);
		}
		$txt = implode("<br />", $txt);		
		$participantdatainfo = new ilNonEditableValueGUI($this->lng->txt("mailparticipantdata_placeholder"), "", true);
		$participantdatainfo->setValue($txt);

		$mailnotification->addSubItem($mailaddresses);
		$mailnotification->addSubItem($participantdata);
		$mailnotification->addSubItem($participantdatainfo);
		$form->addItem($mailnotification);
		
		// reminder/notification - currently not available for 360° 
		if(!$this->object->get360Mode())
		{
			// parent course?
			global $tree;
			$has_parent = $tree->checkForParentType($this->object->getRefId(), "grp");
			if(!$has_parent)
			{
				$has_parent = $tree->checkForParentType($this->object->getRefId(), "crs");
			}
			$num_inv = sizeof($this->object->getInvitedUsers());

			$ntf = new ilFormSectionHeaderGUI();
			$ntf->setTitle($this->lng->txt("survey_notification_settings"));
			$form->addItem($ntf);

			// reminder
			$rmd = new ilCheckboxInputGUI($this->lng->txt("survey_reminder_setting"), "rmd");
			$rmd->setChecked($this->object->getReminderStatus());
			$form->addItem($rmd);

			$rmd_start = new ilDateTimeInputGUI($this->lng->txt("survey_reminder_start"), "rmd_start");
			$rmd_start->setRequired(true);
			$start = $this->object->getReminderStart();
			if($start)
			{
				$rmd_start->setDate($start);
			}
			$rmd->addSubItem($rmd_start);

			$end = $this->object->getReminderEnd();
			$rmd_end = new ilDateTimeInputGUI($this->lng->txt("survey_reminder_end"), "rmd_end");
			$rmd_end->enableDateActivation("", "rmd_end_tgl", (bool)$end);
			if($end)
			{
				$rmd_end->setDate($end);
			}
			$rmd->addSubItem($rmd_end);

			$rmd_freq = new ilNumberInputGUI($this->lng->txt("survey_reminder_frequency"), "rmd_freq");
			$rmd_freq->setRequired(true);
			$rmd_freq->setSize(3);		
			$rmd_freq->setSuffix($this->lng->txt("survey_reminder_frequency_days"));
			$rmd_freq->setValue($this->object->getReminderFrequency());
			$rmd_freq->setMinValue(1);
			$rmd->addSubItem($rmd_freq);

			$rmd_grp = new ilRadioGroupInputGUI($this->lng->txt("survey_notification_target_group"), "rmd_grp");
			$rmd_grp->setRequired(true);
			$rmd_grp->setValue($this->object->getReminderTarget());
			$rmd->addSubItem($rmd_grp);

			$rmd_grp_crs = new ilRadioOption($this->lng->txt("survey_notification_target_group_parent_course"), 
				ilObjSurvey::NOTIFICATION_PARENT_COURSE);		
			if(!$has_parent)
			{
				$rmd_grp_crs->setInfo($this->lng->txt("survey_notification_target_group_parent_course_inactive"));
			}
			$rmd_grp->addOption($rmd_grp_crs);

			$rmd_grp_inv = new ilRadioOption($this->lng->txt("survey_notification_target_group_invited"), 
				ilObjSurvey::NOTIFICATION_INVITED_USERS);
			$rmd_grp_inv->setInfo(sprintf($this->lng->txt("survey_notification_target_group_invited_info"), $num_inv));
			$rmd_grp->addOption($rmd_grp_inv);


			// notification
			$tut = new ilCheckboxInputGUI($this->lng->txt("survey_notification_tutor_setting"), "tut");
			$tut->setChecked($this->object->getTutorNotificationStatus());
			$form->addItem($tut);

			$tut_logins = array();
			$tuts = $this->object->getTutorNotificationRecipients();
			if($tuts)
			{
				foreach($tuts as $tut_id)
				{
					$tmp = ilObjUser::_lookupName($tut_id);
					if($tmp["login"])
					{
						$tut_logins[] = $tmp["login"];
					}
				}
			}		
			$tut_ids = new ilTextInputGUI($this->lng->txt("survey_notification_tutor_recipients"), "tut_ids");
			$tut_ids->setDataSource($this->ctrl->getLinkTarget($this, "doAutoComplete", "", true));
			$tut_ids->setRequired(true);
			$tut_ids->setMulti(true);		
			$tut_ids->setMultiValues($tut_logins);
			$tut_ids->setValue(array_shift($tut_logins));		
			$tut->addSubItem($tut_ids);

			$tut_grp = new ilRadioGroupInputGUI($this->lng->txt("survey_notification_target_group"), "tut_grp");
			$tut_grp->setRequired(true);
			$tut_grp->setValue($this->object->getTutorNotificationTarget());
			$tut->addSubItem($tut_grp);

			$tut_grp_crs = new ilRadioOption($this->lng->txt("survey_notification_target_group_parent_course"), 
				ilObjSurvey::NOTIFICATION_PARENT_COURSE);
			if(!$has_parent)
			{
				$tut_grp_crs->setInfo($this->lng->txt("survey_notification_target_group_parent_course_inactive"));
			}
			$tut_grp->addOption($tut_grp_crs);

			$tut_grp_inv = new ilRadioOption($this->lng->txt("survey_notification_target_group_invited"), 
				ilObjSurvey::NOTIFICATION_INVITED_USERS);
			$tut_grp_inv->setInfo(sprintf($this->lng->txt("survey_notification_target_group_invited_info"), $num_inv));
			$tut_grp->addOption($tut_grp_inv);
		}		
		
		// competence service activation for 360 mode
		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		if($this->object->get360Mode() && $skmg_set->isActivated())
		{
			$other = new ilFormSectionHeaderGUI();
			$other->setTitle($this->lng->txt("other"));
			$form->addItem($other);
			
			$skill_service = new ilCheckboxInputGUI($this->lng->txt("survey_activate_skill_service"), "skill_service");
			$skill_service->setChecked($this->object->get360SkillService());
			$form->addItem($skill_service);
		}
				
		$form->addCommandButton("saveProperties", $this->lng->txt("save"));

		// remove items when using template
		if($template_settings)
		{
			foreach($template_settings as $id => $item)
			{
				if($item["hide"])
				{
					$form->removeItemByPostVar($id);
				}
			}
		}
		
		return $form;
	}
	
	/**
	* Display and fill the properties form of the test
	*
	* @access	public
	*/
	function propertiesObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilAccess, $ilTabs, $ilHelp;
		
		$this->handleWriteAccess();
		
		$ilTabs->activateTab("settings");


		if ($this->object->get360Mode())
		{
			$ilHelp->setScreenId("settings_360");
		}
		
		if(!$a_form)
		{
			$a_form = $this->initPropertiesForm();
		}
		
		// using template?
		$message = "";
		if($this->object->getTemplate())
		{						
			$link = $this->ctrl->getLinkTarget($this, "confirmResetTemplate");
			$link = "<a href=\"".$link."\">".$this->lng->txt("survey_using_template_link")."</a>";
			$message = "<div style=\"margin-top:10px\">".
				$this->tpl->getMessageHTML(sprintf($this->lng->txt("survey_using_template"), 
					ilSettingsTemplate::lookupTitle($this->object->getTemplate()), $link), "info"). // #10651
				"</div>";
		}
	
		$this->tpl->setContent($a_form->getHTML().$message);
	}		
	
	function doAutoCompleteObject()
	{
		$fields = array('login','firstname','lastname','email');
				
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields($fields);
		$auto->setResultField('login');
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList(ilUtil::stripSlashes($_REQUEST['term']));
		exit();
	}
					
	/**
	 * Enable all settings - Confirmation
	 */
	function confirmResetTemplateObject()
	{
		ilUtil::sendQuestion($this->lng->txt("survey_confirm_template_reset"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_confirm_resettemplate.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BTN_CONFIRM_REMOVE", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_REMOVE", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "resetTemplateObject"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Enable all settings - remove template
	 */
	function resetTemplateObject()
	{
		$this->object->setTemplate(null);
		$this->object->saveToDB();

		ilUtil::sendSuccess($this->lng->txt("survey_template_reset"), true);
		$this->ctrl->redirect($this, "properties");
	}

	
	
	//
	// IMPORT/EXPORT
	// 
	
	protected function initImportForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("import_svy"));

		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);

		include_once("./Modules/Survey/classes/class.ilObjSurvey.php");
		$svy = new ilObjSurvey();
		$questionspools = $svy->getAvailableQuestionpools(true, true, true);

		$pools = new ilSelectInputGUI($this->lng->txt("select_questionpool_short"), "spl");
		$pools->setOptions(array(""=>$this->lng->txt("dont_use_questionpool")) + $questionspools);
		$pools->setRequired(false);
		$form->addItem($pools);

		$form->addCommandButton("importFile", $this->lng->txt("import"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}
	
	/**
	* form for new survey object import
	*/
	function importFileObject()
	{
		global $tpl, $ilErr;

		$parent_id = $_GET["ref_id"];
		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->checkPermissionBool("create", "", $new_type))
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}

		$this->lng->loadLanguageModule($new_type);
		$this->ctrl->setParameter($this, "new_type", $new_type);

		$form = $this->initImportForm($new_type);
		if ($form->checkInput())
		{
			include_once("./Modules/Survey/classes/class.ilObjSurvey.php");
			$newObj = new ilObjSurvey();
			$newObj->setType($new_type);
			$newObj->setTitle("dummy");
			$newObj->setDescription("dummy");
			$newObj->create(true);
			$this->putObjectInTree($newObj);

			// copy uploaded file to import directory
			$error = $newObj->importObject($_FILES["importfile"], $form->getInput("spl"));
			if (strlen($error))
			{
				$newObj->delete();
				$this->ilias->raiseError($error, $this->ilias->error_obj->MESSAGE);
				return;
			}

			ilUtil::sendSuccess($this->lng->txt("object_imported"),true);
			ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjSurveyGUI");

			// using template?
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$templates = ilSettingsTemplate::getAllSettingsTemplates("svy");
			if($templates)
			{
				global $tpl;
				$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
				// $tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");

				$this->tpl->setCurrentBlock("template_option");
				$this->tpl->setVariable("VAL_TEMPLATE_OPTION", "");
				$this->tpl->setVariable("TXT_TEMPLATE_OPTION", $this->lng->txt("none"));
				$this->tpl->parseCurrentBlock();

				foreach($templates as $item)
				{
					$this->tpl->setCurrentBlock("template_option");
					$this->tpl->setVariable("VAL_TEMPLATE_OPTION", $item["id"]);
					$this->tpl->setVariable("TXT_TEMPLATE_OPTION", $item["title"]);
					$this->tpl->parseCurrentBlock();

					$desc = str_replace("\n", "", nl2br($item["description"]));
					$desc = str_replace("\r", "", $desc);

					$this->tpl->setCurrentBlock("js_data");
					$this->tpl->setVariable("JS_DATA_ID", $item["id"]);
					$this->tpl->setVariable("JS_DATA_TEXT", $desc);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("templates");
				$this->tpl->setVariable("TXT_TEMPLATE", $this->lng->txt("svy_settings_template"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// display form to correct errors
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

  /*
	* list all export files
	*/
	public function exportObject()
	{
		global $ilTabs;
		
		$this->handleWriteAccess();
		$ilTabs->activateTab("export");

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if(count($export_files) > 0)
		{
			foreach($export_files as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Survey/classes/tables/class.ilSurveyExportTableGUI.php";
		$table_gui = new ilSurveyExportTableGUI($this, 'export');
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* create export file
	*/
	public function createExportFileObject()
	{
		$this->handleWriteAccess();
		include_once("./Modules/Survey/classes/class.ilSurveyExport.php");
		$survey_exp = new ilSurveyExport($this->object);
		$survey_exp->buildExportFile();
		$this->ctrl->redirect($this, "export");
	}

	/**
	* download export file
	*/
	public function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		if (count($_POST["file"]) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("select_max_one_item"), true);
			$this->ctrl->redirect($this, "export");
		}

		$file = basename($_POST["file"][0]);

		$export_dir = $this->object->getExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::deliverFile($export_dir."/".$file, $file);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		global $ilTabs;
		
		$this->handleWriteAccess();
		$ilTabs->activateTab("export");

		if (!isset($_POST["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if (count($_POST["file"]) > 0)
		{
			foreach ($_POST["file"] as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Survey/classes/tables/class.ilSurveyExportTableGUI.php";
		$table_gui = new ilSurveyExportTableGUI($this, 'export', true);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}


	/**
	* cancel deletion of export files
	*/
	public function cancelDeleteExportFileObject()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, "export");
	}


	/**
	* delete export files
	*/
	public function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach ($_POST["file"] as $file)
		{
			$file = basename($file);
			
			$exp_file = $export_dir."/".$file;
			$exp_dir = $export_dir."/".substr($file, 0, strlen($file) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::delDir($exp_dir);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_export_files'), true);
		$this->ctrl->redirect($this, "export");
	}

	
	// 
	// INFOSCREEN
	// 

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilTabs, $ilUser, $ilToolbar;
		
		if (!$this->external_rater_360 &&
			!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		$ilTabs->activateTab("info_short");
		
		include_once "./Modules/Survey/classes/class.ilSurveyExecutionGUI.php";
		$output_gui =& new ilSurveyExecutionGUI($this->object);		
		
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
				
		// "active" survey?
		$canStart = $this->object->canStartSurvey(null, $this->external_rater_360);
		
		$showButtons = $canStart["result"];
		if (!$showButtons)
		{
			if($canStart["edit_settings"] &&
				$ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$canStart["messages"][] = "<a href=\"".$this->ctrl->getLinkTarget($this, "properties")."\">&raquo; ".
					$this->lng->txt("survey_edit_settings")."</a>";
			}
			ilUtil::sendInfo(implode("<br />", $canStart["messages"]));
		}				
				
		$big_button = false;
		if ($showButtons)
		{				
			// closing survey?
			$is_appraisee = false; 
			if($this->object->get360Mode() && 
				$this->object->isAppraisee($ilUser->getId()))
			{
				$info->addSection($this->lng->txt("survey_360_appraisee_info"));

				$appr_data = $this->object->getAppraiseesData();
				$appr_data = $appr_data[$ilUser->getId()];
				$info->addProperty($this->lng->txt("survey_360_raters_status_info"), $appr_data["finished"]);		

				if(!$appr_data["closed"])
				{
					$close_button_360 = '<div>'.
						'<a class="submit" href="'.$this->ctrl->getLinkTargetByClass("ilsurveyparticipantsgui", "confirmappraiseeclose").'">'.
						$this->lng->txt("survey_360_appraisee_close_action").'</a></div>';

					$txt = "survey_360_appraisee_close_action_info";
					if($this->object->get360SkillService())
					{
						$txt .= "_skill";
					}								
					$info->addProperty($this->lng->txt("status"), 
						$close_button_360.$this->lng->txt($txt));									
				}
				else								
				{									
					ilDatePresentation::setUseRelativeDates(false);

					$dt = new ilDateTime($appr_data["closed"], IL_CAL_UNIX);								
					$info->addProperty($this->lng->txt("status"), 
						sprintf($this->lng->txt("survey_360_appraisee_close_action_status"),
							ilDatePresentation::formatDate($dt)));										
				}
				
				$is_appraisee = true;
			}
			
			
			// handle code				
			
			// validate incoming
			$code_input = false;
			$anonymous_code = $_POST["anonymous_id"];	
			if ($anonymous_code)
			{
				$code_input = true;
				// if(!$this->object->isUnusedCode($anonymous_code, $ilUser->getId()))
				if(!$this->object->checkSurveyCode($anonymous_code)) // #15031 - valid as long survey is not finished
				{
					$anonymous_code = null;
				}	
				else
				{
					// #15860
					$this->object->bindSurveyCodeToUser($ilUser->getId(), $anonymous_code);
				}
			}
			if ($anonymous_code)
			{
				$_SESSION["anonymous_id"][$this->object->getId()] = $anonymous_code;			
			}	
			else 
			{
				$anonymous_code = $_SESSION["anonymous_id"][$this->object->getId()];											
				if($anonymous_code)
				{
					$code_input = true;
				}
			}				
							
			// try to find code for current (registered) user from existing run
			if($this->object->getAnonymize() && !$anonymous_code)
			{
				$anonymous_code = $this->object->findCodeForUser($ilUser->getId());						
			}
			
			// get existing runs for current user, might generate code
			$participant_status = $this->object->getUserSurveyExecutionStatus($anonymous_code);
			if($participant_status)
			{				
				$anonymous_code = $participant_status["code"];				
				$participant_status = $participant_status["runs"];
			}
			
			// (final) check for proper anonymous code
			if(!$this->object->isAccessibleWithoutCode() && 
				!$is_appraisee &&
				$code_input && // #11346
				(!$anonymous_code || !$this->object->isAnonymousKey($anonymous_code)))
			{				
				 $anonymous_code = null;
				 ilUtil::sendInfo($this->lng->txt("wrong_survey_code_used"));
			}						
			
			// :TODO: really save in session?			
			$_SESSION["anonymous_id"][$this->object->getId()] = $anonymous_code;
			
			// code is mandatory and not given yet
			if(!$is_appraisee &&
				!$anonymous_code && 
				!$this->object->isAccessibleWithoutCode())
			{				
				$info->setFormAction($this->ctrl->getFormAction($this, "infoScreen"));
				$info->addSection($this->lng->txt("anonymization"));
				$info->addProperty("", $this->lng->txt("anonymize_anonymous_introduction"));
				$info->addPropertyTextinput($this->lng->txt("enter_anonymous_id"), "anonymous_id", "", 8, "infoScreen", $this->lng->txt("submit"), true);
			}						
			else
			{										
				// trunk/default
				if(!$this->object->get360Mode())
				{			
					if($anonymous_code)
					{
						$info->addHiddenElement("anonymous_id", $anonymous_code);
					}				
					
					$survey_started = $this->object->isSurveyStarted($ilUser->getId(), $anonymous_code);
					if ($survey_started === 1)
					{
						ilUtil::sendInfo($this->lng->txt("already_completed_survey"));
					}
					elseif ($survey_started === 0)
					{
						$big_button = array("resume", $this->lng->txt("resume_survey"));
					}
					elseif ($survey_started === FALSE)
					{
						$big_button = array("start", $this->lng->txt("start_survey"));
					}																
				}
				// 360°
				else
				{
					$appr_ids = array();
					
					// use given code (if proper external one)
					if($anonymous_code)
					{
						$anonymous_id = $this->object->getAnonymousIdByCode($anonymous_code);		
						if($anonymous_id)
						{
							$appr_ids = $this->object->getAppraiseesToRate(0, $anonymous_id);
						}
					}
					
					// registered user
					// if an auto-code was generated, we still have to check for the original user id
					if(!$appr_ids && $ilUser->getId() != ANONYMOUS_USER_ID)
					{
						$appr_ids = $this->object->getAppraiseesToRate($ilUser->getId());						
					}					
					
					if(sizeof($appr_ids))
					{																			
						// map existing runs to appraisees
						$active_appraisees = array();
						if($participant_status)
						{
							foreach($participant_status as $item)
							{
								$active_appraisees[$item["appr_id"]] = $item["finished"];
							}
						}					
						
						$list = array();
						
						foreach($appr_ids as $appr_id)
						{				
							if($this->object->isAppraiseeClosed($appr_id))
							{
								// closed
								$list[$appr_id] = $this->lng->txt("survey_360_appraisee_is_closed");
							}
							else if(array_key_exists($appr_id, $active_appraisees))
							{
								// already done							
								if($active_appraisees[$appr_id])
								{								
									$list[$appr_id] = $this->lng->txt("already_completed_survey");
								}
								// resume
								else
								{
									$list[$appr_id] = array("resume", $this->lng->txt("resume_survey"));
								}
							}
							else
							{
								// start
								$list[$appr_id] = array("start", $this->lng->txt("start_survey"));
							}
						}
						
						$info->addSection($this->lng->txt("survey_360_rate_other_appraisees"));
						
						include_once "Services/User/classes/class.ilUserUtil.php";
						foreach($list as $appr_id => $item)
						{					
							$appr_name = ilUserUtil::getNamePresentation($appr_id, false, false, "", true);
							
							if(!is_array($item))
							{							
								$info->addProperty($appr_name, $item);							
							}
							else
							{
								$this->ctrl->setParameter($output_gui, "appr_id", $appr_id);
								$href = $this->ctrl->getLinkTarget($output_gui, $item[0]);
								$this->ctrl->setParameter($output_gui, "appr_id", "");

								$big_button_360 = '<div>'.
									'<a class="submit" href="'.$href.'">'.$item[1].'</a></div>';

								$info->addProperty($appr_name, $big_button_360);							
							}						
						}																
					}					
					else if(!$is_appraisee)
					{
						ilUtil::sendFailure($this->lng->txt("survey_360_no_appraisees"));
					}																										
				}			
			}
			
			if($this->object->get360Mode() &&
				$this->object->get360SelfAppraisee() && 
				!$this->object->isAppraisee($ilUser->getId()) &&
				$ilUser->getId() != ANONYMOUS_USER_ID) // #14968
			{
				$link = $this->ctrl->getLinkTargetByClass("ilsurveyparticipantsgui", "addSelfAppraisee");
				$link = '<a href="'.$link.'">'.$this->lng->txt("survey_360_add_self_appraisee").'</a>';						
				$info->addProperty("&nbsp;", $link);								
			}				
		}
		
		if($big_button)
		{			
			$ilToolbar->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));
			$ilToolbar->addFormButton($big_button[1], $big_button[0], "", true);
			$ilToolbar->setCloseFormTag(false);
			$info->setOpenFormTag(false);
		}
		/* #12016
		else
		{
			$info->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));
		}
		*/
		
		if (strlen($this->object->getIntroduction()))
		{
			$introduction = $this->object->getIntroduction();
			$info->addSection($this->lng->txt("introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($introduction).
				"<br />".$info->getHiddenToggleButton());
		}
		else
		{
			$info->addSection("");
			$info->addProperty("", $info->getHiddenToggleButton());
		}

		$info->hideFurtherSections(false);
		
		$info->addSection($this->lng->txt("svy_general_properties"));
		if (strlen($this->object->getAuthor()))
		{
			$info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
		}
		$info->addProperty($this->lng->txt("title"), $this->object->getTitle());
		switch ($this->object->getAnonymize())
		{
			case ilObjSurvey::ANONYMIZE_OFF:
				$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_personalized"));
				break;
			case ilObjSurvey::ANONYMIZE_ON:
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("info_anonymize_with_code"));
				}
				else
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("info_anonymize_registered_user"));
				}
				break;
			case ilObjSurvey::ANONYMIZE_FREEACCESS:
				$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("info_anonymize_without_code"));
				break;
		}
		include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if ($ilAccess->checkAccess("write", "", $this->ref_id) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
		{
			$info->addProperty($this->lng->txt("evaluation_access"), $this->lng->txt("evaluation_access_info"));
		}
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		$this->ctrl->forwardCommand($info);
	}
						
	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "next":
			case "previous":
			case "start":
			case "resume":
			case "redirectQuestion":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
			case "evaluation":
			case "checkEvaluationAccess":
			case "evaluationdetails":
			case "evaluationuser":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"), "", $_GET["ref_id"]);
				break;
			case "create":
			case "save":
			case "cancel":
			case "importFile":
			case "cloneAll":
				break;
			case "infoScreen":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
		default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
						
				// this has to be done here because ilSurveyEditorGUI is called after finalizing the locator
				if ((int)$_GET["q_id"] && !(int)$_REQUEST["new_for_survey"])
				{
					// not on create
					// see ilObjSurveyQuestionPool::addLocatorItems
					$q_id = (int)$_GET["q_id"];
					include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
					$q_type = SurveyQuestion::_getQuestionType($q_id)."GUI";					
					$this->ctrl->setParameterByClass($q_type, "q_id", $q_id);
					$ilLocator->addItem(SurveyQuestion::_getTitle($q_id), 
						$this->ctrl->getLinkTargetByClass(array("ilSurveyEditorGUI", $q_type), "editQuestion"));																			
				}			
				break;
		}
	}
	
   
   
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target, $a_access_code = "")
	{
		global $ilAccess, $ilErr, $lng;
		
		// see ilObjSurveyAccess::_checkGoto()
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		if (strlen($a_access_code))
		{
			$_SESSION["anonymous_id"][ilObject::_lookupObjId($a_target)] = $a_access_code;
			$_GET["baseClass"] = "ilObjSurveyGUI";
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include("ilias.php");
			exit;
		}
		
		if ($ilAccess->checkAccess("read", "", $a_target))
		{			
			$_GET["baseClass"] = "ilObjSurveyGUI";
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include("ilias.php");
			exit;		
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}
} 

?>