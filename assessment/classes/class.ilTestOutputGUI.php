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

include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Output class for assessment test execution
*
* The ilTestOutputGUI class creates the output for the ilObjTestGUI
* class when learners execute a test. This saves some heap space because 
* the ilObjTestGUI class will be much smaller then
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilTestOutputGUI.php
* @modulegroup   assessment
*/
class ilTestOutputGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;

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
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
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
	 * updates working time and stores state saveresult to see if question has to be stored or not
	 */
	
	function updateWorkingTime() 
	{
		if ($_SESSION["active_time_id"]) // && $this->object->getEnableProcessingTime())
		{
			$this->object->updateWorkingTime($_SESSION["active_time_id"]);
			//echo "updating Worktime<br>";
		}	
	}	

/**
 * saves the user input of a question
 */
	function saveQuestionSolution()
	{
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
				if ($ilUser->prefs["tst_javascript"])
				{
					$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				$pass = NULL;
				$active = $this->object->getActiveTestUser($ilUser->getId());
				if ($this->object->isRandomTest())
				{
					$pass = $this->object->_getPass($active->active_id);
				}
				$this->saveResult = $question_gui->object->saveWorkingData($active->active_id, $pass);
			}												
		}			
	}
	
	/**
	 * returns if answers can be saved
	 * 
	 */
	 function canSaveResult() 
	 {
		 return !$this->isEndingTimeReached() && !$this->isMaxProcessingTimeReached() && !$this->isNrOfTriesReached();
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
		$starting_time = $this->object->getStartingTimeOfUser($ilUser->getId());
		if ($starting_time === FALSE)
		{
			return FALSE;
		}
		else
		{
			return $this->object->isMaxProcessingTimeReached($starting_time);
		}
	}
	
	function isEndingTimeReached()
	{
		global $ilUser;
		if (!is_bool($this->endingTimeReached))			
			$this->endingTimeReached = $this->object->endingTimeReached() && ($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->isOnlineTest());
			
		return $this->endingTimeReached;
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_delete_results_confirm.html", true);
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
		sendInfo($this->lng->txt("tst_confirm_delete_results_info"), true);
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
		{
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
			$this->tpl->parseCurrentBlock();
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
			$this->tpl->parseCurrentBlock();
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
		if ($ilUser->prefs["tst_javascript"])
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}

		$is_postponed = false;
		if (is_object($active))
		{			
			if (!preg_match("/(^|\D)" . $question_gui->object->getId() . "($|\D)/", $active->postponed) and 
				!($active->postponed == $question_gui->object->getId()))
			{
				$is_postponed = false;
			}
			else
			{
				$is_postponed = true;
			}
		}

		$this->ctrl->setParameter($this, "sequence", "$sequence");
		$formaction = $this->ctrl->getFormAction($this);
		$question_gui->setSequenceNumber($sequence);
		$question_gui->setQuestionCount(count($this->object->questions));
		// output question
		$use_post_solutions = false;
		if ($this->saveResult === false)
		{
			$use_post_solutions = true;
		}
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$question_gui->outQuestionForTest($formaction, $active->active_id, NULL, $is_postponed, $user_post_solutions);
		if ($directfeedback)
		{
			if ($this->object->getShowSolutionDetails())
			{
				$solutionoutput = $question_gui->getSolutionOutput("", NULL);
				$this->tpl->setCurrentBlock("solution_output");
				$this->tpl->setVariable("CORRECT_SOLUTION", $this->lng->txt("correct_solution_is"));
				$this->tpl->setVariable("QUESTION_FEEDBACK", $solutionoutput);
				$this->tpl->setVariable("RECEIVED_POINTS_INFORMATION", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->calculateReachedPoints($active->active_id, NULL), $question_gui->object->getMaximumPoints()));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("solution_output");
				$this->tpl->setVariable("RECEIVED_POINTS_INFORMATION", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->calculateReachedPoints($active->active_id, NULL), $question_gui->object->getMaximumPoints()));
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
		
		if (($this->object->isOnlineTest() && !$finish) || ($this->object->getShowSummary() && !$this->object->isOnlineTest())) 
		{
			$this->tpl->setCurrentBlock("summary");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("summary_bottom");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
		}

		if (!$this->object->isOnlineTest()) 
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
			if (!$this->object->isOnlineTest()) 
			{
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
			} 
			else 
			{
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
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

		if ($this->object->isOnlineTest()) 
		{
			include_once "./assessment/classes/class.ilObjTest.php";
			$solved_array = ilObjTest::_getSolvedQuestions($active->active_id, $question_gui->object->getId());
			$solved = 0;
			
			if (count ($solved_array) > 0) 
			{
				$solved = array_pop($solved_array);
				$solved = $solved->solved;
			}			
			
			if ($solved==1) 
			{
			 	$solved = ilUtil::getImagePath("solved.png", true);
			 	$solved_cmd = "resetsolved";
			 	$solved_txt = $this->lng->txt("tst_qst_resetsolved");
			} 
			else 
			{				 
				$solved = ilUtil::getImagePath("not_solved.png", true);
				$solved_cmd = "setsolved";
				$solved_txt = $this->lng->txt("tst_qst_setsolved");
			}			
			$solved = "<input align=\"middle\" border=\"0\" alt=\"".$this->lng->txt("tst_qst_solved_state_click_to_change")."\" name=\"cmd[$solved_cmd]\" type=\"image\" src=\"$solved\" id=\"$solved_cmd\">&nbsp;<small><label for=\"$solved_cmd\">$solved_txt</label></small>";
			
			$this->tpl->setCurrentBlock("question_status");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS_LABEL", $this->lng->txt("tst_question_solved_state").":");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS", $solved);
			$this->tpl->parseCurrentBlock();			
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_password_protection.html", true);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
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
			sendInfo($this->lng->txt("tst_password_entered_wrong_password"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen"); 
		}
	}
	
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
* Start a test for the first time
*
* @access public
*/
	function start()
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
		$this->readFullSequence();
		
		if ($this->object->isRandomTest())
		{
			$this->object->generateRandomQuestions();
			$this->object->loadQuestions();
		}
		$this->handleStartCommands();
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_anonymous_code_presentation.html", true);
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("TEXT_ANONYMOUS_CODE_CREATED", $this->lng->txt("tst_access_code_created"));
			$this->tpl->setVariable("TEXT_ANONYMOUS_CODE", $this->object->getAccessCodeSession());
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("CONTINUE", $this->lng->txt("continue_work"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->ctrl->setParameter($this, "activecommand", "start");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
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
		$this->readFullSequence();
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
		
		// create new time dataset and set start time
		$active_time_id = $this->object->startWorkingTime($ilUser->id);
		$_SESSION["active_time_id"] = $active_time_id;
		
		if ($_POST["chb_javascript"])
		{
			$ilUser->setPref("tst_javascript", 1);
			$ilUser->writePref("tst_javascript", 1);
		}
		else
		{
			$ilUser->setPref("tst_javascript", 0);
			$ilUser->writePref("tst_javascript", 0);
		}
		
		// hide previous results
		if ($this->object->getNrOfTries() != 1)
		{
			if ($this->object->getHidePreviousResults() != 1)
			{
				if ($_POST["chb_hide_previous_results"])
				{
					$ilUser->setPref("tst_hide_previous_results", 1);
					$ilUser->writePref("tst_hide_previous_results", 1);
				}
				else
				{
					$ilUser->setPref("tst_hide_previous_results", 0);
					$ilUser->writePref("tst_hide_previous_results", 0);
				}
			}
		}
		
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			global $ilias;
			$ilias->auth->setIdle(0, false);					
		}
	}
	
	function redirectQuestion()
	{
		global $ilUser;
		include_once "./assessment/classes/class.ilObjTest.php";
		switch ($_GET["activecommand"])
		{
			case "next":
				$this->sequence = $this->calculateSequence();
				// calculate count of questions statically to prevent problems with
				// random tests. If the numer of questions in the used questionpools
				// has been reduced lower than the number of questions which should be
				// chosen, the dynamic method fails because it returns the number of questions
				// that should be chosen. This leds to an error if the test is completed
				$questioncount = ilObjTest::_getQuestionCount($this->object->getTestId(), $ilUser->getId());
				if ($this->sequence > $questioncount)
		//			if ($this->sequence > $this->object->getQuestionCount())
				{
					if ($this->object->isOnlineTest())
					{
						$this->outTestSummary();
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
			case "setsolved":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$q_id  = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
				$this->object->setQuestionSetSolved(1, $q_id, $ilUser->getId());
				$this->outTestPage();
				break;
			case "resetsolved":
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
				if ($this->object->isOnlineTest())
				{
					$this->ctrl->redirect($this, "outTestSummary");
				}
				else
				{
					$this->ctrl->redirect($this, "outQuestionSummary");
				}
				break;
			case "start":
			case "resume":
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				if ($this->object->isOnlineTest())
				{
					$this->outTestSummary();
				}
				else
				{
					$this->outTestPage();
				}
				break;
			default:
				$this->sequence = $this->calculateSequence();	
				$this->object->setActiveTestUser($this->sequence);
				$this->outTestPage();
				break;
		}
	}
	
	function calculateSequence() 
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($this->object->isActiveTestSubmitted()) return "";
		}
		$sequence = $_GET["sequence"];
		if (!$sequence) $sequence = 1;
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
		global $ilUser;
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
		$this->ctrl->setParameter($this, "activecommand", "summary");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Set a question solved
*
* Set a question solved
*
* @access public
*/
	function setsolved()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "setsolved");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

/**
* Set a question unsolved
*
* Set a question unsolved
*
* @access public
*/
	function resetsolved()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "resetsolved");
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
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
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
		$this->gotoQuestion();
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish_confirmation.html", true);
		$this->tpl->setVariable("FINISH_QUESTION", $this->lng->txt("tst_finish_confirmation_question"));
		$this->tpl->setVariable("BUTTON_CONFIRM", $this->lng->txt("tst_finish_confirm_button"));
		$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("tst_finish_confirm_cancel_button"));
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
		
		unset($_SESSION["tst_next"]);
		
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$actualpass = $this->object->_getPass($active->active_id);
		if (($confirm) && ($actualpass == $this->object->getNrOfTries() - 1))
		{
			// show confirmation page
			return $this->confirmFinishTest();
		}
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			// create a new set of random questions if more passes are allowed
			$maxpass = $this->object->getNrOfTries();
			if (($maxpass == 0) || (($actualpass+1) < ($maxpass)))
			{
				$this->object->generateRandomQuestions($actualpass+1);
			}
		}
		
		if ($this->object->isOnlineTest() && !$this->object->isActiveTestSubmitted($ilUser->getId())) 
		{
			$this->outTestSummary();
			return;
		}
			
		$this->object->setActiveTestUser(1, "", true);

		//if (($this->object->getTestType() != TYPE_VARYING_RANDOMTEST) && (!$this->object->canViewResults())) 
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage($maxprocessingtimereached);
		}
		else
		{
			$this->outResults();
		}

		if($_GET['crs_show_result'])
		{
			$this->ctrl->redirectByClass("ilobjtestgui", "backToCourse");
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_output.html", true);	
		if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->prepareRequestVariables();
		
		$this->onRunObjectEnter();
		
		// update working time and set saveResult state
		$this->updateWorkingTime();
					
		if ($this->isMaxProcessingTimeReached())
		{
			$this->maxProcessingTimeReached();
			return;
		}
		
		if ($this->isEndingTimeReached())
		{
			$this->endingTimeReached();
			return;
		}
			
		if ($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)
		{
			$this->tpl->setCurrentBlock("direct_feedback");
			$this->tpl->setVariable("TEXT_DIRECT_FEEDBACK", $this->lng->txt("direct_feedback"));
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
			$this->outProcessingTime();
		}

		$this->tpl->setVariable("FORM_TIMESTAMP", time());
		$directfeedback = 0;
		if (strcmp($_GET["activecommand"], "directfeedback") == 0) $directfeedback = 1;
		$this->outWorkingForm($this->sequence, $finish, $this->object->getTestId(), $active, $postpone, $user_question_order, $directfeedback, $show_summary);
	}

	/**
	 * prepare Request variables e.g. some get parameters have to be mapped to post params
	 */
	function prepareRequestVariables()
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($_GET["sort_summary"])
			//	sort summary: click on title to sort in summary
				$_POST["cmd"]["summary"]="1";
	
			if ($_POST["cmd"]["cancel_show_answers"]) 
			{
			// cancel_show_answers: click on back in show_answer view
				if ($this->isTestAccessible()) 
				{	// everythings ok goto summary
					$_POST["cmd"]["summary"]="1";
				} 
					else 
				{
					$_POST["cmd"]["run"]="1";
					unset($_GET ["sequence"]);
				}			
			}
			
			if ($_POST["cmd"]["show_answers"] or $_POST["cmd"]["back"] or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"]) 
			{
				unset($_GET ["sort_summary"]);			
				unset($_GET ["setsolved"]);
				unset($_GET ["resetsolved"]);
				if ($_POST["cmd"]["show_answers"]  or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"])					
					unset($_GET ["sequence"]);		
			}			
		}
		else
		{
			// set showresult cmd if pressed on sort in result overview
			if ($_GET["sortres"])
				$_POST["cmd"]["showresults"] = 1;
		}
	}
	
	/**
	 * what to when entering the run object
	 */
	function onRunObjectEnter()
	{
		// cancel Test if it's not online test
		if ($_POST["cmd"]["cancelTest"])
		{
			$this->handleCancelCommand();
		}		
		// check online exams access restrictions due to participants and client ip
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			$this->checkOnlineTestAccess();
		}
	}	

	/**
	 * check access restrictions like client ip, partipating user etc. 
	 *
	 */
		
	function checkOnlineTestAccess() 
	{
		global $ilUser;
		
		// check if user is invited to participate
		$user = $this->object->getInvitedUsers($ilUser->getId());
		if (!is_array ($user) || count($user)!=1)
		{
				sendInfo($this->lng->txt("user_not_invited"), true);
				$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}
			
		$user = array_pop($user);
		// check if client ip is set and if current remote addr is equal to stored client-ip			
		if (strcmp($user->clientip,"")!=0 && strcmp($user->clientip,$_SERVER["REMOTE_ADDR"])!=0)
		{
			sendInfo($this->lng->txt("user_wrong_clientip"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}		
	}	
	
	/**
	 * get next or previous sequence
	 */
	
	function getSequence() 
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($this->object->isActiveTestSubmitted()) return "";
		}
		$sequence = $_GET["sequence"];
		if (!$sequence) $sequence = 1;
		$saveResult = $this->saveResult;
		if (isset($_POST["cmd"]["next"]) && $saveResult == true)
		{
			if($_GET['crs_show_result'])
			{
				$sequence = $this->getNextSequenceByResult($sequence);
			}
			else
			{
				$sequence++;
			}
		}
		elseif (($_POST["cmd"]["previous"]) and ($sequence != 0) and ($saveResult))
		{
			if($_GET['crs_show_result'])
			{
				$sequence = $this->getPreviousSequenceByResult($sequence);
			}
			else
			{
				$sequence--;
			}
		}
		elseif($_GET['crs_show_result'])
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

	function readFullSequence()
	{
		global $ilUser;

		$active = $this->object->getActiveTestUser($ilUser->getId());
		$results = $this->object->getTestResult($active->active_id);

		$_SESSION['crs_sequence'] = array();
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
		return true;
	}

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
			 	and  !$this->isEndingTimeReached();
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
	 * resumable is when there exists a test and the restrictions (time, nr of tries etc) don't prevent an access
	 */
	
	function isTestResumable() 
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($this->object->isActiveTestSubmitted()) return false;
		}

		$active = $this->object->getActiveTestUser();		
		return is_object($active) && $this->object->startingTimeReached() && !$this->object->endingTimeReached();
	}
	
	/**
	 * handle cancel command
	 */
		
	function handleCancelCommand()
	{
		sendInfo($this->lng->txt("test_cancelled"), true);
		$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
	}
	
	/**
	 * showTestResults returns true if the according request is set
	 */
	function showTestResults() 
	{
		return $_GET['crs_show_result'];
	}
	
	function outResults()
	{
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			$this->outResultsOverview();
		}
		else
		{
			$this->outTestResults();
		}
	}
	
