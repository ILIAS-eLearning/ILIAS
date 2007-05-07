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

/**
* Service GUI class for tests. This class is the parent class for all
* service classes which are called from ilObjTestGUI. This is mainly
* done to reduce the size of ilObjTestGUI to put command service functions
* into classes that could be called by ilCtrl.
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilTestServiceGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;
	var $ref_id;
	
/**
* ilTestScoringGUI constructor
*
* The constructor takes the test object reference as parameter 
*
* @param object $a_object Associated ilObjTest class
* @access public
*/
  function ilTestServiceGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
		$this->ref_id = $a_object->ref_id;
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
* Returns the pass overview for a given active ID
*
* Returns the pass overview for a given active ID
*
* @return string HTML code of the pass overview
* @access public
*/
	function getPassOverview($active_id, $targetclass = "", $targetcommand = "", $short = FALSE)
	{
		global $ilUser;

		if ($short)
		{
			$template = new ilTemplate("tpl.il_as_tst_pass_overview_short.html", TRUE, TRUE, "Modules/Test");
		}
		else
		{
			$template = new ilTemplate("tpl.il_as_tst_pass_overview.html", TRUE, TRUE, "Modules/Test");
		}
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;

		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		$counted_pass = $this->object->_getResultPass($active_id);
		$reached_pass = $this->object->_getPass($active_id);

		$result_percentage = 0;
		$result_total_reached = 0;
		$result_total_max = 0;
		for ($pass = 0; $pass <= $reached_pass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($active_id, $pass);
			if ($finishdate > 0)
			{
				if (!$short)
				{
					$result_array =& $this->object->getTestResult($active_id, $pass);
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
				}
				if (strlen($targetclass) && strlen($targetcommand))
				{
					$this->ctrl->setParameterByClass($targetclass, "active_id", $active_id);
					$this->ctrl->setParameterByClass($targetclass, "pass", $pass);
					$template->setCurrentBlock("pass_details");
					$template->setVariable("HREF_PASS_DETAILS", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommand));
					$template->setVariable("TEXT_PASS_DETAILS", $this->lng->txt("tst_pass_details"));
					if (($pass == $counted_pass) && (!$short))
					{
						$template->setVariable("COLOR_CLASS", "tblrowmarked");
					}
					else
					{
						$template->setVariable("COLOR_CLASS", $color_class[$pass % 2]);
					}
					$template->parseCurrentBlock();
				}

				$template->setCurrentBlock("result_row");

				if (($pass == $counted_pass) && (!$short))
				{
					$template->setVariable("COLOR_CLASS", "tblrowmarked");
					$template->setVariable("VALUE_SCORED", "&otimes;");
					$result_percentage = $percentage;
					$result_total_reached = $total_reached;
					$result_total_max = $total_max;
				}
				else
				{
					$template->setVariable("COLOR_CLASS", $color_class[$pass % 2]);
				}
				$template->setVariable("VALUE_PASS", $pass + 1);
				$template->setVariable("VALUE_DATE", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($finishdate), "date"));
				if (!$short)
				{
					$template->setVariable("VALUE_ANSWERED", $this->object->getAnsweredQuestionCount($active_id, $pass) . " " . strtolower($this->lng->txt("of")) . " " . (count($result_array)-1));
					$template->setVariable("VALUE_REACHED", $total_reached . " " . strtolower($this->lng->txt("of")) . " " . $total_max);
					$template->setVariable("VALUE_PERCENTAGE", sprintf("%.2f", $percentage) . "%");
				}
				$template->parseCurrentBlock();
			}
		}

		$template->setVariable("PASS_COUNTER", $this->lng->txt("pass"));
		$template->setVariable("DATE", $this->lng->txt("date"));
		if (!$short)
		{
			$template->setVariable("PASS_SCORED", $this->lng->txt("scored_pass"));
			$template->setVariable("ANSWERED_QUESTIONS", $this->lng->txt("tst_answered_questions"));
			$template->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
			$template->setVariable("PERCENTAGE_CORRECT", $this->lng->txt("tst_percent_solved"));
		}
		$template->parseCurrentBlock();
		
		return $template->get();
	}

