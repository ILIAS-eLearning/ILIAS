<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./survey/classes/inc.SurveyConstants.php";

/**
* Survey execution graphical output
*
* The ilSurveyExecutionGUI class creates the execution output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilSurveyExecutionGUI.php
* @modulegroup   Survey
*/
class ilSurveyExecutionGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;
	
/**
* ilSurveyExecutionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyExecutionGUI object.
*
* @param object $a_object Associated ilObjSurvey class
* @access public
*/
  function ilSurveyExecutionGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

/**
* Retrieves the ilCtrl command
*
* Retrieves the ilCtrl command
*
* @access public
*/
	function getCommand($cmd)
	{
		return $cmd;
	}

/**
* Resumes the survey
*
* Resumes the survey
*
* @access private
*/
	function resume()
	{
		$this->start(true);
	}
	
/**
* Starts the survey
*
* Starts the survey
*
* @access private
*/
	function start($resume = false)
	{
		global $ilUser;
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_read_survey"),$this->ilias->error_obj->MESSAGE);
		}

		if ($this->object->getAnonymize())
		{
			if ($resume)
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
				if (strlen($_SESSION["anonymous_id"]) == 0)
				{
					sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), ""), true);
					$this->ctrl->redirect($this, "run");
				}
			}
		}
		
		$direction = 0;

		if ($this->object->getAnonymize())
		{
			if ($this->object->checkSurveyCode($_POST["anonymous_id"]))
			{
				$_SESSION["anonymous_id"] = $_POST["anonymous_id"];
			}
			else
			{
				sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]), true);
				$this->ctrl->redirect($this, "run");
			}
			if (strlen($_SESSION["anonymous_id"]) == 0)
			{
				sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), ""), true);
				$this->ctrl->redirect($this, "run");
			}
		}
		
		$activepage = "";
		if ($resume)
		{
			$activepage = $this->object->getLastActivePage($ilUser->id);
			$direction = 0;
		}
		$this->outSurveyPage($activepage, $direction);
	}

/**
* Navigates to the previous pages
*
* Navigates to the previous pages
*
* @access private
*/
	function previous()
	{
		$this->navigate("previous");
	}
	
/**
* Navigates to the next pages
*
* Navigates to the next pages
*
* @access private
*/
	function next()
	{
		$this->navigate("next");
	}
	
/**
* Output of the active survey question to the screen
*
* Output of the active survey question to the screen
*
* @access private
*/
	function outSurveyPage($activepage, $direction)
	{
		global $ilUser;
		
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
			$this->object->finishSurvey($ilUser->id, $_SESSION["anonymous_id"]);
			$this->runShowFinishedPage();
			return;
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", true);
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
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK_TITLE", $page[0]["questionblock_title"]);
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
				$error_messages = array();
				if (is_array($_SESSION["svy_errors"]))
				{
					$error_messages = $_SESSION["svy_errors"];
				}
				$question_gui->outWorkingForm($working_data, $this->object->getShowQuestionTitles(), $error_messages[$data["question_id"]]);
				$qid = "&qid=" . $data["question_id"];
				$this->tpl->parse("survey_content");
			}
			$this->outNavigationButtons("bottom", $page);
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this) . $qid);
		}
	}
	
/**
* Survey navigation
*
* Survey navigation
*
* @access private
*/
	function navigate($navigationDirection = "next") 
	{
		global $ilUser;
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_read_survey"),$this->ilias->error_obj->MESSAGE);
		}

		// check users input when it is a metric question
		$page_error = 0;
		$page = $this->object->getNextPage($_GET["qid"], 0);
		foreach ($page as $data)
		{
			$page_error += $this->saveActiveQuestionData($data);
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

		$direction = 0;
		switch ($navigationDirection)
		{
			case "next":
			default:
				$activepage = $_GET["qid"];
				if (!$page_error)
				{
					$direction = 1;
				}
				break;
			case "previous":
				$activepage = $_GET["qid"];
				if (!$page_error)
				{
					$direction = -1;
				}
				break;
		}
		$this->outSurveyPage($activepage, $direction);
	}

