<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestion.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionResult.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionVariable.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionUnit.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionUnitCategory.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';

/**
 * Single choice question GUI representation
 * The assFormulaQuestionGUI class encapsulates the GUI representation
 * for single choice questions.
 * @author            Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version           $Id: class.assFormulaQuestionGUI.php 1235 2010-02-15 15:21:18Z hschottm $
 * @ingroup           ModulesTestQuestionPool
 */
class assFormulaQuestionGUI extends assQuestionGUI
{
	/**
	 * assFormulaQuestionGUI constructor
	 * The constructor takes possible arguments an creates an instance of the assFormulaQuestionGUI object.
	 * @param integer $id The database id of a multiple choice question object
	 * @access public
	 */
	function __construct($id = -1)
	{
		parent::__construct();
		$this->object    = new assFormulaQuestion();
		$this->newUnitId = null;
		if($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	 * Sets the ILIAS tabs for this question type
	 * Sets the ILIAS tabs for this question type
	 * @access public
	 */
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;

		$ilTabs->clearTargets();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if(strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_page",
					$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}

			$this->addTab_QuestionPreview($ilTabs);
		}

		$force_active = false;
		if($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";

			if($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$commands = $_POST["cmd"];
			if(is_array($commands))
			{
				foreach($commands as $key => $value)
				{
					if(preg_match("/^suggestrange_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array(
					"editQuestion", "save", "cancel", "addSuggestedSolution",
					"cancelExplorer", "linkChilds", "removeSuggestedSolution",
					"parseQuestion", "saveEdit", "suggestRange"
				),
				$classname, "", $force_active);
		}

		if($_GET["q_id"])
		{
			// add tab for question feedback within common class assQuestionGUI
			$this->addTab_QuestionFeedback($ilTabs);
		}

		if($_GET["q_id"])
		{
			// add tab for question hint within common class assQuestionGUI
			$this->addTab_QuestionHints($ilTabs);
		}

		// Unit editor
		if($_GET['q_id'])
		{
			// add tab for question hint within common class assQuestionGUI
			$this->addTab_Units($ilTabs);
		}

		// Assessment of questions sub menu entry
		if($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}

		$this->addBackTab($ilTabs);
	}

	function getCommand($cmd)
	{
		if(preg_match("/suggestrange_(.*?)/", $cmd, $matches))
		{
			$cmd = "suggestRange";
		}
		return $cmd;
	}

	/**
	 * Suggest a range for a result
	 * @access public
	 */
	function suggestRange()
	{
		if($this->writePostData())
		{
			ilUtil::sendInfo($this->getErrorMessage());
		}
		$this->editQuestion();
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 */
	public function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		$checked = true;
		if(!$hasErrors)
		{
			$this->object->setTitle($_POST["title"]);
			$this->object->setAuthor($_POST["author"]);
			$this->object->setComment($_POST["comment"]);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripOnlySlashes($_POST["question"]);
			$this->object->setQuestion($questiontext);
			$this->object->setEstimatedWorkingTime(
				$_POST["Estimated"]["hh"],
				$_POST["Estimated"]["mm"],
				$_POST["Estimated"]["ss"]
			);

			$this->object->parseQuestionText();
			$found_vars    = array();
			$found_results = array();

			
			foreach($_POST as $key => $value)
			{
				if(preg_match("/^unit_(\\\$v\d+)$/", $key, $matches))
				{
					array_push($found_vars, $matches[1]);
				}
				if(preg_match("/^unit_(\\\$r\d+)$/", $key, $matches))
				{
					array_push($found_results, $matches[1]);
				}
			}

//			if(!$this->object->checkForDuplicateVariables())
//			{
//				
//				$this->addErrorMessage($this->lng->txt("err_duplicate_variables"));
//				$checked = FALSE;
//			}
			if(!$this->object->checkForDuplicateResults())
			{
				$this->addErrorMessage($this->lng->txt("err_duplicate_results"));
				$checked = FALSE;
			}

			foreach($found_vars as $variable)
			{
				if($this->object->getVariable($variable) != null)
				{
					$varObj = new assFormulaQuestionVariable($variable, $_POST["range_min_$variable"], $_POST["range_max_$variable"], $this->object->getUnitrepository()->getUnit($_POST["unit_$variable"]), $_POST["precision_$variable"], $_POST["intprecision_$variable"]);
					$varObj->setRangeMinTxt($_POST["range_min_$variable"]);
					$varObj->setRangeMaxTxt($_POST["range_max_$variable"]);
					$this->object->addVariable($varObj);
				}
			}

			$tmp_form_vars = array();
			$tmp_quest_vars = array();
			foreach($found_results as $result)
			{
				$tmp_res_match = preg_match_all("/([$][v][0-9]*)/", $_POST["formula_$result"], $form_vars);
				$tmp_form_vars = array_merge($tmp_form_vars,$form_vars[0]);

				$tmp_que_match = preg_match_all("/([$][v][0-9]*)/", $_POST['question'] , $quest_vars);
				$tmp_quest_vars= array_merge($tmp_quest_vars,$quest_vars[0]);
			}
			$result_has_undefined_vars = array_diff($tmp_form_vars, $found_vars);
			$question_has_unused_vars =  array_diff($tmp_quest_vars, $tmp_form_vars);

			if(count($result_has_undefined_vars) > 0 || count($question_has_unused_vars) > 0)
			{
				$error_message = '';
				if(count($result_has_undefined_vars) > 0)
				{
					$error_message .= $this->lng->txt("res_contains_undef_var"). '<br>';
				}
				if(count($question_has_unused_vars) > 0)
				{
					$error_message .= $this->lng->txt("que_contains_unused_var");
				}
				$checked =  false;
				if($this->isSaveCommand())
				{
					ilUtil::sendFailure($error_message);
				}
			}
			foreach($found_results as $result)
			{
				if(is_object($this->object->getUnitrepository()->getUnit($_POST["unit_$result"])))
				{
					$tmp_result_unit = $this->object->getUnitrepository()->getUnit($_POST["unit_$result"]);
				}
				else 
				{
					$tmp_result_unit = NULL;
				}
				
				if($this->object->getResult($result) != null)
				{
					$use_simple_rating = ($_POST["rating_advanced_$result"] == 1) ? FALSE : TRUE;
					$resObj = new assFormulaQuestionResult(
						$result,
						$_POST["range_min_$result"],
						$_POST["range_max_$result"],
						$_POST["tolerance_$result"],

						$tmp_result_unit,
						$_POST["formula_$result"],
						$_POST["points_$result"],
						$_POST["precision_$result"],
						$use_simple_rating,
						($_POST["rating_advanced_$result"] == 1) ? $_POST["rating_sign_$result"] : "",
						($_POST["rating_advanced_$result"] == 1) ? $_POST["rating_value_$result"] : "",
						($_POST["rating_advanced_$result"] == 1) ? $_POST["rating_unit_$result"] : "",
						$_POST["result_type_$result"] != 0 ? $_POST["result_type_$result"] : 0
					);
					$resObj->setRangeMinTxt($_POST["range_min_$result"]);
					$resObj->setRangeMaxTxt($_POST["range_max_$result"]);
					$this->object->addResult($resObj);
					$this->object->addResultUnits($resObj, $_POST["units_$result"]);
				}
			}
			if($checked == false)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return 1;
		}
	}

	function isSaveCommand()
	{
		return in_array($this->ctrl->getCmd(), array('saveFQ', 'saveEdit', 'saveReturnFQ'));
	}

	/**
	 * Creates an output of the edit form for the question
	 * @param bool $checkonly
	 * @return bool
	 */
	function editQuestion($checkonly = FALSE)
	{
		$save = $this->isSaveCommand();
		
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth('100%');
		$form->setId('assformulaquestion');

		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);
		
		// Add info text
		$question = $form->getItemByPostVar('question');
		$question->setInfo($this->lng->txt('fq_question_desc'));

		$variables         = $this->object->getVariables();
		$categorized_units = $this->object->getUnitrepository()->getCategorizedUnits();
		$result_units      = $this->object->__get('resultunits');
		
		$unit_options  = array();
		$category_name = '';
		$new_category  = false;
		foreach((array)$categorized_units as $item)
		{
			/**
			 * @var $item assFormulaQuestionUnitCategory|assFormulaQuestionUnit
			 */
			if($item instanceof assFormulaQuestionUnitCategory)
			{
				if($category_name != $item->getDisplayString())
				{
					$new_category  = true;
					$category_name = $item->getDisplayString();
				}
				continue;
			}
			$unit_options[$item->getId()] = $item->getDisplayString() . ($new_category ? ' (' . $category_name . ')' : '');
			$new_category                 = false;
		}

		if(count($variables))
		{
			uasort($variables, function(assFormulaQuestionVariable $v1, assFormulaQuestionVariable $v2) {
				$num_v1 = (int)substr($v1->getVariable(), 2);
				$num_v2 = (int)substr($v2->getVariable(), 2);
				if($num_v1 > $num_v2)
				{
					return 1;
				}
				else if($num_v1 < $num_v2)
				{
					return -1;
				}

				return 0;
			});

			foreach($variables as $variable)
			{
				/**
				 * @var $variable assFormulaQuestionVariable
				 */
				$variable_header = new ilFormSectionHeaderGUI();
				$variable_header->setTitle(sprintf($this->lng->txt('variable_x'), $variable->getVariable()));
				
				$range_min = new ilNumberInputGUI($this->lng->txt('range_min'), 'range_min_' . $variable->getVariable());
				$range_min->allowDecimals(true);
				$range_min->setSize(3);
				$range_min->setRequired(true);
				$range_min->setValue($variable->getRangeMin());

				$range_max = new ilNumberInputGUI($this->lng->txt('range_max'), 'range_max_' . $variable->getVariable());
				$range_max->allowDecimals(true);
				$range_max->setSize(3);
				$range_max->setRequired(true);
				$range_max->setValue($variable->getRangeMax());

				$units = new ilSelectInputGUI($this->lng->txt('unit'), 'unit_' . $variable->getVariable());
				$units->setOptions(array(0 => $this->lng->txt('no_selection')) + $unit_options);
				if(is_object($variable->getUnit()))
				{
					$units->setValue($variable->getUnit()->getId());
				}

				$precision = new ilNumberInputGUI($this->lng->txt('precision'), 'precision_' . $variable->getVariable());
				$precision->setRequired(true);
				$precision->setSize(3);
				$precision->setMinValue(0);
				$precision->setValue($variable->getPrecision());
				$precision->setInfo($this->lng->txt('fq_precision_info'));

				$intprecision = new ilNumberInputGUI($this->lng->txt('intprecision'), 'intprecision_' . $variable->getVariable());
				$intprecision->setSize(3);
				$intprecision->setMinValue(1);
				$intprecision->setValue($variable->getIntprecision());
				$intprecision->setInfo($this->lng->txt('intprecision_info'));

				$form->addItem($variable_header);
				$form->addItem($range_min);
				$form->addItem($range_max);
				$form->addItem($units);
				$form->addItem($precision);
				$form->addItem($intprecision);
			}
		}

		$results = $this->object->getResults();
		if(count($results))
		{
			require_once 'Services/Form/classes/class.ilMultiSelectInputGUI.php';

			uasort($results, function(assFormulaQuestionResult $r1, assFormulaQuestionResult $r2) {
				$num_r1 = (int)substr($r1->getResult(), 2);
				$num_r2 = (int)substr($r2->getResult(), 2);
				if($num_r1 > $num_r2)
				{
					return 1;
				}
				else if($num_r1 < $num_r2)
				{
					return -1;
				}

				return 0;
			});

			foreach($results as $result)
			{
				/**
				 * @var $result assFormulaQuestionResult
				 */
				$result_header = new ilFormSectionHeaderGUI();
				$result_header->setTitle(sprintf($this->lng->txt('result_x'), $result->getResult()));
				
				$formula = new ilTextInputGUI($this->lng->txt('formula'), 'formula_' . $result->getResult());
				$formula->setInfo($this->lng->txt('fq_formula_desc'));
				$formula->setRequired(true);
				$formula->setSize(50);
				$formula->setValue($result->getFormula());
				$formula->setSuffix(' = ' . $result->getResult());

				if(
					preg_match("/suggestrange_(.*)/", $this->ctrl->getCmd(), $matches) &&
					strcmp($matches[1], $result->getResult()) == 0
				)
				{
					// suggest a range for the result
					if(strlen($result->substituteFormula($variables, $results)))
					{
						$result->suggestRange($variables, $results);
					}
				}

				$range_min = new ilNumberInputGUI($this->lng->txt('range_min'), 'range_min_' . $result->getResult());
				$range_min->allowDecimals(true);
				$range_min->setSize(3);
				$range_min->setRequired(true);
				$range_min->setValue($result->getRangeMin());

				$range_max = new ilNumberInputGUI($this->lng->txt('range_max'), 'range_max_' . $result->getResult());
				$range_max->allowDecimals(true);
				$range_max->setSize(3);
				$range_max->setRequired(true);
				$range_max->setValue($result->getRangeMax());

				$matches = array();

				$precision = new ilNumberInputGUI($this->lng->txt('precision'), 'precision_' . $result->getResult());
				$precision->setRequired(true);
				$precision->setSize(3);
				$precision->setMinValue(0);
				$precision->setInfo($this->lng->txt('fq_precision_info'));
				$precision->setValue($result->getPrecision());

				$tolerance = new ilNumberInputGUI($this->lng->txt('tolerance'), 'tolerance_' . $result->getResult());
				$tolerance->setSize(3);
				$tolerance->setMinValue(0);
				$tolerance->setMaxValue(100);
				$tolerance->allowDecimals(false);
				$tolerance->setInfo($this->lng->txt('tolerance_info'));								
				$tolerance->setValue($result->getTolerance());
				
				$suggest_range_button = new ilCustomInputGUI('', '');
				$suggest_range_button->setHtml('<input type="submit" class="btn btn-default" name="cmd[suggestrange_'.$result->getResult().']" value="'.$this->lng->txt("suggest_range").'" />');

				$sel_result_units = new ilSelectInputGUI($this->lng->txt('unit'), 'unit_' . $result->getResult());
				$sel_result_units->setOptions(array(0 => $this->lng->txt('no_selection')) + $unit_options);
				$sel_result_units->setInfo($this->lng->txt('result_unit_info'));
				if(is_object($result->getUnit()))
				{
					$sel_result_units->setValue($result->getUnit()->getId());
				}
				
				$mc_result_units = new ilMultiSelectInputGUI($this->lng->txt('result_units'), 'units_' . $result->getResult());
				$mc_result_units->setOptions($unit_options);
				$mc_result_units->setInfo($this->lng->txt('result_units_info'));
				$selectedvalues = array();
				foreach($unit_options as $unit_id => $txt)
				{
					if($this->hasResultUnit($result, $unit_id, $result_units))
					{
						$selectedvalues[] = $unit_id;
					}
				}
				$mc_result_units->setValue($selectedvalues);
				
				$result_type = new ilRadioGroupInputGUI($this->lng->txt('result_type_selection'), 'result_type_' . $result->getResult());
				$result_type->setRequired(true);

				$no_type = new ilRadioOption($this->lng->txt('no_result_type'), 0);
				$no_type->setInfo($this->lng->txt('fq_no_restriction_info'));

				$result_dec = new ilRadioOption($this->lng->txt('result_dec'), 1);
				$result_dec->setInfo($this->lng->txt('result_dec_info'));

				$result_frac = new ilRadioOption($this->lng->txt('result_frac'), 2);
				$result_frac->setInfo($this->lng->txt('result_frac_info'));

				$result_co_frac = new ilRadioOption($this->lng->txt('result_co_frac'), 3);
				$result_co_frac->setInfo($this->lng->txt('result_co_frac_info'));

				$result_type->addOption($no_type);
				$result_type->addOption($result_dec);
				$result_type->addOption($result_frac);
				$result_type->addOption($result_co_frac);
				$result_type->setValue(strlen($result->getResultType()) ? $result->getResultType() : 0);

				$points = new ilNumberInputGUI($this->lng->txt('points'), 'points_' . $result->getResult());
				$points->allowDecimals(true);
				$points->setRequired(true);
				$points->setSize(3);
				$points->setMinValue(0);
				$points->setValue(strlen($result->getPoints()) ? $result->getPoints() : 1);

				$rating_type = new ilCheckboxInputGUI($this->lng->txt('advanced_rating'), 'rating_advanced_' . $result->getResult());
				$rating_type->setValue(1);
				$rating_type->setInfo($this->lng->txt('advanced_rating_info'));
				
				if(!$save)
				{
					$advanced_rating = $this->canUseAdvancedRating($result);
					if(!$advanced_rating)
					{
						$rating_type->setDisabled(true);
						$rating_type->setChecked(false);
					}
					else
					{
						$rating_type->setChecked(strlen($result->getRatingSimple()) && $result->getRatingSimple() ? false : true);
					}
				}

				$sign = new ilNumberInputGUI($this->lng->txt('rating_sign'), 'rating_sign_' . $result->getResult());
				$sign->setRequired(true);
				$sign->setSize(3);
				$sign->setMinValue(0);
				$sign->setValue($result->getRatingSign());
				$rating_type->addSubItem($sign);

				$value = new ilNumberInputGUI($this->lng->txt('rating_value'), 'rating_value_' . $result->getResult());
				$value->setRequired(true);
				$value->setSize(3);
				$value->setMinValue(0);
				$value->setValue($result->getRatingValue());
				$rating_type->addSubItem($value);

				$unit = new ilNumberInputGUI($this->lng->txt('rating_unit'), 'rating_unit_' . $result->getResult());
				$unit->setRequired(true);
				$unit->setSize(3);
				$unit->setMinValue(0);
				$unit->setValue($result->getRatingUnit());
				$rating_type->addSubItem($unit);
				
				$info_text = new ilNonEditableValueGUI($this->lng->txt('additional_rating_info'));
				$rating_type->addSubItem($info_text);

				$form->addItem($result_header);
				$form->addItem($formula);
				$form->addItem($range_min);
				$form->addItem($range_max);
				$form->addItem($suggest_range_button);
				$form->addItem($precision);
				$form->addItem($tolerance);
				$form->addItem($sel_result_units);
				$form->addItem($mc_result_units);
				$form->addItem($result_type);
				$form->addItem($points);
				$form->addItem($rating_type);
			}

			$defined_result_vars = array();
			$quest_vars = array();

			$defined_result_res = array();
			$result_vars = array();
			
			foreach($variables as $key => $object)
			{
				$quest_vars[$key] = $key;
			}

			foreach($results as $key => $object)
			{
				$result_vars[$key] = $key;
			}
			
			foreach($results as $tmp_result)
			{
				/**
				 * @var $tmp_result assFormulaQuestionResult
				 */
				$formula = $tmp_result->getFormula();

				preg_match_all("/([$][v][0-9]*)/", $formula, $form_vars);
				preg_match_all("/([$][r][0-9]*)/", $formula, $form_res);
				foreach($form_vars[0] as $res_var)
				{
					$defined_result_vars[$res_var] = $res_var;	
				}

				foreach($form_res[0] as $res_res)
				{
					$defined_result_res[$res_res] = $res_res;
				}
			}
		}
		
		$result_has_undefined_vars = array();
		$question_has_unused_vars = array();
		
		if(is_array($quest_vars) && count($quest_vars) > 0)
		{
			$result_has_undefined_vars = array_diff($defined_result_vars, $quest_vars);
			$question_has_unused_vars = array_diff($quest_vars, $defined_result_vars);
		}

		if(is_array($result_vars) && count($result_vars) > 0)
		{
			$result_has_undefined_res = array_diff($defined_result_res, $result_vars);
					
		}
		$error_message = '';
		
		if(count($result_has_undefined_vars) > 0 || count($question_has_unused_vars) > 0)
		{
			if(count($result_has_undefined_vars) > 0)
			{
				$error_message .= $this->lng->txt("res_contains_undef_var"). '<br>';
			}
			if(count($question_has_unused_vars) > 0)
			{
				$error_message .= $this->lng->txt("que_contains_unused_var"). '<br>';
			}
	
			$checked =  false;
			if($save)
			{
				ilUtil::sendFailure($error_message);
			}
		}

		if(count($result_has_undefined_res) > 0)
		{
			$error_message .= $this->lng->txt("res_contains_undef_res"). '<br>';
			$checked =  false;
		}
		
		if($save && !$checked)
		{
			ilUtil::sendFailure($error_message);
		}
		
		if($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue($this->object->getId());
			$form->addItem($hidden);
		}

		$this->populateTaxonomyFormSection($form);

		$form->addCommandButton('parseQuestion', $this->lng->txt("parseQuestion"));
		$form->addCommandButton('saveReturnFQ', $this->lng->txt("save_return"));
		$form->addCommandButton('saveFQ', $this->lng->txt("save"));
		
		$errors = $checked;

		if($save)
		{
			$found_vars    = array();
			$found_results = array();
			foreach((array)$_POST as $key => $value)
			{
				if(preg_match("/^unit_(\\\$v\d+)$/", $key, $matches))
				{
					array_push($found_vars, $matches[1]);
				}
				if(preg_match("/^unit_(\\\$r\d+)$/", $key, $matches))
				{
					array_push($found_results, $matches[1]);
				}
			}
			
			$form->setValuesByPost();
			$errors = !$form->checkInput();

			$custom_errors = false;
			if(count($variables))
			{
				foreach($variables as $variable)
				{
					/**
					 * @var $variable assFormulaQuestionVariable
					 */
					$min_range = $form->getItemByPostVar('range_min_' . $variable->getVariable());
					$max_range = $form->getItemByPostVar('range_max_' . $variable->getVariable());
					if($min_range->getValue() > $max_range->getValue())
					{
						$min_range->setAlert($this->lng->txt('err_range'));
						$max_range->setAlert($this->lng->txt('err_range'));
						$custom_errors = true;
					}
				}
			}

			if(count($results))
			{
				foreach($results as $result)
				{
					/**
					 * @var $result assFormulaQuestionResult
					 */
					$min_range = $form->getItemByPostVar('range_min_' . $result->getResult());
					$max_range = $form->getItemByPostVar('range_max_' . $result->getResult());
					if($min_range->getValue() > $max_range->getValue())
					{
						$min_range->setAlert($this->lng->txt('err_range'));
						$max_range->setAlert($this->lng->txt('err_range'));
						$custom_errors = true;
					}


					$formula = $form->getItemByPostVar('formula_' . $result->getResult());
					if(strpos($formula->getValue(), $result->getResult()) !== FALSE)
					{
						$formula->setAlert($this->lng->txt('errRecursionInResult'));
						$custom_errors = true;
					}
					
					$result_unit    = $form->getItemByPostVar('unit_' . $result->getResult());
					$rating_advanced = $form->getItemByPostVar('rating_advanced_' . $result->getResult());
					if(((int)$result_unit->getValue() <= 0) && $rating_advanced->getChecked())
					{
						unset($_POST['rating_advanced_' . $result->getResult()]);
						$rating_advanced->setDisabled(true);
						$rating_advanced->setChecked(false);
						$rating_advanced->setAlert($this->lng->txt('err_rating_advanced_not_allowed'));
						$custom_errors = true;
					}
					else if($rating_advanced->getChecked())
					{
						$rating_sign  = $form->getItemByPostVar('rating_sign_' . $result->getResult());
						$rating_value = $form->getItemByPostVar('rating_value_' . $result->getResult());
						$rating_unit  = $form->getItemByPostVar('rating_unit_' . $result->getResult());
						
						$percentage = $rating_sign->getValue() + $rating_value->getValue() + $rating_unit->getValue();
						if($percentage != 100)
						{
							$rating_advanced->setAlert($this->lng->txt('err_wrong_rating_advanced'));
							$custom_errors = true;
						}
					}

					preg_match_all("/([$][v][0-9]*)/", $formula->getValue(), $form_vars);
					$result_has_undefined_vars = array_diff($form_vars[0], (array)$found_vars);
					if(count($result_has_undefined_vars))
					{
						$errors = true;
						ilUtil::sendInfo($this->lng->txt('res_contains_undef_var'));
					}
				}
			}
			
			if($custom_errors && !$errors)
			{
				$errors = true;
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			}
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if($errors)
			{
				$checkonly = false;
			}
		}

		if(!$checkonly)
		{
			$this->tpl->setVariable('QUESTION_DATA', $form->getHTML());	
		}
		return $errors;
	}

	private function hasResultUnit($result, $unit_id, $resultunits)
	{
		if (array_key_exists($result->getResult(), $resultunits))
		{
			if (array_key_exists($unit_id, $resultunits[$result->getResult()])) return TRUE;
		}
		return FALSE;
	}

	/**
	 * Check if advanced rating can be used for a result. This is only possible if there is exactly
	 * one possible correct unit for the result, otherwise it is impossible to determine wheather the
	 * unit is correct or the value.
	 *
	 * @return boolean True if advanced rating could be used, false otherwise
	 */
	private function canUseAdvancedRating($result)
	{
		$resultunit = $result->getUnit();

		/*
		 *  if there is a result-unit (unit selectbox) selected it is possible to use advanced rating
		 * 	if there is no result-unit selected it is NOT possible to use advanced rating, because there is no 
		 * 	definition if the result-value or the unit-value should be the correct solution!!
		 * 
		 */
		if(is_object($resultunit))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function parseQuestion()
	{
		$this->writePostData();
		$this->editQuestion();
	}
	
	public function saveReturnFQ()
	{
		global $ilUser;
		$old_id = $_GET["q_id"];
		$result = $this->writePostData();
		if ($result == 0)
		{
			$ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
			$ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
			$this->saveTaxonomyAssignments();
			$this->object->saveToDb();
			$originalexists = $this->object->_questionExistsInPool($this->object->original_id);
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			if (($_GET["calling_test"] || (isset($_GET['calling_consumer']) && (int)$_GET['calling_consumer'])) && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
			{
				$this->ctrl->redirect($this, "originalSyncForm");
				return;
			}
			elseif ($_GET["calling_test"])
			{
				require_once 'Modules/Test/classes/class.ilObjTest.php';
				$test = new ilObjTest($_GET["calling_test"]);
				#var_dump(assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId()));
				$q_id = $this->object->getId();
				if(!assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId()))
				{
					global $tree, $ilDB, $ilPluginAdmin;

					include_once("./Modules/Test/classes/class.ilObjTest.php");
					$_GET["ref_id"] = $_GET["calling_test"];
					$test = new ilObjTest($_GET["calling_test"], true);

					require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
					$testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

					$new_id = $test->insertQuestion(
						$testQuestionSetConfigFactory->getQuestionSetConfig(), $this->object->getId()
					);

					$q_id = $new_id;
					if(isset($_REQUEST['prev_qid']))
					{
						$test->moveQuestionAfter($this->object->getId() + 1, $_REQUEST['prev_qid']);
					}

					$this->ctrl->setParameter($this, 'q_id', $new_id);
					$this->ctrl->setParameter($this, 'calling_test', $_GET['calling_test']);
					#$this->ctrl->setParameter($this, 'test_ref_id', false);

				}
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
				if($_REQUEST['test_express_mode'])
				{
					ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($q_id));
				}
				else
				{
					ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["calling_test"]);
				}
			}
			else
			{
				if ($this->object->getId() !=  $old_id)
				{
					$this->callNewIdListeners($this->object->getId());
					ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
					$this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
				}
				if (strcmp($_SESSION["info"], "") != 0)
				{
					ilUtil::sendSuccess($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), true);
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
				}
				$this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
			}
		}
		else
		{
			$ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
			$ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
			$this->object->saveToDb();
			$this->editQuestion();
		}
	}

