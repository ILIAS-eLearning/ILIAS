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


/**
* Class ilObjSurveyGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
*
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyEvaluationGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilSurveyExecutionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilMDEditorGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSurveyGUI: ilRepositorySearchGUI, ilSurveyPageGUI
*
* @extends ilObjectGUI
* @ingroup ModulesSurvey
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

class ilObjSurveyGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyGUI()
	{
    global $lng, $ilCtrl;

		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id", "pgov", "pgov_pos");
	
		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
	}
	
	function backToRepositoryObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory,$ilCtrl;

		if ((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) && (!$ilAccess->checkAccess("visible", "", $_GET["ref_id"])))
		{
			global $ilias;
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjSurveyGUI&cmd=infoScreen&ref_id=".$_GET["ref_id"], "svy");
		}

		$cmd = $this->ctrl->getCmd("properties");

		// workaround for bug #6288, needs better solution
		if ($cmd == "saveTags")
		{
			$ilCtrl->setCmdClass("ilinfoscreengui");
		}

		// deep link from repository - "redirect" to page view
		if(!$this->ctrl->getCmdClass() && $cmd == "questionsrepo")
		{
			$_REQUEST["pgov"] = 1;
			$cmd = "questions";
			$ilCtrl->setCmd($cmd);
		}

		// return to questions in page view mode
		if(in_array($cmd, array("cancelRemoveQuestions", "questions", "confirmRemoveQuestions", 
			"cancelDeleteAllUserData", "confirmDeleteAllUserData", "cancelCreateQuestion",
			"cancelHeading", "cancelRemoveHeading", "confirmRemoveHeading", "cancelRemoveQuestions",
			"cancelDefineQuestionblock"))
			&& $_REQUEST["pgov"])
		{
			$ilCtrl->setCmdClass("ilsurveypagegui");
			if(!in_array($cmd, array("confirmRemoveQuestions", "confirmDeleteAllUserData",
				"confirmRemoveHeading")))
			{
				$ilCtrl->setCmd("renderPage");
			}
		}

		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "survey.css", "Modules/Survey"), "screen");
		$this->prepareOutput();
		//echo "<br>nextclass:$next_class:cmd:$cmd:qtype=$q_type";
		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->addHeaderAction();
				$this->infoScreen();	// forwards command
				break;
			
			case 'ilmdeditorgui':
				$this->addHeaderAction();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
			
			case "ilsurveyevaluationgui":
				$this->addHeaderAction();
				include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
				$eval_gui = new ilSurveyEvaluationGUI($this->object);
				$ret =& $this->ctrl->forwardCommand($eval_gui);
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'inviteUserGroupObject',
					array(
						)
					);

				// Set tabs
				$this->ctrl->setReturn($this, 'invite');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->tabs_gui->setTabActive('invitation');
				break;

			case "ilsurveyexecutiongui":
				include_once("./Modules/Survey/classes/class.ilSurveyExecutionGUI.php");
				$exec_gui = new ilSurveyExecutionGUI($this->object);
				$ret =& $this->ctrl->forwardCommand($exec_gui);
				break;
				
			case 'ilpermissiongui':
				$this->addHeaderAction();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('svy');
				$this->ctrl->forwardCommand($cp);
				break;

			case 'ilsurveypagegui':
				$this->addHeaderAction();
				include_once './Modules/Survey/classes/class.ilSurveyPageGUI.php';
				$pg = new ilSurveyPageGUI($this);
				$this->ctrl->forwardCommand($pg);
				break;

			default:
				$this->addHeaderAction();
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}

		if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}

	/**
	* save object
	* @access	public
	*/
	function afterSave(ilObject $a_new_object)
	{
		$template_id = (int)$_POST['template'];

		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$template = new ilSettingsTemplate($template_id);
		$template_settings = $template->getSettings();
		if($template_settings)
		{
			if($template_settings["show_question_titles"] !== NULL)
			{
				if($template_settings["show_question_titles"]["value"])
				{
					$a_new_object->setShowQuestionTitles(true);
				}
				else
				{
					$a_new_object->setShowQuestionTitles(false);
				}
			}

			if($template_settings["use_pool"] !== NULL)
			{
				if($template_settings["use_pool"]["value"])
				{
					$a_new_object->setPoolUsage(true);
				}
				else
				{
					$a_new_object->setPoolUsage(false);
				}
			}

			if($template_settings["anonymization_options"]["value"])
			{
				$anon_map = array('personalized' => ANONYMIZE_OFF,
					'anonymize_with_code' => ANONYMIZE_ON,
					'anonymize_without_code' => ANONYMIZE_FREEACCESS);
				$a_new_object->setAnonymize($anon_map[$template_settings["anonymization_options"]["value"]]);
			}

			/* other settings: not needed here
			 * - enabled_end_date
			 * - enabled_start_date
			 * - rte_switch
			 */
		}

		$a_new_object->setTemplate($template_id);
		$a_new_object->saveToDb();

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=".
			$a_new_object->getRefId()."&cmd=properties");
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* Cancel actions in the properties form
	*
	* Cancel actions in the properties form
	*
	* @access private
	*/
	function cancelPropertiesObject()
	{
		$this->ctrl->redirect($this, "properties");
	}
	
/**
* Checks for write access and returns to the parent object
*
* Checks for write access and returns to the parent object
*
* @access public
*/
  function handleWriteAccess()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), TRUE);
			$this->ctrl->redirect($this, "infoScreen");
		}
	}
	
	/**
	* Save the survey properties
	*
	* Save the survey properties
	*
	* @access private
	*/
	function savePropertiesObject()
	{
		$hasErrors = $this->propertiesObject(true);
		if (!$hasErrors)
		{
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
			
			$result = $this->object->setStatus($_POST['online']);
			$this->object->setEvaluationAccess($_POST["evaluation_access"]);

			if(!$template_settings["enabled_start_date"]["hide"])
			{
				$this->object->setStartDateEnabled($_POST["enabled_start_date"]);
				if ($this->object->getStartDateEnabled())
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
				$this->object->setEndDateEnabled($_POST["enabled_end_date"]);
				if ($this->object->getEndDateEnabled())
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

			$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
			if (!$hasDatasets)
			{
				$anon_map = array('personalized' => ANONYMIZE_OFF,
					'anonymize_with_code' => ANONYMIZE_ON,
					'anonymize_without_code' => ANONYMIZE_FREEACCESS);
				if(array_key_exists($_POST["anonymization_options"], $anon_map))
				{
					$this->object->setAnonymize($anon_map[$_POST["anonymization_options"]]);
					if (strcmp($_POST['anonymization_options'], 'anonymize_with_code') == 0) $anonymize = ANONYMIZE_ON;
					if (strcmp($_POST['anonymization_options'], 'anonymize_with_code_all') == 0) $anonymize = ANONYMIZE_CODE_ALL;
				}
			}

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

	/**
	* Display and fill the properties form of the test
	*
	* @access	public
	*/
	function propertiesObject($checkonly = FALSE)
	{
		global $ilAccess;

		$template_settings = $hide_rte_switch = null;
		$template = $this->object->getTemplate();
		if($template)
		{
			global $tpl;

			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);

			$template_settings = $template->getSettings();
			$hide_rte_switch = $template_settings["rte_switch"]["hide"];
		}

		$save = (strcmp($this->ctrl->getCmd(), "saveProperties") == 0) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("survey_properties");

		// general properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("settings"));
		$form->addItem($header);
		
		// online
		$online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$online->setValue(1);
		$online->setChecked($this->object->isOnline());
		$form->addItem($online);


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
		$pool_usage = new ilCheckboxInputGUI($this->lng->txt("survey_question_pool_usage"), "use_pool");
		$pool_usage->setValue(1);
		$pool_usage->setChecked($this->object->getPoolUsage());
		$form->addItem($pool_usage);

		
		// access properties
		$acc = new ilFormSectionHeaderGUI();
		$acc->setTitle($this->lng->txt("access"));
		$form->addItem($acc);
		
		// anonymization
		$anonymization_options = new ilRadioGroupInputGUI($this->lng->txt("survey_auth_mode"), "anonymization_options");
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

		// enable start date
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("start_date"), "enabled_start_date");
		$enablestartingtime->setValue(1);
		// $enablestartingtime->setOptionTitle($this->lng->txt("enabled"));
		$enablestartingtime->setChecked($this->object->getStartDateEnabled());
		// start date
		$startingtime = new ilDateTimeInputGUI('', 'start_date');
		$startingtime->setShowDate(true);
		$startingtime->setShowTime(true);
		if ($this->object->getStartDateEnabled())
		{
			$startingtime->setDate(new ilDate($this->object->getStartDate(), IL_CAL_DATE));
		}
		else
		{
			$startingtime->setDate(new ilDate(time(), IL_CAL_UNIX));
		}
		$enablestartingtime->addSubItem($startingtime);
		$form->addItem($enablestartingtime);

		// enable end date
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("end_date"), "enabled_end_date");
		$enableendingtime->setValue(1);
		// $enableendingtime->setOptionTitle($this->lng->txt("enabled"));
		$enableendingtime->setChecked($this->object->getEndDateEnabled());
		// end date
		$endingtime = new ilDateTimeInputGUI('', 'end_date');
		$endingtime->setShowDate(true);
		$endingtime->setShowTime(true);
		if ($this->object->getEndDateEnabled())
		{
			$endingtime->setDate(new ilDate($this->object->getEndDate(), IL_CAL_DATE));
		}
		else
		{
			$endingtime->setDate(new ilDate(time(), IL_CAL_UNIX));
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
		$intro->addPlugin("pastelatex");
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
		$finalstatement->addPlugin("pastelatex");
		$finalstatement->setRTESupport($this->object->getId(), "svy", "survey", null, $hide_rte_switch);
		$form->addItem($finalstatement);

		
		// results properties
		$results = new ilFormSectionHeaderGUI();
		$results->setTitle($this->lng->txt("results"));
		$form->addItem($results);

		// evaluation access
		$evaluation_access = new ilRadioGroupInputGUI($this->lng->txt('evaluation_access'), "evaluation_access");
		$evaluation_access->setInfo($this->lng->txt('evaluation_access_description'));
		$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_off"), EVALUATION_ACCESS_OFF, ''));
		$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_all"), EVALUATION_ACCESS_ALL, ''));
		$evaluation_access->addOption(new ilCheckboxOption($this->lng->txt("evaluation_access_participants"), EVALUATION_ACCESS_PARTICIPANTS, ''));
		$evaluation_access->setValue($this->object->getEvaluationAccess());
		$form->addItem($evaluation_access);

		// mail notification
		$mailnotification = new ilCheckboxInputGUI($this->lng->txt("mailnotification"), "mailnotification");
		// $mailnotification->setOptionTitle($this->lng->txt("activate"));
		$mailnotification->setValue(1);
		$mailnotification->setChecked($this->object->getMailNotification());

		// addresses
		$mailaddresses = new ilTextInputGUI($this->lng->txt("mailaddresses"), "mailaddresses");
		$mailaddresses->setValue($this->object->getMailAddresses());
		$mailaddresses->setSize(80);
		$mailaddresses->setInfo($this->lng->txt('mailaddresses_info'));
		$mailaddresses->setRequired(true);
		if (($save) && !$_POST['mailnotification'])
		{
			$mailaddresses->setRequired(false);
		}

		// participant data
		$participantdata = new ilTextAreaInputGUI($this->lng->txt("mailparticipantdata"), "mailparticipantdata");
		$participantdata->setValue($this->object->getMailParticipantData());
		$participantdata->setRows(6);
		$participantdata->setCols(80);
		$participantdata->setUseRte(false);
		$participantdata->setInfo($this->lng->txt('mailparticipantdata_info'));

		$mailnotification->addSubItem($mailaddresses);
		$mailnotification->addSubItem($participantdata);
		$form->addItem($mailnotification);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("saveProperties", $this->lng->txt("save"));

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

		$errors = false;
		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if (!$errors)
			{
				if (($online->getChecked()) && (count($this->object->questions) == 0))
				{
					$online->setAlert($this->lng->txt("cannot_switch_to_online_no_questions"));
					ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
					$errors = true;
				}
			}
			if ($errors) $checkonly = false;
		}
		
		$mailaddresses->setRequired(true);

		if (!$checkonly)
		{
			// using template?
			$message = "";
			if($template)
			{
				global $tpl;
				
				$link = $this->ctrl->getLinkTarget($this, "confirmResetTemplate");
				$link = "<a href=\"".$link."\">".$this->lng->txt("survey_using_template_link")."</a>";
				$message = "<div style=\"margin-top:10px\">".
					$tpl->getMessageHTML(sprintf($this->lng->txt("survey_using_template"), $template->getTitle(), $link), "info").
					"</div>";
			}
	
			$this->tpl->setVariable("ADM_CONTENT", $form->getHTML().$message);
		}
		
		return $errors;
	}
	
	/**
	* Remove questions from the survey
	*
	* Remove questions from the survey
	*
	* @access private
	*/
	function removeQuestionsObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, true, true);
		if (count($items["blocks"]) + count($items["questions"]) + count($items["headings"]) > 0)
		{
			ilUtil::sendQuestion($this->lng->txt("remove_questions"));
			$this->removeQuestionsForm($items["blocks"], $items["questions"], $items["headings"]);
			return;
		} 
		else 
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}

	/**
	* Insert questions into the survey
	*/
	public function insertQuestionsObject()
	{
		$inserted_objects = 0;
		if (is_array($_POST['q_id']))
		{
			foreach ($_POST['q_id'] as $question_id)
			{
				$this->object->insertQuestion($question_id);
				$inserted_objects++;
			}
		}
		if ($inserted_objects)
		{
			$this->object->saveCompletionStatus();
			ilUtil::sendSuccess($this->lng->txt("questions_inserted"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("insert_missing_question"), true);
			$this->ctrl->redirect($this, 'browseForQuestions');
		}
	}

	/**
	* Insert question blocks into the survey
	*/
	public function insertQuestionblocksObject()
	{
		$inserted_objects = 0;
		if (is_array($_POST['cb']))
		{
			foreach ($_POST['cb'] as $questionblock_id)
			{
				$this->object->insertQuestionblock($questionblock_id);
				$inserted_objects++;
			}
		}
		if ($inserted_objects)
		{
			$this->object->saveCompletionStatus();
			ilUtil::sendSuccess(($inserted_objects == 1) ? $this->lng->txt("questionblock_inserted") : $this->lng->txt("questionblocks_inserted"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("insert_missing_questionblock"), true);
			$this->ctrl->redirect($this, 'browseForQuestionblocks');
		}
	}
	
	/**
	* Change the object type in the question browser
	*/
	public function changeDatatypeObject()
	{
		global $ilUser;
		$ilUser->writePref('svy_insert_type', $_POST['datatype']);
		switch ($_POST["datatype"])
		{
			case 0:
				$this->ctrl->redirect($this, 'browseForQuestionblocks');
				break;
			case 1:
			default:
				$this->ctrl->redirect($this, 'browseForQuestions');
				break;
		}
	}
	
	/**
	* Filter the questionblock browser
	*/
	public function filterQuestionblockBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks');
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'browseForQuestionblocks');
	}
	
	/**
	* Reset the questionblock browser filter
	*/
	public function resetfilterQuestionblockBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks');
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'browseForQuestionblocks');
	}
	
	/**
	* list questions of question pool
	*/
	public function browseForQuestionblocksObject($arrFilter = null)
	{
		global $rbacsystem;
		global $ilUser;

		$this->setBrowseForQuestionsSubtabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questionbrowser.html", "Modules/Survey");
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', (($rbacsystem->checkAccess('write', $_GET['ref_id']) ? true : false)));
		$table_gui->setEditable($rbacsystem->checkAccess('write', $_GET['ref_id']));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getQuestionblocksTable($arrFilter);
		$table_gui->setData($data);
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());	

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, 'changeDatatype'));
		$this->tpl->setVariable("OPTION_QUESTIONS", $this->lng->txt("questions"));
		$this->tpl->setVariable("OPTION_QUESTIONBLOCKS", $this->lng->txt("questionblocks"));
		$this->tpl->setVariable("SELECTED_QUESTIONBLOCKS", " selected=\"selected\"");
		$this->tpl->setVariable("TEXT_DATATYPE", $this->lng->txt("display_all_available"));
		$this->tpl->setVariable("BTN_CHANGE", $this->lng->txt("change"));
	}

	/**
	* Filter the question browser
	*/
	public function filterQuestionBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions');
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'browseForQuestions');
	}
	
	/**
	* Reset the question browser filter
	*/
	public function resetfilterQuestionBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions');
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'browseForQuestions');
	}
	
	/**
	* list questions of question pool
	*/
	public function browseForQuestionsObject($arrFilter = null)
	{
		global $rbacsystem;
		global $ilUser;

		$this->setBrowseForQuestionsSubtabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questionbrowser.html", "Modules/Survey");
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', (($rbacsystem->checkAccess('write', $_GET['ref_id']) ? true : false)));
		$table_gui->setEditable($rbacsystem->checkAccess('write', $_GET['ref_id']));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getQuestionsTable($arrFilter);
		$table_gui->setData($data);
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());	

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, 'changeDatatype'));
		$this->tpl->setVariable("OPTION_QUESTIONS", $this->lng->txt("questions"));
		$this->tpl->setVariable("OPTION_QUESTIONBLOCKS", $this->lng->txt("questionblocks"));
		$this->tpl->setVariable("SELECTED_QUESTIONS", " selected=\"selected\"");
		$this->tpl->setVariable("TEXT_DATATYPE", $this->lng->txt("display_all_available"));
		$this->tpl->setVariable("BTN_CHANGE", $this->lng->txt("change"));
	}