/**
* Saves the users input of the active page
*
* Saves the users input of the active page
*
* @access private
*/
	function saveActiveQuestionData(&$data)
	{
		global $ilUser;
		
		$page_error = 0;
		$save_answer = 0;
		$error = 0;
		$error_messages = array();
		unset($_SESSION["svy_errors"]);
		
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
			include_once "./survey/classes/class.SurveyMetricQuestion.php";
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
			include_once "./survey/classes/class.SurveyNominalQuestion.php";
			if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) and ($data["subtype"] == SUBTYPE_MCSR) and ($data["obligatory"]))
			{
				// none of the radio buttons was checked
				$error_messages[$data["question_id"]] = $this->lng->txt("nominal_question_not_checked");
				$error = 1;
			}
			if ((strcmp($_POST[$data["question_id"] . "_value"], "") == 0) and ($data["subtype"] == SUBTYPE_MCSR) and (!$data["obligatory"])) 
			{
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
				include_once "./survey/classes/class.SurveyTextQuestion.php";
				$maxchars = SurveyTextQuestion::_getMaxChars($data["question_id"]);
				if ($maxchars)
				{
					$_POST[$data["question_id"] . "_text_question"] = substr($_POST[$data["question_id"] . "_text_question"], 0, $maxchars);
				}
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
					include_once "./survey/classes/class.SurveyNominalQuestion.php";
					if ($data["subtype"] == SUBTYPE_MCSR)
					{
						$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"], $_POST[$data["question_id"] . "_value"]);
					}
					else
					{
						if (is_array($_POST[$data["question_id"] . "_value"]))
						{
							foreach ($_POST[$data["question_id"] . "_value"] as $value)
							{
								$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"], $value);
							}
						}
						else
						{
							$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"]);
						}
					}
					break;
				case "qt_ordinal":
					$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"], $_POST[$data["question_id"] . "_value"]);
					break;
				case "qt_metric":
					$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"], $_POST[$data["question_id"] . "_metric_question"]);
					break;
				case "qt_text":
					include_once("./classes/class.ilUtil.php");
					$this->object->saveWorkingData($data["question_id"], $ilUser->id, $_SESSION["anonymous_id"], 0, ilUtil::stripSlashes($_POST[$data["question_id"] . "_text_question"]));
					break;
			}
		}
		else
		{
			$_SESSION["svy_errors"] = $error_messages;
		}
		return $page_error;
	}
	
/**
* Creates the form output for running the survey
*
* Creates the form output for running the survey
*
* @access public
*/
	function run() {
		global $ilUser;
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_read_survey"),$this->ilias->error_obj->MESSAGE);
		}

		$this->runShowIntroductionPage();
	}

/**
* Called on cancel
*
* Called on cancel
*
* @access private
*/
	function cancel()
	{
		$this->ctrl->redirectByClass("ilobjsurveygui", "backToRepository");
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
		if (($this->object->getAnonymize()) && (strcmp($ilUser->login, "anonymous") == 0))
		{
			$survey_started = false;
		}
		else
		{
			$survey_started = $this->object->isSurveyStarted($ilUser->id, $this->object->getUserSurveyCode());
		}
		// show introduction page
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_introduction.html", true);
		if ((strcmp($ilUser->login, "anonymous") == 0) && (!$this->object->getAnonymize()))
		{
			$this->tpl->setCurrentBlock("back");
			$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("anonymous_with_personalized_survey"));
			$this->tpl->parseCurrentBlock();
			return;
		}

		if ($this->object->getAnonymize())
		{
			$this->tpl->setCurrentBlock("start");
			$anonymize_key = $this->object->getUserSurveyCode();
			if (strcmp($ilUser->login, "anonymous") == 0)
			{
				$this->tpl->setVariable("TEXT_ANONYMIZE", $this->lng->txt("anonymize_anonymous_introduction"));
			}
			else
			if ($survey_started === 0)
			{
				$this->tpl->setVariable("TEXT_ANONYMIZE", $this->lng->txt("anonymize_resume_introduction"));
			}
			elseif ($survey_started === false)
			{
				$this->tpl->setVariable("TEXT_ANONYMIZE", sprintf($this->lng->txt("anonymize_key_introduction"), $anonymize_key));
			}
			$this->tpl->setVariable("ENTER_ANONYMOUS_ID", $this->lng->txt("enter_anonymous_id"));
			if (strlen($_GET["accesscode"]))
			{
				$this->tpl->setVariable("ANONYMOUS_ID_VALUE", $_GET["accesscode"]);
			}
			else
			{
				$this->tpl->setVariable("ANONYMOUS_ID_VALUE", $anonymize_key);
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("start");
		$canStart = $this->object->canStartSurvey();
		if ($survey_started === 1)
		{
			sendInfo($this->lng->txt("already_completed_survey"));
			$this->tpl->setCurrentBlock("start");
			$this->tpl->setVariable("BTN_START", $this->lng->txt("start_survey"));
			$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
			$this->tpl->parseCurrentBlock();
		}
		if ($survey_started === 0)
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
		if ($survey_started === false)
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
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
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
		unset($_SESSION["anonymous_id"]);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_finished.html", true);
		$this->tpl->setVariable("TEXT_FINISHED", $this->lng->txt("survey_finished"));
		$this->tpl->setVariable("BTN_EXIT", $this->lng->txt("exit"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

/**
* Exits the survey after finishing it
*
* Exits the survey after finishing it
*
* @access public
*/
	function exitSurvey()
	{
		$this->ctrl->redirectByClass("ilobjsurveygui", "backToRepository");
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
}
?>
