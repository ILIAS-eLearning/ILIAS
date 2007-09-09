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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";

/**
* Output class for assessment test execution
*
* The ilTestOutputGUI class creates the output for the ilObjTestGUI
* class when learners execute a test. This saves some heap space because 
* the ilObjTestGUI class will be much smaller then
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
* @extends ilTestServiceGUI
*/
class ilTestOutputGUI extends ilTestServiceGUI
{
	var $ref_id;

	var $saveResult;
	var $sequence;
	var $cmdCtrl;
	var $maxProcessingTimeReached;
	var $endingTimeReached;

/**
* ilSurveyExecutionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyExecutionGUI object.
*
* @param object $a_object Associated ilObjSurvey class
* @access public
*/
  function ilTestOutputGUI($a_object)
  {
		parent::ilTestServiceGUI($a_object);
		$this->ref_id = $_GET["ref_id"];
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->saveParameter($this, "sequence");
		$this->ctrl->saveParameter($this, "active_id");

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
	 * updates working time and stores state saveresult to see if question has to be stored or not
	 */
	
	function updateWorkingTime() 
	{
		if ($_SESSION["active_time_id"])
		{
			$this->object->updateWorkingTime($_SESSION["active_time_id"]);
		}	
	}	

/**
 * saves the user input of a question
 */
	function saveQuestionSolution()
	{
		$this->updateWorkingTime();
		$this->saveResult = false;
		$formtimestamp = $_POST["formtimestamp"];
		if (strlen($formtimestamp) == 0) $formtimestamp = $_GET["formtimestamp"];
		if ($formtimestamp != $_SESSION["formtimestamp"])
		{
			$_SESSION["formtimestamp"] = $formtimestamp;
		}
		else
		{
			return;
		}
		// save question solution
		if ($this->canSaveResult())
		{
			// but only if the ending time is not reached
			$q_id = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);
			if (is_numeric($q_id)) 
			{
				global $ilUser;
				
				$question_gui = $this->object->createQuestionGUI("", $q_id);
				if ($this->object->getJavaScriptOutput())
				{
					$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				$pass = NULL;
				$active_id = $this->getActiveId();
				if ($this->object->isRandomTest())
				{
					$pass = $this->object->_getPass($active_id);
				}
				$this->saveResult = $question_gui->object->saveWorkingData($active_id, $pass);
			}												
		}
		if ($this->saveResult == FALSE)
		{
			$this->ctrl->setParameter($this, "save_error", "1");
			$_SESSION["previouspost"] = $_POST;
		}
	}
	
	/**
	* Returns TRUE if the answers of the current user could be saved
	*
	* Returns TRUE if the answers of the current user could be saved
	*
	* @return boolean TRUE if the answers could be saved, FALSE otherwise
	* @access private
	*/
	 function canSaveResult() 
	 {
		 return !$this->object->endingTimeReached() && !$this->isMaxProcessingTimeReached() && !$this->isNrOfTriesReached();
	 }
	 
	/**
	* Creates the introduction page for a test
	*
	* Creates the introduction page for a test
	*
	* @access public
	*/
	function outIntroductionPage()
	{
		$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen"); 
	}
	
	/**
	* Checks wheather the maximum processing time is reached or not
	*
	* Checks wheather the maximum processing time is reached or not
	*
	* @return TRUE if the maximum processing time is reached, FALSE otherwise
	* @access public
	*/
	function isMaxProcessingTimeReached() 
	{
		global $ilUser;
		$active_id = $this->getActiveId();
		$starting_time = $this->object->getStartingTimeOfUser($active_id);
		if ($starting_time === FALSE)
		{
			return FALSE;
		}
		else
		{
			return $this->object->isMaxProcessingTimeReached($starting_time);
		}
	}
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function deleteresults() 
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_delete_results_confirm.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_CONFIRM_DELETE_RESULTS", $this->lng->txt("tst_confirm_delete_results"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_delete_results"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Deletes the results of the current user for the active pass
*
* Deletes the results of the current user for the active pass
*
* @access public
*/
	function confirmdeleteresults()
	{
		global $ilUser;
		
		$this->object->deleteResults($ilUser->id);
		ilUtil::sendInfo($this->lng->txt("tst_confirm_delete_results_info"), true);
		$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen"); 
	}
	
/**
* Cancels the deletion of the results of the current user for the active pass
*
* Cancels the deletion of the results of the current user for the active pass
*
* @access public
*/
	function canceldeleteresults()
	{
		$this->ctrl->redirect($this, "outIntroductionPage"); 
	}

/**
* Shows a short result overview in courses
*
* Shows a short result overview in courses
*
* @access public
*/
	function outShortResult($user_question_order) 
	{
		if(!$_GET['crs_show_result'])
		{/*
			$this->tpl->setCurrentBlock("percentage");
			$this->tpl->setVariable("PERCENTAGE", 200);
			$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf($this->lng->txt("tst_position"), $this->sequence, count($user_question_order)));
			$this->tpl->setVariable("HUNDRED_PERCENT", "200");
			$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("percentage_bottom");
			$this->tpl->setVariable("PERCENTAGE", 200);
			$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf($this->lng->txt("tst_position"), $this->sequence, count($user_question_order)));
			$this->tpl->setVariable("HUNDRED_PERCENT", "200");
			$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
			$this->tpl->parseCurrentBlock();*/
		}
		else
		{
			$num_wrong = count($_SESSION['crs_sequence']);
			$pos = 1;
			foreach($_SESSION['crs_sequence'] as $sequence)
			{
				if($sequence == $this->sequence)
				{
					break;
				}
				$pos++;
			}
/*
			$this->tpl->setCurrentBlock("percentage");
			$this->tpl->setVariable("PERCENTAGE", 200);
			$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf($this->lng->txt("tst_position"), $pos, $num_wrong));
			$this->tpl->setVariable("HUNDRED_PERCENT", "200");
			$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("percentage_bottom");
			$this->tpl->setVariable("PERCENTAGE", 200);
			$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf($this->lng->txt("tst_position"), $pos, $num_wrong));
			$this->tpl->setVariable("HUNDRED_PERCENT", "200");
			$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
			$this->tpl->parseCurrentBlock();*/
		}
	}

	/**
	* Creates the learners output of a question
	*
	* Creates the learners output of a question
	*
	* @access public
	*/
	function outWorkingForm($sequence = 1, $finish = false, $test_id, $active, $postpone_allowed, $user_question_order, $directfeedback = 0)
	{
		global $ilUser;
	
		if (is_object($active))
		{
			// create new time dataset and set start time
			$active_time_id = $this->object->startWorkingTime($active->active_id);
			$_SESSION["active_time_id"] = $active_time_id;
		}
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
		ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
		ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();
		$question_gui = $this->object->createQuestionGUI("", $this->object->getQuestionIdFromActiveUserSequence($sequence));
		if ($this->object->getJavaScriptOutput())
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}

		$is_postponed = false;
		if (is_object($active))
		{
			$postponed_array = split(",", $active->postponed);
			$sequence_array = split(",", $active->sequence);
			if ((count($sequence_array)) && ($sequence > 0))
			{
				if (in_array($sequence_array[$sequence-1], $postponed_array))
				{
					$is_postponed = TRUE;
				}
				else
				{
					$is_postponed = FALSE;
				}
			}
		}

		$this->ctrl->setParameter($this, "sequence", "$sequence");
		$formaction = $this->ctrl->getFormAction($this, "redirectQuestion");

		if($_GET['crs_show_result'])
		{
			$question_gui->setSequenceNumber(array_search($sequence,(array) $_SESSION['crs_sequence']) + 1);
			$question_gui->setQuestionCount(count($_SESSION['crs_sequence']));
		}
		else
		{
			$question_gui->setSequenceNumber($sequence);
			$question_gui->setQuestionCount(ilObjTest::_getQuestionCount($this->object->getTestId()));
		}
		// output question
		$user_post_solution = FALSE;
		if (array_key_exists("previouspost", $_SESSION))
		{
			$user_post_solution = $_SESSION["previouspost"];
			unset($_SESSION["previouspost"]);
		}
		if (!is_object($active)) $active = $this->object->getActiveTestUser($ilUser->getId());
		$answer_feedback = FALSE;
		if (($directfeedback) && ($this->object->getAnswerFeedback()))
		{
			$answer_feedback = TRUE;
		}
		global $ilNavigationHistory;
		$ilNavigationHistory->addItem($_GET["ref_id"], $this->ctrl->getLinkTarget($this, "resume"), "tst");
		$question_gui->outQuestionForTest($formaction, $active->active_id, NULL, $is_postponed, $user_post_solution, $answer_feedback);
		if ($directfeedback)
		{
			if ($this->object->getInstantFeedbackSolution())
			{
				$solutionoutput = $question_gui->getSolutionOutput("", NULL, FALSE, FALSE, FALSE);
				$this->tpl->setCurrentBlock("solution_output");
				$this->tpl->setVariable("CORRECT_SOLUTION", $this->lng->txt("tst_best_solution_is"));
				$this->tpl->setVariable("QUESTION_FEEDBACK", $solutionoutput);
				$this->tpl->parseCurrentBlock();
			}
			if ($this->object->getAnswerFeedbackPoints())
			{
				$this->tpl->setCurrentBlock("solution_output");
				$this->tpl->setVariable("RECEIVED_POINTS_INFORMATION", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->calculateReachedPoints($active->active_id, NULL), $question_gui->object->getMaximumPoints()));
				$this->tpl->parseCurrentBlock();
			}
			if ($this->object->getAnswerFeedback())
			{
				$this->tpl->setCurrentBlock("answer_feedback");
				$this->tpl->setVariable("ANSWER_FEEDBACK", $question_gui->getAnswerFeedbackOutput($active->active_id, NULL));
				$this->tpl->parseCurrentBlock();
			}
		}

