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
  function __construct($a_object)
  {
		global $lng, $tpl, $ilCtrl, $tree;

		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->object = $a_object;
		$this->tree = $tree;
				
		$this->external_rater_360 = false;
		if($this->object->get360Mode() &&
			$_SESSION["anonymous_id"][$this->object->getId()] && 
			ilObjSurvey::validateExternalRaterCode($this->object->getRefId(), 
					$_SESSION["anonymous_id"][$this->object->getId()]))
		{
			$this->external_rater_360 = true;
		}

		// stay in preview mode
		$this->preview = (bool)$_REQUEST["prvw"];
		$this->ctrl->saveParameter($this, "prvw");
		$this->ctrl->saveParameter($this, "pgov");
	}
	
	/**
	* execute command
	*/
	function executeCommand()
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
	
	protected function checkAuth($a_may_start = false, $a_ignore_status = false)
	{
		global $rbacsystem, $ilUser;
		
		if($this->preview)
		{
			if(!$rbacsystem->checkAccess("write", $this->object->ref_id))
			{				
				// only with write access it is possible to preview the survey
				include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
				throw new ilSurveyException($this->lng->txt("survey_cannot_preview_survey"));
			}
			
			return true;
		}
						
		if (!$this->external_rater_360 &&
			!$rbacsystem->checkAccess("read", $this->object->ref_id)) 
		{
			// only with read access it is possible to run the test
			include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
			throw new ilSurveyException($this->lng->txt("cannot_read_survey"));
		}
		
		$user_id = $ilUser->getId();

		// check existing code 
		// see ilObjSurveyGUI::infoScreen()
		$anonymous_id = $anonymous_code = null;
		if ($this->object->getAnonymize() || !$this->object->isAccessibleWithoutCode())
		{
			$anonymous_code = $_SESSION["anonymous_id"][$this->object->getId()];		
			$anonymous_id = $this->object->getAnonymousIdByCode($anonymous_code);			
			if(!$anonymous_id)
			{
				ilUtil::sendFailure(sprintf($this->lng->txt("error_retrieving_anonymous_survey"), $anonymous_code, true));
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
		}
		
		// appraisee validation
		$appr_id = 0;
		if($this->object->get360Mode())
		{
			$appr_id = $_REQUEST["appr_id"];
			if(!$appr_id)
			{
				$appr_id = $_SESSION["appr_id"][$this->object->getId()];
			}				
			// check if appraisee is valid
			if($anonymous_id)
			{
				$appraisees = $this->object->getAppraiseesToRate(0, $anonymous_id);
			}
			if(!$appraisees && $user_id != ANONYMOUS_USER_ID)
			{
				$appraisees = $this->object->getAppraiseesToRate($user_id);
			}									
			if(!in_array($appr_id, $appraisees))
			{
				ilUtil::sendFailure($this->lng->txt("survey_360_execution_invalid_appraisee"), true);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}									
		}
		$_SESSION["appr_id"][$this->object->getId()] = $appr_id;
					
		if(!$a_ignore_status)
		{
			$status = $this->object->isSurveyStarted($user_id, $anonymous_code, $appr_id);		
			// completed
			if($status === 1)
			{
				ilUtil::sendFailure($this->lng->txt("already_completed_survey"), true);
				$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
			}
			// starting 
			else if ($status === false)
			{			
				if($a_may_start)
				{								
					$_SESSION["finished_id"][$this->object->getId()] = 
						$this->object->startSurvey($user_id, $anonymous_code, $appr_id);				
				}
				else
				{
					ilUtil::sendFailure($this->lng->txt("survey_use_start_button"), true);
					$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");
				}
			}		
			// resuming
			else
			{
				// nothing todo
			}
		}
		
		// validate finished id
		if($this->object->getActiveID($user_id, $anonymous_code, $appr_id) != 
			$_SESSION["finished_id"][$this->object->getId()])
		{
			ilUtil::sendFailure($this->lng->txt("cannot_read_survey"), true);
			$this->ctrl->redirectByClass("ilobjsurveygui", "infoScreen");			
		}
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
		if($this->preview)
		{
			unset($_SESSION["preview_data"]); 
		}
		unset($_SESSION["svy_errors"]);
		
		$this->checkAuth(!$resume);
				
		$activepage = "";
		if ($resume)
		{
			$activepage = $this->object->getLastActivePage($_SESSION["finished_id"][$this->object->getId()]);
		}
		
		if (strlen($activepage)) 
		{
			$this->ctrl->setParameter($this, "qid", $activepage);
		}
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
	
	function previousNoSave()
	{
		$this->previous(false);
	}

/**
* Navigates to the previous pages
*
* Navigates to the previous pages
*
* @access private
*/
	function previous($a_save_input = true)
	{
		if($a_save_input)
		{
			// #16209
			$has_error = $this->saveUserInput("previous");
		}
		$this->ctrl->setParameter($this, "activecommand", "previous");
		$this->ctrl->setParameter($this, "qid", $_GET["qid"]);
		if (strlen($has_error))
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
		global $ilUser;
		
		$this->checkAuth();			
		
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
					// #11047 - we are skipping the page, so we have to get rid of existing answers for that question(s)
					foreach($page as $page_question)
					{
						$qid = $page_question["question_id"];
												
						// see saveActiveQuestionData()
						if(!$this->preview)
						{							
							$this->object->deleteWorkingData($qid, $_SESSION["finished_id"][$this->object->getId()]);
						}
						else
						{
							$_SESSION["preview_data"][$this->object->getId()][$qid] = null;
						}
					}
					
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
			$state = $this->object->getUserSurveyExecutionStatus();
			if($this->preview || 
				!$state["runs"][$_SESSION["finished_id"][$this->object->getId()]]["finished"])
			{
				$this->showFinishConfirmation();
			}
			else
			{
				$this->runShowFinishedPage();
			}
			return;
		}
		else
		{
			global $ilHelp;
			$ilHelp->setScreenIdComponent("svy");
			$ilHelp->setScreenId("quest_presentation");
			
			if($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				include_once "Services/Tracking/classes/class.ilLearningProgress.php";
				ilLearningProgress::_tracProgress($ilUser->getId(), $this->object->getId(), $this->object->ref_id, "svy");		
			}

			$required = false;
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_content.html", "Modules/Survey");
			
			if($this->object->get360Mode())
			{			
				$appr_id = $_SESSION["appr_id"][$this->object->getId()];
				
				include_once "Services/User/classes/class.ilUserUtil.php";
				$this->tpl->setTitle($this->object->getTitle()." (".
					$this->lng->txt("survey_360_appraisee").": ".
					ilUserUtil::getNamePresentation($appr_id).")");
			}				

			if (!($this->object->getAnonymize() && $this->object->isAccessibleWithoutCode() && ($ilUser->getId() == ANONYMOUS_USER_ID)))
			{
				$this->tpl->setCurrentBlock("suspend_survey");

				if(!$this->preview)
				{
					$this->tpl->setVariable("TEXT_SUSPEND", $this->lng->txt("cancel_survey"));
					$this->tpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "infoScreen"));					
				}
				else
				{
					$this->ctrl->setParameterByClass("ilObjSurveyGUI", "pgov", $_REQUEST["pgov"]);
					$this->tpl->setVariable("TEXT_SUSPEND", $this->lng->txt("survey_cancel_preview"));
					$this->tpl->setVariable("HREF_SUSPEND", $this->ctrl->getLinkTargetByClass(array("ilObjSurveyGUI", "ilSurveyEditorGUI"), "questions"));					
				}
				
				$this->tpl->setVariable("ALT_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
				$this->tpl->setVariable("TITLE_IMG_SUSPEND", $this->lng->txt("cancel_survey"));
				$this->tpl->parseCurrentBlock();
			}
			$this->outNavigationButtons("top", $page);
			
			
			$this->tpl->setCurrentBlock("percentage");
			
			$percentage = (int)(($page[0]["position"])*100);
			
			include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
			$pbar = ilProgressBar::getInstance();
			$pbar->setCurrent($percentage);			
			$this->tpl->setVariable("NEW_PBAR", $pbar->render());
			
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
	/*
	function navigate($navigationDirection = "next") 
	{		
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
*/
	
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
				// delete old answers
				$this->object->deleteWorkingData($data["question_id"], $_SESSION["finished_id"][$this->object->getId()]);
		
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
		global $ilToolbar, $ilUser;
		
		$has_button = false;
		
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
		
		if(!$this->preview)
		{
			if($this->object->hasViewOwnResults())
			{								
				$button = ilLinkButton::getInstance();
				$button->setCaption("svy_view_own_results");								
				$button->setUrl($this->ctrl->getLinkTarget($this, "viewUserResults"));										
				$ilToolbar->addButtonInstance($button);		
			
				$has_button = true;
			}
				
			if($this->object->hasMailConfirmation())
			{
				if($has_button)
				{
					$ilToolbar->addSeparator();
				}

				if($ilUser->getId() == ANONYMOUS_USER_ID ||
					!$ilUser->getEmail())
				{
					require_once "Services/Form/classes/class.ilTextInputGUI.php";								
					$mail = new ilTextInputGUI($this->lng->txt("email"), "mail");
					$mail->setSize(25);									
					$ilToolbar->addInputItem($mail, true);	
				}
								
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "mailUserResults"));
								
				include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
				$button = ilSubmitButton::getInstance();
				$button->setCaption("svy_mail_send_confirmation");
				$button->setCommand("mailUserResults");
				$ilToolbar->addButtonInstance($button);		
				
				$has_button = true;
			}			
			
			// #6307
			include_once "Modules/Survey/classes/class.ilObjSurveyAccess.php";
			if(ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId()))
			{
				$button = ilLinkButton::getInstance();
				$button->setCaption("svy_results");								
				$button->setUrl($this->ctrl->getLinkTargetByClass("ilObjSurveyGUI", "evaluation"));										
				$ilToolbar->addButtonInstance($button);		
			
				$has_button = true;
			}
		}
		
		if (!$has_button &&
			strlen($this->object->getOutro()) == 0)
		{
			$this->exitSurvey();
		}
		else
		{			
			if($has_button)
			{
				$ilToolbar->addSeparator();
			}
			
			$button = ilLinkButton::getInstance();
			$button->setCaption("survey_execution_exit");								
			$button->setUrl($this->ctrl->getLinkTarget($this, "exitSurvey"));										
			$ilToolbar->addButtonInstance($button);		
		
			if(strlen($this->object->getOutro()))
			{	
				include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
				$panel = ilPanelGUI::getInstance();
				$panel->setBody($this->object->prepareTextareaOutput($this->object->getOutro()));
				$this->tpl->setContent($panel->getHTML());			
			}							
		}
	}
	
	function backToRepository()
	{
		global $tree;
		
		// #14971
		if($this->object->get360Mode())
		{
			$target_ref_id = $this->object->getRefId();
		}
		else
		{		
			// #11534
			$target_ref_id = $tree->getParentId($this->object->getRefId());
		}
				
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink::_getLink($target_ref_id));	
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
			$this->backToRepository();
		}
		else
		{
			// #12841
			$this->ctrl->setParameterByClass("ilsurveyeditorgui", "pgov", $_REQUEST["pgov"]);
			$this->ctrl->redirectByClass(array("ilobjsurveygui", "ilsurveyeditorgui"), "questions");
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
	
	function viewUserResults()
	{
		global $ilToolbar;
		
		if(!$this->object->hasViewOwnResults())
		{
			$this->backToRepository();
		}
		
		$this->checkAuth(false, true);		
				
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
		$button = ilLinkButton::getInstance();
		$button->setCaption("btn_back");								
		$button->setUrl($this->ctrl->getLinkTarget($this, "runShowFinishedPage"));										
		$ilToolbar->addButtonInstance($button);		
			
		$survey_gui = new ilObjSurveyGUI();
		$html = $survey_gui->getUserResultsTable($_SESSION["finished_id"][$this->object->getId()]);
		$this->tpl->setContent($html);	
	}
	
	function mailUserResults()
	{		
		if(!$this->object->hasMailConfirmation())
		{
			$this->backToRepository();
		}
		
		$this->checkAuth(false, true);		
		
		$recipient = $_POST["mail"];
		if(!$recipient)
		{
			$recipient = $ilUser->getEmail();
		}
		if(!ilUtil::is_email($recipient))
		{
			$this->ctrl->redirect($this, "runShowFinishedPage");
		}
		
		$survey_gui = new ilObjSurveyGUI();									
		$survey_gui->sendUserResultsMail(
			$_SESSION["finished_id"][$this->object->getId()], 
			$recipient
		);		
		
		ilUtil::sendSuccess($this->lng->txt("mail_sent"), true);
		$this->ctrl->redirect($this, "runShowFinishedPage");
	}
	
	function showFinishConfirmation()
	{
		global $tpl;
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($this->lng->txt("survey_execution_sure_finish"));

		$cgui->setFormAction($this->ctrl->getFormAction($this, "confirmedFinish"));
		$cgui->setCancel($this->lng->txt("cancel"), "previousNoSave");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmedFinish");
		
		$tpl->setContent($cgui->getHTML());
	}
	
	function confirmedFinish()
	{
		global $ilUser;
		
		if(!$this->preview)
		{
			$this->object->finishSurvey($_SESSION["finished_id"][$this->object->getId()]);
						
			if($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";
				ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
			}
									
			if ($this->object->getMailNotification())
			{
				$this->object->sendNotificationMail($ilUser->getId(), 
					$_SESSION["anonymous_id"][$this->object->getId()],
					$_SESSION["appr_id"][$this->object->getId()]);
			}		
		}

		/*
		unset($_SESSION["anonymous_id"][$this->object->getId()]);
		unset($_SESSION["appr_id"][$this->object->getId()]);
		unset($_SESSION["finished_id"][$this->object->getId()]);
		*/ 
			
		$this->ctrl->redirect($this, "runShowFinishedPage");
	}
}
?>