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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version  $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package survey
*/

require_once "./classes/class.ilObjectGUI.php";
require_once "./classes/class.ilMetaDataGUI.php";
require_once "./classes/class.ilUtil.php";
require_once "./classes/class.ilSearch.php";
require_once "./classes/class.ilObjUser.php";
require_once "./classes/class.ilObjGroup.php";
require_once "./survey/classes/class.SurveySearch.php";

define ("TYPE_XLS", "excel");
define ("TYPE_SPSS", "csv");
define ("TYPE_PRINT", "prnt");


class ilObjSurveyGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
    global $lng;
		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		if (!defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("adm_object.php");
		}
		else
		{
			$this->setTabTargetScript("survey.php");
		}
		if ($a_prepare_output) {
			$this->prepareOutput();
		}
	}

	/**
	* Returns the calling script of the GUI class
	*
	* @access	public
	*/
	function getCallingScript()
	{
		$module = $this->object->getModule($this->type);
		$module_dir = ($module == "")
			? ""
			: $module."/";
		return $module . "survey.php";
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);
		
		$returnlocation = "survey.php";
		if (!defined("ILIAS_MODULE"))
		{
			$returnlocation = "adm_object.php";
		}
		header("Location:".$this->getReturnLocation("save","$returnlocation?".$this->link_params));
		exit();
	}

	function updateObject() {
		$this->object->updateTitleAndDescription();
		$this->update = $this->object->update();
		$this->object->saveToDb();
		if (strcmp($_SESSION["info"], "") != 0)
		{
			sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), true);
		}
		else
		{
			sendInfo($this->lng->txt("msg_obj_modified"), true);
		}
		ilUtil::redirect($this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]);
	}

/**
* Returns the GET parameters for the survey object URLs
*
* Returns the GET parameters for the survey object URLs
*
* @access public
*/
  function getAddParameter()
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }

	function writePropertiesFormData()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$result = $this->object->setStatus($_POST["status"]);
		if ($result)
		{
			sendInfo($result, true);
		}
		$this->object->setEvaluationAccess($_POST["evaluation_access"]);
		$this->object->setStartDate(sprintf("%04d-%02d-%02d", $_POST["start_date"]["y"], $_POST["start_date"]["m"], $_POST["start_date"]["d"]));
		$this->object->setStartDateEnabled($_POST["checked_start_date"]);
		$this->object->setEndDate(sprintf("%04d-%02d-%02d", $_POST["end_date"]["y"], $_POST["end_date"]["m"], $_POST["end_date"]["d"]));
		$this->object->setEndDateEnabled($_POST["checked_end_date"]);
		$this->object->setIntroduction(ilUtil::stripSlashes($_POST["introduction"]));
		$this->object->setAnonymize($_POST["anonymize"]);
		if ($_POST["showQuestionTitles"])
		{
			$this->object->showQuestionTitles();
		}
		else
		{
			$this->object->hideQuestionTitles();
		}
	}

/**
* Creates an input form to enter the anonymous survey id to resume a survey
*
* Creates an input form to enter the anonymous survey id to resume a survey
*
* @access public
*/
	function resumeSurveyForm()
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_resume_survey.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("LABEL_RESUME_SURVEY", $this->lng->txt("label_resume_survey"));
		$this->tpl->setVariable("TEXT_RESUME_SURVEY", $this->lng->txt("text_resume_survey"));
		$this->tpl->setVariable("TITLE_RESUME_SURVEY", $this->lng->txt("title_resume_survey"));
		$this->tpl->setVariable("BUTTON_RESUME", $this->lng->txt("resume_survey"));
		$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates the form output for running the survey
*
* Creates the form output for running the survey
*
* @access public
*/
	function runObject() {
		global $ilUser;

		if ($_POST["cmd"]["exit"])
		{
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel",ILIAS_HTTP_PATH."/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}

    $add_parameter = $this->getAddParameter();

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_svy_svy_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();

		$this->setLocator();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		if ($_POST["cmd"]["resume"])
		{
			$anonymize_key = $this->object->getAnonymousId($_POST["anonymous_id"]);
			if ($anonymize_key)
			{
				$_SESSION["anonymous_id"] = $anonymize_key;
			}
			else
			{
				unset($_POST["cmd"]["resume"]);
				sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]));
			}
		}

		if ($_POST["cmd"]["resume_check"])
		{
			if ($this->object->getAnonymize())
			{
				$this->resumeSurveyForm();
				return;
			}
			else
			{
				$_POST["cmd"]["resume"] = "resume";
			}
		}

		$direction = 0;
		$page_error = 0;
		$error_messages = array();
		if ($_POST["cmd"]["start"] or $_POST["cmd"]["previous"] or $_POST["cmd"]["next"] or $_POST["cmd"]["resume"])
		{
			if ($_POST["cmd"]["start"])
			{
				$anonymize_key = md5($ilUser->getLogin() . strftime('%c'));
				$_SESSION["anonymous_id"] = $anonymize_key;
			}
			$activepage = "";
			$direction = 0;
			if ($_POST["cmd"]["resume"])
			{
				$activepage = $this->object->getLastActivePage($ilUser->id);
				$direction = 0;
			}
			if ($_POST["cmd"]["previous"] or $_POST["cmd"]["next"])
			{
				// check users input when it is a metric question
				$page = $this->object->getNextPage($_GET["qid"], 0);
				foreach ($page as $data)
				{
					$save_answer = 0;
					$error = 0;
					if (strcmp($data["type_tag"], "qt_metric") == 0)
					{
						// there is a metric question -> check input
						$variables =& $this->object->getVariables($data["question_id"]);
						$entered_value = $_POST[$data["question_id"] . "_metric_question"];
						// replace german notation with international notation
						$entered_value = str_replace(",", ".", $entered_value);
						$_POST[$data["question_id"] . "_metric_question"] = $entered_value;
						if (((($entered_value < $variables[0]->value1) or (($entered_value > $variables[0]->value2) and ($variables[0]->value2 > 0)))) && $data["obligatory"])
						{
							// there is an error: value is not in bounds
							$error_messages[$data["question_id"]] = $this->lng->txt("metric_question_out_of_bounds");
							$error = 1;
						}
						if (!is_numeric($entered_value) && ($data["obligatory"]))
						{
							$error_messages[$data["question_id"]] = $this->lng->txt("metric_question_not_a_value");
							$error = 1;
						}
						if ((strcmp($entered_value, "") == 0) && ($data["obligatory"]))
						{
							// there is an error: value is not in bounds
							$error_messages[$data["question_id"]] = $this->lng->txt("metric_question_out_of_bounds");
							$error = 1;
						}
						if (($data["subtype"] == SUBTYPE_RATIO_ABSOLUTE) && (intval($entered_value) != doubleval($entered_value)) && ($data["obligatory"]))
						{
							$error_messages[$data["question_id"]] = $this->lng->txt("metric_question_floating_point");
							$error = 1;
						}
						if (($error == 0) && (strcmp($entered_value, "") != 0))
						{
							$save_answer = 1;
						}
					}
					if (strcmp($data["type_tag"], "qt_nominal") == 0)
					{
						$variables =& $this->object->getVariables($data["question_id"]);
						if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) and ($data["subtype"] == SUBTYPE_MCSR) and ($data["obligatory"]))
						{
							// none of the radio buttons was checked
							$error_messages[$data["question_id"]] = $this->lng->txt("nominal_question_not_checked");
							$error = 1;
						}
						if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) and ($data["subtype"] == SUBTYPE_MCSR) and (!$data["obligatory"])) {
							$save_answer = 0;
						}
						else
						{
							$save_answer = 1;
						}
					}
					if (strcmp($data["type_tag"], "qt_ordinal") == 0)
					{
						$variables =& $this->object->getVariables($data["question_id"]);
						if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) && ($data["obligatory"]))
						{
							// none of the radio buttons was checked
							$error_messages[$data["question_id"]] = $this->lng->txt("ordinal_question_not_checked");
							$error = 1;
						}
						if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) && !$error)
						{
							$save_answer = 0;
						}
						else
						{
							$save_answer = 1;
						}
					}
					if (strcmp($data["type_tag"], "qt_text") == 0)
					{
						$variables =& $this->object->getVariables($data["question_id"]);
						if ((strcmp($_POST[$data["question_id"] . "_text_question"], "") == 0) && ($data["obligatory"]))
						{
							// none of the radio buttons was checked
							$error_messages[$data["question_id"]] = $this->lng->txt("text_question_not_filled_out");
							$error = 1;
						}
						if ((strcmp($_POST[$data["question_id"] . "_text_question"], "") == 0) && (!$data["obligatory"]))
						{
							$save_answer = 0;
						}
						else
						{
							$save_answer = 1;
						}
					}
					$page_error += $error;
					if ((!$error) && ($save_answer))
					{
						// save user input
						$this->object->deleteWorkingData($data["question_id"], $ilUser->id);
						switch ($data["type_tag"])
						{
							case "qt_nominal":
								if ($data["subtype"] == SUBTYPE_MCSR)
								{
									$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_POST[$data["question_id"] . "_value"]);
								}
								else
								{
									if (is_array($_POST[$data["question_id"] . "_value"]))
									{
										foreach ($_POST[$data["question_id"] . "_value"] as $value)
										{
											$this->object->saveWorkingData($data["question_id"], $ilUser->id, $value);
										}
									}
									else
									{
										$this->object->saveWorkingData($data["question_id"], $ilUser->id);
									}
								}
								break;
							case "qt_ordinal":
								$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_POST[$data["question_id"] . "_value"]);
								break;
							case "qt_metric":
								$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_POST[$data["question_id"] . "_metric_question"]);
								break;
							case "qt_text":
								$this->object->saveWorkingData($data["question_id"], $ilUser->id, 0, ilUtil::stripSlashes($_POST[$data["question_id"] . "_text_question"]));
								break;
						}
					}
				}
			}
			if ($page_error)
			{
				if ($page_error == 1)
				{
					sendInfo($this->lng->txt("svy_page_error"));
				}
				else
				{
					sendInfo($this->lng->txt("svy_page_errors"));
				}
			}

			if ($_POST["cmd"]["previous"])
			{
				$activepage = $_GET["qid"];
				if (!$page_error)
				{
					$direction = -1;
				}
			}
			else if ($_POST["cmd"]["next"])
			{
				$activepage = $_GET["qid"];
				if (!$page_error)
				{
					$direction = 1;
				}
			}

			$page = $this->object->getNextPage($activepage, $direction);
			$constraint_true = 0;
			// check for constraints
			if (count($page[0]["constraints"]))
			{
				while (is_array($page) and ($constraint_true == 0) and (count($page[0]["constraints"])))
				{
					$constraint_true = 1;
					foreach ($page[0]["constraints"] as $constraint)
					{
						$working_data = $this->object->loadWorkingData($constraint["question"], $ilUser->id);
						$constraint_true = $constraint_true & $this->object->checkConstraint($constraint, $working_data);
					}
					if ($constraint_true == 0)
					{
						$page = $this->object->getNextPage($page[0]["question_id"], $direction);
					}
				}
			}

			$qid = "";
			if ($page === 0)
			{
				$this->runShowIntroductionPage();
				return;
			}
			else if ($page === 1)
			{
				$this->object->finishSurvey($ilUser->id);
				$this->runShowFinishedPage();
				return;
			}
			else
			{
				$this->outNavigationButtons("top", $page);
				$this->tpl->addBlockFile("NOMINAL_QUESTION", "nominal_question", "tpl.il_svy_out_nominal.html", true);
				$this->tpl->addBlockFile("ORDINAL_QUESTION", "ordinal_question", "tpl.il_svy_out_ordinal.html", true);
				$this->tpl->addBlockFile("METRIC_QUESTION", "metric_question", "tpl.il_svy_out_metric.html", true);
				$this->tpl->addBlockFile("TEXT_QUESTION", "text_question", "tpl.il_svy_out_text.html", true);
				$this->tpl->setCurrentBlock("percentage");
				$this->tpl->setVariable("PERCENTAGE", (int)(($page[0]["position"])*200));
				$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($page[0]["position"])*100));
				$this->tpl->setVariable("HUNDRED_PERCENT", "200");
				$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
				$this->tpl->parseCurrentBlock();
				if (count($page) > 1)
				{
					$this->tpl->setCurrentBlock("questionblock_title");
					$this->tpl->setVariable("TEXT_QUESTIONBLOCK_TITLE", $this->lng->txt("questionblock") . ": " . $page[0]["questionblock_title"]);
					$this->tpl->parseCurrentBlock();
				}
				foreach ($page as $data)
				{
					$this->tpl->setCurrentBlock("survey_content");
					if ($data["heading"])
					{
						$this->tpl->setVariable("QUESTION_HEADING", $data["heading"]);
					}
					$question_gui = $this->object->getQuestionGUI($data["type_tag"], $data["question_id"]);
					$working_data = $this->object->loadWorkingData($data["question_id"], $ilUser->id);
					$question_gui->object->setObligatory($data["obligatory"]);
					$question_gui->outWorkingForm($working_data, $this->object->getShowQuestionTitles(), $error_messages[$data["question_id"]]);
					$qid = "&qid=" . $data["question_id"];
					$this->tpl->parse("survey_content");
				}
				$this->outNavigationButtons("bottom", $page);
			}
			$this->tpl->setCurrentBlock("content");
			$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . "$add_parameter$qid");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->runShowIntroductionPage();
		}
	}