		// Normally the first sequence is 1
		// In course objective mode it is the first wrongly answered question
		if($_GET['crs_show_result'])
		{
			$first_sequence = $_SESSION['crs_sequence'][0] ? $_SESSION['crs_sequence'][0] : 1;
		}
		else
		{
			$first_sequence = 1;
		}
		if ($sequence == $first_sequence)
		{
			$this->tpl->setCurrentBlock("prev");
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("prev_bottom");
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("prev");
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("prev_bottom");
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
			$this->tpl->parseCurrentBlock();
		}

		if ($postpone_allowed)
		{
			if (!$is_postponed)
			{
				$this->tpl->setCurrentBlock("postpone");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("postpone_bottom");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->getListOfQuestions()) 
		{
			$this->tpl->setCurrentBlock("summary");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("question_summary"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("summary_bottom");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("question_summary"));
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getShowCancel()) 
		{
			$this->tpl->setCurrentBlock("cancel_test");
			$this->tpl->setVariable("TEXT_CANCELTEST", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_ALTCANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_TITLECANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("HREF_IMGCANCELTEST", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("HREF_CANCELTEXT", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("IMAGE_CANCEL", ilUtil::getImagePath("cancel.gif"));
			$this->tpl->parseCurrentBlock();
		}		

		if ($finish)
		{
			if ($this->object->getListOfQuestionsEnd()) 
			{
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("question_summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("question_summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();				
			} 
			else 
			{
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("next");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("next_bottom");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getShowMarker())
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$solved_array = ilObjTest::_getSolvedQuestions($active->active_id, $question_gui->object->getId());
			$solved = 0;
			
			if (count ($solved_array) > 0) 
			{
				$solved = array_pop($solved_array);
				$solved = $solved->solved;
			}			
			
			if ($solved==1) 
			{
				$this->tpl->setCurrentBlock("ismarked");
				$this->tpl->setVariable("TEXT_QUESTION_STATUS_LABEL", $this->lng->txt("tst_question_marked").":");
				$this->tpl->setVariable("TEXT_RESET_MARK", $this->lng->txt("remove"));
				$this->tpl->setVariable("ALT_MARKED", $this->lng->txt("tst_question_marked"));
				$this->tpl->setVariable("TITLE_MARKED", $this->lng->txt("tst_question_marked"));
				$this->tpl->setVariable("MARKED_SOURCE", ilUtil::getImagePath("marked.png"));
				$this->tpl->parseCurrentBlock();
			} 
			else 
			{
				$this->tpl->setCurrentBlock("ismarked");
				$this->tpl->setVariable("TEXT_MARK_QUESTION", $this->lng->txt("tst_question_mark"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($this->object->getJavaScriptOutput())
		{
			$this->tpl->setVariable("JAVASCRIPT_IMAGE", ilUtil::getImagePath("javascript_disable.png"));
			$this->tpl->setVariable("JAVASCRIPT_IMAGE_ALT", $this->lng->txt("disable_javascript"));
			$this->tpl->setVariable("JAVASCRIPT_IMAGE_TITLE", $this->lng->txt("disable_javascript"));
			$this->ctrl->setParameter($this, "tst_javascript", "0");
			$this->tpl->setVariable("JAVASCRIPT_URL", $this->ctrl->getLinkTarget($this, "gotoQuestion"));
		}
		else
		{
			$this->tpl->setVariable("JAVASCRIPT_IMAGE", ilUtil::getImagePath("javascript.png"));
			$this->tpl->setVariable("JAVASCRIPT_IMAGE_ALT", $this->lng->txt("enable_javascript"));
			$this->tpl->setVariable("JAVASCRIPT_IMAGE_TITLE", $this->lng->txt("enable_javascript"));
			$this->ctrl->setParameter($this, "tst_javascript", "1");
			$this->tpl->setVariable("JAVASCRIPT_URL", $this->ctrl->getLinkTarget($this, "gotoQuestion"));
		}

		if ($question_gui->object->supportsJavascriptOutput())
		{
			$this->tpl->touchBlock("jsswitch");
		}

		$this->tpl->setCurrentBlock("adm_content");
		//$this->tpl->setVariable("FORMACTION", $formaction);
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Displays a password protection page when a test password is set
*
* Displays a password protection page when a test password is set
*
* @access public
*/
	function showPasswordProtectionPage()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_password_protection.html", "Modules/Test");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "checkPassword"));
		$this->tpl->setVariable("PASSWORD_INTRODUCTION", $this->lng->txt("tst_password_introduction"));
		$this->tpl->setVariable("TEXT_PASSWORD", $this->lng->txt("tst_password"));
		$this->tpl->setVariable("SUBMIT", $this->lng->txt("submit"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Check the password, a user entered for test access
*
* Check the password, a user entered for test access
*
* @access public
*/
	function checkPassword()
	{
		if (strcmp($this->object->getPassword(), $_POST["password"]) == 0)
		{
			global $ilUser;
			$ilUser->setPref("tst_password_".$this->object->getTestId(), $this->object->getPassword());
			$ilUser->writePref("tst_password_".$this->object->getTestId(), $this->object->getPassword());
			$this->ctrl->redirect($this, "start");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_password_entered_wrong_password"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen"); 
		}
	}
	
/**
* Sets a session variable with the test access code for an anonymous test user
*
* Sets a session variable with the test access code for an anonymous test user
*
* @access public
*/
	function setAnonymousId()
	{
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->object->setAccessCodeSession($_POST["anonymous_id"]);
		}
		$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
	}

/**
* Start a test for the first time
*
* Start a test for the first time. This method contains a lock
* to prevent multiple submissions by the start test button
*
* @access public
*/
	function start()
	{
		if (strcmp($_SESSION["lock"], $_POST["lock"]) != 0)
		{
			$_SESSION["lock"] = $_POST["lock"];
			$this->handleStartCommands();
			$this->ctrl->redirect($this, "startTest");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjtestgui", "redirectToInfoScreen");
		}
	}

/**
* Start a test for the first time after a redirect
*
* Start a test for the first time after a redirect
*
* @access public
*/
	function startTest()
	{
		if ($this->object->checkMaximumAllowedUsers() == FALSE)
		{
			return $this->showMaximumAllowedUsersReachedMessage();
		}
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->object->setAccessCodeSession($this->object->createNewAccessCode());
		}
		else
		{
			$this->object->unsetAccessCodeSession();
		}
		if (strlen($this->object->getPassword()))
		{
			global $ilUser;
			global $rbacsystem;
			
			$pwd = $ilUser->getPref("tst_password_".$this->object->getTestId());

			if ((strcmp($pwd, $this->object->getPassword()) != 0) && (!$rbacsystem->checkAccess("write", $this->object->getRefId())))
			{
				return $this->showPasswordProtectionPage();
			}
		}
		if ($this->object->isRandomTest())
		{
			$this->object->generateRandomQuestions();
			$this->object->loadQuestions();
		}
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->ctrl->redirect($this, "displayCode");
		}
		else
		{
			$this->ctrl->setParameter($this, "activecommand", "start");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
	}
	
	function displayCode()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_anonymous_code_presentation.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_ANONYMOUS_CODE_CREATED", $this->lng->txt("tst_access_code_created"));
		$this->tpl->setVariable("TEXT_ANONYMOUS_CODE", $this->object->getAccessCodeSession());
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CONTINUE", $this->lng->txt("continue_work"));
		$this->tpl->parseCurrentBlock();
	}
	
	function codeConfirmed()
	{
		$this->ctrl->setParameter($this, "activecommand", "start");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Resume a test at the last position
*
* Resume a test at the last position
*
* @access public
*/
	function resume()
	{
		if ($this->object->checkMaximumAllowedUsers() == FALSE)
		{
			return $this->showMaximumAllowedUsersReachedMessage();
		}
		$this->handleStartCommands();
		$this->ctrl->setParameter($this, "activecommand", "resume");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Handles some form parameters on starting and resuming a test
*
* Handles some form parameters on starting and resuming a test
*
* @access public
*/
	function handleStartCommands()
	{
		global $ilUser;

		if ($_POST["chb_javascript"])
		{
			$ilUser->writePref("tst_javascript", 1);
		}
		else
		{
			$ilUser->writePref("tst_javascript", 0);
		}
		
		// hide previous results
		if ($this->object->getNrOfTries() != 1)
		{
			if ($this->object->getUsePreviousAnswers() == 1)
			{
				if ($_POST["chb_use_previous_answers"])
				{
					$ilUser->writePref("tst_use_previous_answers", 1);
				}
				else
				{ 
					$ilUser->writePref("tst_use_previous_answers", 0);
				}
			}
		}
/*		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			global $ilias;
			$ilias->auth->setIdle(0, false);					
		}*/
	}
	
/**
* Called when a user answered a question to perform a redirect after POST
*
* Called when a user answered a question to perform a redirect after POST.
* This is called for security reasons to prevent users sending a form twice.
*
* @access public
*/
	function redirectQuestion()
	{
		global $ilUser;
		
		// check the test restrictions to access the test in case one
		// of the test navigation commands was called by an external script
		// e.g. $ilNavigationHistory
		$executable = $this->object->isExecutable($ilUser->getId());
		if (!$executable["executable"])
		{
			ilUtil::sendInfo($executable["errormessage"], TRUE);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}
		switch ($_GET["activecommand"])
		{
			case "next":
				$this->sequence = $this->calculateSequence();
				// calculate count of questions statically to prevent problems with
				// random tests. If the numer of questions in the used questionpools
				// has been reduced lower than the number of questions which should be
				// chosen, the dynamic method fails because it returns the number of questions
				// that should be chosen. This leds to an error if the test is completed
				$questioncount = ilObjTest::_getQuestionCount($this->object->getTestId());
				if ($this->sequence > $questioncount)
		//			if ($this->sequence > $this->object->getQuestionCount())
				{
					if ($this->object->getListOfQuestionsEnd())
					{
						$this->outQuestionSummary();
					}
					else
					{
						$this->ctrl->redirect($this, "finishTest");
					}
				}
				else
				{
					$this->object->setActiveTestUser($this->sequence);
					$this->outTestPage();
				}
				break;
			case "previous":
				$this->sequence = $this->calculateSequence();
				$this->object->setActiveTestUser($this->sequence);
				if (!$this->sequence)
				{
					$this->ctrl->redirect($this, "outIntroductionPage");
				}
				else
				{
					$this->outTestPage();
				}
				break;
			case "postpone":
				$this->sequence = $this->calculateSequence();	
				$postpone = $this->sequence;
				$this->object->setActiveTestUser($this->sequence, $postpone);
				$this->outTestPage();
				break;
			case "setmarked":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$q_id  = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
				$this->object->setQuestionSetSolved(1, $q_id, $ilUser->getId());
				$this->outTestPage();
				break;
			case "resetmarked":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$q_id  = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
				$this->object->setQuestionSetSolved(0, $q_id, $ilUser->getId());
				$this->outTestPage();
				break;
			case "directfeedback":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$this->outTestPage();
				break;
			case "selectImagemapRegion":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$this->outTestPage();
				break;
			case "summary":
				$this->ctrl->redirect($this, "outQuestionSummary");
				break;
			case "start":
			case "resume":
				$this->sequence = $this->calculateSequence();
				$active_id = $this->object->setActiveTestUser($this->sequence);
				$this->ctrl->setParameter($this, "active_id", $active_id);
				$active_time_id = $this->object->startWorkingTime($active_id);
				$_SESSION["active_time_id"] = $active_time_id;
				$this->readFullSequence();
				if ($this->object->getListOfQuestionsStart())
				{
					$this->outQuestionSummary();
				}
				else
				{
					$this->outTestPage();
				}
				break;
			case "back":
			case "gotoquestion":
			default:
				if (array_key_exists("tst_javascript", $_GET))
				{
					$ilUser->writePref("tst_javascript", $_GET["tst_javascript"]);
				}
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$this->outTestPage();
				break;
		}
	}
	
/**
* Calculates the sequence to determine the next question
*
* Calculates the sequence to determine the next question
*
* @access public
*/
	function calculateSequence() 
	{
		$sequence = $_GET["sequence"];
		if (!$sequence) $sequence = 1;
		$questionCount = $this->object->getQuestionCount();
		if ($sequence > $questionCount) $sequence = $questionCount;
		if (array_key_exists("save_error", $_GET))
		{
			if ($_GET["save_error"] == 1)
			{
				return $sequence;
			}
		}
		switch ($_GET["activecommand"])
		{
			case "next":
				if($_GET['crs_show_result'])
				{
					$sequence = $this->getNextSequenceByResult($sequence);
				}
				else
				{
					$sequence++;
				}
				break;
			case "previous":
				if($_GET['crs_show_result'])
				{
					$sequence = $this->getPreviousSequenceByResult($sequence);
				}
				else
				{
					$sequence--;
				}
				break;
		}
		
		if ($_GET['crs_show_result'])
		{
			if(isset($_SESSION['crs_sequence'][0]))
			{
				$sequence = max($sequence,$_SESSION['crs_sequence'][0]);
			}
			else
			{
				$sequence = max($sequence,$this->object->getFirstSequence());
			}
		}
		return $sequence;
	}
	
/**
* Go to the next question
*
* Go to the next question
*
* @access public
*/
	function next()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "next");
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Go to the previous question
*
* Go to the previous question
*
* @access public
*/
	function previous()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "previous");
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Postpone a question to the end of the test
*
* Postpone a question to the end of the test
*
* @access public
*/
	function postpone()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "postpone");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Show the question summary in online exams
*
* Show the question summary in online exams
*
* @access public
*/
	function summary()
	{
		$this->saveQuestionSolution();
		if ($this->saveResult == FALSE)
		{
			$this->ctrl->setParameter($this, "activecommand", "");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
		else
		{
			$this->ctrl->setParameter($this, "activecommand", "summary");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
	}

/**
* Set a question solved
*
* Set a question solved
*
* @access public
*/
	function setmarked()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "setmarked");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Set a question unsolved
*
* Set a question unsolved
*
* @access public
*/
	function resetmarked()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "resetmarked");
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* The direct feedback button was hit to show an instant feedback
*
* The direct feedback button was hit to show an instant feedback
*
* @access public
*/
	function directfeedback()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "directfeedback");
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Select an image map region in an image map question
*
* Select an image map region in an image map question
*
* @access public
*/
	function selectImagemapRegion()
	{
		$this->saveQuestionSolution();
		$activecommand = "selectImagemapRegion";
		if (array_key_exists("cmd", $_POST))
		{
			$activecommand = key($_POST["cmd"]);
		}
		$this->ctrl->setParameter($this, "activecommand", $activecommand);
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Go to the question with the active sequence
*
* Go to the question with the active sequence
*
* @access public
*/
	function gotoQuestion()
	{
		$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
		$this->ctrl->setParameter($this, "activecommand", "gotoquestion");
		$this->ctrl->saveParameter($this, "tst_javascript");
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
/**
* Go back to the last active question from the summary
*
* Go back to the last active question from the summary
*
* @access public
*/
	function backFromSummary()
	{
		$this->ctrl->setParameter($this, "activecommand", "back");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* The final submission of a test was confirmed
*
* The final submission of a test was confirmed
*
* @access public
*/
	function confirmFinish()
	{
		$this->finishTest(false);
	}
	
/**
* Confirmation of the tests final submission
*
* Confirmation of the tests final submission
*
* @access public
*/
	function confirmFinishTest()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish_confirmation.html", "Modules/Test");
		$this->tpl->setVariable("FINISH_QUESTION", $this->lng->txt("tst_finish_confirmation_question"));
		$this->tpl->setVariable("BUTTON_CONFIRM", $this->lng->txt("tst_finish_confirm_button"));
		if ($this->object->canShowSolutionPrintview($ilUser->getId()))
		{
			$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("tst_finish_confirm_list_of_answers_button"));
		}
		else
		{
			$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("tst_finish_confirm_cancel_button"));
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Finish the test
*
* Finish the test
*
* @access public
*/
	function finishTest($confirm = true)
	{
		global $ilUser;
		global $ilias;
		
		unset($_SESSION["tst_next"]);
		
		$active_id = $this->getActiveId();
		$actualpass = $this->object->_getPass($active_id);
		
		if (($actualpass == $this->object->getNrOfTries() - 1) && (!$confirm))
		{		
			$this->object->setActiveTestSubmitted($ilUser->getId());
			$ilias->auth->setIdle($ilias->ini->readVariable("session","expire"), false);
			$ilias->auth->setExpire(0);
		}
		
		if (($confirm) && ($actualpass == $this->object->getNrOfTries() - 1))
		{
			if ($this->object->canShowSolutionPrintview($ilUser->getId()))
			{
				$template = new ilTemplate("tpl.il_as_tst_finish_navigation.html", TRUE, TRUE, "Modules/Test");
				$template->setVariable("BUTTON_FINISH", $this->lng->txt("btn_next"));
				$template->setVariable("BUTTON_CANCEL", $this->lng->txt("btn_previous"));
				
				$template_top = new ilTemplate("tpl.il_as_tst_list_of_answers_topbuttons.html", TRUE, TRUE, "Modules/Test");
				$template_top->setCurrentBlock("button_print");
				$template_top->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
				$template_top->parseCurrentBlock();
	
				$this->showListOfAnswers($active_id, NULL, $template_top->get(), $template->get());
				return;
			}
			else
			{
				// show confirmation page
				return $this->confirmFinishTest();
			}
		}
		if ($this->object->isRandomTest())
		{
			// create a new set of random questions if more passes are allowed
			$maxpass = $this->object->getNrOfTries();
			if (($maxpass == 0) || (($actualpass+1) < ($maxpass)))
			{
				$this->object->generateRandomQuestions($actualpass+1);
			}
		}

		$this->object->setActiveTestUser(1, "", true);
		
		if($_GET['crs_show_result'])
		{
			$this->ctrl->redirectByClass("ilobjtestgui", "backToCourse");
		}

		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage($maxprocessingtimereached);
		}
		else
		{
			$this->outIntroductionPage();
		}
	}
	
/**
* Outputs the question of the active sequence
*
* Outputs the question of the active sequence
*
* @access public
*/
	function outTestPage()
	{
		global $rbacsystem;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_output.html", "Modules/Test");	
		if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($this->isMaxProcessingTimeReached())
		{
			$this->maxProcessingTimeReached();
			return;
		}
		
		if ($this->object->endingTimeReached())
		{
			$this->endingTimeReached();
			return;
		}
			
		if (($this->object->getInstantFeedbackSolution() == 1) || ($this->object->getAnswerFeedback() == 1))
		{
			$this->tpl->setCurrentBlock("direct_feedback");
			$this->tpl->setVariable("TEXT_DIRECT_FEEDBACK", $this->lng->txt("check"));
			$this->tpl->parseCurrentBlock();
		}
		
		// show next/previous question
	
		if ($this->sequence == $this->object->getQuestionCount())
		{
			$finish = true;
		}
		else
		{
			$finish = false;
		}

		$postpone = false;

		if ($this->object->getSequenceSettings() == TEST_POSTPONE)
		{
			$postpone = true;
		}

		$active = $this->object->getActiveTestUser();

		$user_question_order =& $this->object->getAllQuestionsForActiveUser();
		$this->outShortResult($user_question_order);
			
		if ($this->object->getEnableProcessingTime())
		{
			$this->outProcessingTime($active->active_id);
		}

		$this->tpl->setVariable("FORM_TIMESTAMP", time());
		$directfeedback = 0;
		if (strcmp($_GET["activecommand"], "directfeedback") == 0) $directfeedback = 1;
		$this->outWorkingForm($this->sequence, $finish, $this->object->getTestId(), $active, $postpone, $user_question_order, $directfeedback, $show_summary);
	}

/**
* check access restrictions like client ip, partipating user etc.
*
* check access restrictions like client ip, partipating user etc.
*
* @access public
*/
	function checkOnlineTestAccess() 
	{
		global $ilUser;
		
		// check if user is invited to participate
		$user = $this->object->getInvitedUsers($ilUser->getId());
		if (!is_array ($user) || count($user)!=1)
		{
				ilUtil::sendInfo($this->lng->txt("user_not_invited"), true);
				$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}
			
		$user = array_pop($user);
		// check if client ip is set and if current remote addr is equal to stored client-ip			
		if (strcmp($user->clientip,"")!=0 && strcmp($user->clientip,$_SERVER["REMOTE_ADDR"])!=0)
		{
			ilUtil::sendInfo($this->lng->txt("user_wrong_clientip"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}		
	}	

/**
* Reads the question sequence into a session variable
*
* Reads the question sequence into a session variable. This is used
* by courses
*
* @access public
*/
	function readFullSequence()
	{
		global $ilUser;

		if(!$_GET['crs_show_result'])
		{
			return true;
		}

		$_SESSION['crs_sequence'] = array();
		$active = $this->getActiveId();
		$results = $this->object->getTestResult($active);
		for($i = $this->object->getFirstSequence();
			$i <= $this->object->getQuestionCount();
			$i++)
		{
			$qid = $this->object->getQuestionIdFromActiveUserSequence($i);
			foreach($results as $result)
			{
				if($qid == $result['qid'])
				{
					if(!$result['max'] or $result['max'] != $result['reached'])
					{
						$_SESSION['crs_sequence'][] = $i;
					}
				}
			}
		}
		$this->sequence = $_SESSION['crs_sequence'][0];
		$_SESSION['crs_sequence'] = array_unique($_SESSION['crs_sequence']);
		return true;
	}

/**
* Determines the next question sequence for a course
*
* Determines the next question sequence for a course. This can skip
* correct answered questions
*
* @access public
*/
	function getNextSequenceByResult($a_sequence)
	{
		if(!is_array($_SESSION['crs_sequence']))
		{
			return 1;
		}
		$counter = 0;
		foreach($_SESSION['crs_sequence'] as $sequence)
		{
			if($sequence == $a_sequence)
			{
				if($_SESSION['crs_sequence'][$counter+1])
				{
					return $_SESSION['crs_sequence'][$counter+1];
				}
				else
				{
					return $this->object->getQuestionCount() + 1;
				}
			}
			++$counter;
		}
		return $this->object->getQuestionCount() + 1;
	}

/**
* Determines the previous question sequence for a course
*
* Determines the previous question sequence for a course. This can skip
* correct answered questions
*
* @access public
*/
	function getPreviousSequenceByResult($a_sequence)
	{
		if(!is_array($_SESSION['crs_sequence']))
		{
			return 0;
		}
		$counter = 0;
		foreach($_SESSION['crs_sequence'] as $sequence)
		{
			if($sequence == $a_sequence)
			{
				if($_SESSION['crs_sequence'][$counter-1])
				{
					return $_SESSION['crs_sequence'][$counter-1];
				}
				else
				{
					return 0;
				}
			}
			++$counter;
		}
		return 0;
	}		
	
/**
 * test accessible returns true if the user can perform the test
 */
	function isTestAccessible() 
	{		
		return 	!$this->isNrOfTriesReached() 				
			 	and	 !$this->isMaxProcessingTimeReached()
			 	and  $this->object->startingTimeReached()
			 	and  !$this->object->endingTimeReached();
	}

/**
 * nr of tries exceeded
 */
	function isNrOfTriesReached() 
	{
		$active = $this->object->getActiveTestUser();
		return $this->object->hasNrOfTriesRestriction() && is_object($active) && $this->object->isNrOfTriesReached($active->tries);	
	}
	
/**
 * showTestResults returns true if the according request is set
 */
	function showTestResults() 
	{
		return $_GET['crs_show_result'];
	}
	
/**
* Output of the learners view of an existing test pass
*
* Output of the learners view of an existing test pass
*
* @access public
*/
	function passDetails()
	{
		if (array_key_exists("pass", $_GET) && (strlen($_GET["pass"]) > 0))
		{
			$this->ctrl->saveParameter($this, "pass");
			$this->ctrl->saveParameter($this, "active_id");
			$this->outTestResults(false, $_GET["pass"]);
		}
		else
		{
			$this->outTestResults(false);
		}
	}
	
/**
* Output of a test certificate
*
* Output of a test certificate
*
* @access public
*/
	function outCertificate()
	{
		global $ilUser;
		
		$active_id = $this->getActiveId();
		$counted_pass = ilObjTest::_getResultPass($active_id);
		$this->ctrl->setParameterByClass("iltestcertificategui","active_id", $active_id);
		$this->ctrl->setParameterByClass("iltestcertificategui","pass", $counted_pass);
		$this->ctrl->redirectByClass("iltestcertificategui", "certificateOutput");
	}
	
	/**
	 * handle endingTimeReached
	 * @private
	 */
	
	function endingTimeReached() 
	{
		ilUtil::sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			$this->outResults();
		}
	}
	
/**
* Outputs a message when the maximum processing time is reached
*
* Outputs a message when the maximum processing time is reached
*
* @access public
*/
	function maxProcessingTimeReached()
	{
		$this->outIntroductionPage();
	}		

	/**
	* confirm submit results
	* if confirm then results are submitted and the screen will be redirected to the startpage of the test
	* @access public
	*/
	function confirmSubmitAnswers() 
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isActiveTestSubmitted()) 
		{
			$this->tpl->setCurrentBlock("not_submit_allowed");
			$this->tpl->setVariable("TEXT_ALREADY_SUBMITTED", $this->lng->txt("tst_already_submitted"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_show_answer_sheet"));
		} else 
		{
			$this->tpl->setCurrentBlock("submit_allowed");
			$this->tpl->setVariable("TEXT_CONFIRM_SUBMIT_RESULTS", $this->lng->txt("tst_confirm_submit_answers"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_results"));
		}
		$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function outProcessingTime($active_id) 
	{
		global $ilUser;

		$starting_time = $this->object->getStartingTimeOfUser($active_id);
		$processing_time = $this->object->getProcessingTimeInSeconds();
		$processing_time_minutes = floor($processing_time / 60);
		$processing_time_seconds = $processing_time - $processing_time_minutes * 60;
		$str_processing_time = "";
		if ($processing_time_minutes > 0)
		{
			$str_processing_time = $processing_time_minutes . " " . $this->lng->txt("minutes");
		}
		if ($processing_time_seconds > 0)
		{
			if (strlen($str_processing_time) > 0) $str_processing_time .= " " . $this->lng->txt("and") . " ";
			$str_processing_time .= $processing_time_seconds . " " . $this->lng->txt("seconds");
		}
		$time_left = $starting_time + $processing_time - mktime();
		$time_left_minutes = floor($time_left / 60);
		$time_left_seconds = $time_left - $time_left_minutes * 60;
		$str_time_left = "";
		if ($time_left_minutes > 0)
		{
			$str_time_left = $time_left_minutes . " " . $this->lng->txt("minutes");
		}
		if ($time_left_seconds > 0)
		{
			if (strlen($str_time_left) > 0) $str_time_left .= " " . $this->lng->txt("and") . " ";
			$str_time_left .= $time_left_seconds . " " . $this->lng->txt("seconds");
		}
		$date = getdate($starting_time);
		$formattedStartingTime = ilFormat::formatDate(
			$date["year"]."-".
			sprintf("%02d", $date["mon"])."-".
			sprintf("%02d", $date["mday"])." ".
			sprintf("%02d", $date["hours"]).":".
			sprintf("%02d", $date["minutes"]).":".
			sprintf("%02d", $date["seconds"])
		);
		$datenow = getdate();
		$this->tpl->setCurrentBlock("enableprocessingtime");
		$this->tpl->setVariable("USER_WORKING_TIME", 
			sprintf(
				$this->lng->txt("tst_time_already_spent"),
				$formattedStartingTime,
				$str_processing_time
			)
		);
		$this->tpl->setVariable("USER_REMAINING_TIME", sprintf($this->lng->txt("tst_time_already_spent_left"), $str_time_left));
		$this->tpl->parseCurrentBlock();
		$template = new ilTemplate("tpl.workingtime.js.html", TRUE, TRUE, TRUE);
		$template->setVariable("STRING_MINUTE", $this->lng->txt("minute"));
		$template->setVariable("STRING_MINUTES", $this->lng->txt("minutes"));
		$template->setVariable("STRING_SECOND", $this->lng->txt("second"));
		$template->setVariable("STRING_SECONDS", $this->lng->txt("seconds"));
		$template->setVariable("STRING_TIMELEFT", $this->lng->txt("tst_time_already_spent_left"));
		$template->setVariable("AND", strtolower($this->lng->txt("and")));
		$template->setVariable("YEAR", $date["year"]);
		$template->setVariable("MONTH", $date["mon"]-1);
		$template->setVariable("DAY", $date["mday"]);
		$template->setVariable("HOUR", $date["hours"]);
		$template->setVariable("MINUTE", $date["minutes"]);
		$template->setVariable("SECOND", $date["seconds"]);
		if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getEndingTime(), $matches))
		{
			$template->setVariable("ENDYEAR", $matches[1]);
			$template->setVariable("ENDMONTH", $matches[2]-1);
			$template->setVariable("ENDDAY", $matches[3]);
			$template->setVariable("ENDHOUR", $matches[4]);
			$template->setVariable("ENDMINUTE", $matches[5]);
			$template->setVariable("ENDSECOND", $matches[6]);
		}
		$template->setVariable("YEARNOW", $datenow["year"]);
		$template->setVariable("MONTHNOW", $datenow["mon"]-1);
		$template->setVariable("DAYNOW", $datenow["mday"]);
		$template->setVariable("HOURNOW", $datenow["hours"]);
		$template->setVariable("MINUTENOW", $datenow["minutes"]);
		$template->setVariable("SECONDNOW", $datenow["seconds"]);
		$template->setVariable("PTIME_M", $processing_time_minutes);
		$template->setVariable("PTIME_S", $processing_time_seconds);
		
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $template->get());
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Calculates the active id of the active test user
	*
	* @access public
	*/
	function getActiveId()
	{
		if (array_key_exists("active_id", $_GET))
		{
			return $_GET["active_id"];
		}
		else
		{
			$active = $this->object->getActiveTestUser();
			if (is_object($active))
			{
				return $active->active_id;
			}
			else
			{
				return 0;
			}
		}
	}
	
/**
* Output of a summary of all test questions for test participants
*
* @access public
*/
	function outQuestionSummary() 
	{
		$active_id = $this->getActiveId();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_question_summary.html", "Modules/Test");
		$color_class = array ("tblrow1", "tblrow2");
		$counter = 0;
		
		$result_array = & $this->object->getTestSummary($active_id);
		$marked_questions = array();
		if ($this->object->getShowMarker())
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$marked_questions = ilObjTest::_getSolvedQuestions($active_id);
		}
		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) 
			{
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->ctrl->setParameter($this, "sequence", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "gotoQuestion")."\">" . $value["title"] . "</a>");
				$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
				if ($this->object->getListOfQuestionsDescription())
				{
					$this->tpl->setVariable("VALUE_QUESTION_DESCRIPTION", $value["description"]);
				}
				if ($value["worked_through"])
				{
					$this->tpl->setVariable("VALUE_WORKED_THROUGH", ilUtil::getImagePath("icon_ok.gif"));
					$this->tpl->setVariable("ALT_WORKED_THROUGH", $this->lng->txt("worked_through"));
				}
				else
				{
					$this->tpl->setVariable("VALUE_WORKED_THROUGH", ilUtil::getImagePath("icon_not_ok.gif"));
					$this->tpl->setVariable("ALT_WORKED_THROUGH", $this->lng->txt("not_worked_through"));
				}
				if ($value["postponed"])
				{
					$this->tpl->setVariable("VALUE_POSTPONED", $this->lng->txt("postponed"));
				}
				if (!$this->object->getTitleOutput())
				{
					$this->tpl->setVariable("VALUE_QUESTION_POINTS", $value["points"]."&nbsp;".$this->lng->txt("points_short"));
				}
				if (count($marked_questions))
				{
					if (array_key_exists($value["qid"], $marked_questions))
					{
						$obj = $marked_questions[$value["qid"]];
						if ($obj->solved == 1)
						{
							$this->tpl->setVariable("ALT_MARKED_IMAGE", $this->lng->txt("tst_question_marked"));
							$this->tpl->setVariable("TITLE_MARKED_IMAGE", $this->lng->txt("tst_question_marked"));
							$this->tpl->setVariable("MARKED_IMAGE", ilUtil::getImagePath("marked.png"));
						}
					} 
				}
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		$this->tpl->setVariable("QUESTION_ACTION","actions");
		$this->tpl->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_qst_order"));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		if (!$this->object->getTitleOutput())
		{
			$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("tst_maximum_points"));
		}
		if ($this->object->getShowMarker())
		{
			$this->tpl->setVariable("TEXT_MARKED", $this->lng->txt("tst_question_marker"));
		}
		$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("worked_through"));
		$this->tpl->setVariable("USER_FEEDBACK", $this->lng->txt("tst_qst_summary_text"));
		$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("TXT_SHOW_AND_SUBMIT_ANSWERS", $this->lng->txt("save_finish"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));	
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("question_summary"));		
		
		if ($this->object->getEnableProcessingTime())
			$this->outProcessingTime($active_id);
	}
	
	function showMaximumAllowedUsersReachedMessage()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_max_allowed_users_reached.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("MAX_ALLOWED_USERS_MESSAGE", sprintf($this->lng->txt("tst_max_allowed_users_message"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("MAX_ALLOWED_USERS_HEADING", sprintf($this->lng->txt("tst_max_allowed_users_heading"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("BACK_TO_INTRODUCTION", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function backConfirmFinish()
	{
		global $ilUser;
		if ($this->object->canShowSolutionPrintview($ilUser->getId()))
		{
			$template = new ilTemplate("tpl.il_as_tst_finish_navigation.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("BUTTON_FINISH", $this->lng->txt("btn_next"));
			$template->setVariable("BUTTON_CANCEL", $this->lng->txt("btn_previous"));
			
			$template_top = new ilTemplate("tpl.il_as_tst_list_of_answers_topbuttons.html", TRUE, TRUE, "Modules/Test");
			$template_top->setCurrentBlock("button_print");
			$template_top->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
			$template_top->parseCurrentBlock();
			$active_id = $this->getActiveId();
			return $this->showListOfAnswers($active_id, NULL, $template_top->get(), $template->get());
		}
		else
		{
			return $this->gotoQuestion();
		}
	}
	
	function finishListOfAnswers()
	{
		$this->confirmFinishTest();
	}
	
	/**
	* Creates an output of the solution of an answer compared to the correct solution
	*
	* @access public
	*/
	function outCorrectSolution()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_correct_solution.html", "Modules/Test");

		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");

		$this->tpl->setCurrentBlock("adm_content");
		$solution = $this->getCorrectSolutionOutput($_GET["evaluation"], $_GET["active_id"], $_GET["pass"]);
		$this->tpl->setVariable("OUTPUT_SOLUTION", $solution);
		$this->tpl->setVariable("TEXT_BACK", $this->lng->txt("back"));
		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		$this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTarget($this, "outUserResultsOverview"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates an output of the list of answers for a test participant during the test
	* (only the actual pass will be shown)
	*
	* @param integer $active_id Active id of the participant
	* @param integer $pass Test pass of the participant
	* @param boolean $testnavigation Deceides wheather to show a navigation for tests or not
	* @access public
	*/
	function showListOfAnswers($active_id, $pass = NULL, $top_data = "", $bottom_data = "")
	{
		global $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish_list_of_answers.html", "Modules/Test");

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
	
		if (strlen($top_data))
		{
			$this->tpl->setCurrentBlock("top_data");
			$this->tpl->setVariable("TOP_DATA", $top_data);
			$this->tpl->parseCurrentBlock();
		}
		
		if (strlen($bottom_data))
		{
			$this->tpl->setCurrentBlock("bottom_data");
			$this->tpl->setVariable("BOTTOM_DATA", $bottom_data);
			$this->tpl->parseCurrentBlock();
		}
		
		$result_array =& $this->object->getTestResult($active_id, $pass);

		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			$question = $question_data["qid"];
			if (is_numeric($question))
			{
				$this->tpl->setCurrentBlock("printview_question");
				$question_gui = $this->object->createQuestionGUI("", $question);
				$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
				$template->setVariable("COUNTER_QUESTION", $counter.". ");
				$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
				
				$result_output = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, FALSE, $this->object->getShowSolutionFeedback());
				$template->setVariable("SOLUTION_OUTPUT", $result_output);
				$this->tpl->setVariable("QUESTION_OUTPUT", $template->get());
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_list_of_answers"));
		$user_data = $this->getResultsUserdata($active_id, TRUE);
		$signature = $this->getResultsSignature();
		$this->tpl->setVariable("USER_DETAILS", $user_data);
		$this->tpl->setVariable("SIGNATURE", $signature);
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();

		$invited_user =& $this->object->getInvitedUsers($ilUser->getId());
		$pagetitle = $this->object->getTitle() . " - " . $this->lng->txt("clientip") . 
			": " . $invited_user[$ilUser->getId()]->clientip . " - " . 
			$this->lng->txt("matriculation") . ": " . 
			$invited_user[$ilUser->getId()]->matriculation;
		$this->tpl->setVariable("PAGETITLE", $pagetitle);
	}
 
/**
* Output of the pass overview for a user when he/she wants to see his/her list of answers
*
* Output of the pass overview for a user when he/she wants to see his/her list of answers
*
* @access public
*/
	function outUserListOfAnswerPasses()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_info_list_of_answers.html", "Modules/Test");

		$pass = null;
		if (array_key_exists("pass", $_GET))
		{
			if (strlen($_GET["pass"])) $pass = $_GET["pass"];
		}
		$user_id = $ilUser->getId();
		$active_id = $this->getActiveId();
		$overview = "";
		if ($this->object->getNrOfTries() == 1)
		{
			$pass = 0;
		}
		else
		{
			$overview = $this->getPassOverview($active_id, "iltestoutputgui", "outUserListOfAnswerPasses", TRUE);
			$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_passes"));
			$this->tpl->setVariable("PASS_OVERVIEW", $overview);
		}

		$signature = "";
		if (strlen($pass))
		{
			$signature = $this->getResultsSignature();
			$result_array =& $this->object->getTestResult($active_id, $pass);
			$answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, FALSE, TRUE);
			$this->tpl->setVariable("PASS_DETAILS", $answers);
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "infoScreen"));
		$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$this->tpl->setVariable("PRINT_URL", "javascript:window.print();");
		
		$user_data = $this->getResultsUserdata($active_id, TRUE);
		$this->tpl->setVariable("USER_DATA", $user_data);
		$this->tpl->setVariable("TEXT_LIST_OF_ANSWERS", $this->lng->txt("tst_list_of_answers"));
		if (strlen($signature))
		{
			$this->tpl->setVariable("SIGNATURE", $signature);
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
	}

/**
* Output of the pass overview for a test called by a test participant
*
* Output of the pass overview for a test called by a test participant
*
* @access public
*/
	function outUserResultsOverview()
	{
		global $ilUser, $ilias;

		include_once("./classes/class.ilTemplate.php");
		$templatehead = new ilTemplate("tpl.il_as_tst_results_participants.html", TRUE, TRUE, "Modules/Test");
		$template = new ilTemplate("tpl.il_as_tst_results_participant.html", TRUE, TRUE, "Modules/Test");

		$pass = null;
		$user_id = $ilUser->getId();
		$uname = $this->object->userLookupFullName($user_id, TRUE);
		$active_id = $this->getActiveId();
		$hide_details = !$this->object->getShowPassDetails();
		if ($hide_details)
		{
			$executable = $this->object->isExecutable($ilUser->getId());
			if (!$executable["executable"])  $hide_details = FALSE;
		}
		if (($this->object->getNrOfTries() == 1) && (!$hide_details))
		{
			$pass = 0;
		}
		else
		{
			$template->setCurrentBlock("pass_overview");
			$overview = $this->getPassOverview($active_id, "iltestoutputgui", "outUserResultsOverview", FALSE, $hide_details);
			$template->setVariable("PASS_OVERVIEW", $overview);
			$template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results_overview"));
			$template->parseCurrentBlock();
		}

		if (((array_key_exists("pass", $_GET)) && (strlen($_GET["pass"]) > 0)) || (!is_null($pass)))
		{
			if (is_null($pass))	$pass = $_GET["pass"];
		}

		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
		{
			$this->ctrl->setParameter($this, "pass", $pass);
			$this->ctrl->setParameter($this, "pdf", "1");
			$templatehead->setCurrentBlock("pdf_export");
			$templatehead->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "outUserResultsOverview"));
			$this->ctrl->setParameter($this, "pass", "");
			$this->ctrl->setParameter($this, "pdf", "");
			$templatehead->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$templatehead->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$templatehead->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$templatehead->parseCurrentBlock();
		}
		$templatehead->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_introduction"));
		$templatehead->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "infoScreen"));
		$templatehead->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$templatehead->setVariable("PRINT_URL", "javascript:window.print();");
		
		$result_pass = $this->object->_getResultPass($active_id);
		$result_array =& $this->object->getTestResult($active_id, $result_pass);
		$statement = $this->getFinalStatement($result_array["test"]);
		$user_data = $this->getResultsUserdata($active_id, TRUE);

		// output of the details of a selected pass
		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		if (!is_null($pass))
		{
			$result_array =& $this->object->getTestResult($active_id, $pass);
			$command_solution_details = "";
			if ($this->object->getShowSolutionDetails())
			{
				$command_solution_details = "outCorrectSolution";
			}
			$detailsoverview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestoutputgui", "outUserResultsOverview", $command_solution_details);
	
			$user_id = $this->object->_getUserIdFromActiveId($active_id);
	
			if (!$hide_details)
			{
				$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass);
			}
			
			$template->setVariable("LIST_OF_ANSWERS", $list_of_answers);
			$template->setVariable("PASS_RESULTS_OVERVIEW", sprintf($this->lng->txt("tst_results_overview_pass"), $pass + 1));
			$template->setVariable("PASS_DETAILS", $detailsoverview);

			$signature = $this->getResultsSignature();
			$template->setVariable("SIGNATURE", $signature);
		}
		$template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name"), $uname));
		$template->setVariable("USER_DATA", $user_data);
		$template->setVariable("USER_MARK", $statement["mark"]);
		if (strlen($statement["markects"]))
		{
			$template->setVariable("USER_MARK_ECTS", $statement["mark"]);
		}
		$template->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
		$templatehead->setVariable("RESULTS_PARTICIPANT", $template->get());
		
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
			$this->tpl->setVariable("PRINT_CONTENT", $templatehead->get());
		}
	}
	
/**
* Output of the pass details of an existing test pass for the active test participant
*
* Output of the pass details of an existing test pass for the active test participant
*
* @access public
*/
	function outUserPassDetails()
	{
		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		$active_id = $_GET["active_id"];
		$pass = $_GET["pass"];
		$result_array =& $this->object->getTestResult($active_id, $pass);

		$command_solution_details = "";
		if ($this->object->getShowSolutionDetails())
		{
			$command_solution_details = "outCorrectSolution";
		}
		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestoutputgui", "outUserPassDetails", $command_solution_details);

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_pass_details_overview_participants.html", "Modules/Test");

		if ($this->object->getNrOfTries() == 1)
		{
			$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_introduction"));
			$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "infoScreen"));
		}
		else
		{
			$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass(get_class($this), "outUserResultsOverview"));
			$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_overview"));
		}

		$this->tpl->parseCurrentBlock();

		if ($this->object->getNrOfTries() == 1)
		{
			$statement = $this->getFinalStatement($result_array["test"]);
			$this->tpl->setVariable("USER_MARK", $statement["mark"]);
			if (strlen($statement["markects"]))
			{
				$this->tpl->setVariable("USER_MARK_ECTS", $statement["mark"]);
			}
		}
		
		$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass);
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("LIST_OF_ANSWERS", $list_of_answers);
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("PASS_DETAILS", $overview);
		$uname = $this->object->userLookupFullName($user_id, TRUE);
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
	}

/**
* Output of the pass overview for a test called from the statistics
*
* Output of the pass overview for a test called from the statistics
*
* @access public
*/
	function outParticipantsResultsOverview()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_pass_overview_participants.html", "Modules/Test");

