<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Services/Utilities/classes/class.ilFormat.php';

/**
 * Export class for tests
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author Maximilian Becker <mbecker@databay.de>
 * 
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
class ilTestExport
{
	/** @var  ilErrorHandling $err */
	var $err;			// error object
	
	/** @var  ilDB $db */
	var $db;			// database object
	
	/** @var  ILIAS $ilias */
	var $ilias;			// ilias object
	
	/** @var  ilObjTest $test_obj */
	var $test_obj;		// test object
	
	var $inst_id;		// installation id
	var $mode;
	
	/** @var ilLanguage $lng */
	private $lng;
	
	private $resultsfile;

	/**
	 * Constructor
	 */
	public function __construct(&$a_test_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias, $lng;

		$this->test_obj =& $a_test_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;
		$this->lng =& $lng;

		$this->inst_id = IL_INST_ID;

		$date = time();
		$this->export_dir = $this->test_obj->getExportDirectory();
		switch($this->mode)
		{
			case "results":
				$this->subdir = $date."__".$this->inst_id."__".
					"tst__results_".$this->test_obj->getId();
				break;
			case "aggregated":
				$this->subdir = $date."__".$this->inst_id."__".
					"test__aggregated__results_".$this->test_obj->getId();
				break;
			default:
				$this->subdir = $date."__".$this->inst_id."__".
					"tst"."_".$this->test_obj->getId();
				$this->filename = $this->subdir.".xml";
				$this->resultsfile = $date."__".$this->inst_id."__".
					"results"."_".$this->test_obj->getId().".xml";
				$this->qti_filename = $date."__".$this->inst_id."__".
					"qti"."_".$this->test_obj->getId().".xml";
				break;
		}
		$this->filename = $this->subdir.".".$this->getExtension();
	}

	function getExtension () {
		switch ($this->mode) {
			case "results":
				return "csv"; break;
			default:
			 	return "xml"; break;
		}
	}

	function getInstId()
	{
		return $this->inst_id;
	}


	/**
	*   build export file (complete zip file)
	*
	*   @access public
	*   @return
	*/
	function buildExportFile()
	{
		switch ($this->mode)
		{
			case "results":
				return $this->buildExportResultFile();
				break;
			default:
				return $this->buildExportFileXML();
				break;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportResultFile()
	{
		global $ilBench;
		global $log;

		//get Log File
		$expDir = $this->test_obj->getExportDirectory();
		
		// make_directories
		$this->test_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir);

		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Of Results");

		$data = $this->exportToCSV($deliver = FALSE);
		$file = fopen($this->export_dir."/".$this->filename, "w");
		fwrite($file, $data);
		fclose($file);

		$excelfile = $this->exportToExcel($deliver = FALSE);
		@copy($excelfile, $this->export_dir . "/" . str_replace($this->getExtension(), "xls", $this->filename));
		@unlink($excelfile);
		// end
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export of Results");

		return $this->export_dir."/".$this->filename;
	}

	/**
	* Exports the aggregated results to the Microsoft Excel file format
	*
	* @param boolean $deliver TRUE to directly deliver the file, FALSE to return the binary data
	*/
	protected function aggregatedResultsToExcel($deliver = TRUE)
	{
		$data = $this->test_obj->getAggregatedResultsData();
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$testname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->test_obj->getTitle() . '_aggregated')) . ".xls";
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
		// Creating a worksheet
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		$worksheet =& $workbook->addWorksheet(ilExcelUtils::_convert_text($this->lng->txt("tst_results_aggregated")));
		$row = 0;
		$col = 0;
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("result")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("value")), $format_title);
		$row++;
		foreach ($data["overview"] as $key => $value)
		{
			$col = 0;
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($key));
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value));
			$row++;
		}
		$row++;
		$col = 0;
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("question_id")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("question_title")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("average_reached_points")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("points")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("percentage")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("number_of_answers")), $format_title);
		$row++;
		foreach ($data["questions"] as $key => $value)
		{
			$col = 0;
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($key));
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value[0]));
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value[4]));
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value[5]));
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value[6]), $format_percent);
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($value[3]));
			$row++;
		}
		$workbook->close();
		if ($deliver)
		{
			ilUtil::deliverFile($excelfile, $testname, "application/vnd.ms-excel", false, true);
			exit;
		}
		else
		{
			return $excelfile;
		}
	}

	/**
	* Exports the aggregated results to CSV
	*
	* @param boolean $deliver TRUE to directly deliver the file, FALSE to return the data
	*/
	protected function aggregatedResultsToCSV($deliver = TRUE)
	{
		$data = $this->test_obj->getAggregatedResultsData();
		$rows = array();
		array_push($rows, array(
			$this->lng->txt("result"),
			$this->lng->txt("value")
		));
		foreach ($data["overview"] as $key => $value)
		{
			array_push($rows, array(
				$key,
				$value
			));
		}
		array_push($rows, array(
			$this->lng->txt("question_title"),
			$this->lng->txt("average_reached_points"),
			$this->lng->txt("points"),
			$this->lng->txt("percentage"),
			$this->lng->txt("number_of_answers")
		));
		foreach ($data["questions"] as $key => $value)
		{
			array_push($rows, array(
				$value[0],
				$value[4],
				$value[5],
				$value[6],
				$value[3]
			));
		}
		$csv = "";
		$separator = ";";
		foreach ($rows as $evalrow)
		{
			$csvrow =& $this->test_obj->processCSVRow($evalrow, TRUE, $separator);
			$csv .= join($csvrow, $separator) . "\n";
		}
		if ($deliver)
		{
			ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->test_obj->getTitle() . "_aggregated.csv"));
			exit;
		}
		else
		{
			return $csv;
		}
	}

	/**
	 * Exports the evaluation data to the Microsoft Excel file format
	 *
	 * @param bool    $deliver
	 * @param string  $filterby
	 * @param string  $filtertext Filter text for the user data
	 * @param boolean $passedonly TRUE if only passed user datasets should be exported, FALSE otherwise
	 *
	 * @return string
	 */
	public function exportToExcel($deliver = TRUE, $filterby = "", $filtertext = "", $passedonly = FALSE)
	{
		if (strcmp($this->mode, "aggregated") == 0) return $this->aggregatedResultsToExcel($deliver);

		require_once './Services/Excel/classes/class.ilExcelWriterAdapter.php';
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$testname = $this->test_obj->getTitle();
		switch($this->mode)
		{
				case 'results':
				$testname .= '_results';
				break;
		}
		$testname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $testname)) . ".xls";
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
		require_once './Services/Excel/classes/class.ilExcelUtils.php';
		$worksheet =& $workbook->addWorksheet(ilExcelUtils::_convert_text($this->lng->txt("tst_results")));
		$additionalFields = $this->test_obj->getEvaluationAdditionalFields();
		$row = 0;
		$col = 0;

		if ($this->test_obj->getAnonymity())
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
		if ($this->test_obj->getECTSOutput())
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

		$counter = 1;
		$data =& $this->test_obj->getCompleteEvaluationData(TRUE, $filterby, $filtertext);
		$firstrowwritten = false;
		foreach ($data->getParticipants() as $active_id => $userdata) 
		{
			$remove = FALSE;
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
				if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions())
				{
					$row++;
				}
				$col = 0;
				if ($this->test_obj->getAnonymity())
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
				if ($this->test_obj->getECTSOutput())
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
				$mark = $this->test_obj->mark_schema->getMatchingMark($pct);
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
				if ($this->test_obj->getPassScoring() == SCORE_BEST_PASS)
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
					$finishdate = $this->test_obj->getPassFinishDate($active_id, $pass);
					if ($finishdate > 0)
					{
						if ($pass > 0)
						{
							$row++;
							if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions())
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
								if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions())
								{
									$worksheet->write($row-1, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
								}
								else
								{
									if ($pass == 0 && !$firstrowwritten)
									{
										$worksheet->write(0, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
									}
								}
								$col++;
							}
							$firstrowwritten = true;
						}
					}
				}
				$counter++;
			}
		}
		if ($this->test_obj->getExportSettingsSingleChoiceShort() && !$this->test_obj->isRandomTest() && $this->test_obj->hasSingleChoiceQuestions())
		{
			// special tab for single choice tests
			$titles =& $this->test_obj->getQuestionTitlesAndIndexes();
			$positions = array();
			$pos = 0;
			$row = 0;
			foreach ($titles as $id => $title)
			{
				$positions[$id] = $pos;
				$pos++;
			}
			$usernames = array();
			$participantcount = count($data->getParticipants());
			$allusersheet = false;
			$pages = 0;
			$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users"));

			$col = 0;
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('name')), $format_title);
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('login')), $format_title);
			if (count($additionalFields))
			{
				foreach ($additionalFields as $fieldname)
				{
					if (strcmp($fieldname, "matriculation") == 0)
					{
						$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('matriculation')), $format_title);
					}
				}
			}
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('test')), $format_title);
			foreach ($titles as $title)
			{
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($title), $format_title);
			}
			$row++;

			foreach ($data->getParticipants() as $active_id => $userdata) 
			{
				$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
				if (array_key_exists($username, $usernames))
				{
					$usernames[$username]++;
					$username .= " ($i)";
				}
				else
				{
					$usernames[$username] = 1;
				}
				$col = 0;
				$resultsheet->write($row, $col++, $username);
				$resultsheet->write($row, $col++, $userdata->getLogin());
				if (count($additionalFields))
				{
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "matriculation") == 0)
						{
							if (strlen($userfields[$fieldname]))
							{
								$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
							}
							else
							{
								$col++;
							}
						}
					}
				}
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->test_obj->getTitle()));
				$pass = $userdata->getScoredPass();
				if (is_object($userdata) && is_array($userdata->getQuestions($pass)))
				{
					foreach ($userdata->getQuestions($pass) as $question)
					{
						$objQuestion =& $this->test_obj->_instanciateQuestion($question["aid"]);
						if (is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0)
						{
							$solution = $objQuestion->getSolutionValues($active_id, $pass);
							$pos = $positions[$question["aid"]];
							$selectedanswer = "x";
							foreach ($objQuestion->getAnswers() as $id => $answer)
							{
								if (strlen($solution[0]["value1"]) && $id == $solution[0]["value1"])
								{
									$selectedanswer = $answer->getAnswertext();
								}
							}
							$resultsheet->write($row, $col+$pos, ilExcelUtils::_convert_text($selectedanswer));
						}
					}
				}
				$row++;
			}
			if ($this->test_obj->isSingleChoiceTestWithoutShuffle())
			{
				// special tab for single choice tests without shuffle option
				$pos = 0;
				$row = 0;
				$usernames = array();
				$allusersheet = false;
				$pages = 0;
				$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users") . " (2)");

				$col = 0;
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('name')), $format_title);
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('login')), $format_title);
				if (count($additionalFields))
				{
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "matriculation") == 0)
						{
							$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('matriculation')), $format_title);
						}
					}
				}
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('test')), $format_title);
				foreach ($titles as $title)
				{
					$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($title), $format_title);
				}
				$row++;

				foreach ($data->getParticipants() as $active_id => $userdata) 
				{
					$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
					if (array_key_exists($username, $usernames))
					{
						$usernames[$username]++;
						$username .= " ($i)";
					}
					else
					{
						$usernames[$username] = 1;
					}
					$col = 0;
					$resultsheet->write($row, $col++, $username);
					$resultsheet->write($row, $col++, $userdata->getLogin());
					if (count($additionalFields))
					{
						$userfields = ilObjUser::_lookupFields($userdata->getUserID());
						foreach ($additionalFields as $fieldname)
						{
							if (strcmp($fieldname, "matriculation") == 0)
							{
								if (strlen($userfields[$fieldname]))
								{
									$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
								}
								else
								{
									$col++;
								}
							}
						}
					}
					$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->test_obj->getTitle()));
					$pass = $userdata->getScoredPass();
					if (is_object($userdata) && is_array($userdata->getQuestions($pass)))
					{
						foreach ($userdata->getQuestions($pass) as $question)
						{
							$objQuestion =& $this->test_obj->_instanciateQuestion($question["aid"]);
							if (is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0)
							{
								$solution = $objQuestion->getSolutionValues($active_id, $pass);
								$pos = $positions[$question["aid"]];
								$selectedanswer = chr(65+$solution[0]["value1"]);
								$resultsheet->write($row, $col+$pos, ilExcelUtils::_convert_text($selectedanswer));
							}
						}
					}
					$row++;
				}
			}
		}
		else
		{
			// test participant result export
			$usernames = array();
			$participantcount = count($data->getParticipants());
			$allusersheet = false;
			$pages = 0;
			$i = 0;
			foreach ($data->getParticipants() as $active_id => $userdata) 
			{
				$i++;
				
				$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
				if (array_key_exists($username, $usernames))
				{
					$usernames[$username]++;
					$username .= " ($i)";
				}
				else
				{
					$usernames[$username] = 1;
				}
				if ($participantcount > 250) {
					if (!$allusersheet || ($pages-1) < floor($row / 64000)) {
						$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users") . (($pages > 0) ? " (".($pages+1).")" : ""));
						$allusersheet = true;
						$row = 0;
						$pages++;
					}
				} else {
					$resultsheet =& $workbook->addWorksheet(utf8_decode($username));
				}
				if (method_exists($resultsheet, "writeString"))
				{
					$pass = $userdata->getScoredPass();
					$row = ($allusersheet) ? $row : 0;
					$resultsheet->writeString($row, 0, ilExcelUtils::_convert_text(sprintf($this->lng->txt("tst_result_user_name_pass"), $pass+1, $userdata->getName())), $format_bold);
					$row += 2;
					if (is_object($userdata) && is_array($userdata->getQuestions($pass)))
					{
						foreach ($userdata->getQuestions($pass) as $question)
						{
							require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
							$question = assQuestion::_instanciateQuestion($question["id"]);
							if (is_object($question))
							{
								$row = $question->setExportDetailsXLS($resultsheet, $row, $active_id, $pass, $format_title, $format_bold);
							}
						}
					}
				}
			}
		}
		$workbook->close();
		if ($deliver)
		{
			ilUtil::deliverFile($excelfile, $testname, "application/vnd.ms-excel", false, true);
			exit;
		}
		else
		{
			return $excelfile;
		}
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
	function exportToCSV($deliver = TRUE, $filterby = "", $filtertext = "", $passedonly = FALSE)
	{
		global $ilLog;
		
		if (strcmp($this->mode, "aggregated") == 0) return $this->aggregatedResultsToCSV($deliver);

		$rows = array();
		$datarow = array();
		$col = 1;
		if ($this->test_obj->getAnonymity())
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
		$additionalFields = $this->test_obj->getEvaluationAdditionalFields();
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
		if ($this->test_obj->getECTSOutput())
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

		$data =& $this->test_obj->getCompleteEvaluationData(TRUE, $filterby, $filtertext);
		$headerrow = $datarow;
		$counter = 1;
		foreach ($data->getParticipants() as $active_id => $userdata) 
		{
			$datarow = $headerrow;
			$remove = FALSE;
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
				if ($this->test_obj->getAnonymity())
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
				if ($this->test_obj->getECTSOutput())
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
				
				$fv = $data->getParticipant($active_id)->getFirstVisit();
				$lv = $data->getParticipant($active_id)->getLastVisit();
				foreach(array($fv, $lv) as $ts)
				{
					if($ts)
					{
						$visit = ilFormat::formatDate(date('Y-m-d H:i:s', $ts), "datetime", false, false);
						array_push($datarow2, $visit);
					}
					else
					{
						array_push($datarow2, "");
					}
				}

				$median = $data->getStatistics()->getStatistics()->median();
				$pct = $data->getParticipant($active_id)->getMaxpoints() ? $median / $data->getParticipant($active_id)->getMaxpoints() * 100.0 : 0;
				$mark = $this->test_obj->mark_schema->getMatchingMark($pct);
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
				if ($this->test_obj->getPassScoring() == SCORE_BEST_PASS)
				{
					array_push($datarow2, $data->getParticipant($active_id)->getBestPass() + 1);
				}
				else
				{
					array_push($datarow2, $data->getParticipant($active_id)->getLastPass() + 1);
				}
				for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++)
				{
					$finishdate = $this->test_obj->getPassFinishDate($active_id, $pass);
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
						if (is_object($data->getParticipant($active_id)) && is_array($data->getParticipant($active_id)->getQuestions($pass)))
						{
							foreach ($data->getParticipant($active_id)->getQuestions($pass) as $question)
							{
								$question_data = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
								array_push($datarow2, $question_data["reached"]);
								array_push($datarow, preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"])));
							}
						}
						if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions() || ($counter == 1 && $pass == 0))
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
			$csvrow =& $this->test_obj->processCSVRow($evalrow, TRUE, $separator);
			$csv .= join($csvrow, $separator) . "\n";
		}
		if ($deliver)
		{
			ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->test_obj->getTitle() . "_results.csv"));
			exit;
		}
		else
		{
			return $csv;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportFileXML()
	{
		global $ilBench;

		$ilBench->start("TestExport", "buildExportFile");

		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Test ".
			$this->test_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		$this->test_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->test_obj->getExportDirectory();
		include_once "./Services/Logging/classes/class.ilLog.php";
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// write qti file
		$qti_file = fopen($this->export_dir."/".$this->subdir."/".$this->qti_filename, "w");
		fwrite($qti_file, $this->test_obj->toXML());
		fclose($qti_file);

		// get xml content
		$ilBench->start("TestExport", "buildExportFile_getXML");
		$this->test_obj->exportPagesXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("TestExport", "buildExportFile_getXML");

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$ilBench->start("TestExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("TestExport", "buildExportFile_dumpToFile");

		if (@file_exists("./Modules/Test/classes/class.ilTestResultsToXML.php"))
		{
			// dump results xml document to file
			include_once "./Modules/Test/classes/class.ilTestResultsToXML.php";
			$resultwriter = new ilTestResultsToXML($this->test_obj->getTestId(), $this->test_obj->getAnonymity());
			$ilBench->start("TestExport", "buildExportFile_results");
			$resultwriter->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->resultsfile, false);
			$ilBench->stop("TestExport", "buildExportFile_results");
		}

			// add media objects which were added with tiny mce
		$ilBench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
		$this->exportXHTMLMediaObjects($this->export_dir."/".$this->subdir);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

		// zip the file
		$ilBench->start("TestExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("TestExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("TestExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	function exportXHTMLMediaObjects($a_export_dir)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->test_obj->getId());
		foreach ($mobs as $mob)
		{
			if (ilObjMediaObject::_exists($mob))
			{
				$mob_obj =& new ilObjMediaObject($mob);
				$mob_obj->exportFiles($a_export_dir);
				unset($mob_obj);
			}
		}
		foreach ($this->test_obj->questions as $question_id)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
			foreach ($mobs as $mob)
			{
				if (ilObjMediaObject::_exists($mob))
				{
					$mob_obj =& new ilObjMediaObject($mob);
					$mob_obj->exportFiles($a_export_dir);
					unset($mob_obj);
				}
			}
		}
	}

}

?>
