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
		if ($ilAccess->checkAccess("tst_statistics", "", $this->ref_id))
		{
			sendInfo($this->lng->txt("cannot_edit_test"), TRUE);
			$this->ctrl->redirectByClass("ilobjtest", "infoScreen");
		}
	}
	
	function eval_stat()
	{
		$this->ctrl->setCmdClass(get_class($this));
		$this->ctrl->setCmd("eval_stat");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation_selection.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CMD_EVAL", "evalAllUsers");
		$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
		$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_all_users"));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Saves the  evaluation settings for the current user
	*
	* Saves the  evaluation settings for the current user
	*
	* @access private
	*/
	function saveEvaluationSettings()
	{
		global $ilUser;
		
		$eval_statistical_settings = array(
			"resultspoints" => $_POST["chb_result_resultspoints"],
			"resultsmarks" => $_POST["chb_result_resultsmarks"],
			"qworkedthrough" => $_POST["chb_result_qworkedthrough"],
			"pworkedthrough" => $_POST["chb_result_pworkedthrough"],
			"timeofwork" => $_POST["chb_result_timeofwork"],
			"atimeofwork" => $_POST["chb_result_atimeofwork"],
			"firstvisit" => $_POST["chb_result_firstvisit"],
			"lastvisit" => $_POST["chb_result_lastvisit"],
			"distancemedian" => $_POST["chb_result_distancemedian"]
		);
		$this->object->evalSaveStatisticalSettings($eval_statistical_settings, $ilUser->getId());
		$this->ctrl->redirect($this, "evalAllUsers");
	}
	
	/**
	* Creates and returns the HTML for the statistical evaluation settings
	*
	* Creates HTML output with the list of selectable statistics parameters for the statistical
	* output and fills it with the preselection of the current user
	*
	* @access private
	*/
	function getStatisticalSettingsOutput()
	{
		global $ilUser;
		
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_tst_evaluation_specification.html", TRUE, TRUE, "Modules/Test");
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BUTTON_CHANGE", $this->lng->txt("change"));
		$template->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$template->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$template->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$template->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$template->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$template->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$template->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$template->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$template->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$template->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->getId());
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$template->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$template->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$template->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$template->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$template->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$template->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$template->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$template->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$template->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		return $template->get();
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
	
	function &getEvaluationRow($active_id, &$eval_data, $user_data, $counter, $total_participants, $rank_participant, $rank_median, $median)
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
			$filtertext = $_POST["userfilter"];
			if ($_POST["passedonly"] == 1)
			{
				$passedonly = TRUE;
			}
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
			$filter = 1;
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
					if (!array_key_exists($sarray["qid"], $question_stat))
					{
						$question_stat[$sarray["qid"]] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$sarray["qid"]]["single_max"] = $sarray["max"];
					$question_stat[$sarray["qid"]]["max"] += $sarray["max"];
					$question_stat[$sarray["qid"]]["reached"] += $sarray["reached"];
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
		
		include_once("./classes/class.ilTableGUI.php");
		$table = new ilTableGUI(0, FALSE);
		$table->setTitle($this->lng->txt("participants_evaluation"));
		$table->setHeaderNames($this->getHeaderNames());

		$table->enable("auto_sort");
		$table->enable("sort");
		//$table->disable("sort");
		//$table->disable("auto_sort");
		// $table->disable("title");
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
				$stat_eval =& $this->object->evalStatistical($key);
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				$rank_median = $statistics->rank_median();
				$tablerow =& $this->getEvaluationRow($key, $stat_eval, $value, $counter, count($evaluation_array), $rank_participant, $rank_median, $median);
				array_push($evaluation_rows, $tablerow);
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
		$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
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
			sendInfo($this->lng->txt("detailed_evaluation_missing_active_id"), TRUE);
			$this->ctrl->redirect($this, "outEvaluation");
		}
		
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();

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
					if (!array_key_exists($sarray["qid"], $question_stat))
					{
						$question_stat[$sarray["qid"]] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$sarray["qid"]]["single_max"] = $sarray["max"];
					$question_stat[$sarray["qid"]]["max"] += $sarray["max"];
					$question_stat[$sarray["qid"]]["reached"] += $sarray["reached"];
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
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
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
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"])));
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
					if (!array_key_exists($sarray["qid"], $question_stat))
					{
						$question_stat[$sarray["qid"]] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$sarray["qid"]]["single_max"] = $sarray["max"];
					$question_stat[$sarray["qid"]]["max"] += $sarray["max"];
					$question_stat[$sarray["qid"]]["reached"] += $sarray["reached"];
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
	}
	
	function evalAllUsers()
	{
		global $ilUser;

		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$savetextanswers = 0;
		$export = 0;
		$filter = 0;
		$filtertext = "";
		$passedonly = FALSE;
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("set_filter")) == 0)
		{
			$filter = 1;
			$filtertext = $_POST["userfilter"];
			if ($_POST["passedonly"] == 1)
			{
				$passedonly = TRUE;
			}
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
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("reset_filter")) == 0)
		{
			$filter = 1;
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
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("export")) == 0)
		{
			$export = 1;
		}
		if (($export == 1) && (strcmp($_POST["export_type"], "certificate") == 0))
		{
			if ($passedonly)
			{
				$this->ctrl->setParameterByClass("iltestcertificategui", "g_passedonly", "1");
			}
			if (strlen($filtertext))
			{
				$this->ctrl->setParameterByClass("iltestcertificategui", "g_userfilter", $filtertext);
			}
			$this->ctrl->redirectByClass("iltestcertificategui", "exportCertificate");
			return;
		}
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		$eval_statistical_settings = array(
			"resultspoints" => $user_settings["resultspoints"],
			"resultsmarks" => $user_settings["resultsmarks"],
			"qworkedthrough" => $user_settings["qworkedthrough"],
			"pworkedthrough" => $user_settings["pworkedthrough"],
			"timeofwork" => $user_settings["timeofwork"],
			"atimeofwork" => $user_settings["atimeofwork"],
			"firstvisit" => $user_settings["firstvisit"],
			"lastvisit" => $user_settings["lastvisit"],
			"distancemedian" => $user_settings["distancemedian"]
		);

		$legend = array();
		$legendquestions = array();
		$titlerow = array();
		// build title columns
		$sortimage = "";
		$sortparameter = "asc";
		if (strcmp($_GET["sortname"], "asc") == 0 || strcmp($_GET["sortname"], "") == 0)
		{
			$sortimage = " <img src=\"".ilUtil::getImagePath("asc_order.gif")."\" alt=\"" . $this->lng->txt("ascending_order") . "\" />";
			$sortparameter = "asc";
			$this->ctrl->setParameter($this, "sortname", "asc");
		}
		else
		{
			$sortimage = " <img src=\"".ilUtil::getImagePath("desc_order.gif")."\" alt=\"" . $this->lng->txt("descending_order") . "\" />";
			$sortparameter = "desc";
			$this->ctrl->setParameter($this, "sortname", "desc");
		}
		$name_column = $this->lng->txt("name");
		if ($this->object->getAnonymity())
		{
			$name_column = $this->lng->txt("counter");
		}
		array_push($titlerow, $name_column);
		
		$char = "A";
		if ($eval_statistical_settings["resultspoints"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultspoints");
			$char++;
		}
		if ($eval_statistical_settings["resultsmarks"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultsmarks");
			$char++;
			
			if ($this->object->ects_output)
			{
				array_push($titlerow, $char);
				$legend[$char] = $this->lng->txt("ects_grade");
				$char++;
			}
		}
		if ($eval_statistical_settings["qworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_qworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["pworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_pworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["timeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_timeofwork");
			$char++;
		}
		if ($eval_statistical_settings["atimeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_atimeofwork");
			$char++;
		}
		if ($eval_statistical_settings["firstvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_firstvisit");
			$char++;
		}
		if ($eval_statistical_settings["lastvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_lastvisit");
			$char++;
		}
		if ($eval_statistical_settings["distancemedian"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_mark_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_participant");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_total_participants");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_median");
			$char++;
		}
		
		$titlerow_without_questions = $titlerow;
		if (!$this->object->isRandomTest())
		{
			$qtitles =& $this->object->getQuestionTitles();
			$i = 1;
			foreach ($qtitles as $title)
			{
				array_push($titlerow, $this->lng->txt("question_short") . " " . $i);
				$legendquestions[$i] = $title;
				$legend[$this->lng->txt("question_short") . " " . $i] = $i;
				$i++;
			}
		}
		else
		{
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				array_push($titlerow, "&nbsp;");
			}
		}
		$total_users =& $this->object->evalTotalPersonsArray($sortparameter);
		$selected_users = array();
		$selected_users = $total_users;

		//			$ilBench->stop("Test_Statistical_evaluation", "getAllParticipants");
		$row = 0;
		$question_legend = false;
		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) 
		{
			// receive array with statistical information on the test for a specific user
//				$ilBench->start("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($sarray["qid"], $question_stat))
					{
						$question_stat[$sarray["qid"]] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$sarray["qid"]]["single_max"] = $sarray["max"];
					$question_stat[$sarray["qid"]]["max"] += $sarray["max"];
					$question_stat[$sarray["qid"]]["reached"] += $sarray["reached"];
				}
			}
