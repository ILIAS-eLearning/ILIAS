<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const TYPE_XLS = "excel";
	const TYPE_SPSS = "csv";
	
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $appr_id = null;
	
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
		if ($this->object->get360Mode())
		{
			$this->determineAppraiseeId();
		}
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		if ($this->object->get360SkillService() && $skmg_set->isActivated())
		{
			$cmd = $this->ctrl->getCmd("competenceEval");
		}
		else
		{
			$cmd = $this->ctrl->getCmd("evaluation");
		}
		
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$this->setEvalSubTabs();
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
	* Set the tabs for the evaluation output
	*
	* @access private
	*/
	function setEvalSubtabs()
	{
		global $ilTabs;
		global $ilAccess;

		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		if ($this->object->get360SkillService() && $skmg_set->isActivated())
		{
			$ilTabs->addSubTabTarget(
				"svy_eval_competences", 
				$this->ctrl->getLinkTarget($this, "competenceEval"), 
				array("competenceEval")
			);
		}

		$ilTabs->addSubTabTarget(
			"svy_eval_cumulated", 
			$this->ctrl->getLinkTarget($this, "evaluation"), 
			array("evaluation", "checkEvaluationAccess")
		);

		$ilTabs->addSubTabTarget(
			"svy_eval_detail", 
			$this->ctrl->getLinkTarget($this, "evaluationdetails"), 
			array("evaluationdetails")
		);
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addSubTabTarget(
				"svy_eval_user", 
				$this->ctrl->getLinkTarget($this, "evaluationuser"), 
				array("evaluationuser")
			);
		}
	}

	
	/**
	 * Set appraisee id
	 *
	 * @param int $a_val appraisee id	
	 */
	function setAppraiseeId($a_val)
	{
		$this->appr_id = $a_val;
	}
	
	/**
	 * Get appraisee id
	 *
	 * @return int appraisee id
	 */
	function getAppraiseeId()
	{
		return $this->appr_id;
	}
	
	/**
	 * Determine appraisee id
	 */
	function determineAppraiseeId()
	{
		global $ilUser, $rbacsystem;
		
		$appr_id = "";
		
		// always start with current user
		if ($_REQUEST["appr_id"] == "")
		{
			$req_appr_id = $ilUser->getId();
		}
		else
		{
			$req_appr_id = (int) $_REQUEST["appr_id"];
		}
		
		// write access? allow selection
		if ($req_appr_id > 0)
		{
			$all_appr = ($this->object->get360Results() == ilObjSurvey::RESULTS_360_ALL);
			
			$valid = array();				
			foreach($this->object->getAppraiseesData() as $item)
			{				
				if ($item["closed"] &&
					($item["user_id"] == $ilUser->getId() ||
					$rbacsystem->checkAccess("write", $this->object->getRefId()) ||
					$all_appr))
				{
					$valid[] = $item["user_id"];
				}				
			}
			if(in_array($req_appr_id, $valid))
			{
				$appr_id = $req_appr_id;
			}
			else 
			{
				// current selection / user is not valid, use 1st valid instead
				$appr_id = array_shift($valid);
			}				
		}
		
		$this->ctrl->setParameter($this, "appr_id", $appr_id);		
		$this->setAppraiseeId($appr_id);	
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
		global $ilUser;
		
		if($this->object->getAnonymize() == 1 && 
			$_SESSION["anon_evaluation_access"] == $_GET["ref_id"])
		{
			return true;
		}
		
		include_once "Modules/Survey/classes/class.ilObjSurveyAccess.php";
		if(ilObjSurveyAccess::_hasEvaluationAccess(ilObject::_lookupObjId($_GET["ref_id"]), $ilUser->getId()))
		{
			if($this->object->getAnonymize() == 1)
			{
				$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
			}
			return true;
		}
		
		if($this->object->getAnonymize() == 1)
		{
			// autocode
			$surveycode = $this->object->getUserAccessCode($ilUser->getId());		
			if ($this->object->isAnonymizedParticipant($surveycode))
			{
				$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
				return true;
			}
			
			/* try to find code for current (registered) user from existing run		
			if($this->object->findCodeForUser($ilUser->getId()))
			{
				$_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
				return true;
			}		
			*/
			
			// code needed
			$this->tpl->setVariable("TABS", "");
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_checkaccess.html", "Modules/Survey");
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("AUTHENTICATION_NEEDED", $this->lng->txt("svy_check_evaluation_authentication_needed"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "checkEvaluationAccess"));
			$this->tpl->setVariable("EVALUATION_CHECKACCESS_INTRODUCTION", $this->lng->txt("svy_check_evaluation_access_introduction"));
			$this->tpl->setVariable("VALUE_CHECK", $this->lng->txt("ok"));
			$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TEXT_SURVEY_CODE", $this->lng->txt("survey_code"));
			$this->tpl->parseCurrentBlock();
		}
		
		$_SESSION["anon_evaluation_access"] = null;
		return false;
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
		global $ilCtrl;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		global $tree;
		$path = $tree->getPathFull($this->object->getRefID());
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
			$path[count($path) - 2]["child"]);
		$ilCtrl->redirectByClass("ilrepositorygui", "frameset");
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
		
		switch ($_POST["export_format"])
		{
			case self::TYPE_XLS:
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
			
			case self::TYPE_SPSS:
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
		
		$finished_ids = null;
		if($this->object->get360Mode())
		{
			$appr_id = $_REQUEST["appr_id"];
			if(!$appr_id)
			{
				$this->ctrl->redirect($this, $details ? "evaluationdetails" : "evaluation");
			}			
			$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);	
			if(!sizeof($finished_ids))
			{
				$finished_ids = array(-1);
			}
		}
				
		$questions =& $this->object->getSurveyQuestions();
		$counter++;
		foreach ($questions as $data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question = SurveyQuestion::_instanciateQuestion($data["question_id"]);		
			$eval = $this->object->getCumulatedResults($question, $finished_ids);
			switch ($_POST["export_format"])
			{
				case self::TYPE_XLS:
					$counter = $question->setExportCumulatedXLS($mainworksheet, $format_title, $format_bold, $eval, $counter, $_POST['export_label']);
					break;
				
				case self::TYPE_SPSS:
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
					case self::TYPE_XLS:
						$question->setExportDetailsXLS($workbook, $format_title, $format_bold, $eval, $_POST['export_label']);
						break;
				}
			}
		}
		
		// #11179
		if(!$details)
		{
			$type = $this->lng->txt("svy_eval_cumulated");
		}
		else
		{
			$type = $this->lng->txt("svy_eval_detail");
		}		
		$surveyname = $this->object->getTitle()." ".$type." ".date("Y-m-d");
		$surveyname = preg_replace("/\s/", "_", trim($surveyname));
		$surveyname = ilUtil::getASCIIFilename($surveyname);
		
		switch ($_POST["export_format"])
		{
			case self::TYPE_XLS:
				// Let's send the file
				$workbook->close();
				ilUtil::deliverFile($excelfile, "$surveyname.xls", "application/vnd.ms-excel");
				exit();
				break;
			
			case self::TYPE_SPSS:
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
		global $rbacsystem, $ilToolbar;

		// auth
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{			
			if (!$rbacsystem->checkAccess("read",$_GET["ref_id"]))
			{
				ilUtil::sendFailure($this->lng->txt("permission_denied"));
				return;
			}		
				
			switch ($this->object->getEvaluationAccess())
			{
				case ilObjSurvey::EVALUATION_ACCESS_OFF:
					ilUtil::sendFailure($this->lng->txt("permission_denied"));
					return;

				case ilObjSurvey::EVALUATION_ACCESS_ALL:				
				case ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS:
					if(!$this->checkAnonymizedEvaluationAccess())
					{
						ilUtil::sendFailure($this->lng->txt("permission_denied"));
						return;
					}
					break;
			}
		}
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", "Modules/Survey");
		
		$data = null;

		if($this->object->get360Mode())
		{				
			$appr_id = $this->getAppraiseeId();
			$this->addApprSelectionToToolbar();
		}

		if(!$this->object->get360Mode() || $appr_id)
		{
			$format = new ilSelectInputGUI("", "export_format");
			$format->setOptions(array(
				self::TYPE_XLS => $this->lng->txt('exp_type_excel'),
				self::TYPE_SPSS => $this->lng->txt('exp_type_csv')
				));
			$ilToolbar->addInputItem($format);

			$label = new ilSelectInputGUI("", "export_label");
			$label->setOptions(array(
				'label_only' => $this->lng->txt('export_label_only'), 
				'title_only' => $this->lng->txt('export_title_only'), 
				'title_label'=> $this->lng->txt('export_title_label')
				));
			$ilToolbar->addInputItem($label);

			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";		
			$button = ilSubmitButton::getInstance();
			$button->setCaption("export");			
			if ($details)
			{
				$button->setCommand('exportDetailData');					
			}
			else
			{
				$button->setCommand('exportData');				
			}
			$button->setOmitPreventDoubleSubmission(true);
			$ilToolbar->addButtonInstance($button);	
			
			$finished_ids = null;
			if($appr_id)
			{
				$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);	
				if(!sizeof($finished_ids))
				{
					$finished_ids = array(-1);
				}
			}
			
			$questions =& $this->object->getSurveyQuestions();
			$data = array();
			$counter = 1;
			$last_questionblock_id = null;
			foreach ($questions as $qdata)
			{			
				include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
				$question_gui = SurveyQuestion::_instanciateQuestionGUI($qdata["question_id"]);
				$question = $question_gui->object;
				$c = $question->getCumulatedResultData($this->object->getSurveyId(), $counter, $finished_ids);
				if (is_array($c[0]))
				{
					// keep only "main" entry - sub-items will be handled in tablegui
					// this will enable proper sorting
					$main = array_shift($c);
					$main["subitems"] = $c;
					array_push($data, $main);					
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

					$detail = $question_gui->getCumulatedResultsDetails($this->object->getSurveyId(), $counter-1, $finished_ids);
					$this->tpl->setCurrentBlock("detail");
					$this->tpl->setVariable("DETAIL", $detail);				
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		
		include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsCumulatedTableGUI.php";
		$table_gui = new ilSurveyResultsCumulatedTableGUI($this, $details ? 'evaluationdetails' : 'evaluation', $detail);
		$table_gui->setData($data);
		$this->tpl->setVariable('CUMULATED', $table_gui->getHTML());	
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'evaluation'));		
	}
	
	/**
	 * Add appraisee selection to toolbar
	 *
	 * @param
	 * @return
	 */
	function addApprSelectionToToolbar()
	{
		global $ilToolbar, $rbacsystem;
		
		if($this->object->get360Mode())
		{
			$appr_id = $this->getAppraiseeId();

			$options = array();
			if(!$appr_id)
			{
				$options[""] = $this->lng->txt("please_select");
			}
			$no_appr = true;
			foreach($this->object->getAppraiseesData() as $item)
			{
				if($item["closed"])
				{
					$options[$item["user_id"]] = $item["login"];
					$no_appr = false;
				}
			}

			if(!$no_appr)
			{								
				if ($rbacsystem->checkAccess("write", $this->object->getRefId()) ||
					$this->object->get360Results() == ilObjSurvey::RESULTS_360_ALL)
				{			
					include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
					$appr = new ilSelectInputGUI($this->lng->txt("survey_360_appraisee"), "appr_id");
					$appr->setOptions($options);
					$appr->setValue($this->getAppraiseeId());
					$ilToolbar->addInputItem($appr, true);
					
					include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";		
					$button = ilSubmitButton::getInstance();
					$button->setCaption("survey_360_select_appraisee");								
					$button->setCommand($this->ctrl->getCmd());															
					$ilToolbar->addButtonInstance($button);	
	
					if($appr_id)
					{
						$ilToolbar->addSeparator();												
					}
				}
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("survey_360_no_closed_appraisees"));				
			}
		}

	}
	
	
	/**
	* Export the user specific results for the survey
	*
	* Export the user specific results for the survey
	*
	* @access private
	*/
	function exportUserSpecificResults($export_format, $export_label, $finished_ids)
	{
		global $ilLog;
		
		// #13620
		ilDatePresentation::setUseRelativeDates(false);
		
		$csvfile = array();
		$csvrow = array();
		$csvrow2 = array();
		$questions = array();
		$questions =& $this->object->getSurveyQuestions(true);		
		array_push($csvrow, $this->lng->txt("lastname")); // #12756
		array_push($csvrow, $this->lng->txt("firstname"));
		array_push($csvrow, $this->lng->txt("login"));
		array_push($csvrow, $this->lng->txt('workingtime')); // #13622
		array_push($csvrow, $this->lng->txt('survey_results_finished'));
		array_push($csvrow2, "");
		array_push($csvrow2, "");
		array_push($csvrow2, "");
		array_push($csvrow2, "");
		array_push($csvrow2, "");
		if ($this->object->canExportSurveyCode())
		{
			array_push($csvrow, $this->lng->txt("codes"));
			array_push($csvrow2, "");
		}
		/* #8211
		if ($this->object->getAnonymize() == ilObjSurvey::ANONYMIZE_OFF)
		{
			array_push($csvrow, $this->lng->txt("gender"));
		}		 
	    */
		$cellcounter = 1;
		
		foreach ($questions as $question_id => $question_data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question = SurveyQuestion::_instanciateQuestion($question_data["question_id"]);
			switch ($export_label)
			{
				case "label_only":
					$question->addUserSpecificResultsExportTitles($csvrow, true);					
					break;
					
				case "title_only":
					$question->addUserSpecificResultsExportTitles($csvrow, false);	
					break;
					
				default:
					$question->addUserSpecificResultsExportTitles($csvrow, false);		
					$question->addUserSpecificResultsExportTitles($csvrow2, true, false);		
					break;
			}
			
			$questions[$question_data["question_id"]] = $question;
		}
		array_push($csvfile, $csvrow);
		if(sizeof($csvrow2) && implode("", $csvrow2))
		{
			array_push($csvfile, $csvrow2);
		}				
		if(!$finished_ids)
		{
			$participants =& $this->object->getSurveyFinishedIds();
		}
		else
		{
			$participants = $finished_ids;
		}
		$finished_data = array();
		foreach($this->object->getSurveyParticipants($participants) as $item)
		{
			$finished_data[$item["active_id"]] = $item;
		}
		foreach ($participants as $user_id)
		{		
			if($user_id < 1)
			{
				continue;
			}
			
			$resultset =& $this->object->getEvaluationByUser($questions, $user_id);			
			$csvrow = array();
			
			// #12756			
			array_push($csvrow, (trim($resultset["lastname"])) 
				? $resultset["lastname"] 
				: $resultset["name"]); // anonymous
			array_push($csvrow, $resultset["firstname"]);
			
			array_push($csvrow, $resultset["login"]); // #10579
			if ($this->object->canExportSurveyCode())
			{
				array_push($csvrow, $user_id);
			}
			/* #8211
			if ($this->object->getAnonymize() == ilObjSurvey::ANONYMIZE_OFF)
			{
				array_push($csvrow, $resultset["gender"]);
			}			
		    */
			$wt = $this->object->getWorkingtimeForParticipant($user_id);
			array_push($csvrow, $wt);
			
			$finished = $finished_data[$user_id];
			if((bool)$finished["finished"])
			{
				if($export_format == self::TYPE_XLS)
				{				
					// see ilObjUserFolder::createExcelExport()
					$date = strftime("%Y-%m-%d %H:%M:%S", $finished["finished_tstamp"]);
					if(preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $date, $matches))
					{
						array_push($csvrow, array("excelTime", ilUtil::excelTime($matches[1],$matches[2],$matches[3],$matches[4],$matches[5],$matches[6])));
					}			
				}			
				else
				{
					array_push($csvrow, ilDatePresentation::formatDate(new ilDateTime($finished["finished_tstamp"], IL_CAL_UNIX)));
				}
			}
			else
			{
				array_push($csvrow, "-");
			}			
			
			foreach ($questions as $question_id => $question)
			{
				$question->addUserSpecificResultsData($csvrow, $resultset);
			}			
			
			array_push($csvfile, $csvrow);
		}
		
		// #11179
		$surveyname = $this->object->getTitle()." ".$this->lng->txt("svy_eval_user")." ".date("Y-m-d");
		$surveyname = preg_replace("/\s/", "_", trim($surveyname));
		$surveyname = ilUtil::getASCIIFilename($surveyname);
		
		switch ($export_format)
		{
			case self::TYPE_XLS:
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
						$row = $contentstartrow;
					}
					else
					{
						$worksheet = 0;
						$mainworksheet =& $worksheets[$worksheet];
						foreach ($csvrow as $text)
						{
							if (is_array($text) && $text[0] == "excelTime")
							{
								$mainworksheet->write($row, $col++, $text[1], $format_datetime);
							}							
							else if (is_numeric($text))
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
				
			case self::TYPE_SPSS:
				$csv = "";
				$separator = ";";				
				foreach ($csvfile as $idx => $csvrow)
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
	
	function exportEvaluationUser()
	{
		$finished_ids = null;
		if($this->object->get360Mode())
		{
			$appr_id = $_REQUEST["appr_id"];
			if(!$appr_id)
			{
				$this->ctrl->redirect($this, "evaluationuser");
			}			
			$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);	
			if(!sizeof($finished_ids))
			{
				$finished_ids = array(-1);
			}
		}
		
		return $this->exportUserSpecificResults($_POST["export_format"], $_POST["export_label"], $finished_ids);						
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
		global $ilAccess, $ilToolbar;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			ilUtil::sendFailure($this->lng->txt("no_permission"), TRUE);
			$this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "evaluationuser"));
		
		if($this->object->get360Mode())
		{				
			$appr_id = $this->getAppraiseeId();
			$this->addApprSelectionToToolbar();
		}

		$tabledata = null;
		if(!$this->object->get360Mode() || $appr_id)
		{
			$format = new ilSelectInputGUI("", "export_format");
			$format->setOptions(array(
				self::TYPE_XLS => $this->lng->txt('exp_type_excel'),
				self::TYPE_SPSS => $this->lng->txt('exp_type_csv')
				));
			$ilToolbar->addInputItem($format);

			$label = new ilSelectInputGUI("", "export_label");
			$label->setOptions(array(
				'label_only' => $this->lng->txt('export_label_only'), 
				'title_only' => $this->lng->txt('export_title_only'), 
				'title_label'=> $this->lng->txt('export_title_label')
				));
			$ilToolbar->addInputItem($label);
			
			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
			$button = ilSubmitButton::getInstance();
			$button->setCaption("export");
			$button->setCommand('exportevaluationuser');
			$button->setOmitPreventDoubleSubmission(true);
			$ilToolbar->addButtonInstance($button);		

			$ilToolbar->addSeparator();

			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("print");
			$button->setOnClick("window.print(); return false;");
			$button->setOmitPreventDoubleSubmission(true);
			$ilToolbar->addButtonInstance($button);		
			
			$finished_ids = null;
			if($appr_id)
			{
				$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);	
				if(!sizeof($finished_ids))
				{
					$finished_ids = array(-1);
				}
			}

			$userResults =& $this->object->getUserSpecificResults($finished_ids);	
			$questions =& $this->object->getSurveyQuestions(true);
			$participants =& $this->object->getSurveyParticipants($finished_ids);
			$tabledata = array();	
			$counter = -1;
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
					if (strlen($text) == 0) $text = ilObjSurvey::getSurveySkippedValue();
					$wt = $this->object->getWorkingtimeForParticipant($data['active_id']);
					if ($first)
					{
						if($data["finished"])
						{
							$finished =  $data["finished_tstamp"];
						}	
						else
						{
							$finished = false;
						}
						$tabledata[++$counter] = array(
								'username' => $data["sortname"],
								// 'gender' => $data["gender"],
								'question' => $questioncounter++ . ". " . $question_data["title"],
								'results' => $text,
								'workingtime' => $wt,
								'finished' => $finished
							);
						$first = false;						
					}
					else
					{
						$tabledata[$counter]["subitems"][] = array(
								'username' => " ",
								// 'gender' => " ",
								'question' => $questioncounter++ . ". " . $question_data["title"],
								'results' => $text,
								'workingtime' => null,
								'finished' => null
							);
					}
				}
			}
		}
		
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Survey/templates/default/evaluation_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();				
		
		include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsUserTableGUI.php";
		$table_gui = new ilSurveyResultsUserTableGUI($this, 'evaluationuser', $this->object->hasAnonymizedResults());
		$table_gui->setData($tabledata);
		$this->tpl->setContent($table_gui->getHTML());			
	}
	
	/**
	 * Competence Evaluation
	 *
	 * @param
	 * @return
	 */
	function competenceEval()
	{
		global $ilUser, $lng, $ilCtrl, $ilToolbar, $tpl, $ilTabs;
		
		$survey = $this->object;
		
		$ilTabs->activateSubtab("svy_eval_competences");
		$ilTabs->activateTab("svy_results");

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "competenceEval"));
		
		if($this->object->get360Mode())
		{				
			$appr_id = $this->getAppraiseeId();
			$this->addApprSelectionToToolbar();
		}
		
		if ($appr_id == 0)
		{
			return;
		}
		
		// evaluation modes
		$eval_modes = array();
		
		// get all competences of survey
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$sskill = new ilSurveySkill($survey);
		$opts = $sskill->getAllAssignedSkillsAsOptions();
		$skills = array();
		foreach ($opts as $id => $o)
		{
			$idarr = explode(":", $id);
			$skills[$id] = array("id" => $id, "title" => $o, "profiles" => array(),
				"base_skill" => $idarr[0], "tref_id" => $idarr[1]);
		}
