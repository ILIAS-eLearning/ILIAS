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
		$data =& $this->object->getCompleteEvaluationData(FALSE, $filterby, $filtertext);
		if (count($data->getParticipants()) == 0)
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
				array_push($evaluationrow, $userdata->getMark());
				if ($this->object->ects_output)
				{
					array_push($evaluationrow, $userdata->getECTSMark());
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
		if (count($data->getParticipants()) > 0)
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
		
		if (count($data->getParticipants()) > 0)
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
					$this->ctrl->setParameterByClass("ilTestOutputGUI", "statistics", "1");
					$this->ctrl->setParameterByClass("ilTestOutputGUI", "active_id", $active_id);
					$this->ctrl->setParameterByClass("ilTestOutputGUI", "pass", $pass);
					$this->tpl->setVariable("URL_TO_DETAILED_RESULTS", $this->ctrl->getLinkTargetByClass("ilTestOutputGUI", "outParticipantsPassDetails"));
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
		if (count($data->getParticipants())) 
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", count($data->getParticipants()));
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
			foreach ($data->getParticipants() as $userdata)
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
			foreach ($data->getParticipants() as $userdata)
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
				$this->exportToExcel($filtertext, $passedonly);
				break;
			case "csv":
				$this->exportToCSV($filtertext, $passedonly);
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
* Exports the evaluation data to the Microsoft Excel file format
*
* Exports the evaluation data to the Microsoft Excel file format
*
* @param string $filtertext Filter text for the user data
* @param boolean $passedonly TRUE if only passed user datasets should be exported, FALSE otherwise
* @access public
*/
	function exportToExcel($filtertext, $passedonly)
	{
		include_once "./classes/class.ilExcelWriterAdapter.php";
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$testname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->object->getTitle())) . ".xls";
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
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
		$worksheet =& $workbook->addWorksheet();
		$additionalFields = $this->object->getEvaluationAdditionalFields();
		$row = 0;
		$col = 0;
		include_once "./classes/class.ilExcelUtils.php";

		if ($this->object->getAnonymity())
		{
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("counter")), $format_title);
		}
		else
		{
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("name")), $format_title);
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("login")), $format_title);
		}
		if (count($additionalFields))
		{
			foreach ($additionalFields as $fieldname)
			{
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt($fieldname)), $format_title);
			}
		}
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultspoints")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("maximum_points")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultsmarks")), $format_title);
		if ($this->object->ects_output)
		{
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("ects_grade")), $format_title);
		}
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qworkedthrough")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qmax")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_pworkedthrough")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_timeofwork")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_atimeofwork")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_firstvisit")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_lastvisit")), $format_title);
		
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_mark_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_rank_participant")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_rank_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_total_participants")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("scored_pass")), $format_title);
		
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("pass")), $format_title);

		include_once "./classes/class.ilExcelUtils.php";
		$counter = 1;
		$data =& $this->object->getCompleteEvaluationData();
		foreach ($data->getParticipants() as $active_id => $userdata) 
		{
			$remove = FALSE;
			if (strlen($filtertext))
			{
				$username = $value["name"];
				if (!@preg_match("/$filtertext/i", $data->getParticipant($active_id)->getName()))
				{
					$remove = TRUE;
				}
			}
			if ($passedonly)
			{
				if ($data->getParticipant($active_id)->getPassed() == FALSE)
				{
					$remove = TRUE;
				}
			}
			if (!$remove)
			{
				$row++;
				if ($this->object->isRandomTest() || $this->object->getShuffleQuestions())
				{
					$row++;
				}
				$col = 0;
				if ($this->object->getAnonymity())
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($counter));
				}
				else
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getName()));
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getLogin()));
				}
				if (count($additionalFields))
				{
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "gender") == 0)
						{
							$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("gender_" . $userfields[$fieldname])));
						}
						else
						{
							$worksheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
						}
					}
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getReached()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getMaxpoints()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getMark()));
				if ($this->object->ects_output)
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getECTSMark()));
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getQuestionsWorkedThrough()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getNumberOfQuestions()));
				$worksheet->write($row, $col++, $data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent() / 100.0, $format_percent);
				$time = $data->getParticipant($active_id)->getTimeOfWork();
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$time = $data->getParticipant($active_id)->getQuestionsWorkedThrough() ? $data->getParticipant($active_id)->getTimeOfWork() / $data->getParticipant($active_id)->getQuestionsWorkedThrough() : 0;
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$fv = getdate($data->getParticipant($active_id)->getFirstVisit());
				$firstvisit = ilUtil::excelTime(
					$fv["year"],
					$fv["mon"],
					$fv["mday"],
					$fv["hours"],
					$fv["minutes"],
					$fv["seconds"]
				);
				$worksheet->write($row, $col++, $firstvisit, $format_datetime);
				$lv = getdate($data->getParticipant($active_id)->getLastVisit());
				$lastvisit = ilUtil::excelTime(
					$lv["year"],
					$lv["mon"],
					$lv["mday"],
					$lv["hours"],
					$lv["minutes"],
					$lv["seconds"]
				);
				$worksheet->write($row, $col++, $lastvisit, $format_datetime);

				$median = $data->getStatistics()->getStatistics()->median();
				$pct = $data->getParticipant($active_id)->getMaxpoints() ? $median / $data->getParticipant($active_id)->getMaxpoints() * 100.0 : 0;
				$mark = $this->object->mark_schema->getMatchingMark($pct);
				$mark_short_name = "";
				if (is_object($mark))
				{
					$mark_short_name = $mark->getShortName();
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($mark_short_name));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached())));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->rank_median()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->count()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($median));
				if ($this->object->getPassScoring() == SCORE_BEST_PASS)
				{
					$worksheet->write($row, $col++, $data->getParticipant($active_id)->getBestPass() + 1);
				}
				else
				{
					$worksheet->write($row, $col++, $data->getParticipant($active_id)->getLastPass() + 1);
				}
				$startcol = $col;
				for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++)
				{
					$col = $startcol;
					$finishdate = $this->object->getPassFinishDate($active_id, $pass);
					if ($finishdate > 0)
					{
						if ($pass > 0)
						{
							$row++;
							if ($this->object->isRandomTest() || $this->object->getShuffleQuestions())
							{
								$row++;
							}
						}
						$worksheet->write($row, $col++, ilExcelUtils::_convert_text($pass+1));
						if (is_object($data->getParticipant($active_id)) && is_array($data->getParticipant($active_id)->getQuestions($pass)))
						{
							foreach ($data->getParticipant($active_id)->getQuestions($pass) as $question)
							{
								$question_data = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($question_data["reached"]));
								if ($this->object->isRandomTest() || $this->object->getShuffleQuestions())
								{
									$worksheet->write($row-1, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
								}
								else
								{
									if ($pass == 0)
									{
										$worksheet->write(0, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
									}
								}
								$col++;
							}
						}
					}
				}
				$counter++;
			}
		}
		$workbook->close();
		ilUtil::deliverFile($excelfile, $testname, "application/vnd.ms-excel");
		exit;
	}
	