//				$ilBench->stop("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$evaluation_array[$key] = $stat_eval;
		}

		foreach ($selected_users as $key => $name)
		{
			if (strlen($filtertext))
			{
				$username = $selected_users[$key];
				if (!@preg_match("/$filtertext/i", $username))
				{
					unset($selected_users[$key]);
				}
			}
			if ($passedonly)
			{
				if ($evaluation_array[$key]["passed"] == 0)
				{
					unset($selected_users[$key]);
				}
			}
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
//			$ilBench->stop("Test_Statistical_evaluation", "calculate all statistical data");
//			$ilBench->save();
		$evalcounter = 1;
		$question_titles = array();
		$question_title_counter = 1;
		$eval_complete = array();
		foreach ($selected_users as $key => $name)
		{
			$stat_eval = $evaluation_array[$key];
			$titlerow_user = array();
			if ($this->object->isRandomTest())
			{
				include_once "./Modules/Test/classes/class.ilObjTest.php";
				$active = $this->object->getActiveTestUser($key);
				$counted_pass = ilObjTest::_getResultPass($active->active_id);
				$this->object->loadQuestions($key, $counted_pass);
				$titlerow_user = $titlerow_without_questions;
				$i = 1;
				foreach ($stat_eval as $key1 => $value1)
				{
					if (preg_match("/\d+/", $key1))
					{
						$qt = $value1["title"];
						$qt = preg_replace("/<.*?>/", "", $qt);
						if (!array_key_exists($value1["qid"], $legendquestions))
						{
							array_push($titlerow_user, $this->lng->txt("question_short") . " " . $question_title_counter);
							$legend[$this->lng->txt("question_short") . " " . $question_title_counter] = $value1["qid"];
							$legendquestions[$value1["qid"]] = $qt;
							$question_title_counter++;
						}
						else
						{
							$arraykey = array_search($value1["qid"], $legend);
							array_push($titlerow_user, $arraykey);
						}
					}
				}
			}

			$evalrow = array();
			$username = $this->lng->txt("user") . " " . $evalcounter++; 
			if (!$this->object->getAnonymity())
			{
				$username = $selected_users[$key];
			}
			array_push($evalrow, array(
				"html" => "<a href=\"".$this->ctrl->getLinkTarget($this, "outStatisticsResultsOverview")."&active_id=$key\">$username</a>",
				"xls"  => $username,
				"csv"  => $username
			));
			if ($eval_statistical_settings["resultspoints"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultspoints"]." ".strtolower($this->lng->txt("of"))." ". $stat_eval["maxpoints"],
					"xls"  => $stat_eval["resultspoints"],
					"csv"  => $stat_eval["resultspoints"]
				));
			}
			if ($eval_statistical_settings["resultsmarks"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultsmarks"],
					"xls"  => $stat_eval["resultsmarks"],
					"csv"  => $stat_eval["resultsmarks"]
				));

				if ($this->object->ects_output)
				{
					$mark_ects = $this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"]);
					array_push($evalrow, array(
						"html" => $mark_ects,
						"xls"  => $mark_ects,
						"csv"  => $mark_ects
					));
				}
			}
			if ($eval_statistical_settings["qworkedthrough"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["qworkedthrough"],
					"xls"  => $stat_eval["qworkedthrough"],
					"csv"  => $stat_eval["qworkedthrough"]
				));
			}
			if ($eval_statistical_settings["pworkedthrough"]) {
				array_push($evalrow, array(
					"html" => sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %",
					"xls"  => $stat_eval["pworkedthrough"],
					"csv"  => $stat_eval["pworkedthrough"],
					"format" => "%"
				));
			}
			if ($eval_statistical_settings["timeofwork"]) 
			{
				$time = $stat_eval["timeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"csv"  => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)
				));
			}
			if ($eval_statistical_settings["atimeofwork"]) 
			{
				$time = $stat_eval["atimeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"csv"  => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)
				));
			}
			if ($eval_statistical_settings["firstvisit"]) 
			{
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["firstvisit"]["year"],$stat_eval["firstvisit"]["mon"],$stat_eval["firstvisit"]["mday"],$stat_eval["firstvisit"]["hours"],$stat_eval["firstvisit"]["minutes"],$stat_eval["firstvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"format" => "t"
				));
			}
			if ($eval_statistical_settings["lastvisit"]) {
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["lastvisit"]["year"],$stat_eval["lastvisit"]["mon"],$stat_eval["lastvisit"]["mday"],$stat_eval["lastvisit"]["hours"],$stat_eval["lastvisit"]["minutes"],$stat_eval["lastvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"format" => "t"
				));
			}
			
			if ($eval_statistical_settings["distancemedian"]) {
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
				array_push($evalrow, array(
					"html" => $mark_short_name,
					"xls"  => $mark_short_name,
					"csv"  => $mark_short_name
				));
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				array_push($evalrow, array(
					"html" => $rank_participant,
					"xls"  => $rank_participant,
					"csv"  => $rank_participant
				));
				$rank_median = $statistics->rank_median();
				array_push($evalrow, array(
					"html" => $rank_median,
					"xls"  => $rank_median,
					"csv"  => $rank_median
				));
				$total_participants = count($median_array);
				array_push($evalrow, array(
					"html" => $total_participants,
					"xls"  => $total_participants,
					"csv"  => $total_participants
				));
				array_push($evalrow, array(
					"html" => $median,
					"xls"  => $median,
					"csv"  => $median
				));
			}
			
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				$qshort = "";
				$qt = "";
				if ($this->object->isRandomTest())
				{
					$qt = $stat_eval[$i-1]["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					$arrkey = array_search($stat_eval[$i-1]["qid"], $legend);
					if ($arrkey)
					{
						$qshort = "<span title=\"" . ilUtil::prepareFormOutput($qt) . "\">" . $arrkey . "</span>: ";
					}
				}

				$htmloutput = $qshort . $stat_eval[$i-1]["reached"] . " " . strtolower($this->lng->txt("of")) . " " .  $stat_eval[$i-1]["max"];

				array_push($evalrow, array(
					"html" => $htmloutput,
					"xls"  => $stat_eval[$i-1]["reached"],
					"csv"  => $stat_eval[$i-1]["reached"]
				));
			}
			array_push($eval_complete, array("title" => $titlerow_user, "data" => $evalrow));
		}

		$noqcount = count($titlerow_without_questions);
		if ($export)
		{
			$testname = preg_replace("/\s/", "_", $this->object->getTitle());
			switch ($_POST["export_type"])
			{
				case TYPE_XLS_PC:
					// Creating a workbook
					$result = @include_once 'Spreadsheet/Excel/Writer.php';
					if (!$result)
					{
						include_once './classes/Spreadsheet/Excel/Writer.php';
					}
					$workbook = new Spreadsheet_Excel_Writer();
					// sending HTTP headers
					$workbook->send("$testname.xls");
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
					if (!$this->object->isRandomTest())
					{
						foreach ($titlerow as $title)
						{
							if (preg_match("/\d+/", $title))
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($legendquestions[$legend[$title]], $_POST["export_type"]), $format_title);
							}
							else if (strlen($title) == 1)
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$title], $_POST["export_type"]), $format_title);
							}
							else
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($title, $_POST["export_type"]), $format_title);
							}
							$col++;
						}
						$row++;
					}
					foreach ($eval_complete as $evalrow)
					{
						$col = 0;
						if ($this->object->isRandomTest())
						{
							foreach ($evalrow["title"] as $key => $value)
							{
								if ($key == 0)
								{
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $_POST["export_type"]), $format_title);
								}
								else
								{
									if (preg_match("/\d+/", $value))
									{
										$worksheet->write($row, $col, ilExcelUtils::_convert_text($legendquestions[$legend[$value]], $_POST["export_type"]), $format_title);
									}
									else
									{
										$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$value], $_POST["export_type"]), $format_title);
									}
								}
								$col++;
							}
							$row++;
						}
						$col = 0;
						foreach ($evalrow["data"] as $key => $value)
						{
							switch ($value["format"])
							{
								case "%":
									$worksheet->write($row, $col, $value["xls"], $format_percent);
									break;
								case "t":
									$worksheet->write($row, $col, $value["xls"], $format_datetime);
									break;
								default:
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value["xls"], $_POST["export_type"]));
									break;
							}
							$col++;
						}
						$row++;
					}
					$workbook->close();
					exit;
				case TYPE_SPSS:
					$csv = "";
					$separator = ";";
					if (!$this->object->isRandomTest())
					{
						$titlerow =& $this->object->processCSVRow($titlerow, TRUE, $separator);
						$csv .= join($titlerow, $separator) . "\n";
					}
					foreach ($eval_complete as $evalrow)
					{
						$csvrow = array();
						foreach ($evalrow["data"] as $dataarray)
						{
							array_push($csvrow, $dataarray["csv"]);
						}
						if ($this->object->isRandomTest())
						{
							$evalrow["title"] =& $this->object->processCSVRow($evalrow["title"], TRUE, $separator);
							$csv .= join($evalrow["title"], $separator) . "\n";
						}
						$csvarr = array();
						$evalrow["data"] =& $this->object->processCSVRow($csvrow, TRUE, $separator);
						$csv .= join($evalrow["data"], $separator) . "\n";
					}
					ilUtil::deliverData($csv, "$testname.csv");
					break;
			}
			exit;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation.html", "Modules/Test");
		$color_class = array("tblrow1", "tblrow2");
		foreach ($legend as $short => $long)
		{
			$this->tpl->setCurrentBlock("legendrow");
			$this->tpl->setVariable("TXT_SYMBOL", $short);
			if (preg_match("/\d+/", $short))
			{
				$this->tpl->setVariable("TXT_MEANING", $legendquestions[$long]);
			}
			else
			{
				$this->tpl->setVariable("TXT_MEANING", $long);
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("legend");
		$this->tpl->setVariable("TXT_LEGEND", $this->lng->txt("legend"));
		$this->tpl->setVariable("TXT_LEGEND_LINK", $this->lng->txt("eval_legend_link"));
		$this->tpl->setVariable("TXT_SYMBOL", $this->lng->txt("symbol"));
		$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("meaning"));
		$this->tpl->parseCurrentBlock();

		$counter = 0;
		foreach ($question_stat as $title => $values)
		{
			$this->tpl->setCurrentBlock("meanrow");
			$this->tpl->setVariable("TXT_QUESTION", ilUtil::prepareFormOutput($values["title"]));
			$percent = 0;
			if ($values["max"] > 0)
			{
				$percent = $values["reached"] / $values["max"];
			}
			$this->tpl->setVariable("TXT_MEAN", sprintf("%.2f", $values["single_max"]*$percent) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $values["single_max"]) . " (" . sprintf("%.2f", $percent*100) . " %)");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("question_mean_points");
		$this->tpl->setVariable("TXT_AVERAGE_POINTS", $this->lng->txt("average_reached_points"));
		$this->tpl->setVariable("TXT_QUESTION", $this->lng->txt("question_title"));
		$this->tpl->setVariable("TXT_MEAN", $this->lng->txt("average_reached_points"));
		$this->tpl->parseCurrentBlock();
		
		$noq = $noqcount;		
		foreach ($titlerow as $title)
		{
			if (strcmp($title, $this->lng->txt("name")) == 0)
			{
				if (strcmp($sortparameter, "asc") == 0)
				{
					$this->ctrl->setParameter($this, "sortname", "desc");
				}
				else
				{
					$this->ctrl->setParameter($this, "sortname", "asc");
				}
				$title = "<a href=\"".$this->ctrl->getLinkTarget($this, "evalAllUsers")."\">" . $this->lng->txt("name") . "</a>";
				$title .= $sortimage;
				$this->ctrl->setParameter($this, "sortname", $sortparameter);
			}
			if ($noq > 0)
			{
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . ilUtil::prepareFormOutput($legendquestions[$legend[$title]]) . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
				if ($noq == $noqcount)
				{
					$this->tpl->setCurrentBlock("questions_titlecol");
					$this->tpl->setVariable("TXT_TITLE", $title);
					$this->tpl->parseCurrentBlock();
				}
				$noq--;
			}
			else
			{
				$this->tpl->setCurrentBlock("questions_titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $legendquestions[$legend[$title]] . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
			}
		}
		$counter = 0;
		foreach ($eval_complete as $row)
		{
			$noq = $noqcount;
			foreach ($row["data"] as $key => $value)
			{
				if ($noq > 0)
				{
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
					if ($noq == $noqcount)
					{
						$this->tpl->setCurrentBlock("questions_datacol");
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->setVariable("TXT_DATA", $value["html"]);
						$this->tpl->parseCurrentBlock();
					}
					$noq--;
				}
				else
				{
					$this->tpl->setCurrentBlock("questions_datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("questions_row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}

		$this->tpl->setCurrentBlock("questions_output");
		$this->tpl->setVariable("TXT_QUESTIONS",  $this->lng->txt("assQuestions"));
		$this->tpl->setVariable("FORM_ACTION_RESULTS", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("export_btn");
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
		$this->tpl->setVariable("TEXT_CERTIFICATE", $this->lng->txt("exp_type_certificate"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_FILTER_USERS", $this->lng->txt("filter_users"));
		$this->tpl->setVariable("TEXT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("TEXT_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->setVariable("TEXT_PASSEDONLY", $this->lng->txt("passed_only"));
		if ($passedonly)
		{
			$this->tpl->setVariable("CHECKED_PASSEDONLY", " checked=\"checked\"");
		}
		if (strlen($filtertext) > 0)
		{
			$this->tpl->setVariable("VALUE_FILTER_USERS", " value=\"" . $filtertext . "\"");
		}
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->setVariable("SPECS", $this->getStatisticalSettingsOutput());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("PAGETITLE", $this->object->getTitle());
	}
	
/**
* Output of the pass overview for a test called from the statistics
*
* Output of the pass overview for a test called from the statistics
*
* @access public
*/
	function outStatisticsResultsOverview()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_pass_overview_statistics.html", "Modules/Test");

		$active_id = $_GET["active_id"];
		if ($this->object->getNrOfTries() == 1)
		{
			$this->ctrl->setParameter($this, "active_id", $active_id);
			$this->ctrl->setParameter($this, "pass", ilObjTest::_getResultPass($active_id));
			$this->ctrl->redirect($this, "statisticsPassDetails");
		}

		$overview = $this->getPassOverview($active_id, "iltestevaluationgui", "statisticsPassDetails");

		$this->tpl->setVariable("PASS_OVERVIEW", $overview);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_evaluation"));
		$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "evalAllUsers"));
		$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$this->tpl->setVariable("PRINT_URL", "javascript:window.print();");
		
		$result_pass = $this->object->_getResultPass($active_id);
		$result_array =& $this->object->getTestResult($active_id, $result_pass);
		$statement = $this->getFinalStatement($result_array["test"]);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		$user_data = $this->getResultsUserdata($user_id);
		$this->tpl->setVariable("USER_DATA", $user_data);
		$this->tpl->setVariable("TEXT_OVERVIEW", $this->lng->txt("tst_results_overview"));
		$this->tpl->setVariable("USER_FEEDBACK", $statement);
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Output of the pass details of an existing test pass for the test statistics
*
* Output of the pass details of an existing test pass for the test statistics
*
* @access public
*/
	function statisticsPassDetails()
	{
		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		$active_id = $_GET["active_id"];
		$pass = $_GET["pass"];
		$result_array =& $this->object->getTestResult($active_id, $pass);

		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestevaluationgui", "statisticsPassDetails");

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_pass_details_overview_statistics.html", "Modules/Test");

		if ($this->object->getNrOfTries() == 1)
		{
			$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_evaluation"));
			$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "evalAllUsers"));
		}
		else
		{
			$this->tpl->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass(get_class($this), "outStatisticsResultsOverview"));
			$this->tpl->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_overview"));
		}

		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("test_user_name");
		
		$uname = $this->object->userLookupFullName($user_id);
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));
		$this->tpl->parseCurrentBlock();

		if ($this->object->getNrOfTries() == 1)
		{
			$statement = $this->getFinalStatement($result_array["test"]);
			$this->tpl->setVariable("USER_FEEDBACK", $statement);
		}
		
		$list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, TRUE);
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("LIST_OF_ANSWERS", $list_of_answers);
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("PASS_DETAILS", $overview);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Test/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
	}
}
?>
