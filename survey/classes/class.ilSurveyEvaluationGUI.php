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
		$surveycode = $this->object->getUserSurveyCode();
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
	
	function evaluation($details = 0)
	{
		global $ilUser;
		if (($this->object->getAnonymize() == 1) && ($_SESSION["anon_evaluation_access"] != 1))
		{
			$this->checkAnonymizedEvaluationAccess();
			return;
		}
		include_once './classes/Spreadsheet/Excel/Writer.php';
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";
		$object_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->object->getTitle());
		$surveyname = preg_replace("/\s/", "_", $object_title);

		if (!$_POST["export_format"])
		{
			$_POST["export_format"] = TYPE_PRINT;
		}
		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
			case TYPE_XLS_MAC:
				// Creating a workbook
				include_once "./classes/Spreadsheet/Excel/Writer.php";
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
			case (TYPE_SPSS || TYPE_PRINT):
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

		$this->setEvalTabs();
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", true);
		$counter = 0;
		$classes = array("tblrow1", "tblrow2");
		$questions =& $this->object->getSurveyQuestions();
		foreach ($questions as $data)
		{
			$eval = $this->object->getEvaluation($data["question_id"], $ilUser->id);
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("QUESTION_TITLE", ($counter+1) . ". " . $data["title"]);
			$maxlen = 37;
			if (strlen($data["questiontext"]) > $maxlen + 3)
			{
				$questiontext = substr($data["questiontext"], 0, $maxlen) . "...";
			}
			else
			{
				$questiontext = $data["questiontext"];
			}
			$this->tpl->setVariable("QUESTION_TEXT", $questiontext);
			$this->tpl->setVariable("USERS_ANSWERED", $eval["USERS_ANSWERED"]);
			$this->tpl->setVariable("USERS_SKIPPED", $eval["USERS_SKIPPED"]);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($eval["QUESTION_TYPE"]));
			$this->tpl->setVariable("MODE", $eval["MODE"]);
			$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
			$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
			$this->tpl->setVariable("ARITHMETIC_MEAN", $eval["ARITHMETIC_MEAN"]);
			$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
			switch ($_POST["export_format"])
			{
				case TYPE_XLS:
				case TYPE_XLS_MAC:
					include_once ("./classes/class.ilExcelUtils.php");
					$mainworksheet->writeString($counter+1, 0, ilExcelUtils::_convert_text($data["title"], $_POST["export_format"]));
					$mainworksheet->writeString($counter+1, 1, ilExcelUtils::_convert_text($data["questiontext"], $_POST["export_format"]));
					$mainworksheet->writeString($counter+1, 2, ilExcelUtils::_convert_text($this->lng->txt($eval["QUESTION_TYPE"]), $_POST["export_format"]));
					$mainworksheet->write($counter+1, 3, $eval["USERS_ANSWERED"]);
					$mainworksheet->write($counter+1, 4, $eval["USERS_SKIPPED"]);
					preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
					switch ($eval["QUESTION_TYPE"])
					{
						case "qt_metric":
							$mainworksheet->write($counter+1, 5, ilExcelUtils::_convert_text($eval["MODE"], $_POST["export_format"]));
							$mainworksheet->write($counter+1, 6, ilExcelUtils::_convert_text($eval["MODE"], $_POST["export_format"]));
							break;
						default:
							$mainworksheet->write($counter+1, 5, ilExcelUtils::_convert_text($matches[1], $_POST["export_format"]));
							$mainworksheet->write($counter+1, 6, ilExcelUtils::_convert_text($matches[2], $_POST["export_format"]));
							break;
					}
					$mainworksheet->write($counter+1, 7, $eval["MODE_NR_OF_SELECTIONS"]);
					$mainworksheet->write($counter+1, 8, ilExcelUtils::_convert_text($eval["MEDIAN"], $_POST["export_format"]));
					$mainworksheet->write($counter+1, 9, $eval["ARITHMETIC_MEAN"]);
					break;
				case (TYPE_SPSS || TYPE_PRINT):
					$csvrow = array();
					array_push($csvrow, $data["title"]);
					array_push($csvrow, $data["questiontext"]);
					array_push($csvrow, $this->lng->txt($eval["QUESTION_TYPE"]));
					array_push($csvrow, $eval["USERS_ANSWERED"]);
					array_push($csvrow, $eval["USERS_SKIPPED"]);
					array_push($csvrow, $eval["MODE"]);
					array_push($csvrow, $eval["MODE_NR_OF_SELECTIONS"]);
					array_push($csvrow, $eval["MEDIAN"]);
					array_push($csvrow, $eval["ARITHMETIC_MEAN"]);
					array_push($csvfile, $csvrow);
					break;
			}
			$this->tpl->parseCurrentBlock();
			if ($details)
			{
				$printDetail = array();
				switch ($_POST["export_format"])
				{
					case TYPE_XLS:
					case TYPE_XLS_MAC:
						include_once ("./classes/class.ilExcelUtils.php");
						$worksheet =& $workbook->addWorksheet();
						$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($data["title"], $_POST["export_format"]));
						$worksheet->writeString(1, 0, ilExcelUtils::_convert_text($this->lng->txt("question"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(1, 1, ilExcelUtils::_convert_text($data["questiontext"], $_POST["export_format"]));
						$worksheet->writeString(2, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type"), $_POST["export_format"]), $format_bold);
						$worksheet->writeString(2, 1, ilExcelUtils::_convert_text($this->lng->txt($eval["QUESTION_TYPE"]), $_POST["export_format"]));
						$worksheet->writeString(3, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered"), $_POST["export_format"]), $format_bold);
						$worksheet->write(3, 1, $eval["USERS_ANSWERED"]);
						$worksheet->writeString(4, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped"), $_POST["export_format"]), $format_bold);
						$worksheet->write(4, 1, $eval["USERS_SKIPPED"]);
						$rowcounter = 5;
						break;
					case TYPE_PRINT:
						array_push($printDetail, $this->lng->txt("title"));
						array_push($printDetail, $data["title"]);
						array_push($printDetail, $this->lng->txt("question"));
						array_push($printDetail, $data["questiontext"]);
						array_push($printDetail, $this->lng->txt("question_type"));
						array_push($printDetail, $this->lng->txt($eval["QUESTION_TYPE"]));
						array_push($printDetail, $this->lng->txt("users_answered"));
						array_push($printDetail, $eval["USERS_ANSWERED"]);
						array_push($printDetail, $this->lng->txt("users_skipped"));
						array_push($printDetail, $eval["USERS_SKIPPED"]);
						break;
				}
				$this->tpl->setCurrentBlock("detail");
				$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_QUESTION_TEXT", $this->lng->txt("question"));
				$this->tpl->setVariable("QUESTION_TEXT", $data["questiontext"]);
				$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("question_type"));
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($eval["QUESTION_TYPE"]));
				$this->tpl->setVariable("TEXT_USERS_ANSWERED", $this->lng->txt("users_answered"));
				$this->tpl->setVariable("USERS_ANSWERED", $eval["USERS_ANSWERED"]);
				$this->tpl->setVariable("TEXT_USERS_SKIPPED", $this->lng->txt("users_skipped"));
				$this->tpl->setVariable("USERS_SKIPPED", $eval["USERS_SKIPPED"]);
				switch ($eval["QUESTION_TYPE"])
				{
					case "qt_ordinal":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
							case TYPE_XLS_MAC:
								preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[1], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[2], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_NR_OF_SELECTIONS"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MEDIAN"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections"), $_POST["export_format"]), $format_title);
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_MEDIAN", $this->lng->txt("median"));
						$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
						$this->tpl->setVariable("TEXT_CATEGORIES", $this->lng->txt("categories"));
						$categories = "";
						foreach ($eval["variables"] as $key => $value)
						{
							$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
								$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
								$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
									case TYPE_XLS_MAC:
										$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"], $_POST["export_format"]));
										$worksheet->write($rowcounter, 2, $key+1);
										$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
										$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
										break;
								}
						}
						$categories = "<ol>$categories</ol>";
						$this->tpl->setVariable("VALUE_CATEGORIES", $categories);
						
						// display chart for ordinal question for array $eval["variables"]
						$this->tpl->setVariable("TEXT_CHART", $this->lng->txt("chart"));
						$this->tpl->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");
						$this->tpl->setVariable("CHART","./survey/displaychart.php?grName=" . urlencode($data["title"]) . 
							"&type=bars" . 
							"&x=" . urlencode($this->lng->txt("answers")) . 
							"&y=" . urlencode($this->lng->txt("users_answered")) . 
							"&arr=".base64_encode(serialize($eval["variables"])));
						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("median"));
								array_push($printDetail, $eval["MEDIAN"]);
								array_push($printDetail, $this->lng->txt("categories"));
								array_push($printDetail, $categories);
								break;
						}
						break;
					case "qt_nominal":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
							case TYPE_XLS_MAC:
								preg_match("/(.*?)\s+-\s+(.*)/", $eval["MODE"], $matches);
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[1], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[2], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval["MODE_NR_OF_SELECTIONS"], $_POST["export_format"]));
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories"), $_POST["export_format"]), $format_bold);
								$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value"), $_POST["export_format"]), $format_title);
								$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected"), $_POST["export_format"]), $format_title);
								switch ($data["subtype"])
								{
									case SUBTYPE_MCSR:
										$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections"), $_POST["export_format"]), $format_title);
										break;
									case SUBTYPE_MCMR:
										$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_offered_answers"), $_POST["export_format"]), $format_title);
										break;
								}
								break;
						}
						array_push($printDetail, $this->lng->txt("subtype"));
						$this->tpl->setVariable("TEXT_QUESTION_SUBTYPE", $this->lng->txt("subtype"));
						$charttype = "bars";
						switch ($data["subtype"])
						{
							case SUBTYPE_MCSR:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("multiple_choice_single_response"));
								array_push($printDetail, $this->lng->txt("multiple_choice_single_response"));
								break;
							case SUBTYPE_MCMR:
								$charttype = "pie";
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("multiple_choice_multiple_response"));
								array_push($printDetail, $this->lng->txt("multiple_choice_multiple_response"));
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_CATEGORIES", $this->lng->txt("categories"));
						$categories = "";
						foreach ($eval["variables"] as $key => $value)
						{
							switch ($data["subtype"])
							{
								case SUBTYPE_MCSR:
									$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
										$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
										$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
									break;
								case SUBTYPE_MCMR:
									$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
										$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
										$this->lng->txt("percentage_of_offered_answers") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
									break;
							}
							switch ($_POST["export_format"])
							{
								case TYPE_XLS:
								case TYPE_XLS_MAC:
									$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"], $_POST["export_format"]));
									$worksheet->write($rowcounter, 2, $key+1);
									$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
									$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
									break;
							}
						}
						$categories = "<ol>$categories</ol>";
						$this->tpl->setVariable("VALUE_CATEGORIES", $categories);

						// display chart for nominal question for array $eval["variables"]
						$this->tpl->setVariable("TEXT_CHART", $this->lng->txt("chart"));
						$this->tpl->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");
						$this->tpl->setVariable("CHART","./survey/displaychart.php?grName=" . urlencode($data["title"]) . 
							"&type=$charttype" . 
							"&x=" . urlencode($this->lng->txt("answers")) . 
							"&y=" . urlencode($this->lng->txt("users_answered")) . 
							"&arr=".base64_encode(serialize($eval["variables"])));

						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("categories"));
								array_push($printDetail, $categories);
								break;
						}
						break;
					case "qt_metric":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
							case TYPE_XLS_MAC:
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
						$this->tpl->setVariable("TEXT_QUESTION_SUBTYPE", $this->lng->txt("subtype"));
						array_push($printDetail, $this->lng->txt("subtype"));
						switch ($data["subtype"])
						{
							case SUBTYPE_NON_RATIO:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("non_ratio"));
								array_push($printDetail, $this->lng->txt("non_ratio"));
								break;
							case SUBTYPE_RATIO_NON_ABSOLUTE:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("ratio_non_absolute"));
								array_push($printDetail, $this->lng->txt("ratio_non_absolute"));
								break;
							case SUBTYPE_RATIO_ABSOLUTE:
								$this->tpl->setVariable("QUESTION_SUBTYPE", $this->lng->txt("ratio_absolute"));
								array_push($printDetail, $this->lng->txt("ratio_absolute"));
								break;
						}
						$this->tpl->setVariable("TEXT_MODE", $this->lng->txt("mode"));
						$this->tpl->setVariable("MODE", $eval["MODE"]);
						$this->tpl->setVariable("TEXT_MODE_NR_OF_SELECTIONS", $this->lng->txt("mode_nr_of_selections"));
						$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", $eval["MODE_NR_OF_SELECTIONS"]);
						$this->tpl->setVariable("TEXT_MEDIAN", $this->lng->txt("median"));
						$this->tpl->setVariable("MEDIAN", $eval["MEDIAN"]);
						$this->tpl->setVariable("TEXT_ARITHMETIC_MEAN", $this->lng->txt("arithmetic_mean"));
						$this->tpl->setVariable("ARITHMETIC_MEAN", $eval["ARITHMETIC_MEAN"]);
						$this->tpl->setVariable("TEXT_VALUES", $this->lng->txt("values"));
						$values = "";
						if (is_array($eval["value"]))
						{
							foreach ($eval["values"] as $key => $value)
							{
								$values .= "<li>" . $this->lng->txt("value") . ": " . "<span class=\"bold\">" . $value["value"] . "</span><br />" .
									$this->lng->txt("value_nr_entered") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
									$this->lng->txt("percentage_of_entered_values") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
									case TYPE_XLS_MAC:
										$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["value"], $_POST["export_format"]));
										$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($value["selected"], $_POST["export_format"]));
										$worksheet->write($rowcounter++, 3, ilExcelUtils::_convert_text($value["percentage"], $_POST["export_format"]), $format_percent);
										break;
								}
							}
						}
						$values = "<ol>$values</ol>";
						$this->tpl->setVariable("VALUE_VALUES", $values);
						// display chart for metric question for array $eval["values"]
						$this->tpl->setVariable("TEXT_CHART", $this->lng->txt("chart"));
						$this->tpl->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");
						$this->tpl->setVariable("CHART","./survey/displaychart.php?grName=" . urlencode($data["title"]) . 
							"&type=bars" . 
							"&x=" . urlencode($this->lng->txt("answers")) . 
							"&y=" . urlencode($this->lng->txt("users_answered")) . 
							"&arr=".base64_encode(serialize($eval["values"])));

						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("mode"));
								array_push($printDetail, $eval["MODE"]);
								array_push($printDetail, $this->lng->txt("mode_nr_of_selections"));
								array_push($printDetail, $eval["MODE_NR_OF_SELECTIONS"]);
								array_push($printDetail, $this->lng->txt("median"));
								array_push($printDetail, $eval["MEDIAN"]);
								array_push($printDetail, $this->lng->txt("values"));
								array_push($printDetail, $values);
								break;
						}
						break;
					case "qt_text":
						switch ($_POST["export_format"])
						{
							case TYPE_XLS:
							case TYPE_XLS_MAC:
								$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("given_answers"), $_POST["export_format"]), $format_bold);
								break;
						}
						$this->tpl->setVariable("TEXT_TEXTVALUES", $this->lng->txt("given_answers"));
						$textvalues = "";
						if (is_array($eval["textvalues"]))
						{
							foreach ($eval["textvalues"] as $textvalue)
							{
								$textvalues .= "<li>" . preg_replace("/\n/", "<br>", $textvalue) . "</li>";
								switch ($_POST["export_format"])
								{
									case TYPE_XLS:
									case TYPE_XLS_MAC:
										$worksheet->write($rowcounter++, 1, $textvalue);
										break;
								}
							}
						}
						$textvalues = "<ul>$textvalues</ul>";
						$this->tpl->setVariable("VALUE_TEXTVALUES", $textvalues);
						switch ($_POST["export_format"])
						{
							case TYPE_PRINT:
								array_push($printDetail, $this->lng->txt("given_answers"));
								array_push($printDetail, $textvalues);
								break;
						}
						break;
				}

				if ($_POST["export_format"]==TYPE_PRINT)
				{
					$printdetail_file = array();
					array_push($printdetail_file, $printDetail);
					$s_question = $counter+1;
					$_SESSION[$this->lng->txt("question").$s_question] = $printdetail_file;
					$this->tpl->setVariable("PRINT_ACTION", $this->ctrl->getLinkTarget($this, "printEvaluation") . "&".$this->lng->txt("question")."=".$s_question);
					$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
					include_once "./classes/class.ilUtil.php";
					$this->tpl->setVariable("PRINT_IMAGE", ilUtil::getImagePath("icon_print.gif"));
				}
				$this->tpl->parseCurrentBlock();
			}
			$counter++;
		}
		if ($_POST["export_format"]==TYPE_PRINT)
		{
			$_SESSION["print_eval"] = $csvfile;
		}


		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
			case TYPE_XLS_MAC:
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
		$this->tpl->setVariable("TEXT_EXCEL_MAC", $this->lng->txt("exp_type_excel_mac"));
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
		include_once './classes/Spreadsheet/Excel/Writer.php';
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";
		$format_title_plain = "";
		$object_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->object->getTitle());
		$surveyname = preg_replace("/\s/", "_", $object_title);

		if (!$_POST["export_format"])
		{
			$_POST["export_format"] = TYPE_PRINT;
		}

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
									array_push($csvrow, $resultset["answers"][$question_id][0]["value"]);
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
									array_push($csvrow, $answer["value"]);
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
		$this->tpl->setVariable("TEXT_EXCEL_MAC", $this->lng->txt("exp_type_excel_mac"));
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
			case TYPE_XLS_MAC:
				// Let's send the file
				// Creating a workbook
				include_once "./classes/Spreadsheet/Excel/Writer.php";
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
