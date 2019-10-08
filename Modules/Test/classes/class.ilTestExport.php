<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
/**
 * Export class for tests
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 * 
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
abstract class ilTestExport
{
	/** @var  ilErrorHandling $err */
	var $err;			// error object
	
	/** @var  ilDBInterface $db */
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
	
	protected $resultExportingEnabledForTestExport = false;
	
	/**
	 * @var ilTestParticipantList
	 */
	protected $forcedAccessFilteredParticipantList = null;

	/**
	 * Constructor
	 */
	public function __construct(&$a_test_obj, $a_mode = "xml")
	{
		global $DIC;
		$ilErr = $DIC['ilErr'];
		$ilDB = $DIC['ilDB'];
		$ilias = $DIC['ilias'];
		$lng = $DIC['lng'];

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

	/**
	 * @return boolean
	 */
	public function isResultExportingEnabledForTestExport()
	{
		return $this->resultExportingEnabledForTestExport;
	}

	/**
	 * @param boolean $resultExprtingEnabledForTestExport
	 */
	public function setResultExportingEnabledForTestExport($resultExprtingEnabledForTestExport)
	{
		$this->resultExportingEnabledForTestExport = $resultExprtingEnabledForTestExport;
	}
	
	/**
	 * @return ilTestParticipantList
	 */
	public function getForcedAccessFilteredParticipantList()
	{
		return $this->forcedAccessFilteredParticipantList;
	}
	
	/**
	 * @param ilTestParticipantList $forcedAccessFilteredParticipantList
	 */
	public function setForcedAccessFilteredParticipantList(ilTestParticipantList $forcedAccessFilteredParticipantList)
	{
		$this->forcedAccessFilteredParticipantList = $forcedAccessFilteredParticipantList;
	}
	
	/**
	 * @return ilTestParticipantList
	 */
	public function getAccessFilteredParticipantList()
	{
		if( $this->getForcedAccessFilteredParticipantList() instanceof ilTestParticipantList )
		{
			return $this->getForcedAccessFilteredParticipantList();
		}
		
		return $this->test_obj->buildStatisticsAccessFilteredParticipantList();
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
		global $DIC;
		$ilBench = $DIC['ilBench'];
		$log = $DIC['log'];

		//get Log File
		$expDir = $this->test_obj->getExportDirectory();
		
		// make_directories
		$this->test_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir);

		include_once './Services/Logging/classes/class.ilLog.php';
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Of Results");

		$data = $this->exportToCSV($deliver = FALSE);
		$file = fopen($this->export_dir."/".$this->filename, "w");
		fwrite($file, $data);
		fclose($file);

		$excelfile = $this->exportToExcel($deliver = FALSE);
		@copy($excelfile, $this->export_dir . "/" . str_replace($this->getExtension(), "xlsx", $this->filename));
		@unlink($excelfile);
		// end
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export of Results");

		return $this->export_dir."/".$this->filename;
	}

	/**
	 * Exports the aggregated results to the Microsoft Excel file format
	 * @param boolean $deliver TRUE to directly deliver the file, FALSE to return the binary data
	 * @return string
	 */
	protected function aggregatedResultsToExcel($deliver = TRUE)
	{
		$data = $this->test_obj->getAggregatedResultsData();

		require_once 'Modules/TestQuestionPool/classes/class.ilAssExcelFormatHelper.php';
		$worksheet = new ilAssExcelFormatHelper();
		$worksheet->addSheet($this->lng->txt('tst_results_aggregated'));

		$row = 1;
		$col = 0;
		$worksheet->setCell($row, $col++, $this->lng->txt('result'));
		$worksheet->setCell($row, $col++, $this->lng->txt('value'));

		$worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

		$row++;
		foreach($data['overview'] as $key => $value)
		{
			$col = 0;
			$worksheet->setCell($row, $col++, $key);
			$worksheet->setCell($row, $col++, $value);
			$row++;
		}

		$row++;
		$col = 0;

		$worksheet->setCell($row, $col++, $this->lng->txt('question_id'));
		$worksheet->setCell($row, $col++, $this->lng->txt('question_title'));
		$worksheet->setCell($row, $col++, $this->lng->txt('average_reached_points'));
		$worksheet->setCell($row, $col++, $this->lng->txt('points'));
		$worksheet->setCell($row, $col++, $this->lng->txt('percentage'));
		$worksheet->setCell($row, $col++, $this->lng->txt('number_of_answers'));

		$worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

		$row++;
		foreach($data['questions'] as $key => $value)
		{
			$col = 0;
			$worksheet->setCell($row, $col++, $key);
			$worksheet->setCell($row, $col++, $value[0]);
			$worksheet->setCell($row, $col++, $value[4]);
			$worksheet->setCell($row, $col++, $value[5]);
			$worksheet->setCell($row, $col++, $value[6]);
			$worksheet->setCell($row, $col++, $value[3]);
			$row++;
		}

		if($deliver)
		{
			$worksheet->sendToClient(
				ilUtil::getASCIIFilename(preg_replace("/\s/", '_', $this->test_obj->getTitle() . '_aggregated')) . '.xlsx'
			);
		}
		else
		{
			$excelfile = ilUtil::ilTempnam();
			$worksheet->writeToFile($excelfile);
			return $excelfile . '.xlsx';
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
			$this->lng->txt("question_id"),
			$this->lng->txt("question_title"),
			$this->lng->txt("average_reached_points"),
			$this->lng->txt("points"),
			$this->lng->txt("percentage"),
			$this->lng->txt("number_of_answers")
		));
		foreach ($data["questions"] as $key => $value)
		{
			array_push($rows, array(
				$key,
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
		$this->test_obj->setAccessFilteredParticipantList( $this->getAccessFilteredParticipantList() );
		
		if (strcmp($this->mode, "aggregated") == 0) return $this->aggregatedResultsToExcel($deliver);

		require_once 'Modules/TestQuestionPool/classes/class.ilAssExcelFormatHelper.php';

		$worksheet = new ilAssExcelFormatHelper();
		$worksheet->addSheet($this->lng->txt('tst_results'));

		$additionalFields = $this->test_obj->getEvaluationAdditionalFields();

		$row = 1;
		$col = 0;

		if($this->test_obj->getAnonymity())
		{
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('counter'));
		}
		else
		{
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('name'));
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('login'));
		}

		if(count($additionalFields))
		{
			foreach($additionalFields as $fieldname)
			{
				$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt($fieldname));
			}
		}

		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_resultspoints'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('maximum_points'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_resultsmarks'));

		if($this->test_obj->getECTSOutput())
		{
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('ects_grade'));
		}

		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_qworkedthrough'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_qmax'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_pworkedthrough'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_timeofwork'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_atimeofwork'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_firstvisit'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_lastvisit'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_mark_median'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_rank_participant'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_rank_median'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_total_participants'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('tst_stat_result_median'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('scored_pass'));
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('pass'));

		$worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

		$counter = 1;
		$data = $this->test_obj->getCompleteEvaluationData(TRUE, $filterby, $filtertext);
		$firstrowwritten = false;
		foreach($data->getParticipants() as $active_id => $userdata) 
		{
			if($passedonly && $data->getParticipant($active_id)->getPassed() == FALSE)
			{
				continue;
			}
			
			$row++;
			$col = 0;
			
			// each participant gets an own row for question column headers
			if($this->test_obj->isRandomTest())
			{
				$row++;
			}

			if($this->test_obj->getAnonymity())
			{
				$worksheet->setCell($row, $col++, $counter);
			}
			else
			{
				$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getName());
				$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getLogin());
			}

			if(count($additionalFields))
			{
				$userfields = ilObjUser::_lookupFields($userdata->getUserId());
				foreach ($additionalFields as $fieldname)
				{
					if(strcmp($fieldname, 'gender') == 0)
					{
						$worksheet->setCell($row, $col++, $this->lng->txt('gender_' . $userfields[$fieldname]));
					}
					else
					{
						$worksheet->setCell($row, $col++, $userfields[$fieldname]);
					}
				}
			}

			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getReached());
			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getMaxpoints());
			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getMark());

			if($this->test_obj->getECTSOutput())
			{
				$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getECTSMark());
			}

			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getQuestionsWorkedThrough());
			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getNumberOfQuestions());
			$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent() . '%');

			$time = $data->getParticipant($active_id)->getTimeOfWork();
			$time_seconds = $time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$worksheet->setCell($row, $col++, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$time = $data->getParticipant($active_id)->getQuestionsWorkedThrough() ? $data->getParticipant($active_id)->getTimeOfWork() / $data->getParticipant($active_id)->getQuestionsWorkedThrough() : 0;
			$time_seconds = $time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$worksheet->setCell($row, $col++, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$worksheet->setCell($row, $col++, new ilDateTime($data->getParticipant($active_id)->getFirstVisit(), IL_CAL_UNIX));
			$worksheet->setCell($row, $col++, new ilDateTime($data->getParticipant($active_id)->getLastVisit(), IL_CAL_UNIX));

			$median = $data->getStatistics()->getStatistics()->median();
			$pct = $data->getParticipant($active_id)->getMaxpoints() ? $median / $data->getParticipant($active_id)->getMaxpoints() * 100.0 : 0;
			$mark = $this->test_obj->mark_schema->getMatchingMark($pct);
			$mark_short_name = "";

			if(is_object($mark))
			{
				$mark_short_name = $mark->getShortName();
			}

			$worksheet->setCell($row, $col++, $mark_short_name);
			$worksheet->setCell($row, $col++, $data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached()));
			$worksheet->setCell($row, $col++, $data->getStatistics()->getStatistics()->rank_median());
			$worksheet->setCell($row, $col++, $data->getStatistics()->getStatistics()->count());
			$worksheet->setCell($row, $col++, $median);

			if($this->test_obj->getPassScoring() == SCORE_BEST_PASS)
			{
				$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getBestPass() + 1);
			}
			else
			{
				$worksheet->setCell($row, $col++, $data->getParticipant($active_id)->getLastPass() + 1);
			}

			$startcol = $col;

			for($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++)
			{
				$col = $startcol;
				$finishdate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);
				if($finishdate > 0)
				{
					if ($pass > 0)
					{
						$row++;
						if ($this->test_obj->isRandomTest())
						{
							$row++;
						}
					}
					$worksheet->setCell($row, $col++, $pass + 1);
					if(is_object($data->getParticipant($active_id)) && is_array($data->getParticipant($active_id)->getQuestions($pass)))
					{
						$evaluatedQuestions = $data->getParticipant($active_id)->getQuestions($pass);
						
						if( $this->test_obj->getShuffleQuestions() )
						{
							// reorder questions according to general fixed sequence,
							// so participant rows can share single questions header
							$questions = array();
							foreach($this->test_obj->getQuestions() as $qId)
							{
								foreach($evaluatedQuestions as $evaledQst)
								{
									if( $evaledQst['id'] != $qId )
									{
										continue;
									}
									
									$questions[] = $evaledQst;
								}
							}
						}
						else
						{
							$questions = $evaluatedQuestions;
						}
						
						foreach($questions as $question)
						{
							$question_data = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
							$worksheet->setCell($row, $col, $question_data["reached"]);
							if($this->test_obj->isRandomTest())
							{
								// random test requires question headers for every participant
								// and we allready skipped a row for that reason ( --> row - 1)
								$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . ($row - 1),  preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"])));
							}
							else
							{
								if($pass == 0 && !$firstrowwritten)
								{
									$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . 1, $data->getQuestionTitle($question["id"]));
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

		if($this->test_obj->getExportSettingsSingleChoiceShort() && !$this->test_obj->isRandomTest() && $this->test_obj->hasSingleChoiceQuestions())
		{
			// special tab for single choice tests
			$titles = $this->test_obj->getQuestionTitlesAndIndexes();
			$positions = array();
			$pos = 0;
			$row = 1;
			foreach($titles as $id => $title)
			{
				$positions[$id] = $pos;
				$pos++;
			}

			$usernames = array();
			$participantcount = count($data->getParticipants());
			$allusersheet = false;
			$pages = 0;

			$worksheet->addSheet($this->lng->txt('eval_all_users'));

			$col = 0;
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt('name'));
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('login'));
			if(count($additionalFields))
			{
				foreach($additionalFields as $fieldname)
				{
					if(strcmp($fieldname, "matriculation") == 0)
					{
						$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('matriculation'));
					}
				}
			}
			$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('test'));
			foreach($titles as $title)
			{
				$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $title);
			}
			$worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

			$row++;
			foreach($data->getParticipants() as $active_id => $userdata) 
			{
				$username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
				if (array_key_exists($username, $usernames))
				{
					$usernames[$username]++;
					$username .= " ($usernames[$username])";
				}
				else
				{
					$usernames[$username] = 1;
				}
				$col = 0;
				$worksheet->setCell($row, $col++, $username);
				$worksheet->setCell($row, $col++, $userdata->getLogin());
				if (count($additionalFields))
				{
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "matriculation") == 0)
						{
							if (strlen($userfields[$fieldname]))
							{
								$worksheet->setCell($row, $col++, $userfields[$fieldname]);
							}
							else
							{
								$col++;
							}
						}
					}
				}
				$worksheet->setCell($row, $col++, $this->test_obj->getTitle());
				$pass = $userdata->getScoredPass();
				if(is_object($userdata) && is_array($userdata->getQuestions($pass)))
				{
					foreach($userdata->getQuestions($pass) as $question)
					{
						$objQuestion = assQuestion::_instantiateQuestion($question["id"]);
						if(is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0)
						{
							$solution = $objQuestion->getSolutionValues($active_id, $pass);
							$pos = $positions[$question["id"]];
							$selectedanswer = "x";
							foreach ($objQuestion->getAnswers() as $id => $answer)
							{
								if (strlen($solution[0]["value1"]) && $id == $solution[0]["value1"])
								{
									$selectedanswer = $answer->getAnswertext();
								}
							}
							$worksheet->setCell($row, $col+$pos, $selectedanswer);
						}
					}
				}
				$row++;
			}

			if($this->test_obj->isSingleChoiceTestWithoutShuffle())
			{
				// special tab for single choice tests without shuffle option
				$pos = 0;
				$row = 1;
				$usernames = array();
				$allusersheet = false;
				$pages = 0;

				$worksheet->addSheet($this->lng->txt('eval_all_users'). ' (2)');

				$col = 0;
				$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('name'));
				$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('login'));
				if (count($additionalFields))
				{
					foreach ($additionalFields as $fieldname)
					{
						if (strcmp($fieldname, "matriculation") == 0)
						{
							$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('matriculation'));
						}
					}
				}
				$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $this->lng->txt('test'));
				foreach($titles as $title)
				{
					$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row,  $title);
				}
				$worksheet->setBold('A' . $row . ':' . $worksheet->getColumnCoord($col - 1) . $row);

				$row++;
				foreach ($data->getParticipants() as $active_id => $userdata) 
				{
					$username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
					if (array_key_exists($username, $usernames))
					{
						$usernames[$username]++;
						$username .= " ($usernames[$username])";
					}
					else
					{
						$usernames[$username] = 1;
					}
					$col = 0;
					$worksheet->setCell($row, $col++, $username);
					$worksheet->setCell($row, $col++, $userdata->getLogin());
					if (count($additionalFields))
					{
						$userfields = ilObjUser::_lookupFields($userdata->getUserId());
						foreach ($additionalFields as $fieldname)
						{
							if (strcmp($fieldname, "matriculation") == 0)
							{
								if (strlen($userfields[$fieldname]))
								{
									$worksheet->setCell($row, $col++, $userfields[$fieldname]);
								}
								else
								{
									$col++;
								}
							}
						}
					}
					$worksheet->setCell($row, $col++, $this->test_obj->getTitle());
					$pass = $userdata->getScoredPass();
					if(is_object($userdata) && is_array($userdata->getQuestions($pass)))
					{
						foreach($userdata->getQuestions($pass) as $question)
						{
							$objQuestion = ilObjTest::_instanciateQuestion($question["aid"]);
							if(is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0)
							{
								$solution = $objQuestion->getSolutionValues($active_id, $pass);
								$pos = $positions[$question["aid"]];
								$selectedanswer = chr(65+$solution[0]["value1"]);
								$worksheet->setCell($row, $col+$pos, $selectedanswer);
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
			foreach($data->getParticipants() as $active_id => $userdata) 
			{
				$i++;
				
				$username = (!is_null($userdata) && $userdata->getName()) ? $userdata->getName() : "ID $active_id";
				if(array_key_exists($username, $usernames))
				{
					$usernames[$username]++;
					$username .= " ($i)";
				}
				else
				{
					$usernames[$username] = 1;
				}

				if($participantcount > 250)
				{
					if(!$allusersheet || ($pages-1) < floor($row / 64000))
					{
						$worksheet->addSheet($this->lng->txt("eval_all_users") . (($pages > 0) ? " (".($pages+1).")" : ""));
						$allusersheet = true;
						$row = 1;
						$pages++;
					}
				}
				else
				{
					$resultsheet = $worksheet->addSheet($username);
				}

				$pass = $userdata->getScoredPass();
				$row = ($allusersheet) ? $row : 1;
				$worksheet->setCell($row, 0, sprintf($this->lng->txt("tst_result_user_name_pass"), $pass+1, $userdata->getName()));
				$worksheet->setBold($worksheet->getColumnCoord(0) . $row);
				$row += 2;
				if(is_object($userdata) && is_array($userdata->getQuestions($pass)))
				{
					foreach($userdata->getQuestions($pass) as $question)
					{
						require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
						$question = assQuestion::_instanciateQuestion($question["id"]);
						if(is_object($question))
						{
							$row = $question->setExportDetailsXLS($worksheet, $row, $active_id, $pass);
						}
					}
				}
			}
		}

		if($deliver)
		{
			$testname = $this->test_obj->getTitle();
			switch($this->mode)
			{
				case 'results':
					$testname .= '_results';
					break;
			}
			$testname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $testname)) . '.xlsx';
			$worksheet->sendToClient($testname);
		}
		else
		{
			$excelfile = ilUtil::ilTempnam();
			$worksheet->writeToFile($excelfile);
			return $excelfile . '.xlsx';
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
		$this->test_obj->setAccessFilteredParticipantList(
			$this->test_obj->buildStatisticsAccessFilteredParticipantList()
		);
		
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
						$visit = ilDatePresentation::formatDate(new ilDateTime($ts, IL_CAL_UNIX));
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
					$finishdate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);
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

	abstract protected function initXmlExport();
	
	abstract protected function getQuestionIds();

	/**
	* build xml export file
	*/
	function buildExportFileXML()
	{
		global $DIC;
		$ilBench = $DIC['ilBench'];

		$ilBench->start("TestExport", "buildExportFile");

		$this->initXmlExport();

		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Test ".
			$this->test_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		$this->xml->xmlStartTag("ContentObject", array('Type' => 'Test'));

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
		fwrite($qti_file, $this->getQtiXml());
		fclose($qti_file);

		// get xml content
		$ilBench->start("TestExport", "buildExportFile_getXML");
		$this->test_obj->exportPagesXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("TestExport", "buildExportFile_getXML");
		
		$this->populateQuestionSetConfigXml($this->xml);
		
		$assignmentList = $this->buildQuestionSkillAssignmentList();
		$this->populateQuestionSkillAssignmentsXml($this->xml, $assignmentList, $this->getQuestionIds());
		$this->populateSkillLevelThresholdsXml($this->xml, $assignmentList);

		$this->xml->xmlEndTag("ContentObject");

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

		if ($this->isResultExportingEnabledForTestExport() && @file_exists("./Modules/Test/classes/class.ilTestResultsToXML.php"))
		{
			// dump results xml document to file
			include_once "./Modules/Test/classes/class.ilTestResultsToXML.php";
			$resultwriter = new ilTestResultsToXML($this->test_obj->getTestId(), $this->test_obj->getAnonymity());
			$resultwriter->setIncludeRandomTestQuestionsEnabled($this->test_obj->isRandomTest());
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
	
	abstract protected function populateQuestionSetConfigXml(ilXmlWriter $xmlWriter);
	
	protected function getQtiXml()
	{
		$tstQtiXml = $this->test_obj->toXML();
		$qstQtiXml = $this->getQuestionsQtiXml();
		
		if (strpos($tstQtiXml, "</section>") !== false)
		{
			$qtiXml = str_replace("</section>", "$qstQtiXml</section>", $tstQtiXml);
		}
		else
		{
			$qtiXml = str_replace("<section ident=\"1\"/>", "<section ident=\"1\">\n$qstQtiXml</section>", $tstQtiXml);
		}
		
		return $qtiXml;
	}
	
	abstract protected function getQuestionsQtiXml();
	
	protected function getQuestionQtiXml($questionId)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$questionOBJ = assQuestion::_instantiateQuestion($questionId);
		$xml = $questionOBJ->toXML(false);

		// still neccessary? there is an include header flag!?
		$xml = preg_replace("/<questestinterop>/", "", $xml);
		$xml = preg_replace("/<\/questestinterop>/", "", $xml);
		
		return $xml;
	}

	function exportXHTMLMediaObjects($a_export_dir)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->test_obj->getId());
		foreach ($mobs as $mob)
		{
			if (ilObjMediaObject::_exists($mob))
			{
				$mob_obj = new ilObjMediaObject($mob);
				$mob_obj->exportFiles($a_export_dir);
				unset($mob_obj);
			}
		}
		foreach ($this->getQuestionIds() as $question_id)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
			foreach ($mobs as $mob)
			{
				if (ilObjMediaObject::_exists($mob))
				{
					$mob_obj = new ilObjMediaObject($mob);
					$mob_obj->exportFiles($a_export_dir);
					unset($mob_obj);
				}
			}
		}
	}
	
	/**
	 * @param ilXmlWriter $a_xml_writer
	 * @param $questions
	 */
	protected function populateQuestionSkillAssignmentsXml(ilXmlWriter $a_xml_writer, ilAssQuestionSkillAssignmentList $assignmentList, $questions)
	{
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentExporter.php';
		$skillQuestionAssignmentExporter = new ilAssQuestionSkillAssignmentExporter();
		$skillQuestionAssignmentExporter->setXmlWriter($a_xml_writer);
		$skillQuestionAssignmentExporter->setQuestionIds($questions);
		$skillQuestionAssignmentExporter->setAssignmentList($assignmentList);
		$skillQuestionAssignmentExporter->export();
	}
	
	protected function populateSkillLevelThresholdsXml(ilXmlWriter $a_xml_writer, ilAssQuestionSkillAssignmentList $assignmentList)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
		$thresholdList = new ilTestSkillLevelThresholdList($ilDB);
		$thresholdList->setTestId($this->test_obj->getTestId());
		$thresholdList->loadFromDb();
		
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdExporter.php';
		$skillLevelThresholdExporter = new ilTestSkillLevelThresholdExporter();
		$skillLevelThresholdExporter->setXmlWriter($a_xml_writer);
		$skillLevelThresholdExporter->setAssignmentList($assignmentList);
		$skillLevelThresholdExporter->setThresholdList($thresholdList);
		$skillLevelThresholdExporter->export();
	}
	
	/**
	 * @return ilAssQuestionSkillAssignmentList
	 */
	protected function buildQuestionSkillAssignmentList()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
		$assignmentList->setParentObjId($this->test_obj->getId());
		$assignmentList->loadFromDb();
		$assignmentList->loadAdditionalSkillData();
		
		return $assignmentList;
	}
}

?>
