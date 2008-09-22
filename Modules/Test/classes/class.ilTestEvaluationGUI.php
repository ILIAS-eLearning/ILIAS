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
* Output class for assessment test evaluation
*
* The ilTestEvaluationGUI class creates the output for the ilObjTestGUI
* class when authors evaluate a test. This saves some heap space because 
* the ilObjTestGUI class will be much smaller then
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
* @extends ilTestServiceGUI
*/
class ilTestEvaluationGUI extends ilTestServiceGUI
{
/**
* ilTestEvaluationGUI constructor
*
* The constructor takes possible arguments an creates an instance of the 
* ilTestEvaluationGUI object.
*
* @param object $a_object Associated ilObjTest class
* @access public
*/
  function ilTestEvaluationGUI($a_object)
  {
		global $ilAccess;
		
		parent::ilTestServiceGUI($a_object);
		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), TRUE);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser;
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

	function &getHeaderNames()
	{
		$headernames = array();
		if ($this->object->getAnonymity())
		{
			array_push($headernames, $this->lng->txt("counter"));
		}
		else
		{
			array_push($headernames, $this->lng->txt("name"));
			array_push($headernames, $this->lng->txt("login"));
		}
		$additionalFields = $this->object->getEvaluationAdditionalFields();
		if (count($additionalFields))
		{
			foreach ($additionalFields as $fieldname)
			{
				array_push($headernames, $this->lng->txt($fieldname));
			}
		}
		array_push($headernames, $this->lng->txt("tst_reached_points"));
		array_push($headernames, $this->lng->txt("tst_mark"));
		if ($this->object->ects_output)
		{
			array_push($headernames, $this->lng->txt("ects_grade"));
		}
		array_push($headernames, $this->lng->txt("tst_answered_questions"));
		array_push($headernames, $this->lng->txt("working_time"));
		array_push($headernames, $this->lng->txt("detailed_evaluation"));
		return $headernames;
	}
	
	function &getHeaderVars()
	{
		$headervars = array();
		if ($this->object->getAnonymity())
		{
			array_push($headervars, "counter");
		}
		else
		{
			array_push($headervars, "name");
			array_push($headervars, "login");
		}
		array_push($headervars, "resultspoints");
		array_push($headervars, "resultsmarks");
		if ($this->object->ects_output)
		{
			array_push($headervars, "ects_grade");
		}
		array_push($headervars, "qworkedthrough");
		array_push($headervars, "timeofwork");
		array_push($headervars, "");
		return $headervars;
	}

	/**
	* Creates the evaluation output for the test
	*
	* Creates HTML output with the list of the results of all test participants.
	*
	* @access public
	*/
	function outEvaluation()
	{
		global $ilAccess;
		
		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_evaluation.html", "Modules/Test");

		$filter = 0;
		$filtertext = "";
		$filterby = "";
		$passedonly = FALSE;
		$setting = new ilSetting("assessment");
		// set filter was pressed
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("set_filter")) == 0)
		{
			$filter = 1;
			$filtertext = trim($_POST["userfilter"]);
			$filterby = $_POST["filterby"];
			if ($_POST["passedonly"] == 1)
			{
				$passedonly = TRUE;
			}
			if ((strlen($filtertext) == 0) && ($passedonly == FALSE)) $filter = 0;
			// save the filter for later usage
			$setting->set("tst_stat_filter_passed_" . $this->object->getTestId(), ($passedonly) ? 1 : 0);
			$setting->set("tst_stat_filter_text_" . $this->object->getTestId(), $filtertext);
			$setting->set("tst_stat_filter_by_" . $this->object->getTestId(), $filterby);
		}
		else
		{
			if (array_key_exists("g_userfilter", $_GET))
			{
				$filtertext = $_GET["g_userfilter"];
				$filterby = $_GET["g_filterby"];
			}
			else
			{
				// try to read the filter from the users preferences
				$pref = $setting->get("tst_stat_filter_text_" . $this->object->getTestId());
				if ($pref !== FALSE)
				{
					$filtertext = $pref;
				}
				$pref = $setting->get("tst_stat_filter_by_" . $this->object->getTestId());
				if ($pref !== FALSE)
				{
					$filterby = $pref;
				}
			}
			if (array_key_exists("g_passedonly", $_GET))
			{
				if ($_GET["g_passedonly"] == 1)
				{
					$passedonly = TRUE;
				}
			}
			else
			{
				// try to read the filter from the users preferences
				$pref = $setting->get("tst_stat_filter_passed_" . $this->object->getTestId());
				if ($pref !== FALSE)
				{
					$passedonly = ($pref) ? TRUE : FALSE;
				}
			}
		}
		// reset filter was pressed
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("reset_filter")) == 0)
		{
			$filter = 0;
			$filtertext = "";
			$filterby = "name";
			$passedonly = FALSE;
			$setting->delete("tst_stat_filter_passed_" . $this->object->getTestId());
			$setting->delete("tst_stat_filter_text_" . $this->object->getTestId());
			$setting->delete("tst_stat_filter_by_" . $this->object->getTestId());
		}
		if (strlen($filtertext))
		{
			$this->ctrl->setParameter($this, "g_userfilter", $filtertext);
			$this->ctrl->setParameter($this, "g_filterby", $filterby);
		}
		if ($passedonly)
		{
			$this->ctrl->setParameter($this, "g_passedonly", "1");
		}

		$offset = ($_GET["offset"]) ? $_GET["offset"] : 0;
		$orderdirection = ($_GET["sort_order"]) ? $_GET["sort_order"] : "asc";
		$defaultOrderColumn = "name";
		if ($this->object->getAnonymity()) $defaultOrderColumn = "counter";
		$ordercolumn = ($_GET["sort_by"]) ? $_GET["sort_by"] : $defaultOrderColumn;
		
		$maxentries = $ilUser->getPref("hits_per_page");
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
		
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$table = new ilTableGUI(0, FALSE);
		$table->setTitle($this->lng->txt("participants_evaluation"));
		$table->setHeaderNames($this->getHeaderNames());

		$table->enable("auto_sort");
		$table->enable("sort");
		$table->setLimit($maxentries);

		$header_params = $this->ctrl->getParameterArray($this, "outEvaluation");
		$header_vars = $this->getHeaderVars();
		$table->setHeaderVars($header_vars, $header_params);
		$table->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

		$table->setOffset($offset);
		$table->setMaxCount(count($total_users));
		$table->setOrderColumn($ordercolumn);
		$table->setOrderDirection($orderdirection);

		$evaluation_rows = array();
		$counter = 1;
		include_once "./Modules/Test/classes/class.ilTestEvaluationData.php";
		$data = new ilTestEvaluationData($this->object);