/**
* Creates a confirmation form to remove questions from the survey
*
* @param array $checked_questions An array containing the id's of the questions to be removed
* @param array $checked_questionblocks An array containing the id's of the question blocks to be removed
* @access public
*/
	function removeQuestionsForm($checked_questionblocks, $checked_questions, $checked_headings)
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_remove_questions.html", "Modules/Survey");
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$surveyquestions =& $this->object->getSurveyQuestions();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		foreach ($surveyquestions as $question_id => $data)
		{
			if (in_array($data["question_id"], $checked_questions) or (in_array($data["questionblock_id"], $checked_questionblocks)))
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TEXT_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_DESCRIPTION", $data["description"]);
				$this->tpl->setVariable("TEXT_TYPE", SurveyQuestion::_getQuestionTypeName($data["type_tag"]));
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $data["questionblock_title"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
			else if (in_array($data["question_id"], $checked_headings))
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TEXT_TITLE", $data["heading"]);
				$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("heading"));
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $data["questionblock_title"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_".$id);
			$this->tpl->setVariable("HIDDEN_VALUE", $id);
			$this->tpl->parseCurrentBlock();
		}
		foreach ($checked_questionblocks as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_qb_".$id);
			$this->tpl->setVariable("HIDDEN_VALUE", $id);
			$this->tpl->parseCurrentBlock();
		}
		foreach ($checked_headings as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_tb_".$id);
			$this->tpl->setVariable("HIDDEN_VALUE", $id);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
		$this->tpl->parseCurrentBlock();
	}


/**
* Displays the definition form for a question block
*
* @param integer $questionblock_id The database id of the questionblock to edit an existing questionblock
* @access public
*/
	function defineQuestionblock($questionblock_id = "", $question_ids = null)
	{
		$this->questionsSubtabs("questions");
		if ($questionblock_id)
		{
			$questionblock = $this->object->getQuestionblock($questionblock_id);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_define_questionblock.html", "Modules/Survey");
		if ($question_ids)
		{
			foreach ($question_ids as $q_id)
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "qids[]");
				$this->tpl->setVariable("HIDDEN_VALUE", $q_id);
				$this->tpl->parseCurrentBlock();
			}
		}
		if ($questionblock_id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "questionblock_id");
			$this->tpl->setVariable("HIDDEN_VALUE", $questionblock_id);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		if ($questionblock_id)
		{
			$this->tpl->setVariable("VALUE_TITLE", $questionblock["title"]);
		}
		$this->tpl->setVariable("TXT_QUESTIONTEXT_DESCRIPTION", $this->lng->txt("show_questiontext_description"));
		$this->tpl->setVariable("TXT_QUESTIONTEXT", $this->lng->txt("show_questiontext"));
		if (($questionblock["show_questiontext"]) || (strlen($questionblock_id) == 0))
		{
			$this->tpl->setVariable("CHECKED_QUESTIONTEXT", " checked=\"checked\"");
		}		
		$this->tpl->setVariable("TXT_BLOCKTITLE_DESCRIPTION", $this->lng->txt("survey_show_blocktitle_description"));
		$this->tpl->setVariable("TXT_BLOCKTITLE", $this->lng->txt("survey_show_blocktitle"));
		if (($questionblock["show_blocktitle"]) || (strlen($questionblock_id) == 0))
		{
			$this->tpl->setVariable("CHECKED_BLOCKTITLE", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("HEADING_QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "saveDefineQuestionblock"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a form to select a survey question pool for storage
*
* @access public
*/
	function createQuestionObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilUser;

		if(!$this->object->isPoolActive())
		{
			$_POST["usage"] = 1;
			$_GET["sel_question_types"] = $_POST["sel_question_types"];
			return $this->executeCreateQuestionObject();
		}

		if(!$a_form)
		{
			if(!$_REQUEST["pgov"])
			{
				$this->questionsSubtabs("questions");
			}
			else
			{
				$this->questionsSubtabs("questions_per_page");
			}

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$form = new ilPropertyFormGUI();

			$sel_question_types = (strlen($_POST["sel_question_types"])) ? $_POST["sel_question_types"] : $_GET["sel_question_types"];
			$this->ctrl->setParameter($this, "sel_question_types", $sel_question_types);
			$form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
		}
		else
		{
			$form = $a_form;
		}

		$usage = new ilRadioGroupInputGUI($this->lng->txt("survey_pool_selection"), "usage");
		$usage->setRequired(true);
		$no_pool = new ilRadioOption($this->lng->txt("survey_no_pool"), 1);
		$usage->addOption($no_pool);
		$existing_pool = new ilRadioOption($this->lng->txt("survey_existing_pool"), 3);
		$usage->addOption($existing_pool);
		$new_pool = new ilRadioOption($this->lng->txt("survey_new_pool"), 2);
		$usage->addOption($new_pool);
		$form->addItem($usage);

		if(isset($_SESSION["svy_qpool_choice"]))
		{
			$usage->setValue($_SESSION["svy_qpool_choice"]);
		}
		else
		{
			// default: no pool
			$usage->setValue(1);
		}

		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, TRUE, TRUE, "write");
		$pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_spl");
		$pools->setOptions($questionpools);
		$existing_pool->addSubItem($pools);

		$name = new ilTextInputGUI($this->lng->txt("cat_create_spl"), "name_spl");
		$name->setSize(50);
		$name->setMaxLength(50);
		$new_pool->addSubItem($name);

		if($a_form)
		{
			return $a_form;
		}

		$form->addCommandButton("executeCreateQuestion", $this->lng->txt("submit"));
		$form->addCommandButton("cancelCreateQuestion", $this->lng->txt("cancel"));

		return $this->tpl->setContent($form->getHTML());
	}

/**
* Cancel the creation of a new questions in a survey
*
* @access private
*/
	function cancelCreateQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Execute the creation of a new questions in a survey
*
* @access private
*/
	function executeCreateQuestionObject()
	{
		$addurl = "";
		if($_REQUEST["pgov"])
		{
			$addurl .= "&pgov=".$_REQUEST["pgov"]."&pgov_pos=".$_REQUEST["pgov_pos"];
		}

		include_once "./Services/Utilities/classes/class.ilUtil.php";

		$_SESSION["svy_qpool_choice"] = $_POST["usage"];

		// no pool
		if ($_POST["usage"] == 1)
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=".
				$_GET["ref_id"]."&cmd=createQuestionForSurvey&new_for_survey=".
				$_GET["ref_id"]."&sel_question_types=".$_GET["sel_question_types"].$addurl);
		}
		// existing pool
		else if ($_POST["usage"] == 3 && strlen($_POST["sel_spl"]))
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=".
				$_POST["sel_spl"]."&cmd=createQuestionForSurvey&new_for_survey=".$_GET["ref_id"].
				"&sel_question_types=".$_GET["sel_question_types"].$addurl);
		}
		// new pool
		elseif ($_POST["usage"] == 2 && strlen($_POST["name_spl"]))
		{
			$ref_id = $this->createQuestionPool($_POST["name_spl"]);
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=".$ref_id.
				"&cmd=createQuestionForSurvey&new_for_survey=".$_GET["ref_id"].
				"&sel_question_types=".$_GET["sel_question_types"].$addurl);
		}
		else
		{
			if(!$_POST["usage"])
			{
				ilUtil::sendFailure($this->lng->txt("select_one"), true);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("err_no_pool_name"), true);
			}
			$this->ctrl->setParameter($this, "sel_question_types", $_GET["sel_question_types"]);
			$this->ctrl->redirect($this, "createQuestion");
		}
	}
	
	/**
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	private function createQuestionPool($name = "dummy")
	{
		global $tree;
		$parent_ref = $tree->getParentId($this->object->getRefId());
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$qpl = new ilObjSurveyQuestionPool();
		$qpl->setType("spl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		return $qpl->getRefId();
	}

/**
* Creates a form to add a heading to a survey
*
* @param integer $question_id The id of the question directly after the heading. If the id is given, an existing heading will be edited
* @access public
*/
	function addHeadingObject($checkonly = false, $question_id = "")
	{
		$this->questionsSubtabs("questions");

		global $ilAccess;
		
		$save = (strcmp($this->ctrl->getCmd(), "saveHeading") == 0) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("survey_heading");

		// general properties
		$header = new ilFormSectionHeaderGUI();
		if ($question_id)
		{
			$header->setTitle($this->lng->txt("edit_heading"));
		}
		else
		{
			$header->setTitle($this->lng->txt("add_heading"));
		}
		$form->addItem($header);

		$survey_questions =& $this->object->getSurveyQuestions();
		
		// heading
		$heading = new ilTextAreaInputGUI($this->lng->txt("heading"), "heading");
		$heading->setValue($this->object->prepareTextareaOutput(array_key_exists('heading', $_POST) ? $_POST['heading'] : $survey_questions[$question_id]["heading"]));
		$heading->setRows(10);
		$heading->setCols(80);
		$heading->setUseRte(TRUE);
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$heading->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
		$heading->removePlugin("ibrowser");
		$heading->setRTESupport($this->object->getId(), "svy", "survey");
		$heading->setRequired(true);
		$form->addItem($heading);

		$insertbefore = new ilSelectInputGUI($this->lng->txt("insert"), "insertbefore");
		$options = array();
		foreach ($survey_questions as $key => $value)
		{
			$options[$key] = $this->lng->txt("before") . ": \"" . $value["title"] . "\"";
		}
		$insertbefore->setOptions($options);
		$insertbefore->setValue((array_key_exists('insertbefore', $_REQUEST)) ? $_REQUEST['insertbefore'] : $question_id);
		$insertbefore->setRequired(true);
		if ($question_id || array_key_exists('insertbefore', $_REQUEST))
		{
			$insertbefore->setDisabled(true);
		}
		$form->addItem($insertbefore);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("saveHeading", $this->lng->txt("save"));
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("cancelHeading", $this->lng->txt("cancel"));
		$errors = false;
		
		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if ($errors) $checkonly = false;
		}
		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}