/**
* Returns the final statement for a user
*
* Returns the final statement for a user
*
* @param array An array containing the information on reached points, max points etc. ("test" key of ilObjTest::getTestResult)
* @return string HTML code of the final statement
* @access public
*/
	function getFinalStatement(&$test_data_array)
	{
		if (!$test_data_array["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($test_data_array["total_reached_points"]/$test_data_array["total_max_points"])*100;
		}
		$total_max = $test_data_array["total_max_points"];
		$total_reached = $test_data_array["total_reached_points"];
		$result_percentage = $percentage;
		$result_total_reached = $total_reached;
		$result_total_max = $total_max;

		$mark = "";
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
		return $mark;
	}

/**
* Returns the list of answers of a users test pass
*
* Returns the list of answers of a users test pass
*
* @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
* @param integer $active_id Active ID of the active user
* @param integer $pass Test pass
* @param boolean $show_solutions TRUE, if the solution output should be shown in the answers, FALSE otherwise
* @return string HTML code of the list of answers
* @access public
*/
	function getPassListOfAnswers(&$result_array, $active_id, $pass, $show_solutions = FALSE, $only_answered_questions = FALSE)
	{
		$maintemplate = new ilTemplate("tpl.il_as_tst_list_of_answers.html", TRUE, TRUE, "Modules/Test");

		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			if (($question_data["workedthrough"] == 1) || ($only_answered_questions == FALSE))
			{
				$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
				$question = $question_data["qid"];
				if (is_numeric($question))
				{
					$this->tpl->setCurrentBlock("printview_question");
					$question_gui = $this->object->createQuestionGUI("", $question);

					$template->setVariable("COUNTER_QUESTION", $counter.". ");
					$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());

					$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, FALSE, $this->object->getShowSolutionFeedback());

					$template->setVariable("SOLUTION_OUTPUT", $result_output);
					$maintemplate->setCurrentBlock("printview_question");
					$maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
					$maintemplate->parseCurrentBlock();
					$counter ++;
				}
			}
		}
		$maintemplate->setVariable("RESULTS_OVERVIEW", sprintf($this->lng->txt("tst_eval_results_by_pass"), $pass+1));
		return $maintemplate->get();
	}
	
/**
* Returns the list of answers of a users test pass and offers a scoring option
*
* Returns the list of answers of a users test pass and offers a scoring option
*
* @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
* @param integer $active_id Active ID of the active user
* @param integer $pass Test pass
* @param boolean $show_solutions TRUE, if the solution output should be shown in the answers, FALSE otherwise
* @return string HTML code of the list of answers
* @access public
*/
	function getPassListOfAnswersWithScoring(&$result_array, $active_id, $pass, $show_solutions = FALSE)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		
		$maintemplate = new ilTemplate("tpl.il_as_tst_list_of_answers.html", TRUE, TRUE, "Modules/Test");

		include_once "./classes/class.ilObjAssessmentFolder.php";
		$scoring = ilObjAssessmentFolder::_getManualScoring();
		
		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			$question = $question_data["qid"];
			if (is_numeric($question))
			{
				$question_gui = $this->object->createQuestionGUI("", $question);
				if (in_array($question_gui->object->getQuestionTypeID(), $scoring))
				{
					$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$scoretemplate = new ilTemplate("tpl.il_as_tst_manual_scoring_points.html", TRUE, TRUE, "Modules/Test");
					$this->tpl->setCurrentBlock("printview_question");
					$template->setVariable("COUNTER_QUESTION", $counter.". ");
					$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
					$points = $question_gui->object->getMaximumPoints();
					if ($points == 1)
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("point"));
					}
					else
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("points"));
					}
					
					$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, FALSE, $this->object->getShowSolutionFeedback());
		
					$scoretemplate->setVariable("NAME_INPUT", $question);
					$scoretemplate->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
					$scoretemplate->setVariable("LABEL_INPUT", $this->lng->txt("tst_change_points_for_question"));
					$scoretemplate->setVariable("BUTTON_POINTS", $this->lng->txt("change"));
					$scoretemplate->setVariable("VALUE_INPUT", " value=\"" . assQuestion::_getReachedPoints($active_id, $question_data["qid"], $pass) . "\"");
					
					$template->setVariable("SOLUTION_OUTPUT", $result_output);
					$maintemplate->setCurrentBlock("printview_question");
					$maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
					$maintemplate->setVariable("QUESTION_SCORING", $scoretemplate->get());
					$maintemplate->parseCurrentBlock();
					$counter ++;
				}
			}
		}
		if ($counter == 1)
		{
			// no scorable questions found
			$maintemplate->setVariable("NO_QUESTIONS_FOUND", $this->lng->txt("manscoring_questions_not_found"));
		}
		$maintemplate->setVariable("RESULTS_OVERVIEW", sprintf($this->lng->txt("manscoring_results_pass"), $pass+1));
		return $maintemplate->get();
	}
	