//		$data =& $this->object->getCompleteEvaluationData(FALSE, $filterby, $filtertext);
		$data->setFilter($filterby, $filtertext);
		$foundParticipants =& $data->getParticipants();
		if (count($foundParticipants) == 0)
		{
			$this->tpl->setVariable("EVALUATION_DATA", $this->lng->txt("tst_no_evaluation_data"));
		}
		$additionalFields = $this->object->getEvaluationAdditionalFields();

		foreach ($data->getParticipants() as $active_id => $userdata)
		{
			$remove = FALSE;
			if ($passedonly)
			{
				if ($userdata->getPassed() == FALSE)
				{
					$remove = TRUE;
				}
			}
			if (!$remove)
			{
				// build the evaluation row
				$evaluationrow = array();
				$fullname = "";
				if ($this->object->getAnonymity())
				{
					$fullname = $counter;
					array_push($evaluationrow, $fullname);
				}
				else
				{
					array_push($evaluationrow, $userdata->getName());
					if (strlen($userdata->getLogin()))
					{
						array_push($evaluationrow, "[" . $userdata->getLogin() . "]");
					}
					else
					{
						array_push($evaluationrow, "");
					}
				}
				if (count($additionalFields))
				{
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "gender") == 0)
						{
							array_push($evaluationrow, $this->lng->txt("gender_" . $userfields[$fieldname]));
						}
						else
						{
							array_push($evaluationrow, $userfields[$fieldname]);
						}
					}
				}
				array_push($evaluationrow, $userdata->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $userdata->getMaxpoints());
				$percentage = $userdata->getReachedPointsInPercent();
				$mark = $this->object->getMarkSchema()->getMatchingMark($percentage);
				if (is_object($mark))
				{
					array_push($evaluationrow, $mark->getShortName());
				}
				if ($this->object->ects_output)
				{
					$ects_mark = $this->object->getECTSGrade($userdata->getReached(), $userdata->getMaxPoints());
					array_push($evaluationrow, $ects_mark);
				}
				array_push($evaluationrow, $userdata->getQuestionsWorkedThrough() . " " . strtolower($this->lng->txt("of")) . " " . $userdata->getNumberOfQuestions() . " (" . sprintf("%2.2f", $userdata->getQuestionsWorkedThroughInPercent()) . " %" . ")");

				$time_seconds = $userdata->getTimeOfWork();
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evaluationrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
				$this->ctrl->setParameter($this, "active_id", $active_id);
				$href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
				$detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
				array_push($evaluationrow, "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>");
				array_push($evaluation_rows, $evaluationrow);
				$counter++;
			}
		}
		if (count($foundParticipants) > 0)
		{
			$table->setData($evaluation_rows);
			$tableoutput = $table->render();
			$this->tpl->setVariable("EVALUATION_DATA", $tableoutput);
		}

		$template = new ilTemplate("tpl.il_as_tst_evaluation_filter.html", TRUE, TRUE, "Modules/Test");
		$filters = array("name" => $this->lng->txt("name"), "group" => $this->lng->txt("grp"), "course" => $this->lng->txt("crs"));
		foreach ($filters as $value => $name)
		{
			$template->setCurrentBlock("filterby");
			$template->setVariable("FILTER_BY_NAME", $name);
			$template->setVariable("FILTER_BY_VALUE", $value);
			if (strcmp($filterby, $value) == 0)
			{
				$template->setVariable("FILTER_BY_SELECTED", " selected=\"selected\"");
			}
			$template->parseCurrentBlock();
		}
		$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "outEvaluation"));
		$template->setVariable("TEXT_FILTER_USERS", $this->lng->txt("filter"));
		$template->setVariable("TEXT_FILTER", $this->lng->txt("set_filter"));
		$template->setVariable("TEXT_BY", $this->lng->txt("by"));
		$template->setVariable("TEXT_RESET_FILTER", $this->lng->txt("reset_filter"));
		$template->setVariable("TEXT_PASSEDONLY", $this->lng->txt("passed_only"));
		if ($passedonly)
		{
			$template->setVariable("CHECKED_PASSEDONLY", " checked=\"checked\"");
		}
		if (strlen($filtertext) > 0)
		{
			$template->setVariable("VALUE_FILTER_USERS", " value=\"" . $filtertext . "\"");
		}
		$filteroutput = $template->get();
		
		if (count($foundParticipants) > 0)
		{
			$template = new ilTemplate("tpl.il_as_tst_evaluation_export.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
			if (!$this->object->getAnonymity())
			{
				include_once "./Modules/Test/classes/class.ilTestCertificate.php";
				if (ilTestCertificate::_isComplete($this->object->getId()))
				{
					$template->setVariable("TEXT_CERTIFICATE", $this->lng->txt("exp_type_certificate"));
				}
			}
			$template->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
			$template->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
			$template->setVariable("BTN_EXPORT", $this->lng->txt("export"));
			$template->setVariable("BTN_PRINT", $this->lng->txt("print"));
			$template->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
			$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "exportEvaluation"));
			$exportoutput = $template->get();
			$this->tpl->setVariable("EVALUATION_EXPORT", $exportoutput);
		}

		$this->tpl->setVariable("EVALUATION_FILTER", $filteroutput);
		
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
	}
	
	/**
	* Creates the detailed evaluation output for a selected participant
	*
	* Creates the detailed evaluation output for a selected participant
	*
	* @access public
	*/
	function detailedEvaluation()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_evaluation_details.html", "Modules/Test");

		$active_id = $_GET["active_id"];
		if (strlen($active_id) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("detailed_evaluation_missing_active_id"), TRUE);
			$this->ctrl->redirect($this, "outEvaluation");
		}
		
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

		$data =& $this->object->getCompleteEvaluationData();
		$this->tpl->setVariable("TEXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTarget($this, "outEvaluation"));
		$this->tpl->setVariable("HEADING_DETAILED_EVALUATION", sprintf($this->lng->txt("detailed_evaluation_for"), 
			$data->getParticipant($active_id)->getName())
		);
		$this->tpl->setVariable("STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("VALUE_RESULTSPOINTS", $data->getParticipant($active_id)->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getMaxpoints() . " (" . sprintf("%2.2f", $data->getParticipant($active_id)->getReachedPointsInPercent()) . " %" . ")");
		if (strlen($data->getParticipant($active_id)->getMark()))
		{
			$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
			$this->tpl->setVariable("VALUE_RESULTSMARKS", $data->getParticipant($active_id)->getMark());
			if (strlen($data->getParticipant($active_id)->getECTSMark()))
			{
				$this->tpl->setVariable("TXT_ECTS", $this->lng->txt("ects_grade"));
				$this->tpl->setVariable("VALUE_ECTS", $data->getParticipant($active_id)->getECTSMark());
			}
		}
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("VALUE_QWORKEDTHROUGH", $data->getParticipant($active_id)->getQuestionsWorkedThrough() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getNumberOfQuestions() . " (" . sprintf("%2.2f", $data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent()) . " %" . ")");

		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$time_seconds = $data->getParticipant($active_id)->getTimeOfWork();
		$atime_seconds = $data->getParticipant($active_id)->getNumberOfQuestions() ? $time_seconds / $data->getParticipant($active_id)->getNumberOfQuestions() : 0;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("VALUE_TIMEOFWORK", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$time_hours    = floor($atime_seconds/3600);
		$atime_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($atime_seconds/60);
		$atime_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("VALUE_ATIMEOFWORK", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $atime_seconds));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		#$this->tpl->setVariable("VALUE_FIRSTVISIT", 
		#	date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $data->getParticipant($active_id)->getFirstVisit())
		#);
		$this->tpl->setVariable('VAL_FIRST_VISIT',ilDatePresentation::formatDate(
			new ilDateTime($data->getParticipant($active_id)->getFirstVisit(),IL_CAL_UNIX)));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		#$this->tpl->setVariable("VALUE_LASTVISIT",
		#	date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $data->getParticipant($active_id)->getLastVisit())
		#);
		$this->tpl->setVariable('VAL_FIRST_VISIT',ilDatePresentation::formatDate(
			new ilDateTime($data->getParticipant($active_id)->getLastVisit(),IL_CAL_UNIX)));
		
		$this->tpl->setVariable("TXT_NROFPASSES", $this->lng->txt("tst_nr_of_passes"));
		$this->tpl->setVariable("VALUE_NROFPASSES", $data->getParticipant($active_id)->getLastPass() + 1);
		$this->tpl->setVariable("TXT_SCOREDPASS", $this->lng->txt("scored_pass"));
		if ($this->object->getPassScoring() == SCORE_BEST_PASS)
		{
			$this->tpl->setVariable("VALUE_SCOREDPASS", $data->getParticipant($active_id)->getBestPass() + 1);
		}
		else
		{
			$this->tpl->setVariable("VALUE_SCOREDPASS", $data->getParticipant($active_id)->getLastPass() + 1);
		}
		
		$median = $data->getStatistics()->getStatistics()->median();
		$pct = $data->getParticipant($active_id)->getMaxpoints() ? ($median / $data->getParticipant($active_id)->getMaxpoints()) * 100.0 : 0;
		$mark = $this->object->mark_schema->getMatchingMark($pct);
		if (is_object($mark))
		{
			$this->tpl->setVariable("TXT_MARK_MEDIAN", $this->lng->txt("tst_stat_result_mark_median"));
			$this->tpl->setVariable("VALUE_MARK_MEDIAN", $mark->getShortName());
		}

		$this->tpl->setVariable("TXT_RANK_PARTICIPANT", $this->lng->txt("tst_stat_result_rank_participant"));
		$this->tpl->setVariable("VALUE_RANK_PARTICIPANT", $data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached()));
		$this->tpl->setVariable("TXT_RANK_MEDIAN", $this->lng->txt("tst_stat_result_rank_median"));
		$this->tpl->setVariable("VALUE_RANK_MEDIAN", $data->getStatistics()->getStatistics()->rank_median());
		$this->tpl->setVariable("TXT_TOTAL_PARTICIPANTS", $this->lng->txt("tst_stat_result_total_participants"));
		$this->tpl->setVariable("VALUE_TOTAL_PARTICIPANTS", $data->getStatistics()->getStatistics()->count());
		$this->tpl->setVariable("TXT_RESULT_MEDIAN", $this->lng->txt("tst_stat_result_median"));
		$this->tpl->setVariable("VALUE_RESULT_MEDIAN", $median);

		for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($active_id, $pass);
			if ($finishdate > 0)
			{
				$this->tpl->setCurrentBlock("question_header");
				$this->tpl->setVariable("TXT_QUESTION_DATA", sprintf($this->lng->txt("tst_eval_question_points"), $pass+1));
				$this->tpl->parseCurrentBlock();
				global $ilAccess;
				if (($ilAccess->checkAccess("write", "", $_GET["ref_id"])))
				{
					$this->tpl->setCurrentBlock("question_footer");
					$this->tpl->setVariable("TEXT_TO_DETAILED_RESULTS", $this->lng->txt("tst_show_answer_sheet"));
					$this->ctrl->setParameter($this, "statistics", "1");
					$this->ctrl->setParameter($this, "active_id", $active_id);
					$this->ctrl->setParameter($this, "pass", $pass);
					$this->tpl->setVariable("URL_TO_DETAILED_RESULTS", $this->ctrl->getLinkTarget($this, "outParticipantsPassDetails"));
					$this->tpl->parseCurrentBlock();
				}
				$questions = $data->getParticipant($active_id)->getQuestions($pass);
				if (!is_array($questions))
				{
					$questions = $data->getParticipant($active_id)->getQuestions(0);
				}
				$counter = 1;
				foreach ($questions as $question)
				{
					$this->tpl->setCurrentBlock("question_row");
					$this->tpl->setVariable("QUESTION_COUNTER", $counter);
					$this->tpl->setVariable("QUESTION_TITLE", $data->getQuestionTitle($question["id"]));
					$answeredquestion = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
					if (is_array($answeredquestion))
					{
						$percent = $answeredquestion["points"] ? $answeredquestion["reached"] / $answeredquestion["points"] * 100.0 : 0;
						$this->tpl->setVariable("QUESTION_POINTS", $answeredquestion["reached"] . " " . strtolower($this->lng->txt("of")) . " " . $answeredquestion["points"] . " (" . sprintf("%.2f", $percent) . " %)");
					}
					else
					{
						$this->tpl->setVariable("QUESTION_POINTS",  "0 " . strtolower($this->lng->txt("of")) . " " . $question["points"] . " (" . sprintf("%.2f", 0) . " %) - " . $this->lng->txt("question_not_answered"));
					}
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->touchBlock("question_stats");
			}
		}
	}
	
	/**
	* Creates a PDF representation of the answers for a given question in a test
	*
	* @access public
	*/
	function exportQuestionForAllParticipants()
	{
		$this->getQuestionResultForTestUsers($_GET["qid"], $this->object->getTestId());
	}
	
