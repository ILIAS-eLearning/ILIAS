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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Survey evaluation graphical output
*
* The ilSurveyEvaluationGUI class creates the evaluation output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
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
			$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
			return $this->evaluation();
		}
		if ($this->object->getEvaluationAccess() == EVALUATION_ACCESS_ALL)
		{
			// if the evaluation access is open for all users, grant it
			$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
			return $this->evaluation();
		}
		$surveycode = $this->object->getUserAccessCode($ilUser->getId());
		if ($this->object->isAnonymizedParticipant($surveycode))
		{
			$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
			return $this->evaluation();
		}
		$this->tpl->setVariable("TABS", "");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_checkaccess.html", "Modules/Survey");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("AUTHENTICATION_NEEDED", $this->lng->txt("svy_check_evaluation_authentication_needed"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "checkAnonymizedEvaluationAccess"));
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
			$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
			$this->evaluation();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("svy_check_evaluation_wrong_key", true));
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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
		$format_bold = "";
		$format_percent = "";
		$format_datetime = "";
		$format_title = "";
		$surveyname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->object->getTitle()));

		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				include_once "./classes/class.ilExcelWriterAdapter.php";
				$excelfile = ilUtil::ilTempnam();
				$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
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
				$format_title->setAlign('center');
				// Creating a worksheet
				include_once ("./classes/class.ilExcelUtils.php");
				$mainworksheet =& $workbook->addWorksheet();
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
		$counter++;
		foreach ($questions as $data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question = SurveyQuestion::_instanciateQuestion($data["question_id"]);

			$eval = $this->object->getCumulatedResults($question);
			switch ($_POST["export_format"])
			{
				case TYPE_XLS:
					$counter = $question->setExportCumulatedXLS($mainworksheet, $format_title, $format_bold, $eval, $counter);
					break;
				case (TYPE_SPSS):
					$csvrows =& $question->setExportCumulatedCVS($eval);
					foreach ($csvrows as $csvrow)
					{
						array_push($csvfile, $csvrow);
					}
					break;
			}
			if ($details)
			{
				switch ($_POST["export_format"])
				{
					case TYPE_XLS:
						$question->setExportDetailsXLS($workbook, $format_title, $format_bold, $eval);
						break;
				}
			}
		}

		switch ($_POST["export_format"])
		{
			case TYPE_XLS:
				// Let's send the file
				$workbook->close();
				ilUtil::deliverFile($excelfile, "$surveyname.xls", "application/vnd.ms-excel");
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
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::deliverData($csv, "$surveyname.csv");
				exit();
				break;
		}
	}
	
	function evaluation($details = 0)
	{
		global $ilUser;
		global $rbacsystem;
		global $ilias;

		if (!$rbacsystem->checkAccess("read",$_GET["ref_id"]))
		{
			ilUtil::sendInfo($this->lng->txt("permission_denied"));
			return;
		}
		switch ($this->object->getEvaluationAccess())
		{
			case EVALUATION_ACCESS_OFF:
				if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
				{
					ilUtil::sendInfo($this->lng->txt("permission_denied"));
					return;
				}
				break;
			case EVALUATION_ACCESS_ALL:
				include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
				if (!($rbacsystem->checkAccess("write",$_GET["ref_id"]) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId())))
				{
					ilUtil::sendInfo($this->lng->txt("permission_denied"));
					return;
				}
				break;
			case EVALUATION_ACCESS_PARTICIPANTS:
				if (($this->object->getAnonymize() == 1) && ($_SESSION["anon_evaluation_access"] != $_GET["ref_id"]))
				{
					$this->checkAnonymizedEvaluationAccess();
					return;
				}
				break;
		}
	
		if (strlen($_POST["export_format"]))
		{
			$this->exportCumulatedResults($details);
			return;
		}

		ilUtil::sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", "Modules/Survey");
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$counter = 0;
		$classes = array("tblrow1", "tblrow2");
		$questions =& $this->object->getSurveyQuestions();
		foreach ($questions as $data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question_gui = SurveyQuestion::_instanciateQuestionGUI($data["question_id"]);
			$question = $question_gui->object;
			$row = $question_gui->getCumulatedResultRow($counter, $classes[$counter % 2], $this->object->getSurveyId());
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("ROW", $row);
			$this->tpl->parseCurrentBlock();
			if ($details)
			{
				$detail = $question_gui->getCumulatedResultsDetails($this->object->getSurveyId(), $counter+1);
				$this->tpl->setCurrentBlock("detail");
				$this->tpl->setVariable("DETAIL", $detail);
				$this->tpl->parseCurrentBlock();
			}
			$counter++;
		}

		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Survey/templates/default/evaluation_print.css");
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
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) 
		{
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "evaluation"));
			$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("export_data_as"));
			$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
			$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_csv"));
			$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
			if ($details)
			{
				$this->tpl->setVariable("CMD_EXPORT", "evaluationdetails");
			}
			else
			{
				$this->tpl->setVariable("CMD_EXPORT", "evaluation");
			}
		}
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("VALUE_DETAIL", $details);
		$this->tpl->setVariable("PRINT_ACTION", $this->ctrl->getFormAction($this, "evaluation"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Export the user specific results for the survey
	*
	* Export the user specific results for the survey
	*
	* @access private
	*/
	function exportUserSpecificResults($export_format)
	{
		global $ilLog;
		$surveyname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->object->getTitle()));
		$csvfile = array();
		$csvrow = array();
		$questions = array();
		$questions =& $this->object->getSurveyQuestions(true);
		array_push($csvrow, $this->lng->txt("username"));
		if ($this->object->canExportSurveyCode())
		{
			array_push($csvrow, $this->lng->txt("codes"));
		}
		if ($this->object->getAnonymize() == ANONYMIZE_OFF)
		{
			array_push($csvrow, $this->lng->txt("gender"));
		}
		$cellcounter = 1;
		foreach ($questions as $question_id => $question_data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question = SurveyQuestion::_instanciateQuestion($question_data["question_id"]);
			$question->addUserSpecificResultsExportTitles($csvrow);
			$questions[$question_data["question_id"]] = $question;
		}
		array_push($csvfile, $csvrow);
		$participants =& $this->object->getSurveyFinishedIds();

		foreach ($participants as $user_id)
		{
			$resultset =& $this->object->getEvaluationByUser($questions, $user_id);
			$csvrow = array();
			array_push($csvrow, $resultset["name"]);
			if ($this->object->canExportSurveyCode())
			{
				array_push($csvrow, $user_id);
			}
			if ($this->object->getAnonymize() == ANONYMIZE_OFF)
			{
				array_push($csvrow, $resultset["gender"]);
			}
			foreach ($questions as $question_id => $question)
			{
				$question->addUserSpecificResultsData($csvrow, $resultset);
			}
			array_push($csvfile, $csvrow);
		}
		switch ($export_format)
		{
			case TYPE_XLS:
				include_once "./classes/class.ilExcelWriterAdapter.php";
				$excelfile = ilUtil::ilTempnam();
				$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
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
				$format_title_plain =& $workbook->addFormat();
				$format_title_plain->setColor('black');
				$format_title_plain->setPattern(1);
				$format_title_plain->setFgColor('silver');
				// Creating a worksheet
				$pages = floor((count($csvfile[0])) / 250) + 1;
				$worksheets = array();
				for ($i = 0; $i < $pages; $i++)
				{
					$worksheets[$i] =& $workbook->addWorksheet();
				}
				$row = 0;
				include_once "./classes/class.ilExcelUtils.php";
				foreach ($csvfile as $csvrow)
				{
					$col = 0;
					if ($row == 0)
					{
						$worksheet = 0;
						$mainworksheet =& $worksheets[$worksheet];
						foreach ($csvrow as $text)
						{
							$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($text, $_POST["export_format"]), $format_title);
							if ($col % 251 == 0) 
							{
								$worksheet++;
								$col = 1;
								$mainworksheet =& $worksheets[$worksheet];
								$mainworksheet->writeString($row, 0, ilExcelUtils::_convert_text($csvrow[0], $_POST["export_format"]), $format_title);
							}
						}
					}
					else
					{
						$worksheet = 0;
						$mainworksheet =& $worksheets[$worksheet];
						foreach ($csvrow as $text)
						{
							if (is_numeric($text))
							{
								$mainworksheet->writeNumber($row, $col++, $text);
							}
							else
							{
								$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($text, $_POST["export_format"]));
							}
							if ($col % 251 == 0) 
							{
								$worksheet++;
								$col = 1;
								$mainworksheet =& $worksheets[$worksheet];
								$mainworksheet->writeString($row, 0, ilExcelUtils::_convert_text($csvrow[0], $_POST["export_format"]));
							}
						}
					}
					$row++;
				}
				$workbook->close();
				ilUtil::deliverFile($excelfile, "$surveyname.xls", "application/vnd.ms-excel");
				exit();
				break;
			case TYPE_SPSS:
				$csv = "";
				$separator = ";";
				foreach ($csvfile as $csvrow)
				{
					$csvrow =& str_replace("\n", " ", $this->object->processCSVRow($csvrow, TRUE, $separator));
					$csv .= join($csvrow, $separator) . "\n";
				}
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::deliverData($csv, "$surveyname.csv");
				exit();
				break;
		}
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
		global $ilAccess, $ilLog;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			ilUtil::sendInfo($this->lng->txt("no_permission"), TRUE);
			$this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
		}
		if (!is_array($_POST))
		{
			$_POST = array();
		}
		if (array_key_exists("export_format", $_POST))
		{
			return $this->exportUserSpecificResults($_POST["export_format"]);
		}

		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$userResults =& $this->object->getUserSpecificResults();
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_user.html", "Modules/Survey");
		$counter = 0;
		$classes = array("tblrow1top", "tblrow2top");
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
		$this->tpl->setCurrentBlock("headercell");
		$this->tpl->setVariable("TEXT_HEADER_CELL", $this->lng->txt("question"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("headercell");
		$this->tpl->setVariable("TEXT_HEADER_CELL", $this->lng->txt("results"));
		$this->tpl->parseCurrentBlock();

		$cellcounter = 1;
		$participants =& $this->object->getSurveyParticipants();
		foreach ($participants as $data)
		{
			$this->tpl->setCurrentBlock("bodycell");
			$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
			$this->tpl->setVariable("TEXT_BODY_CELL", $data["sortname"]);
			$this->tpl->parseCurrentBlock();
			if ($this->object->getAnonymize() == ANONYMIZE_OFF)
			{
				$this->tpl->setCurrentBlock("bodycell");
				$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
				$this->tpl->setVariable("TEXT_BODY_CELL", $data["gender"]);
				$this->tpl->parseCurrentBlock();
			}
			$intro = TRUE;
			$questioncounter = 1;
			foreach ($questions as $question_id => $question_data)
			{
				if ($intro)
				{
					$intro = FALSE;
				}
				else
				{
					if ($this->object->getAnonymize() == ANONYMIZE_OFF)
					{
						$this->tpl->setCurrentBlock("bodycell");
						$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("bodycell");
					$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("bodycell");
				$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
				$this->tpl->setVariable("TEXT_BODY_CELL", $questioncounter++ . ". " . $question_data["title"]);
				$this->tpl->parseCurrentBlock();
				
				$found = $userResults[$question_id][$data["active_id"]];
				$text = "";
				if (is_array($found))
				{
					$text = implode("<br />", $found);
				}
				else
				{
					$text = $found;
				}
				if (strlen($text) == 0) $text = $this->lng->txt("skipped");
				$this->tpl->setCurrentBlock("bodycell");
				$this->tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
				$this->tpl->setVariable("TEXT_BODY_CELL", $text);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("row");
				$this->tpl->parse("row");
			}
			$counter++;
		}
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Survey/templates/default/evaluation_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("export_data_as"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_csv"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "evaluationuser"));
		$this->tpl->setVariable("PRINT_ACTION", $this->ctrl->getFormAction($this, "evaluationuser"));
		$this->tpl->setVariable("CMD_EXPORT", "evaluationuser");
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Creates an image visualising the results of a question
*
* Creates an image visualising the results of a question
*
* @access public
*/
	function outChart()
	{
		$survey_id = $_GET["survey"];
		$question_id = $_GET["question"];
		$type = (strlen($_GET["type"])) ? $_GET["type"] : "";
		$question =& $this->object->_instanciateQuestion($question_id);
		$question->outChart($survey_id, $type);
	}
}
?>
