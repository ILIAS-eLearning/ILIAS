<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./survey/classes/inc.SurveyConstants.php";

/**
* Survey evaluation graphical output
*
* The ilSurveyEvaluationGUI class creates the evaluation output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilSurveyEvaluationGUI.php
* @modulegroup   Survey
*/
class ilSurveyEvaluationGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	
/**
* ilSurveyEvaluationGUI constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyEvaluationGUI object.
*
* @param object $a_object Associated ilObjSurvey class
* @access public
*/
  function ilSurveyEvaluationGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->object =& $a_object;
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

	function getCommand($cmd)
	{
		return $cmd;
	}

	/**
	* Show the detailed evaluation
	*
	* Show the detailed evaluation
	*
	* @access private
	*/
	function checkAnonymizedEvaluationAccess()
	{
		global $rbacsystem;
		global $ilUser;
		
		if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			// people with write access always have access to the evaluation
			$_SESSION["anon_evaluation_access"] = 1;
			return $this->evaluation();
		}
		if ($this->object->getEvaluationAccess() == EVALUATION_ACCESS_ALL)
		{
			// if the evaluation access is open for all users, grant it
			$_SESSION["anon_evaluation_access"] = 1;
			return $this->evaluation();
		}
		$surveycode = $this->object->getUserAccessCode($ilUser->getId());
		if ($this->object->isAnonymizedParticipant($surveycode))
		{
			$_SESSION["anon_evaluation_access"] = 1;
			return $this->evaluation();
		}
		$this->tpl->setVariable("TABS", "");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_checkaccess.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("AUTHENTICATION_NEEDED", $this->lng->txt("svy_check_evaluation_authentication_needed"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("EVALUATION_CHECKACCESS_INTRODUCTION", $this->lng->txt("svy_check_evaluation_access_introduction"));
		$this->tpl->setVariable("VALUE_CHECK", $this->lng->txt("ok"));
		$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TEXT_SURVEY_CODE", $this->lng->txt("survey_code"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Checks the evaluation access after entering the survey access code
	*
	* Checks the evaluation access after entering the survey access code
	*
	* @access private
	*/
	function checkEvaluationAccess()
	{
		$surveycode = $_POST["surveycode"];
		if ($this->object->isAnonymizedParticipant($surveycode))
		{
			$_SESSION["anon_evaluation_access"] = 1;
			$this->evaluation();
		}
		else
		{
			sendInfo($this->lng->txt("svy_check_evaluation_wrong_key", true));
			$this->cancelEvaluationAccess();
		}
	}
	
	/**
	* Cancels the input of the survey access code for evaluation access
	*
	* Cancels the input of the survey access code for evaluation access
	*
	* @access private
	*/
	function cancelEvaluationAccess()
	{
		include_once "./classes/class.ilUtil.php";
		global $tree;
		$path = $tree->getPathFull($this->object->getRefID());
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]);
	}
	
	/**
	* Show the detailed evaluation
	*
	* Show the detailed evaluation
	*
	* @access private
	*/
	function evaluationdetails()
	{
		$this->evaluation(1);
	}
	
	function exportCumulatedResults($details = 0)
	{
		$result = @include_once 'Spreadsheet/Excel/Writer.php';
		if (!$result)
		{
			include_once './classes/Spreadsheet/Excel/Writer.php';
		}
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";
		$object_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->object->getTitle());
		$surveyname = preg_replace("/\s/", "_", $object_title);

		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Creating a workbook
				$workbook = new Spreadsheet_Excel_Writer();

				// sending HTTP headers
				$workbook->send("$surveyname.xls");

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
				// Creating a worksheet
				include_once ("./classes/class.ilExcelUtils.php");
				$mainworksheet =& $workbook->addWorksheet();
				include_once ("./classes/class.ilExcelUtils.php");
				$mainworksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->lng->txt("question"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 2, ilExcelUtils::_convert_text($this->lng->txt("question_type"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 3, ilExcelUtils::_convert_text($this->lng->txt("users_answered"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 4, ilExcelUtils::_convert_text($this->lng->txt("users_skipped"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 5, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 6, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 7, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 8, ilExcelUtils::_convert_text($this->lng->txt("median"), $_POST["export_format"]), $format_bold);
				$mainworksheet->writeString(0, 9, ilExcelUtils::_convert_text($this->lng->txt("arithmetic_mean"), $_POST["export_format"]), $format_bold);
				break;
			case (TYPE_SPSS):
				$csvfile = array();
				$csvrow = array();
				array_push($csvrow, $this->lng->txt("title"));
				array_push($csvrow, $this->lng->txt("question"));
				array_push($csvrow, $this->lng->txt("question_type"));
				array_push($csvrow, $this->lng->txt("users_answered"));
				array_push($csvrow, $this->lng->txt("users_skipped"));
				array_push($csvrow, $this->lng->txt("mode"));

				//array_push($csvrow, $this->lng->txt("mode_text"));


				array_push($csvrow, $this->lng->txt("mode_nr_of_selections"));
				array_push($csvrow, $this->lng->txt("median"));
				array_push($csvrow, $this->lng->txt("arithmetic_mean"));
				array_push($csvfile, $csvrow);
				break;
		}
		$questions =& $this->object->getSurveyQuestions();
		foreach ($questions as $data)
		{
			include_once "./survey/classes/class.SurveyQuestion.php";
			$question_type = SurveyQuestion::_getQuestionType($data["question_id"]);
			include_once "./survey/classes/class.$question_type.php";
			$question = new $question_type();
			$question->loadFromDb($data["question_id"]);

			$eval = $this->object->getCumulatedResults($question);
			$row =& $question->outEvaluationCumulatedResults($eval);
			switch ($_POST["export_format"])
			{
				case TYPE_XLS:
					include_once ("./classes/class.ilExcelUtils.php");
					$mainworksheet->writeString($counter+1, 0, ilExcelUtils::_convert_text($row["QUESTION_TITLE"], $_POST["export_format"]));
					$mainworksheet->writeString($counter+1, 1, ilExcelUtils::_convert_text($row["QUESTION_TEXT"], $_POST["export_format"]));
					$mainworksheet->writeString($counter+1, 2, ilExcelUtils::_convert_text($row["QUESTION_TYPE"], $_POST["export_format"]));
					$mainworksheet->write($counter+1, 3, $row["USERS_ANSWERED"]);
					$mainworksheet->write($counter+1, 4, $row["USERS_SKIPPED"]);
					$mainworksheet->write($counter+1, 5, ilExcelUtils::_convert_text($row["MODE_VALUE"], $_POST["export_format"]));
					$mainworksheet->write($counter+1, 6, ilExcelUtils::_convert_text($row["MODE"], $_POST["export_format"]));
					$mainworksheet->write($counter+1, 7, $row["MODE_NR_OF_SELECTIONS"]);
					$mainworksheet->write($counter+1, 8, ilExcelUtils::_convert_text(str_replace("<br />", " ", $row["MEDIAN"]), $_POST["export_format"]));
					$mainworksheet->write($counter+1, 9, $row["ARITHMETIC_MEAN"]);
					break;
				case (TYPE_SPSS):
					$csvrow = array();
					array_push($csvrow, $row["QUESTION_TITLE"]);
					array_push($csvrow, $row["QUESTION_TEXT"]);
					array_push($csvrow, $row["QUESTION_TYPE"]);
					array_push($csvrow, $row["USERS_ANSWERED"]);
					array_push($csvrow, $row["USERS_SKIPPED"]);
					array_push($csvrow, $row["MODE"]);
					array_push($csvrow, $row["MODE_NR_OF_SELECTIONS"]);
					array_push($csvrow, $row["MEDIAN"]);
					array_push($csvrow, $row["ARITHMETIC_MEAN"]);
					array_push($csvfile, $csvrow);
					break;
			}
			if ($details)
			{
				switch ($_POST["export_format"])
				{
					case TYPE_XLS:
						include_once ("./classes/class.ilExcelUtils.php");
						$worksheet =& $workbook->addWorksheet();
						$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($row["QUESTION_TITLE"], $_POST["export_format"]));
						$worksheet->writeString(1, 0, ilExcelUtils::_convert_text($this->lng->txt("question"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(1, 1, ilExcelUtils::_convert_text($row["QUESTION_TEXT"], $_POST["export_format"]));
						$worksheet->writeString(2, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(2, 1, ilExcelUtils::_convert_text($row["QUESTION_TYPE"], $_POST["export_format"]));
						$worksheet->writeString(3, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered"), $_POST["export_format"]), $format_bold);
						$worksheet->write(3, 1, $row["USERS_ANSWERED"]);
						$worksheet->writeString(4, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped"), $_POST["export_format"]), $format_bold);
						$worksheet->write(4, 1, $row["USERS_SKIPPED"]);
						$rowcounter = 5;
						break;
				}
				switch ($eval["QUESTION_TYPE"])
				{
					case "SurveyOrdinalQuestion":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[1], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[2], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_NR_OF_SELECTIONS"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text(str_replace("<br />", " ", $eval["MEDIAN"]), $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections"), $_POST["export_format"]), $format_title);
								break;
						}
						foreach ($eval["variables"] as $key => $value)
						{
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
										$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"], $_POST["export_format"]));
										$worksheet->write($rowcounter, 2, $key+1);
										$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
										$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
										break;
								}
						}
						break;
					case "SurveyNominalQuestion":
						include_once "./survey/classes/class.SurveyNominalQuestion.php";
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_VALUE"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_NR_OF_SELECTIONS"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections"), $_POST["export_format"]), $format_title);
								break;
						}
						foreach ($eval["variables"] as $key => $value)
						{
							switch ($_POST["export_format"])
							{
								case TYPE_XLS:
									$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"], $_POST["export_format"]));
									$worksheet->write($rowcounter, 2, $key+1);
									$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
									$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
									break;
							}
						}
						break;
					case "SurveyMetricQuestion":
						include_once "./survey/classes/class.SurveyMetricQuestion.php";
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								$worksheet->write($rowcounter, 0, $this->lng->txt("subtype"), $format_bold);
								switch ($data["subtype"])
								{
									case SUBTYPE_NON_RATIO:
										$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("non_ratio"), $_POST["export_format"]), $format_bold);
										break;
									case SUBTYPE_RATIO_NON_ABSOLUTE:
										$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("ratio_non_absolute"), $_POST["export_format"]), $format_bold);
										break;
									case SUBTYPE_RATIO_ABSOLUTE:
										$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("ratio_absolute"), $_POST["export_format"]), $format_bold);
										break;
								}
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_NR_OF_SELECTIONS"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MEDIAN"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("arithmetic_mean"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["ARITHMETIC_MEAN"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("values"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("value"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter++, 3, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections"), $_POST["export_format"]), $format_title);
								break;
						}
						$values = "";
						if (is_array($eval["values"]))
						{
							foreach ($eval["values"] as $key => $value)
							{
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
										$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["value"], $_POST["export_format"]));
										$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
										$worksheet->write($rowcounter++, 3, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
										break;
								}
							}
						}
						break;
					case "SurveyTextQuestion":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("given_answers"), $_POST["export_format"]), $format_bold);
								break;
						}
						$textvalues = "";
						if (is_array($eval["textvalues"]))
						{
							foreach ($eval["textvalues"] as $textvalue)
							{
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
										$worksheet->write($rowcounter++, 1, $textvalue);
										break;
								}
							}
						}
						break;
				}
			}
			$counter++;
		}

		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Let's send the file
				$workbook->close();
				exit();
				break;
			case TYPE_SPSS:
				$csv = "";
				$separator = ";";
				foreach ($csvfile as $csvrow)
				{
					$csvrow =& $this->object->processCSVRow($csvrow, TRUE, $separator);
					$csv .= join($csvrow, $separator) . "\n";
				}
				include_once "./classes/class.ilUtil.php";
				ilUtil::deliverData($csv, "$surveyname.csv");
				exit();
				break;
		}
	}
	
	function evaluation($details = 0)
	{
		global $ilUser;
		if (($this->object->getAnonymize() == 1) && ($_SESSION["anon_evaluation_access"] != 1))
		{
			$this->checkAnonymizedEvaluationAccess();
			return;
		}
		
		if (strlen($_POST["export_format"]))
		{
			$this->exportCumulatedResults($details);
			return;
		}

		$this->setEvalTabs();
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", true);
		$counter = 0;
		$classes = array("tblrow1", "tblrow2");
		$questions =& $this->object->getSurveyQuestions();
		foreach ($questions as $data)
		{
			include_once "./survey/classes/class.SurveyQuestion.php";
			$question_type = SurveyQuestion::_getQuestionType($data["question_id"]);
			$question_type_gui = $question_type . "GUI";
			include_once "./survey/classes/class.$question_type". "GUI.php";
			$question_gui = new $question_type_gui($data["question_id"]);
			$question = $question_gui->object;
			//$question->loadFromDb($data["question_id"]);

			$eval = $this->object->getCumulatedResults($question);
			$row =& $question->outEvaluationCumulatedResults($eval);
			
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("QUESTION_TITLE", ($counter+1) . ". ".$row["QUESTION_TITLE"]);
			$maxlen = 37;
			$questiontext = "";
			if (strlen($row["QUESTION_TEXT"]) > $maxlen + 3)
			{
				$questiontext = substr($row["QUESTION_TEXT"], 0, $maxlen) . "...";
			}
			else
			{
				$questiontext = $row["QUESTION_TEXT"];
			}
			$this->tpl->setVariable("QUESTION_TEXT", $questiontext);
			$this->tpl->setVariable("USERS_ANSWERED", $row["USERS_ANSWERED"]);
			$this->tpl->setVariable("USERS_SKIPPED", $row["USERS_SKIPPED"]);
			$this->tpl->setVariable("QUESTION_TYPE", $row["QUESTION_TYPE"]);
			$this->tpl->setVariable("MODE", $row["MODE"]);
			$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $row["MODE_NR_OF_SELECTIONS"]);
			$this->tpl->setVariable("MEDIAN", $row["MEDIAN"]);
			$this->tpl->setVariable("ARITHMETIC_MEAN", $row["ARITHMETIC_MEAN"]);
			$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			if ($details)
			{
				$question_gui->outCumulatedResultsDetails($eval, $counter+1);
			}
			$counter++;
		}

		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./survey/templates/default/evaluation_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("QUESTION_TEXT", $this->lng->txt("question"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("USERS_ANSWERED", $this->lng->txt("users_answered"));
		$this->tpl->setVariable("USERS_SKIPPED", $this->lng->txt("users_skipped"));
		$this->tpl->setVariable("MODE", $this->lng->txt("mode"));
		$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
		$this->tpl->setVariable("MEDIAN", $this->lng->txt("median"));
		$this->tpl->setVariable("ARITHMETIC_MEAN", $this->lng->txt("arithmetic_mean"));
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("export_data_as"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_csv"));
		$this->tpl->setVariable("VALUE_DETAIL", $details);
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("PRINT_ACTION", $this->ctrl->getFormAction($this));
		if ($details)
		{
			$this->tpl->setVariable("CMD_EXPORT", "evaluationdetails");
		}
		else
		{
			$this->tpl->setVariable("CMD_EXPORT", "evaluation");
		}
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Print the survey evaluation for a selected user
	*
	* Print the survey evaluation for a selected user
	*
	* @access private
	*/
	function evaluationuser()
	{
		if (!is_array($_POST))
		{
			$_POST = array();
		}
		$result = @include_once 'Spreadsheet/Excel/Writer.php';
		if (!$result)
		{
			include_once './classes/Spreadsheet/Excel/Writer.php';
		}
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";
		$format_title_plain = "";
		$object_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->object->getTitle());
		$surveyname = preg_replace("/\s/", "_", $object_title);

		$eval =& $this->object->getEvaluationForAllUsers();
		$this->setEvalTabs();
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_user.html", true);
		$counter = 0;
		$classes = array("tblrow1top", "tblrow2top");
		$csvrow = array();
		$questions =& $this->object->getSurveyQuestions(true);
		$this->tpl->setCurrentBlock("headercell");
		$this->tpl->setVariable("TEXT_HEADER_CELL", $this->lng->txt("username"));
		$this->tpl->parseCurrentBlock();
		if ($this->object->getAnonymize() == ANONYMIZE_OFF)
		{
			$this->tpl->setCurrentBlock("headercell");
			$this->tpl->setVariable("TEXT_HEADER_CELL", $this->lng->txt("gender"));
			$this->tpl->parseCurrentBlock();
		}
		if (array_key_exists("export_format", $_POST))
		{
			array_push($csvrow, $this->lng->txt("username"));
			if ($this->object->getAnonymize() == ANONYMIZE_OFF)
			{
				array_push($csvrow, $this->lng->txt("gender"));
			}
		}
		$char = "A";
		$cellcounter = 1;
		foreach ($questions as $question_id => $question_data)
		{
			$this->tpl->setCurrentBlock("headercell");
			$this->tpl->setVariable("TEXT_HEADER_CELL", $char);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("legendrow");
			$this->tpl->setVariable("TEXT_KEY", $char++);
			$this->tpl->setVariable("TEXT_VALUE", $question_data["title"]);
			if (array_key_exists("export_format", $_POST))
			{
				array_push($csvrow, $question_data["title"]);
				switch ($question_data["questiontype_fi"])
				{
					case 1:
						include_once "./survey/classes/class.SurveyNominalQuestion.php";
						if ($question_data["subtype"] == SUBTYPE_MCMR)
						{
							foreach ($question_data["answers"] as $cat => $cattext)
							{
								array_push($csvrow, ($cat+1) . " - $cattext");
							}
						}
						break;
					case 2:
					case 3:
					case 4:
						break;
				}
			}
			$this->tpl->parseCurrentBlock();
		}
		$csvfile = array();
		array_push($csvfile, $csvrow);

		foreach ($eval as $user_id => $resultset)
		{
			$csvrow = array();
			$this->tpl->setCurrentBlock("bodycell");
			$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
			$this->tpl->setVariable("TEXT_BODY_CELL", $resultset["name"]);
			array_push($csvrow, $resultset["name"]);
			$this->tpl->parseCurrentBlock();
			if ($this->object->getAnonymize() == ANONYMIZE_OFF)
			{
				$this->tpl->setCurrentBlock("bodycell");
				$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
				$this->tpl->setVariable("TEXT_BODY_CELL", $resultset["gender"]);
				array_push($csvrow, $resultset["gender"]);
				$this->tpl->parseCurrentBlock();
			}
			foreach ($questions as $question_id => $question_data)
			{
				// csv output
				if (array_key_exists("export_format", $_POST))
				{
					switch ($question_data["questiontype_fi"])
					{
						case 1:
							// nominal question
							include_once "./survey/classes/class.SurveyNominalQuestion.php";
							if (count($resultset["answers"][$question_id]))
							{
								if ($question_data["subtype"] == SUBTYPE_MCMR)
								{
									array_push($csvrow, "");
									foreach ($question_data["answers"] as $cat => $cattext)
									{
										$found = 0;
										foreach ($resultset["answers"][$question_id] as $answerdata)
										{
											if (strcmp($cat, $answerdata["value"]) == 0)
											{
												$found = 1;
											}
										}
										if ($found)
										{
											array_push($csvrow, "1");
										}
										else
										{
											array_push($csvrow, "0");
										}
									}
								}
								else
								{
									array_push($csvrow, $resultset["answers"][$question_id][0]["value"]+1);
								}
							}
							else
							{
								array_push($csvrow, $this->lng->txt("skipped"));
								if ($question_data["subtype"] == SUBTYPE_MCMR)
								{
									foreach ($question_data["answers"] as $cat => $cattext)
									{
										array_push($csvrow, "");
									}
								}
							}
							break;
						case 2:
							// ordinal question
							if (count($resultset["answers"][$question_id]))
							{
								foreach ($resultset["answers"][$question_id] as $key => $answer)
								{
									array_push($csvrow, $answer["value"]+1);
								}
							}
							else
							{
								array_push($csvrow, $this->lng->txt("skipped"));
							}
							break;
						case 3:
							// metric question
							if (count($resultset["answers"][$question_id]))
							{
								foreach ($resultset["answers"][$question_id] as $key => $answer)
								{
									array_push($csvrow, $answer["value"]);
								}
							}
							else
							{
								array_push($csvrow, $this->lng->txt("skipped"));
							}
							break;
						case 4:
							// text question
							if (count($resultset["answers"][$question_id]))
							{
								foreach ($resultset["answers"][$question_id] as $key => $answer)
								{
									array_push($csvrow, $answer["textanswer"]);
								}
							}
							else
							{
								array_push($csvrow, $this->lng->txt("skipped"));
							}
							break;
					}
				}
				// html output
				if (count($resultset["answers"][$question_id]))
				{
					$answervalues = array();
					include_once "./classes/class.ilUtil.php";
					foreach ($resultset["answers"][$question_id] as $key => $answer)
					{
						switch ($question_data["questiontype_fi"])
						{
							case 1:
								// nominal question
								if (strcmp($answer["value"], "") != 0)
								{
									array_push($answervalues, ($answer["value"]+1) . " - " . ilUtil::prepareFormOutput($questions[$question_id]["answers"][$answer["value"]]));
								}
								break;
							case 2:
								// ordinal question
								array_push($answervalues, ($answer["value"]+1) . " - " . ilUtil::prepareFormOutput($questions[$question_id]["answers"][$answer["value"]]));
								break;
							case 3:
								// metric question
								array_push($answervalues, $answer["value"]);
								break;
							case 4:
								// text question
								array_push($answervalues, $answer["textanswer"]);
								break;
						}
					}
					$this->tpl->setCurrentBlock("bodycell");
					$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
					$this->tpl->setVariable("TEXT_BODY_CELL", join($answervalues, "<br />"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock("bodycell");
					$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
					$this->tpl->setVariable("TEXT_BODY_CELL", $this->lng->txt("skipped"));
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("row");
			$this->tpl->parse("row");
			$counter++;
			array_push($csvfile, $csvrow);
		}
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./survey/templates/default/evaluation_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("export_data_as"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_csv"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("PRINT_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_LEGEND", $this->lng->txt("legend"));
		$this->tpl->setVariable("TEXT_LEGEND_LINK", $this->lng->txt("eval_legend_link"));
		$this->tpl->setVariable("CMD_EXPORT", "evaluationuser");
		$this->tpl->parseCurrentBlock();
		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Let's send the file
				// Creating a workbook
				$workbook = new Spreadsheet_Excel_Writer();

				// sending HTTP headers
				$workbook->send("$surveyname.xls");

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
				$format_title_plain =& $workbook->addFormat();
				$format_title_plain->setColor('black');
				$format_title_plain->setPattern(1);
				$format_title_plain->setFgColor('silver');
				// Creating a worksheet
				$mainworksheet =& $workbook->addWorksheet();
				$row = 0;
				include_once "./classes/class.ilExcelUtils.php";
				foreach ($csvfile as $csvrow)
				{
					$col = 0;
					if ($row == 0)
					{
						foreach ($csvrow as $text)
						{
							$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($text, $_POST["export_format"]), $format_title);
						}
					}
					else
					{
						foreach ($csvrow as $text)
						{
							if (preg_match("/\d+/", $text))
							{
								$mainworksheet->writeNumber($row, $col++, $text);
							}
							else
							{
								$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($text, $_POST["export_format"]));
							}
						}
					}
					$row++;
				}
				$workbook->close();
				exit();
				break;
			case TYPE_SPSS:
				$csv = "";
				$separator = ";";
				foreach ($csvfile as $csvrow)
				{
					$csvrow =& $this->object->processCSVRow($csvrow, TRUE, $separator);
					$csv .= join($csvrow, $separator) . "\n";
				}
				include_once "./classes/class.ilUtil.php";
				ilUtil::deliverData($csv, "$surveyname.csv");
				exit();
				break;
		}
	}
	
	/**
	* Set the tabs for the evaluation output
	*
	* Set the tabs for the evaluation output
	*
	* @access private
	*/
	function setEvalTabs()
	{
		global $rbacsystem,$ilTabs;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$tabs_gui->addTarget(
			"svy_eval_cumulated", 
			$this->ctrl->getLinkTargetByClass(get_class($this), "evaluation"), 
			array("evaluation", "checkEvaluationAccess"),	
			""
		);

		$tabs_gui->addTarget(
			"svy_eval_detail", 
			$this->ctrl->getLinkTargetByClass(get_class($this), "evaluationdetails"), 
			array("evaluationdetails"),	
			""
		);
		
		$tabs_gui->addTarget(
			"svy_eval_user", 
			$this->ctrl->getLinkTargetByClass(get_class($this), "evaluationuser"), 
			array("evaluationuser"),	
			""
		);
		$ilTabs = $tabs_gui;
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}
	
}
?>