		$active_id = $_GET["active_id"];
		if ($this->object->getNrOfTries() == 1)
		{
			$this->ctrl->setParameter($this, "active_id", $active_id);
			$this->ctrl->setParameter($this, "pass", ilObjTest::_getResultPass($active_id));
			$this->ctrl->redirect($this, "outParticipantsPassDetails");
		}

		$overview = $this->getPassOverview($active_id, "iltestoutputgui", "outParticipantsPassDetails");

		$this->tpl->setVariable("PASS_OVERVIEW", $overview);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("back"));
		$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
		$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$this->tpl->setVariable("PRINT_URL", "javascript:window.print();");
		
		$result_pass = $this->object->_getResultPass($active_id);
		$result_array =& $this->object->getTestResult($active_id, $result_pass);
		$statement = $this->getFinalStatement($result_array["test"]);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		$user_data = $this->getResultsUserdata($active_id);
		$this->tpl->setVariable("USER_DATA", $user_data);
		$this->tpl->setVariable("TEXT_OVERVIEW", $this->lng->txt("tst_results_overview"));
		$this->tpl->setVariable("USER_MARK", $statement["mark"]);
		if (strlen($statement["markects"]))
		{
			$this->tpl->setVariable("USER_MARK_ECTS", $statement["mark"]);
		}
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
	}
	