/**
* Returns the pass details overview for a given active ID and pass
*
* Returns the pass details overview for a given active ID and pass
*
* @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
* @param integer $active_id Active ID of the active user
* @param integer $pass Test pass
* @param string $targetclass The name of the ILIAS class for the "pass details" URL (optional)
* @param string $targetcommand The name of the ILIAS command for the "pass details" URL (optional)
* @param string $targetcommanddetails The name of the ILIAS command which should be called for the details of an answer (optional)
* @return string HTML code of the pass details overview
* @access public
*/
	function getPassDetailsOverview(&$result_array, $active_id, $pass, $targetclass = "", $targetcommandsort = "", $targetcommanddetails = "")
	{
		/**
		* Helper function to sort the pass details by percentage
		*/
		function sort_percent($a, $b) 
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
			if ($a["percent"] == $b["percent"]) 
			{
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

		/**
		* Helper function to sort the pass details by the question sequence
		*/
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

		global $ilUser;

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$template = new ilTemplate("tpl.il_as_tst_pass_details_overview.html", TRUE, TRUE, "Modules/Test");

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
		switch ($_GET["sortres"]) 
		{
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.gif") . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
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
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.gif") . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
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
		if (!$sortpercent) 
		{
			$sortpercent = "ASC";
		}
		if (!$sortnr) 
		{
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) 
			{
				if (strlen($targetclass) && strlen($targetcommanddetails))
				{
					$template->setCurrentBlock("linked_title");
					$template->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$template->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
					$this->ctrl->setParameterByClass($targetclass, "evaluation", $value["qid"]);
					$template->setVariable("URL_QUESTION_TITLE", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommanddetails));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("plain_title");
					$template->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
					$template->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$template->parseCurrentBlock();
				}


				$template->setCurrentBlock("question");
				$template->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$template->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$template->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$template->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				if ((preg_match("/http/", $value["solution"])) || (preg_match("/goto/", $value["solution"])))
				{
					$template->setVariable("SOLUTION_HINT", "<a href=\"".$value["solution"]."\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a>");
				}
				else
				{
					if ($value["solution"])
					{
						$template->setVariable("SOLUTION_HINT", $this->lng->txt($value["solution"]));
					}
					else
					{
						$template->setVariable("SOLUTION_HINT", "");
					}
				}
				$template->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$template->parseCurrentBlock();
				$counter++;
			}
		}

		$template->setCurrentBlock("footer");
		$template->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$template->setVariable("VALUE_QUESTION_TITLE", "");
		$template->setVariable("SOLUTION_HINT", "");
		$template->setVariable("VALUE_MAX_POINTS", "<strong>$total_max</strong>");
		$template->setVariable("VALUE_REACHED_POINTS", "<strong>$total_reached</strong>");
		$template->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$template->parseCurrentBlock();

		if (strlen($targetclass) && strlen($targetcommandsort))
		{
			$template->setCurrentBlock("question_counter_url");
			$this->ctrl->setParameterByClass($targetclass, "sortres", "nr");
			$this->ctrl->setParameterByClass($targetclass, "order", "$sortnr");
			$template->setVariable("URL_QUESTION_COUNTER",  $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no") . $img_title_nr);
			$template->parseCurrentBlock();
		}
		else
		{
			$template->setCurrentBlock("question_counter_plain");
			$template->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no"));
			$template->parseCurrentBlock();
		}

		if (strlen($targetclass) && strlen($targetcommandsort))
		{
			$template->setCurrentBlock("percent_url");
			$this->ctrl->setParameterByClass($targetclass, "sortres", "percent");
			$this->ctrl->setParameterByClass($targetclass, "order", "$sortpercent");
			$template->setVariable("URL_PERCENT_SOLVED",  $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved") . $img_title_percent);
			$template->parseCurrentBlock();
		}
		else
		{
			$template->setCurrentBlock("percent_plain");
			$template->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved"));
			$template->parseCurrentBlock();
		}

		$template->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$template->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$template->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$template->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));

		return $template->get();
	}

/**
* Returns HTML code for a signature field
*
* Returns HTML code for a signature field
*
* @return string HTML code of the date and signature field for the test results
* @access public
*/
	function getResultsSignature()
	{
		// output of time/date and signature
		$template = new ilTemplate("tpl.il_as_tst_results_userdata_signature.html", TRUE, TRUE, "Modules/Test");
		$template->setVariable("TXT_DATE", $this->lng->txt("date"));
		$template->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", time()));
		$template->setVariable("TXT_SIGNATURE", $this->lng->txt("tst_signature"));
		$template->setVariable("IMG_SPACER", ilUtil::getImagePath("spacer.gif"));
		return $template->get();
	}
	
