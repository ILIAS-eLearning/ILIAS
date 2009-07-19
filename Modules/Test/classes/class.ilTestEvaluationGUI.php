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

	public function filterEvaluation()
	{
		include_once "./Modules/Test/classes/class.ilEvaluationAllTableGUI.php";
		$table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation', array());
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, "outEvaluation");
	}

	/**
	* Creates the evaluation output for the test
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

		$additionalFields = $this->object->getEvaluationAdditionalFields();
		if ($this->object->ects_output)
		{
			array_push($additionalFields, 'ects_grade');
		}
		include_once "./Modules/Test/classes/class.ilEvaluationAllTableGUI.php";
		$table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation', $additionalFields);
		$data = array();
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				switch ($item->getPostVar())
				{
					case 'group':
					case 'name':
					case 'course':
						$arrFilter[$item->getPostVar()] = $item->getValue();
						break;
					case 'passed_only':
						$passedonly = $item->getChecked();
						break;
				}
			}
		}
		include_once "./Modules/Test/classes/class.ilTestEvaluationData.php";
		$eval = new ilTestEvaluationData($this->object);
		$eval->setFilterArray($arrFilter);
		$foundParticipants =& $eval->getParticipants();
		$counter = 1;
		if (count($foundParticipants) > 0)
		{
			foreach ($foundParticipants as $active_id => $userdata)
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
						$evaluationrow['name'] = $fullname;
						$evaluationrow['login'] = '';
					}
					else
					{
						$evaluationrow['name'] = $userdata->getName();
						if (strlen($userdata->getLogin()))
						{
							$evaluationrow['login'] = "[" . $userdata->getLogin() . "]";
						}
						else
						{
							$evaluationrow['login'] = '';
						}
					}

					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($userfields as $key => $value)
					{
						$evaluationrow[$key] = strlen($value) ? $value : ' ';
					}
					$evaluationrow['reached'] = $userdata->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $userdata->getMaxpoints();
					$percentage = $userdata->getReachedPointsInPercent();
					$mark = $this->object->getMarkSchema()->getMatchingMark($percentage);
					if (is_object($mark))
					{
						$evaluationrow['mark'] = $mark->getShortName();
					}
					if ($this->object->ects_output)
					{
						$ects_mark = $this->object->getECTSGrade($userdata->getReached(), $userdata->getMaxPoints());
						$evaluationrow['ects_grade'] = $ects_mark;
					}
					$evaluationrow['answered'] = $userdata->getQuestionsWorkedThrough() . " " . strtolower($this->lng->txt("of")) . " " . $userdata->getNumberOfQuestions() . " (" . sprintf("%2.2f", $userdata->getQuestionsWorkedThroughInPercent()) . " %" . ")";
					$time_seconds = $userdata->getTimeOfWork();
					$time_hours    = floor($time_seconds/3600);
					$time_seconds -= $time_hours   * 3600;
					$time_minutes  = floor($time_seconds/60);
					$time_seconds -= $time_minutes * 60;
					$evaluationrow['working_time'] = sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds);
					$this->ctrl->setParameter($this, "active_id", $active_id);
					$href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
					$detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
					$evaluationrow['details'] = "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>";
					$counter++;
					array_push($data, $evaluationrow);
				}
			}
		}

		$table_gui->setData($data);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_evaluation.html", "Modules/Test");
		$this->tpl->setVariable('EVALUATION_DATA', $table_gui->getHTML());	
		if (count($foundParticipants) > 0)
		{
			$template = new ilTemplate("tpl.il_as_tst_evaluation_export.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
			if (!$this->object->getAnonymity())
			{
				include_once "./Services/Certificate/classes/class.ilCertificate.php";
				include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
				if (ilCertificate::_isComplete(new ilTestCertificateAdapter($this->object)))
				{
					$template->setVariable("TEXT_CERTIFICATE", $this->lng->txt("exp_type_certificate"));
				}
			}
			$template->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
			$template->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
			$template->setVariable("CMD_EXPORT", "exportEvaluation");
			$template->setVariable("BTN_EXPORT", $this->lng->txt("export"));
			$template->setVariable("BTN_PRINT", $this->lng->txt("print"));
			$template->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
			$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "exportEvaluation"));
			$exportoutput = $template->get();
			$this->tpl->setVariable("EVALUATION_EXPORT", $exportoutput);
		}

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
		global $ilAccess;
		
		if ((!$ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) && (!$ilAccess->checkAccess("write", "", $this->ref_id)))
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), TRUE);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

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
	* Creates a ZIP file containing all file uploads for a given question in a test
	*
	* @access public
	*/
	function exportFileUploadsForAllParticipants()
	{
		$question_object =& ilObjTest::_instanciateQuestion($_GET["qid"]);
		$download = "";
		if (method_exists($question_object, "getFileUploadZIPFile"))
		{
			$question_object->getFileUploadZIPFile($this->object->getTestId());
		}
		else
		{
			$this->ctrl->redirect($this, "singleResults");
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", "Modules/Test");
		$eval =& $this->object->getCompleteEvaluationData();
		$data = array();
		$foundParticipants =& $eval->getParticipants();
		if (count($foundParticipants)) 
		{
			$template = new ilTemplate("tpl.il_as_tst_evaluation_export.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
			$template->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
			$template->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
			$template->setVariable("CMD_EXPORT", "exportAggregatedResults");
			$template->setVariable("BTN_EXPORT", $this->lng->txt("export"));
			$template->setVariable("BTN_PRINT", $this->lng->txt("print"));
			$template->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
			$template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "exportAggregatedResults"));
			$exportoutput = $template->get();
			$this->tpl->setVariable("EVALUATION_EXPORT", $exportoutput);

			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_persons"),
				'value'  => count($foundParticipants)
			));
			$total_finished = $this->object->evalTotalFinished();
			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_finished"),
				'value'  => $total_finished
			));
			$average_time = $this->object->evalTotalStartedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_finished_average_time"),
				'value'  => sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds)
			));
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
			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_passed"),
				'value'  => $total_passed
			));
			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_passed_average_points"),
				'value'  => sprintf("%2.2f", $average_passed_reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $average_passed_max)
			));
			$average_time = $average_passed_time;
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			array_push($data, array(
				'result' => $this->lng->txt("tst_eval_total_passed_average_time"),
				'value'  => sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds)
			));
		} 

		include_once "./Modules/Test/classes/tables/class.ilTestAggregatedResultsTableGUI.php";
		$table_gui = new ilTestAggregatedResultsTableGUI($this, 'eval_a');
		$table_gui->setData($data);
		$this->tpl->setVariable('AGGREGATED_RESULTS', $table_gui->getHTML());	
		
		$rows = array();
		foreach ($eval->getQuestionTitles() as $question_id => $question_title)
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
						'title' => $question_title, 
						'points' => sprintf("%.2f", $answered ? $reached / $answered : 0) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $answered ? $max / $answered : 0),
						'percentage' => sprintf("%.2f", $percent) . "%",
						'answers' => $answered
				)
			);
		}
		include_once "./Modules/Test/classes/tables/class.ilTestAverageReachedPointsTableGUI.php";
		$table_gui = new ilTestAverageReachedPointsTableGUI($this, 'eval_a');
		$table_gui->setData($rows);
		$this->tpl->setVariable('TBL_AVG_REACHED', $table_gui->getHTML());	
	}

