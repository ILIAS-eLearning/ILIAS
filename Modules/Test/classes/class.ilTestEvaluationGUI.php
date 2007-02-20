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
		if (!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id))
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

	function &getEvaluationRowNew($evaldata)
	{
		$evaluationrow = array();
		if ($this->object->getAnonymity())
		{
			array_push($evaluationrow, $counter);
		}
		else
		{
			array_push($evaluationrow, $user_data["name"]);
			array_push($evaluationrow, "[" . $user_data["login"] . "]");
		}
		array_push($evaluationrow, $eval_data["resultspoints"] . " " . strtolower($this->lng->txt("of")) . " " . $eval_data["maxpoints"]);
		array_push($evaluationrow, $eval_data["resultsmarks"]);
		if ($this->object->ects_output)
		{
			$mark_ects = $this->object->getECTSGrade($eval_data["resultspoints"],$eval_data["maxpoints"]);
			array_push($evaluationrow, $mark_ects);
		}
		array_push($evaluationrow, $eval_data["qworkedthrough"] . " " . strtolower($this->lng->txt("of")) . " " . $eval_data["qmax"] . " (" . sprintf("%2.2f", $eval_data["pworkedthrough"] * 100.0) . " %" . ")");
		$time = $eval_data["timeofwork"];
		$time_seconds = $time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		array_push($evaluationrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->ctrl->setParameter($this, "active_id", $active_id);
		$href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
		$detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
		array_push($evaluationrow, "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>");
		return $evaluationrow;
	}
	
	function &getEvaluationRow($active_id, &$eval_data, $user_data, $counter, $total_participants)
	{
		$evaluationrow = array();
		if ($this->object->getAnonymity())
		{
			array_push($evaluationrow, $counter);
		}
		else
		{
			array_push($evaluationrow, $user_data["name"]);
			array_push($evaluationrow, "[" . $user_data["login"] . "]");
		}
		array_push($evaluationrow, $eval_data["resultspoints"] . " " . strtolower($this->lng->txt("of")) . " " . $eval_data["maxpoints"]);
		array_push($evaluationrow, $eval_data["resultsmarks"]);
		if ($this->object->ects_output)
		{
			$mark_ects = $this->object->getECTSGrade($eval_data["resultspoints"],$eval_data["maxpoints"]);
			array_push($evaluationrow, $mark_ects);
		}
		array_push($evaluationrow, $eval_data["qworkedthrough"] . " " . strtolower($this->lng->txt("of")) . " " . $eval_data["qmax"] . " (" . sprintf("%2.2f", $eval_data["pworkedthrough"] * 100.0) . " %" . ")");
		$time = $eval_data["timeofwork"];
		$time_seconds = $time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		array_push($evaluationrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->ctrl->setParameter($this, "active_id", $active_id);
		$href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
		$detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
		array_push($evaluationrow, "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>");
		return $evaluationrow;
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
		
		if (!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) 
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_evaluation.html", "Modules/Test");

		$total_users =& $this->object->getParticipants();
		if (count($total_users) == 0)
		{
			$this->tpl->setVariable("EVALUATION_DATA", $this->lng->txt("tst_no_evaluation_data"));
			return;
		}
		$filter = 0;
		$filtertext = "";
		$passedonly = FALSE;
		// set filter was pressed
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("set_filter")) == 0)
		{
			$filter = 1;
			$filtertext = trim($_POST["userfilter"]);
			if ($_POST["passedonly"] == 1)
			{
				$passedonly = TRUE;
			}
			if ((strlen($filtertext) == 0) && ($passedonly == FALSE)) $filter = 0;
			// save the filter for later usage
			$ilUser->writePref("tst_stat_filter_passed_" . $this->object->getTestId(), ($passedonly) ? 1 : 0);
			$ilUser->writePref("tst_stat_filter_text_" . $this->object->getTestId(), $filtertext);
		}
		else
		{
			if (array_key_exists("g_userfilter", $_GET))
			{
				$filtertext = $_GET["g_userfilter"];
			}
			else
			{
				// try to read the filter from the users preferences
				$pref = $ilUser->getPref("tst_stat_filter_text_" . $this->object->getTestId());
				if ($pref !== FALSE)
				{
					$filtertext = $pref;
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
				$pref = $ilUser->getPref("tst_stat_filter_passed_" . $this->object->getTestId());
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
			$passedonly = FALSE;
			$ilUser->deletePref("tst_stat_filter_passed_" . $this->object->getTestId());
			$ilUser->deletePref("tst_stat_filter_text_" . $this->object->getTestId());
		}
		if (strlen($filtertext))
		{
			$this->ctrl->setParameter($this, "g_userfilter", $filtertext);
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
		$overview =& $this->object->evalResultsOverview();
		$times =& $this->object->getCompleteWorkingTimeOfParticipants();
		foreach ($overview as $active_id => $pass)
		{
			$bestpass = 0;
			$bestpasspoints = 0;
			$lastpass = 0;
			foreach ($pass as $passnr => $userresults)
			{
				if (is_numeric($passnr))
				{
					$reached = $pass[$passnr]["reached"];
					if ($reached > $bestpasspoints)
					{
						$bestpasspoints = $reached;
						$bestpass = $passnr;
					}
					if ($passnr > $lastpass) $lastpass = $passnr;
				}
			}
			$statpass = 0;
			if ($this->object->getPassScoring() == SCORE_BEST_PASS)
			{
				$statpass = $bestpass;
			}
			else
			{
				$statpass = $lastpass;
			}
			// build the evaluation row
			$qpass =& $this->object->getQuestionsOfPass($active_id, $statpass);
			$evaluationrow = array();
			$fullname = "";
			if ($this->object->getAnonymity())
			{
				$fullname = $counter;
				array_push($evaluationrow, $fullname);
			}
			else
			{
				$fullname = $this->object->buildName($pass["usr_id"], $pass["firstname"], $pass["lastname"], $pass["title"]);
				array_push($evaluationrow, $fullname);
				if (strlen($pass["login"]))
				{
					array_push($evaluationrow, "[" . $pass["login"] . "]");
				}
				else
				{
					array_push($evaluationrow, "");
				}
			}
			$maxpoints = 0;
			foreach ($qpass as $row)
			{
				$maxpoints += $row["points"];
			}
			array_push($evaluationrow, $pass[$statpass]["reached"] . " " . strtolower($this->lng->txt("of")) . " " . $maxpoints);
			$pct = ($maxpoints ? ($pass[$statpass]["reached"] / $maxpoints)*100.0 : 0);
			$mark = $this->object->mark_schema->getMatchingMark($pct);
			if (is_object($mark))
			{
				array_push($evaluationrow, $mark->getShortName());
			}
			else
			{
				array_push($evaluationrow, "");
			}
			if ($this->object->ects_output)
			{
				$mark_ects = $this->object->getECTSGrade($pass[$statpass]["reached"], $maxpoints);
				array_push($evaluationrow, $mark_ects);
			}
			$preached = count($qpass) ? ((count($pass[$statpass])-2) / count($qpass))* 100.0 : 0;
			array_push($evaluationrow, (count($pass[$statpass])-2) . " " . strtolower($this->lng->txt("of")) . " " . count($qpass) . " (" . sprintf("%2.2f", $preached) . " %" . ")");

			$time_seconds = $times[$active_id];
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			array_push($evaluationrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->ctrl->setParameter($this, "active_id", $active_id);
			$href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
			$detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
			if ((!$filter) || (($filter) && ($passedonly) && ($mark->getPassed())) || (($filter) && (strlen($filtertext) > 0) && (strpos(strtolower($fullname), strtolower($filtertext)) !== FALSE)))
			{
				array_push($evaluationrow, "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>");
				array_push($evaluation_rows, $evaluationrow);
				$counter++;
			}
		}
		$table->setData($evaluation_rows);
		$tableoutput = $table->render();
		$this->tpl->setVariable("EVALUATION_DATA", $tableoutput);

		$template = new ilTemplate("tpl.il_as_tst_evaluation_filter.html", TRUE, TRUE, "Modules/Test");
		$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("TEXT_FILTER_USERS", $this->lng->txt("filter_users"));
		$template->setVariable("TEXT_FILTER", $this->lng->txt("set_filter"));
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
		
		$template = new ilTemplate("tpl.il_as_tst_evaluation_export.html", TRUE, TRUE, "Modules/Test");
		$template->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
		include_once "./Modules/Test/classes/class.ilTestCertificate.php";
		if (ilTestCertificate::_isComplete($this->object->getId()))
		{
			$template->setVariable("TEXT_CERTIFICATE", $this->lng->txt("exp_type_certificate"));
		}
		$template->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$template->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
		$template->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$template->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$template->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
		$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "exportEvaluation"));
		$exportoutput = $template->get();

		$this->tpl->setVariable("EVALUATION_FILTER", $filteroutput);
		$this->tpl->setVariable("EVALUATION_EXPORT", $exportoutput);
		
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
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
		
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();

		$overview =& $this->object->evalResultsOverviewOfParticipant($active_id);
		$userdata =& $overview[$active_id];
		$questions =& $this->object->getQuestionsOfTest($active_id);

		$bestpass = 0;
		$bestpasspoints = 0;
		$lastpass = 0;
		foreach ($userdata as $passnr => $userresults)
		{
			if (is_numeric($passnr))
			{
				$reached = $pass[$passnr]["reached"];
				if ($reached > $bestpasspoints)
				{
					$bestpasspoints = $reached;
					$bestpass = $passnr;
				}
				if ($passnr > $lastpass) $lastpass = $passnr;
			}
		}
		$statpass = 0;
		if ($this->object->getPassScoring() == SCORE_BEST_PASS)
		{
			$statpass = $bestpass;
		}
		else
		{
			$statpass = $lastpass;
		}

		$this->tpl->setVariable("TEXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTarget($this, "outEvaluation"));
		$this->tpl->setVariable("HEADING_DETAILED_EVALUATION", sprintf($this->lng->txt("detailed_evaluation_for"), 
			$this->object->buildName($userdata["usr_id"], $userdata["firstname"], $userdata["lastname"], $userdata["title"]))
		);
		$this->tpl->setVariable("STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));

		$maxpoints = 0;
		foreach ($questions as $row)
		{
			if (!$this->object->isRandomTest())
			{
				$maxpoints += $row["points"];
			}
			else
			{
				if (count($questions) == $this->object->getQuestionCount())
				{
					if ($row["pass"] == 0) $maxpoints += $row["points"];
				}
				else
				{
					if ($row["pass"] == $statpass) $maxpoints += $row["points"];
				}
			}
		}
		$reachedpercent = $maxpoints ? $userdata[$statpass]["reached"] / $maxpoints * 100.0 : 0;
		$this->tpl->setVariable("VALUE_RESULTSPOINTS", $userdata[$statpass]["reached"] . " " . strtolower($this->lng->txt("of")) . " " . $maxpoints . " (" . sprintf("%2.2f", $reachedpercent) . " %" . ")");
		$mark = $this->object->mark_schema->getMatchingMark($reachedpercent);
		if (is_object($mark))
		{
			$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
			$this->tpl->setVariable("VALUE_RESULTSMARKS", $mark->getShortName());
			if ($this->object->ects_output)
			{
				$this->tpl->setVariable("TXT_ECTS", $this->lng->txt("ects_grade"));
				$this->tpl->setVariable("VALUE_ECTS", $this->object->getECTSGrade($userdata[$statpass]["reached"], $maxpoints));
			}
		}
		$workedthrough = (count($userdata[$statpass])-2);
		$percentworked = $this->object->getQuestionCount() ? $workedthrough / $this->object->getQuestionCount() * 100.0 : 0;
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("VALUE_QWORKEDTHROUGH", $workedthrough . " " . strtolower($this->lng->txt("of")) . " " . $this->object->getQuestionCount() . " (" . sprintf("%2.2f", $percentworked) . " %" . ")");

		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$time_seconds = $this->object->getCompleteWorkingTimeOfParticipant($active_id);
		$atime_seconds = $this->object->getQuestionCount() ? $time_seconds / $this->object->getQuestionCount() : 0;
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
		$visits = $this->object->getVisitTimeOfParticipant($active_id);
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("VALUE_FIRSTVISIT", 
			date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $visits["firstvisit"])
		);
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("VALUE_LASTVISIT",
			date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $visits["lastvisit"])
		);
		$this->tpl->setVariable("TXT_NROFPASSES", $this->lng->txt("tst_nr_of_passes"));
		$this->tpl->setVariable("VALUE_NROFPASSES", $lastpass + 1);
		$this->tpl->setVariable("TXT_SCOREDPASS", $this->lng->txt("scored_pass"));
		$this->tpl->setVariable("VALUE_SCOREDPASS", $statpass + 1);
		
		include_once "./Modules/Test/classes/class.ilTestStatistics.php";
		$stat = new ilTestStatistics($this->object->getTestId());
		$median = $stat->getStatistics()->median();
		$pct = $maxpoints ? ($median / $maxpoints) * 100.0 : 0;
		$mark = $this->object->mark_schema->getMatchingMark($pct);
		if (is_object($mark))
		{
			$this->tpl->setVariable("TXT_MARK_MEDIAN", $this->lng->txt("tst_stat_result_mark_median"));
			$this->tpl->setVariable("VALUE_MARK_MEDIAN", $mark->getShortName());
		}

		$this->tpl->setVariable("TXT_RANK_PARTICIPANT", $this->lng->txt("tst_stat_result_rank_participant"));
		$this->tpl->setVariable("VALUE_RANK_PARTICIPANT", $stat->getStatistics()->rank($userdata[$statpass]["reached"]));
		$this->tpl->setVariable("TXT_RANK_MEDIAN", $this->lng->txt("tst_stat_result_rank_median"));
		$this->tpl->setVariable("VALUE_RANK_MEDIAN", $stat->getStatistics()->rank_median());
		$this->tpl->setVariable("TXT_TOTAL_PARTICIPANTS", $this->lng->txt("tst_stat_result_total_participants"));
		$this->tpl->setVariable("VALUE_TOTAL_PARTICIPANTS", $stat->getStatistics()->count());
		$this->tpl->setVariable("TXT_RESULT_MEDIAN", $this->lng->txt("tst_stat_result_median"));
		$this->tpl->setVariable("VALUE_RESULT_MEDIAN", $median);

		for ($pass = 0; $pass <= $lastpass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($active_id, $pass);
			if ($finishdate > 0)
			{
				$this->tpl->setCurrentBlock("question_header");
				$this->tpl->setVariable("TXT_QUESTION_DATA", sprintf($this->lng->txt("tst_eval_question_points"), $pass+1));
				$this->tpl->parseCurrentBlock();
				$result_array =& $this->object->getTestResult($active_id, $pass, TRUE);
				$counter = 1;
				foreach ($result_array as $index => $question_data)
				{
					if (is_numeric($index))
					{
						$this->tpl->setCurrentBlock("question_row");
						$this->tpl->setVariable("QUESTION_COUNTER", $counter);
						$this->tpl->setVariable("QUESTION_TITLE", $question_data["title"]);
						$this->tpl->setVariable("QUESTION_POINTS", $question_data["reached"] . " " . strtolower($this->lng->txt("of")) . " " . $question_data["max"] . " (" . $question_data["percent"] . ")");
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
				$this->tpl->touchBlock("question_stats");
			}
		}

/*
		$total_users =& $this->object->getParticipants();

		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) 
		{
			// receive array with statistical information on the test for a specific user
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"]), $question_stat))
					{
						$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["single_max"] = $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["max"] += $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["reached"] += $sarray["reached"];
				}
			}
			$evaluation_array[$key] = $stat_eval;
		}
		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$median_array = array();
		foreach ($evaluation_array as $key => $value)
		{
			array_push($median_array, $value["resultspoints"]);
		}
		include_once "./classes/class.ilStatistics.php";
		$statistics = new ilStatistics();
		$statistics->setData($median_array);
		$median = $statistics->median();
		
		$stat_eval =& $this->object->evalStatistical($active_id);
		$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
		$rank_median = $statistics->rank_median();
		
		$this->tpl->setVariable("HEADING_DETAILED_EVALUATION", sprintf($this->lng->txt("detailed_evaluation_for"), $total_users[$active_id]["fullname"]));
		$this->tpl->setVariable("STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_MARK_MEDIAN", $this->lng->txt("tst_stat_result_mark_median"));
		$this->tpl->setVariable("TXT_RANK_PARTICIPANT", $this->lng->txt("tst_stat_result_rank_participant"));
		$this->tpl->setVariable("TXT_RANK_MEDIAN", $this->lng->txt("tst_stat_result_rank_median"));
		$this->tpl->setVariable("TXT_TOTAL_PARTICIPANTS", $this->lng->txt("tst_stat_result_total_participants"));
		$this->tpl->setVariable("TXT_RESULT_MEDIAN", $this->lng->txt("tst_stat_result_median"));
		$this->tpl->setVariable("VALUE_QWORKEDTHROUGH", $stat_eval["qworkedthrough"] . " " . strtolower($this->lng->txt("of")) . " " . $stat_eval["qmax"] . " (" . sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %" . ")");
		$time = $stat_eval["timeofwork"];
		$time_seconds = $time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("VALUE_TIMEOFWORK", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$time = $stat_eval["atimeofwork"];
		$time_seconds = $time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("VALUE_ATIMEOFWORK", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->tpl->setVariable("VALUE_FIRSTVISIT", 
			date(
				$this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], 
				mktime(
					$stat_eval["firstvisit"]["hours"], 
					$stat_eval["firstvisit"]["minutes"], 
					$stat_eval["firstvisit"]["seconds"], 
					$stat_eval["firstvisit"]["mon"], 
					$stat_eval["firstvisit"]["mday"], 
					$stat_eval["firstvisit"]["year"]
				)
			)
		);
		$this->tpl->setVariable("VALUE_LASTVISIT",
			date(
				$this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], 
				mktime(
					$stat_eval["lastvisit"]["hours"], 
					$stat_eval["lastvisit"]["minutes"], 
					$stat_eval["lastvisit"]["seconds"], 
					$stat_eval["lastvisit"]["mon"], 
					$stat_eval["lastvisit"]["mday"], 
					$stat_eval["lastvisit"]["year"]
				)
			)
		);
		
		if (($stat_eval["maxpoints"]) > 0)
		{
			$reachedpercent = $stat_eval["resultspoints"] / $stat_eval["maxpoints"];
		}
		else
		{
			$reachedpercent = 0;
		}
		$this->tpl->setVariable("VALUE_RESULTSPOINTS", $stat_eval["resultspoints"] . " " . strtolower($this->lng->txt("of")) . " " . $stat_eval["maxpoints"] . " (" . sprintf("%2.2f", $reachedpercent * 100.0) . " %" . ")");
		$this->tpl->setVariable("VALUE_RESULTSMARKS", $stat_eval["resultsmarks"]);
		if ($this->object->ects_output)
		{
			$this->tpl->setVariable("TXT_ECTS", $this->lng->txt("ects_grade"));
			$this->tpl->setVariable("VALUE_ECTS", $this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"]));
		}
		if ($stat_eval["maxpoints"] == 0)
		{
			$pct = 0;
		}
		else
		{
			$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
		}
		$mark = $this->object->mark_schema->getMatchingMark($pct);
		$mark_short_name = "";
		if ($mark)
		{
			$mark_short_name = $mark->getShortName();
		}
		$this->tpl->setVariable("VALUE_MARK_MEDIAN", $mark_short_name);
		$this->tpl->setVariable("VALUE_RANK_PARTICIPANT", $statistics->rank($stat_eval["resultspoints"]));
		$this->tpl->setVariable("VALUE_RANK_MEDIAN", $statistics->rank_median());
		$this->tpl->setVariable("VALUE_TOTAL_PARTICIPANTS", count($median_array));
		$this->tpl->setVariable("VALUE_RESULT_MEDIAN", $median);
		$this->tpl->setVariable("TEXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTarget($this, "outEvaluation"));
		$reached_pass = $this->object->_getPass($active_id);
		for ($pass = 0; $pass <= $reached_pass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($active_id, $pass);
			if ($finishdate > 0)
			{
				$this->tpl->setCurrentBlock("question_header");
				$this->tpl->setVariable("TXT_QUESTION_DATA", sprintf($this->lng->txt("tst_eval_question_points"), $pass+1));
				$this->tpl->parseCurrentBlock();
				$result_array =& $this->object->getTestResult($active_id, $pass, TRUE);
				foreach ($result_array as $index => $question_data)
				{
					if (is_numeric($index))
					{
						$this->tpl->setCurrentBlock("question_row");
						$this->tpl->setVariable("QUESTION_TITLE", $question_data["title"]);
						$this->tpl->setVariable("QUESTION_POINTS", $question_data["reached"] . " " . strtolower($this->lng->txt("of")) . " " . $question_data["max"] . " (" . $question_data["percent"] . ")");
						$this->tpl->parseCurrentBlock();
					}
				}
				$this->tpl->touchBlock("question_stats");
			}
		}*/
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
		if (!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) 
		{
			// allow only evaluation access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", "Modules/Test");
		$total_persons = $this->object->evalTotalPersons();
		if ($total_persons) {
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", $total_persons);
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
			$passed_tests = $this->object->evalTotalFinishedPassed();
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed"));
			$this->tpl->setVariable("TXT_VALUE", $passed_tests["total_passed"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_points"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $passed_tests["average_points"]) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $passed_tests["maximum_points"]));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_time"));
			$average_time = $this->object->evalTotalPassedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ANONYMOUS_AGGREGATION", $this->lng->txt("tst_eval_no_anonymous_aggregation"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		
		$overview =& $this->object->_evalResultsOverview();
		$questions = array();
		foreach ($overview as $active_id => $pass)
		{
			foreach ($pass as $passnr => $userresults)
			{
				if (is_numeric($passnr))
				{
					foreach ($userresults as $userresult)
					{
						if (is_array($userresult))
						{
							if (!array_key_exists($userresult["original_id"], $questions))
							{
								$questions[$userresult["original_id"]] = array(
									"reached" => 0, 
									"max" => $userresult["maxpoints"], 
									"count" => 0, 
									"title" => $userresult["questiontitle"]
								);
							}
							$questions[$userresult["original_id"]]["reached"] += $userresult["points"];
							$questions[$userresult["original_id"]]["count"]++;
						}
					}
				}
			}
		}
		$counter = 0;
		foreach ($questions as $avg)
		{
			$this->tpl->setCurrentBlock("avg_row");
			$this->tpl->setVariable("TXT_QUESTIONTITLE", $avg["title"]);
			$reached = $avg["count"] ? $avg["reached"]/$avg["count"] : 0;
			$max = $avg["max"];
			$percent = $max ? $reached/$max * 100.0 : 0;
			$this->tpl->setVariable("TXT_POINTS", sprintf("%.2f", $reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $max));
			$this->tpl->setVariable("TXT_PERCENT", sprintf("%.2f", $percent) . "%");
			$this->tpl->setVariable("TXT_ANSWERS", $avg["count"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		}
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
		// Creating a workbook
		$result = @include_once 'Spreadsheet/Excel/Writer.php';
		if (!$result)
		{
			include_once './classes/Spreadsheet/Excel/Writer.php';
		}
		$workbook = new Spreadsheet_Excel_Writer();
		// sending HTTP headers
		$workbook->send(ilUtil::getASCIIFilename($this->object->getTitle() . ".xls"));
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
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultspoints")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("maximum_points")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultsmarks")), $format_title);
		if ($this->object->ects_output)
		{
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("ects_grade")), $format_title);
		}
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qworkedthrough")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qmax")), $format_title);
		if ($stat_eval["qmax"] > 0)
		{
			$workload = $stat_eval["qworkedthrough"] / $stat_eval["qmax"];
		}
		else
		{
			$workload = 0;
		}
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

		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("pass")), $format_title);

		include_once "./classes/class.ilExcelUtils.php";
		$total_users =& $this->object->getParticipants();
		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) 
		{
			// receive array with statistical information on the test for a specific user
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"]), $question_stat))
					{
						$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["single_max"] = $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["max"] += $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["reached"] += $sarray["reached"];
				}
			}
			$evaluation_array[$key] = $stat_eval;
		}
		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$median_array = array();
		foreach ($evaluation_array as $key => $value)
		{
			array_push($median_array, $value["resultspoints"]);
		}
		include_once "./classes/class.ilStatistics.php";
		$statistics = new ilStatistics();
		$statistics->setData($median_array);
		$median = $statistics->median();
		
		$counter = 1;
		foreach ($total_users as $key => $value) 
		{
			$remove = FALSE;
			if (strlen($filtertext))
			{
				$username = $value["name"];
				if (!@preg_match("/$filtertext/i", $username))
				{
					$remove = TRUE;
				}
			}
			if ($passedonly)
			{
				if ($evaluation_array[$key]["passed"] == 0)
				{
					$remove = TRUE;
				}
			}
			if (!$remove)
			{
				$row++;
				if ($this->object->isRandomTest())
				{
					$row++;
				}
				$col = 0;
				$stat_eval =& $this->object->evalStatistical($key);
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				$rank_median = $statistics->rank_median();
				if ($this->object->getAnonymity())
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($counter));
				}
				else
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value["name"]));
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value["login"]));
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($stat_eval["resultspoints"]));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($stat_eval["maxpoints"]));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($stat_eval["resultsmarks"]));
				if ($this->object->ects_output)
				{
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"])));
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($stat_eval["qworkedthrough"]));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($stat_eval["qmax"]));
				if ($stat_eval["qmax"] > 0)
				{
					$workload = $stat_eval["qworkedthrough"] / $stat_eval["qmax"];
				}
				else
				{
					$workload = 0;
				}
				$worksheet->write($row, $col++, $workload, $format_percent);
				$time = $stat_eval["timeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$time = $stat_eval["atimeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$firstvisit = ilUtil::excelTime(
					$stat_eval["firstvisit"]["year"],
					$stat_eval["firstvisit"]["mon"],
					$stat_eval["firstvisit"]["mday"],
					$stat_eval["firstvisit"]["hours"],
					$stat_eval["firstvisit"]["minutes"],
					$stat_eval["firstvisit"]["seconds"]
				);
				$worksheet->write($row, $col++, $firstvisit, $format_datetime);				
				$lastvisit = ilUtil::excelTime(
					$stat_eval["lastvisit"]["year"],
					$stat_eval["lastvisit"]["mon"],
					$stat_eval["lastvisit"]["mday"],
					$stat_eval["lastvisit"]["hours"],
					$stat_eval["lastvisit"]["minutes"],
					$stat_eval["lastvisit"]["seconds"]
				);
				$worksheet->write($row, $col++, $lastvisit, $format_datetime);				
				
				if (($stat_eval["maxpoints"]) > 0)
				{
					$reachedpercent = $stat_eval["resultspoints"] / $stat_eval["maxpoints"];
				}
				else
				{
					$reachedpercent = 0;
				}
				if ($stat_eval["maxpoints"] == 0)
				{
					$pct = 0;
				}
				else
				{
					$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
				}
				$mark = $this->object->mark_schema->getMatchingMark($pct);
				$mark_short_name = "";
				if ($mark)
				{
					$mark_short_name = $mark->getShortName();
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($mark_short_name));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($statistics->rank($stat_eval["resultspoints"])));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($statistics->rank_median()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(count($median_array)));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($median));
				$reached_pass = $this->object->_getPass($key);
				$startcol = $col;
				for ($pass = 0; $pass <= $reached_pass; $pass++)
				{
					$col = $startcol;
					$finishdate = $this->object->getPassFinishDate($key, $pass);
					if ($finishdate > 0)
					{
						if ($pass > 0)
						{
							$row++;
							if ($this->object->isRandomTest())
							{
								$row++;
							}
						}
						$worksheet->write($row, $col++, ilExcelUtils::_convert_text($pass+1));
						$result_array =& $this->object->getTestResult($key, $pass, TRUE);
						foreach ($result_array as $index => $question_data)
						{
							if (is_numeric($index))
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($question_data["reached"]));
								if ($this->object->isRandomTest())
								{
									$worksheet->write($row-1, $col, ilExcelUtils::_convert_text($question_data["title"]), $format_title);
								}
								else
								{
									if ($pass == 0)
									{
										$worksheet->write(0, $col, ilExcelUtils::_convert_text($question_data["title"]), $format_title);
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
		if ($this->object->getAnonymity())
		{
			array_push($datarow, $this->lng->txt("counter"));
		}
		else
		{
			array_push($datarow, $this->lng->txt("name"));
			array_push($datarow, $this->lng->txt("login"));
		}
		array_push($datarow, $this->lng->txt("tst_stat_result_resultspoints"));
		array_push($datarow, $this->lng->txt("maximum_points"));
		array_push($datarow, $this->lng->txt("tst_stat_result_resultsmarks"));
		if ($this->object->ects_output)
		{
			array_push($datarow, $this->lng->txt("ects_grade"));
		}
		array_push($datarow, $this->lng->txt("tst_stat_result_qworkedthrough"));
		array_push($datarow, $this->lng->txt("tst_stat_result_qmax"));
		if ($stat_eval["qmax"] > 0)
		{
			$workload = $stat_eval["qworkedthrough"] / $stat_eval["qmax"];
		}
		else
		{
			$workload = 0;
		}
		array_push($datarow, $this->lng->txt("tst_stat_result_pworkedthrough"));
		array_push($datarow, $this->lng->txt("tst_stat_result_timeofwork"));
		array_push($datarow, $this->lng->txt("tst_stat_result_atimeofwork"));
		array_push($datarow, $this->lng->txt("tst_stat_result_firstvisit"));
		array_push($datarow, $this->lng->txt("tst_stat_result_lastvisit"));
		
		array_push($datarow, $this->lng->txt("tst_stat_result_mark_median"));
		array_push($datarow, $this->lng->txt("tst_stat_result_rank_participant"));
		array_push($datarow, $this->lng->txt("tst_stat_result_rank_median"));
		array_push($datarow, $this->lng->txt("tst_stat_result_total_participants"));
		array_push($datarow, $this->lng->txt("tst_stat_result_median"));

		array_push($datarow, $this->lng->txt("pass"));

		$total_users =& $this->object->getParticipants();
		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) 
		{
			// receive array with statistical information on the test for a specific user
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"]), $question_stat))
					{
						$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["single_max"] = $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["max"] += $sarray["max"];
					$question_stat[$this->getEvaluationQuestionId($sarray["qid"], $sarray["original_id"])]["reached"] += $sarray["reached"];
				}
			}
			$evaluation_array[$key] = $stat_eval;
		}
		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$median_array = array();
		foreach ($evaluation_array as $key => $value)
		{
			array_push($median_array, $value["resultspoints"]);
		}
		include_once "./classes/class.ilStatistics.php";
		$statistics = new ilStatistics();
		$statistics->setData($median_array);
		$median = $statistics->median();
		
		$counter = 1;
		$headerrow = $datarow;
		foreach ($total_users as $key => $value) 
		{
			$datarow = $headerrow;
			$remove = FALSE;
			if (strlen($filtertext))
			{
				$username = $value["name"];
				if (!@preg_match("/$filtertext/i", $username))
				{
					$remove = TRUE;
				}
			}
			if ($passedonly)
			{
				if ($evaluation_array[$key]["passed"] == 0)
				{
					$remove = TRUE;
				}
			}
			if (!$remove)
			{
				$datarow2 = array();
				$stat_eval =& $this->object->evalStatistical($key);
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				$rank_median = $statistics->rank_median();
				if ($this->object->getAnonymity())
				{
					array_push($datarow2, $counter);
				}
				else
				{
					array_push($datarow2, $value["name"]);
					array_push($datarow2, $value["login"]);
				}
				array_push($datarow2, $stat_eval["resultspoints"]);
				array_push($datarow2, $stat_eval["maxpoints"]);
				array_push($datarow2, $stat_eval["resultsmarks"]);
				if ($this->object->ects_output)
				{
					array_push($datarow2, $this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"]));
				}
				array_push($datarow2, $stat_eval["qworkedthrough"]);
				array_push($datarow2, $stat_eval["qmax"]);
				if ($stat_eval["qmax"] > 0)
				{
					$workload = $stat_eval["qworkedthrough"] / $stat_eval["qmax"];
				}
				else
				{
					$workload = 0;
				}
				array_push($datarow2, $workload);
				$time = $stat_eval["timeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($datarow2, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
				$time = $stat_eval["atimeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($datarow2, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
				$firstvisit = date(
					$this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], 
					mktime(
						$stat_eval["firstvisit"]["hours"], 
						$stat_eval["firstvisit"]["minutes"], 
						$stat_eval["firstvisit"]["seconds"], 
						$stat_eval["firstvisit"]["mon"], 
						$stat_eval["firstvisit"]["mday"], 
						$stat_eval["firstvisit"]["year"]
					)
				);
				array_push($datarow2, $firstvisit);				
				$lastvisit = date(
					$this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], 
					mktime(
						$stat_eval["lastvisit"]["hours"], 
						$stat_eval["lastvisit"]["minutes"], 
						$stat_eval["lastvisit"]["seconds"], 
						$stat_eval["lastvisit"]["mon"], 
						$stat_eval["lastvisit"]["mday"], 
						$stat_eval["lastvisit"]["year"]
					)
				);
				array_push($datarow2, $lastvisit);				
				
				if (($stat_eval["maxpoints"]) > 0)
				{
					$reachedpercent = $stat_eval["resultspoints"] / $stat_eval["maxpoints"];
				}
				else
				{
					$reachedpercent = 0;
				}
				if ($stat_eval["maxpoints"] == 0)
				{
					$pct = 0;
				}
				else
				{
					$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
				}
				$mark = $this->object->mark_schema->getMatchingMark($pct);
				$mark_short_name = "";
				if ($mark)
				{
					$mark_short_name = $mark->getShortName();
				}
				array_push($datarow2, $mark_short_name);
				array_push($datarow2, $statistics->rank($stat_eval["resultspoints"]));
				array_push($datarow2, $statistics->rank_median());
				array_push($datarow2, count($median_array));
				array_push($datarow2, $median);
				$pass = $this->object->_getResultPass($key);
				$finishdate = $this->object->getPassFinishDate($key, $pass);
				if ($finishdate > 0)
				{
					array_push($datarow2, $pass+1);
					$result_array =& $this->object->getTestResult($key, $pass, TRUE);
					foreach ($result_array as $index => $question_data)
					{
						if (is_numeric($index))
						{
							array_push($datarow2, $question_data["reached"]);
							array_push($datarow, $question_data["title"]);
						}
					}
					if ($this->object->isRandomTest() || $counter == 1)
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
		$csv = "";
		$separator = ";";
		foreach ($rows as $evalrow)
		{
			$csvrow =& $this->object->processCSVRow($evalrow, TRUE, $separator);
			$csv .= join($csvrow, $separator) . "\n";
		}
		ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->object->getTitle() . " .csv"));
		break;
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
}
?>