/**
* Returns the user data for a test results output
*
* Returns the user data for a test results output
*
* @param integer $user_id The user ID of the user
* @param boolean $overwrite_anonymity TRUE if the anonymity status should be overwritten, FALSE otherwise
* @return string HTML code of the user data for the test results
* @access public
*/
	function getResultsUserdata($user_id, $overwrite_anonymity = FALSE)
	{
		$template = new ilTemplate("tpl.il_as_tst_results_userdata.html", TRUE, TRUE, "Modules/Test");
		include_once "./classes/class.ilObjUser.php";
		$user = new ilObjUser($user_id);
		$active = $this->object->getActiveTestUser($user_id);
		$t = $active->submittimestamp;
		if (!$t)
		{
			$t = $this->object->_getLastAccess($active->active_id);
		}
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));

		$title_matric = "";
		if (strlen($user->getMatriculation()) && (($this->object->getAnonymity() == FALSE) || ($overwrite_anonymity)))
		{
			$template->setCurrentBlock("user_matric");
			$template->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_matric_value");
			$template->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
			$template->parseCurrentBlock();
			$template->touchBlock("user_matric_separator");
			$title_matric = " - " . $this->lng->txt("matriculation") . ": " . $user->getMatriculation();
		}

		$invited_user = array_pop($this->object->getInvitedUsers($user_id));
		if (strlen($invited_user->clientip))
		{
			$this->tpl->setCurrentBlock("user_clientip");
			$this->tpl->setVariable("TXT_CLIENT_IP", $this->lng->txt("matriculation"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("user_clientip_value");
			$this->tpl->setVariable("VALUE_CLIENT_IP", $invited_users->clientip);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("user_clientip_separator");
			$title_client = " - " . $this->lng->txt("clientip") . ": " . $invited_user->clientip;
		}

		$template->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$template->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$template->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$uname = $this->object->userLookupFullName($user_id, $overwrite_anonymity);
		$template->setVariable("VALUE_USR_NAME", $uname);
		$template->setVariable("TXT_TEST_DATE", $this->lng->txt("tst_tst_date"));
		$template->setVariable("VALUE_TEST_DATE", strftime("%Y-%m-%d %H:%M:%S",ilUtil::date_mysql2time($t)));
		$template->setVariable("TXT_PRINT_DATE", $this->lng->txt("tst_print_date"));
		$template->setVariable("VALUE_PRINT_DATE", strftime("%Y-%m-%d %H:%M:%S",$print_date));
		
		// change the pagetitle
		$pagetitle = ": " . $this->object->getTitle() . $title_matric . $title_client;
		$this->tpl->setHeaderPageTitle($pagetitle);
		
		return $template->get();
	}

	/**
	* Returns an output of the solution to an answer compared to the correct solution
	*
	* Returns an output of the solution to an answer compared to the correct solution
	*
	* @param integer $question_id Database ID of the question
	* @param integer $active_id Active ID of the active user
	* @param integer $pass Test pass
	* @return string HTML code of the correct solution comparison
	* @access public
	*/
	function getCorrectSolutionOutput($question_id, $active_id, $pass)
	{
		global $ilUser;

		$test_id = $this->object->getTestId();
		$question_gui = $this->object->createQuestionGUI("", $question_id);

		$template = new ilTemplate("tpl.il_as_tst_correct_solution_output.html", TRUE, TRUE, "Modules/Test");
		$result_output = $question_gui->getSolutionOutput($active_id, $pass, TRUE, FALSE, FALSE, $this->object->getShowSolutionFeedback());
		$best_output = $question_gui->getSolutionOutput("", NULL, FALSE, FALSE, FALSE);
		$template->setVariable("TEXT_YOUR_SOLUTION", $this->lng->txt("tst_your_answer_was"));
		$template->setVariable("TEXT_BEST_SOLUTION", $this->lng->txt("tst_best_solution_is"));
		$maxpoints = $question_gui->object->getMaximumPoints();
		if ($maxpoints == 1)
		{
			$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle() . " (" . $maxpoints . " " . $this->lng->txt("point") . ")");
		}
		else
		{
			$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle() . " (" . $maxpoints . " " . $this->lng->txt("points") . ")");
		}
		$template->setVariable("SOLUTION_OUTPUT", $result_output);
		$template->setVariable("BEST_OUTPUT", $best_output);
		$template->setVariable("RECEIVED_POINTS", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->getReachedPoints($active_id, $pass), $maxpoints));
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		return $template->get();
	}

}

?>
