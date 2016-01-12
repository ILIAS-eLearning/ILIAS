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
* @ilCtrl_Calls ilObjSurveyGUI: ilObjectMetaDataGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveySkillDeterminationGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilCommonActionDispatcherGUI, ilSurveySkillGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyEditorGUI, ilSurveyConstraintsGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyParticipantsGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilExportGUI
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
			if (!$ilAccess->checkAccess("visible", "", $this->ref_id) &&
				!$ilAccess->checkAccess("read", "", $this->ref_id))
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
				if(!in_array($this->ctrl->getCmdClass(),
					array('ilpublicuserprofilegui', 'ilobjportfoliogui')))
				{		
					$this->addHeaderAction();
					$this->infoScreen(); // forwards command
				}
				else 
				{
					// #16891
					$ilTabs->clearTargets();
					include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
					$info = new ilInfoScreenGUI($this);
					$this->ctrl->forwardCommand($info);
				}
				break;
			
			case 'ilobjectmetadatagui':
				$this->handleWriteAccess();			
				$ilTabs->activateTab("meta_data");
				$this->addHeaderAction();				
				include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
				$md_gui = new ilObjectMetaDataGUI($this->object);				
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
				
			case "illearningprogressgui":
				$ilTabs->activateTab("learning_progress");
				include_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");
				$new_gui = new ilLearningProgressGUI(ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
					$this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);				
				break;
			
			case 'ilexportgui':				
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this); 
				$exp_gui->addFormat("xml");
				$this->ctrl->forwardCommand($exp_gui);
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
		$tpl = $this->getDidacticTemplateVar("svytpl");
		if($tpl)
		{
			$a_new_object->applySettingsTemplate($tpl);
		}
		
		$a_new_object->set360Mode((bool)$this->getDidacticTemplateVar("svy360"));
		if($a_new_object->get360Mode())
		{
			// this should rather be ilObjSurvey::ANONYMIZE_ON - see ilObjSurvey::getUserDataFromActiveId()
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
		
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
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
				$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"));
		}
		
		// learning progress
		include_once "./Services/Tracking/classes/class.ilLearningProgressAccess.php";
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget("learning_progress",
				$this->ctrl->getLinkTargetByClass(array("ilobjsurveygui", "illearningprogressgui"), ""),
				"",
				array("illplistofobjectsgui", "illplistofsettingsgui", "illearningprogressgui", "illplistofprogressgui"));
		}		

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			if(!in_array("meta_data", $hidden_tabs))
			{
				// meta data			
				include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
				$mdgui = new ilObjectMetaDataGUI($this->object);					
				$mdtab = $mdgui->getTab();
				if($mdtab)
				{								
					$tabs_gui->addTab("meta_data",
						$this->lng->txt("meta_data"),
						$mdtab);
				}
			}

			if(!in_array("export", $hidden_tabs))
			{
				// export
				$tabs_gui->addTab("export",
					$this->lng->txt("export"),
					$this->ctrl->getLinkTargetByClass("ilexportgui", ""));				
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
				
				$this->object->setViewOwnResults($_POST["view_own"]);
				$this->object->setMailOwnResults($_POST["mail_own"]);

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
					if(!$template_settings["evaluation_access"]["hide"])
					{
						$this->object->setEvaluationAccess($_POST["evaluation_access"]);
					}

					$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
					if (!$hasDatasets)
					{						
						$hide_codes = $template_settings["acc_codes"]["hide"];
						$hide_anon = $template_settings["anonymization_options"]["hide"];						
						if(!$hide_codes || !$hide_anon)
						{					
							$current = $this->object->getAnonymize();
							
							// get current setting if property is hidden
							if(!$hide_codes)
							{
								$codes = (bool)$_POST["acc_codes"];
							}
							else
							{
								$codes = ($current == ilObjSurvey::ANONYMIZE_CODE_ALL ||
									$current == ilObjSurvey::ANONYMIZE_ON);
							}
							if(!$hide_anon)
							{
								$anon = ((string)$_POST["anonymization_options"] == "statanon");	
							}
							else
							{
								$anon = ($current == ilObjSurvey::ANONYMIZE_FREEACCESS ||
									$current == ilObjSurvey::ANONYMIZE_ON);
							}
							
							// parse incoming values
							if (!$anon)
							{			
								if (!$codes)
								{
									$this->object->setAnonymize(ilObjSurvey::ANONYMIZE_OFF);
								}
								else
								{
									$this->object->setAnonymize(ilObjSurvey::ANONYMIZE_CODE_ALL);
								}
							}
							else
							{
								if ($codes)
								{
									$this->object->setAnonymize(ilObjSurvey::ANONYMIZE_ON);
								}
								else
								{
									$this->object->setAnonymize(ilObjSurvey::ANONYMIZE_FREEACCESS);
								}
							}	

							// if settings were changed get rid of existing code
							unset($_SESSION["anonymous_id"][$this->object->getId()]);
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
			else
			{
				// #16714
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
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
		
		// 360°: appraisees
		if($this->object->get360Mode())
		{								
			$self_eval = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_evaluation"), "self_eval");
			$self_eval->setInfo($this->lng->txt("survey_360_self_evaluation_info"));
			$self_eval->setChecked($this->object->get360SelfEvaluation());
			$form->addItem($self_eval);

			$self_rate = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_raters"), "self_rate");
			$self_rate->setInfo($this->lng->txt("survey_360_self_raters_info"));
			$self_rate->setChecked($this->object->get360SelfRaters());
			$form->addItem($self_rate);

			$self_appr = new ilCheckboxInputGUI($this->lng->txt("survey_360_self_appraisee"), "self_appr");
			$self_appr->setInfo($this->lng->txt("survey_360_self_appraisee_info"));
			$self_appr->setChecked($this->object->get360SelfAppraisee());
			$form->addItem($self_appr);
		}
		
		
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
				
						
		// before start
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('svy_settings_section_before_start'));
		$form->addItem($section);
		
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

		
		// access
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('svy_settings_section_access'));
		$form->addItem($section);
		
		// enable start date
		$start = $this->object->getStartDate();
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("start_date"), "enabled_start_date");
		$enablestartingtime->setValue(1);
		// $enablestartingtime->setOptionTitle($this->lng->txt("enabled"));
		$enablestartingtime->setChecked($start);
		// start date
		$startingtime = new ilDateTimeInputGUI('', 'start_date');		
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
		$endingtime->setShowTime(true);		
		if ($end)
		{
			$endingtime->setDate(new ilDate($end, IL_CAL_TIMESTAMP));
		}
		$enableendingtime->addSubItem($endingtime);
		$form->addItem($enableendingtime);
							
		// anonymization
		if(!$this->object->get360Mode())
		{			
			$codes = new ilCheckboxInputGUI($this->lng->txt("survey_access_codes"), "acc_codes");
			$codes->setInfo($this->lng->txt("survey_access_codes_info"));
			$codes->setChecked(!$this->object->isAccessibleWithoutCode());
			$form->addItem($codes);
				
			if ($this->object->_hasDatasets($this->object->getSurveyId()))
			{
				$codes->setDisabled(true);				
			}			
		}
		
		
		// question behaviour
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('svy_settings_section_question_behaviour'));
		$form->addItem($section);
		
		// show question titles
		$show_question_titles = new ilCheckboxInputGUI($this->lng->txt("svy_show_questiontitles"), "show_question_titles");
		$show_question_titles->setValue(1);
		$show_question_titles->setChecked($this->object->getShowQuestionTitles());
		$form->addItem($show_question_titles);
		
		
		// finishing
		
		$info = new ilFormSectionHeaderGUI();
		$info->setTitle($this->lng->txt("svy_settings_section_finishing"));
		$form->addItem($info);
							
		$view_own = new ilCheckboxInputGUI($this->lng->txt("svy_results_view_own"), "view_own");
		$view_own->setInfo($this->lng->txt("svy_results_view_own_info"));
		$view_own->setChecked($this->object->hasViewOwnResults());
		$form->addItem($view_own);
		
		$mail_own = new ilCheckboxInputGUI($this->lng->txt("svy_results_mail_own"), "mail_own");
		$mail_own->setInfo($this->lng->txt("svy_results_mail_own_info"));
		$mail_own->setChecked($this->object->hasMailOwnResults());
		$form->addItem($mail_own);				
		
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
		$participantdata->setInfo($this->lng->txt("mailparticipantdata_info"));
		
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
	
		// tutor notification - currently not available for 360° 
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
		
		
		// reminders
		
		// reminder - currently not available for 360° 
		if(!$this->object->get360Mode())
		{			
			$info = new ilFormSectionHeaderGUI();
			$info->setTitle($this->lng->txt("svy_settings_section_reminders"));
			$form->addItem($info);		
			
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
		}		
		
		
		// results
		
		$results = new ilFormSectionHeaderGUI();
		$results->setTitle($this->lng->txt("results"));
		$form->addItem($results);

		// evaluation access
		if(!$this->object->get360Mode())
		{			
			$evaluation_access = new ilRadioGroupInputGUI($this->lng->txt('evaluation_access'), "evaluation_access");
			
			$option = new ilCheckboxOption($this->lng->txt("evaluation_access_off"), ilObjSurvey::EVALUATION_ACCESS_OFF, '');
			$option->setInfo($this->lng->txt("svy_evaluation_access_off_info"));
			$evaluation_access->addOption($option);
			
			$option = new ilCheckboxOption($this->lng->txt("evaluation_access_all"), ilObjSurvey::EVALUATION_ACCESS_ALL, '');
			$option->setInfo($this->lng->txt("svy_evaluation_access_all_info"));
			$evaluation_access->addOption($option);
			
			$option = new ilCheckboxOption($this->lng->txt("evaluation_access_participants"), ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS, '');
			$option->setInfo($this->lng->txt("svy_evaluation_access_participants_info"));
			$evaluation_access->addOption($option);
			
			$evaluation_access->setValue($this->object->getEvaluationAccess());
			$form->addItem($evaluation_access);
			
			
			$anonymization_options = new ilRadioGroupInputGUI($this->lng->txt("survey_results_anonymization"), "anonymization_options");	
			
			$option = new ilCheckboxOption($this->lng->txt("survey_results_personalized"), "statpers");
			$option->setInfo($this->lng->txt("survey_results_personalized_info"));			
			$anonymization_options->addOption($option);
			
			$option = new ilCheckboxOption($this->lng->txt("survey_results_anonymized"), "statanon");
			$option->setInfo($this->lng->txt("survey_results_anonymized_info"));			
			$anonymization_options->addOption($option);					
			$anonymization_options->setValue($this->object->hasAnonymizedResults()
				? "statanon"
				: "statpers");				
			$form->addItem($anonymization_options);
			
			if ($this->object->_hasDatasets($this->object->getSurveyId()))
			{
				$anonymization_options->setDisabled(true);
			}						
		}
		// 360°
		else
		{			
			$ts_results = new ilRadioGroupInputGUI($this->lng->txt("survey_360_results"), "ts_res");
			$ts_results->setValue($this->object->get360Results());
			
			$option = new ilRadioOption($this->lng->txt("survey_360_results_none"), ilObjSurvey::RESULTS_360_NONE);
			$option->setInfo($this->lng->txt("survey_360_results_none_info"));
			$ts_results->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt("survey_360_results_own"), ilObjSurvey::RESULTS_360_OWN);
			$option->setInfo($this->lng->txt("survey_360_results_own_info"));
			$ts_results->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt("survey_360_results_all"), ilObjSurvey::RESULTS_360_ALL);
			$option->setInfo($this->lng->txt("survey_360_results_all_info"));
			$ts_results->addOption($option);
			$form->addItem($ts_results);		
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
			$skill_service->setInfo($this->lng->txt("survey_activate_skill_service_info"));
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
		global $ilTabs, $ilHelp;
		
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
		$auto->setMoreLinkAvailable(true);

		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

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
			!$ilAccess->checkAccess("visible", "", $this->ref_id) &&
			!$ilAccess->checkAccess("read", "", $this->ref_id))
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
					include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
					$button = ilLinkButton::getInstance();
					$button->setCaption("survey_360_appraisee_close_action");
					$button->setUrl($this->ctrl->getLinkTargetByClass("ilsurveyparticipantsgui", "confirmappraiseeclose"));
					$close_button_360 = '<div>'.$button->render().'</div>';

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
						if($ilUser->getId() != ANONYMOUS_USER_ID)
						{
							if($this->object->hasViewOwnResults())
							{
								include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
								$button = ilLinkButton::getInstance();
								$button->setCaption("svy_view_own_results");								
								$button->setUrl($this->ctrl->getLinkTarget($this, "viewUserResults"));										
								$ilToolbar->addButtonInstance($button);		
							}

							if($this->object->hasMailOwnResults())
							{
								if($this->object->hasViewOwnResults())
								{
									$ilToolbar->addSeparator();
								}

								require_once "Services/Form/classes/class.ilTextInputGUI.php";								
								$mail = new ilTextInputGUI($this->lng->txt("email"), "mail");
								$mail->setSize(25);		
								$mail->setValue($ilUser->getEmail());															
								$ilToolbar->addInputItem($mail, true);							

								$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "mailUserResults"));
								
								include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
								$button = ilSubmitButton::getInstance();
								$button->setCaption("svy_mail_own_results");
								$button->setCommand("mailUserResults");
								$ilToolbar->addButtonInstance($button);																				
							}						
						}
						
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
								
								include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
								$button = ilLinkButton::getInstance();
								$button->setCaption($item[1], false);
								$button->setUrl($href);								
								$big_button_360 = '<div>'.$button->render().'</div>';

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
			
			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
			$button = ilSubmitButton::getInstance();
			$button->setCaption($big_button[1], false);
			$button->setCommand($big_button[0]);
			$button->setPrimary(true);
			$ilToolbar->addButtonInstance($button);		
			
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
						
		if(!$this->object->get360Mode())
		{
			$info->addSection($this->lng->txt("svy_general_properties"));
			
			$info->addProperty($this->lng->txt("survey_results_anonymization"), 
				!$this->object->hasAnonymizedResults()
					? $this->lng->txt("survey_results_personalized_info")
					: $this->lng->txt("survey_results_anonymized_info"));
					
			include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
			if ($ilAccess->checkAccess("write", "", $this->ref_id) || 
				ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
			{
				$info->addProperty($this->lng->txt("evaluation_access"), $this->lng->txt("evaluation_access_info"));
			}
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
	public static function _goto($a_target, $a_access_code = "")
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
		
		if ($ilAccess->checkAccess("visible", "", $a_target) ||
			$ilAccess->checkAccess("read", "", $a_target))
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
	
	public function getUserResultsTable($a_active_id)
	{
		$rtpl = new ilTemplate("tpl.svy_view_user_results.html", true, true, "Modules/Survey");
		
		$show_titles = (bool)$this->object->getShowQuestionTitles();
		
		foreach($this->object->getSurveyPages() as $page)
		{
			if(count($page) > 0)
			{
				// question block
				if(count($page) > 1)
				{
					if((bool)$page[0]["questionblock_show_blocktitle"])
					{
						$rtpl->setVariable("BLOCK_TITLE", trim($page[0]["questionblock_title"]));
					}
				}
				
				// questions
				foreach($page as $question)
				{				
					$question_gui = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
					if(is_object($question_gui))
					{
						$rtpl->setCurrentBlock("question_bl");
						
						// heading
						if(strlen($question["heading"]))
						{
							$rtpl->setVariable("HEADING", trim($question["heading"]));
						}
						
						$rtpl->setVariable("QUESTION_DATA", 
							$question_gui->getPrintView(
								$show_titles, 
								(bool)$question["questionblock_show_questiontext"], 
								$this->object->getId(),
								$this->object->loadWorkingData($question["question_id"], $a_active_id)
							)
						);
						
						$rtpl->parseCurrentBlock();						
					}
				}
				
				$rtpl->setCurrentBlock("block_bl");
				$rtpl->parseCurrentBlock();
			}			
		}
		
		return $rtpl->get();		
	}
	
	protected function viewUserResultsObject()
	{
		global $ilUser, $tpl, $ilTabs;
		
		$anonymous_code = $_SESSION["anonymous_id"][$this->object->getId()];
		$active_id = $this->object->getActiveID($ilUser->getId(), $anonymous_code, 0);
		if($this->object->isSurveyStarted($ilUser->getId(), $anonymous_code) !== 1 ||
			!$active_id)
		{
			$this->ctrl->redirect($this, "infoScreen");
		}			
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt("btn_back"), 
			$this->ctrl->getLinkTarget($this, "infoScreen"));
		
		$html = $this->getUserResultsTable($active_id);
		$tpl->setContent($html);									
	}
	
	protected function getUserResultsPlain($a_active_id)
	{		
		$res = array();
		
		$show_titles = (bool)$this->object->getShowQuestionTitles();
		
		foreach($this->object->getSurveyPages() as $page)
		{
			if(count($page) > 0)
			{
				$res[] = "\n~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
				
				// question block
				if(count($page) > 1)
				{
					if((bool)$page[0]["questionblock_show_blocktitle"])
					{						
						$res[$this->lng->txt("questionblock")] = trim($page[0]["questionblock_title"])."\n";
					}
				}
				
				// questions
				
				$page_res = array();
				
				foreach($page as $question)
				{						
					$question_gui = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
					if(is_object($question_gui))
					{						
						$question_parts = array();										
						
						// heading
						if(strlen($question["heading"]))
						{
							$question_parts[$this->lng->txt("heading")] = trim($question["heading"]);
						}
						
						if($show_titles)
						{
							$question_parts[$this->lng->txt("title")] = trim($question["title"]);
						}
						
						if((bool)$question["questionblock_show_questiontext"])
						{
							$question_parts[$this->lng->txt("question")] = trim(strip_tags($question_gui->object->getQuestionText()));
						}
						
						$answers = $question_gui->getParsedAnswers(
							$this->object->loadWorkingData($question["question_id"], $a_active_id),
							true
						);
						
						if(sizeof($answers))
						{				
							$multiline = false;
							if(sizeof($answers) > 1 || 
								get_class($question_gui) == "SurveyTextQuestionGUI")
							{
								$multiline = true;
							}
							
							$parts = array();
							foreach($answers as $answer)
							{	
								$text = null;
								if($answer["textanswer"])
								{
									$text = ' ("'.$answer["textanswer"].'")';
								}								
								if(!isset($answer["cols"]))
								{									
									if(isset($answer["title"]))
									{
										$parts[] = $answer["title"].$text;
									}
									else if(isset($answer["value"]))
									{
										$parts[] = $answer["value"];
									}
									else if($text)
									{
										$parts[] = substr($text, 2, -1);
									}											
								}
								// matrix
								else
								{									
									$tmp = array();
									foreach($answer["cols"] as $col)
									{
										$tmp[] = $col["title"];
									}								
									$parts[] = $answer["title"].": ".implode(", ", $tmp).$text;
								}
														
							}							
							$question_parts[$this->lng->txt("answer")] = 
								($multiline ? "\n" : "").implode("\n", $parts);
						}
						
						$tmp = array();
						foreach($question_parts as $type => $value)
						{
							$tmp[] = $type.": ".$value;
						}
						$page_res[] = implode("\n", $tmp);
					}													
				}				
				
				$res[] = implode("\n\n-------------------------------\n\n", $page_res);
			}			
		}
		
		$res[] = "\n~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
		
		return implode("\n", $res);
	}
	
	public function sendUserResultsMail($a_active_id, $a_recipient)
	{		
		global $ilUser;
		
		$finished = $this->object->getSurveyParticipants(array($a_active_id));
		$finished = array_pop($finished);
		$finished = ilDatePresentation::formatDate(new ilDateTime($finished["finished_tstamp"], IL_CAL_UNIX));
				
		require_once "Services/Mail/classes/class.ilMail.php";	
		require_once "Services/Link/classes/class.ilLink.php";
				
		$body = ilMail::getSalutation($ilUser->getId())."\n\n";		
		$body .= $this->lng->txt("svy_mail_own_results_body")."\n";		
		$body .= "\n".$this->lng->txt("obj_svy").": ".$this->object->getTitle()."\n";					
		$body .= ilLink::_getLink($this->object->getRefId(), "svy")."\n";
		$body .= "\n".$this->lng->txt("survey_results_finished").": ".$finished."\n\n";
		
		$body .= $this->getUserResultsPlain($a_active_id);
		
		// $body .= ilMail::_getAutoGeneratedMessageString($this->lng);
		$body .= ilMail::_getInstallationSignature();
				
		require_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail(ANONYMOUS_USER_ID);
		$mail->sendMimeMail(
			$a_recipient,
			null,
			null,
			sprintf($this->lng->txt("svy_mail_own_results_subject"), $this->object->getTitle()),
			$body,
			null,
			true
		);
	}
		
	function mailUserResultsObject()
	{
		global $ilUser;
		
		$anonymous_code = $_SESSION["anonymous_id"][$this->object->getId()];
		$active_id = $this->object->getActiveID($ilUser->getId(), $anonymous_code, 0);
		if($this->object->isSurveyStarted($ilUser->getId(), $anonymous_code) !== 1 ||
			!$active_id)
		{
			$this->ctrl->redirect($this, "infoScreen");
		}			
		
		$recipient = $_POST["mail"];	
		if(!ilUtil::is_email($recipient))
		{
			$this->ctrl->redirect($this, "infoScreen");
		}
		
		$this->sendUserResultsMail($active_id, $recipient);
		
		ilUtil::sendSuccess($this->lng->txt("mail_sent"), true);
		$this->ctrl->redirect($this, "infoScreen");
	}
} 

?>