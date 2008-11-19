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
* @ilCtrl_IsCalledBy ilTestServiceGUI: ilObjTestGUI
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
	function getPassOverview($active_id, $targetclass = "", $targetcommand = "", $short = FALSE, $hide_details = FALSE)
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
				if (!$hide_details)
				{
					if (strlen($targetclass) && strlen($targetcommand))
					{
						$this->ctrl->setParameterByClass($targetclass, "active_id", $active_id);
						$this->ctrl->setParameterByClass($targetclass, "pass", $pass);
						$template->setCurrentBlock("pass_details");
						$template->setVariable("HREF_PASS_DETAILS", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommand));
						$template->setVariable("TEXT_PASS_DETAILS", $this->lng->txt("tst_pass_details"));
						$template->parseCurrentBlock();
					}
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
				$template->setVariable("VALUE_DATE",ilDatePresentation::formatDate(new ilDate($finishdate,IL_CAL_TIMESTAMP)));
				
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
		$markects = "";
		$mark_obj = $this->object->mark_schema->getMatchingMark($result_percentage);
		if ($mark_obj)
		{
			if ($mark_obj->getPassed()) 
			{
				$mark = $this->lng->txt("mark_tst_passed");
			} 
			else 
			{
				$mark = $this->lng->txt("mark_tst_failed");
			}
			$mark = str_replace("[mark]", $mark_obj->getOfficialName(), $mark);
			$mark = str_replace("[markshort]", $mark_obj->getShortName(), $mark);
			$mark = str_replace("[percentage]", sprintf("%.2f", $result_percentage), $mark);
			$mark = str_replace("[reached]", $result_total_reached, $mark);
			$mark = str_replace("[max]", $result_total_max, $mark);
		}
		if ($this->object->ects_output)
		{
			$ects_mark = $this->object->getECTSGrade($result_total_reached, $result_total_max);
			$markects = $this->lng->txt("mark_tst_ects");
			$markects = str_replace("[markects]", $this->lng->txt("ects_grade_". strtolower($ects_mark)), $markects);
		}
		return array("mark" => $mark, "markects" => $markects);
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
	function getPassListOfAnswers(&$result_array, $active_id, $pass, $show_solutions = FALSE, $only_answered_questions = FALSE, $show_question_only = FALSE, $show_reached_points = FALSE)
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
					$maintemplate->setCurrentBlock("printview_question");
					$question_gui = $this->object->createQuestionGUI("", $question);

					if ($show_reached_points)
					{
						$template->setCurrentBlock("result_points");
						$template->setVariable("RESULT_POINTS", $this->lng->txt("tst_reached_points") . ": " . $question_gui->object->getReachedPoints($active_id, $pass) . " " . $this->lng->txt("of") . " " . $question_gui->object->getMaximumPoints());
						$template->parseCurrentBlock();
					}
					$template->setVariable("COUNTER_QUESTION", $counter.". ");
					$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()));

					$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
					$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, $show_question_only, $this->object->getShowSolutionFeedback());

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

		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
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
					$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()));
					$points = $question_gui->object->getMaximumPoints();
					if ($points == 1)
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("point"));
					}
					else
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("points"));
					}
					
					$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
					$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, $show_question_only, $this->object->getShowSolutionFeedback());
//					if ($this->object->getShowSolutionFeedback())
//					{
						$scoretemplate->setCurrentBlock("feedback");
						$scoretemplate->setVariable("FEEDBACK_NAME_INPUT", $question);
						$feedback = $this->object->getManualFeedback($active_id, $question, $pass);
						$scoretemplate->setVariable("VALUE_FEEDBACK", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($feedback, TRUE)));
						$scoretemplate->setVariable("VALUE_SAVE", $this->lng->txt("save"));
						$scoretemplate->setVariable("TEXT_MANUAL_FEEDBACK", $this->lng->txt("set_manual_feedback"));
						$scoretemplate->parseCurrentBlock();