/**
* Output of the learner overview for a varying random test
*
* Output of the learner overview for a varying random test
*
* @access public
*/
	function outResultsOverview()
	{
		global $ilUser;
		
		if ($this->object->getTestType() != TYPE_VARYING_RANDOMTEST)
		{
			$this->ctrl->redirect($this, "outIntroductionPage");
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_varying_results.html", true);
		$user_id = $ilUser->id;
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./assessment/classes/class.ilObjTest.php";
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$counted_pass = ilObjTest::_getResultPass($active->active_id);
		$reached_pass = $this->object->_getPass($active->active_id);
		$result_percentage = 0;
		$result_total_reached = 0;
		$result_total_max = 0;
		for ($pass = 0; $pass <= $reached_pass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($active->active_id, $pass);
			if ($finishdate > 0)
			{
				$result_array =& $this->object->getTestResult($active->active_id, $pass);
				if (!$result_array["test"]["total_max_points"])
				{
					$percentage = 0;
				}
				else
				{
					$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
				}
				$total_max = $result_array["test"]["total_max_points"];
				$total_reached = $result_array["test"]["total_reached_points"];
				$this->tpl->setCurrentBlock("result_row");
				if ($pass == $counted_pass)
				{
					$this->tpl->setVariable("COLOR_CLASS", "tblrowmarked");
					$this->tpl->setVariable("VALUE_SCORED", "&otimes;");
					$result_percentage = $percentage;
					$result_total_reached = $total_reached;
					$result_total_max = $total_max;
				}
				else
				{
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$pass % 2]);
				}
				$this->tpl->setVariable("VALUE_PASS", $pass + 1);
				$this->tpl->setVariable("VALUE_DATE", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($finishdate), "date"));
				$this->tpl->setVariable("VALUE_ANSWERED", $this->object->getAnsweredQuestionCount($active->active_id, $pass) . " " . strtolower($this->lng->txt("of")) . " " . (count($result_array)-1));
				$this->tpl->setVariable("VALUE_REACHED", $total_reached . " " . strtolower($this->lng->txt("of")) . " " . $total_max);
				$this->tpl->setVariable("VALUE_PERCENTAGE", sprintf("%.2f", $percentage) . "%");
				if ($this->object->canViewResults())
				{
					$this->tpl->setVariable("HREF_PASS_DETAILS", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "passDetails")."&pass=$pass\">" . $this->lng->txt("tst_pass_details") . "</a>");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("PASS_SCORED", $this->lng->txt("scored_pass"));
		$this->tpl->setVariable("PASS_COUNTER", $this->lng->txt("pass"));
		$this->tpl->setVariable("DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("ANSWERED_QUESTIONS", $this->lng->txt("tst_answered_questions"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENTAGE_CORRECT", $this->lng->txt("tst_percent_solved"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TO_INTRODUCTION", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("test_user_name");
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $ilUser->getFullname()));
		$this->tpl->parseCurrentBlock();

		if ($this->object->canViewResults())
		{
			$mark_obj = $this->object->mark_schema->getMatchingMark($result_percentage);
			if ($mark_obj)
			{
				if ($mark_obj->getPassed()) 
				{
					$mark = $this->lng->txt("tst_result_congratulations");
				} 
				else 
				{
					$mark = $this->lng->txt("tst_result_sorry");
				}
				$mark .= "<br />" . $this->lng->txt("tst_your_mark_is") . ": &quot;" . $mark_obj->getOfficialName() . "&quot;";
			}
			if ($this->object->ects_output)
			{
				$ects_mark = $this->object->getECTSGrade($result_total_reached, $result_total_max);
				$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
			}
			$this->tpl->setVariable("USER_FEEDBACK", $mark);
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
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
			$this->outTestResults(false, $_GET["pass"]);
		}
		else
		{
			$this->outTestResults(false);
		}
	}
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outTestResults($print = false, $pass = NULL) 
	{
		global $ilUser;

		function sort_percent($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["percent"] == $b["percent"]) {
				if ($a["nr"] == $b["nr"]) return 0;
		 	 	return ($a["nr"] < $b["nr"]) ? -1 : 1;
			}
			$apercent = 0.0;
			if ($a["max"] != 0) 
			{
				$apercent = $a["reached"] / $a["max"];
			}
			$bpercent = 0.0;
			if ($b["max"] != 0)
			{
				$bpercent = $b["reached"] / $b["max"];
			}
			return ($apercent < $bpercent) ? $smaller : $greater;
		}

		function sort_nr($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$user_id = $ilUser->id;
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_results.html", true);
		$result_array =& $this->object->getTestResult($active->active_id, $pass);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];
		$img_title_percent = "";
		$img_title_nr = "";
		switch ($_GET["sortres"]) {
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortpercent = "DESC";
				} 
				else 
				{
					$sortpercent = "ASC";
				}
				break;
			case "nr":
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortnr = "DESC";
				} 
				else 
				{
					$sortnr = "ASC";
				}
				break;
		}
		if (!$sortpercent) {
			$sortpercent = "ASC";
		}
		if (!$sortnr) {
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) {
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				if ($this->object->isOnlineTest() || ($this->object->getShowSolutionDetails() == 0))
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				else
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "outEvaluationForm") . "&evaluation=" . $value["qid"] . "\">" . $value["title"] . "</a>");
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				if ((preg_match("/http/", $value["solution"])) || (preg_match("/goto/", $value["solution"])))
				{
					$this->tpl->setVariable("SOLUTION_HINT", "<a href=\"".$value["solution"]."\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a>");
				}
				else
				{
					if ($value["solution"])
					{
						$this->tpl->setVariable("SOLUTION_HINT", $this->lng->txt($value["solution"]));
					}
					else
					{
						$this->tpl->setVariable("SOLUTION_HINT", "");
					}
				}
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("footer");
		$this->tpl->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$this->tpl->setVariable("VALUE_QUESTION_TITLE", "");
		$this->tpl->setVariable("SOLUTION_HINT", "");
		$this->tpl->setVariable("VALUE_MAX_POINTS", "<strong>" . sprintf("%d", $total_max) . "</strong>");
		$this->tpl->setVariable("VALUE_REACHED_POINTS", "<strong>" . sprintf("%d", $total_reached) . "</strong>");
		$this->tpl->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$this->tpl->parseCurrentBlock();

		if ($this->object->canShowSolutionPrintview($ilUser->getId()))
		{
			if ($this->object->isRandomTest())
			{
				$this->object->loadQuestions($active->active_id, $pass);
			}
			$counter = 1;
			// output of questions with solutions
			foreach ($this->object->questions as $question) 
			{
				$this->tpl->setCurrentBlock("printview_question");
				$question_gui = $this->object->createQuestionGUI("", $question);
	
				$this->tpl->setVariable("COUNTER_QUESTION", $counter.". ");
				$this->tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
				
				$active = $this->object->getActiveTestUser($ilUser->getId());
				$result_output = $question_gui->getSolutionOutput($active->active_id, $pass);
				$this->tpl->setVariable("SOLUTION_OUTPUT", $result_output);
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
			$this->tpl->setCurrentBlock("printview_details");
			$this->tpl->setVariable("RESULTS_OVERVIEW", $this->lng->txt("tst_eval_results_by_pass"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_COUNTER", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=nr&order=$sortnr\">" . $this->lng->txt("tst_question_no") . "</a>$img_title_nr");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=percent&order=$sortpercent\">" . $this->lng->txt("tst_percent_solved") . "</a>$img_title_percent");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		if ($this->object->getTestType() != TYPE_VARYING_RANDOMTEST)
		{
			$mark_obj = $this->object->mark_schema->getMatchingMark($percentage);
			if ($mark_obj)
			{
				if ($mark_obj->getPassed()) 
				{
					$mark = $this->lng->txt("tst_result_congratulations");
				} 
				else 
				{
					$mark = $this->lng->txt("tst_result_sorry");
				}
				$mark .= "<br />" . $this->lng->txt("tst_your_mark_is") . ": &quot;" . $mark_obj->getOfficialName() . "&quot;";
			}
			if ($this->object->ects_output)
			{
				$ects_mark = $this->object->getECTSGrade($total_reached, $total_max);
				$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
			}
			$this->tpl->setVariable("USER_FEEDBACK", $mark);
		}
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			$this->tpl->setVariable("BACK_TO_OVERVIEW", $this->lng->txt("tst_results_back_overview"));
		}
		else
		{
			$this->tpl->setVariable("BACK_TO_OVERVIEW", $this->lng->txt("tst_results_back_introduction"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("test_user_name");
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $ilUser->getFullname()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
	}

	function outEvaluationForm()
	{
		global $ilUser;

		$this->ctrl->saveParameter($this, "pass");
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$test_id = $this->object->getTestId();
		$question_gui = $this->object->createQuestionGUI("", $_GET["evaluation"]);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_evaluation.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$result_output = $question_gui->getSolutionOutput($active->active_id, NULL);
		$best_output = $question_gui->getSolutionOutput("");
		$this->tpl->setVariable("TEXT_YOUR_SOLUTION", $this->lng->txt("tst_your_answer_was"));
		$this->tpl->setVariable("TEXT_BEST_SOLUTION", $this->lng->txt("tst_best_solution_is"));
		$maxpoints = $question_gui->object->getMaximumPoints();
		if ($maxpoints == 1)
		{
			$this->tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle() . " (" . $maxpoints . " " . $this->lng->txt("point") . ")");
		}
		else
		{
			$this->tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle() . " (" . $maxpoints . " " . $this->lng->txt("points") . ")");
		}
		$this->tpl->setVariable("SOLUTION_OUTPUT", $result_output);
		$this->tpl->setVariable("BEST_OUTPUT", $best_output);
		$this->tpl->setVariable("RECEIVED_POINTS", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->getReachedPoints($active->active_id), $maxpoints));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Outputs all answers including the solutions for the active user
*
* Outputs all answers including the solutions for the active user
*
* @access public
*/
	function show_answers()
	{
		global $ilUser;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		$this->outShowAnswersDetails($ilUser->getId(), true);
	}

/**
* Output of the results of the active learner
*
* Output of the results of the active learner
*
* @access public
*/
	function showAnswersOfUser()
	{
		global $ilUser;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);			
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./assessment/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("navigation_buttons");
		$this->tpl->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "infoScreen"));
		$this->tpl->parseCurrentBlock();
		$invited_user =& $this->object->getInvitedUsers($ilUser->getId());
		$pagetitle = $this->object->getTitle() . " - " . $this->lng->txt("clientip") . 
			": " . $invited_user[$ilUser->getId()]->clientip . " - " . 
			$this->lng->txt("matriculation") . ": " . 
			$invited_user[$ilUser->getId()]->matriculation;
		$this->tpl->setVariable("PAGETITLE", $pagetitle);
		$this->outShowAnswersDetails($ilUser->getId());
	}

