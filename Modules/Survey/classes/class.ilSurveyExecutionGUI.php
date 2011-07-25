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
	var $preview;
	
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

		// stay in preview mode
		$this->preview = (bool)$_REQUEST["prvw"];
		$this->ctrl->saveParameter($this, "prvw");
		$this->ctrl->saveParameter($this, "pgov");
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		if (strlen($cmd) == 0)
		{
			$this->ctrl->setParameter($this, "qid", $_GET["qid"]);
			$this->ctrl->redirect($this, "gotoPage");
		}
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

		if($this->preview)
		{
			unset($_SESSION["preview_data"]); 
		}
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
					$_SESSION["anonymous_id"][$this->object->getId()] = $anonymize_key;
				}
				else
				{
					unset($_POST["cmd"]["resume"]);
					ilUtil::sendFailure(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]));
				}
			}
		}
		
		$direction = 0;

		if ($this->object->getAnonymize() && !$this->object->isAccessibleWithoutCode())
		{
			if ($this->object->checkSurveyCode($_POST["anonymous_id"]))
			{
				$_SESSION["anonymous_id"][$this->object->getId()] = $_POST["anonymous_id"];
			}
			else
			{
				ilUtil::sendFailure(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $_POST["anonymous_id"]), true);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
		}
		if ($this->object->isAccessibleWithoutCode())
		{
			$anonymous_id = $this->object->getUserSurveyCode($ilUser->getId());
			if (strlen($anonymous_id))
			{
				$_SESSION["anonymous_id"][$this->object->getId()] = $anonymous_id;
			}
			else
			{
				$_SESSION["anonymous_id"][$this->object->getId()] = $this->object->createNewAccessCode();
			}
		}
		
		$activepage = "";
		if ($resume)
		{
			$activepage = $this->object->getLastActivePage($_SESSION["finished_id"][$this->object->getId()]);
			$direction = 0;
		}
		// explicitly set the survey started!
		if ($this->object->isSurveyStarted($ilUser->getId(), $_SESSION["anonymous_id"][$this->object->getId()]) === FALSE)
		{
			$_SESSION["finished_id"][$this->object->getId()] = $this->object->startSurvey($ilUser->getId(), $_SESSION["anonymous_id"][$this->object->getId()]);
		}
		if (strlen($activepage)) $this->ctrl->setParameter($this, "qid", $activepage);
		$this->ctrl->setParameter($this, "activecommand", "default");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	* Called when a user answered a page to perform a redirect after POST.
	* This is called for security reasons to prevent users sending a form twice.
	*
	* @access public
	*/
	function redirectQuestion()
	{
		switch ($_GET["activecommand"])
		{
			case "next":
				$this->outSurveyPage($_GET["qid"], $_GET["direction"]);
				break;
			case "previous":
				$this->outSurveyPage($_GET["qid"], $_GET["direction"]);
				break;
			case "gotoPage":
				$this->outSurveyPage($_GET["qid"], $_GET["direction"]);
				break;
			case "default":
				$this->outSurveyPage($_GET["qid"]);
				break;
			default:
				// don't save input, go to the first page
				$this->outSurveyPage();
				break;
		}
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
		$result = $this->saveUserInput("previous");
		$this->ctrl->setParameter($this, "activecommand", "previous");
		$this->ctrl->setParameter($this, "qid", $_GET["qid"]);
		if (strlen($result))
		{
			$this->ctrl->setParameter($this, "direction", "0");
		}
		else
		{
			$this->ctrl->setParameter($this, "direction", "-1");
		}
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Navigates to the next pages
*
* @access private
*/
	function next()
	{
		$result = $this->saveUserInput("next");
		$this->ctrl->setParameter($this, "activecommand", "next");
		$this->ctrl->setParameter($this, "qid", $_GET["qid"]);
		if (strlen($result))
		{
			$this->ctrl->setParameter($this, "direction", "0");
		}
		else
		{
			$this->ctrl->setParameter($this, "direction", "1");
		}
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
	/**
	* Go to a specific page without saving
	*
	* @access private
	*/
	function gotoPage()
	{
		$this->ctrl->setParameter($this, "activecommand", "gotoPage");
		$this->ctrl->setParameter($this, "qid", $_GET["qid"]);
		$this->ctrl->setParameter($this, "direction", "0");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Output of the active survey question to the screen
*
* Output of the active survey question to the screen
*
* @access private
*/
	function outSurveyPage($activepage = NULL, $direction = NULL)
	{
		global $ilUser,$rbacsystem;

		if(!$this->preview)
		{
			// security check if someone tries to go into a survey using an URL to one of the questions
			$canStart = $this->object->canStartSurvey($_SESSION["anonymous_id"][$this->object->getId()]);
			if (!$canStart["result"])
			{
				ilUtil::sendInfo(implode("<br />", $canStart["messages"]), TRUE);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
			$survey_started = $this->object->isSurveyStarted($ilUser->getId(), $_SESSION["anonymous_id"][$this->object->getId()]);
			if ($survey_started === FALSE)
			{
				ilUtil::sendInfo($this->lng->txt("survey_use_start_button"), TRUE);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
		}
		else if (!$rbacsystem->checkAccess("write", $this->object->ref_id))
		{
			// only with write access it is possible to preview the survey
			$this->ilias->raiseError($this->lng->txt("survey_cannot_preview_survey"),$this->ilias->error_obj->MESSAGE);
		}

		$page = $this->object->getNextPage($activepage, $direction);
		$constraint_true = 0;

		// check for constraints
		if (count($page[0]["constraints"]))
		{
			while (is_array($page) and ($constraint_true == 0) and (count($page[0]["constraints"])))
			{
				$constraint_true = ($page[0]['constraints'][0]['conjunction'] == 0) ? true : false;
				foreach ($page[0]["constraints"] as $constraint)
				{
					if(!$this->preview)
					{					
						$working_data = $this->object->loadWorkingData($constraint["question"], $_SESSION["finished_id"][$this->object->getId()]);
					}
					else
					{												
						$working_data = $_SESSION["preview_data"][$this->object->getId()][$constraint["question"]];												
					}
					if ($constraint['conjunction'] == 0)
					{
						// and
						$constraint_true = $constraint_true & $this->object->checkConstraint($constraint, $working_data);
					}
					else
					{
						// or
						$constraint_true = $constraint_true | $this->object->checkConstraint($constraint, $working_data);
					}
				}
				if ($constraint_true == 0)
				{
					$page = $this->object->getNextPage($page[0]["question_id"], $direction);
				}
			}
		}

		$first_question = -1;
		if ($page === 0)
		{
			$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
		}
		else if ($page === 1)
		{
			$this->object->finishSurvey($ilUser->id, $_SESSION["anonymous_id"][$this->object->getId()]);
			if (array_key_exists("anonymous_id", $_SESSION)) unset($_SESSION["anonymous_id"][$this->object->getId()]);
			$this->runShowFinishedPage();
			return;
		}
		else
		{
			$required = false;
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", "Modules/Survey");

			if (!($this->object->getAnonymize() && $this->object->isAccessibleWithoutCode() && ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)))
			{
				$this->tpl->setCurrentBlock("suspend_survey");

				if(!$this->preview)
				{
					$this->tpl->setVariable("TEXT_SUSPEND", $this->lng->txt("cancel_survey"));
					$this->tpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
					$this->tpl->setVariable("HREF_IMG_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));
				}
				else
				{
					$this->ctrl->setParameterByClass("ilObjSurveyGUI", "pgov", $_REQUEST["pgov"]);
					$this->tpl->setVariable("TEXT_SUSPEND", $this->lng->txt("survey_cancel_preview"));
					$this->tpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "questions"));
					$this->tpl->setVariable("HREF_IMG_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "questions"));
				}
				
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
			if (count($page) > 1 && $page[0]["questionblock_show_blocktitle"])
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
				if ($first_question == -1) $first_question = $data["question_id"];
				$question_gui = $this->object->getQuestionGUI($data["type_tag"], $data["question_id"]);
				if (is_array($_SESSION["svy_errors"]))
				{
					$working_data =& $question_gui->object->getWorkingDataFromUserInput($_SESSION["postdata"]);
				}
				else
				{
					$working_data = $this->object->loadWorkingData($data["question_id"], $_SESSION["finished_id"][$this->object->getId()]);
				}
				$question_gui->object->setObligatory($data["obligatory"]);
				$error_messages = array();
				if (is_array($_SESSION["svy_errors"]))
				{
					$error_messages = $_SESSION["svy_errors"];
				}
				$show_questiontext = ($data["questionblock_show_questiontext"]) ? 1 : 0;
				$question_output = $question_gui->getWorkingForm($working_data, $this->object->getShowQuestionTitles(), $show_questiontext, $error_messages[$data["question_id"]], $this->object->getSurveyId());
				$this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
				$this->ctrl->setParameter($this, "qid", $data["question_id"]);
				$this->tpl->parse("survey_content");
				if ($data["obligatory"]) $required = true;
			}
			if ($required)
			{
				$this->tpl->setCurrentBlock("required");
				$this->tpl->setVariable("TEXT_REQUIRED", $this->lng->txt("required_field"));
				$this->tpl->parseCurrentBlock();
			}

			$this->outNavigationButtons("bottom", $page);

			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "redirectQuestion"));
		}

		if(!$this->preview)
		{
			$this->object->setPage($_SESSION["finished_id"][$this->object->getId()], $page[0]['question_id']);
			$this->object->setStartTime($_SESSION["finished_id"][$this->object->getId()], $first_question);
		}
	}
	
	/**
	* Save the user's input
	*
	* @access private
	*/
	function saveUserInput($navigationDirection = "next") 
	{
		global $ilUser;
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_read_survey"),$this->ilias->error_obj->MESSAGE);
		}

		if(!$this->preview)
		{
			$this->object->setEndTime($_SESSION["finished_id"][$this->object->getId()]);
		}
		
		// check users input when it is a metric question
		unset($_SESSION["svy_errors"]);
		$_SESSION["postdata"] = $_POST;
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
				ilUtil::sendFailure($this->lng->txt("svy_page_error"), TRUE);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("svy_page_errors"), TRUE);
			}
		}
		else
		{
			$page_error = "";
			unset($_SESSION["svy_errors"]);
		}
		return $page_error;
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
				ilUtil::sendFailure($this->lng->txt("svy_page_error"));
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("svy_page_errors"));
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
			if(!$this->preview)
			{
				$user_id = $ilUser->getId();
			
				// delete old answers
				$this->object->deleteWorkingData($data["question_id"], $_SESSION["finished_id"][$this->object->getId()]);

				if ($this->object->isSurveyStarted($user_id, $_SESSION["anonymous_id"][$this->object->getId()]) === false)
				{
					$_SESSION["finished_id"][$this->object->getId()] = $this->object->startSurvey($user_id, $_SESSION["anonymous_id"][$this->object->getId()]);
				}
				if ($this->object->getAnonymize())
				{
					$user_id = 0;
				}				
				$question->saveUserInput($_POST, $_SESSION["finished_id"][$this->object->getId()]);
			}
			else
			{
				$_SESSION["preview_data"][$this->object->getId()][$data["question_id"]] = 
					$question->saveUserInput($_POST, $_SESSION["finished_id"][$this->object->getId()], true);
			}
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
		unset($_SESSION["anonymous_id"][$this->object->getId()]);
		if (strlen($this->object->getOutro()) == 0)
		{
			$this->ctrl->redirectByClass("ilobjsurveygui", "backToRepository");
		}
		else
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_finished.html", "Modules/Survey");
			$this->tpl->setVariable("TEXT_FINISHED", $this->object->prepareTextareaOutput($this->object->getOutro()));
			$this->tpl->setVariable("BTN_EXIT", $this->lng->txt("exit"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "runShowFinishedPage"));
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
		if(!$this->preview)
		{
			$this->ctrl->redirectByClass("ilobjsurveygui", "backToRepository");
		}
		else
		{
			$this->ctrl->setParameterByClass("ilobjsurveygui", "pgov", $_REQUEST["pgov"]);
			$this->ctrl->redirectByClass("ilobjsurveygui", "questions");
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

	function preview()
	{
		$this->outSurveyPage();
	}
}
?>