/**
* Output of anonymous aggregated results for the test
*
* Output of anonymous aggregated results for the test
*
* @access public
*/
	function eval_a()
	{
		global $ilAccess;

		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		$data =& $this->object->getCompleteEvaluationData();
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", "Modules/Test");
		$foundParticipants =& $data->getParticipants();
		if (count($foundParticipants)) 
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", count($foundParticipants));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished"));
			$total_finished = $this->object->evalTotalFinished();
			$this->tpl->setVariable("TXT_VALUE", $total_finished);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$average_time = $this->object->evalTotalStartedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished_average_time"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$total_passed = 0;
			$total_passed_reached = 0;
			$total_passed_max = 0;
			$total_passed_time = 0;
			foreach ($foundParticipants as $userdata)
			{
				if ($userdata->getPassed()) 
				{
					$total_passed++;
					$total_passed_reached += $userdata->getReached();
					$total_passed_max += $userdata->getMaxpoints();
					$total_passed_time += $userdata->getTimeOfWork();
				}
			}
			$average_passed_reached = $total_passed ? $total_passed_reached / $total_passed : 0;
			$average_passed_max = $total_passed ? $total_passed_max / $total_passed : 0;
			$average_passed_time = $total_passed ? $total_passed_time / $total_passed : 0;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed"));
			$this->tpl->setVariable("TXT_VALUE", $total_passed);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_points"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $average_passed_reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $average_passed_max));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_time"));
			$average_time = $average_passed_time;
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		} 
		else 
		{
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ANONYMOUS_AGGREGATION", $this->lng->txt("tst_eval_no_anonymous_aggregation"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		
		global $ilUser;
		$maxentries = $ilUser->getPref("hits_per_page");
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}

		$offset = ($_GET["offset"]) ? $_GET["offset"] : 0;
		$orderdirection = ($_GET["sort_order"]) ? $_GET["sort_order"] : "asc";
		$defaultOrderColumn = "name";
		if ($this->object->getAnonymity()) $defaultOrderColumn = "counter";
		$ordercolumn = ($_GET["sort_by"]) ? $_GET["sort_by"] : $defaultOrderColumn;

		$counter = 0;
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$table = new ilTableGUI(0, FALSE);
		$table->setTitle($this->lng->txt("average_reached_points"));
		$table->setHeaderNames(array($this->lng->txt("question_title"), $this->lng->txt("points"), $this->lng->txt("percentage"), $this->lng->txt("number_of_answers")));

		$table->enable("auto_sort");
		$table->enable("sort");
		$table->setLimit($maxentries);

		$header_params = $this->ctrl->getParameterArray($this, "eval_a");
		$header_vars = array("question_title", "points", "percentage", "number_of_answers");
		$table->setHeaderVars($header_vars, $header_params);
		$table->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

		$table->setOffset($offset);
		$table->setMaxCount(count($data->getQuestionTitles()));
		$table->setOrderColumn($ordercolumn);
		$table->setOrderDirection($orderdirection);
		$rows = array();
		foreach ($data->getQuestionTitles() as $question_id => $question_title)
		{
			$answered = 0;
			$reached = 0;
			$max = 0;
			foreach ($foundParticipants as $userdata)
			{
				for ($i = 0; $i <= $userdata->getLastPass(); $i++)
				{
					if (is_object($userdata->getPass($i)))
					{
						$question =& $userdata->getPass($i)->getAnsweredQuestionByQuestionId($question_id);
						if (is_array($question))
						{
							$answered++;
							$reached += $question["reached"];
							$max += $question["points"];
						}
					}
				}
			}
			$percent = $max ? $reached/$max * 100.0 : 0;
			$counter++;
			$this->ctrl->setParameter($this, "qid", $question_id);
			array_push($rows, 
				array(
						$question_title, 
						sprintf("%.2f", $answered ? $reached / $answered : 0) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $answered ? $max / $answered : 0),
						sprintf("%.2f", $percent) . "%",
						$answered
				)
			);
		}
		$table->setData($rows);
		$tableoutput = $table->render();
		$this->tpl->setVariable("TBL_AVG_REACHED", $tableoutput);

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->setVariable("TXT_AVG_REACHED", $this->lng->txt("average_reached_points"));
		$this->tpl->setVariable("TXT_QUESTIONTITLE", $this->lng->txt("question_title"));
		$this->tpl->setVariable("TXT_POINTS", $this->lng->txt("points"));
		$this->tpl->setVariable("TXT_ANSWERS", $this->lng->txt("number_of_answers"));
		$this->tpl->setVariable("TXT_PERCENT", $this->lng->txt("percentage"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Exports the evaluation data to a selected file format
*
* Exports the evaluation data to a selected file format
*
* @access public
*/
	function exportEvaluation()
	{
		$filterby = "";
		if (array_key_exists("g_filterby", $_GET))
		{
			$filterby = $_GET["g_filterby"];
		}
		$filtertext = "";
		if (array_key_exists("g_userfilter", $_GET))
		{
			$filtertext = $_GET["g_userfilter"];
		}
		$passedonly = FALSE;
		if (array_key_exists("g_passedonly", $_GET))
		{
			if ($_GET["g_passedonly"] == 1)
			{
				$passedonly = TRUE;
			}
		}
		switch ($_POST["export_type"])
		{
			case "excel":
				include_once "./Modules/Test/classes/class.ilTestExport.php";
				$exportObj = new ilTestExport($this->object, "results");
				$exportObj->exportToExcel($deliver = TRUE, $filterby, $filtertext, $passedonly);
				break;
			case "csv":
				include_once "./Modules/Test/classes/class.ilTestExport.php";
				$exportObj = new ilTestExport($this->object, "results");
				$exportObj->exportToCSV($deliver = TRUE, $filterby, $filtertext, $passedonly);
				break;
			case "certificate":
				if ($passedonly)
				{
					$this->ctrl->setParameterByClass("iltestcertificategui", "g_passedonly", "1");
				}
				if (strlen($filtertext))
				{
					$this->ctrl->setParameterByClass("iltestcertificategui", "g_userfilter", $filtertext);
				}
				$this->ctrl->redirectByClass("iltestcertificategui", "exportCertificate");
				break;
		}
	}
	
	/**
	 * Returns the ID of a question for evaluation purposes. If a question id and the id of the
	 * original question are given, this function returns the original id, otherwise the  question id
	 *
	 * @return int question or original id
	 **/
	function getEvaluationQuestionId($question_id, $original_id = "")
	{
		if ($original_id > 0)
		{
			return $original_id;
		}
		else
		{
			return $question_id;
		}
	}
	
	function saveEvalSettings()
	{
		$results = $_POST;
		$additionalFields = array();
		foreach ($results as $key => $value)
		{
			if (preg_match("/cb_(\w+)/", $key, $matches) && ($value == 1))
			{
				array_push($additionalFields, $matches[1]);
			}
		}
		$this->object->setEvaluationAdditionalFields($additionalFields);
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), TRUE);
		$this->ctrl->redirect($this, "evalSettings");
	}
	
	function evalSettings()
	{
		global $ilAccess;

		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveEvalSettings"));
		$form->setTitle($this->lng->txt("assessment_eval_settings"));
		
		// Additional User fields
		$fields = array("gender", "email", "institution", "street", "city", "zipcode", "country", "department", "matriculation");
		$additionalFields = $this->object->getEvaluationAdditionalFields();
		
		foreach ($fields as $dbfield)
		{
			$checkbox = new ilCheckboxInputGUI($this->lng->txt($dbfield), "cb_" . $dbfield);
//			$checkbox->setInfo($lng->txt("assessment_use_javascript_desc"));
			if ($this->object->getAnonymity()) 
			{
				$checkbox->setDisabled(TRUE);
			}
			else
			{
				if (in_array($dbfield, $additionalFields)) $checkbox->setChecked(TRUE);
			}
			$form->addItem($checkbox);
		}
		$form->addCommandButton("saveEvalSettings", $this->lng->txt("save"));
		
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
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

		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestevaluationgui", "outParticipantsPassDetails");
		$user_data = $this->getResultsUserdata($active_id, FALSE);

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$template = new ilTemplate("tpl.il_as_tst_pass_details_overview_participants.html", TRUE, TRUE, "Modules/Test");

		if (array_key_exists("statistics", $_GET) && ($_GET["statistics"] == 1))
		{
			$template->setVariable("BACK_TEXT", $this->lng->txt("back"));
			$this->ctrl->setParameterByClass("ilTestEvaluationGUI", "active_id", $active_id);
			$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilTestEvaluationGUI", "detailedEvaluation"));
		}
		else
		{
			if ($this->object->getNrOfTries() == 1)
			{
				$template->setVariable("BACK_TEXT", $this->lng->txt("back"));
				$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
			}
			else
			{
				$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass(get_class($this), "outParticipantsResultsOverview"));
				$template->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_overview"));
			}
		}
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		if ($this->object->getNrOfTries() == 1)
		{
			$statement = $this->getFinalStatement($result_array["test"]);
			$template->setVariable("USER_MARK", $statement["mark"]);
			if (strlen($statement["markects"]))
			{
				$template->setVariable("USER_MARK_ECTS", $statement["markects"]);
			}
		}

		$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, TRUE);

		$template->setVariable("LIST_OF_ANSWERS", $list_of_answers);
		$template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("PASS_DETAILS", $overview);
		$template->setVariable("USER_DETAILS", $user_data);
		$uname = $this->object->userLookupFullName($user_id);
		$template->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));

		$this->tpl->setVariable("ADM_CONTENT", $template->get());
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
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
		$template = new ilTemplate("tpl.il_as_tst_pass_overview_participants.html", TRUE, TRUE, "Modules/Test");

		$active_id = $_GET["active_id"];
		if ($this->object->getNrOfTries() == 1)
		{
			$this->ctrl->setParameter($this, "active_id", $active_id);
			$this->ctrl->setParameter($this, "pass", ilObjTest::_getResultPass($active_id));
			$this->ctrl->redirect($this, "outParticipantsPassDetails");
		}

		$overview = $this->getPassOverview($active_id, "iltestevaluationgui", "outParticipantsPassDetails");
		$template->setVariable("PASS_OVERVIEW", $overview);
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BACK_TEXT", $this->lng->txt("back"));
		$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		$result_pass = $this->object->_getResultPass($active_id);
		$result_array =& $this->object->getTestResult($active_id, $result_pass);
		$statement = $this->getFinalStatement($result_array["test"]);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		$user_data = $this->getResultsUserdata($active_id);
		$template->setVariable("USER_DATA", $user_data);
		$template->setVariable("TEXT_OVERVIEW", $this->lng->txt("tst_results_overview"));
		$template->setVariable("USER_MARK", $statement["mark"]);
		if (strlen($statement["markects"]))
		{
			$template->setVariable("USER_MARK_ECTS", $statement["markects"]);
		}
		$template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$template->parseCurrentBlock();

		$this->tpl->setVariable("ADM_CONTENT", $template->get());
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
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
		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestevaluationgui", "outUserPassDetails", $command_solution_details);

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
				$this->tpl->setVariable("USER_MARK_ECTS", $statement["markects"]);
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

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
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
		$active_id = $this->object->getTestSession()->getActiveId();
		$hide_details = !$this->object->getShowPassDetails();
		if ($hide_details)
		{
			$executable = $this->object->isExecutable($ilUser->getId());
			if (!$executable["executable"]) $hide_details = FALSE;
		}
		if (($this->object->getNrOfTries() == 1) && (!$hide_details))
		{
			$pass = 0;
		}
		else
		{
			$template->setCurrentBlock("pass_overview");
			$overview = $this->getPassOverview($active_id, "iltestevaluationgui", "outUserResultsOverview", FALSE, $hide_details);
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
			if ($this->object->canShowCertificate($user_id, $active_id))
			{
				$templatehead->setVariable("CERTIFICATE_URL", $this->ctrl->getLinkTargetByClass("iltestcertificategui", "outCertificate"));
				$templatehead->setVariable("CERTIFICATE_TEXT", $this->lng->txt("certificate"));
			}
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
			$detailsoverview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestevaluationgui", "outUserResultsOverview", $command_solution_details);

			$user_id = $this->object->_getUserIdFromActiveId($active_id);

			if (!$hide_details && $this->object->canShowSolutionPrintview())
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
			$template->setVariable("USER_MARK_ECTS", $statement["markects"]);
		}
		$template->parseCurrentBlock();

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
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
		$active_id = $this->object->getTestSession()->getActiveId();
		$overview = "";
		if ($this->object->getNrOfTries() == 1)
		{
			$pass = 0;
		}
		else
		{
			$overview = $this->getPassOverview($active_id, "iltestevaluationgui", "outUserListOfAnswerPasses", TRUE);
			$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_passes"));
			$this->tpl->setVariable("PASS_OVERVIEW", $overview);
		}

		$signature = "";
		if (strlen($pass))
		{
			$signature = $this->getResultsSignature();
			$result_array =& $this->object->getTestResult($active_id, $pass);
			$user_id =& $this->object->_getUserIdFromActiveId($active_id);
			$showAllAnswers = TRUE;
			if ($this->object->isExecutable($user_id))
			{
				$showAllAnswers = FALSE;
			}
			$answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, FALSE, $showAllAnswers);
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

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
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
	* Creates an output of the solution of an answer compared to the correct solution
	*
	* @access public
	*/
	function outCorrectSolution()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_correct_solution.html", "Modules/Test");

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}

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
	* Creates user results for single questions
	*
	* @access public
	*/
	function singleResults()
	{
		global $ilAccess;

		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		$data =& $this->object->getCompleteEvaluationData();
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_single_answers.html", "Modules/Test");
		$foundParticipants =& $data->getParticipants();
		if (count($foundParticipants) == 0)
		{
			$this->tpl->setCurrentBlock("no_participants");
			$this->tpl->setVariable("NO_PARTICIPANTS", $this->lng->txt("tst_no_evaluation_data"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TXT_SINGLE_ANSWERS", $this->lng->txt("tst_answered_questions"));
			return;
		}
		$this->tpl->setVariable("TXT_SINGLE_ANSWERS", $this->lng->txt("tst_answered_questions"));

		global $ilUser;
		$maxentries = $ilUser->getPref("hits_per_page");
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}

		$offset = ($_GET["offset"]) ? $_GET["offset"] : 0;
		$orderdirection = ($_GET["sort_order"]) ? $_GET["sort_order"] : "asc";
		$defaultOrderColumn = "name";
		if ($this->object->getAnonymity()) $defaultOrderColumn = "counter";
		$ordercolumn = ($_GET["sort_by"]) ? $_GET["sort_by"] : $defaultOrderColumn;

		$counter = 0;
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$table = new ilTableGUI(0, FALSE);
		$table->setTitle($this->lng->txt("tst_answered_questions_test"));
		$table->setHeaderNames(array($this->lng->txt("question_title"), $this->lng->txt("number_of_answers"), $this->lng->txt("output")));

		$table->enable("auto_sort");
		$table->enable("sort");
		$table->setLimit($maxentries);

		$header_params = $this->ctrl->getParameterArray($this, "eval_a");
		$header_vars = array("question_title", "number_of_answers", "export");
		$table->setHeaderVars($header_vars, $header_params);
		$table->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

		$table->setOffset($offset);
		$table->setMaxCount(count($data->getQuestionTitles()));
		$table->setOrderColumn($ordercolumn);
		$table->setOrderDirection($orderdirection);
		$rows = array();
		foreach ($data->getQuestionTitles() as $question_id => $question_title)
		{
			$answered = 0;
			$reached = 0;
			$max = 0;
			foreach ($foundParticipants as $userdata)
			{
				$pass = $userdata->getScoredPass();
				if (is_object($userdata->getPass($pass)))
				{
					$question =& $userdata->getPass($pass)->getAnsweredQuestionByQuestionId($question_id);
					if (is_array($question))
					{
						$answered++;
					}
				}
			}
			$counter++;
			$this->ctrl->setParameter($this, "qid", $question_id);
			array_push($rows, 
				array(
						$question_title, 
						$answered,
						"<a href=\"" . $this->ctrl->getLinkTarget($this, "exportQuestionForAllParticipants"). "\">" . $this->lng->txt("pdf_export") . "</a>"
				)
			);
		}
		$table->setData($rows);
		$tableoutput = $table->render();
		$this->tpl->setVariable("TBL_SINGLE_ANSWERS", $tableoutput);
	}
}
?>
