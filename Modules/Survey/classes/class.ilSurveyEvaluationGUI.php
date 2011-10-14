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
			ilUtil::sendFailure($this->lng->txt("svy_check_evaluation_wrong_key", true));
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
				include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
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
				include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
				$mainworksheet =& $workbook->addWorksheet();
				$column = 0;
				switch ($_POST['export_label'])
				{
					case 'label_only':
						$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("label"), $_POST["export_format"]), $format_bold);
						break;
					case 'title_only':
						$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_bold);
						break;
					default:
						$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("title"), $_POST["export_format"]), $format_bold);
						$column++;
						$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("label"), $_POST["export_format"]), $format_bold);
						break;
				}
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("question"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("question_type"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("users_answered"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("users_skipped"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("mode"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("mode_text"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("median"), $_POST["export_format"]), $format_bold);
				$column++;
				$mainworksheet->writeString(0, $column, ilExcelUtils::_convert_text($this->lng->txt("arithmetic_mean"), $_POST["export_format"]), $format_bold);
				break;
			case (TYPE_SPSS):
				$csvfile = array();
				$csvrow = array();
				switch ($_POST['export_label'])
				{
					case 'label_only':
						array_push($csvrow, $this->lng->txt("label"));
						break;
					case 'title_only':
						array_push($csvrow, $this->lng->txt("title"));
						break;
					default:
						array_push($csvrow, $this->lng->txt("title"));
						array_push($csvrow, $this->lng->txt("label"));
						break;
				}
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
					$counter = $question->setExportCumulatedXLS($mainworksheet, $format_title, $format_bold, $eval, $counter, $_POST['export_label']);
					break;
				case (TYPE_SPSS):
					$csvrows =& $question->setExportCumulatedCVS($eval, $_POST['export_label']);
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
						$question->setExportDetailsXLS($workbook, $format_title, $format_bold, $eval, $_POST['export_label']);
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
	
	public function exportData()
	{
		if (strlen($_POST["export_format"]))
		{
			$this->exportCumulatedResults(0);
			return;
		}
		else
		{
			$this->ctrl->redirect($this, 'evaluation');
		}
	}
	
	public function exportDetailData()
	{
		if (strlen($_POST["export_format"]))
		{
			$this->exportCumulatedResults(1);
			return;
		}
		else
		{
			$this->ctrl->redirect($this, 'evaluation');
		}
	}
	
	public function printEvaluation()
	{
		ilUtil::sendInfo($this->lng->txt('use_browser_print_function'), true);
		$this->ctrl->redirect($this, 'evaluation');
	}
	
	function evaluation($details = 0)
	{
		global $ilUser;
		global $rbacsystem;
		global $ilias;
		global $ilToolbar;

		if (!$rbacsystem->checkAccess("read",$_GET["ref_id"]))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"));
			return;
		}		
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$format = new ilSelectInputGUI("", "export_format");
		$format->setOptions(array(
			"excel" => $this->lng->txt('exp_type_excel'),
			"csv" => $this->lng->txt('exp_type_csv')
			));
		$ilToolbar->addInputItem($format);
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$label = new ilSelectInputGUI("", "export_label");
		$label->setOptions(array(
			'label_only' => $this->lng->txt('export_label_only'), 
			'title_only' => $this->lng->txt('export_title_only'), 
			'title_label'=> $this->lng->txt('export_title_label')
			));
		$ilToolbar->addInputItem($label);
		
		if ($details)
		{
			$ilToolbar->addFormButton($this->lng->txt("export"), 'exportDetailData');			
		}
		else
		{
			$ilToolbar->addFormButton($this->lng->txt("export"), 'exportData');
		}
		
		switch ($this->object->getEvaluationAccess())
		{
			case EVALUATION_ACCESS_OFF:
				if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
				{
					ilUtil::sendFailure($this->lng->txt("permission_denied"));
					return;
				}
				break;
			case EVALUATION_ACCESS_ALL:
				include_once "./Modules/Survey/classes/class.ilObjSurveyAccess.php";
				if (!($rbacsystem->checkAccess("write",$_GET["ref_id"]) || ilObjSurveyAccess::_hasEvaluationAccess($this->object->getId(), $ilUser->getId())))
				{
					ilUtil::sendFailure($this->lng->txt("permission_denied"));
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", "Modules/Survey");
		$questions =& $this->object->getSurveyQuestions();
		$data = array();
		$counter = 1;
		$last_questionblock_id = null;
		foreach ($questions as $qdata)
		{			
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question_gui = SurveyQuestion::_instanciateQuestionGUI($qdata["question_id"]);
			$question = $question_gui->object;
			$c = $question->getCumulatedResultData($this->object->getSurveyId(), $counter);
			if (is_array($c[0]))
			{
				foreach ($c as $a)
				{
					array_push($data, $a);
				}
			}
			else
			{
				array_push($data, $c);
			}
			$counter++;
			if ($details)
			{								
				// questionblock title handling
				if($qdata["questionblock_id"] && $qdata["questionblock_id"] != $last_questionblock_id)
				{
					$qblock = $this->object->getQuestionblock($qdata["questionblock_id"]);
					if($qblock["show_blocktitle"])
					{
						$this->tpl->setCurrentBlock("detail_qblock");
						$this->tpl->setVariable("BLOCKTITLE", $qdata["questionblock_title"]);		
						$this->tpl->parseCurrentBlock();						
					}
					
					$last_questionblock_id = $qdata["questionblock_id"];
				}
				
				$detail = $question_gui->getCumulatedResultsDetails($this->object->getSurveyId(), $counter-1);
				$this->tpl->setCurrentBlock("detail");
				$this->tpl->setVariable("DETAIL", $detail);				
				$this->tpl->parseCurrentBlock();
			}
		}
		
		include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsCumulatedTableGUI.php";
		$table_gui = new ilSurveyResultsCumulatedTableGUI($this, 'evaluation', $detail);
		$table_gui->setData($data);
		$this->tpl->setVariable('CUMULATED', $table_gui->getHTML());	
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'evaluation'));
	}
	
	/**
	* Export the user specific results for the survey
	*
	* Export the user specific results for the survey
	*
	* @access private
	*/
	function exportUserSpecificResults($export_format, $export_label = "")
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
			$question->addUserSpecificResultsExportTitles($csvrow, $export_label);
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
			$wt = $this->object->getWorkingtimeForParticipant($user_id);
			array_push($csvrow, $wt);
			array_push($csvfile, $csvrow);
		}
		switch ($export_format)
		{
			case TYPE_XLS:
				include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
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
				include_once "./Services/Excel/classes/class.ilExcelUtils.php";
				$contentstartrow = 0;
				foreach ($csvfile as $csvrow)
				{
					$col = 0;
					if ($row == 0)
					{
						$worksheet = 0;
						$mainworksheet =& $worksheets[$worksheet];
						foreach ($csvrow as $text)
						{
							if (is_array($text))
							{
								$textcount = 0;
								foreach ($text as $string)
								{
									$mainworksheet->writeString($row + $textcount, $col, ilExcelUtils::_convert_text($string, $_POST["export_format"]), $format_title);
									$textcount++;
									$contentstartrow = max($contentstartrow, $textcount);
								}
								$col++;
							}
							else
							{
								$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($text, $_POST["export_format"]), $format_title);
							}
							if ($col % 251 == 0) 
							{
								$worksheet++;
								$col = 1;
								$mainworksheet =& $worksheets[$worksheet];
								$mainworksheet->writeString($row, 0, ilExcelUtils::_convert_text($csvrow[0], $_POST["export_format"]), $format_title);
							}
						}
						$mainworksheet->writeString($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('workingtime'), $_POST['export_format']), $format_title);
						$row = $contentstartrow;
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
		global $ilAccess, $ilLog, $ilToolbar;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			ilUtil::sendFailure($this->lng->txt("no_permission"), TRUE);
			$this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
		}
		if (!is_array($_POST))
		{
			$_POST = array();
		}
		if (array_key_exists("export_format", $_POST))
		{
			return $this->exportUserSpecificResults($_POST["export_format"], $_POST["export_label"]);
		}
		
		$userResults =& $this->object->getUserSpecificResults();	
		$questions =& $this->object->getSurveyQuestions(true);
		$participants =& $this->object->getSurveyParticipants();
		$tabledata = array();
		foreach ($participants as $data)
		{
			$questioncounter = 1;
			$question = "";
			$results = "";
			$first = true;
			foreach ($questions as $question_id => $question_data)
			{
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
				$wt = $this->object->getWorkingtimeForParticipant($data['active_id']);
				if ($first)
				{
					array_push($tabledata, array(
							'username' => $data["sortname"],
							'gender' => $data["gender"],
							'question' => $questioncounter++ . ". " . $question_data["title"],
							'results' => $text,
							'workingtime' => $wt
						));
					$first = false;
				}
				else
				{
					array_push($tabledata, array(
							'username' => " ",
							'gender' => " ",
							'question' => $questioncounter++ . ". " . $question_data["title"],
							'results' => $text,
							'workingtime' => null
						));
				}
			}
		}
		
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Survey/templates/default/evaluation_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "evaluationuser"));

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$format = new ilSelectInputGUI("", "export_format");
		$format->setOptions(array(
			"excel" => $this->lng->txt('exp_type_excel'),
			"csv" => $this->lng->txt('exp_type_csv')
			));
		$ilToolbar->addInputItem($format);
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$label = new ilSelectInputGUI("", "export_label");
		$label->setOptions(array(
			'label_only' => $this->lng->txt('export_label_only'), 
			'title_only' => $this->lng->txt('export_title_only'), 
			'title_label'=> $this->lng->txt('export_title_label')
			));
		$ilToolbar->addInputItem($label);
		
		$ilToolbar->addFormButton($this->lng->txt("export"), 'evaluationuser');		
		
		$ilToolbar->addSeparator();
		
		$ilToolbar->addButton($this->lng->txt("print"), "#", "", "", "onclick=\"javascript:window.print()\"");
		
		include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsUserTableGUI.php";
		$table_gui = new ilSurveyResultsUserTableGUI($this, 'evaluationuser', $this->object->getAnonymize());
		$table_gui->setData($tabledata);
		$this->tpl->setContent($table_gui->getHTML());			
	}
}

?>