//					}
					$scoretemplate->setVariable("NAME_INPUT", $question);
					$this->ctrl->setParameter($this, "active_id", $active_id);
					$this->ctrl->setParameter($this, "pass", $pass);
					$scoretemplate->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "manscoring"));
					$scoretemplate->setVariable("LABEL_INPUT", $this->lng->txt("tst_change_points_for_question"));
					$scoretemplate->setVariable("BUTTON_POINTS", $this->lng->txt("change"));
					$scoretemplate->setVariable("VALUE_INPUT", " value=\"" . assQuestion::_getReachedPoints($active_id, $question_data["qid"], $pass) . "\"");
					
					$template->setVariable("SOLUTION_OUTPUT", $result_output);
					$maintemplate->setCurrentBlock("printview_question");
					$maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
					$maintemplate->setVariable("QUESTION_SCORING", $scoretemplate->get());
					$maintemplate->parseCurrentBlock();
				}
				$counter ++;
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
* @param boolean $standard_header TRUE if the table headers should be plain text, FALSE if the table headers should be URL's for sortable columns
* @return string HTML code of the pass details overview
* @access public
*/
	function getPassDetailsOverview($result_array, $active_id, $pass, $targetclass = "", $targetcommandsort = "", $targetcommanddetails = "", $standard_header = TRUE)
	{
		global $ilUser;

		$testresults = $result_array["test"];
		unset($result_array["test"]);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$sort = ($_GET["sort"]) ? ($_GET["sort"]) : "nr";
		$sortorder = ($_GET["sortorder"]) ? ($_GET["sortorder"]) : "asc";

		if (!$standard_header)
		{
			// change sortorder of result array
			usort($result_array, "sortResults");
		}
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$template = new ilTemplate("tpl.il_as_tst_pass_details_overview.html", TRUE, TRUE, "Modules/Test");
		$this->ctrl->setParameterByClass($targetclass, "pass", "$pass");

		if (!$testresults["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($testresults["total_reached_points"]/$testresults["total_max_points"])*100;
		}
		$total_max = $testresults["total_max_points"];
		$total_reached = $testresults["total_reached_points"];

		$img_title_percent = "";
		$img_title_nr = "";
		$hasSuggestedSolutions = FALSE;
		
		foreach ($result_array as $key => $value)
		{
			if (strlen($value["solution"]))
			{
				$hasSuggestedSolutions = TRUE;
			}
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
					$this->ctrl->setParameterByClass($targetclass, "active_id", $active_id);
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
				if ($hasSuggestedSolutions)
				{
					$template->setCurrentBlock("question_suggested_solution");
					$template->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
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
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("question");
				$template->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$template->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$template->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$template->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				$template->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$template->parseCurrentBlock();
				$counter++;
			}
		}

		if ($hasSuggestedSolutions)
		{
			$template->touchBlock("footer_suggested_solution");
		}
		$template->setCurrentBlock("footer");
		$template->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$template->setVariable("VALUE_QUESTION_TITLE", "");
		$template->setVariable("VALUE_MAX_POINTS", "<strong>$total_max</strong>");
		$template->setVariable("VALUE_REACHED_POINTS", "<strong>$total_reached</strong>");
		$template->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$template->parseCurrentBlock();

		if ($standard_header)
		{
			if ($hasSuggestedSolutions)
			{
				$template->setCurrentBlock("standard_header_suggested_solution");
				$template->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("standard_header");
			$template->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no"));
			$template->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved"));
			$template->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
			$template->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
			$template->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
			$template->parseCurrentBlock();
		}
		else
		{
			if ($hasSuggestedSolutions)
			{
				$template->setCurrentBlock("linked_header_suggested_solution");
				if (strcmp($sort, "solution") == 0)
				{
					$this->ctrl->setParameterByClass($targetclass, "sortorder", !strcmp($sortorder, "asc") ? "desc" : "asc");
				}
				else
				{
					$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
				}
				$this->ctrl->setParameterByClass($targetclass, "sort", "solution");
				$template->setVariable("URL_SOLUTION_HINT_HEADER", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
				$template->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
				if (strcmp($sort, "solution") == 0)
				{
					$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
					$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
					$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
					$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
					$template->setVariable("IMAGE_SOLUTION_HINT_HEADER", $image->get());
				}
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("linked_header");
			$this->ctrl->setParameterByClass($targetclass, "sort", "nr");
			if (strcmp($sort, "nr") == 0)
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", !strcmp($sortorder, "asc") ? "desc" : "asc");
			}
			else
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
			}
			$template->setVariable("URL_QUESTION_COUNTER", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no"));
			if (strcmp($sort, "nr") == 0)
			{
				$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
				$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
				$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
				$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
				$template->setVariable("IMAGE_QUESTION_COUNTER", $image->get());
			}
			$this->ctrl->setParameterByClass($targetclass, "sort", "percent");
			if (strcmp($sort, "percent") == 0)
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", !strcmp($sortorder, "asc") ? "desc" : "asc");
			}
			else
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
			}
			$template->setVariable("URL_PERCENT_SOLVED", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved"));
			if (strcmp($sort, "percent") == 0)
			{
				$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
				$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
				$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
				$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
				$template->setVariable("IMAGE_PERCENT_SOLVED", $image->get());
			}
			$this->ctrl->setParameterByClass($targetclass, "sort", "title");
			if (strcmp($sort, "title") == 0)
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", !strcmp($sortorder, "asc") ? "desc" : "asc");
			}
			else
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
			}
			$template->setVariable("URL_QUESTION_TITLE", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
			if (strcmp($sort, "title") == 0)
			{
				$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
				$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
				$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
				$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
				$template->setVariable("IMAGE_QUESTION_TITLE", $image->get());
			}
			$this->ctrl->setParameterByClass($targetclass, "sort", "max");
			if (strcmp($sort, "max") == 0)
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", strcmp($sortorder, "asc") ? "desc" : "asc");
			}
			else
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
			}
			$template->setVariable("URL_MAX_POINTS", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
			if (strcmp($sort, "max") == 0)
			{
				$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
				$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
				$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
				$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
				$template->setVariable("IMAGE_MAX_POINTS", $image->get());
			}
			$this->ctrl->setParameterByClass($targetclass, "sort", "reached");
			if (strcmp($sort, "reached") == 0)
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", !strcmp($sortorder, "asc") ? "desc" : "asc");
			}
			else
			{
				$this->ctrl->setParameterByClass($targetclass, "sortorder", "asc");
			}
			$template->setVariable("URL_REACHED_POINTS", $this->ctrl->getLinkTargetByClass($targetclass, $targetcommandsort));
			$template->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
			if (strcmp($sort, "reached") == 0)
			{
				$image = new ilTemplate("tpl.image.html", TRUE, TRUE);
				$image->setVariable("IMAGE_SOURCE", ilUtil::getImagePath($sortorder . "_order.gif"));
				$image->setVariable("IMAGE_ALT", $this->lng->txt("change_sort_direction"));
				$image->setVariable("IMAGE_TITLE", $this->lng->txt("change_sort_direction"));
				$template->setVariable("IMAGE_REACHED_POINTS", $image->get());
			}
			$template->parseCurrentBlock();
		}

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
		if ($this->object->getShowSolutionSignature() && !$this->object->getAnonymity())
		{
			// output of time/date and signature
			$template = new ilTemplate("tpl.il_as_tst_results_userdata_signature.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("TXT_DATE", $this->lng->txt("date"));
			$template->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", time()));
			$template->setVariable("TXT_SIGNATURE", $this->lng->txt("tst_signature"));
			$template->setVariable("IMG_SPACER", ilUtil::getImagePath("spacer.gif"));
			return $template->get();
		}
		else
		{
			return "";
		}
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
	function getResultsUserdata($active_id, $overwrite_anonymity = FALSE)
	{
		$template = new ilTemplate("tpl.il_as_tst_results_userdata.html", TRUE, TRUE, "Modules/Test");
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		if (strlen(ilObjUser::_lookupLogin($user_id)) > 0)
		{
			$user = new ilObjUser($user_id);
		}
		else
		{
			$user = new ilObjUser();
			$user->setLastname($this->lng->txt("deleted_user"));
		}
		$t = $this->object->getTestSession($active_id)->getSubmittedTimestamp();
		if (!$t)
		{
			$t = $this->object->_getLastAccess($this->object->getTestSession()->getActiveId());
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
			$template->setCurrentBlock("user_clientip");
			$template->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_clientip_value");
			$template->setVariable("VALUE_CLIENT_IP", $invited_user->clientip);
			$template->parseCurrentBlock();
			$template->touchBlock("user_clientip_separator");
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
		$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
		$result_output = $question_gui->getSolutionOutput($active_id, $pass, TRUE, FALSE, $show_question_only, $this->object->getShowSolutionFeedback());
		$best_output = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, $show_question_only, FALSE, TRUE);
		$template->setVariable("TEXT_YOUR_SOLUTION", $this->lng->txt("tst_your_answer_was"));
		if (strlen($best_output)) $template->setVariable("TEXT_BEST_SOLUTION", $this->lng->txt("tst_best_solution_is"));
		$maxpoints = $question_gui->object->getMaximumPoints();
		if ($maxpoints == 1)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("point") . ")");
		}
		else
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("points") . ")");
		}
		$template->setVariable("SOLUTION_OUTPUT", $result_output);
		$template->setVariable("BEST_OUTPUT", $best_output);
		$template->setVariable("RECEIVED_POINTS", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->getReachedPoints($active_id, $pass), $maxpoints));
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		return $template->get();
	}

	/**
	* Output of the pass overview for a test called by a test participant
	*
	* Output of the pass overview for a test called by a test participant
	*
	* @access public
	*/
	function getResultsOfUserOutput($active_id, $pass, $show_pass_details = TRUE, $show_answers = TRUE, $show_question_only = FALSE, $show_reached_points = FALSE)
	{
		global $ilias, $tpl;

		include_once("./classes/class.ilTemplate.php");
		$template = new ilTemplate("tpl.il_as_tst_results_participant.html", TRUE, TRUE, "Modules/Test");

		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		$uname = $this->object->userLookupFullName($user_id, TRUE);

		if (((array_key_exists("pass", $_GET)) && (strlen($_GET["pass"]) > 0)) || (!is_null($pass)))
		{
			if (is_null($pass))	$pass = $_GET["pass"];
		}

		$result_pass = $this->object->_getResultPass($active_id);
		$result_array =& $this->object->getTestResult($active_id, $result_pass);
		$statement = $this->getFinalStatement($result_array["test"]);
		$user_data = $this->getResultsUserdata($active_id, TRUE);

		if (!is_null($pass))
		{
			$result_array =& $this->object->getTestResult($active_id, $pass);
			$command_solution_details = "";
			if ($show_pass_details)
			{
				$detailsoverview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestservicegui", "getResultsOfUserOutput", $command_solution_details);
			}

			$user_id = $this->object->_getUserIdFromActiveId($active_id);
			$showAllAnswers = TRUE;
			if ($this->object->isExecutable($user_id))
			{
				$showAllAnswers = FALSE;
			}
			if ($show_answers)
			{
				$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, FALSE, $showAllAnswers, $show_question_only, $show_reached_points);
			}

			$template->setVariable("LIST_OF_ANSWERS", $list_of_answers);
			//$template->setVariable("PASS_RESULTS_OVERVIEW", sprintf($this->lng->txt("tst_results_overview_pass"), $pass + 1));
			$template->setVariable("PASS_DETAILS", $detailsoverview);

			$signature = $this->getResultsSignature();
			$template->setVariable("SIGNATURE", $signature);
		}
		$template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name"), $uname));
		$template->setVariable("USER_DATA", $user_data);
		$template->setVariable("USER_MARK", $statement["mark"]);
		if (strlen($statement["markects"]))
		{
			$template->setVariable("USER_MARK_ECTS", $statement["markects"]);
		}
		$template->parseCurrentBlock();

		return $template->get();
	}

	/**
	* Returns the user and pass data for a test results output
	*
	* @param integer $active_id The active ID of the user
	* @return string HTML code of the user data for the test results
	* @access public
	*/
	function getResultsHeadUserAndPass($active_id, $pass)
	{
		$template = new ilTemplate("tpl.il_as_tst_results_head_user_pass.html", TRUE, TRUE, "Modules/Test");
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		if (strlen(ilObjUser::_lookupLogin($user_id)) > 0)
		{
			$user = new ilObjUser($user_id);
		}
		else
		{
			$user = new ilObjUser();
			$user->setLastname($this->lng->txt("deleted_user"));
		}
		$title_matric = "";
		if (strlen($user->getMatriculation()) && (($this->object->getAnonymity() == FALSE)))
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
			$template->setCurrentBlock("user_clientip");
			$template->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_clientip_value");
			$template->setVariable("VALUE_CLIENT_IP", $invited_user->clientip);
			$template->parseCurrentBlock();
			$template->touchBlock("user_clientip_separator");
			$title_client = " - " . $this->lng->txt("clientip") . ": " . $invited_user->clientip;
		}

		$template->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$uname = $this->object->userLookupFullName($user_id, FALSE);
		$template->setVariable("VALUE_USR_NAME", $uname);
		$template->setVariable("TXT_PASS", $this->lng->txt("scored_pass"));
		$template->setVariable("VALUE_PASS", $pass);
		return $template->get();
	}

	/**
	* Creates a HTML representation for the results of a given question in a test
	*
	* @param integer $question_id The original id of the question
	* @param integer $test_id The test id
	* @return string HTML code of the question results
	* @access public
	*/
	function getQuestionResultForTestUsers($question_id, $test_id)
	{
		$foundusers = $this->object->getParticipantsForTestAndQuestion($test_id, $question_id);
		$output = "";
		foreach ($foundusers as $active_id => $passes)
		{
			$resultpass = $this->object->_getResultPass($active_id);
			for ($i = 0; $i < count($passes); $i++)
			{
				if (($resultpass != null) && ($resultpass == $passes[$i]["pass"]))
				{
					$question_gui =& $this->object->createQuestionGUI("", $passes[$i]["qid"]);
					$output .= $this->getResultsHeadUserAndPass($active_id, $resultpass+1);
					$output .= $question_gui->getSolutionOutput($active_id, $resultpass, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = FALSE, $show_feedback = FALSE);
					$output .= "<br /><br /><br />";
				}
			}
		}
		$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", TRUE, TRUE, "Modules/Test");
		$printbody->setVariable("TITLE", $this->lng->txt("tst_results"));
		$printbody->setVariable("ADM_CONTENT", $output);
		$printoutput = $printbody->get();
		$printoutput = preg_replace("/href=\".*?\"/", "", $printoutput);
		$fo = $this->object->processPrintoutput2FO($printoutput);
		$this->object->deliverPDFfromFO($fo, $question_gui->object->getTitle());
	}
}

// internal sort function to sort the result array
function sortResults($a, $b)
{
	$sort = ($_GET["sort"]) ? ($_GET["sort"]) : "nr";
	$sortorder = ($_GET["sortorder"]) ? ($_GET["sortorder"]) : "asc";
	if (strcmp($sortorder, "asc")) 
	{
		$smaller = 1;
		$greater = -1;
	} 
	else 
	{
		$smaller = -1;
		$greater = 1;
	}
	if ($a[$sort] == $b[$sort]) return 0;
	return ($a[$sort] < $b[$sort]) ? $smaller : $greater;
}

?>