/**
* Creates the navigation buttons for a survey
*
* Creates the navigation buttons for a survey.
* Runs twice to generate a top and a bottom navigation to
* ease the use of long forms.
*
* @access public
*/
	function outNavigationButtons($navigationblock = "top", $page)
	{
		$prevpage = $this->object->getNextPage($page[0]["question_id"], -1);
		$this->tpl->setCurrentBlock($navigationblock . "_prev");
		if ($prevpage === 0)
		{
			$this->tpl->setVariable("BTN_PREV", $this->lng->txt("survey_start"));
		}
		else
		{
			$this->tpl->setVariable("BTN_PREV", $this->lng->txt("survey_previous"));
		}
		$this->tpl->parseCurrentBlock();
		$nextpage = $this->object->getNextPage($page[0]["question_id"], 1);
		$this->tpl->setCurrentBlock($navigationblock . "_next");
		if ($nextpage === 1)
		{
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("survey_finish"));
		}
		else
		{
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("survey_next"));
		}
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates the introduction page for a running survey
*
* Creates the introduction page for a running survey
*
* @access public
*/
	function runShowIntroductionPage()
	{
		global $ilUser;
		global $rbacsystem;
		
		// show introduction page
    $add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_introduction.html", true);
		$this->tpl->setCurrentBlock("start");
		$canStart = $this->object->canStartSurvey();
		if ($this->object->isSurveyStarted($ilUser->id) === 1)
		{
			sendInfo($this->lng->txt("already_completed_survey"));
			$this->tpl->setCurrentBlock("start");
			$this->tpl->setVariable("BTN_START", $this->lng->txt("start_survey"));
			$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
			$this->tpl->parseCurrentBlock();
		}
		if ($this->object->isSurveyStarted($ilUser->id) === 0)
		{
			$this->tpl->setCurrentBlock("resume");
			$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("resume_survey"));
			switch ($canStart)
			{
				case SURVEY_START_START_DATE_NOT_REACHED:
					sendInfo($this->lng->txt("start_date_not_reached"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
				case SURVEY_START_END_DATE_REACHED:
					sendInfo($this->lng->txt("end_date_reached"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
				case SURVEY_START_OFFLINE:
					sendInfo($this->lng->txt("survey_is_offline"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
			}
			$this->tpl->parseCurrentBlock();
		}
		if ($this->object->isSurveyStarted($ilUser->id) === false)
		{
			$this->tpl->setCurrentBlock("start");
			$this->tpl->setVariable("BTN_START", $this->lng->txt("start_survey"));
			if (!$rbacsystem->checkAccess('participate', $this->object->getRefId()))
			{
				sendInfo($this->lng->txt("cannot_participate_survey"));
				$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
			}
			switch ($canStart)
			{
				case SURVEY_START_START_DATE_NOT_REACHED:
					sendInfo($this->lng->txt("start_date_not_reached"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
				case SURVEY_START_END_DATE_REACHED:
					sendInfo($this->lng->txt("end_date_reached"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
				case SURVEY_START_OFFLINE:
					sendInfo($this->lng->txt("survey_is_offline"));
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					break;
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$introduction = $this->object->getIntroduction();
		$introduction = preg_replace("/\n/i", "<br />", $introduction);
		$this->tpl->setVariable("TEXT_INTRODUCTION", $introduction);
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . "$add_parameter");
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates the finished page for a running survey
*
* Creates the finished page for a running survey
*
* @access public
*/
	function runShowFinishedPage()
	{
		// show introduction page
    $add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_finished.html", true);
		$this->tpl->setVariable("TEXT_FINISHED", $this->lng->txt("survey_finished"));
		$this->tpl->setVariable("BTN_EXIT", $this->lng->txt("exit"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . "$add_parameter");
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates the properties form for the survey object
*
* Creates the properties form for the survey object
*
* @access public
*/
  function propertiesObject()
  {
		global $rbacsystem;

    $add_parameter = $this->getAddParameter();
		if ($_POST["cmd"]["save"])
		{
			$this->writePropertiesFormData();
		}
    if ($_POST["cmd"]["save"]) {
			$this->updateObject();
    }
    if ($_POST["cmd"]["cancel"]) {
      sendInfo($this->lng->txt("msg_cancel"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel",ILIAS_HTTP_PATH."/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
      exit();
    }

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_properties.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("VALUE_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("VALUE_DESCRIPTION", ilUtil::prepareFormOutput($this->object->getDescription()));
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", ilUtil::prepareFormOutput($this->object->getIntroduction()));
		$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("status"));
		$this->tpl->setVariable("TEXT_START_DATE", $this->lng->txt("start_date"));
		$this->tpl->setVariable("VALUE_START_DATE", ilUtil::makeDateSelect("start_date", $this->object->getStartYear(), $this->object->getStartMonth(), $this->object->getStartDay()));
		$this->tpl->setVariable("TEXT_END_DATE", $this->lng->txt("end_date"));
		$this->tpl->setVariable("VALUE_END_DATE", ilUtil::makeDateSelect("end_date", $this->object->getEndYear(), $this->object->getEndMonth(), $this->object->getEndDay()));
		$this->tpl->setVariable("TEXT_EVALUATION_ACCESS", $this->lng->txt("evaluation_access"));
		$this->tpl->setVariable("VALUE_OFFLINE", $this->lng->txt("offline"));
		$this->tpl->setVariable("VALUE_ONLINE", $this->lng->txt("online"));
		$this->tpl->setVariable("TEXT_ENABLED", $this->lng->txt("enabled"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		$this->tpl->setVariable("TEXT_ANONYMIZATION", $this->lng->txt("anonymize_survey"));
		$this->tpl->setVariable("TEXT_ANONYMIZATION_EXPLANATION", $this->lng->txt("anonymize_survey_explanation"));
		$this->tpl->setVariable("ANON_VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("ANON_VALUE_ON", $this->lng->txt("on"));
		
		if ($this->object->getAnonymize())
		{
			$this->tpl->setVariable("ANON_SELECTED_ON", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("ANON_SELECTED_OFF", " selected=\"selected\"");
		}
		
		if ($this->object->getEndDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_END_DATE", " checked=\"checked\"");
		}
		if ($this->object->getStartDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_START_DATE", " checked=\"checked\"");
		}

		if ($this->object->getEvaluationAccess() == EVALUATION_ACCESS_ON)
		{
			$this->tpl->setVariable("SELECTED_ON", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFF", " selected=\"selected\"");
		}
		if ($this->object->getStatus() == STATUS_ONLINE)
		{
			$this->tpl->setVariable("SELECTED_ONLINE", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFFLINE", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->setVariable("TEXT_SHOW_QUESTIONTITLES", $this->lng->txt("svy_show_questiontitles"));
		if ($this->object->getShowQuestionTitles())
		{
			$this->tpl->setVariable("QUESTIONTITLES_CHECKED", " checked=\"checked\"");
		}
    $this->tpl->parseCurrentBlock();
  }

/**
* Creates the questionbrowser to select questions from question pools
*
* Creates the questionbrowser to select questions from question pools
*
* @access public
*/
	function questionBrowser() {
    global $rbacsystem;

    $add_parameter = $this->getAddParameter() . "&insert_question=1";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questionbrowser.html", true);
    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_qpl_action_buttons.html", true);
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_svy_filter_questions.html", true);

		$questionpools =& $this->object->getQuestionpoolTitles();

		$filter_type = $_GET["sel_filter_type"];
		if (!$filter_type)
		{
			$filter_type = $_POST["sel_filter_type"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_type = "";
		}
		$add_parameter .= "&sel_filter_type=$filter_type";

		$filter_text = $_GET["filter_text"];
		if (!$filter_text)
		{
			$filter_text = $_POST["filter_text"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_text = "";
		}
		$add_parameter .= "&filter_text=$filter_text";

		$browsequestions = 1;
		if (strcmp($_POST["cmd"]["datatype"], "") != 0)
		{
			$browsequestions = $_POST["datatype"];
		}
		else
		{
			if (strcmp($_GET["browsetype"], "") != 0)
			{
				$browsequestions = $_GET["browsetype"];
			}
		}
		$add_parameter .= "&browsetype=$browsequestions";

		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) {
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (strcmp($_POST["cmd"]["resetFilter"], "") == 0) {
				if (strcmp($filter_type, $key) == 0) {
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		$filter_question_type = $_POST["sel_question_type"];
		if (!$filter_question_type)
		{
			$filter_question_type = $_GET["sel_question_type"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_question_type = "";
		}
		$add_parameter .= "&sel_question_type=$filter_question_type";

		if ($browsequestions)
		{
			$questiontypes =& $this->object->_getQuestiontypes();
			foreach ($questiontypes as $key => $value)
			{
				$this->tpl->setCurrentBlock("questiontype_row");
				$this->tpl->setVariable("VALUE_QUESTION_TYPE", $value);
				$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($value));
				if (strcmp($filter_question_type, $value) == 0)
				{
					$this->tpl->setVariable("SELECTED_QUESTION_TYPE", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($_POST["cmd"]["filter"])
		{
			$filter_questionpool = $_POST["sel_questionpool"];
		}
		else
		{
			$filter_questionpool = $_GET["sel_questionpool"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_questionpool = "";
		}
		$add_parameter .= "&sel_questionpool=$filter_questionpool";
		
		if ($browsequestions)
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("questionpool_row");
				$this->tpl->setVariable("VALUE_QUESTIONPOOL", $key);
				$this->tpl->setVariable("TEXT_QUESTIONPOOL", $value);
				if (strcmp($filter_questionpool, $key) == 0)
				{
					$this->tpl->setVariable("SELECTED_QUESTIONPOOL", " selected=\"selected\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($browsequestions)
		{
			$this->tpl->setCurrentBlock("question_filters");
			$this->tpl->setVariable("SHOW_QUESTION_TYPES", $this->lng->txt("filter_show_question_types"));
			$this->tpl->setVariable("TEXT_ALL_QUESTION_TYPES", $this->lng->txt("filter_all_question_types"));
			$this->tpl->setVariable("SHOW_QUESTIONPOOLS", $this->lng->txt("filter_show_questionpools"));
			$this->tpl->setVariable("TEXT_ALL_QUESTIONPOOLS", $this->lng->txt("filter_all_questionpools"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
    $this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
    $this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
    if (!$_POST["cmd"]["reset"]) {
      $this->tpl->setVariable("VALUE_FILTER_TEXT", $filter_text);
    }
    $this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
    $this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
    $this->tpl->setVariable("OPTION_QUESTIONS", $this->lng->txt("questions"));
    $this->tpl->setVariable("OPTION_QUESTIONBLOCKS", $this->lng->txt("questionblocks"));
		if ($browsequestions)
		{
	    $this->tpl->setVariable("SELECTED_QUESTIONS", " selected=\"selected\"");
		}
		else
		{
	    $this->tpl->setVariable("SELECTED_QUESTIONBLOCKS", " selected=\"selected\"");
		}
    $this->tpl->setVariable("TEXT_DATATYPE", $this->lng->txt("display_all_available"));
		$this->tpl->setVariable("BTN_CHANGE", $this->lng->txt("change"));
    $this->tpl->parseCurrentBlock();

		if ($_POST["cmd"]["reset"])
		{
			$_POST["filter_text"] = "";
		}
		$startrow = 0;
		if ($_GET["prevrow"])
		{
			$startrow = $_GET["prevrow"];
		}
		if ($_GET["nextrow"])
		{
			$startrow = $_GET["nextrow"];
		}
		if ($_GET["startrow"])
		{
			$startrow = $_GET["startrow"];
		}
		if (!$_GET["sort"])
		{
			// default sort order
			$_GET["sort"] = array("title" => "ASC");
		}
		if ($browsequestions)
		{
			$table = $this->object->getQuestionsTable($_GET["sort"], $filter_text, $filter_type, $startrow, 1, $filter_question_type, $filter_questionpool);
		}
		else
		{
			$table = $this->object->getQuestionblocksTable($_GET["sort"], $filter_text, $filter_type, $startrow);
		}
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
		$questionblock_id = 0;
		if ($browsequestions)
		{
			foreach ($table["rows"] as $data)
			{
				if ($rbacsystem->checkAccess("write", $data["ref_id"])) {
					$this->tpl->setCurrentBlock("QTab");
					if ($data["complete"]) {
						// make only complete questions selectable
						$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					}
					$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
					$this->tpl->setVariable("PREVIEW", "[<a href=\"" . "questionpool.php?ref_id=" . $data["ref_id"] . "&cmd=preview&preview=" . $data["question_id"] . " \" target=\"_blank\">" . $this->lng->txt("preview") . "</a>]");
					$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
					$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
					$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
					$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP"]), "date"));
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data["obj_fi"]]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
			if ($table["rowcount"] > count($table["rows"]))
			{
				$nextstep = $table["nextrow"] + $table["step"];
				if ($nextstep > $table["rowcount"])
				{
					$nextstep = $table["rowcount"];
				}
				$sort = "";
				if (is_array($_GET["sort"]))
				{
					$key = key($_GET["sort"]);
					$sort = "&sort[$key]=" . $_GET["sort"]["$key"];
				}
				$counter = 1;
				for ($i = 0; $i < $table["rowcount"]; $i += $table["step"])
				{
					$this->tpl->setCurrentBlock("pages_questions");
					if ($table["startrow"] == $i)
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<span class=\"inactivepage\">$counter</span>");
					}
					else
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->getCallingScript() . $add_parameter . "$sort&nextrow=$i" . "\">$counter</a>");
					}
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("questions_navigation_bottom");
				$this->tpl->setVariable("TEXT_ITEM", $this->lng->txt("item"));
				$this->tpl->setVariable("TEXT_ITEM_START", $table["startrow"] + 1);
				$end = $table["startrow"] + $table["step"];
				if ($end > $table["rowcount"])
				{
					$end = $table["rowcount"];
				}
				$this->tpl->setVariable("TEXT_ITEM_END", $end);
				$this->tpl->setVariable("TEXT_OF", strtolower($this->lng->txt("of")));
				$this->tpl->setVariable("TEXT_ITEM_COUNT", $table["rowcount"]);
				$this->tpl->setVariable("TEXT_PREVIOUS", $this->lng->txt("previous"));
				$this->tpl->setVariable("TEXT_NEXT", $this->lng->txt("next"));
				$this->tpl->setVariable("HREF_PREV_ROWS", $this->getCallingScript() . $add_parameter . "$sort&prevrow=" . $table["prevrow"]);
				$this->tpl->setVariable("HREF_NEXT_ROWS", $this->getCallingScript() . $add_parameter . "$sort&nextrow=" . $table["nextrow"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			foreach ($table["rows"] as $data)
			{
				$this->tpl->setCurrentBlock("questionblock_row");
				$this->tpl->setVariable("QUESTIONBLOCK_ID", $data["questionblock_id"]);
				$this->tpl->setVariable("QUESTIONBLOCK_TITLE", "<strong>" . $data["title"] . "</strong>");
				$this->tpl->setVariable("SURVEY_TITLE", $data["surveytitle"]);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("QUESTIONS_TITLE", $data["questions"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
			if ($table["rowcount"] > count($table["rows"]))
			{
				$nextstep = $table["nextrow"] + $table["step"];
				if ($nextstep > $table["rowcount"])
				{
					$nextstep = $table["rowcount"];
				}
				$sort = "";
				if (is_array($_GET["sort"]))
				{
					$key = key($_GET["sort"]);
					$sort = "&sort[$key]=" . $_GET["sort"]["$key"];
				}
				$counter = 1;
				for ($i = 0; $i < $table["rowcount"]; $i += $table["step"])
				{
					$this->tpl->setCurrentBlock("pages_questionblocks");
					if ($table["startrow"] == $i)
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<strong>$counter</strong>");
					}
					else
					{
						$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $this->getCallingScript() . $add_parameter . "$sort&nextrow=$i" . "\">$counter</a>");
					}
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("questionblocks_navigation_bottom");
				$this->tpl->setVariable("TEXT_ITEM", $this->lng->txt("item"));
				$this->tpl->setVariable("TEXT_ITEM_START", $table["startrow"] + 1);
				$end = $table["startrow"] + $table["step"];
				if ($end > $table["rowcount"])
				{
					$end = $table["rowcount"];
				}
				$this->tpl->setVariable("TEXT_ITEM_END", $end);
				$this->tpl->setVariable("TEXT_OF", strtolower($this->lng->txt("of")));
				$this->tpl->setVariable("TEXT_ITEM_COUNT", $table["rowcount"]);
				$this->tpl->setVariable("TEXT_PREVIOUS", $this->lng->txt("previous"));
				$this->tpl->setVariable("TEXT_NEXT", $this->lng->txt("next"));
				$this->tpl->setVariable("HREF_PREV_ROWS", $this->getCallingScript() . $add_parameter . "$sort&prevrow=" . $table["prevrow"]);
				$this->tpl->setVariable("HREF_NEXT_ROWS", $this->getCallingScript() . $add_parameter . "$sort&nextrow=" . $table["nextrow"]);
				$this->tpl->parseCurrentBlock();
			}
		}

    // if there are no questions, display a message
    if ($counter == 0) {
      $this->tpl->setCurrentBlock("Emptytable");
			if ($browsequestions)
			{
      	$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			}
			else
			{
      	$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questionblocks_available"));
			}
      $this->tpl->parseCurrentBlock();
    }
		else
		{
			// create edit buttons & table footer
			$this->tpl->setCurrentBlock("selection");
			$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("Footer");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->parseCurrentBlock();
		}
    // define the sort column parameters
    $sort = array(
      "title" => $_GET["sort"]["title"],
      "description" => $_GET["sort"]["description"],
      "type" => $_GET["sort"]["type"],
      "author" => $_GET["sort"]["author"],
      "created" => $_GET["sort"]["created"],
      "updated" => $_GET["sort"]["updated"],
			"qpl" => $_GET["sort"]["qpl"],
			"svy" => $_GET["sort"]["svy"]
    );
    foreach ($sort as $key => $value) {
      if (strcmp($value, "ASC") == 0) {
        $sort[$key] = "DESC";
      } else {
        $sort[$key] = "ASC";
      }
    }

		if ($browsequestions)
		{
			$this->tpl->setCurrentBlock("questions_header");
			$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
			$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[description]=" . $sort["description"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["description"]);
			$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
			$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
			$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
			$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
			$this->tpl->setVariable("QUESTION_POOL", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[qpl]=" . $sort["qpl"] . "\">" . $this->lng->txt("obj_spl") . "</a>" . $table["images"]["qpl"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("questionblocks_header");
			$this->tpl->setVariable("QUESTIONBLOCK_TITLE", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
			$this->tpl->setVariable("SURVEY_TITLE", "<a href=\"" . $this->getCallingScript() . "$add_parameter&startrow=" . $table["startrow"] . "&sort[svy]=" . $sort["svy"] . "\">" . $this->lng->txt("obj_svy") . "</a>" . $table["images"]["svy"]);
			$this->tpl->setVariable("QUESTIONS_TITLE", $this->lng->txt("contains"));
			$this->tpl->parseCurrentBlock();
		}
    $this->tpl->setCurrentBlock("adm_content");
    // create table header
    $this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
    $this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
    $this->tpl->parseCurrentBlock();
	}

/**
* Creates a form to search questions for inserting
*
* Creates a form to search questions for inserting
*
* @access public
*/
	function searchQuestionsForm()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_search_questions.html", true);

		if ($_POST["cmd"]["search"])
		{
			$search = new SurveySearch(ilUtil::stripSlashes($_POST["search_term"]), $_POST["concat"], $_POST["search_field"], $_POST["search_type"]);
			$search->search();
			if (count($search->search_results))
			{
				$classes = array("tblrow1", "tblrow2");
				$counter = 0;
				$titles = $this->object->getQuestionpoolTitles();
				$forbidden_pools =& $this->object->getForbiddenQuestionpools();
				$existing_questions =& $this->object->getExistingQuestions();
				foreach ($search->search_results as $data)
				{
					if ((!in_array($data["question_id"], $existing_questions)) && (!in_array($data["obj_fi"], $forbidden_pools)))
					{
						$this->tpl->setCurrentBlock("result_row");
						$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
						$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
						$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
						$this->tpl->setVariable("QUESTION_DESCRIPTION", $data["description"]);
						$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
						$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
						$this->tpl->setVariable("QUESTION_POOL", $titles[$data["ref_id"]]);
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
				$this->tpl->setCurrentBlock("search_results");
				$this->tpl->setVariable("RESULT_IMAGE", ilUtil::getImagePath("icon_spl_b.gif"));
				$this->tpl->setVariable("ALT_IMAGE", $this->lng->txt("found_questions"));
				$this->tpl->setVariable("TEXT_QUESTION_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_QUESTION_DESCRIPTION", $this->lng->txt("description"));
				$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("question_type"));
				$this->tpl->setVariable("TEXT_QUESTION_AUTHOR", $this->lng->txt("author"));
				$this->tpl->setVariable("TEXT_QUESTION_POOL", $this->lng->txt("obj_spl"));
				$this->tpl->setVariable("BTN_INSERT", $this->lng->txt("insert"));
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("FOUND_QUESTIONS", $this->lng->txt("found_questions"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				sendInfo($this->lng->txt("no_search_results"));
			}
		}

		sendInfo();
		$add_parameter = $this->getAddParameter();
		$questiontypes = &$this->object->getQuestiontypes();
		foreach ($questiontypes as $questiontype)
		{
			$this->tpl->setCurrentBlock("questiontypes");
			$this->tpl->setVariable("VALUE_QUESTION_TYPE", $questiontype);
			$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($questiontype));
			if (strcmp($_POST["search_type"], $questiontype) == 0)
			{
				$this->tpl->setVariable("SELECTED_SEARCH_TYPE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		switch ($_POST["search_field"])
		{
			case "title":
				$this->tpl->setVariable("CHECKED_TITLE", " selected=\"selected\"");
				break;
			case "description":
				$this->tpl->setVariable("CHECKED_DESCRIPTION", " selected=\"selected\"");
				break;
			case "author":
				$this->tpl->setVariable("CHECKED_AUTHOR", " selected=\"selected\"");
				break;
			case "questiontext":
				$this->tpl->setVariable("CHECKED_QUESTIONTEXT", " selected=\"selected\"");
				break;
			case "default":
				$this->tpl->setVariable("CHECKED_ALL", " selected=\"selected\"");
				break;
		}
		$this->tpl->setVariable("TEXT_SEARCH_TERM", $this->lng->txt("search_term"));
		$this->tpl->setVariable("VALUE_SEARCH_TERM", $_POST["search_term"]);
		$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("concatenation"));
		$this->tpl->setVariable("TEXT_AND", $this->lng->txt("and"));
		$this->tpl->setVariable("TEXT_OR", $this->lng->txt("or"));
		if ($_POST["concat"] == 1)
		{
			$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
		}
		else
		{
			$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
		}
		$this->tpl->setVariable("TEXT_SEARCH_FOR", $this->lng->txt("search_for"));
		$this->tpl->setVariable("SEARCH_FIELD_ALL", $this->lng->txt("search_field_all"));
		$this->tpl->setVariable("SEARCH_FIELD_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("SEARCH_FIELD_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("SEARCH_FIELD_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("SEARCH_FIELD_QUESTIONTEXT", $this->lng->txt("question"));
		$this->tpl->setVariable("SEARCH_TYPE_ALL", $this->lng->txt("search_type_all"));
		$this->tpl->setVariable("BTN_SEARCH", $this->lng->txt("search"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter . "&search_question=1&browsetype=1&insert_question=1");
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a confirmation form to insert questions into the survey
*
* Creates a confirmation form to insert questions into the survey
*
* @access public
*/
	function insertQuestionsForm($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_insert_questions.html", true);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($_GET["browsetype"] == 1)
		{
			$questions = &$this->object->getQuestions($checked_questions);
			if (count($questions))
			{
				foreach ($questions as $data)
				{
					if (in_array($data["question_id"], $checked_questions))
					{
						$this->tpl->setCurrentBlock("row");
						$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
						$this->tpl->setVariable("TEXT_TITLE", $data["title"]);
						$this->tpl->setVariable("TEXT_DESCRIPTION", $data["description"]);
						$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt($data["type_tag"]));
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
			}
		}
		else
		{
			$questionblocks = &$this->object->getQuestionblocks($checked_questions);
			if (count($questionblocks))
			{
				foreach ($questionblocks as $questionblock_id => $data)
				{
					if (in_array($questionblock_id, $checked_questions))
					{
						$this->tpl->setCurrentBlock("row");
						$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
						$this->tpl->setVariable("TEXT_TITLE", $data[key($data)]["title"]);
						$this->tpl->setVariable("TEXT_TYPE", $data[key($data)]["surveytitle"]);
						$contains = array();
						foreach ($data as $key => $value)
						{
							array_push($contains, $value["sequence"] . ". " . $value["questiontitle"]);
						}
						$this->tpl->setVariable("TEXT_DESCRIPTION", join($contains, ", "));
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		if ($_GET["browsetype"] == 1)
		{
			$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
			$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		}
		else
		{
			$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("contains"));
			$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("obj_svy"));
		}
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter() . "&browsetype=" . $_GET["browsetype"]);
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a confirmation form to remove questions from the survey
*
* Creates a confirmation form to remove questions from the survey
*
* @param array $checked_questions An array containing the id's of the questions to be removed
* @param array $checked_questionblocks An array containing the id's of the question blocks to be removed
* @access public
*/
	function removeQuestionsForm($checked_questions, $checked_questionblocks)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_remove_questions.html", true);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$surveyquestions =& $this->object->getSurveyQuestions();
		foreach ($surveyquestions as $question_id => $data)
		{
			if (in_array($data["question_id"], $checked_questions) or (in_array($data["questionblock_id"], $checked_questionblocks)))
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TEXT_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_DESCRIPTION", $data["description"]);
				$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $data["questionblock_title"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}
		foreach ($checked_questionblocks as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_qb_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}


/**
* Displays the definition form for a question block
*
* Displays the definition form for a question block
*
* @param integer $questionblock_id The database id of the questionblock to edit an existing questionblock
* @access public
*/
	function defineQuestionblock($questionblock_id = "")
	{
		sendInfo();
		if ($questionblock_id)
		{
			$questionblock = $this->object->getQuestionblock($questionblock_id);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_define_questionblock.html", true);
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "cb_$matches[1]");
				$this->tpl->setVariable("HIDDEN_VALUE", $matches[1]);
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
		$this->tpl->setVariable("DEFINE_QUESTIONBLOCK_HEADING", $this->lng->txt("define_questionblock"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		if ($questionblock_id)
		{
			$this->tpl->setVariable("VALUE_TITLE", $questionblock["title"]);
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a form to select a survey question pool for storage
*
* Creates a form to select a survey question pool for storage
*
* @access public
*/
	function questionpoolSelectForm()
	{
		global $ilUser;
    $add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_qpl_select.html", true);
		$questionpools =& $this->object->getAvailableQuestionpools();
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("select_questionpool"));
		if (count($questionpools))
		{
			$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		}
		else
		{
			sendInfo($this->lng->txt("create_questionpool_before_add_question"));
		}
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates the form to edit the question/questionblock constraints
*
* Creates the form to edit the question/questionblock constraints
*
* @param array $checked_questions An array with the id's of the questions checked for editing
* @param array $checked_questionblocks An array with the id's of the questionblocks checked for editing
* @access public
*/
	function constraintsForm($checked_questions, $checked_questionblocks)
	{
		global $rbacsystem;
		sendInfo();
		$pages =& $this->object->getSurveyPages();
		$all_questions =& $this->object->getSurveyQuestions();
		$add_constraint = 0;
		$delete_constraint = 0;
		$constraint_question = -1;
		foreach ($_POST as $key => $value) {
			if (preg_match("/add_constraint_(\d+)/", $key, $matches)) {
				$add_constraint = 1;
				$constraint_question = $matches[1];
			}
		}
		if ($_POST["cmd"]["save_constraint"])
		{
			foreach ($checked_questions as $id)
			{
				foreach ($pages as $question_array)
				{
					foreach ($question_array as $question_data)
					{
						if ($question_data["question_id"] == $id)
						{
							$this->object->addConstraint($question_data["question_id"], $_POST["q"], $_POST["r"], $_POST["v"]);
						}
					}
				}
			}
			foreach ($checked_questionblocks as $id)
			{
				foreach ($pages as $question_array)
				{
					if ($question_array[0]["questionblock_id"] == $id)
					{
						foreach ($question_array as $question_data)
						{
							$this->object->addConstraint($question_data["question_id"], $_POST["q"], $_POST["r"], $_POST["v"]);
						}
					}
				}
			}
			$add_constraint = 0;
		}
		else if ($_POST["cmd"]["cancel_add_constraint"])
		{
			// do nothing, just cancel the form
			$add_constraint = 0;
		}
		else
		{
		}
		if ($add_constraint)
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_add_constraint.html", true);
			$found = 0;
			if ($_POST["cmd"]["select_relation"] or $_POST["cmd"]["select_value"])
			{
				$this->tpl->setCurrentBlock("option_q");
				$this->tpl->setVariable("OPTION_VALUE", $_POST["q"]);
				$this->tpl->setVariable("OPTION_TEXT", $all_questions[$_POST["q"]]["title"] . " (" . $this->lng->txt($all_questions[$_POST["q"]]["type_tag"]) . ")");
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				foreach ($pages as $question_array)
				{
					if (!$found)
					{
						foreach ($question_array as $question)
						{
							if ($question["question_id"] == $constraint_question)
							{
								$found = 1;
							}
						}
						if (!$found)
						{
							foreach ($question_array as $question)
							{
								$this->tpl->setCurrentBlock("option_q");
								$this->tpl->setVariable("OPTION_VALUE", $question["question_id"]);
								$this->tpl->setVariable("OPTION_TEXT", $question["title"] . " (" . $this->lng->txt($question["type_tag"]) . ")");
								if ($question["question_id"] == $_POST["q"])
								{
									$this->tpl->setVariable("OPTION_CHECKED", " selected=\"selected\"");
								}
								$this->tpl->parseCurrentBlock();
							}
						}
					}
				}
			}
			foreach ($_POST as $key => $value) {
				if (preg_match("/add_constraint_(\d+)/", $key, $matches)) {
					$this->tpl->setCurrentBlock("hidden");
					$this->tpl->setVariable("HIDDEN_NAME", $key);
					$this->tpl->setVariable("HIDDEN_VALUE", $value);
					$this->tpl->parseCurrentBlock();
					foreach ($checked_questions as $id)
					{
						$this->tpl->setCurrentBlock("hidden");
						$this->tpl->setVariable("HIDDEN_NAME", "cb_$id");
						$this->tpl->setVariable("HIDDEN_VALUE", "$id");
						$this->tpl->parseCurrentBlock();
					}
					foreach ($checked_questionblocks as $id)
					{
						$this->tpl->setCurrentBlock("hidden");
						$this->tpl->setVariable("HIDDEN_NAME", "cb_qb_$id");
						$this->tpl->setVariable("HIDDEN_VALUE", "$id");
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			$continue_command = "select_relation";
			$back_command = "cancel_add_constraint";
			if ($_POST["cmd"]["select_relation"] or $_POST["cmd"]["select_value"])
			{
				$relations = $this->object->getAllRelations();
				switch ($all_questions[$_POST["q"]]["type_tag"])
				{
					case "qt_nominal":
						foreach ($relations as $rel_id => $relation)
						{
							if ((strcmp($relation["short"], "=") == 0) or (strcmp($relation["short"], "<>") == 0))
							{
								$this->tpl->setCurrentBlock("option_r");
								$this->tpl->setVariable("OPTION_VALUE", $rel_id);
								$this->tpl->setVariable("OPTION_TEXT", $relation["short"]);
								if ($rel_id == $_POST["r"])
								{
									$this->tpl->setVariable("OPTION_CHECKED", " selected=\"selected\"");
								}
								$this->tpl->parseCurrentBlock();
							}
						}
						break;
					case "qt_ordinal":
					case "qt_metric":
						foreach ($relations as $rel_id => $relation)
						{
							$this->tpl->setCurrentBlock("option_r");
							$this->tpl->setVariable("OPTION_VALUE", $rel_id);
							$this->tpl->setVariable("OPTION_TEXT", $relation["short"]);
							if ($rel_id == $_POST["r"])
							{
								$this->tpl->setVariable("OPTION_CHECKED", " selected=\"selected\"");
							}
							$this->tpl->parseCurrentBlock();
						}
						break;
				}
				$this->tpl->setCurrentBlock("select_relation");
				$this->tpl->setVariable("SELECT_RELATION", $this->lng->txt("select_relation"));
				$this->tpl->parseCurrentBlock();
				$continue_command = "select_value";
				$back_command = "begin_add_constraint";
			}
			if ($_POST["cmd"]["select_value"])
			{
				$variables =& $this->object->getVariables($_POST["q"]);
				switch ($all_questions[$_POST["q"]]["type_tag"])
				{
					case "qt_nominal":
					case "qt_ordinal":
						foreach ($variables as $sequence => $row)
						{
							$this->tpl->setCurrentBlock("option_v");
							$this->tpl->setVariable("OPTION_VALUE", $sequence);
							$this->tpl->setVariable("OPTION_TEXT", ($sequence+1) . " - " . $row->title);
							$this->tpl->parseCurrentBlock();
						}
						break;
					case "qt_metric":
							$this->tpl->setCurrentBlock("textfield");
							$this->tpl->setVariable("TEXTFIELD_VALUE", "");
							$this->tpl->parseCurrentBlock();
						break;
				}
				$this->tpl->setCurrentBlock("select_value");
				if (strcmp($all_questions[$_POST["q"]]["type_tag"], "qt_metric") == 0)
				{
					$this->tpl->setVariable("SELECT_VALUE", $this->lng->txt("enter_value"));
				}
				else
				{
					$this->tpl->setVariable("SELECT_VALUE", $this->lng->txt("select_value"));
				}
				$this->tpl->parseCurrentBlock();
				$continue_command = "save_constraint";
				$back_command = "select_relation";
			}
			$this->tpl->setCurrentBlock("buttons");
			$this->tpl->setVariable("BTN_CONTINUE", $this->lng->txt("continue"));
			$this->tpl->setVariable("COMMAND", "$continue_command");
			$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));
			$this->tpl->setVariable("COMMAND_BACK", "$back_command");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$textoutput = "";
			foreach ($checked_questions as $id)
			{
				foreach ($pages as $question_array)
				{
					foreach ($question_array as $question_data)
					{
						if ($question_data["question_id"] == $id)
						{
							if ($textoutput)
							{
								$textoutput .= "<br>";
							}
							$textoutput .= $question_data["title"] . ": " . $question_data["questiontext"];
						}
					}
				}
			}
			foreach ($checked_questionblocks as $id)
			{
				foreach ($pages as $question_array)
				{
					if ($question_array[0]["questionblock_id"] == $id)
					{
						if ($textoutput)
						{
							$textoutput .= "<br>";
						}
						$textoutput .= $this->lng->txt("questionblock") . ": " . $question_array[0]["questionblock_title"];
					}
				}
			}
			$this->tpl->setVariable("CONSTRAINT_QUESTION_TEXT", "$textoutput");
			$this->tpl->setVariable("SELECT_PRIOR_QUESTION", $this->lng->txt("select_prior_question"));
			$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/delete_constraint_(\d+)_(\d+)/", $key, $matches)) {
					foreach ($pages as $question_array)
					{
						$found = 0;
						foreach ($question_array as $question_data)
						{
							if ($question_data["question_id"] == $matches[2])
							{
								$found = 1;
							}
						}
						if ($found)
						{
							foreach ($question_array as $question_id => $question_data)
							{
								$this->object->deleteConstraint($matches[1], $question_data["question_id"]);
							}
						}
					}
				}
			}
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_constraints.html", true);
			$colors = array("tblrow1", "tblrow2");
			$counter = 0;
			foreach ($pages as $question_array)
			{
				if (count($question_array) > 1)
				{
					// question block
					$data = $question_array[0];
				}
				else
				{
					// question
					$data = $question_array[0];
				}
				if (in_array($data["questionblock_id"], $checked_questionblocks) or (in_array($data["question_id"], $checked_questions)))
				{
					$counter = 0;
					$constraints = $this->object->getConstraints($data["question_id"]);
					if (count($constraints))
					{
						foreach ($constraints as $constraint)
						{
							$value = "";
							$variables =& $this->object->getVariables($constraint["question"]);
							switch ($all_questions[$constraint["question"]]["type_tag"])
							{
								case "qt_metric":
									$value = $constraint["value"];
									break;
								case "qt_nominal":
								case "qt_ordinal":
									$value = sprintf("%d", $constraint["value"]+1) . " - " . $variables[$constraint["value"]]->title;
									break;
							}
							$this->tpl->setCurrentBlock("constraint");
							$this->tpl->setVariable("CONSTRAINT_TEXT", $all_questions[$constraint["question"]]["title"] . " " . $constraint["short"] . " $value");
							if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
								$this->tpl->setVariable("CONSTRAINT_ID", $constraint["id"]);
								$this->tpl->setVariable("CONSTRAINT_QUESTION_ID", $constraint["question"]);
								$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
							}
							$this->tpl->parseCurrentBlock();
						}
					}
					else
					{
						$this->tpl->setCurrentBlock("empty_row");
						$this->tpl->setVariable("EMPTY_TEXT", $this->lng->txt("no_available_constraints"));
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("question");
					if ($data["questionblock_id"])
					{
						$this->tpl->setVariable("QUESTION_IDENTIFIER", $this->lng->txt("questionblock") . ": " . $data["questionblock_title"]);
					}
					else
					{
						$this->tpl->setVariable("QUESTION_IDENTIFIER", $this->lng->txt($data["type_tag"]) . ": " . $data["title"]);
					}
					if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
						$this->tpl->setVariable("ADD_QUESTION_ID", $data["question_id"]);
						$this->tpl->setVariable("BTN_ADD", $this->lng->txt("add"));
					}
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));
					$this->tpl->parseCurrentBlock();
				}
			}
			foreach ($checked_questions as $id)
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "cb_$id");
				$this->tpl->setVariable("HIDDEN_VALUE", "$id");
				$this->tpl->parseCurrentBlock();
			}
			foreach ($checked_questionblocks as $id)
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "cb_qb_$id");
				$this->tpl->setVariable("HIDDEN_VALUE", "$id");
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("adm_content");
			if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
				$this->tpl->setVariable("TEXT_EDIT_CONSTRAINTS", $this->lng->txt("edit_constraints_introduction"));
			}
			else
			{
				$this->tpl->setVariable("TEXT_EDIT_CONSTRAINTS", $this->lng->txt("view_constraints_introduction"));
			}
			$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $this->getAddParameter());
			$this->tpl->parseCurrentBlock();
		}
	}

/**
* Creates a form to add a heading to a survey
*
* Creates a form to add a heading to a survey
*
* @param integer $question_id The id of the question directly after the heading. If the id is given, an existing heading will be edited
* @access public
*/
	function addHeadingObject($question_id = "")
	{
    $add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_heading.html", true);
		$survey_questions =& $this->object->getSurveyQuestions();
		if ($question_id)
		{
			$_POST["insertbefore"] = $question_id;
			$_POST["heading"] = $survey_questions[$question_id]["heading"];
		}
		foreach ($survey_questions as $key => $value)
		{
			$this->tpl->setCurrentBlock("insertbefore_row");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$option = $this->lng->txt("before") . ": \"" . $value["title"] . "\"";
			if (strlen($option) > 80)
			{
				$option = preg_replace("/^(.{40}).*(.{40})$/", "\\1 [...] \\2", $option);
			}
			$this->tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($option));
			if ($key == $_POST["insertbefore"])
			{
				$this->tpl->setVariable("SELECTED_OPTION", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		if ($question_id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("INSERTBEFORE_ORIGINAL", $question_id);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($question_id)
		{
			$this->tpl->setVariable("TEXT_ADD_HEADING", $this->lng->txt("edit_heading"));
			$this->tpl->setVariable("SELECT_DISABLED", " disabled=\"disabled\"");
		}
		else
		{
			$this->tpl->setVariable("TEXT_ADD_HEADING", $this->lng->txt("add_heading"));
		}
		$this->tpl->setVariable("TEXT_HEADING", $this->lng->txt("heading"));
		$this->tpl->setVariable("VALUE_HEADING", $_POST["heading"]);
		$this->tpl->setVariable("TEXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Creates the questions form for the survey object
*
* Creates the questions form for the survey object
*
* @access public
*/
	function questionsObject() {
		global $rbacsystem;

		if ($_GET["new_id"] > 0)
		{
			// add a question to the survey previous created in a questionpool
			$this->object->insertQuestion($_GET["new_id"]);
		}
		
		if ($_GET["eqid"] and $_GET["eqpl"])
		{
			header("Location:questionpool.php?ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForSurvey&calling_survey=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}

		$_SESSION["calling_survey"] = $this->object->getRefId();
    $add_parameter = $this->getAddParameter();

		if ($_GET["editheading"])
		{
			$this->addHeadingObject($_GET["editheading"]);
			return;
		}
		
		if ($_GET["up"] > 0)
		{
			$this->object->moveUpQuestion($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->moveDownQuestion($_GET["down"]);
		}
		if ($_GET["qbup"] > 0)
		{
			$this->object->moveUpQuestionblock($_GET["qbup"]);
		}
		if ($_GET["qbdown"] > 0)
		{
			$this->object->moveDownQuestionblock($_GET["qbdown"]);
		}
		
		if ($_POST["cmd"]["saveHeading"])
		{
			if ($_POST["heading"])
			{
				$insertbefore = $_POST["insertbefore"];
				if (!$insertbefore)
				{
					$insertbefore = $_POST["insertbefore_original"];
				}
				$this->object->saveHeading($_POST["heading"], $insertbefore);
			}
			else
			{
				sendInfo($this->lng->txt("error_add_heading"));
				$this->addHeadingObject();
				return;
			}
		}
		
		if ($_POST["cmd"]["addHeading"])
		{
			$this->addHeadingObject();
			return;
		}
		
		if ($_POST["cmd"]["saveObligatory"])
		{
			$obligatory = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/obligatory_(\d+)/", $key, $matches))
				{
					$obligatory[$matches[1]] = 1;
				}
			}
			$this->object->setObligatoryStates($obligatory);
		}
		
		if ($_POST["cmd"]["insert_before"] or $_POST["cmd"]["insert_after"])
		{
			// get all questions to move
			$move_questions = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^move_(\d+)$/", $key, $matches))
				{
					array_push($move_questions, $value);
				}
			}
			// get insert point
			$insert_id = -1;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^cb_(\d+)$/", $key, $matches))
				{
					if ($insert_id < 0)
					{
						$insert_id = $matches[1];
					}
				}
				if (preg_match("/^cb_qb_(\d+)$/", $key, $matches))
				{
					if ($insert_id < 0)
					{
						$ids =& $this->object->getQuestionblockQuestionIds($matches[1]);
						if (count($ids))
						{
							if ($_POST["cmd"]["insert_before"])
							{
								$insert_id = $ids[0];
							}
							else if ($_POST["cmd"]["insert_after"])
							{
								$insert_id = $ids[count($ids)-1];
							}
						}
					}
				}
			}
			if ($insert_id <= 0)
			{
				sendInfo($this->lng->txt("no_target_selected_for_move"));
			}
			else
			{
				$insert_mode = 1;
				if ($_POST["cmd"]["insert_before"])
				{
					$insert_mode = 0;
				}
				$this->object->moveQuestions($move_questions, $insert_id, $insert_mode);
			}
		}

		if ($_GET["removeheading"])
		{
			$this->object->saveHeading("", $_GET["removeheading"]);
		}
		
		if ($_GET["editblock"])
		{
			$this->defineQuestionblock($_GET["editblock"]);
			return;
		}

		if ($_POST["cmd"]["questionblock"])
		{
			$questionblock = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_(\d+)/", $key, $matches))
				{
					array_push($questionblock, $value);
				}
			}
			if (count($questionblock) < 2)
			{
        sendInfo($this->lng->txt("qpl_define_questionblock_select_missing"));
			}
			else
			{
				$this->defineQuestionblock();
				return;
			}
		}

		if ($_POST["cmd"]["unfold"])
		{
			$unfoldblocks = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($unfoldblocks, $matches[1]);
				}
			}
			if (count($unfoldblocks))
			{
				$this->object->unfoldQuestionblocks($unfoldblocks);
			}
			else
			{
        sendInfo($this->lng->txt("qpl_unfold_select_none"));
			}
		}

		if ($_POST["cmd"]["save_questionblock"])
		{
			if ($_POST["title"])
			{
				if ($_POST["questionblock_id"])
				{
					$this->object->modifyQuestionblock($_POST["questionblock_id"], ilUtil::stripSlashes($_POST["title"]));
				}
				else
				{
					$questionblock = array();
					foreach ($_POST as $key => $value)
					{
						if (preg_match("/cb_(\d+)/", $key, $matches))
						{
							array_push($questionblock, $value);
						}
					}
					$this->object->createQuestionblock(ilUtil::stripSlashes($_POST["title"]), $questionblock);
				}
			}
		}

		$add_constraint = 0;
		$delete_constraint = 0;
		foreach ($_POST as $key => $value) {
			if (preg_match("/add_constraint_(\d+)/", $key, $matches)) {
				$add_constraint = 1;
			}
		}
		foreach ($_POST as $key => $value) {
			if (preg_match("/delete_constraint_(\d+)_(\d+)/", $key, $matches)) {
				$delete_constraint = 1;
			}
		}
		if ($_POST["cmd"]["constraints"] or $add_constraint or $delete_constraint or $_GET["constraints"])
		{
			$checked_questions = array();
			$checked_questionblocks = array();
			if ($_GET["constraints"])
			{
				$survey_questions =& $this->object->getSurveyQuestions();
				if (strcmp($survey_questions[$_GET["constraints"]]["questionblock_id"], "") == 0)
				{
					array_push($checked_questions, $_GET["constraints"]);
				}
				else
				{
					array_push($checked_questionblocks, $survey_questions[$_GET["constraints"]]["questionblock_id"]);
				}
			}
			foreach ($_POST as $key => $value) {
				if (preg_match("/cb_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
				if (preg_match("/cb_qb_(\d+)/", $key, $matches)) {
					array_push($checked_questionblocks, $matches[1]);
				}
			}
			if ($_POST["cmd"]["constraints"] and (count($checked_questions)+count($checked_questionblocks) == 0))
			{
				sendInfo($this->lng->txt("no_constraints_checked"));
			}
			else
			{
				$this->constraintsForm($checked_questions, $checked_questionblocks);
				return;
			}
		}

		if ($_POST["cmd"]["create_question"]) {
			$this->questionpoolSelectForm();
			return;
		}

		if ($_POST["cmd"]["create_question_execute"])
		{
			header("Location:questionpool.php?ref_id=" . $_POST["sel_spl"] . "&cmd=createQuestionForSurvey&new_for_survey=".$_GET["ref_id"]."&sel_question_types=".$_POST["sel_question_types"]);
			exit();
		}

		if ($_GET["add"])
		{
			// called after a new question was created from a questionpool
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
//			$total = $this->object->evalTotalPersons();
//			if ($total) {
				// the test was executed previously
//				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
//			} else {
				sendInfo($this->lng->txt("ask_insert_questions"));
//			}
			$this->insertQuestionsForm($selected_array);
			return;
		}

		if (($_POST["cmd"]["search_question"]) or ($_GET["search_question"]) and (!$_POST["cmd"]["insert"]))
		{
			$this->searchQuestionsForm();
			return;
		}

		if (($_POST["cmd"]["insert_question"]) or ($_GET["insert_question"])) {
			$show_questionbrowser = true;
			if ($_POST["cmd"]["insert"]) {
				// insert selected questions into test
				$selected_array = array();
				foreach ($_POST as $key => $value) {
					if (preg_match("/cb_(\d+)/", $key, $matches)) {
						array_push($selected_array, $matches[1]);
					}
				}
				if (!count($selected_array)) {
					if ($_GET["browsetype"] == 1)
					{
						sendInfo($this->lng->txt("insert_missing_question"));
					}
					else
					{
						sendInfo($this->lng->txt("insert_missing_questionblock"));
					}
				} else {
//					$total = $this->object->evalTotalPersons();
//					if ($total) {
						// the test was executed previously
//						sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
//					} else {
						if ($_GET["browsetype"] == 1)
						{
							sendInfo($this->lng->txt("ask_insert_questions"));
						}
						else
						{
							sendInfo($this->lng->txt("ask_insert_questionblocks"));
						}
//					}
					$this->insertQuestionsForm($selected_array);
					return;
				}
			}
			if ($_POST["cmd"]["back"]) {
				$show_questionbrowser = false;
			}
			if ($show_questionbrowser) {
				$this->questionBrowser();
				return;
			}
		}

		if (strlen($_POST["cmd"]["confirm_insert"]) > 0)
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
			sendInfo($this->lng->txt("questions_inserted"));
		}

		if (strlen($_POST["cmd"]["confirm_remove"]) > 0)
		{
			// remove questions from test after confirmation
			sendInfo($this->lng->txt("questions_removed"));
			$checked_questions = array();
			$checked_questionblocks = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
				if (preg_match("/id_qb_(\d+)/", $key, $matches)) {
					array_push($checked_questionblocks, $matches[1]);
				}
			}
			$this->object->removeQuestions($checked_questions, $checked_questionblocks);
			$this->object->saveCompletionStatus();
		}

		if (strlen($_POST["cmd"]["remove"]) > 0) {
			$checked_questions = array();
			$checked_questionblocks = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/cb_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
				if (preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($checked_questionblocks, $matches[1]);
				}
			}
			if (count($checked_questions) + count($checked_questionblocks) > 0) {
				sendInfo($this->lng->txt("remove_questions"));
				$this->removeQuestionsForm($checked_questions, $checked_questionblocks);
				return;
			} else {
				sendInfo($this->lng->txt("no_question_selected_for_removal"));
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questions.html", true);

		$survey_questions =& $this->object->getSurveyQuestions();
		$questionblock_titles =& $this->object->getQuestionblockTitles();
		$questionpools =& $this->object->getQuestionpoolTitles();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$title_counter = 0;
		$obligatory = "<img src=\"" . ilUtil::getImagePath("obligatory.gif", true) . "\" alt=\"" . $this->lng->txt("question_obligatory") . "\" title=\"" . $this->lng->txt("question_obligatory") . "\" />";
		if (count($survey_questions) > 0)
		{
			foreach ($survey_questions as $question_id => $data)
			{
				$title_counter++;
				if (($data["questionblock_id"] > 0) and ($data["questionblock_id"] != $last_questionblock_id))
				{
					if (($data["questionblock_id"] != $last_questionblock_id) and (strcmp($last_questionblock_id, "") != 0))
					{
						$counter++;
					}
					$this->tpl->setCurrentBlock("block");
					$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock") . ": " . $data["questionblock_title"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
						if ($data["question_id"] != $this->object->questions[0])
						{
							$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->getCallingScript() . "$add_parameter&qbup=" . $data["questionblock_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"" . $this->lng->txt("up") . "\" title=\"" . $this->lng->txt("up") . "\" border=\"0\" /></a>");
						}
						$akeys = array_keys($survey_questions);
						if ($data["questionblock_id"] != $survey_questions[$akeys[count($akeys)-1]]["questionblock_id"])
						{
							$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->getCallingScript() . "$add_parameter&qbdown=" . $data["questionblock_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"" . $this->lng->txt("down") . "\" title=\"" . $this->lng->txt("down") . "\" border=\"0\" /></a>");
						}
						$this->tpl->setVariable("TEXT_EDIT", "<img src=\"" . ilUtil::getImagePath("icon_pencil.gif") . "\" alt=\"" . $this->lng->txt("edit") . "\" title=\"" . $this->lng->txt("edit") . "\" border=\"0\" />");
						$this->tpl->setVariable("HREF_EDIT", $this->getCallingScript() . "$add_parameter&editblock=" . $data["questionblock_id"]);
					}
					if (count($data["constraints"]))
					{
						$this->tpl->setVariable("QUESTION_CONSTRAINTS", "<a href=\"" . $this->getCallingScript() . "$add_parameter&constraints=" . $data["question_id"] . "\">" . $this->lng->txt("questionblock_has_constraints") . "</a>");
					}
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("QUESTION_ID", "qb_" . $data["questionblock_id"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				if ($data["heading"])
				{
					$this->tpl->setCurrentBlock("heading");
					$this->tpl->setVariable("TEXT_HEADING", $data["heading"]);
					$this->tpl->setVariable("COLOR_CLASS", "std");
					if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
						$this->tpl->setVariable("TEXT_EDIT", "<img src=\"" . ilUtil::getImagePath("icon_pencil.gif") . "\" alt=\"" . $this->lng->txt("edit") . "\" title=\"" . $this->lng->txt("edit") . "\" border=\"0\" />");
						$this->tpl->setVariable("HREF_EDIT", $this->getCallingScript() . "$add_parameter&editheading=" . $data["question_id"]);
						$this->tpl->setVariable("TEXT_DELETE", "<img src=\"" . ilUtil::getImagePath("delete.gif") . "\" alt=\"" . $this->lng->txt("remove") . "\" title=\"" . $this->lng->txt("remove") . "\" border=\"0\" />");
						$this->tpl->setVariable("HREF_DELETE", $this->getCallingScript() . "$add_parameter&removeheading=" . $data["question_id"]);
					}
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
//					$this->tpl->setVariable("QUESTION_ID", "qb_" . $data["questionblock_id"]);
					$this->tpl->setVariable("COLOR_CLASS", "std");
					$this->tpl->parseCurrentBlock();
				}
				if (!$data["questionblock_id"])
				{
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("QTab");
				$ref_id = SurveyQuestion::_getRefIdFromObjId($data["obj_fi"]);
				if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) 
				{
					$q_id = $data["question_id"];
					$qpl_ref_id = $this->object->_getRefIdFromObjId($data["obj_fi"]);
					$this->tpl->setVariable("QUESTION_TITLE", "$title_counter. <a href=\"" . $this->getCallingScript() . $add_parameter . "&eqid=$q_id&eqpl=$qpl_ref_id" . "\">" . $data["title"] . "</a>");
//					$this->tpl->setVariable("QUESTION_TITLE", "$title_counter. <a href=\"questionpool.php?ref_id=" . $ref_id . "&cmd=questions&edit=" . $data["question_id"] . "\">" . $data["title"] . "</a>");
				}
				else
				{
					$this->tpl->setVariable("QUESTION_TITLE", "$title_counter. ". $data["title"]);
				}

				if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) 
				{
					$obligatory_checked = "";
					if ($data["obligatory"] == 1)
					{
						$obligatory_checked = " checked=\"checked\"";
					}
					$this->tpl->setVariable("QUESTION_OBLIGATORY", "<input type=\"checkbox\" name=\"obligatory_" . $data["question_id"] . "\" value=\"1\"$obligatory_checked />");
				}
				else
				{
					if ($data["obligatory"] == 1)
					{
						$this->tpl->setVariable("QUESTION_OBLIGATORY", $obligatory);
					}
				}
				$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
				if ($rbacsystem->checkAccess("write", $this->ref_id) and ($this->object->isOffline())) {
					if (!$data["questionblock_id"])
					{
						if ($data["question_id"] != $this->object->questions[0])
						{
							$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->getCallingScript() . "$add_parameter&up=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"Up\" border=\"0\" /></a>");
						}
						if ($data["question_id"] != $this->object->questions[count($this->object->questions)-1])
						{
							$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->getCallingScript() . "$add_parameter&down=" . $data["question_id"] . "\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"Down\" border=\"0\" /></a>");
						}
					}
				}
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
				if (count($data["constraints"]) and (strcmp($data["questionblock_id"], "") == 0))
				{
					$this->tpl->setVariable("QUESTION_CONSTRAINTS", "<a href=\"" . $this->getCallingScript() . "$add_parameter&constraints=" . $data["question_id"] . "\">" . $this->lng->txt("question_has_constraints") . "</a>");
				}
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				if (!$data["questionblock_id"])
				{
					$counter++;
				}
				$this->tpl->parseCurrentBlock();
				$last_questionblock_id = $data["questionblock_id"];
			}
		}

		$checked_move = 0;
		if ($_POST["cmd"]["move"])
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_(\d+)/", $key, $matches))
				{
					$checked_move++;
					$this->tpl->setCurrentBlock("move");
					$this->tpl->setVariable("MOVE_COUNTER", $matches[1]);
					$this->tpl->setVariable("MOVE_VALUE", $matches[1]);
					$this->tpl->parseCurrentBlock();
				}
				if (preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					$checked_move++;
					$ids = $this->object->getQuestionblockQuestionIds($matches[1]);
					foreach ($ids as $qkey => $qid)
					{
						$this->tpl->setCurrentBlock("move");
						$this->tpl->setVariable("MOVE_COUNTER", $qid);
						$this->tpl->setVariable("MOVE_VALUE", $qid);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			if ($checked_move)
			{
				sendInfo($this->lng->txt("select_target_position_for_move_question"));
				$this->tpl->setCurrentBlock("move_buttons");
				$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
				$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				sendInfo($this->lng->txt("no_question_selected_for_move"));
			}
		}


		if ($counter == 0) {
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		} else {
	    if ($rbacsystem->checkAccess("write", $this->ref_id) and (!$this->object->getStatus() == STATUS_ONLINE)) {
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
				$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
				$this->tpl->setVariable("SAVE", $this->lng->txt("save_obligatory_state"));
				$this->tpl->setVariable("QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
				$this->tpl->setVariable("UNFOLD", $this->lng->txt("unfold"));
				$this->tpl->setVariable("CONSTRAINTS", $this->lng->txt("constraints"));
				$this->tpl->setVariable("HEADING", $this->lng->txt("add_heading"));
				$this->tpl->parseCurrentBlock();
			}
		}

    if ($rbacsystem->checkAccess("write", $this->ref_id) and (!$this->object->getStatus() == STATUS_ONLINE)) {
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM survey_questiontype";
			$query_result = $this->ilias->db->query($query);
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_OBLIGATORY", $this->lng->txt("obligatory"));
		$this->tpl->setVariable("QUESTION_CONSTRAINTS", $this->lng->txt("constraints"));
		$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("sequence"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_EDIT", $this->lng->txt("edit"));

    if ($rbacsystem->checkAccess("write", $this->ref_id) and (!$this->object->getStatus() == STATUS_ONLINE)) {
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("browse_for_questions"));
			$this->tpl->setVariable("BUTTON_SEARCH_QUESTION", $this->lng->txt("search_questions"));
			$this->tpl->setVariable("TEXT_OR", " " . strtolower($this->lng->txt("or")));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
		}
		if ($this->object->getStatus() == STATUS_ONLINE)
		{
			sendInfo($this->lng->txt("survey_online_warning"));
		}

		$this->tpl->parseCurrentBlock();
	}

	function printEvaluationObject()
	{
		$tpl = new ilTemplate("./survey/templates/default/tpl.il_svy_svy_evaluation_preview.html", true, true);
		$row_classes = array("tblrow1", "tblrow2");
		if (!$_GET[$this->lng->txt("question")]) {
			foreach ($_SESSION["print_eval"] as $key => $value)
			{
				if ($key == 0)
				{
					$tpl->setCurrentBlock("titlecol");
					for ($i = 0; $i < count($value); $i++)
					{
						$tpl->setVariable("TXT_TITLE", $value[$i]);
						$tpl->parseCurrentBlock();
					}
				}
				else if (preg_match("/\d+/", $key))
				{
					$tpl->setCurrentBlock("datacol");
					for ($i = 0; $i < count($value); $i++)
					{
						$tpl->setVariable("TXT_DATA", $value[$i]);
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("row");
					$tpl->setVariable("ROW_CLASS", $row_classes[$key % 2]);
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setCurrentBlock("heading");
			$tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("svy_statistical_evaluation") . " " . $this->lng->txt("of") . " " . $this->object->getTitle());
			$tpl->setVariable("PRINT_CSS", "./templates/default/print.css");
			$tpl->setVariable("PRINT_TYPE", "summary");
			$tpl->parseCurrentBlock();
			$tpl->show();
		}
		else
		{
			foreach ($_SESSION[$this->lng->txt("question").$_GET[$this->lng->txt("question")]] as $key => $value)
			{
				$i=0;
				$row_num = 1;
				while ($i < count($value))
				{
					$tpl->setCurrentBlock("detail_title");
					$tpl->setVariable("TXT_TITLE", $value[$i++]);
					$tpl->setVariable("TXT_DATA", $value[$i++]);
					$tpl->parseCurrentBlock();

					$tpl->setCurrentBlock("row");
					$tpl->setVariable("ROW_CLASS", $row_classes[$row_num % 2]);
					$tpl->parseCurrentBlock();
					$row_num++;
				}
			}
			$tpl->setCurrentBlock("heading");
			$tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("svy_statistical_evaluation") . " " . $this->lng->txt("of") . " " . $this->object->getTitle());
			$tpl->setVariable("PRINT_CSS", "./templates/default/print.css");
			$tpl->parseCurrentBlock();
			$tpl->show();
		}
		exit();
	}

	function evaluationuserObject()
	{
	}
	
	function evaluationdetailsObject()
	{
		$this->evaluationObject();
	}
	
	/**
	* Creates the evaluation form
	*
	* Creates the evaluation form
	*
	* @access	public
	*/
	function evaluationObject()
	{
		global $ilUser;

		require_once './classes/Spreadsheet/Excel/Writer.php';
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";

		$object_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->object->getTitle());
		$surveyname = preg_replace("/\s/", "_", $object_title);

		if (!$_POST["export_format"])
		{
			$_POST["export_format"] = TYPE_PRINT;
		}
		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Creating a workbook
				$workbook = new Spreadsheet_Excel_Writer();

				// sending HTTP headers
				$workbook->send("$surveyname.xls");

				// Creating a worksheet
				$format_bold =& $workbook->addFormat();
				$format_bold->setBold();
				$format_percent =& $workbook->addFormat();
				$format_percent->setNumFormat("0.00%");
				$format_datetime =& $workbook->addFormat();
				$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
				$format_title =& $workbook->addFormat();
				$format_title->setBold();
				$format_title->setColor('black');
				$format_title->setPattern(1);
				$format_title->setFgColor('silver');
				// Creating a worksheet
				$mainworksheet =& $workbook->addWorksheet();
				$mainworksheet->writeString(0, 0, $this->lng->txt("title"), $format_bold);
				$mainworksheet->writeString(0, 1, $this->lng->txt("question"), $format_bold);
				$mainworksheet->writeString(0, 2, $this->lng->txt("question_type"), $format_bold);
				$mainworksheet->writeString(0, 3, $this->lng->txt("users_answered"), $format_bold);
				$mainworksheet->writeString(0, 4, $this->lng->txt("users_skipped"), $format_bold);
				$mainworksheet->writeString(0, 5, $this->lng->txt("mode"), $format_bold);
				$mainworksheet->writeString(0, 6, $this->lng->txt("mode_text"), $format_bold);
				$mainworksheet->writeString(0, 7, $this->lng->txt("mode_nr_of_selections"), $format_bold);
				$mainworksheet->writeString(0, 8, $this->lng->txt("median"), $format_bold);
				$mainworksheet->writeString(0, 9, $this->lng->txt("arithmetic_mean"), $format_bold);
				break;
			case (TYPE_SPSS || TYPE_PRINT):
				$csvfile = array();
				$csvrow = array();
				array_push($csvrow, $this->lng->txt("title"));
				array_push($csvrow, $this->lng->txt("question"));
				array_push($csvrow, $this->lng->txt("question_type"));
				array_push($csvrow, $this->lng->txt("users_answered"));
				array_push($csvrow, $this->lng->txt("users_skipped"));
				array_push($csvrow, $this->lng->txt("mode"));

				//array_push($csvrow, $this->lng->txt("mode_text"));


				array_push($csvrow, $this->lng->txt("mode_nr_of_selections"));
				array_push($csvrow, $this->lng->txt("median"));
				array_push($csvrow, $this->lng->txt("arithmetic_mean"));
				array_push($csvfile, $csvrow);
				break;
		}

    $add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_svy_svy_content.html", true);
		$this->setEvalTabs();
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();
		$this->setLocator();
		$this->tpl->setVariable("HEADER", $title);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", true);
		$counter = 0;
		$classes = array("tblrow1", "tblrow2");
		$questions =& $this->object->getSurveyQuestions();
		foreach ($questions as $data)
		{
			$eval = $this->object->getEvaluation($data["question_id"], $ilUser->id);
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
			$maxlen = 37;
			if (strlen($data["questiontext"]) > $maxlen + 3)
			{
				$questiontext = substr($data["questiontext"], 0, $maxlen) . "...";
			}
			else
			{
				$questiontext = $data["questiontext"];
			}
			$this->tpl->setVariable("QUESTION_TEXT", $questiontext);
			$this->tpl->setVariable("USERS_ANSWERED", $eval["USERS_ANSWERED"]);
			$this->tpl->setVariable("USERS_SKIPPED", $eval["USERS_SKIPPED"]);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($eval["QUESTION_TYPE"]));
			$this->tpl->setVariable("MODE", $eval["MODE"]);
			$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
			$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
			$this->tpl->setVariable("ARITHMETIC_MEAN", $eval["ARITHMETIC_MEAN"]);
			$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
			switch ($_POST["export_format"])
			{
				case TYPE_XLS:
					$mainworksheet->writeString($counter+1, 0, $data["title"]);
					$mainworksheet->writeString($counter+1, 1, $data["questiontext"]);
					$mainworksheet->writeString($counter+1, 2, $this->lng->txt($eval["QUESTION_TYPE"]));
					$mainworksheet->write($counter+1, 3, $eval["USERS_ANSWERED"]);
					$mainworksheet->write($counter+1, 4, $eval["USERS_SKIPPED"]);
					preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
					switch ($eval["QUESTION_TYPE"])
					{
						case "qt_metric":
							$mainworksheet->write($counter+1, 5, $eval["MODE"]);
							$mainworksheet->write($counter+1, 6, $eval["MODE"]);
							break;
						default:
							$mainworksheet->write($counter+1, 5, $matches[1]);
							$mainworksheet->write($counter+1, 6, $matches[2]);
							break;
					}
					$mainworksheet->write($counter+1, 7, $eval["MODE_NR_OF_SELECTIONS"]);
					$mainworksheet->write($counter+1, 8, $eval["MEDIAN"]);
					$mainworksheet->write($counter+1, 9, $eval["ARITHMETIC_MEAN"]);
					break;
				case (TYPE_SPSS || TYPE_PRINT):
					$csvrow = array();
					array_push($csvrow, $data["title"]);
					array_push($csvrow, $data["questiontext"]);
					array_push($csvrow, $this->lng->txt($eval["QUESTION_TYPE"]));
					array_push($csvrow, $eval["USERS_ANSWERED"]);
					array_push($csvrow, $eval["USERS_SKIPPED"]);
					array_push($csvrow, $eval["MODE"], $matches);
					array_push($csvrow, $eval["MODE_NR_OF_SELECTIONS"]);
					array_push($csvrow, $eval["MEDIAN"]);
					array_push($csvrow, $eval["ARITHMETIC_MEAN"]);
					array_push($csvfile, $csvrow);
					break;
			}
			$this->tpl->parseCurrentBlock();
			if ($_GET["details"])
			{
				$printDetail = array();
				switch ($_POST["export_format"])
				{
					case TYPE_XLS:
						$worksheet =& $workbook->addWorksheet();
						$worksheet->writeString(0, 0, $this->lng->txt("title"), $format_bold);
						$worksheet->writeString(0, 1, $data["title"]);
						$worksheet->writeString(1, 0, $this->lng->txt("question"), $format_bold);
						$worksheet->writeString(1, 1, $data["questiontext"]);
						$worksheet->writeString(2, 0, $this->lng->txt("question_type"), $format_bold);
						$worksheet->writeString(2, 1, $this->lng->txt($eval["QUESTION_TYPE"]));
						$worksheet->writeString(3, 0, $this->lng->txt("users_answered"), $format_bold);
						$worksheet->write(3, 1, $eval["USERS_ANSWERED"]);
						$worksheet->writeString(4, 0, $this->lng->txt("users_skipped"), $format_bold);
						$worksheet->write(4, 1, $eval["USERS_SKIPPED"]);
						$rowcounter = 5;
						break;
					case TYPE_PRINT:
						array_push($printDetail, $this->lng->txt("title"));
						array_push($printDetail, $data["title"]);
						array_push($printDetail, $this->lng->txt("question"));
						array_push($printDetail, $data["questiontext"]);
						array_push($printDetail, $this->lng->txt("question_type"));
						array_push($printDetail, $this->lng->txt($eval["QUESTION_TYPE"]));
						array_push($printDetail, $this->lng->txt("users_answered"));
						array_push($printDetail, $eval["USERS_ANSWERED"]);
						array_push($printDetail, $this->lng->txt("users_skipped"));
						array_push($printDetail, $eval["USERS_SKIPPED"]);
						break;
				}
				$this->tpl->setCurrentBlock("detail");
				$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_QUESTION_TEXT", $this->lng->txt("question"));
				$this->tpl->setVariable("QUESTION_TEXT", $data["questiontext"]);
				$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("question_type"));
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($eval["QUESTION_TYPE"]));
				$this->tpl->setVariable("TEXT_USERS_ANSWERED", $this->lng->txt("users_answered"));
				$this->tpl->setVariable("USERS_ANSWERED", $eval["USERS_ANSWERED"]);
				$this->tpl->setVariable("TEXT_USERS_SKIPPED", $this->lng->txt("users_skipped"));
				$this->tpl->setVariable("USERS_SKIPPED", $eval["USERS_SKIPPED"]);
				switch ($eval["QUESTION_TYPE"])
				{
					case "qt_ordinal":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode"), $format_bold);
								$worksheet->write($rowcounter++, 1, $matches[1]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_text"), $format_bold);
								$worksheet->write($rowcounter++, 1, $matches[2]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_nr_of_selections"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MODE_NR_OF_SELECTIONS"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("median"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MEDIAN"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("categories"), $format_bold);
								$worksheet->write($rowcounter, 1, $this->lng->txt("title"), $format_title);
								$worksheet->write($rowcounter, 2, $this->lng->txt("value"), $format_title);
								$worksheet->write($rowcounter, 3, $this->lng->txt("category_nr_selected"), $format_title);
								$worksheet->write($rowcounter++, 4, $this->lng->txt("percentage_of_selections"), $format_title);
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_MEDIAN", $this->lng->txt("median"));
						$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
						$this->tpl->setVariable("TEXT_CATEGORIES", $this->lng->txt("categories"));
						$categories = "";
						foreach ($eval["variables"] as $key => $value)
						{
							$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
								$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
								$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
										$worksheet->write($rowcounter, 1, $value["title"]);
										$worksheet->write($rowcounter, 2, $key+1);
										$worksheet->write($rowcounter, 3, $value["selected"]);
										$worksheet->write($rowcounter++, 4, $value["percentage"], $format_percent);
										break;
								}
						}
						$categories = "<ol>$categories</ol>";
						$this->tpl->setVariable("VALUE_CATEGORIES", $categories);
						
						// display chart for ordinal question for array $eval["variables"]
						$this->tpl->setVariable("TEXT_CHART", "Chart");
						$this->tpl->setVariable("CHART", "<img src=\"\">");
						
						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("median"));
								array_push($printDetail, $eval["MEDIAN"]);
								array_push($printDetail, $this->lng->txt("categories"));
								array_push($printDetail, $categories);
								break;
						}
						break;
					case "qt_nominal":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode"), $format_bold);
								$worksheet->write($rowcounter++, 1, $matches[1]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_text"), $format_bold);
								$worksheet->write($rowcounter++, 1, $matches[2]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_nr_of_selections"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MODE_NR_OF_SELECTIONS"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("categories"), $format_bold);
								$worksheet->write($rowcounter, 1, $this->lng->txt("title"), $format_title);
								$worksheet->write($rowcounter, 2, $this->lng->txt("value"), $format_title);
								$worksheet->write($rowcounter, 3, $this->lng->txt("category_nr_selected"), $format_title);
								$worksheet->write($rowcounter++, 4, $this->lng->txt("percentage_of_selections"), $format_title);
								break;
						}
						array_push($printDetail, $this->lng->txt("subtype"));
						$this->tpl->setVariable("TEXT_QUESTION_SUBTYPE", $this->lng->txt("subtype"));
						switch ($data["subtype"])
						{
							case SUBTYPE_MCSR:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("multiple_choice_single_response"));
								array_push($printDetail, $this->lng->txt("multiple_choice_single_response"));
								break;
							case SUBTYPE_MCMR:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("multiple_choice_multiple_response"));
								array_push($printDetail, $this->lng->txt("multiple_choice_multiple_response"));
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_CATEGORIES", $this->lng->txt("categories"));
						$categories = "";
						foreach ($eval["variables"] as $key => $value)
						{
							$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
								$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
								$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
							switch ($_POST["export_format"])
							{
								case TYPE_XLS:
									$worksheet->write($rowcounter, 1, $value["title"]);
									$worksheet->write($rowcounter, 2, $key+1);
									$worksheet->write($rowcounter, 3, $value["selected"]);
									$worksheet->write($rowcounter++, 4, $value["percentage"], $format_percent);
									break;
							}
						}
						$categories = "<ol>$categories</ol>";
						$this->tpl->setVariable("VALUE_CATEGORIES", $categories);

						// display chart for nominal question for array $eval["variables"]

						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("categories"));
								array_push($printDetail, $categories);
								break;
						}
						break;
					case "qt_metric":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								$worksheet->write($rowcounter, 0, $this->lng->txt("subtype"), $format_bold);
								switch ($data["subtype"])
								{
									case SUBTYPE_NON_RATIO:
										$worksheet->write($rowcounter++, 1, $this->lng->txt("non_ratio"), $format_bold);
										break;
									case SUBTYPE_RATIO_NON_ABSOLUTE:
										$worksheet->write($rowcounter++, 1, $this->lng->txt("ratio_non_absolute"), $format_bold);
										break;
									case SUBTYPE_RATIO_ABSOLUTE:
										$worksheet->write($rowcounter++, 1, $this->lng->txt("ratio_absolute"), $format_bold);
										break;
								}
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MODE"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_text"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MODE"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("mode_nr_of_selections"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MODE_NR_OF_SELECTIONS"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("median"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["MEDIAN"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("arithmetic_mean"), $format_bold);
								$worksheet->write($rowcounter++, 1, $eval["ARITHMETIC_MEAN"]);
								$worksheet->write($rowcounter, 0, $this->lng->txt("values"), $format_bold);
								$worksheet->write($rowcounter, 1, $this->lng->txt("value"), $format_title);
								$worksheet->write($rowcounter, 2, $this->lng->txt("category_nr_selected"), $format_title);
								$worksheet->write($rowcounter++, 3, $this->lng->txt("percentage_of_selections"), $format_title);
								break;
						}
						$this->tpl->setVariable("TEXT_QUESTION_SUBTYPE", $this->lng->txt("subtype"));
						array_push($printDetail, $this->lng->txt("subtype"));
						switch ($data["subtype"])
						{
							case SUBTYPE_NON_RATIO:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("non_ratio"));
								array_push($printDetail, $this->lng->txt("non_ratio"));
								break;
							case SUBTYPE_RATIO_NON_ABSOLUTE:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("ratio_non_absolute"));
								array_push($printDetail, $this->lng->txt("ratio_non_absolute"));
								break;
							case SUBTYPE_RATIO_ABSOLUTE:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("ratio_absolute"));
								array_push($printDetail, $this->lng->txt("ratio_absolute"));
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_MEDIAN", $this->lng->txt("median"));
						$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
						$this->tpl->setVariable("TEXT_ARITHMETIC_MEAN", $this->lng->txt("arithmetic_mean"));
						$this->tpl->setVariable("ARITHMETIC_MEAN", $eval["ARITHMETIC_MEAN"]);
						$this->tpl->setVariable("TEXT_VALUES", $this->lng->txt("values"));
						$values = "";
						foreach ($eval["values"] as $key => $value)
						{
							$values .= "<li>" . $this->lng->txt("value") . ": " . "<span class=\"bold\">" . $value["value"] . "</span><br />" .
								$this->lng->txt("value_nr_entered") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
								$this->lng->txt("percentage_of_entered_values") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
							switch ($_POST["export_format"])
							{
								case TYPE_XLS:
									$worksheet->write($rowcounter, 1, $value["value"]);
									$worksheet->write($rowcounter, 2, $value["selected"]);
									$worksheet->write($rowcounter++, 3, $value["percentage"], $format_percent);
									break;
							}
						}
						$values = "<ol>$values</ol>";
						$this->tpl->setVariable("VALUE_VALUES", $values);

						// display chart for metric question for array $eval["values"]

						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("median"));
								array_push($printDetail, $eval["MEDIAN"]);
								array_push($printDetail, $this->lng->txt("values"));
								array_push($printDetail, $values);
								break;
						}
						break;
					case "qt_text":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								$worksheet->write($rowcounter, 0, $this->lng->txt("given_answers"), $format_bold);
								break;
						}
						$this->tpl->setVariable("TEXT_TEXTVALUES", $this->lng->txt("given_answers"));
						$textvalues = "";
						foreach ($eval["textvalues"] as $textvalue)
						{
							$textvalues .= "<li>" . preg_replace("/\n/", "<br>", $textvalue) . "</li>";
							switch ($_POST["export_format"])
							{
								case TYPE_XLS:
									$worksheet->write($rowcounter++, 1, $textvalue);
									break;
							}
						}
						$textvalues = "<ul>$textvalues</ul>";
						$this->tpl->setVariable("VALUE_TEXTVALUES", $textvalues);
						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("given_answers"));
								array_push($printDetail, $textvalues);
								break;
						}
						break;
				}

				if ($_POST["export_format"]==TYPE_PRINT)
				{
					$printdetail_file = array();
					array_push($printdetail_file, $printDetail);
					$s_question = $counter+1;
					$_SESSION[$this->lng->txt("question").$s_question] = $printdetail_file;
					$this->tpl->setVariable("PRINT_ACTION", $this->getCallingScript() . "?ref_id=" . $_GET["ref_id"] . "&cmd=printEvaluation&".$this->lng->txt("question")."=".$s_question);
					$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
					$this->tpl->setVariable("PRINT_IMAGE", ilUtil::getImagePath("icon_print.gif"));
				}
				$this->tpl->parseCurrentBlock();
			}
			$counter++;
		}
		if ($_POST["export_format"]==TYPE_PRINT)
		{
			$_SESSION["print_eval"] = $csvfile;
		}


		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Let's send the file
				$workbook->close();
				exit();
				break;
			case TYPE_SPSS:
				$csv = "";
				foreach ($csvfile as $csvrow)
				{
					$csv .= join($csvrow, ",") . "\n";
				}
				ilUtil::deliverData($csv, "$surveyname.csv");
				exit();
				break;
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("QUESTION_TEXT", $this->lng->txt("question"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("USERS_ANSWERED", $this->lng->txt("users_answered"));
		$this->tpl->setVariable("USERS_SKIPPED", $this->lng->txt("users_skipped"));
		$this->tpl->setVariable("MODE", $this->lng->txt("mode"));
		$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
		$this->tpl->setVariable("MEDIAN", $this->lng->txt("median"));
		$this->tpl->setVariable("ARITHMETIC_MEAN", $this->lng->txt("arithmetic_mean"));
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("export_data_as"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("excel"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("csv"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Extracts the results of a posted invitation form
	*
	* Extracts the results of a posted invitation form
	*
	* @access	public
	*/
	function writeInviteFormData()
	{
		global $ilUser;

		$message = "";
		$this->object->setInvitationAndMode($_POST["invitation"], $_POST["mode"]);
		if ($_POST["cmd"]["disinvite"])
		{
			// disinvite users
			if (is_array($_POST["invited_users"]))
			{
				foreach ($_POST["invited_users"] as $user_id)
				{
					$this->object->disinviteUser($user_id);
				}
			}
			// disinvite groups
			if (is_array($_POST["invited_groups"]))
			{
				foreach ($_POST["invited_groups"] as $group_id)
				{
					$this->object->disinviteGroup($group_id);
				}
			}
		}

		if ($_POST["cmd"]["add"])
		{
			// add users to invitation
			if (is_array($_POST["user_select"]))
			{
				foreach ($_POST["user_select"] as $user_id)
				{
					$this->object->inviteUser($user_id);
				}
			}
			// add groups to invitation
			if (is_array($_POST["group_select"]))
			{
				foreach ($_POST["group_select"] as $group_id)
				{
					$this->object->inviteGroup($group_id);
				}
			}
		}

		if ($_POST["cmd"]["search"])
		{
			if (is_array($_POST["search_for"]))
			{
				if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]))
				{
					$search =& new ilSearch($ilUser->id);
					$search->setSearchString($_POST["search_term"]);
					$search->setCombination($_POST["concatenation"]);
					$search->setSearchFor($_POST["search_for"]);
					$search->setSearchType("new");
					if($search->validate($message))
					{
						$search->performSearch();
					}
					if ($message)
					{
						sendInfo($message);
					}
					if(!$search->getNumberOfResults() && $search->getSearchFor())
					{
						sendInfo($this->lng->txt("search_no_match"));
						return;
					}
					$buttons = array("add");
					$invited_users = $this->object->getInvitedUsers();
					if ($searchresult = $search->getResultByType("usr"))
					{
						$users = array();
						foreach ($searchresult as $result_array)
						{
							if (!in_array($result_array["id"], $invited_users))
							{
								array_push($users, $result_array["id"]);
							}
						}
						$this->outUserGroupTable("usr", $users, "user_result", "user_row", $this->lng->txt("search_user"), $buttons);
					}
					$searchresult = array();
					$invited_groups = $this->object->getInvitedGroups();
					if ($searchresult = $search->getResultByType("grp"))
					{
						$groups = array();
						foreach ($searchresult as $result_array)
						{
							if (!in_array($result_array["id"], $invited_groups))
							{
								array_push($groups, $result_array["id"]);
							}
						}
						$this->outUserGroupTable("grp", $groups, "group_result", "group_row", $this->lng->txt("search_group"), $buttons);
					}
				}
			}
			else
			{
				sendInfo($this->lng->txt("no_user_or_group_selected"));
			}
		}
	}

	/**
	* Creates the search output for the user/group search form
	*
	* Creates the search output for the user/group search form
	*
	* @access	public
	*/
	function outUserGroupTable($a_type, $id_array, $block_result, $block_row, $title_text, $buttons)
	{
		global $rbacsystem;
		
		$rowclass = array("tblrow1", "tblrow2");
		switch($a_type)
		{
			case "usr":
				foreach ($id_array as $user_id)
				{
					$counter = 0;
					$user = new ilObjUser($user_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $user->getId());
					$this->tpl->setVariable("VALUE_LOGIN", $user->getLogin());
					$this->tpl->setVariable("VALUE_FIRSTNAME", $user->getFirstname());
					$this->tpl->setVariable("VALUE_LASTNAME", $user->getLastname());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_USER_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				if ($rbacsystem->checkAccess('invite', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "grp":
				foreach ($id_array as $group_id)
				{
					$counter = 0;
					$group = new ilObjGroup($group_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $group->getRefId());
					$this->tpl->setVariable("VALUE_TITLE", $group->getTitle());
					$this->tpl->setVariable("VALUE_DESCRIPTION", $group->getDescription());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_GROUP_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_grp_b.gif") . "\" alt=\"\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess('invite', $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				}
				$this->tpl->parseCurrentBlock();
				break;
		}
	}

	/**
	* Creates the output for user/group invitation to a survey
	*
	* Creates the output for user/group invitation to a survey
	*
	* @access	public
	*/
	function inviteObject()
	{
		global $rbacsystem;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_invite.html", true);

		if ($this->object->getStatus() == STATUS_OFFLINE)
		{
			$this->tpl->setCurrentBlock("survey_offline");
			$this->tpl->setVariable("SURVEY_OFFLINE_MESSAGE", $this->lng->txt("survey_offline_message"));
			$this->tpl->parseCurrentBlock();
			return;
		}
		if ($_POST["cmd"]["cancel"])
		{
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel",ILIAS_HTTP_PATH."/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}
		if (count($_POST))
		{
			$this->writeInviteFormData();
		}
		if ($_POST["cmd"]["save"])
		{
			$this->object->saveToDb();
		}
		if (($this->object->getInvitationMode() == MODE_PREDEFINED_USERS) and ($this->object->getInvitation() == INVITATION_ON))
		{
			if ($rbacsystem->checkAccess('invite', $this->ref_id))
			{
				$this->tpl->setCurrentBlock("invitation");
				$this->tpl->setVariable("SEARCH_INVITATION", $this->lng->txt("search_invitation"));
				$this->tpl->setVariable("SEARCH_TERM", $this->lng->txt("search_term"));
				$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("search_for"));
				$this->tpl->setVariable("SEARCH_USERS", $this->lng->txt("search_users"));
				$this->tpl->setVariable("SEARCH_GROUPS", $this->lng->txt("search_groups"));
				$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("concatenation"));
				$this->tpl->setVariable("TEXT_AND", $this->lng->txt("and"));
				$this->tpl->setVariable("TEXT_OR", $this->lng->txt("or"));
				$this->tpl->setVariable("VALUE_SEARCH_TERM", $_POST["search_term"]);
				if (is_array($_POST["search_for"]))
				{
					if (in_array("usr", $_POST["search_for"]))
					{
						$this->tpl->setVariable("CHECKED_USERS", " checked=\"checked\"");
					}
					if (in_array("grp", $_POST["search_for"]))
					{
						$this->tpl->setVariable("CHECKED_GROUPS", " checked=\"checked\"");
					}
				}
				if (strcmp($_POST["concatenation"], "and") == 0)
				{
					$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
				}
				else if (strcmp($_POST["concatenation"], "or") == 0)
				{
					$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
				}
				$this->tpl->setVariable("SEARCH", $this->lng->txt("search"));
				$this->tpl->parseCurrentBlock();
			}
		}
		if ($this->object->getInvitationMode() == MODE_PREDEFINED_USERS)
		{
			$invited_users = $this->object->getInvitedUsers();
			$invited_groups = $this->object->getInvitedGroups();
			$buttons = array("disinvite");
			if (count($invited_users))
			{
				$this->outUserGroupTable("usr", $invited_users, "invited_user_result", "invited_user_row", $this->lng->txt("invited_users"), $buttons);
			}
			if (count($invited_groups))
			{
				$this->outUserGroupTable("grp", $invited_groups, "invited_group_result", "invited_group_row", $this->lng->txt("invited_groups"), $buttons);
			}
		}
		if ($this->object->getInvitation() == INVITATION_ON)
		{
			$this->tpl->setCurrentBlock("invitation_mode");
			$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("invitation_mode"));
			$this->tpl->setVariable("VALUE_UNLIMITED", $this->lng->txt("unlimited_users"));
			$this->tpl->setVariable("VALUE_PREDEFINED", $this->lng->txt("predefined_users"));
			if ($this->object->getInvitationMode() == MODE_PREDEFINED_USERS)
			{
				$this->tpl->setVariable("SELECTED_PREDEFINED", " selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("SELECTED_UNLIMITED", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_INVITATION", $this->lng->txt("invitation"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($this->object->getInvitation() == INVITATION_ON)
		{
			$this->tpl->setVariable("SELECTED_ON", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFF", " selected=\"selected\"");
		}
    if ($rbacsystem->checkAccess("write", $this->ref_id) or $rbacsystem->checkAccess('invite', $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates the maintenance form for a survey
	*
	* Creates the maintenance form for a survey
	*
	* @access	public
	*/
	function maintenanceObject()
	{
		global $rbacsystem;
		
		if ($_POST["cmd"]["delete_all_user_data"])
		{
			$this->object->deleteAllUserData();
			sendInfo($this->lng->txt("svy_all_user_data_deleted"));
		}
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_maintenance.html", true);
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("BTN_DELETE_ALL", $this->lng->txt("svy_delete_all_user_data"));
			$this->tpl->setVariable("FORM_ACTION", $this->getCallingScript() . $add_parameter);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo($this->lng->txt("cannot_maintain_survey"));
		}
	}	

	/**
	* Creates the status output for a test
	*
	* Creates the status output for a test
	*
	* @access	public
	*/
	function statusObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_status.html", true);
		if (!$this->object->isComplete())
		{
			if (count($this->object->questions) == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("svy_missing_questions"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->author, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("svy_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->title, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("svy_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("status_list");
			$this->tpl->setVariable("TEXT_MISSING_ELEMENTS", $this->lng->txt("svy_status_missing_elements"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isComplete())
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("svy_status_ok"));
			$this->tpl->setVariable("STATUS_CLASS", "bold");
		}
		else
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("svy_status_missing"));
			$this->tpl->setVariable("STATUS_CLASS", "warning");
		}
		$this->tpl->parseCurrentBlock();
	}	

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php")
	{
		//		global $ilias_locator;
	  $ilias_locator = new ilLocatorGUI(false);
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);
		//check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;
		if (!defined("ILIAS_MODULE")) {
			foreach ($path as $key => $row)
			{
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/adm_object.php?ref_id=".$row["child"],"");
			}
		} else {
			foreach ($path as $key => $row)
			{
				if (strcmp($row["title"], "ILIAS") == 0) {
					$row["title"] = $this->lng->txt("repository");
				}
				if ($this->ref_id == $row["child"]) {
					if ($_GET["cmd"]) {
						$param = "&cmd=" . $_GET["cmd"];
					} else {
						$param = "";
					}
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/survey/survey.php" . "?ref_id=".$row["child"] . $param,"target=\"bottom\"");
				} else {
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/" . $scriptname."?ref_id=".$row["child"],"target=\"bottom\"");
				}
			}

			if (isset($_GET["obj_id"]))
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
				$ilias_locator->navigate($i++,$obj_data->getTitle(),$scriptname."?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"target=\"bottom\"");
			}
		}
    $ilias_locator->output();
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function permObject()
	{
		global $rbacsystem, $rbacreview;

		static $num = 0;

		if (!$rbacsystem->checkAccess("edit_permission", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
			exit();
		}

		// only display superordinate roles; local roles with other scope are not displayed
		$parentRoles = $rbacreview->getParentRoleIds($this->object->getRefId());

		$data = array();

		// GET ALL LOCAL ROLE IDS
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());

		$local_roles = array();

		if ($role_folder)
		{
			$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
		}

		foreach ($parentRoles as $key => $r)
		{
			if ($r["obj_id"] == SYSTEM_ROLE_ID)
			{
				unset($parentRoles[$key]);
				continue;
			}

			if (!in_array($r["obj_id"],$local_roles))
			{
				$data["check_inherit"][] = ilUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
			}
			else
			{
				$r["link"] = true;

				// don't display a checkbox for local roles AND system role
				if ($rbacreview->isAssignable($r["obj_id"],$role_folder["ref_id"]))
				{
					$data["check_inherit"][] = "&nbsp;";
				}
				else
				{
					// linked local roles with stopped inheritance
					$data["check_inherit"][] = ilUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}

			$data["roles"][] = $r;
		}

		$ope_list = getOperationList($this->object->getType());

		// BEGIN TABLE_DATA_OUTER
		foreach ($ope_list as $key => $operation)
		{
			$opdata = array();

			$opdata["name"] = $operation["operation"];

			$colspan = count($parentRoles) + 1;

			foreach ($parentRoles as $role)
			{
				$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
				$disabled = false;

				// Es wird eine 2-dim Post Variable bergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"],$disabled);
				$opdata["values"][] = $box;
			}

			$data["permission"][] = $opdata;
		}

		/////////////////////
		// START DATA OUTPUT
		/////////////////////

		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("permission_settings"));
		$this->tpl->setVariable("COLSPAN", $colspan);
		$this->tpl->setVariable("TXT_OPERATION", $this->lng->txt("operation"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;

		foreach($data["roles"] as $role)
		{
			// BLOCK ROLENAMES
			if ($role["link"])
			{
				$this->tpl->setCurrentBlock("ROLELINK_OPEN");
				$this->tpl->setVariable("LINK_ROLE_RULESET",$this->getTabTargetScript()."?ref_id=".$role_folder["ref_id"]."&obj_id=".$role["obj_id"]."&cmd=perm");
				$this->tpl->setVariable("TXT_ROLE_RULESET",$this->lng->txt("edit_perm_ruleset"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->touchBlock("ROLELINK_CLOSE");
			}

			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$role["title"]);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			if ($this->objDefinition->stopInheritance($this->type))
			{
				$this->tpl->setCurrentBLock("CHECK_INHERIT");
				$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num]);
				$this->tpl->parseCurrentBlock();
			}

			$num++;
		}

		// save num for required column span and the end of parsing
		$colspan = $num + 1;
		$num = 0;

		// offer option 'stop inheritance' only to those objects where this option is permitted
		if ($this->objDefinition->stopInheritance($this->type))
		{
			$this->tpl->setCurrentBLock("STOP_INHERIT");
			$this->tpl->setVariable("TXT_STOP_INHERITANCE", $this->lng->txt("stop_inheritance"));
			$this->tpl->parseCurrentBlock();
		}

		foreach ($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $this->lng->txt($this->object->getType()."_".$ar_perm["name"]));
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}

		// ADD LOCAL ROLE - Skip that until I know how it works with the module folder
		if (false)
		// if ($this->object->getRefId() != ROLE_FOLDER_ID and $rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			$this->tpl->setCurrentBlock("LOCAL_ROLE");

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]."&cmd=addRole"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("addRole"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();
		}

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("permSave",$this->getTabTargetScript()."?".$this->link_params."&cmd=permSave"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("COL_ANZ",$colspan);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* save permissions
	*
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin;

		// first save the new permission settings for all roles
		$rbacadmin->revokePermission($this->ref_id);

		if (is_array($_POST["perm"]))
		{
			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$this->ref_id);
			}
		}

		// update object data entry (to update last modification date)
		$this->object->update();

		// get rolefolder data if a rolefolder already exists
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);
		$rolf_id = $rolf_data["child"];

		if ($_POST["stop_inherit"])
		{
			// rolefolder does not exist, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			// CHECK ACCESS write of role folder
			if (!$rbacsystem->checkAccess("write",$rolf_id))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
			}

			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_id);

				// create role entries for roles with stopped inheritance
				if (!in_array($stop_inherit,$roles_of_folder))
				{
					$parentRoles = $rbacreview->getParentRoleIds($rolf_id);
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_id,$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
			}// END FOREACH
		}// END STOP INHERIT
		elseif 	(!empty($rolf_id))
		{
			// TODO: this feature doesn't work at the moment
			// ok. if the rolefolder is not empty, delete the local roles
			//if (!empty($roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_data["ref_id"])));
			//{
				//foreach ($roles_of_folder as $obj_id)
				//{
					//$rolfObj =& $this->ilias->obj_factory->getInstanceByRefId($rolf_data["child"]);
					//$rolfObj->delete();
					//unset($rolfObj);
				//}
			//}
		}

		sendinfo($this->lng->txt("saved_successfully"),true);
		ilUtil::redirect($this->getReturnLocation("permSave",$this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]."&cmd=perm"));
	}

	function editMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			$this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

		function saveMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		if (!strcmp($_POST["meta_section"], "General")) {
			$meta = $_POST["meta"];
			$this->object->setTitle(ilUtil::stripSlashes($meta["Title"]["Value"]));
			$this->object->setDescription(ilUtil::stripSlashes($meta["Description"][0]["Value"]));
			$this->object->update();
		}
		ilUtil::redirect($this->getTabTargetScript()."?ref_id=".$_GET["ref_id"]);
	}

	// called by administration
	function chooseMetaSectionObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_REQUEST["meta_section"]);
	}

	// called by editor
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}

	function addMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_name = $_POST["meta_name"] ? $_POST["meta_name"] : $_GET["meta_name"];
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		if ($meta_index == "")
			$meta_index = 0;
		$meta_path = $_POST["meta_path"] ? $_POST["meta_path"] : $_GET["meta_path"];
		$meta_section = $_POST["meta_section"] ? $_POST["meta_section"] : $_GET["meta_section"];
		if ($meta_name != "")
		{
			$meta_gui->meta_obj->add($meta_name, $meta_path, $meta_index);
		}
		else
		{
			sendInfo($this->lng->txt("meta_choose_element"), true);
		}
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $meta_section);
	}

	function addMeta()
	{
		$this->addMetaObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}

	function deleteMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = $this->getTabTargetScript()."?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_GET["meta_section"]);
	}

	function deleteMeta()
	{
		$this->deleteMetaObject($this->getTabTargetScript()."?ref_id=".
			$this->object->getRefId());
	}

	/*function prepareOutput()
	{
		$this->tpl->addBlockFile("JAVASCRIPT_EDITOR", "javascript", "tpl.il_svy_editor_javascript.html", true);
		$this->tpl->setCurrentBlock("javascript");
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$this->tpl->setVariable("LOCATION_JAVASCRIPT",dirname($location_stylesheet));
		$this->tpl->setVariable("TEXTAREA_NAME", "introduction");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("BODY_ONLOAD", " onload=\"initDocument()\"");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setAdminTabs($_POST["new_type"]);
		$this->setLocator();

	}
*/

	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;

		//$this->setTabs();

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", "survey.php?ref_id=".$_GET["ref_id"]."&cmd=createExportFile");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("svy_create_export_file"));
		$this->tpl->parseCurrentBlock();

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);

		// create table
		require_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", true);

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "survey.php?cmd=gateway&ref_id=".$_GET["ref_id"]);

		$tbl->setTitle($this->lng->txt("svy_export_files"));

		$tbl->setHeaderNames(array("<input type=\"checkbox\" name=\"chb_check_all\" value=\"1\" onclick=\"setCheckboxes('ObjectItems', 'file', document.ObjectItems.chb_check_all.checked);\" />", $this->lng->txt("svy_file"),
			$this->lng->txt("svy_size"), $this->lng->txt("date") ));

		$tbl->enabled["sort"] = false;
		$tbl->setColumnWidth(array("1%", "49%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 4);

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "downloadExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", filesize($export_dir."/".$exp_file));
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file);

				$file_arr = explode("__", $exp_file);
				$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* create export file
	*/
	function createExportFileObject()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			require_once("./survey/classes/class.ilSurveyExport.php");
			$survey_exp = new ilSurveyExport($this->object);
			$survey_exp->buildExportFile();
			ilUtil::redirect("survey.php?cmd=export&ref_id=".$_GET["ref_id"]);
		}
		else
		{
			sendInfo("cannot_export_survey");
		}
	}

	/**
	* display dialogue for importing tests
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import", "svy");
		$this->tpl->setCurrentBlock("option_qpl");
		require_once("./survey/classes/class.ilObjSurvey.php");
		$svy = new ilObjSurvey();
		$questionpools =& $svy->getAvailableQuestionpools(true);
		if (count($questionpools) == 0)
		{
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option_spl");
				$this->tpl->setVariable("OPTION_VALUE", $key);
				$this->tpl->setVariable("TXT_OPTION", $value);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool"));
		$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=".$this->type);
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_TST", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject($redirect = true)
	{
		if ($_POST["spl"] < 1)
		{
			sendInfo($this->lng->txt("svy_select_questionpools"));
			$this->importObject();
			return;
		}
		include_once("./survey/classes/class.ilObjSurvey.php");
		$newObj = new ilObjSurvey();
		$newObj->setType($_GET["new_type"]);
		$newObj->setTitle("dummy");
		$newObj->setDescription("dummy");
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// copy uploaded file to import directory
		$newObj->importObject($_FILES["xmldoc"], $_POST["spl"]);

		/* update title and description in object data */
		if (is_object($newObj->meta_data))
		{
			$newObj->meta_data->read();
			$newObj->meta_data->setTitle($newObj->getTitle());
			$newObj->meta_data->setDescription($newObj->getDescription());
			ilObject::_writeTitle($newObj->getID(), $newObj->getTitle());
			ilObject::_writeDescription($newObj->getID(), $newObj->getDescription());
		}

		$newObj->update();
		$newObj->saveToDb();
		if ($redirect)
		{
			ilUtil::redirect("adm_object.php?".$this->link_params);
		}
	}

	/**
	* form for new content object creation
	*/
	function createObject()
	{
		global $rbacsystem;
		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->getTemplateFile("create", $new_type);

			require_once("./survey/classes/class.ilObjSurvey.php");
			$svy = new ilObjSurvey();
			
			$surveys =& ilObjSurvey::_getAvailableSurveys(true);
			if (count($surveys) > 0)
			{
				foreach ($surveys as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_svy");
					$this->tpl->setVariable("OPTION_VALUE_SVY", $key);
					$this->tpl->setVariable("TXT_OPTION_SVY", $value);
					if ($_POST["svy"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED_SVY", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$questionpools =& $svy->getAvailableQuestionpools(true);
			if (count($questionpools) > 0)
			{
				foreach ($questionpools as $key => $value)
				{
					$this->tpl->setCurrentBlock("option_spl");
					$this->tpl->setVariable("OPTION_VALUE", $key);
					$this->tpl->setVariable("TXT_OPTION", $value);
					if ($_POST["spl"] == $key)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");				
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["desc"]);

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																	   $_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_short"));
			$this->tpl->setVariable("OPTION_SELECT_QUESTIONPOOL", $this->lng->txt("select_questionpool_option"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TXT_IMPORT_SVY", $this->lng->txt("import_svy"));
			$this->tpl->setVariable("TXT_SVY_FILE", $this->lng->txt("svy_upload_file"));
			$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

			$this->tpl->setVariable("TXT_DUPLICATE_SVY", $this->lng->txt("duplicate_svy"));
			$this->tpl->setVariable("TXT_SELECT_SVY", $this->lng->txt("obj_svy"));
			$this->tpl->setVariable("OPTION_SELECT_SVY", $this->lng->txt("select_svy_option"));
			$this->tpl->setVariable("TXT_DUPLICATE", $this->lng->txt("duplicate"));
		}
	}
	
	/**
	* form for new test object duplication
	*/
	function cloneAllObject()
	{
		if ($_POST["svy"] < 1)
		{
			sendInfo($this->lng->txt("svy_select_surveys"));
			$this->createObject();
			return;
		}
		require_once "./survey/classes/class.ilObjSurvey.php";
		ilObjSurvey::_clone($_POST["svy"]);
		ilUtil::redirect($this->getCallingScript() . "?".$this->link_params);
	}
	
	/**
	* form for new survey object import
	*/
	function importFileObject()
	{
		if ($_POST["spl"] < 1)
		{
			sendInfo($this->lng->txt("svy_select_questionpools"));
			$this->createObject();
			return;
		}
		if (strcmp($_FILES["xmldoc"]["tmp_name"], "") == 0)
		{
			sendInfo($this->lng->txt("svy_select_file_for_import"));
			$this->createObject();
			return;
		}
		$this->uploadObject(false);
		ilUtil::redirect($this->getCallingScript() . "?".$this->link_params);
	}

	/**
	* download export file
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}


		$export_dir = $this->object->getExportDirectory();
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		//$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", true);

		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", "survey.php?cmd=gateway&ref_id=".$_GET["ref_id"]);

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("TEXT_CONTENT", $file);
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDeleteExportFile"  => $this->lng->txt("cancel"),
			"deleteExportFile"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		session_unregister("ilExportFiles");
		ilUtil::redirect("survey.php?cmd=export&ref_id=".$_GET["ref_id"]);
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$exp_file = $export_dir."/".$file;
			$exp_dir = $export_dir."/".substr($file, 0, strlen($file) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		ilUtil::redirect("survey.php?cmd=export&ref_id=".$_GET["ref_id"]);
	}

	function setEvalTabs()
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$tabs_gui->addTarget("svy_eval_cumulated", $this->getCallingScript() . "?ref_id=" . $_GET["ref_id"] . "&cmd=evaluation", "evaluation",	"ilobjsurveygui");
		$tabs_gui->addTarget("svy_eval_detail", $this->getCallingScript() . "?ref_id=" . $_GET["ref_id"] . "&cmd=evaluationdetails" . "&details=1", "evaluationdetails",	"ilobjsurveygui");
		$tabs_gui->addTarget("svy_eval_user", $this->getCallingScript() . "?ref_id=" . $_GET["ref_id"] . "&cmd=evaluationuser", "evaluationuser",	"ilobjsurveygui");
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}
	
	
} // END class.ilObjSurveyGUI
?>