/**
* Output of the pass details of an existing test pass for the test statistics
*
* Output of the pass details of an existing test pass for the test statistics
*
* @access public
*/
	function outParticipantsPassDetails()
	{
		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		$active_id = $_GET["active_id"];
		$pass = $_GET["pass"];
		$result_array =& $this->object->getTestResult($active_id, $pass);

		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestoutputgui", "outParticipantsPassDetails");

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$this->tpl->addBlockFile("PRINT_CONTENT", "adm_content", "tpl.il_as_tst_pass_details_overview_participants.html", "Modules/Test");

		if (array_key_exists("statistics", $_GET) && ($_GET["statistics"] == 1))
		{
			$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("back"));
			$this->ctrl->setParameterByClass("ilTestEvaluationGUI", "active_id", $active_id);
			$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilTestEvaluationGUI", "detailedEvaluation"));
		}
		else
		{
			if ($this->object->getNrOfTries() == 1)
			{
				$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("back"));
				$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
			}
			else
			{
				$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass(get_class($this), "outParticipantsResultsOverview"));
				$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_overview"));
			}
		}

		$this->tpl->parseCurrentBlock();

		if ($this->object->getNrOfTries() == 1)
		{
			$statement = $this->getFinalStatement($result_array["test"]);
			$this->tpl->setVariable("USER_MARK", $statement["mark"]);
			if (strlen($statement["markects"]))
			{
				$this->tpl->setVariable("USER_MARK_ECTS", $statement["mark"]);
			}
		}
		
		$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, TRUE);
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("LIST_OF_ANSWERS", $list_of_answers);
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("PASS_DETAILS", $overview);
		$uname = $this->object->userLookupFullName($user_id);
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss("./Modules/Test/templates/default/test_print.css", "print");
	}
}
?>