/**
* Insert questions or question blocks into the survey after confirmation
*
* @access public
*/
	function confirmInsertQuestionObject()
	{
		// insert questions from test after confirmation
		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				if ($_GET["browsetype"] == 1)
				{
					$this->object->insertQuestion($matches[1]);
				}
				else
				{
					$this->object->insertQuestionBlock($matches[1]);
				}
			}
		}
		$this->object->saveCompletionStatus();
		ilUtil::sendSuccess($this->lng->txt("questions_inserted"), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancels insert questions or question blocks into the survey
*
* @access public
*/
	function cancelInsertQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

/**
* Saves an edited heading in the survey questions list
*
* Saves an edited heading in the survey questions list
*
* @access public
*/
	function saveHeadingObject()
	{
		$hasErrors = $this->addHeadingObject(true);
		if (!$hasErrors)
		{
			$insertbefore = $_POST["insertbefore"];
			if (!$insertbefore)
			{
				$insertbefore = $_POST["insertbefore_original"];
			}
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$this->object->saveHeading(ilUtil::stripSlashes($_POST["heading"], TRUE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")), $insertbefore);
			$this->ctrl->redirect($this, "questions");
		}
	}
	
/**
* Cancels saving a heading in the survey questions list
*
* Cancels saving a heading in the survey questions list
*
* @access public
*/
	function cancelHeadingObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

/**
* Remove a survey heading after confirmation
*
* Remove a survey heading after confirmation
*
* @access public
*/
	function confirmRemoveHeadingObject()
	{
		$this->object->saveHeading("", $_POST["removeheading"]);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancels the removal of survey headings
*
* Cancels the removal of survey headings
*
* @access public
*/
	function cancelRemoveHeadingObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Displays a confirmation form to delete a survey heading
*
* Displays a confirmation form to delete a survey heading
*
* @access public
*/
	function confirmRemoveHeadingForm()
	{
		ilUtil::sendQuestion($this->lng->txt("confirm_remove_heading"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_confirm_removeheading.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BTN_CONFIRM_REMOVE", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_REMOVE", $this->lng->txt("cancel"));
		$this->tpl->setVariable("REMOVE_HEADING", $_GET["removeheading"]);
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "confirmRemoveHeading"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Remove questions from survey after confirmation
*
* Remove questions from survey after confirmation
*
* @access private
*/
	function confirmRemoveQuestionsObject()
	{
		$checked_questions = array();
		$checked_questionblocks = array();
		$checked_headings = array();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/id_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questions, $matches[1]);
			}
			if (preg_match("/id_qb_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questionblocks, $matches[1]);
			}
			if (preg_match("/id_tb_(\d+)/", $key, $matches))
			{
				array_push($checked_headings, $matches[1]);
			}
		}

		if(sizeof($checked_questions) || sizeof($checked_questionblocks))
		{
			$this->object->removeQuestions($checked_questions, $checked_questionblocks);
		}
		if($checked_headings)
		{
			foreach($checked_headings as $q_id)
			{
				$this->object->saveHeading("", $q_id);
			}
		}
		$this->object->saveCompletionStatus();
		ilUtil::sendSuccess($this->lng->txt("questions_removed"), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancel remove questions from survey after confirmation
*
* Cancel remove questions from survey after confirmation
*
* @access private
*/
	function cancelRemoveQuestionsObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	 * Gather (and filter) selected items from table gui
	 *
	 * @param bool $allow_blocks
	 * @param bool $allow_questions
	 * @param bool $allow_headings
	 * @param bool $allow_questions_in_blocks
	 * @return array (questions, blocks, headings)
	 */
	protected function gatherSelectedTableItems($allow_blocks = true, $allow_questions = true, $allow_headings = false, $allow_questions_in_blocks = false)
	{
		$block_map = array();
		foreach($this->object->getSurveyQuestions() as $item)
		{
			$block_map[$item["question_id"]] = $item["questionblock_id"];
		}
		
		$questions = $blocks = $headings = array();
		if($_POST["id"])
		{
			foreach ($_POST["id"] as $key)
			{
				// questions
				if ($allow_questions && preg_match("/cb_(\d+)/", $key, $matches))
				{
					if(($allow_questions_in_blocks || !$block_map[$matches[1]]) &&
						!in_array($block_map[$matches[1]], $blocks))
					{
						array_push($questions, $matches[1]);
					}
				}
				// blocks
				if ($allow_blocks && preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($blocks, $matches[1]);
				}
				// headings
				if ($allow_headings && preg_match("/cb_tb_(\d+)/", $key, $matches))
				{
					array_push($headings, $matches[1]);
				}
			}
		}
		
		return array("questions" => $questions,
			"blocks" => $blocks,
			"headings" => $headings);
	}

/**
* Cancel remove questions from survey after confirmation
*
* Cancel remove questions from survey after confirmation
*
* @access private
*/
	function defineQuestionblockObject()
	{
		$items = $this->gatherSelectedTableItems(false, true, false, false);
		if (count($items["questions"]) < 2)
		{
			ilUtil::sendInfo($this->lng->txt("qpl_define_questionblock_select_missing"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$this->defineQuestionblock("", $items["questions"]);
			return;
		}
	}
	
/**
* Confirm define a question block
*/
	public function saveDefineQuestionblockObject()
	{
		if ($_POST["title"])
		{
			$show_questiontext = ($_POST["show_questiontext"]) ? 1 : 0;
			$show_blocktitle = ($_POST["show_blocktitle"]) ? 1 : 0;
			if ($_POST["questionblock_id"])
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->object->modifyQuestionblock($_POST["questionblock_id"], ilUtil::stripSlashes($_POST["title"]), $show_questiontext, $show_blocktitle);
			}
			else
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->object->createQuestionblock(ilUtil::stripSlashes($_POST["title"]), $show_questiontext, $show_blocktitle, $_POST["qids"]);
			}
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->setParameter($this, "pgov", $_REQUEST["pgov"]);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("enter_questionblock_title"));
			$this->defineQuestionblockObject();
			return;
		}
	}

/**
* Unfold a question block
*/
	public function unfoldQuestionblockObject()
	{
		$items = $this->gatherSelectedTableItems(true, false, false, false);
		if (count($items["blocks"]))
		{
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->object->unfoldQuestionblocks($items["blocks"]);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_unfold_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Cancel define a question block
*/
	public function cancelDefineQuestionblockObject()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Move questions
*/
	public function moveQuestionsObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, false, false);

		$move_questions = $items["questions"];
		foreach ($items["blocks"] as $block_id)
		{
			foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid)
			{
				array_push($move_questions, $qid);
			}
		}
		if (count($move_questions) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_move"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$_SESSION["move_questions"] = $move_questions;
			ilUtil::sendInfo($this->lng->txt("select_target_position_for_move_question"));
			$this->questionsObject();
		}
	}

/**
* Insert questions from move clipboard
*/
	public function insertQuestions($insert_mode)
	{
		$insert_id = null;
		if($_POST["id"])
		{
			$items = $this->gatherSelectedTableItems(true, true, false, false);

			// we are using POST id for original order
			while(!$insert_id && sizeof($_POST["id"]))
			{
				$target = array_shift($_POST["id"]);
				if (preg_match("/^cb_(\d+)$/", $target, $matches))
				{
					// questions in blocks are not allowed
					if(in_array($matches[1], $items["questions"]))
					{
						$insert_id = $matches[1];
					}
				}
				if (!$insert_id && preg_match("/^cb_qb_(\d+)$/", $target, $matches))
				{
					$ids = $this->object->getQuestionblockQuestionIds($matches[1]);
					if (count($ids))
					{
						if ($insert_mode == 0)
						{
							$insert_id = $ids[0];
						}
						else if ($insert_mode == 1)
						{
							$insert_id = $ids[count($ids)-1];
						}
					}
				}
			}
		}

		if(!$insert_id)
		{
			ilUtil::sendInfo($this->lng->txt("no_target_selected_for_move"), true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->object->moveQuestions($_SESSION["move_questions"], $insert_id, $insert_mode);
			unset($_SESSION["move_questions"]);
		}
	
		$this->ctrl->redirect($this, "questions");
	}

/**
* Insert questions before selection
*/
	public function insertQuestionsBeforeObject()
	{
		$this->insertQuestions(0);
	}
	
/**
* Insert questions after selection
*/
	public function insertQuestionsAfterObject()
	{
		$this->insertQuestions(1);
	}

/**
* Save obligatory states
*/
	public function saveObligatoryObject()
	{		
		if(isset($_POST["order"]))
		{
			$position = -1;
			$order = array();
			asort($_POST["order"]);
			foreach(array_keys($_POST["order"]) as $id)
			{
				// block items
				if(substr($id, 0, 3) == "qb_")
				{
					$block_id = substr($id, 3);
					$block = $_POST["block_order"][$block_id];
					asort($block);
					foreach(array_keys($block) as $question_id)
					{
						$position++;
						$order[$question_id] = $position;
					}
				}
				else
				{
					$question_id = substr($id, 2);
					$position++;
					$order[$question_id] = $position;
				}
			}
			$this->object->updateOrder($order);
		}

		$obligatory = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/obligatory_(\d+)/", $key, $matches))
			{
				$obligatory[$matches[1]] = 1;
			}
		}
		$this->object->setObligatoryStates($obligatory);
		ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
		$this->ctrl->redirect($this, "questions");
	}
	
/**
* Creates the questions form for the survey object
*/
	public function questionsObject() 
	{
		global $rbacsystem, $ilToolbar, $ilUser;

		$this->handleWriteAccess();

		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if ($_GET["new_id"] > 0)
		{
			// add a question to the survey previous created in a questionpool
			$existing = $this->object->getExistingQuestions();
			if (!in_array($_GET["new_id"], $existing))
			{
				$inserted = $this->object->insertQuestion($_GET["new_id"]);
				if (!$inserted)
				{
					ilUtil::sendFailure($this->lng->txt("survey_error_insert_incomplete_question"));
				}
			}
		}
		
		if ($_GET["eqid"] and $_GET["eqpl"])
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForSurvey&calling_survey=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}


		$_SESSION["calling_survey"] = $this->object->getRefId();
		unset($_SESSION["survey_id"]);

		if ($_GET["editheading"])
		{
			$this->addHeadingObject(false, $_GET["editheading"]);
			return;
		}

		/*
		if ($_GET["up"] > 0)
		{
			$this->object->moveUpQuestion($_GET["up"]);
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'));
		}
		if ($_GET["down"] > 0)
		{
			$this->object->moveDownQuestion($_GET["down"]);
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'));
		}
		if ($_GET["qbup"] > 0)
		{
			$this->object->moveUpQuestionblock($_GET["qbup"]);
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'));
		}
		if ($_GET["qbdown"] > 0)
		{
			$this->object->moveDownQuestionblock($_GET["qbdown"]);
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'));
		}
		*/
		
		if ($_GET["removeheading"])
		{
			$this->confirmRemoveHeadingForm();
			return;
		}
		
		if ($_GET["editblock"])
		{
			$this->defineQuestionblock($_GET["editblock"]);
			return;
		}

		if ($_GET["add"])
		{
			// called after a new question was created from a questionpool
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			ilUtil::sendQuestion($this->lng->txt("ask_insert_questions"));
			$this->insertQuestionsForm($selected_array);
			return;
		}

		
		$this->questionsSubtabs("questions");

		$read_only = (!$rbacsystem->checkAccess("write", $this->ref_id) || $hasDatasets);
		

		// toolbar

		if (!$read_only)
		{
			$cmd = ($ilUser->getPref('svy_insert_type') == 1 || strlen($ilUser->getPref('svy_insert_type')) == 0) ? 'browseForQuestions' : 'browseForQuestionblocks';
			$ilToolbar->addButton($this->lng->txt("browse_for_questions"),
				$this->ctrl->getLinkTarget($this, $cmd));

			$ilToolbar->addSeparator();

			$qtypes = array();
			include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
			foreach (ilObjSurveyQuestionPool::_getQuestiontypes() as $translation => $data)
			{
				$qtypes[$data["type_tag"]] = $translation;
			}

			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$types = new ilSelectInputGUI($this->lng->txt("create_new"), "sel_question_types");
			$types->setOptions($qtypes);
			$ilToolbar->addInputItem($types, $this->lng->txt("create_new"));
			$ilToolbar->addFormButton($this->lng->txt("create"), "createQuestion");

			$ilToolbar->addSeparator();

			$ilToolbar->addButton($this->lng->txt("add_heading"), 
				$this->ctrl->getLinkTarget($this, "addHeading"));
		}
		if ($hasDatasets)
		{
			// ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning"));
			$link = $this->ctrl->getLinkTarget($this, "maintenance");
			$link = "<a href=\"".$link."\">".$this->lng->txt("survey_has_datasets_warning_page_view_link")."</a>";
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning_page_view")." ".$link);
		}

	
		// table gui

		include_once "Modules/Survey/classes/class.ilSurveyQuestionTableGUI.php";
		$table = new ilSurveyQuestionTableGUI($this, "questions", $this->object,
			$read_only);
		$this->tpl->setContent($table->getHTML());
	}

	/**
	* Redirects the evaluation object call to the ilSurveyEvaluationGUI class
	*
	* Redirects the evaluation object call to the ilSurveyEvaluationGUI class
	*
	* @access	private
	*/
	function evaluationObject()
	{
		include_once("./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php");
		$eval_gui = new ilSurveyEvaluationGUI($this->object);
		$this->ctrl->setCmdClass(get_class($eval_gui));
		$this->ctrl->redirect($eval_gui, "evaluation");
	}
	
	/**
	* Disinvite users or groups from a survey
	*/
	public function disinviteUserGroupObject()
	{
		// disinvite users
		if (is_array($_POST["user_select"]))
		{
			foreach ($_POST["user_select"] as $user_id)
			{
				$this->object->disinviteUser($user_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('msg_users_disinvited'), true);
		$this->ctrl->redirect($this, "invite");
	}
	
	/**
	* Invite users or groups to a survey
	*/
	public function inviteUserGroupObject($a_user_ids = array())
	{
		$invited = 0;
		// add users to invitation
		if (is_array($a_user_ids))
		{
			foreach ($a_user_ids as $user_id)
			{
				$this->object->inviteUser($user_id);
				$invited++;
			}
		}
		if ($invited == 0)
		{
			ilUtil::sendFailure($this->lng->txt('no_user_invited'), TRUE);
			return false;
		}
		else
		{
			ilUtil::sendSuccess(sprintf($this->lng->txt('users_invited'), $invited), TRUE);
			return false;
		}
		$this->ctrl->redirect($this, "invite");
	}

	/**
	* Saves the status of the invitation tab
	*/
	public function saveInvitationStatusObject()
	{
		$mode = $_POST['invitation'];
		switch ($mode)
		{
			case 0:
				$this->object->setInvitation(0);
				break;
			case 1:
				$this->object->setInvitation(1);
				$this->object->setInvitationMode(0);
				break;
			case 2:
				$this->object->setInvitation(1);
				$this->object->setInvitationMode(1);
				break;
		}
		$this->object->saveToDb();
		ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
		$this->ctrl->redirect($this, "invite");
	}
	
	
	/**
	* Creates the output for user/group invitation to a survey
	*/
	public function inviteObject()
	{
		global $ilAccess;
		global $rbacsystem;
		global $ilToolbar;
		global $lng;

		if ((!$rbacsystem->checkAccess("visible,invite", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_survey"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}

		if ($this->object->getStatus() == STATUS_OFFLINE)
		{
			ilUtil::sendInfo($this->lng->txt("survey_offline_message"));
			return;
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("500");
		$form->setId("invite");

		// invitation
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("invitation"));
		$form->addItem($header);
		
		// invitation mode
		$invitation = new ilRadioGroupInputGUI($this->lng->txt('invitation_mode'), "invitation");
		$invitation->setInfo($this->lng->txt('invitation_mode_desc'));
		$invitation->addOption(new ilRadioOption($this->lng->txt("invitation_off"), 0, ''));
		$surveySetting = new ilSetting("survey");
		if ($surveySetting->get("unlimited_invitation"))
		{
			$invitation->addOption(new ilRadioOption($this->lng->txt("unlimited_users"), 1, ''));
		}
		$invitation->addOption(new ilRadioOption($this->lng->txt("predefined_users"), 2, ''));
		$inv = 0;
		if ($this->object->getInvitation())
		{
			$inv = $this->object->getInvitationMode() + 1;
		}
		$invitation->setValue($inv);
		$form->addItem($invitation);
		
		$form->addCommandButton("saveInvitationStatus", $this->lng->txt("save"));

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_invite.html", "Modules/Survey");
		$this->tpl->setVariable("INVITATION_TABLE", $form->getHTML());

		if ($this->object->getInvitation() && $this->object->getInvitationMode() == 1)
		{
			// search button
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$tb,
				array(
					'auto_complete_name'	=> $lng->txt('user'),
					'submit_name'			=> $lng->txt('svy_invite')
				)
			);

			$ilToolbar->addSpacer();

			$ilToolbar->addButton($this->lng->txt("svy_search_users"),
				$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI',''));

			$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());

			$invited_users = $this->object->getUserData($this->object->getInvitedUsers());
			include_once "./Modules/Survey/classes/tables/class.ilSurveyInvitedUsersTableGUI.php";
			$table_gui = new ilSurveyInvitedUsersTableGUI($this, 'invite');
			$table_gui->setData($invited_users);
			$this->tpl->setVariable('TBL_INVITED_USERS', $table_gui->getHTML());	
		}
	}

	/**
	* Creates a confirmation form for delete all user data
	*/
	public function deleteAllUserDataObject()
	{
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_maintenance.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "deleteAllUserData"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Deletes all user data of the survey after confirmation
	*/
	public function confirmDeleteAllUserDataObject()
	{
		$this->object->deleteAllUserData();
		ilUtil::sendSuccess($this->lng->txt("svy_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Cancels delete of all user data in maintenance
	*/
	public function cancelDeleteAllUserDataObject()
	{
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Deletes all user data for the test object
	*/
	public function confirmDeleteSelectedUserDataObject()
	{
		$this->object->removeSelectedSurveyResults($_POST["chbUser"]);
		ilUtil::sendSuccess($this->lng->txt("svy_selected_user_data_deleted"), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Cancels the deletion of all user data for the test object
	*/
	public function cancelDeleteSelectedUserDataObject()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, "maintenance");
	}
	
	/**
	* Asks for a confirmation to delete selected user data of the test object
	*/
	public function deleteSingleUserResultsObject()
	{
		$this->handleWriteAccess();

		if (count($_POST["chbUser"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
			$this->ctrl->redirect($this, "maintenance");
		}

		ilUtil::sendQuestion($this->lng->txt("confirm_delete_single_user_data"));
		include_once "./Modules/Survey/classes/tables/class.ilSurveyMaintenanceTableGUI.php";
		$table_gui = new ilSurveyMaintenanceTableGUI($this, 'maintenance', true);
		$total =& $this->object->getSurveyParticipants();
		$data = array();
		foreach ($total as $user_data)
		{
			if (in_array($user_data['active_id'], $_POST['chbUser']))
			{
				$last_access = $this->object->_getLastAccess($user_data["active_id"]);
				array_push($data, array(
					'id' => $user_data["active_id"],
					'name' => $user_data["sortname"],
					'login' => $user_data["login"],
					'last_access' => $last_access
				));
			}
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
	
	/**
	* Participants maintenance
	*/
	public function maintenanceObject()
	{
		$this->handleWriteAccess();

		if ($_GET["fill"] > 0) 
		{
			for ($i = 0; $i < $_GET["fill"]; $i++) $this->object->fillSurveyForUser();
		}
		include_once "./Modules/Survey/classes/tables/class.ilSurveyMaintenanceTableGUI.php";
		$table_gui = new ilSurveyMaintenanceTableGUI($this, 'maintenance');
		$total =& $this->object->getSurveyParticipants();
		$data = array();
		foreach ($total as $user_data)
		{
			$last_access = $this->object->_getLastAccess($user_data["active_id"]);
			array_push($data, array(
				'id' => $user_data["active_id"],
				'name' => $user_data["sortname"],
				'login' => $user_data["login"],
				'last_access' => $last_access
			));
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	protected function initCreateForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($a_new_type."_new"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		// using template?
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$templates = ilSettingsTemplate::getAllSettingsTemplates("svy");
		if($templates)
		{
			$this->tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
			// $this->tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");

			$options = array(""=>$this->lng->txt("none"));
			$js_data = array();
			foreach($templates as $item)
			{
				$options[$item["id"]] = $item["title"];

				$desc = str_replace("\n", "", nl2br(trim($item["description"])));
				$desc = str_replace("\r", "", $desc);

				$js_data[] = "jsInfo[".$item["id"]."] = \"".$desc."\"";
			}

			$tmpl = new ilSelectInputGUI($this->lng->txt("svy_settings_template"), "template");
			$tmpl->setOptions($options);
			$tmpl->addCustomAttribute("onChange=\"showInfo(this.value);\"");
			$form->addItem($tmpl);

			$js_data = implode("\n", $js_data);

$preview = <<<EOT
			<script>
			var jsInfo = {};
			$js_data
			function showInfo(id) {
				if(jsInfo[id] != undefined && jsInfo[id].length)
				{
					jQuery("#jsInfo").html(jsInfo[id]).css("display", "");
				}
				else
				{
					jQuery("#jsInfo").html("").css("display", "hidden");
				}
			}
			</script>
			<div id="jsInfo" style="display:none; margin: 5px;" class="small">xxx</div></td>
EOT;

			$tmpl->setInfo($preview);
		}

		$form->addCommandButton("save", $this->lng->txt($a_new_type."_add"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}

	protected function initImportForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("import"));

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
		$this->handleWriteAccess();

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


		$export_dir = $this->object->getExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		$this->handleWriteAccess();

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

	/**
	* Change survey language for direct access URL's
	*/
	public function setCodeLanguageObject()
	{
		if (strcmp($_POST["lang"], "-1") != 0)
		{
			global $ilUser;
			$ilUser->writePref("survey_code_language", $_POST["lang"]);
		}
		ilUtil::sendSuccess($this->lng->txt('language_changed'), true);
		$this->ctrl->redirect($this, 'codes');
	}
	
	/**
	* Display the survey access codes tab
	*/
	public function codesObject()
	{
		$this->handleWriteAccess();
		$this->setCodesSubtabs();
		global $ilUser, $ilToolbar;
		if ($this->object->getAnonymize() != 1 && !$this->object->isAccessibleWithCodeForAll())
		{
			return ilUtil::sendInfo($this->lng->txt("survey_codes_no_anonymization"));
		}

		$default_lang = $ilUser->getPref("survey_code_language");

		// creation buttons
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		$languages = $this->lng->getInstalledLanguages();
		$options = array();
		foreach ($languages as $lang)
		{
			$options[$lang] = $this->lng->txt("lang_$lang");
		}
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($this->lng->txt("survey_codes_lang"), "lang");
		$si->setOptions($options);
		$si->setValue($default_lang);
		$ilToolbar->addInputItem($si, true);
		$ilToolbar->addFormButton($this->lng->txt("set"), "setCodeLanguage");

		include_once "./Modules/Survey/classes/tables/class.ilSurveyCodesTableGUI.php";
		$table_gui = new ilSurveyCodesTableGUI($this, 'codes');
		$survey_codes =& $this->object->getSurveyCodesTableData($default_lang);
		$table_gui->setData($survey_codes);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_codes.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "codes"));
		$this->tpl->setVariable("TEXT_CREATE", $this->lng->txt("create"));
		$this->tpl->setVariable("TEXT_SURVEY_CODES", $this->lng->txt("new_survey_codes"));
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());	
	}
	
	/**
	* Delete a list of survey codes
	*/
	public function deleteCodesObject()
	{
		if (is_array($_POST["chb_code"]) && (count($_POST["chb_code"]) > 0))
		{
			foreach ($_POST["chb_code"] as $survey_code)
			{
				$this->object->deleteSurveyCode($survey_code);
			}
			ilUtil::sendSuccess($this->lng->txt('codes_deleted'), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
		}
		$this->ctrl->redirect($this, 'codes');
	}
	
	/**
	* Exports a list of survey codes
	*/
	public function exportCodesObject()
	{
		if (is_array($_POST["chb_code"]) && (count($_POST["chb_code"]) > 0))
		{
			$export = $this->object->getSurveyCodesForExport($_POST["chb_code"]);
			ilUtil::deliverData($export, ilUtil::getASCIIFilename($this->object->getTitle() . ".txt"));
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, 'codes');
		}
	}
	
	/**
	* Exports all survey codes
	*/
	public function exportAllCodesObject()
	{
		$export = $this->object->getSurveyCodesForExport(array());
		ilUtil::deliverData($export, ilUtil::getASCIIFilename($this->object->getTitle() . ".txt"));
	}
	
	/**
	* Create access codes for the survey
	*/
	public function createSurveyCodesObject()
	{
		if (preg_match("/\d+/", $_POST["nrOfCodes"]))
		{
			$this->object->createSurveyCodes($_POST["nrOfCodes"]);
			ilUtil::sendSuccess($this->lng->txt('codes_created'), true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("enter_valid_number_of_codes"), true);
		}
		$this->ctrl->redirect($this, 'codes');
	}
	
	/**
	* Sending access codes via email
	*/
	public function codesMailObject($checkonly = false)
	{
		global $ilAccess;
		
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		$savefields = (strcmp($this->ctrl->getCmd(), "saveMailTableFields") == 0) ? TRUE : FALSE;

		include_once "./Modules/Survey/classes/tables/class.ilSurveyCodesMailTableGUI.php";
		$data = $this->object->getExternalCodeRecipients();
		$table_gui = new ilSurveyCodesMailTableGUI($this, 'codesMail');
		$table_gui->setData($data);
		$table_gui->setTitle($this->lng->txt('externalRecipients'));
		$table_gui->completeColumns();
		$tabledata = $table_gui->getHTML();	
		
		if (!$checkonly)
		{
			$this->tpl->setVariable('ADM_CONTENT', $tabledata);	
		}
		return $errors;
	}
	
	public function insertSavedMessageObject()
	{
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		include_once("./Modules/Survey/classes/forms/FormMailCodesGUI.php");
		$form_gui = new FormMailCodesGUI($this);
		$form_gui->setValuesByPost();
		try
		{
			if ($form_gui->getSavedMessages()->getValue() > 0)
			{
				global $ilUser;
				$settings = $this->object->getUserSettings($ilUser->getId(), 'savemessage');
				$form_gui->getMailMessage()->setValue($settings[$form_gui->getSavedMessages()->getValue()]['value']);
				ilUtil::sendSuccess($this->lng->txt('msg_message_inserted'));
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('msg_no_message_inserted'));
			}
		}
		catch (Exception $e)
		{
			global $ilLog;
			$ilLog->write('Error: ' + $e->getMessage());
		}
		$this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
	}

	public function deleteSavedMessageObject()
	{
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		include_once("./Modules/Survey/classes/forms/FormMailCodesGUI.php");
		$form_gui = new FormMailCodesGUI($this);
		$form_gui->setValuesByPost();
		try
		{
			if ($form_gui->getSavedMessages()->getValue() > 0)
			{
				$this->object->deleteUserSettings($form_gui->getSavedMessages()->getValue());
				$form_gui = new FormMailCodesGUI($this);
				$form_gui->setValuesByPost();
				ilUtil::sendSuccess($this->lng->txt('msg_message_deleted'));
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('msg_no_message_deleted'));
			}
		}
		catch (Exception $e)
		{
			global $ilLog;
			$ilLog->write('Error: ' + $e->getMessage());
		}
		$this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
	}
	
	public function mailCodesObject()
	{
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		$mailData['m_subject'] = (array_key_exists('m_subject', $_POST)) ? $_POST['m_subject'] : sprintf($this->lng->txt('default_codes_mail_subject'), $this->object->getTitle());
		$mailData['m_message'] = (array_key_exists('m_message', $_POST)) ? $_POST['m_message'] : $this->lng->txt('default_codes_mail_message');
		$mailData['m_notsent'] = (array_key_exists('m_notsent', $_POST)) ? $_POST['m_notsent'] : '1';

		include_once("./Modules/Survey/classes/forms/FormMailCodesGUI.php");
		$form_gui = new FormMailCodesGUI($this);
		$form_gui->setValuesByArray($mailData);
		$this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
	}
	
	public function sendCodesMailObject()
	{
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		include_once("./Modules/Survey/classes/forms/FormMailCodesGUI.php");
		$form_gui = new FormMailCodesGUI($this);
		if ($form_gui->checkInput())
		{
			$code_exists = strpos($_POST['m_message'], '[code]') !== FALSE;
			if (!$code_exists)
			{
				if (!$code_exists) ilUtil::sendFailure($this->lng->txt('please_enter_mail_code'));
				$form_gui->setValuesByPost();
			}
			else
			{
				if ($_POST['savemessage'] == 1)
				{
					global $ilUser;
					$title = (strlen($_POST['savemessagetitle'])) ? $_POST['savemessagetitle'] : ilStr::substr($_POST['m_message'], 0, 40) . '...';
					$this->object->saveUserSettings($ilUser->getId(), 'savemessage', $title, $_POST['m_message']);
				}
				$this->object->sendCodes($_POST['m_notsent'], $_POST['m_subject'], $_POST['m_message']);
				ilUtil::sendSuccess($this->lng->txt('mail_sent'), true);
				$this->ctrl->redirect($this, 'codesMail');
			}
		}
		else
		{
			$form_gui->setValuesByPost();
		}
		$this->tpl->setVariable("ADM_CONTENT", $form_gui->getHTML());
	}
	
	public function cancelCodesMailObject()
	{
		$this->ctrl->redirect($this, 'codesMail');
	}

	public function deleteInternalMailRecipientObject()
	{
		if (!is_array($_POST['chb_ext']) || count(is_array($_POST['chb_ext'])) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("err_no_selection"), true);
			$this->ctrl->redirect($this, 'codesMail');
		}
		foreach ($_POST['chb_ext'] as $code)
		{
			$this->object->deleteSurveyCode($code);
		}
		ilUtil::sendSuccess($this->lng->txt('external_recipients_deleted'), true);
		$this->ctrl->redirect($this, 'codesMail');
	}
	
	public function importExternalRecipientsFromDatasetObject()
	{
		$hasErrors = $this->importExternalMailRecipientsObject(true, 2);
		if (!$hasErrors)
		{
			$data = array();
			$existingdata = $this->object->getExternalCodeRecipients();
			if (count($existingdata))
			{
				$first = array_shift($existingdata);
				foreach ($first as $key => $value)
				{
					if (strcmp($key, 'code') != 0 && strcmp($key, 'sent') != 0)
					{
						$data[$key] = $_POST[$key];
					}
				}
			}
			if (count($data))
			{
				$this->object->createSurveyCodesForExternalData(array($data));
				ilUtil::sendSuccess($this->lng->txt('external_recipients_imported'), true);
			}
			$this->ctrl->redirect($this, 'codesMail');
		}
	}

	public function importExternalRecipientsFromTextObject()
	{
		$hasErrors = $this->importExternalMailRecipientsObject(true, 1);
		if (!$hasErrors)
		{
			$data = preg_split("/[\n\r]/", $_POST['externaltext']);
			$fields = preg_split("/;/", array_shift($data));
			if (!in_array('email', $fields))
			{
				$_SESSION['externaltext'] = $_POST['externaltext'];
				ilUtil::sendFailure($this->lng->txt('err_external_rcp_no_email_column'), true);
				$this->ctrl->redirect($this, 'importExternalMailRecipients');
			}
			$existingdata = $this->object->getExternalCodeRecipients();
			$existingcolumns = array();
			if (count($existingdata))
			{
				$first = array_shift($existingdata);
				foreach ($first as $key => $value)
				{
					array_push($existingcolumns, $key);
				}
			}
			$founddata = array();
			foreach ($data as $datarow)
			{
				$row = preg_split("/;/", $datarow);
				if (count($row) == count($fields))
				{
					$dataset = array();
					foreach ($fields as $idx => $fieldname)
					{
						if (count($existingcolumns))
						{
							if (array_key_exists($idx, $existingcolumns))
							{
								$dataset[$fieldname] = $row[$idx];
							}
						}
						else
						{
							$dataset[$fieldname] = $row[$idx];
						}
					}
					if (strlen($dataset['email']))
					{
						array_push($founddata, $dataset);
					}
				}
			}
			$this->object->createSurveyCodesForExternalData($founddata);
			ilUtil::sendSuccess($this->lng->txt('external_recipients_imported'), true);
			$this->ctrl->redirect($this, 'codesMail');
		}
	}

	public function importExternalRecipientsFromFileObject()
	{
		$hasErrors = $this->importExternalMailRecipientsObject(true, 0);
		if (!$hasErrors)
		{
			include_once "./Services/Utilities/classes/class.ilCSVReader.php";
			$reader = new ilCSVReader();
			$reader->open($_FILES['externalmails']['tmp_name']);
			$data = $reader->getDataArrayFromCSVFile();
			$fields = array_shift($data);
			if (!in_array('email', $fields))
			{
				$reader->close();
				ilUtil::sendFailure($this->lng->txt('err_external_rcp_no_email'), true);
				$this->ctrl->redirect($this, 'codesMail');
			}
			$existingdata = $this->object->getExternalCodeRecipients();
			$existingcolumns = array();
			if (count($existingdata))
			{
				$first = array_shift($existingdata);
				foreach ($first as $key => $value)
				{
					array_push($existingcolumns, $key);
				}
			}
			$founddata = array();
			foreach ($data as $row)
			{
				if (count($row) == count($fields))
				{
					$dataset = array();
					foreach ($fields as $idx => $fieldname)
					{
						if (count($existingcolumns))
						{
							if (array_key_exists($idx, $existingcolumns))
							{
								$dataset[$fieldname] = $row[$idx];
							}
						}
						else
						{
							$dataset[$fieldname] = $row[$idx];
						}
					}
					if (strlen($dataset['email']))
					{
						array_push($founddata, $dataset);
					}
				}
			}
			$reader->close();
			$this->object->createSurveyCodesForExternalData($founddata);
			ilUtil::sendSuccess($this->lng->txt('external_recipients_imported'), true);
			$this->ctrl->redirect($this, 'codesMail');
		}
	}

	function importExternalMailRecipientsObject($checkonly = false, $formindex = -1)
	{
		global $ilAccess;
		
		$this->handleWriteAccess();
		$this->setCodesSubtabs();

		$savefields = (
			strcmp($this->ctrl->getCmd(), "importExternalRecipientsFromFile") == 0 || 
			strcmp($this->ctrl->getCmd(), "importExternalRecipientsFromText") == 0 ||
			strcmp($this->ctrl->getCmd(), "importExternalRecipientsFromDataset") == 0
		) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form_import_file = new ilPropertyFormGUI();
		$form_import_file->setFormAction($this->ctrl->getFormAction($this));
		$form_import_file->setTableWidth("100%");
		$form_import_file->setId("codes_import_file");

		$headerfile = new ilFormSectionHeaderGUI();
		$headerfile->setTitle($this->lng->txt("import_from_file"));
		$form_import_file->addItem($headerfile);
		
		$externalmails = new ilFileInputGUI($this->lng->txt("externalmails"), "externalmails");
		$externalmails->setInfo($this->lng->txt('externalmails_info'));
		$externalmails->setRequired(true);
		$form_import_file->addItem($externalmails);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_file->addCommandButton("importExternalRecipientsFromFile", $this->lng->txt("import"));
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_file->addCommandButton("codesMail", $this->lng->txt("cancel"));

		// import text

		$form_import_text = new ilPropertyFormGUI();
		$form_import_text->setFormAction($this->ctrl->getFormAction($this));
		$form_import_text->setTableWidth("100%");
		$form_import_text->setId("codes_import_text");

		$headertext = new ilFormSectionHeaderGUI();
		$headertext->setTitle($this->lng->txt("import_from_text"));
		$form_import_text->addItem($headertext);

		$inp = new ilTextAreaInputGUI($this->lng->txt('externaltext'), 'externaltext');
		if (array_key_exists('externaltext', $_SESSION) && strlen($_SESSION['externaltext']))
		{
			$inp->setValue($_SESSION['externaltext']);
		}
		else
		{
			$inp->setValue($this->lng->txt('mail_import_example1') . "\n" . $this->lng->txt('mail_import_example2') . "\n" . $this->lng->txt('mail_import_example3') . "\n");
		}
		$inp->setRequired(true);
		$inp->setCols(80);
		$inp->setRows(10);
		$inp->setInfo($this->lng->txt('externaltext_info'));
		$form_import_text->addItem($inp);
		unset($_SESSION['externaltext']);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_text->addCommandButton("importExternalRecipientsFromText", $this->lng->txt("import"));
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_text->addCommandButton("codesMail", $this->lng->txt("cancel"));

		// import dataset
		
		$form_import_dataset = new ilPropertyFormGUI();
		$form_import_dataset->setFormAction($this->ctrl->getFormAction($this));
		$form_import_dataset->setTableWidth("100%");
		$form_import_dataset->setId("codes_import_dataset");

		$headerfile = new ilFormSectionHeaderGUI();
		$headerfile->setTitle($this->lng->txt("import_from_dataset"));
		$form_import_dataset->addItem($headerfile);
		
		$existingdata = $this->object->getExternalCodeRecipients();
		$existingcolumns = array('email');
		if (count($existingdata))
		{
			$first = array_shift($existingdata);
			foreach ($first as $key => $value)
			{
				if (strcmp($key, 'email') != 0 && strcmp($key, 'code') != 0 && strcmp($key, 'sent') != 0)
				{
					array_push($existingcolumns, $key);
				}
			}
		}
		
		foreach ($existingcolumns as $column)
		{
			$inp = new ilTextInputGUI($column, $column);
			$inp->setSize(50);
			if (strcmp($column, 'email') == 0)
			{
				$inp->setRequired(true);
			}
			else
			{
				$inp->setRequired(false);
			}
			$form_import_dataset->addItem($inp);
		}
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_dataset->addCommandButton("importExternalRecipientsFromDataset", $this->lng->txt("import"));
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form_import_dataset->addCommandButton("codesMail", $this->lng->txt("cancel"));

		$errors = false;
		
		if ($savefields)
		{
			switch ($formindex)
			{
				case 0:
					$errors = !$form_import_file->checkInput();
					$form_import_file->setValuesByPost();
					if ($errors) $checkonly = false;
					break;
				case 1:
					$errors = !$form_import_text->checkInput();
					$form_import_text->setValuesByPost();
					if ($errors) $checkonly = false;
					break;
				case 2:
					$errors = !$form_import_dataset->checkInput();
					$form_import_dataset->setValuesByPost();
					if ($errors) $checkonly = false;
					break;
			}
		}

		if (!$checkonly) 
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_external_mail.html", "Modules/Survey");
			$this->tpl->setVariable("HEADLINE", $this->lng->txt("external_mails_import"));
			$this->tpl->setVariable("FORM1", $form_import_file->getHTML());
			$this->tpl->setVariable("FORM2", $form_import_text->getHTML());
			$this->tpl->setVariable("FORM3", $form_import_dataset->getHTML());
		}
		return $errors;
	}

	/**
	* Add a precondition for a survey question or question block
	*/
	public function constraintsAddObject()
	{
		if (strlen($_POST["v"]) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("msg_enter_value_for_valid_constraint"));
			return $this->constraintStep3Object();
		}
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$include_elements = $_SESSION["includeElements"];
		foreach ($include_elements as $elementCounter)
		{
			if (is_array($structure[$elementCounter]))
			{
				if (strlen($_GET["precondition"]))
				{
					$this->object->updateConstraint($_GET['precondition'], $_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
				}
				else
				{
					$constraint_id = $this->object->addConstraint($_POST["q"], $_POST["r"], $_POST["v"], $_POST['c']);
					foreach ($structure[$elementCounter] as $key => $question_id)
					{
						$this->object->addConstraintToQuestion($question_id, $constraint_id);
					}
				}
				if (count($structure[$elementCounter]) > 1)
				{
					$this->object->updateConjunctionForQuestions($structure[$elementCounter], $_POST['c']);
				}
			}
		}
		unset($_SESSION["includeElements"]);
		unset($_SESSION["constraintstructure"]);
		$this->ctrl->redirect($this, "constraints");
	}

	/**
	* Handles the first step of the precondition add action
	*/
	public function constraintStep1Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		$start = $_GET["start"];
		$option_questions = array();
		for ($i = 1; $i < $start; $i++)
		{
			if (is_array($structure[$i]))
			{
				foreach ($structure[$i] as $key => $question_id)
				{
					if ($survey_questions[$question_id]["usableForPrecondition"])
					{
						array_push($option_questions, array("question_id" => $survey_questions[$question_id]["question_id"], "title" => $survey_questions[$question_id]["title"], "type_tag" => $survey_questions[$question_id]["type_tag"]));
					}
				}
			}
		}
		if (count($option_questions) == 0)
		{
			unset($_SESSION["includeElements"]);
			unset($_SESSION["constraintstructure"]);
			ilUtil::sendInfo($this->lng->txt("constraints_no_nonessay_available"), true);
			$this->ctrl->redirect($this, "constraints");
		}
		$this->constraintForm(1, $_POST, $survey_questions, $option_questions);
	}
	
	/**
	* Handles the second step of the precondition add action
	*/
	public function constraintStep2Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
		$this->constraintForm(2, $_POST, $survey_questions, $option_questions);
	}
	
	/**
	* Handles the third step of the precondition add action
	*/
	public function constraintStep3Object()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$option_questions = array();
		if (strlen($_GET["precondition"]))
		{
			$pc = $this->object->getPrecondition($_GET["precondition"]);
			$postvalues = array(
				"c" => $pc["conjunction"],
				"q" => $pc["question_fi"],
				"r" => $pc["relation_id"],
				"v" => $pc["value"]
			);
			array_push($option_questions, array("question_id" => $pc["question_fi"], "title" => $survey_questions[$pc["question_fi"]]["title"], "type_tag" => $survey_questions[$pc["question_fi"]]["type_tag"]));
			$this->constraintForm(3, $postvalues, $survey_questions, $option_questions);
		}
		else
		{
			array_push($option_questions, array("question_id" => $_POST["q"], "title" => $survey_questions[$_POST["q"]]["title"], "type_tag" => $survey_questions[$_POST["q"]]["type_tag"]));
			$this->constraintForm(3, $_POST, $survey_questions, $option_questions);
		}
	}
	
	public function constraintForm($step, $postvalues, &$survey_questions, $questions = FALSE)
	{
		if (strlen($_GET["start"])) $this->ctrl->setParameter($this, "start", $_GET["start"]);
		$this->ctrl->saveParameter($this, "precondition");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("constraintsForm");

		$title = "";
		if ($survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["questionblock_id"] > 0)
		{
			$title = $this->lng->txt("questionblock") . ": " . $survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["questionblock_title"];
		}
		else
		{
			$title = $this->lng->txt($survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["type_tag"]) . ": " . $survey_questions[$_SESSION["constraintstructure"][$_GET["start"]][0]]["title"];
		}
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($title);
		$form->addItem($header);
		
		$fulfilled = new ilRadioGroupInputGUI($this->lng->txt("constraint_fulfilled"), "c");
		$fulfilled->addOption(new ilRadioOption($this->lng->txt("conjunction_and"), '0', ''));
		$fulfilled->addOption(new ilRadioOption($this->lng->txt("conjunction_or"), '1', ''));
		$fulfilled->setValue((strlen($postvalues['c'])) ? $postvalues['c'] : 0);
		$form->addItem($fulfilled);

		$step1 = new ilSelectInputGUI($this->lng->txt("step") . " 1: " . $this->lng->txt("select_prior_question"), "q");
		$options = array();
		if (is_array($questions))
		{
			foreach ($questions as $question)
			{
				$options[$question["question_id"]] = $question["title"] . " (" . SurveyQuestion::_getQuestionTypeName($question["type_tag"]) . ")";
			}
		}
		$step1->setOptions($options);
		$step1->setValue($postvalues["q"]);
		$form->addItem($step1);

		if ($step > 1)
		{
			$relations = $this->object->getAllRelations();
			$step2 = new ilSelectInputGUI($this->lng->txt("step") . " 2: " . $this->lng->txt("select_relation"), "r");
			$options = array();
			foreach ($relations as $rel_id => $relation)
			{
				if (in_array($relation["short"], $survey_questions[$postvalues["q"]]["availableRelations"]))
				{
					$options[$rel_id] = $relation['short'];
				}
			}
			$step2->setOptions($options);
			$step2->setValue($postvalues["r"]);
			$form->addItem($step2);
		}
		
		if ($step > 2)
		{
			$variables =& $this->object->getVariables($postvalues["q"]);
			$question_type = $survey_questions[$postvalues["q"]]["type_tag"];
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			SurveyQuestion::_includeClass($question_type);
			$question = new $question_type();
			$question->loadFromDb($postvalues["q"]);

			$step3 = $question->getPreconditionSelectValue($postvalues["v"], $this->lng->txt("step") . " 3: " . $this->lng->txt("select_value"), "v");
			$form->addItem($step3);
		}

		switch ($step)
		{
			case 1:
				$cmd_continue = "constraintStep2";
				$cmd_back = "constraints";
				break;
			case 2:
				$cmd_continue = "constraintStep3";
				$cmd_back = "constraintStep1";
				break;
			case 3:
				$cmd_continue = "constraintsAdd";
				$cmd_back = "constraintStep2";
				break;
		}
		$form->addCommandButton($cmd_back, $this->lng->txt("back"));
		$form->addCommandButton($cmd_continue, $this->lng->txt("continue"));

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	* Delete constraints of a survey
	*/
	public function deleteConstraintsObject()
	{
		$survey_questions =& $this->object->getSurveyQuestions();
		$structure =& $_SESSION["constraintstructure"];
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^constraint_(\d+)_(\d+)/", $key, $matches)) 
			{
				$this->object->deleteConstraint($matches[2]);
			}
		}

		$this->ctrl->redirect($this, "constraints");
	}
	
	function createConstraintsObject()
	{
		$include_elements = $_POST["includeElements"];
		if ((!is_array($include_elements)) || (count($include_elements) == 0))
		{
			ilUtil::sendInfo($this->lng->txt("constraints_no_questions_or_questionblocks_selected"), true);
			$this->ctrl->redirect($this, "constraints");
		}
		else if (count($include_elements) >= 1)
		{
			$_SESSION["includeElements"] = $include_elements;
			sort($include_elements, SORT_NUMERIC);
			$_GET["start"] = $include_elements[0];
			$this->constraintStep1Object();
		}
	}
	
	function editPreconditionObject()
	{
		$_SESSION["includeElements"] = array($_GET["start"]);
		$this->ctrl->setParameter($this, "precondition", $_GET["precondition"]);
		$this->ctrl->setParameter($this, "start", $_GET["start"]);
		$this->ctrl->redirect($this, "constraintStep3");
	}
	
	/**
	* Administration page for survey constraints
	*/
	public function constraintsObject()
	{
		$this->handleWriteAccess();

		global $rbacsystem;
		
		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		$step = 0;
		if (array_key_exists("step", $_GET))	$step = $_GET["step"];
		switch ($step)
		{
			case 1:
				$this->constraintStep1Object();
				return;
				break;
			case 2:
				return;
				break;
			case 3:
				return;
				break;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_constraints_list.html", "Modules/Survey");
		$survey_questions =& $this->object->getSurveyQuestions();
		$last_questionblock_id = 0;
		$counter = 1;
		$hasPreconditions = FALSE;
		$structure = array();
		$colors = array("tblrow1", "tblrow2");
		foreach ($survey_questions as $question_id => $data)
		{
			$title = $data["title"];
			$show = true;
			if ($data["questionblock_id"] > 0)
			{
				$title = $data["questionblock_title"];
				$type = $this->lng->txt("questionblock");
				if ($data["questionblock_id"] != $last_questionblock_id) 
				{
					$last_questionblock_id = $data["questionblock_id"];
					$structure[$counter] = array();
					array_push($structure[$counter], $data["question_id"]);
				}
				else
				{
					array_push($structure[$counter-1], $data["question_id"]);
					$show = false;
				}
			}
			else
			{
				$structure[$counter] = array($data["question_id"]);
				$type = $this->lng->txt("question");
			}
			if ($show)
			{
				if ($counter == 1)
				{
					$this->tpl->setCurrentBlock("description");
					$this->tpl->setVariable("DESCRIPTION", $this->lng->txt("constraints_first_question_description"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$constraints =& $this->object->getConstraints($data["question_id"]);
					$rowcount = 0;
					if (count($constraints))
					{
						$hasPreconditions = TRUE;
						foreach ($constraints as $constraint)
						{
							$this->tpl->setCurrentBlock("constraint");
							$this->tpl->setVariable("SEQUENCE_ID", $counter);
							$this->tpl->setVariable("CONSTRAINT_ID", $constraint["id"]);
							$this->tpl->setVariable("CONSTRAINT_TEXT", $survey_questions[$constraint["question"]]["title"] . " " . $constraint["short"] . " " . $constraint["valueoutput"]);
							$this->tpl->setVariable("TEXT_EDIT_PRECONDITION", $this->lng->txt("edit"));
							$this->ctrl->setParameter($this, "precondition", $constraint["id"]);
							$this->ctrl->setParameter($this, "start", $counter);
							$this->tpl->setVariable("EDIT_PRECONDITION", $this->ctrl->getLinkTarget($this, "editPrecondition"));
							$this->ctrl->setParameter($this, "precondition", "");
							$this->ctrl->setParameter($this, "start", "");
							$this->tpl->parseCurrentBlock();
						}
						if (count($constraints) > 1)
						{
							$this->tpl->setCurrentBlock("conjunction");
							$this->tpl->setVariable("TEXT_CONJUNCTION", ($constraints[0]['conjunction']) ? $this->lng->txt('conjunction_or_title') : $this->lng->txt('conjunction_and_title'));
							$this->tpl->parseCurrentBlock();
						}
					}
				}
				if ($counter != 1)
				{
					$this->tpl->setCurrentBlock("include_elements");
					$this->tpl->setVariable("QUESTION_NR", "$counter");
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("constraint_section");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("QUESTION_NR", "$counter");
				$this->tpl->setVariable("TITLE", "$title");
				$icontype = "question.gif";
				if ($data["questionblock_id"] > 0)
				{
					$icontype = "questionblock.gif";
				}
				$this->tpl->setVariable("TYPE", "$type: ");
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ICON_HREF", ilUtil::getImagePath($icontype, "Modules/Survey"));
				$this->tpl->setVariable("ICON_ALT", $type);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		if ($rbacsystem->checkAccess("write", $this->ref_id) and !$hasDatasets)
		{
			if ($hasPreconditions)
			{
				$this->tpl->setCurrentBlock("selectall_preconditions");
				$this->tpl->setVariable("SELECT_ALL_PRECONDITIONS", $this->lng->txt("select_all"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("selectall");
			$counter++;
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();

			if ($hasPreconditions)
			{
				$this->tpl->setCurrentBlock("delete_button");
				$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("buttons");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->setVariable("BTN_CREATE_CONSTRAINTS", $this->lng->txt("constraint_add"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CONSTRAINTS_INTRODUCTION", $this->lng->txt("constraints_introduction"));
		$this->tpl->setVariable("DEFINED_PRECONDITIONS", $this->lng->txt("existing_constraints"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "constraints"));
		$this->tpl->setVariable("CONSTRAINTS_HEADER", $this->lng->txt("constraints_list_of_entities"));
		$this->tpl->parseCurrentBlock();
		$_SESSION["constraintstructure"] = $structure;
		if ($hasDatasets)
		{
			// ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning"));
			$link = $this->ctrl->getLinkTarget($this, "maintenance");
			$link = "<a href=\"".$link."\">".$this->lng->txt("survey_has_datasets_warning_page_view_link")."</a>";
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning_page_view")." ".$link);
		}
	}

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
	
	function setNewTemplate()
	{
		global $tpl;
		$tpl = new ilTemplate("tpl.il_svy_svy_main.html", TRUE, TRUE, "Modules/Survey");
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
		$tpl->setVariable("LOCATION_JAVASCRIPT",dirname($location_stylesheet));
	}
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;
		global $ilUser;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		include_once "./Modules/Survey/classes/class.ilSurveyExecutionGUI.php";
		$output_gui =& new ilSurveyExecutionGUI($this->object);
		$info->setFormAction($this->ctrl->getFormAction($output_gui, "infoScreen"));
		$info->enablePrivateNotes();
		$anonymize_key = NULL;
		if ($this->object->getAnonymize() == 1)
		{
			if ($_SESSION["anonymous_id"][$this->object->getId()])
			{
				$anonymize_key = $_SESSION["anonymous_id"][$this->object->getId()];
			}
			else if ($_POST["anonymous_id"])
			{
				$anonymize_key = $_POST["anonymous_id"];
			}
		}
		$canStart = $this->object->canStartSurvey($anonymize_key);
		$showButtons = $canStart["result"];
		if (!$showButtons)
		{
			if($canStart["edit_settings"] &&
				$ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$canStart["messages"][] = "<a href=\"".$this->ctrl->getLinkTarget($this, "properties")."\">".
					$this->lng->txt("survey_edit_settings")."</a>";
			}
			ilUtil::sendInfo(implode("<br />", $canStart["messages"]));
		}

		$big_button = false;
		if ($showButtons)
		{
			// output of start/resume buttons for personalized surveys
			if (!$this->object->getAnonymize())
			{
				$survey_started = $this->object->isSurveyStarted($ilUser->getId(), "");
				// Anonymous User tries to start a personalized survey
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					ilUtil::sendInfo($this->lng->txt("anonymous_with_personalized_survey"));
				}
				else
				{
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
			}
			// output of start/resume buttons for anonymized surveys
			else if ($this->object->getAnonymize() && !$this->object->isAccessibleWithoutCode())
			{
				if (($_SESSION["AccountId"] == ANONYMOUS_USER_ID || $this->object->isAccessibleWithCodeForAll()) && (strlen($_POST["anonymous_id"]) == 0) && (strlen($_SESSION["anonymous_id"][$this->object->getId()]) == 0))
				{
					$info->setFormAction($this->ctrl->getFormAction($this, "infoScreen"));
					$info->addSection($this->lng->txt("anonymization"));
					$info->addProperty("", $this->lng->txt("anonymize_anonymous_introduction"));
					$info->addPropertyTextinput($this->lng->txt("enter_anonymous_id"), "anonymous_id", "", 8, "infoScreen", $this->lng->txt("submit"));
				}
				else
				{
					if (strlen($_POST["anonymous_id"]) > 0)
					{
						if (!$this->object->checkSurveyCode($_POST["anonymous_id"]))
						{
							ilUtil::sendInfo($this->lng->txt("wrong_survey_code_used"));
						}
						else
						{
							$anonymize_key = $_POST["anonymous_id"];
						}
					}
					else if (strlen($_SESSION["anonymous_id"][$this->object->getId()]) > 0)
					{
						if (!$this->object->checkSurveyCode($_SESSION["anonymous_id"][$this->object->getId()]))
						{
							ilUtil::sendInfo($this->lng->txt("wrong_survey_code_used"));
						}
						else
						{
							$anonymize_key = $_SESSION["anonymous_id"][$this->object->getId()];
						}
					}
					else
					{
						// registered users do not need to know that there is an anonymous key. The data is anonymized automatically
						$anonymize_key = $this->object->getUserAccessCode($ilUser->getId());
						if (!strlen($anonymize_key))
						{
							$anonymize_key = $this->object->createNewAccessCode();
							$this->object->saveUserAccessCode($ilUser->getId(), $anonymize_key);
						}
					}
					$info->addHiddenElement("anonymous_id", $anonymize_key);
					$survey_started = $this->object->isSurveyStarted($ilUser->getId(), $anonymize_key);
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
			}
			else
			{
				// free access
				$survey_started = $this->object->isSurveyStarted($ilUser->getId(), "");
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
		}

		if($big_button)
		{
			$big_button = '<div class="il_ButtonGroup" style="margin:25px; text-align:center; font-size:25px;">'.
				'<input type="submit" class="submit" name="cmd['.$big_button[0].']" value="'.
				$big_button[1].'" style="padding:10px;" /></div>';
		}
		
		if (strlen($this->object->getIntroduction()))
		{
			$introduction = $this->object->getIntroduction();
			$info->addSection($this->lng->txt("introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($introduction).
					$big_button."<br />".$info->getHiddenToggleButton());
		}
		else
		{
			$info->addSection("");
			$info->addProperty("", $big_button.$info->getHiddenToggleButton());
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
			case ANONYMIZE_OFF:
				$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("anonymize_personalized"));
				break;
			case ANONYMIZE_ON:
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("info_anonymize_with_code"));
				}
				else
				{
					$info->addProperty($this->lng->txt("anonymization"), $this->lng->txt("info_anonymize_registered_user"));
				}
				break;
			case ANONYMIZE_FREEACCESS:
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

	/**
	* Creates a print view of the survey questions
	*
	* @access public
	*/
	function printViewObject()
	{
		global $ilias;
		
		$this->questionsSubtabs("printview");
		$template = new ilTemplate("tpl.il_svy_svy_printview.html", TRUE, TRUE, "Modules/Survey");
			
		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(ilRPCServerSettings::getInstance()->isEnabled())
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "printView"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		$pages =& $this->object->getSurveyPages();
		foreach ($pages as $page)
		{
			if (count($page) > 0)
			{
				foreach ($page as $question)
				{
					$questionGUI = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
					if (is_object($questionGUI))
					{
						if (strlen($question["heading"]))
						{
							$template->setCurrentBlock("textblock");
							$template->setVariable("TEXTBLOCK", $question["heading"]);
							$template->parseCurrentBlock();
						}
						$template->setCurrentBlock("question");
						$template->setVariable("QUESTION_DATA", $questionGUI->getPrintView($this->object->getShowQuestionTitles(), $question["questionblock_show_questiontext"], $this->object->getSurveyId()));
						$template->parseCurrentBlock();
					}
				}
				if (count($page) > 1)
				{
					$template->setCurrentBlock("page");
					$template->setVariable("BLOCKTITLE", $page[0]["questionblock_title"]);
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("page");
					$template->parseCurrentBlock();
				}
			}
		}
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", TRUE, TRUE, "Modules/Test");
			$printbody->setVariable("TITLE", sprintf($this->lng->txt("tst_result_user_name"), $uname));
			$printbody->setVariable("ADM_CONTENT", $template->get());
			$printoutput = $printbody->get();
			$printoutput = preg_replace("/href=\".*?\"/", "", $printoutput);
			$fo = $this->object->processPrintoutput2FO($printoutput);
			$this->object->deliverPDFfromFO($fo);
		}
		else
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
		}
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
				break;
		}
	}
	
	/**
	* Set the subtabs for the questions tab
	*
	* Set the subtabs for the questions tab
	*
	* @access private
	*/
	function questionsSubtabs($a_cmd)
	{
		$questions_per_page = ($a_cmd == 'questions_per_page') ? true : false;
		$questions = ($a_cmd == 'questions') ? true : false;
		$printview = ($a_cmd == 'printview') ? true : false;

		$hidden_tabs = array();
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);
			$hidden_tabs = $template->getHiddenTabs();
		}

		$this->tabs_gui->addSubTabTarget("survey_per_page_view", $this->ctrl->getLinkTargetByClass("ilsurveypagegui", "renderPage"),
			 "", "", "", $questions_per_page);

		if(!in_array("survey_question_editor", $hidden_tabs))
		{
			$this->ctrl->setParameter($this, "pgov", "");
			$this->tabs_gui->addSubTabTarget("survey_question_editor", $this->ctrl->getLinkTarget($this, "questions"),
											 "", "", "", $questions);
			$this->ctrl->setParameter($this, "pgov", $_REQUEST["pgov"]);
		}
		
		$this->tabs_gui->addSubTabTarget("print_view", $this->ctrl->getLinkTarget($this, "printView"),
											"", "", "", $printview);

		if($this->object->getSurveyPages())
		{
			if($questions_per_page)
			{
				$this->ctrl->setParameterByClass("ilsurveyexecutiongui", "pgov", max(1, $_REQUEST["pg"]));
			}
			$this->ctrl->setParameterByClass("ilsurveyexecutiongui", "prvw", 1);
			$this->tabs_gui->addSubTabTarget("preview", $this->ctrl->getLinkTargetByClass("ilsurveyexecutiongui", "preview"),
												"", "", "", false);
		}
	}
	
	/**
	* Set the tabs for the access codes section
	*
	* @access private
	*/
	function setCodesSubtabs()
	{
		global $ilTabs;
		global $ilAccess;

		$ilTabs->addSubTabTarget
		(
			"codes", 
			$this->ctrl->getLinkTarget($this,'codes'),
			array("codes", "createSurveyCodes", "setCodeLanguage", "deleteCodes", "exportCodes"),
			""
		);

		$ilTabs->addSubTabTarget
		(
			"participating_users", 
			$this->ctrl->getLinkTarget($this, "codesMail"), 
			array("codesMail", "saveMailTableFields", "importExternalMailRecipients", 
			'importExternalRecipientsFromFile', 'importExternalRecipientsFromText',
			'importExternalRecipientsFromDataset'),	
			""
		);

		$data = $this->object->getExternalCodeRecipients();
		if (count($data))
		{
			$ilTabs->addSubTabTarget
			(
				"mail_survey_codes", 
				$this->ctrl->getLinkTarget($this, "mailCodes"), 
				array("mailCodes", "sendCodesMail", "insertSavedMessage", "deleteSavedMessage"),	
				""
			);
		}
	}

	/**
	* Set the tabs for the evaluation output
	*
	* @access private
	*/
	function setEvalSubtabs()
	{
		global $ilTabs;
		global $ilAccess;

		$ilTabs->addSubTabTarget(
			"svy_eval_cumulated", 
			$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"), 
			array("evaluation", "checkEvaluationAccess"),	
			""
		);

		$ilTabs->addSubTabTarget(
			"svy_eval_detail", 
			$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluationdetails"), 
			array("evaluationdetails"),	
			""
		);
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addSubTabTarget(
				"svy_eval_user", 
				$this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluationuser"), 
				array("evaluationuser"),	
				""
			);
		}
	}

	function setBrowseForQuestionsSubtabs()
	{
		global $ilAccess;
		global $ilTabs;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $this->ctrl->getLinkTarget($this, "questions"));
			$ilTabs->addTarget("browse_for_questions",
				$this->ctrl->getLinkTarget($this, "browseForQuestions"),
				 array("browseForQuestions", "browseForQuestionblocks"),
				"", ""
			);
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser;

		if (strcmp($this->ctrl->getNextClass(), 'ilrepositorysearchgui') != 0)
		{
			switch ($this->ctrl->getCmd())
			{
				case "browseForQuestions":
				case "browseForQuestionblocks":
				case "insertQuestions":
				case "filterQuestions":
				case "resetFilterQuestions":
				case "changeDatatype":
				case "start":
				case "resume":
				case "next":
				case "previous":
				case "redirectQuestion":
				case "preview":
					return;
		
				case "evaluation":
				case "checkEvaluationAccess":
				case "evaluationdetails":
				case "evaluationuser":
					$this->setEvalSubtabs();
					break;
			}
		}

		$hidden_tabs = array();
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);
			$hidden_tabs = $template->getHiddenTabs();
		}
		
		// questions
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
				? true
				: false;

			$cmd = $this->ctrl->getLinkTargetByClass("ilsurveypagegui", "renderPage");
			// $cmd = $this->ctrl->getLinkTarget($this, "questions");
	
			$tabs_gui->addTarget("survey_questions",
				 $cmd,
				 array("questions", "browseForQuestions", "createQuestion",
				 "filterQuestions", "resetFilterQuestions", "changeDatatype", "insertQuestions",
				 "removeQuestions", "cancelRemoveQuestions", "confirmRemoveQuestions",
				 "defineQuestionblock", "saveDefineQuestionblock", "cancelDefineQuestionblock",
				 "unfoldQuestionblock", "moveQuestions",
				 "insertQuestionsBefore", "insertQuestionsAfter", "saveObligatory",
				 "addHeading", "saveHeading", "cancelHeading", "editHeading",
				 "confirmRemoveHeading", "cancelRemoveHeading", "printView", "renderPage",
				 "addQuestionToolbarForm", "deleteBlock", "movePageForm", "copyQuestionsToPool"),
				 "", "", $force_active);
		}
		
		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTarget($this,'infoScreen'),
				 array("infoScreen", "showSummary"));
		}
			
		// properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$force_active = ($this->ctrl->getCmd() == "")
				? true
				: false;
			$tabs_gui->addTarget("settings",
				 $this->ctrl->getLinkTarget($this,'properties'),
				 array("properties", "save", "cancel", 'saveProperties'), "",
				 "", $force_active);
		}

		// questions
		if ($ilAccess->checkAccess("write", "", $this->ref_id) &&
			!in_array("constraints", $hidden_tabs))
		{
			// constraints
			$tabs_gui->addTarget("constraints",
				 $this->ctrl->getLinkTarget($this, "constraints"),
				 array("constraints", "constraintStep1", "constraintStep2",
				 "constraintStep3", "constraintsAdd", "createConstraints",
				"editPrecondition"),
				 "");
		}

		if (($ilAccess->checkAccess("write", "", $this->ref_id) || $ilAccess->checkAccess("invite", "", $this->ref_id)) &&
			!in_array("invitation", $hidden_tabs))
		{
			// invite
			$tabs_gui->addTarget("invitation",
				 $this->ctrl->getLinkTarget($this, 'invite'),
				 array("invite", "saveInvitationStatus",
				 "inviteUserGroup", "disinviteUserGroup"),
				 "");
		}
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// maintenance
			$tabs_gui->addTarget("maintenance",
				 $this->ctrl->getLinkTarget($this,'maintenance'),
				 array("maintenance", "deleteAllUserData"),
				 "");

			if ($this->object->getAnonymize() == 1 || $this->object->isAccessibleWithCodeForAll())
			{
				// code
				$tabs_gui->addTarget("codes",
					 $this->ctrl->getLinkTarget($this,'codes'),
					 array("codes", "exportCodes", 'codesMail', 'saveMailTableFields', 'importExternalMailRecipients',
						'mailCodes', 'sendCodesMail', 'importExternalRecipientsFromFile', 'importExternalRecipientsFromText',
						'importExternalRecipientsFromDataset', 'insertSavedMessage', 'deleteSavedMessage'),
					 "");
			}
		}
			
		include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if ($ilAccess->checkAccess("write", "", $this->ref_id) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
		{
			// evaluation
			$tabs_gui->addTarget("svy_evaluation",
				 $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "evaluation"),
				 array("evaluation", "checkEvaluationAccess", "evaluationdetails",
				 	"evaluationuser"),
				 "");
		}

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			if(!in_array("meta_data", $hidden_tabs))
			{
				// meta data
				$tabs_gui->addTarget("meta_data",
					 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
					 "", "ilmdeditorgui");
			}

			if(!in_array("export", $hidden_tabs))
			{
				// export
				$tabs_gui->addTarget("export",
					 $this->ctrl->getLinkTarget($this,'export'),
					 array("export", "createExportFile", "confirmDeleteExportFile",
					 "downloadExportFile"),
					 ""
					);
			}
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			// permissions
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
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
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			if (strlen($a_access_code))
			{
				$_SESSION["anonymous_id"][$this->object->getId()] = $a_access_code;
				$_GET["baseClass"] = "ilObjSurveyGUI";
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("ilias.php");
				exit;
			}
			else
			{
				$_GET["baseClass"] = "ilObjSurveyGUI";
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("ilias.php");
				exit;
			}
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
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


	/**
	* Copy questions to pool (form)
	*/
	public function copyQuestionsToPoolObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, false, true);

		// gather questions from blocks
		$copy_questions = $items["questions"];
		foreach ($items["blocks"] as $block_id)
		{
			foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid)
			{
				array_push($copy_questions, $qid);
			}
		}
		$copy_questions = array_unique($copy_questions);

		// only if not already in pool
		if (count($copy_questions))
		{
			foreach($copy_questions as $idx => $question_id)
			{
				$question = ilObjSurvey::_instanciateQuestion($question_id);
				if($question->getOriginalId())
				{
					unset($copy_questions[$idx]);
				}
			}

		}
		if (count($copy_questions) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_copy_to_pool"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$this->questionsSubtabs("questions");

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$form = new ilPropertyFormGUI();

			$form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));

			$ids = new ilHiddenInputGUI("question_ids");
			$ids->setValue(implode(";", $copy_questions));
			$form->addItem($ids);

			$questionpools =& $this->object->getAvailableQuestionpools(false, false, true, "write");
			$pools = new ilSelectInputGUI($this->lng->txt("survey_copy_select_questionpool"), "sel_spl");
			$pools->setOptions($questionpools);
			$form->addItem($pools);

			$form->addCommandButton("executeCopyQuestionsToPool", $this->lng->txt("submit"));
			$form->addCommandButton("questions", $this->lng->txt("cancel"));

			return $this->tpl->setContent($form->getHTML());
		}
	}

	/**
	* Copy questions to pool (action)
	*/
	public function executeCopyQuestionsToPoolObject()
	{
		$question_ids = explode(";", $_POST["question_ids"]);
		$pool_id = ilObject::_lookupObjId($_POST["sel_spl"]);

		foreach($question_ids as $qid)
		{
			// create copy (== pool "original")
			$new_question = ilObjSurvey::_instanciateQuestion($qid);
			$new_question->setId();
			$new_question->setObjId($pool_id);
			$new_question->saveToDb();

			// link "source" (survey) to copy (pool)
			SurveyQuestion::_changeOriginalId($qid, $new_question->getId(), $pool_id);
		}

		ilUtil::sendSuccess($this->lng->txt("survey_copy_to_questionpool_success"), true);
		$this->ctrl->redirect($this, "questions");
	}

} // END class.ilObjSurveyGUI
?>