/**
* Outputs all answers including the solutions for the active user (output of the detail part)
*
* Outputs all answers including the solutions for the active user (output of the detail part)
*
* @access public
*/
	function outShowAnswersDetails($user_id, $isForm = false) 
	{
		$active = $this->object->getActiveTestUser($user_id);
		$t = $active->submittimestamp;
		include_once "./classes/class.ilObjUser.php";
		$ilUser = new ilObjUser($user_id);
		
		if (strlen($ilUser->getMatriculation()))
		{
			$this->tpl->setCurrentBlock("user_matric");
			$this->tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("user_matric_value");
			$this->tpl->setVariable("VALUE_USR_MATRIC", $ilUser->getMatriculation());
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("user_matric_separator");
		}

		$invited_users = array_pop($this->object->getInvitedUsers($ilUser->getId()));
		if (strlen($invited_users->clientip))
		{
			$this->tpl->setCurrentBlock("user_clientip");
			$this->tpl->setVariable("TXT_CLIENT_IP", $this->lng->txt("matriculation"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("user_clientip_value");
			$this->tpl->setVariable("VALUE_CLIENT_IP", $invited_users->clientip);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("user_clientip_separator");
		}
		
		include_once "./classes/class.ilUtil.php";

		// output of submit date and signature
		if ($active->submitted)
		{
			// only display submit date when it exists (not in the summary but in the print form)
			$this->tpl->setCurrentBlock("freefield_bottom");
			$this->tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
			$this->tpl->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", ilUtil::date_mysql2time($t)));

			$freefieldtypes = array(
				"freefield_bottom" => array(
					array(
						"title" => $this->lng->txt("tst_signature"), 
						"length" => 300
					)
				)
			);

			foreach ($freefieldtypes as $type => $freefields) 
			{
				$counter = 0;
				while ($counter < count($freefields)) 
				{
					$freefield = $freefields[$counter];
					$this->tpl->setVariable("TXT_FREE_FIELD", $freefield["title"]);
					$this->tpl->setVariable("IMG_SPACER", ilUtil::getImagePath("spacer.gif"));
					$counter ++;
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		$pass = NULL;
		if ($this->object->isRandomTest())
		{
			$pass = $this->object->_getResultPass($active->active_id);
			$this->object->loadQuestions($active->active_id, $pass);
		}
		$counter = 1;
		// output of questions with solutions
		foreach ($this->object->questions as $question) 
		{
			$this->tpl->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);

			$this->tpl->setVariable("COUNTER_QUESTION", $counter.". ");
			$this->tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			
			$active = $this->object->getActiveTestUser($ilUser->getId());
			$result_output = $question_gui->getSolutionOutput($active->active_id, $pass);
			$this->tpl->setVariable("SOLUTION_OUTPUT", $result_output);
			$this->tpl->parseCurrentBlock();
			$counter ++;
		}

		// output of submit buttons
		if ($isForm && !$active->submitted) 
		{
			$this->tpl->setCurrentBlock("confirm");
			$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("tst_submit_answers_txt"));
			$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("back"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_answers"));
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("answer_sheet");
		$this->tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_answer_sheet"));
		
		$this->tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$this->tpl->setVariable("VALUE_USR_NAME", $ilUser->getLastname().", ".$ilUser->getFirstname());
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * handle endingTimeReached
	 * @private
	 */
	
	function endingTimeReached() 
	{
		sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			if ($this->object->isOnlineTest())
				$this->outTestSummary();
			else
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", true);
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
	
	function outProcessingTime() 
	{
		global $ilUser;

		$starting_time = $this->object->getStartingTimeOfUser($ilUser->getId());
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
		$datenow = getdate();
		$this->tpl->setCurrentBlock("enableprocessingtime");
		$this->tpl->setVariable("USER_WORKING_TIME", 
			sprintf($this->lng->txt("tst_time_already_spent"),
				ilFormat::formatDate(
					$date["year"]."-".
					sprintf("%02d", $date["mon"])."-".
					sprintf("%02d", $date["mday"])." ".
					sprintf("%02d", $date["hours"]).":".
					sprintf("%02d", $date["minutes"]).":".
					sprintf("%02d", $date["seconds"])
				),
				$str_processing_time
			)
			. " <span id=\"timeleft\">" . sprintf($this->lng->txt("tst_time_already_spent_left"), $str_time_left) . "</span>"
		);
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
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"window.setInterval('setWorkingTime()',1000)\"");
	}
	
/**
	* Output of the learners view of an existing test without evaluation
	*
	* @access public
	*/
	function outTestSummary() 
	{
		global $ilUser;

		// handle solved state
		if (is_numeric($_GET["set_solved"]) && is_numeric($_GET["question_id"]))		 
		{
			$this->object->setQuestionSetSolved($_GET["set_solved"] , $_GET["question_id"], $ilUser->getId());
		}
			
		function sort_title($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			if (strcmp($a["title"],$b["title"])< 0)
				return $smaller;
			else if (strcmp($a["title"],$b["title"])> 0)
				return $greater;
			return 0;
		}
		
		
		function sort_nr($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}
		
		function sort_visited($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) 
				return 0;
			return ($a["visited"] < $b["visited"]) ? $smaller : $greater;
		}

		
		function sort_solved($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["solved"] < $b["solved"]) ? $smaller : $greater;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_summary.html", true);
		$user_id = $ilUser->id;
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$color_class = array ("tblrow1", "tblrow2");
		$counter = 0;
		
		$result_array = & $this->object->getTestSummary($active->active_id);
		
		$img_title_nr = "";
		$img_title_title = "";
		$img_title_solved = "";
		
		if (!$_GET["sort_summary"] )
		{
			$_GET["sort_summary"]  = "nr";
			$_GET["order"] = "ASC";
		} 
		
		switch ($_GET["sort_summary"]) 
		{
			case nr:
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortnr = "DESC";
				} 
				else 
				{
					$sortnr = "ASC";
				}
				break;			
			
			case "title":
				usort($result_array, "sort_title");
				$img_title_title = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sorttitle = "DESC";
				} 
				else 
				{
					$sorttitle = "ASC";
				}
				break;
			case "solved":
				usort($result_array, "sort_solved");
				$img_title_solved = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortsolved = "DESC";
				} 
				else 
				{
					$sortsolved = "ASC";
				}
				break;			
		}
		if (!$sorttitle) 
		{
			$sorttitle = "ASC";
		}
		if (!$sortsolved) 
		{
			$sortsolved = "ASC";
		}
		if (!$sortnr) 
		{
			$sortnr = "ASC";
		}
		
		$img_solved = " <img border=\"0\"  align=\"middle\" src=\"" . ilUtil::getImagePath("solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$img_not_solved = " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("not_solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$goto_question =  " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("goto_question.png", true) . "\" alt=\"".$this->lng->txt("tst_qst_goto")."\" />";
		
		$disabled = $this->isMaxProcessingTimeReached() | $this->object->endingTimeReached();
		
		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) 
			{
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				$this->tpl->setVariable("VALUE_QUESTION_SOLVED", ($value["solved"] > 0) ?$img_solved : $img_not_solved);  
				if (!$disabled)
				{
					$this->ctrl->setParameter($this, "sequence", $value["nr"]);
					$this->tpl->setVariable("VALUE_QUESTION_HREF_GOTO", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "gotoQuestion")."\">");
					$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
				}
				$this->tpl->setVariable("VALUE_QUESTION_GOTO", $goto_question);
				$solvedvalue = (($value["solved"]) ? "0" : "1");
				$this->tpl->setVariable("VALUE_QUESTION_HREF_SET_SOLVED", $this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"]."&set_solved=" . $solvedvalue."&question_id=".$value["qid"]);
				$this->tpl->setVariable("VALUE_QUESTION_SET_SOLVED", ($value["solved"] > 0) ?$this->lng->txt("tst_qst_resetsolved"):$this->lng->txt("tst_qst_setsolved"));
				$this->tpl->setVariable("VALUE_QUESTION_DESCRIPTION", $value["description"]);
				$this->tpl->setVariable("VALUE_QUESTION_POINTS", $value["points"]."&nbsp;".$this->lng->txt("points_short"));
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		if (!$disabled) 
		{
			$this->tpl->setCurrentBlock("back");
			$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
			$this->tpl->parseCurrentBlock();
		} 
		else 
		{
			sendinfo($this->lng->txt("detail_max_processing_time_reached"));
		}
		
		$this->tpl->setVariable("QUESTION_ACTION","actions");
		$this->tpl->setVariable("QUESTION_COUNTER","<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sortnr&sort_summary=nr\">".$this->lng->txt("tst_qst_order")."</a>".$img_title_nr);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sorttitle&sort_summary=title\">".$this->lng->txt("tst_question_title")."</a>".$img_title_title);
		$this->tpl->setVariable("QUESTION_SOLVED", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sortsolved&sort_summary=solved\">".$this->lng->txt("tst_question_solved_state")."</a>".$img_title_solved);
		$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("USER_FEEDBACK", $this->lng->txt("tst_qst_summary_text"));
		$this->tpl->setVariable("TXT_SHOW_AND_SUBMIT_ANSWERS", $this->lng->txt("save_finish"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));	
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("summary"));		
		
		if ($this->object->getEnableProcessingTime())
			$this->outProcessingTime();
	}
	
	function finalSubmission()
	{
		global $ilias, $ilUser;
		
		$this->object->setActiveTestSubmitted($ilUser->id);
		$ilias->auth->setIdle($ilias->ini->readVariable("session","expire"), false);
		$ilias->auth->setExpire(0);
		$this->outIntroductionPage();
	}
	
/**
	* Output of a summary of all test questions for test participants
	*
	* @access public
	*/
	function outQuestionSummary() 
	{
		global $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_question_summary.html", true);
		$user_id = $ilUser->id;
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$color_class = array ("tblrow1", "tblrow2");
		$counter = 0;
		
		$result_array = & $this->object->getTestSummary($active->active_id);
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
				$this->tpl->setVariable("VALUE_QUESTION_DESCRIPTION", $value["description"]);
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
				$this->tpl->setVariable("VALUE_QUESTION_POINTS", $value["points"]."&nbsp;".$this->lng->txt("points_short"));
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		$this->tpl->setVariable("QUESTION_ACTION","actions");
		$this->tpl->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_qst_order"));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("worked_through"));
		$this->tpl->setVariable("USER_FEEDBACK", $this->lng->txt("tst_qst_summary_text"));
		$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("TXT_SHOW_AND_SUBMIT_ANSWERS", $this->lng->txt("save_finish"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));	
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("summary"));		
		
		if ($this->object->getEnableProcessingTime())
			$this->outProcessingTime();
	}
	
	function showMaximumAllowedUsersReachedMessage()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_max_allowed_users_reached.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("MAX_ALLOWED_USERS_MESSAGE", sprintf($this->lng->txt("tst_max_allowed_users_message"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("MAX_ALLOWED_USERS_HEADING", sprintf($this->lng->txt("tst_max_allowed_users_heading"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("BACK_TO_INTRODUCTION", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
 
}
?>
