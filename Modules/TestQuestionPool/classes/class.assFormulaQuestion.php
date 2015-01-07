<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionResult.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionVariable.php";
include_once "./Modules/TestQuestionPool/classes/class.ilUnitConfigurationRepository.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php";
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for single choice questions
 * assFormulaQuestion is a class for single choice questions.
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestion.php 1236 2010-02-15 15:44:16Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 */
class assFormulaQuestion extends assQuestion implements iQuestionCondition
{
	private $variables;
	private $results;
	private $resultunits;

	/**
	 * @var ilUnitConfigurationRepository
	 */
	private $unitrepository;

	/**
	 * assFormulaQuestion constructor
	 * The constructor takes possible arguments an creates an instance of the assFormulaQuestion object.
	 * @param string  $title    A title string to describe the question
	 * @param string  $comment  A comment string to describe the question
	 * @param string  $author   A string containing the name of the questions author
	 * @param integer $owner    A numerical ID to identify the owner/creator
	 * @param string  $question The question string of the single choice question
	 * @access public
	 * @see    assQuestion:assQuestion()
	 */
	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->variables        = array();
		$this->results          = array();
		$this->resultunits      = array();
		$this->unitrepository   = new ilUnitConfigurationRepository(0);

	}

	public function clearVariables()
	{
		$this->variables = array();
	}

	public function getVariables()
	{
		return $this->variables;
	}

	public function getVariable($variable)
	{
		if(array_key_exists($variable, $this->variables))
		{
			return $this->variables[$variable];
		}
		return null;
	}

	public function addVariable($variable)
	{
		$this->variables[$variable->getVariable()] = $variable;
	}

	public function clearResults()
	{
		$this->results = array();
	}

	public function getResults()
	{
		return $this->results;
	}

	public function getResult($result)
	{
		if(array_key_exists($result, $this->results))
		{
			return $this->results[$result];
		}
		return null;
	}

	public function addResult($result)
	{
		$this->results[$result->getResult()] = $result;
	}

	public function addResultUnits($result, $unit_ids)	
	{
		$this->resultunits[$result->getResult()] = array();
		if((!is_object($result)) || (!is_array($unit_ids))) return;
		foreach($unit_ids as $id)
		{
			if(is_numeric($id) && ($id > 0)) $this->resultunits[$result->getResult()][$id] = $this->getUnitrepository()->getUnit($id);
		}
	}

	public function addResultUnit($result, $unit)
	{
		if(is_object($result) && is_object($unit))
		{
			if(!is_array($this->resultunits[$result->getResult()]))
			{
				$this->resultunits[$result->getResult()] = array();
			}
			$this->resultunits[$result->getResult()][$unit->getId()] = $unit;
	
		}
	}

	public function getResultUnits($result)
	{
		if(array_key_exists($result->getResult(), $this->resultunits))
		{
			return $this->resultunits[$result->getResult()];
		}
		else
		{
			return array();
		}
	}

	public function hasResultUnit($result, $unit_id)
	{
		if(array_key_exists($result->getResult(), $this->resultunits))
		{
			if(array_key_exists($unit_id, $this->resultunits[$result->getResult()]))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	public function parseQuestionText()
	{
		$this->clearResults();
		$this->clearVariables();
		if(preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches))
		{
			foreach($matches[1] as $variable)
			{
				$varObj = new assFormulaQuestionVariable($variable, 0, 0, null, 0);
				$this->addVariable($varObj);
			}
		}

		if(preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches))
		{
			foreach($rmatches[1] as $result)
			{
				$resObj = new assFormulaQuestionResult($result, NULL, NULL, 0, -1, NULL, 1, 1, TRUE);
				$this->addResult($resObj);
			}
		}
	}

	public function checkForDuplicateVariables()
	{
		if(preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches))
		{
			if((count(array_unique($matches[1]))) != count($matches[1])) return false;
		}
		return true;
	}

	public function checkForDuplicateResults()
	{
		if(preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches))
		{
			if((count(array_unique($rmatches[1]))) != count($rmatches[1])) return false;
		}
		return true;
	}
	
	public function substituteVariables($userdata = null, $graphicalOutput = FALSE, $forsolution = FALSE, $result_output = FALSE, ilAssQuestionPreviewSession $previewSession = null)
	{
		global $ilDB;
		
		if((count($this->results) == 0) && (count($this->variables) == 0)) 
			return false;
		
		$text = $this->getQuestion();
		if(preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches))
		{
			foreach($rmatches[1] as $result)
			{
				$resObj = $this->getResult($result);
				$resObj->findValidRandomVariables($this->getVariables(), $this->getResults());
			}
		}
		if(preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches))
		{
			foreach($matches[1] as $variable)
			{
				$varObj = $this->getVariable($variable);
				if(is_array($userdata))
				{
					if(strlen($userdata[$varObj->getVariable()]))
					{
						$value = $userdata[$varObj->getVariable()];
						$varObj->setValue($value);
					}
					elseif( is_object($previewSession) )
					{
						$userSolution = $previewSession->getParticipantsSolution();
						$userSolution[$varObj->getVariable()] = $varObj->getValue();
						$previewSession->setParticipantsSolution($userSolution);
					}
					else
					{
						// save value to db
						$this->saveCurrentSolution($userdata["active_id"], $userdata["pass"], $variable,$varObj->getValue());
					}
				}
				$unit = (is_object($varObj->getUnit())) ? $varObj->getUnit()->getUnit() : "";
				$val  = (strlen($varObj->getValue()) > 8) ? strtoupper(sprintf("%e", $varObj->getValue())) : $varObj->getValue();
				$text = preg_replace("/\\$" . substr($variable, 1) . "(?![0-9]+)/", $val . " " . $unit . "\\1", $text);
			}
		}
		if(preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches))
		{
			foreach($rmatches[1] as $result)
			{
				$resObj = $this->getResult($result);
				$value  = "";
				$frac_helper = '';
				$user_data[$result]['result_type'] = $resObj->getResultType();

				if(
					$resObj->getResultType() == assFormulaQuestionResult::RESULT_FRAC ||
					$resObj->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC
				)
				{
					$is_frac = true;
				}
				if(is_array($userdata))
				{
					if(is_array($userdata[$result]))
					{
						if($forsolution && $result_output)
						{
							$value_org = $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId());
							$value = sprintf("%." . $resObj->getPrecision() . "f", $value_org);
							if($is_frac)
							{
								$value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($value_org);
								if(is_array($value))
								{
									$frac_helper = $value[1];
									$value =  $value[0];
								}
							}
						}
						else
						{
							if($forsolution)
							{
								$value = $userdata[$result]["value"];
							}
							else
							{
								$value = ' value="' . $userdata[$result]["value"] . '"';
							}
						}
					}
				}
				else
				{
					if($forsolution)
					{
						$value = $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId());
						$value = sprintf("%." . $resObj->getPrecision() . "f", $value);

						if($is_frac)
						{
							$value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($value);
							if(is_array($value))
							{
								$frac_helper = $value[1];
								$value = $value[0];
							}
							$value = ' value="' . $value . '"';
						}
					}
					else
					{
						// Precision fix for Preview by tjoussen
						// If all default values are set, this function is called in getPreview
						$use_precision = !($userdata == null && $graphicalOutput == FALSE && $forsolution == FALSE && $result_output == FALSE);

						$val   = $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId(), $use_precision);

						if($resObj->getResultType() == assFormulaQuestionResult::RESULT_FRAC
							||$resObj->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC)
						{
							$val = $resObj->convertDecimalToCoprimeFraction($val);
							if(is_array($val))
							{
								$frac_helper = $val[1];
								$val = $val[0];
							}
						}
						else
						{
							$val   = sprintf("%." . $resObj->getPrecision() . "f", $val);
							$val   = (strlen($val) > 8) ? strtoupper(sprintf("%e", $val)) : $val;
						}
						$value = ' value="' . $val . '"';
					}
				}

				if($forsolution)
				{
					$input = '<span class="solutionbox">' . ilUtil::prepareFormOutput($value) . '</span>';
				}
				else
				{
					$input = '<input type="text" spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off" name="result_' . $result . '"' . $value . ' />';
				}
				
				$units = "";
				if(count($this->getResultUnits($resObj)) > 0)
				{
					if($forsolution)
					{
						if(is_array($userdata))
						{
							foreach($this->getResultUnits($resObj) as $unit)
							{
								if($userdata[$result]["unit"] == $unit->getId())
								{
									$units = $unit->getUnit();
								}
							}
						}
						else
						{
							if($resObj->getUnit())
							{
								$units = $resObj->getUnit()->getUnit();
							}
						}
					}
					else
					{
						$units = '<select name="result_' . $result . '_unit">';
						$units .= '<option value="-1">' . $this->lng->txt("select_unit") . '</option>';
						foreach($this->getResultUnits($resObj) as $unit)
						{
							$units .= '<option value="' . $unit->getId() . '"';
							if((is_array($userdata[$result])) && (strlen($userdata[$result]["unit"])))
							{
								if($userdata[$result]["unit"] == $unit->getId())
								{
									$units .= ' selected="selected"';
								}
							}
							$units .= '>' . $unit->getUnit() . '</option>';
						}
						$units .= '</select>';
					}
				}
				else
				{
					$units = "";
				}
				switch($resObj->getResultType())
				{
					case assFormulaQuestionResult::RESULT_DEC:
						$units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_dec');
						break;
					case assFormulaQuestionResult::RESULT_FRAC:
						if(strlen($frac_helper))
						{
							$units .= ' &asymp; ' . $frac_helper . ', ';
						}
						elseif (is_array($userdata) && isset($userdata[$result]) && strlen($userdata[$result]["frac_helper"]))
						{
							if(!preg_match('-/-',  $value))
							{
								$units .= ' &asymp; ' . $userdata[$result]["frac_helper"] . ', ';
							}
						}
						$units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_frac');
						break;
					case assFormulaQuestionResult::RESULT_CO_FRAC:
						if(strlen($frac_helper))
						{
							$units .= ' &asymp; ' . $frac_helper . ', ';
						}
						elseif (is_array($userdata) && isset($userdata[$result]) && strlen($userdata[$result]["frac_helper"]))
						{
							if(!preg_match('-/-',  $value))
							{
								$units .= ' &asymp; ' . $userdata[$result]["frac_helper"] . ', ';
							}
						}
						$units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_co_frac');
						break;
					case assFormulaQuestionResult::RESULT_NO_SELECTION:
						break;
				}
				$checkSign = "";
				if($graphicalOutput)
				{
					$resunit    = null;
					$user_value = '';
					if(is_array($userdata) && is_array($userdata[$result]))
					{
						if($userdata[$result]["unit"] > 0)
						{
							$resunit = $this->getUnitrepository()->getUnit($userdata[$result]["unit"]);
						}

						if(isset($userdata[$result]["value"]))
						{
							$user_value = $userdata[$result]["value"];
						}
					}

					$template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_image.html", true, true, 'Modules/TestQuestionPool');

					if($resObj->isCorrect($this->getVariables(), $this->getResults(), $user_value, $resunit))
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_not_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
					$checkSign = $template->get();
				}
				$resultOutput = "";
				if($result_output)
				{
					$template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_result.html", true, true, 'Modules/TestQuestionPool');

					if(is_array($userdata))
					{
						$found = $resObj->getResultInfo($this->getVariables(), $this->getResults(), $userdata[$resObj->getResult()]["value"], $userdata[$resObj->getResult()]["unit"], $this->getUnitrepository()->getUnits());
					}
					else
					{
						$found = $resObj->getResultInfo($this->getVariables(), $this->getResults(), $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId()), is_object($resObj->getUnit()) ? $resObj->getUnit()->getId() : NULL, $this->getUnitrepository()->getUnits());
					}
					$resulttext = "(";
					if($resObj->getRatingSimple())
					{
						if($frac_helper)
						{
							$resulttext .="n/a";
						}
						else
						{
							$resulttext .= $found['points'] . " " . (($found['points'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points'));
						}
					}
					else
					{
						$resulttext .= $this->lng->txt("rated_sign") . " " . (($found['sign']) ? $found['sign'] : 0) . " " . (($found['sign'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points')) . ", ";
						$resulttext .= $this->lng->txt("rated_value") . " " . (($found['value']) ? $found['value'] : 0) . " " . (($found['value'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points')) . ", ";
						$resulttext .= $this->lng->txt("rated_unit") . " " . (($found['unit']) ? $found['unit'] : 0) . " " . (($found['unit'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points'));
					}
					
					$resulttext .= ")";
					$template->setVariable("RESULT_OUTPUT", $resulttext);

					$resultOutput = $template->get();
				}
				$text = preg_replace("/\\\$" . substr($result, 1) . "(?![0-9]+)/", $input . " " . $units . " " . $checkSign . " " . $resultOutput . " " . "\\1", $text);
			}
		}
		return $text;
	}

	/**
	 * Check if advanced rating can be used for a result. This is only possible if there is exactly
	 * one possible correct unit for the result, otherwise it is impossible to determine wheather the
	 * unit is correct or the value.
	 * @return boolean True if advanced rating could be used, false otherwise
	 */
	public function canUseAdvancedRating($result)
	{
		$result_units  = $this->getResultUnits($result);
		$resultunit    = $result->getUnit();
		$similar_units = 0;
		foreach($result_units as $unit)
		{
			if(is_object($resultunit))
			{
				if($resultunit->getId() != $unit->getId())
				{
					if($resultunit->getBaseUnit() && $unit->getBaseUnit())
					{
						if($resultunit->getBaseUnit() == $unit->getBaseUnit()) return false;
					}
					if($resultunit->getBaseUnit())
					{
						if($resultunit->getBaseUnit() == $unit->getId()) return false;
					}
					if($unit->getBaseUnit())
					{
						if($unit->getBaseUnit() == $resultunit->getId()) return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Returns true, if the question is complete for use
	 * @return boolean True, if the single choice question is complete for use, otherwise false
	 */
	public function isComplete()
	{
		if(($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Saves a assFormulaQuestion object to a database
	 * @access public
	 */
	function saveToDb($original_id = "")
	{
		global $ilDB;

		$this->saveQuestionDataToDb($original_id);
		// save variables
		$affectedRows = $ilDB->manipulateF("
		DELETE FROM il_qpl_qst_fq_var 
		WHERE question_fi = %s",
			array("integer"),
			array($this->getId())
		);

		$source_qst_id = $original_id;
		$target_qst_id = $this->getId();

		foreach($this->variables as $variable)
		{
			$next_id      = $ilDB->nextId('il_qpl_qst_fq_var');
			$ilDB->insert('il_qpl_qst_fq_var',
			array(
				'variable_id'   => array('integer', $next_id),
				'question_fi'   => array('integer', $this->getId()),
				'variable'      => array('text', $variable->getVariable()),
				'range_min'     => array('float', ((strlen($variable->getRangeMin())) ? $variable->getRangeMin() : 0.0)),
				'range_max'     => array('float', ((strlen($variable->getRangeMax())) ? $variable->getRangeMax() : 0.0)),
				'unit_fi'       => array('integer', (is_object($variable->getUnit()) ? (int)$variable->getUnit()->getId() : 0)),
				'varprecision'  => array('integer', (int)$variable->getPrecision()),
				'intprecision'  => array('integer', (int)$variable->getIntprecision()),
				'range_min_txt' => array('text', $variable->getRangeMinTxt()),
				'range_max_txt' => array('text', $variable->getRangeMaxTxt())
			));
			
		}
		// save results
		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
			array("integer"),
			array($this->getId())
		);
		
		foreach($this->results as $result)
		{
			$next_id = $ilDB->nextId('il_qpl_qst_fq_res');
			if( is_object($result->getUnit())) 
			{
				$tmp_result_unit = $result->getUnit()->getId();
			} 
			else
			{
				$tmp_result_unit = 	NULL;
			}

			$formula = str_replace(",", ".", $result->getFormula());
			
			$ilDB->insert("il_qpl_qst_fq_res", array(
				"result_id"     => array("integer", $next_id),
				"question_fi"   => array("integer", $this->getId()),
				"result"        => array("text", $result->getResult()),
				"range_min"     => array("float", ((strlen($result->getRangeMin())) ? $result->getRangeMin() : 0)),
				"range_max"     => array("float", ((strlen($result->getRangeMax())) ? $result->getRangeMax() : 0)),
				"tolerance"     => array("float", ((strlen($result->getTolerance())) ? $result->getTolerance() : 0)),
				"unit_fi"       => array("integer", (int)$tmp_result_unit),
				"formula"       => array("clob", $formula),
				"resprecision"  => array("integer", $result->getPrecision()),
				"rating_simple" => array("integer", ($result->getRatingSimple()) ? 1 : 0),
				"rating_sign"   => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingSign()),
				"rating_value"  => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingValue()),
				"rating_unit"   => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingUnit()),
				"points"        => array("float", $result->getPoints()),
				"result_type"   => array('integer', (int)$result->getResultType()),
				"range_min_txt" => array("text", $result->getRangeMinTxt()),
				"range_max_txt" => array("text", $result->getRangeMaxTxt())

			));
		}
		// save result units
		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
			array("integer"),
			array($this->getId())
		);
		foreach($this->results as $result)
		{
			foreach($this->getResultUnits($result) as $unit)
			{
				$next_id      = $ilDB->nextId('il_qpl_qst_fq_res_unit');
				$affectedRows = $ilDB->manipulateF("INSERT INTO il_qpl_qst_fq_res_unit (result_unit_id, question_fi, result, unit_fi) VALUES (%s, %s, %s, %s)",
					array('integer', 'integer', 'text', 'integer'),
					array(
						$next_id,
						$this->getId(),
						$result->getResult(),
						$unit->getId()
					)
				);
			}
		}


		// copy category/unit-process:
		// if $source_qst_id = '' -> nothing to copy because this is a new question
		// if $source_qst_id == $target_qst_id -> nothing to copy because this is just an update-process
		// if $source_qst_id != $target_qst_id -> copy categories and untis because this is a copy-process
		// @todo: Nadia wtf?
		if($source_qst_id != $target_qst_id && $source_qst_id > 0)
		{
			$res = $ilDB->queryF('
				SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s',
				array('integer'), array($source_qst_id));

			$cp_cats = array();
			while($row = $ilDB->fetchAssoc($res))
			{
				$cp_cats[] = $row['category_id'];
			}

			foreach($cp_cats as $old_category_id)
			{
				// copy admin-categorie to custom-category (with question_fi)
				$new_cat_id = $this->unitrepository->copyCategory($old_category_id, $target_qst_id);

				// copy units to custom_category
				$this->unitrepository->copyUnitsByCategories($old_category_id, $new_cat_id, $target_qst_id);
			}
		}
		parent::saveToDb();
	}

	/**
	 * Loads a assFormulaQuestion object from a database
	 * @param integer $question_id A unique key which defines the question in the database
	 */
	public function loadFromDb($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = %s",
			array('integer'),
			array($question_id)
		);
		if($result->numRows() == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
			$this->setSuggestedSolution($data["solution_hint"]);
			$this->setOriginalId($data["original_id"]);
			$this->setObjId($data["obj_fi"]);
			$this->setAuthor($data["author"]);
			$this->setOwner($data["owner"]);

			try
			{
				$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
			}
			catch(ilTestQuestionPoolException $e)
			{
			}

			$this->unitrepository   = new ilUnitConfigurationRepository($question_id);

			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));

			// load variables
			$result = $ilDB->queryF("SELECT * FROM il_qpl_qst_fq_var WHERE question_fi = %s",
				array('integer'),
				array($question_id)
			);
			if($result->numRows() > 0)
			{
				while($data = $ilDB->fetchAssoc($result))
				{
					$varObj = new assFormulaQuestionVariable($data["variable"], $data["range_min"], $data["range_max"], $this->getUnitrepository()->getUnit($data["unit_fi"]), $data["varprecision"], $data["intprecision"]);
					$varObj->setRangeMinTxt($data['range_min_txt']);
					$varObj->setRangeMaxTxt($data['range_max_txt']);
					$this->addVariable($varObj);
				}
			}
			// load results
			$result = $ilDB->queryF("SELECT * FROM il_qpl_qst_fq_res WHERE question_fi = %s",
				array('integer'),
				array($question_id)
			);
			if($result->numRows() > 0)
			{
				while($data = $ilDB->fetchAssoc($result))
				{
					$resObj = new assFormulaQuestionResult($data["result"], $data["range_min"], $data["range_max"], $data["tolerance"], $this->getUnitrepository()->getUnit($data["unit_fi"]), $data["formula"], $data["points"], $data["resprecision"], $data["rating_simple"], $data["rating_sign"], $data["rating_value"], $data["rating_unit"]);
					$resObj->setResultType($data['result_type']);
					$resObj->setRangeMinTxt($data['range_min_txt']);
					$resObj->setRangeMaxTxt($data['range_max_txt']);
					$this->addResult($resObj);
				}
			}

			// load result units
			$result = $ilDB->queryF("SELECT * FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
				array('integer'),
				array($question_id)
			);
			if($result->numRows() > 0)
			{
				while($data = $ilDB->fetchAssoc($result))
				{
					$unit   = $this->getUnitrepository()->getUnit($data["unit_fi"]);
					$resObj = $this->getResult($data["result"]);
					$this->addResultUnit($resObj, $unit);
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	 * Duplicates an assFormulaQuestion
	 * @access public
	 */
	function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$this_id = $this->getId();

		if( (int)$testObjId > 0 )
		{
			$thisObjId = $this->getObjId();
		}

		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;

		if( (int)$testObjId > 0 )
		{
			$clone->setObjId($testObjId);
		}

		if ($title)
		{
			$clone->setTitle($title);
		}

		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}

		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

		// copy question page content
		$clone->copyPageOfQuestion($this_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($this_id);
		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	 * Copies an assFormulaQuestion object
	 * @access public
	 */
	function copyObject($target_questionpool_id, $title = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		$source_questionpool_id = $this->getObjId();
		$clone->setObjId($target_questionpool_id);
		if ($title)
		{
			$clone->setTitle($title);
		}
		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);

		$clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}

		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");

		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();

		// duplicate the question in database
		$clone = $this;
		$clone->id = -1;

		$clone->setObjId($targetParentId);

		if ($targetQuestionTitle)
		{
			$clone->setTitle($targetQuestionTitle);
		}

		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($sourceQuestionId);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	 * Returns the maximum points, a learner can reach answering the question
	 * @see $points
	 */
	public function getMaximumPoints()
	{
		$points = 0;
		foreach($this->results as $result)
		{
			$points += $result->getPoints();
		}
		return $points;
	}

	/**
	 * Returns the points, a learner has reached answering the question
	 * The points are calculated from the given answers.
	 * 
	 * @param integer $user_id The database ID of the learner
	 * @param integer $test_id The database Id of the test containing the question
	 * @access public
	 */
	function calculateReachedPoints($active_id, $pass = NULL, $returndetails = false)
	{
		if(is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$solutions     =& $this->getSolutionValues($active_id, $pass);
		$user_solution = array();
		foreach($solutions as $idx => $solution_value)
		{
			if(preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches))
			{
				$user_solution[$matches[1]] = $solution_value["value2"];
				$varObj                     = $this->getVariable($solution_value["value1"]);
				$varObj->setValue($solution_value["value2"]);
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
		//vd($this->getResults());
				$points = 0;
		foreach($this->getResults() as $result)
		{
			//vd($user_solution[$result->getResult()]["value"]);
			$points += $result->getReachedPoints($this->getVariables(), $this->getResults(), $user_solution[$result->getResult()]["value"], $user_solution[$result->getResult()]["unit"], $this->unitrepository->getUnits());
		}

		return $points;
	}
	
	public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
	{
		$user_solution = $previewSession->getParticipantsSolution();

		$points = 0;
		foreach($this->getResults() as $result)
		{
			$points += $result->getReachedPoints(
				$this->getVariables(),
				$this->getResults(),
				$user_solution[$result->getResult()],
				$user_solution[$result->getResult().'_unit'],
				$this->unitrepository->getUnits());
		}

		return $points;
	}

	/**
	 * Saves the learners input of the question to the database
	 * @param integer $test_id The database id of the test containing this question
	 * @return boolean Indicates the save status (true if saved successful, false otherwise)
	 * @access public
	 * @see    $answers
	 */
	function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;

		if(is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}

		$this->getProcessLocker()->requestUserSolutionUpdateLock();

		$solutionSubmit = $this->getSolutionSubmit();
		
		$entered_values = FALSE;
		foreach($solutionSubmit as $key => $value)
		{
			$matches = null;
			if(preg_match("/^result_(\\\$r\\d+)$/", $key, $matches))
			{
				if(strlen($value)) $entered_values = TRUE;
				$result = $ilDB->queryF("SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s  AND " . $ilDB->like('value1', 'clob', $matches[1]),
					array('integer', 'integer', 'integer'),
					array($active_id, $pass, $this->getId())
				);
				if($result->numRows())
				{
					while($row = $ilDB->fetchAssoc($result))
					{
						$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE solution_id = %s",
							array('integer'),
							array($row['solution_id'])
						);
					}
				}

				$affectedRows = $this->saveCurrentSolution($active_id,$pass,$matches[1],str_replace(",", ".", $value));
			}
			else if(preg_match("/^result_(\\\$r\\d+)_unit$/", $key, $matches))
			{
				$result = $ilDB->queryF("SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND " . $ilDB->like('value1', 'clob', $matches[1] . "_unit"),
					array('integer', 'integer', 'integer'),
					array($active_id, $pass, $this->getId())
				);
				if($result->numRows())
				{
					while($row = $ilDB->fetchAssoc($result))
					{
						$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE solution_id = %s",
							array('integer'),
							array($row['solution_id'])
						);
					}
				}

				$affectedRows = $this->saveCurrentSolution($active_id,$pass,$matches[1] . "_unit",$value);
			}
		}

		$this->getProcessLocker()->releaseUserSolutionUpdateLock();

		if($entered_values)
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if(ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if(ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}

		return true;
	}
	
	protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
	{
		$userSolution = $previewSession->getParticipantsSolution();
		
		foreach($this->getSolutionSubmit() as $key => $val)
		{
			$matches = null;
			
			if(preg_match("/^result_(\\\$r\\d+)$/", $key, $matches))
			{
				$userSolution[$matches[1]] = $val;
			}
			else if(preg_match("/^result_(\\\$r\\d+)_unit$/", $key, $matches))
			{
				$userSolution[$matches[1] . "_unit"] = $val;
			}
		}

		$previewSession->setParticipantsSolution($userSolution);
	}
	
	/**
	 * Reworks the allready saved working data if neccessary
	 *
	 * @abstract
	 * @access protected
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $obligationsAnswered
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered)
	{
		// nothing to do
	}

	/**
	 * Returns the question type of the question
	 * @return string The question type of the question
	 */
	public function getQuestionType()
	{
		return "assFormulaQuestion";
	}

	/**
	 * Returns the name of the additional question data table in the database
	 * @return string The additional table name
	 */
	public function getAdditionalTableName()
	{
		return "";
	}

	/**
	 * Returns the name of the answer table in the database
	 * @return string The answer table name
	 */
	public function getAnswerTableName()
	{
		return "";
	}

	/**
	 * Deletes datasets from answers tables
	 * @param integer $question_id The question id which should be deleted in the answers table
	 * @access public
	 */
	function deleteAnswers($question_id)
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_fq_var WHERE question_fi = %s",
			array('integer'),
			array($question_id)
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
			array('integer'),
			array($question_id)
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
			array('integer'),
			array($question_id)
		);
	}

	/**
	 * Collects all text in the question which could contain media objects
	 * which were created with the Rich Text Editor
	 */
	function getRTETextWithMediaObjects()
	{
		$text = parent::getRTETextWithMediaObjects();
		return $text;
	}

	/**
	 * Creates an Excel worksheet for the detailed cumulated results of this question
	 * @param object $worksheet    Reference to the parent excel worksheet
	 * @param object $startrow     Startrow of the output in the excel worksheet
	 * @param object $active_id    Active id of the participant
	 * @param object $pass         Test pass
	 * @param object $format_title Excel title format
	 * @param object $format_bold  Excel bold format
	 * @param array  $eval_data    Cumulated evaluation data
	 * @access public
	 */
	public function setExportDetailsXLS(&$worksheet, $startrow, $active_id, $pass, &$format_title, &$format_bold)
	{
		require_once 'Services/Excel/classes/class.ilExcelUtils.php';
		$solution = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		foreach($solution as $solutionvalue)
		{
			$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($solutionvalue["value1"]), $format_bold);
			if(strpos($solutionvalue["value1"], "_unit"))
			{
				$unit = $this->getUnit($solutionvalue["value2"]);
				if(is_object($unit))
				{
					$worksheet->write($startrow + $i, 1, $unit->getUnit());
				}
			}
			else
			{
				$worksheet->write($startrow + $i, 1, $solutionvalue["value2"]);
			}
			if(preg_match("/(\\\$v\\d+)/", $solutionvalue["value1"], $matches))
			{
				$var = $this->getVariable($solutionvalue["value1"]);
				if(is_object($var) && (is_object($var->getUnit())))
				{
					$worksheet->write($startrow + $i, 2, $var->getUnit()->getUnit());
				}
			}
			$i++;
		}
		return $startrow + $i + 1;
	}

	/**
	 * Returns the best solution for a given pass of a participant
	 * @return array An associated array containing the best solution
	 * @access public
	 */
	public function getBestSolution($solutions)
	{
		$user_solution              = array();

		foreach($solutions as $idx => $solution_value)
		{
			if(preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches))
			{
				$user_solution[$matches[1]] = $solution_value["value2"];
				$varObj                     = $this->getVariable($matches[1]);
				$varObj->setValue($solution_value["value2"]);
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
		foreach($this->getResults() as $result)
		{
			$resVal = $result->calculateFormula($this->getVariables(), $this->getResults(), parent::getId(), false);

			if(is_object($result->getUnit()))
			{
				$user_solution[$result->getResult()]["unit"] = $result->getUnit()->getId();
				$user_solution[$result->getResult()]["value"] = $resVal;
			}
			else if($result->getUnit() == NULL)
			{
				$unit_factor = 1;
				// there is no fix result_unit, any "available unit" is accepted 
				
				$available_units = $result->getAvailableResultUnits(parent::getId());
				$result_name = $result->getResult();
				
				if($available_units[$result_name] != NULL)
				{
					$check_unit = in_array($user_solution[$result_name]['unit'], $available_units[$result_name]);					
				}

				if($check_unit == true)
				{
					//get unit-factor
					$unit_factor = assFormulaQuestionUnit::lookupUnitFactor($user_solution[$result_name]['unit']);
					$user_solution[$result->getResult()]["value"] = round(ilMath::_div($resVal, $unit_factor), 55);
				}
			}
			if($result->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC
				|| $result->getResultType() == assFormulaQuestionResult::RESULT_FRAC)
			{
				$value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($resVal);
				if(is_array($value))
				{
					$frac_helper = $value[1];
					$value =  $value[0];
				}
				$user_solution[$result->getResult()]["value"] = $value;
				$user_solution[$result->getResult()]["frac_helper"] = $frac_helper;
			}
			elseif($result->getPrecision() > 0)
			{
				$user_solution[$result->getResult()]["value"] = round($resVal, $result->getPrecision());
			}
			else
			{
				$user_solution[$result->getResult()]["value"] = round($resVal);
			}
		}
		return $user_solution;
	}
	
	public function setId($id = -1)
	{
		parent::setId($id);
		$this->unitrepository->setConsumerId($this->getId());
	}

	/**
	 * Object getter
	 */
	public function __get($value)
	{
		switch($value)
		{
			case "resultunits":
				return $this->resultunits;
				break;
			default:
				return parent::__get($value);
				break;
		}
	}

	/**
	 * @param \ilUnitConfigurationRepository $unitrepository
	 */
	public function setUnitrepository($unitrepository)
	{
		$this->unitrepository = $unitrepository;
	}

	/**
	 * @return \ilUnitConfigurationRepository
	 */
	public function getUnitrepository()
	{
		return $this->unitrepository;
	}

	/**
	 * @return array
	 */
	protected function getSolutionSubmit()
	{
		$solutionSubmit = array();
		foreach($_POST as $k => $v)
		{
			if(preg_match("/^result_(\\\$r\\d+)$/", $k))
			{
				$solutionSubmit[$k] = $v;
			} elseif(preg_match("/^result_(\\\$r\\d+)_unit$/", $k))
			{
				$solutionSubmit[$k] = $v;
			}
		}
		return $solutionSubmit;
	}

	/**
	 * Get all available operations for a specific question
	 *
	 * @param $expression
	 *
	 * @internal param string $expression_type
	 * @return array
	 */
	public function getOperators($expression)
	{
		require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
		return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
	}

	/**
	 * Get all available expression types for a specific question
	 * @return array
	 */
	public function getExpressionTypes()
	{
		return array(
			iQuestionCondition::PercentageResultExpression,
			iQuestionCondition::NumericResultExpression,
			iQuestionCondition::EmptyAnswerExpression,
		);
	}

	/**
	 * Get the user solution for a question by active_id and the test pass
	 *
	 * @param int $active_id
	 * @param int $pass
	 *
	 * @return ilUserQuestionResult
	 */
	public function getUserQuestionResult($active_id, $pass)
	{
		/** @var ilDB $ilDB */
		global $ilDB;
		$result = new ilUserQuestionResult($this, $active_id, $pass);

		$data = $ilDB->queryF(
			"SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = (
				SELECT MAX(step) FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s
			)",
			array("integer", "integer", "integer","integer", "integer", "integer"),
			array($active_id, $pass, $this->getId(), $active_id, $pass, $this->getId())
		);

		while($row = $ilDB->fetchAssoc($data))
		{
			if(strstr($row["value1"], '$r') && $row["value2"] != null)
			{
				$result->addKeyValue(str_replace('$r', "", $row["value1"]), $row["value2"]);
			}
		}

		$points = $this->calculateReachedPoints($active_id, $pass);
		$max_points = $this->getMaximumPoints();

		$result->setReachedPercentage(($points/$max_points) * 100);

		return $result;
	}

	/**
	 * If index is null, the function returns an array with all anwser options
	 * Else it returns the specific answer option
	 *
	 * @param null|int $index
	 *
	 * @return array|ASS_AnswerSimple
	 */
	public function getAvailableAnswerOptions($index = null)
	{
		if($index !== null)
		{
			return $this->getResult('$r'.($index+1));
		}
		else
		{
			return $this->getResults();
		}
	}
}