/**
* Exports the evaluation data to the CSV file format
*
* Exports the evaluation data to the CSV file format
*
* @param string $filtertext Filter text for the user data
* @param boolean $passedonly TRUE if only passed user datasets should be exported, FALSE otherwise
* @access public
*/
	function exportToCSV($filtertext, $passedonly)
	{
		$rows = array();
		$datarow = array();
		$col = 1;
		if ($this->object->getAnonymity())
		{
			array_push($datarow, $this->lng->txt("counter"));
			$col++;
		}
		else
		{
			array_push($datarow, $this->lng->txt("name"));
			$col++;
			array_push($datarow, $this->lng->txt("login"));
			$col++;
		}
		$additionalFields = $this->object->getEvaluationAdditionalFields();
		if (count($additionalFields))
		{
			foreach ($additionalFields as $fieldname)
			{
				array_push($datarow, $this->lng->txt($fieldname));
				$col++;
			}
		}
		array_push($datarow, $this->lng->txt("tst_stat_result_resultspoints"));
		$col++;
		array_push($datarow, $this->lng->txt("maximum_points"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_resultsmarks"));
		$col++;
		if ($this->object->ects_output)
		{
			array_push($datarow, $this->lng->txt("ects_grade"));
			$col++;
		}
		array_push($datarow, $this->lng->txt("tst_stat_result_qworkedthrough"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_qmax"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_pworkedthrough"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_timeofwork"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_atimeofwork"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_firstvisit"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_lastvisit"));
		$col++;
		
		array_push($datarow, $this->lng->txt("tst_stat_result_mark_median"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_rank_participant"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_rank_median"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_total_participants"));
		$col++;
		array_push($datarow, $this->lng->txt("tst_stat_result_median"));
		$col++;
		array_push($datarow, $this->lng->txt("scored_pass"));
		$col++;

		array_push($datarow, $this->lng->txt("pass"));
		$col++;

		$data =& $this->object->getCompleteEvaluationData();
		$headerrow = $datarow;
		$counter = 1;
		foreach ($data->getParticipants() as $active_id => $userdata) 
		{
			$datarow = $headerrow;
			$remove = FALSE;
			if (strlen($filtertext))
			{
				$username = $value["name"];
				if (!@preg_match("/$filtertext/i", $data->getParticipant($active_id)->getName()))
				{
					$remove = TRUE;
				}
			}
			if ($passedonly)
			{
				if ($data->getParticipant($active_id)->getPassed() == FALSE)
				{
					$remove = TRUE;
				}
			}
			if (!$remove)
			{
				$datarow2 = array();
				if ($this->object->getAnonymity())
				{
					array_push($datarow2, $counter);
				}
				else
				{
					array_push($datarow2, $data->getParticipant($active_id)->getName());
					array_push($datarow2, $data->getParticipant($active_id)->getLogin());
				}
				if (count($additionalFields))
				{
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "gender") == 0)
						{
							array_push($datarow2, $this->lng->txt("gender_" . $userfields[$fieldname]));
						}
						else
						{
							array_push($datarow2, $userfields[$fieldname]);
						}
					}
				}
				array_push($datarow2, $data->getParticipant($active_id)->getReached());
				array_push($datarow2, $data->getParticipant($active_id)->getMaxpoints());
				array_push($datarow2, $data->getParticipant($active_id)->getMark());
				if ($this->object->ects_output)
				{
					array_push($datarow2, $data->getParticipant($active_id)->getECTSMark());
				}
				array_push($datarow2, $data->getParticipant($active_id)->getQuestionsWorkedThrough());
				array_push($datarow2, $data->getParticipant($active_id)->getNumberOfQuestions());
				array_push($datarow2, $data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent() / 100.0);
				$time = $data->getParticipant($active_id)->getTimeOfWork();
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($datarow2, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
				$time = $data->getParticipant($active_id)->getQuestionsWorkedThrough() ? $data->getParticipant($active_id)->getTimeOfWork() / $data->getParticipant($active_id)->getQuestionsWorkedThrough() : 0;
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($datarow2, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
				$fv = getdate($data->getParticipant($active_id)->getFirstVisit());
				$firstvisit = ilUtil::excelTime(
					$fv["year"],
					$fv["mon"],
					$fv["mday"],
					$fv["hours"],
					$fv["minutes"],
					$fv["seconds"]
				);
				array_push($datarow2, $firstvisit);
				$lv = getdate($data->getParticipant($active_id)->getLastVisit());
				$lastvisit = ilUtil::excelTime(
					$lv["year"],
					$lv["mon"],
					$lv["mday"],
					$lv["hours"],
					$lv["minutes"],
					$lv["seconds"]
				);
				array_push($datarow2, $lastvisit);

				$median = $data->getStatistics()->getStatistics()->median();
				$pct = $data->getParticipant($active_id)->getMaxpoints() ? $median / $data->getParticipant($active_id)->getMaxpoints() * 100.0 : 0;
				$mark = $this->object->mark_schema->getMatchingMark($pct);
				$mark_short_name = "";
				if (is_object($mark))
				{
					$mark_short_name = $mark->getShortName();
				}
				array_push($datarow2, $mark_short_name);
				array_push($datarow2, $data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached()));
				array_push($datarow2, $data->getStatistics()->getStatistics()->rank_median());
				array_push($datarow2, $data->getStatistics()->getStatistics()->count());
				array_push($datarow2, $median);
				if ($this->object->getPassScoring() == SCORE_BEST_PASS)
				{
					array_push($datarow2, $data->getParticipant($active_id)->getBestPass() + 1);
				}
				else
				{
					array_push($datarow2, $data->getParticipant($active_id)->getLastPass() + 1);
				}
				for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++)
				{
					$finishdate = $this->object->getPassFinishDate($active_id, $pass);
					if ($finishdate > 0)
					{
						if ($pass > 0)
						{
							for ($i = 1; $i < $col-1; $i++) 
							{
								array_push($datarow2, "");
								array_push($datarow, "");
							}
							array_push($datarow, "");
						}
						array_push($datarow2, $pass+1);
						foreach ($data->getParticipant($active_id)->getQuestions($pass) as $question)
						{
							$question_data = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
							array_push($datarow2, $question_data["reached"]);
							array_push($datarow, preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"])));
						}
						if ($this->object->isRandomTest() || $this->object->getShuffleQuestions() || ($counter == 1 && $pass == 0))
						{
							array_push($rows, $datarow);
						}
						$datarow = array();
						array_push($rows, $datarow2);
						$datarow2 = array();
					}
				}
				$counter++;
			}
		}
		$csv = "";
		$separator = ";";
		foreach ($rows as $evalrow)
		{
			$csvrow =& $this->object->processCSVRow($evalrow, TRUE, $separator);
			$csv .= join($csvrow, $separator) . "\n";
		}
		ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->object->getTitle() . ".csv"));
		exit;
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
}
?>
