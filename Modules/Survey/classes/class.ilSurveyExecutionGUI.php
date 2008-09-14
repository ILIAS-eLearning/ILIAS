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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Survey execution graphical output
*
* The ilSurveyExecutionGUI class creates the execution output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
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

		unset($_SESSION["svy_errors"]);
		if (!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_read_survey"),$this->ilias->error_obj->MESSAGE);
		}

		if ($this->object->getAnonymize() && !$this->object->isAccessibleWithoutCode())
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
					ilUtil::sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]));
				}
			}
		}
		
		$direction = 0;

		if ($this->object->getAnonymize() && !$this->object->isAccessibleWithoutCode())
		{
			if ($this->object->checkSurveyCode($_POST["anonymous_id"]))
			{
				$_SESSION["anonymous_id"] = $_POST["anonymous_id"];
			}
			else
			{
				ilUtil::sendInfo(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]), true);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
		}
		if ($this->object->isAccessibleWithoutCode())
		{
			$anonymous_id = $this->object->getUserSurveyCode($ilUser->getId());
			if (strlen($anonymous_id))
			{
				$_SESSION["anonymous_id"] = $anonymous_id;
			}
			else
			{
				$_SESSION["anonymous_id"] = $this->object->createNewAccessCode();
			}
		}
		
		$activepage = "";
		if ($resume)
		{
			$activepage = $this->object->getLastActivePage($_SESSION["finished_id"]);
			$direction = 0;
		}
		// explicitly set the survey started!
		if ($this->object->isSurveyStarted($ilUser->getId(), $_SESSION["anonymous_id"]) === FALSE)
		{
			$_SESSION["finished_id"] = $this->object->startSurvey($ilUser->getId(), $_SESSION["anonymous_id"]);
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
		
		// security check if someone tries to go into a survey using an URL to one of the questions
		$canStart = $this->object->canStartSurvey($_SESSION["anonymous_id"]);
		if (!$canStart["result"])
		{
			ilUtil::sendInfo(implode("<br />", $canStart["messages"]), TRUE);
			$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
		}
		$survey_started = $this->object->isSurveyStarted($ilUser->getId(), $_SESSION["anonymous_id"]);
		if ($survey_started === FALSE)
		{
			ilUtil::sendInfo($this->lng->txt("survey_use_start_button"), TRUE);
			$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
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
					$working_data = $this->object->loadWorkingData($constraint["question"], $_SESSION["finished_id"]);
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
			$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
		}
		else if ($page === 1)
		{
			$this->object->finishSurvey($ilUser->id, $_SESSION["anonymous_id"]);
			if (array_key_exists("anonymous_id", $_SESSION)) unset($_SESSION["anonymous_id"]);
			$this->runShowFinishedPage();
			return;
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", "Modules/Survey");

			if (!($this->object->getAnonymize() && $this->object->isAccessibleWithoutCode() && ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)))
			{
				$this->tpl->setCurrentBlock("suspend_survey");
				$this->tpl->setVariable("TEXT_SUSPEND", $this->lng->txt("cancel_survey"));
				$this->tpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
				$this->tpl->setVariable("HREF_IMG_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
				$this->tpl->setVariable("ALT_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
				$this->tpl->setVariable("TITLE_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
				$this->tpl->setVariable("IMG_SUSPEND", ilUtil::getImagePath("cancel.gif"));
				$this->tpl->parseCurrentBlock();
			}
			$this->outNavigationButtons("top", $page);
			$this->tpl->setCurrentBlock("percentage");
			$percentage = (int)(($page[0]["position"])*100);
			$this->tpl->setVariable("PERCENT_BAR_START", ilUtil::getImagePath("bar_start.gif"));
			$this->tpl->setVariable("PERCENT_BAR_FILLED", ilUtil::getImagePath("bar_filled.gif"));
			$this->tpl->setVariable("PERCENT_BAR_EMPTY", ilUtil::getImagePath("bar_empty.gif"));
			$this->tpl->setVariable("PERCENT_BAR_END", ilUtil::getImagePath("bar_end.gif"));
			$this->tpl->setVariable("PERCENTAGE_ALT", $this->lng->txt("percentage"));
			$this->tpl->setVariable("PERCENTAGE_VALUE", $percentage);
			$this->tpl->setVariable("PERCENTAGE_UNFINISHED", 100-$percentage);
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
				if (is_array($_SESSION["svy_errors"]))
				{
					$working_data =& $question_gui->object->getWorkingDataFromUserInput($_POST);
				}
				else
				{
					$working_data = $this->object->loadWorkingData($data["question_id"], $_SESSION["finished_id"]);
				}
				$question_gui->object->setObligatory($data["obligatory"]);
				$error_messages = array();
				if (is_array($_SESSION["svy_errors"]))
				{
					$error_messages = $_SESSION["svy_errors"];
				}
				$show_questiontext = ($data["questionblock_show_questiontext"]) ? 1 : 0;
				$question_output = $question_gui->getWorkingForm($working_data, $this->object->getShowQuestionTitles(), $show_questiontext, $error_messages[$data["question_id"]]);
				$this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
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
		unset($_SESSION["svy_errors"]);
		$page_error = 0;
		$page = $this->object->getNextPage($_GET["qid"], 0);
		foreach ($page as $data)
		{
			$page_error += $this->saveActiveQuestionData($data);
		}
		if ($page_error && (strcmp($navigationDirection, "previous") != 0))
		{
			if ($page_error == 1)
			{
				ilUtil::sendInfo($this->lng->txt("svy_page_error"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("svy_page_errors"));
			}
		}
		else
		{
			$page_error = "";
			unset($_SESSION["svy_errors"]);
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
		
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		$question =& SurveyQuestion::_instanciateQuestion($data["question_id"]);
		$error = $question->checkUserInput($_POST, $this->object->getSurveyId());
		if (strlen($error) == 0)
		{
			$user_id = $ilUser->getId();
			// delete old answers
			$this->object->deleteWorkingData($data["question_id"], $_SESSION["finished_id"]);

			if ($this->object->isSurveyStarted($user_id, $_SESSION["anonymous_id"]) === false)
			{
				$_SESSION["finished_id"] = $this->object->startSurvey($user_id, $_SESSION["anonymous_id"]);
			}
			if ($this->object->getAnonymize())
			{
				$user_id = 0;
			}
			$question->saveUserInput($_POST, $_SESSION["finished_id"]);
			return 0;
		}
		else
		{
			$_SESSION["svy_errors"][$question->getId()] = $error;
			return 1;
		}
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
		$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
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
		unset($_SESSION["anonymous_id"]);
		if (strlen($this->object->getOutro()) == 0)
		{
			$this->ctrl->redirectByClass("ilobjsurveygui", "backToRepository");
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_finished.html", "Modules/Survey");
			$this->tpl->setVariable("TEXT_FINISHED", $this->object->prepareTextareaOutput($this->object->getOutro()));
			$this->tpl->setVariable("BTN_EXIT", $this->lng->txt("exit"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		}
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