//var_dump($opts);
		
		// get matching user competence profiles
		// -> add gap analysis to profile
		include_once("./Services/Skill/classes/class.ilSkillProfile.php");
		$profiles = ilSkillProfile::getProfilesOfUser($appr_id);
		foreach ($profiles as $p)
		{
			$prof = new ilSkillProfile($p["id"]);
			$prof_levels = $prof->getSkillLevels();
			foreach ($prof_levels as $pl)
			{
				if (isset($skills[$pl["base_skill_id"].":".$pl["tref_id"]]))
				{
					$skills[$pl["base_skill_id"].":".$pl["tref_id"]]["profiles"][] =
						$p["id"];

					$eval_modes["gap_".$p["id"]] =
						$lng->txt("svy_gap_analysis").": ".$prof->getTitle();
				}
			}
		}
//var_dump($skills);
//var_dump($eval_modes);

		// if one competence does not match any profiles
		// -> add "competences of survey" alternative
		reset($skills);
		foreach ($skills as $sk)
		{
			if (count($sk["profiles"]) == 0)
			{
				$eval_modes["skills_of_survey"] = $lng->txt("svy_all_survey_competences");
			}
		}
		
		// final determination of current evaluation mode
		$comp_eval_mode = $_GET["comp_eval_mode"];
		if ($_POST["comp_eval_mode"] != "")
		{
			$comp_eval_mode = $_POST["comp_eval_mode"];
		}
		
		if (!isset($eval_modes[$comp_eval_mode]))
		{
			reset($eval_modes);
			$comp_eval_mode = key($eval_modes);
			$ilCtrl->setParameter($this, "comp_eval_mode", $comp_eval_mode);
		}
		
		$ilCtrl->saveParameter($this, "comp_eval_mode");
		
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$mode_sel = new ilSelectInputGUI($lng->txt("svy_analysis"), "comp_eval_mode");
		$mode_sel->setOptions($eval_modes);
		$mode_sel->setValue($comp_eval_mode);
		$ilToolbar->addInputItem($mode_sel, true);
		
		$ilToolbar->addFormButton($lng->txt("select"), "competenceEval");

		if (substr($comp_eval_mode, 0, 4) == "gap_")
		{
			// gap analysis
			$profile_id = (int) substr($comp_eval_mode, 4);
			
			include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
			$pskills_gui = new ilPersonalSkillsGUI();
			$pskills_gui->setProfileId($profile_id);
			$pskills_gui->setGapAnalysisActualStatusModePerObject($survey->getId(), $lng->txt("survey_360_raters"));
			if ($survey->getFinishedIdForAppraiseeIdAndRaterId($appr_id, $appr_id) > 0)
			{
				$sskill = new ilSurveySkill($survey);
				$self_levels = array();
				foreach ($sskill->determineSkillLevelsForAppraisee($appr_id, true) as $sl)
				{
					$self_levels[$sl["base_skill_id"]][$sl["tref_id"]] = $sl["new_level_id"];
				}
				$pskills_gui->setGapAnalysisSelfEvalLevels($self_levels);
			}
			$html = $pskills_gui->getGapAnalysisHTML($appr_id);
			
			$tpl->setContent($html);
		}
		else // must be all survey competences
		{
			include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
			$pskills_gui = new ilPersonalSkillsGUI();
			$pskills_gui->setGapAnalysisActualStatusModePerObject($survey->getId(), $lng->txt("survey_360_raters"));
			if ($survey->getFinishedIdForAppraiseeIdAndRaterId($appr_id, $appr_id) > 0)
			{
				$sskill = new ilSurveySkill($survey);
				$self_levels = array();
				foreach ($sskill->determineSkillLevelsForAppraisee($appr_id, true) as $sl)
				{
					$self_levels[$sl["base_skill_id"]][$sl["tref_id"]] = $sl["new_level_id"];
				}
				$pskills_gui->setGapAnalysisSelfEvalLevels($self_levels);
			}
			$sk = array();
			foreach ($skills as $skill)
			{
				$sk[] = array(
					"base_skill_id" => (int) $skill["base_skill"],
					"tref_id" => (int) $skill["tref_id"]
					);
			}
			$html = $pskills_gui->getGapAnalysisHTML($appr_id, $sk);

			$tpl->setContent($html);
		}
		
	}
}

?>