/**
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
				$this->ctrl->redirect($this, "exportCertificate");
				break;
		}
	}

	/**
	* Exports the aggregated results
	*
	* @access public
	*/
	function exportAggregatedResults()
	{
		switch ($_POST["export_type"])
		{
			case "excel":
				include_once "./Modules/Test/classes/class.ilTestExport.php";
				$exportObj = new ilTestExport($this->object, "aggregated");
				$exportObj->exportToExcel($deliver = TRUE);
				break;
			case "csv":
				include_once "./Modules/Test/classes/class.ilTestExport.php";
				$exportObj = new ilTestExport($this->object, "aggregated");
				$exportObj->exportToCSV($deliver = TRUE);
				break;
		}
	}

	/**
	* Exports the user results as PDF certificates using
	* XSL-FO via XML:RPC calls
	*
	* @access public
	*/
	public function exportCertificate()
	{
		global $ilUser;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$certificate = new ilCertificate(new ilTestCertificateAdapter($this->object));
		$archive_dir = $certificate->createArchiveDirectory();
		$total_users = array();
		$total_users =& $this->object->evalTotalPersonsArray();
		if (count($total_users))
		{
			foreach ($total_users as $active_id => $name)
			{
				$user_id = $this->object->_getUserIdFromActiveId($active_id);
				$pdf = $certificate->outCertificate(
					array(
						"active_id" => $active_id,
						"userfilter" => $userfilter,
						"passedonly" => $passedonly
					),
					FALSE
				);
				if (strlen($pdf))
				{
					$certificate->addPDFtoArchiveDirectory($pdf, $archive_dir, $user_id . "_" . str_replace(" ", "_", ilUtil::getASCIIFilename($name)) . ".pdf");
				}
			}
			$zipArchive = $certificate->zipCertificatesInArchiveDirectory($archive_dir, TRUE);
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
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), TRUE);
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
		global $ilias;

		$this->ctrl->saveParameter($this, "pass");
		$this->ctrl->saveParameter($this, "active_id");
		$active_id = $_GET["active_id"];
		$pass = $_GET["pass"];
		$result_array =& $this->object->getTestResult($active_id, $pass);

		$overview = $this->getPassDetailsOverview($result_array, $active_id, $pass, "iltestevaluationgui", "outParticipantsPassDetails");
		$user_data = $this->getResultsUserdata($active_id, FALSE);

		$user_id = $this->object->_getUserIdFromActiveId($active_id);

		$template = new ilTemplate("tpl.il_as_tst_pass_details_overview_participants.html", TRUE, TRUE, "Modules/Test");

		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "outParticipantsPassDetails"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}

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

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}

		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$this->object->deliverPDFfromHTML($template->get());
		}
		else
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
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
		global $ilias;
		
		$template = new ilTemplate("tpl.il_as_tst_pass_overview_participants.html", TRUE, TRUE, "Modules/Test");

		$active_id = $_GET["active_id"];
		if ($this->object->getNrOfTries() == 1)
		{
			$this->ctrl->setParameter($this, "active_id", $active_id);
			$this->ctrl->setParameter($this, "pass", ilObjTest::_getResultPass($active_id));
			$this->ctrl->redirect($this, "outParticipantsPassDetails");
		}

		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "outParticipantsResultsOverview"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
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

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}

		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
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

		if (!$this->object->canShowTestResults($ilUser->getId())) $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
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
				$templatehead->setVariable("CERTIFICATE_URL", $this->ctrl->getLinkTarget($this, "outCertificate"));
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
			$this->object->deliverPDFfromHTML($template->get(), sprintf($this->lng->txt("tst_result_user_name"), $uname));
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

		$template = new ilTemplate("tpl.il_as_tst_info_list_of_answers.html", TRUE, TRUE, "Modules/Test");

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
			$template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_passes"));
			$template->setVariable("PASS_OVERVIEW", $overview);
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
			$template->setVariable("PASS_DETAILS", $answers);
		}
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BACK_TEXT", $this->lng->txt("tst_results_back_introduction"));
		$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "infoScreen"));
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		$user_data = $this->getResultsUserdata($active_id, TRUE);
		$template->setVariable("USER_DATA", $user_data);
		$template->setVariable("TEXT_LIST_OF_ANSWERS", $this->lng->txt("tst_list_of_answers"));
		if (strlen($signature))
		{
			$template->setVariable("SIGNATURE", $signature);
		}
		$this->tpl->setVariable("ADM_CONTENT", $template->get());

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
			ilUtil::sendInfo($this->lng->txt("tst_no_evaluation_data"));
			return;
		}
		else
		{
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
				$question_object =& ilObjTest::_instanciateQuestion($question_id);
				$download = "";
				if (method_exists($question_object, "hasFileUploads"))
				{
					if ($question_object->hasFileUploads($this->object->getTestId()))
					{
						$download = "<a href=\"" . $this->ctrl->getLinkTarget($this, "exportFileUploadsForAllParticipants"). "\">" . $this->lng->txt("download") . "</a>";
					}
				}
				array_push($rows, 
					array(
							$question_title, 
							$answered,
							"<a href=\"" . $this->ctrl->getLinkTarget($this, "exportQuestionForAllParticipants"). "\">" . $this->lng->txt("pdf_export") . "</a>",
							$download
					)
				);
			}
			if (count($rows))
			{
				include_once("./Modules/Test/classes/tables/class.ilResultsByQuestionTableGUI.php");
				$table_gui = new ilResultsByQuestionTableGUI($this, "singleResults");

				$table_gui->setTitle($this->lng->txt("tst_answered_questions_test"));
				$table_gui->setData($rows);

				$this->tpl->setVariable("TBL_SINGLE_ANSWERS", $table_gui->getHTML());
			}
			else
			{
				$this->tpl->setVariable("TBL_SINGLE_ANSWERS", $this->lng->txt("adm_no_special_users"));
			}
		}
	}

	/**
	* Output of a test certificate
	*/
	public function outCertificate()
	{
		global $ilUser;

		$active_id = $_GET["active_id"];
		$counted_pass = ilObjTest::_getResultPass($active_id);
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$certificate = new ilCertificate(new ilTestCertificateAdapter($this->object));
		$certificate->outCertificate(array("active_id" => $active_id, "pass" => $counted_pass));
	}
	
}
?>