	public function saveFQ()
	{
		$result = $this->writePostData();

		if($result == 1)
		{
			$this->editQuestion();
		}
		else
		{
			$this->saveTaxonomyAssignments();
			$this->save();
		}
	}
	/**
	 * check input fields
	 */
	function checkInput()
	{
		if((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			$this->addErrorMessage($this->lng->txt("fill_out_all_required_fields"));
			return FALSE;
		}


		return TRUE;
	}

	/**
	 * Get the question solution output
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @return The solution output of the question as HTML code
	 */
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
	)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if(($active_id > 0) && (!$show_correct_solution))
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if(!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if(is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$user_solution["active_id"] = $active_id;
			$user_solution["pass"]      = $pass;
			$solutions                  =& $this->object->getSolutionValues($active_id, $pass);
			foreach($solutions as $idx => $solution_value)
			{
				if(preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches))
				{
					$user_solution[$matches[1]] = $solution_value["value2"];
				}
				else if(preg_match("/^(\\\$r\\d+)$/", $solution_value["value1"], $matches))
				{
					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["value"] = $solution_value["value2"];
				}
				else if(preg_match("/^(\\\$r\\d+)_unit$/", $solution_value["value1"], $matches))
				{
					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["unit"] = $solution_value["value2"];
				}
			}
		}
		else if($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if(!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if(is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$user_solution = $this->object->getBestSolution($this->object->getSolutionValues($active_id, $pass));
		}
		elseif( is_object($this->getPreviewSession()) )
		{
			$solutionValues = array();
			
			foreach($this->getPreviewSession()->getParticipantsSolution() as $val1 => $val2)
			{
				$solutionValues[] = array('value1' => $val1, 'value2' => $val2);
			}
			
			$user_solution = $this->object->getBestSolution($solutionValues);
		}
	
		$template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution.html", true, true, 'Modules/TestQuestionPool');
		$questiontext = $this->object->substituteVariables($user_solution, $graphicalOutput, TRUE, $result_output, $this->getPreviewSession());

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput   = $template->get();
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get();
		if(!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}

	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		$user_solution = array();
		
		if( is_object($this->getPreviewSession()) )
		{
			$solutions = $this->getPreviewSession()->getParticipantsSolution();
	
			foreach($solutions as $val1 => $val2)
			{
				if(preg_match("/^(\\\$v\\d+)$/", $val1, $matches))
				{
					$user_solution[$matches[1]] = $val2;
				}
				else if(preg_match("/^(\\\$r\\d+)$/", $val1, $matches))
				{
	
					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["value"] = $val2;
				}
				else if(preg_match("/^(\\\$r\\d+)_unit$/", $val1, $matches))
				{
					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["unit"] = $val2;
				}
	
				if(preg_match("/^(\\\$r\\d+)/", $val1, $matches) && $user_solution[$matches[1]]["result_type"] == 0)
				{
					$user_solution[$matches[1]]["result_type"] = assFormulaQuestionResult::getResultTypeByQstId($this->object->getId(), $val1);
				}
			}
		}

		$template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output.html", true, true, 'Modules/TestQuestionPool');
		if( is_object($this->getPreviewSession()) )
		{
			$questiontext = $this->object->substituteVariables($user_solution, false, false, false, $this->getPreviewSession());
		}
		else
		{
			$questiontext = $this->object->substituteVariables();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		if(!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		ilUtil::sendInfo($this->lng->txt('enter_valid_values'));
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = null;
		if($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if(is_null($pass)) $pass = ilObjTest::_getPass($active_id);

			$user_solution["active_id"] = $active_id;
			$user_solution["pass"]      = $pass;
			$solutions =& $this->object->getSolutionValues($active_id, $pass);

			foreach($solutions as $idx => $solution_value)
			{
				if(preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches))
				{
					$user_solution[$matches[1]] = $solution_value["value2"];
				}
				else if(preg_match("/^(\\\$r\\d+)$/", $solution_value["value1"], $matches))
				{

					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["value"] = $solution_value["value2"];
				}
				else if(preg_match("/^(\\\$r\\d+)_unit$/", $solution_value["value1"], $matches))
				{
					if(!array_key_exists($matches[1], $user_solution)) $user_solution[$matches[1]] = array();
					$user_solution[$matches[1]]["unit"] = $solution_value["value2"];
				}

				if(preg_match("/^(\\\$r\\d+)/", $solution_value["value1"], $matches) && $user_solution[$matches[1]]["result_type"] == 0)
				{
					$user_solution[$matches[1]]["result_type"] = assFormulaQuestionResult::getResultTypeByQstId($this->object->getId(), $solution_value["value1"]);
				}
			}
		}

		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output.html", true, true, 'Modules/TestQuestionPool');

		$questiontext = $this->object->substituteVariables($user_solution);

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));

		$questionoutput = $template->get();
		$pageoutput     = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	public function getSpecificFeedbackOutput($active_id, $pass)
	{
		return '';
	}
}
