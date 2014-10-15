<?php

require_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.assClozeGap.php';
require_once 'Modules/TestQuestionPool/classes/class.assClozeTest.php';

class ilClozeGapInputBuilderGUI extends ilSubEnabledFormPropertyGUI
{
	/**
	 * Set Value.
	 * @param    string $a_value Value
	 */
	public function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	 * Get Value.
	 * @return    string    Value
	 */
	public function getValue()
	{
		$editOrOpen = $this->value;
		if(isset($editOrOpen['author']))
		{
			$json = json_decode(ilUtil::stripSlashes($_POST['gap_json_post']));
			return $json[0];
		}
		return $this->value;
	}

	public function setValueCombination($value)
	{
		$this->value_combination = $value;
	}

	/**
	 * Get Value.
	 * @return    string    Value
	 */
	public function getValueCombination()
	{
		$editOrOpen = $this->value;
		if(isset($editOrOpen['author']))
		{
			$json = json_decode(ilUtil::stripSlashes($_POST['gap_json_combination_post']));
			return $json;
		}
		return (array) $this->value_combination;
	}

	public function setValueCombinationFromDb($value)
	{
		$return_array  = array();
		$return_points = array();
		$temp          = -1;
		if($value)
		{
			foreach($value as $row)
			{
				$return_array[$row['cid']][] = array('answer' => $row['answer'], 'gap' => $row['gap_fi'], 'type' => $row['type']);
				if($temp != $row['cid'])
				{
					$return_points[] = array('key' => $row['cid'], 'points' => $row['points'], 'best_solution' => $row['best_solution']);
					$temp            = $row['cid'];
				}
			}
			foreach($return_points as $row)
			{
				$return_array[$row['key']][] = array('points' => $row['points'], 'best_solution' => $row['best_solution']);
			}
			$this->setValueCombination($return_array);
		}
	}

	public function checkInput()
	{
		$error        = false;
		$json         = json_decode(ilUtil::stripSlashes($_POST['gap_json_post']));
		$_POST['gap'] = ilUtil::stripSlashesRecursive($_POST['gap']);
		if(array_key_exists('gap_combination', $_POST))
		{
			$best_solution = ilUtil::stripSlashes($_POST['best_possible_solution']);
			if($best_solution == '')
			{
				$error = true;
			}
			$_POST['gap_combination'] = ilUtil::stripSlashesRecursive($_POST['gap_combination']);
			$gap_with_points          = array();
			$find_doubles             = array();
			foreach($_POST['gap_combination'] as $key => $item)
			{
				$temp_array = array();
				$points     = $item['points'];
				if($points == 0)
				{
					$error = true;
				}
				$find_double_in_combination = array();
				foreach($item as $inner_key => $inner_value)
				{
					if(is_array($inner_value) && array_key_exists('select', $inner_value))
					{
						if(in_array($inner_value['select'], $find_double_in_combination))
						{
							$error = true;
						}
						$find_double_in_combination[]            = $inner_value['select'];
						$gap_with_points[$inner_value['select']] = $points;
						$temp_array[$inner_value['select']]      = $inner_value['select'] . ' ' . $inner_value['value'];
						if($inner_value['value'] == 'none_selected_minus_one')
						{
							$error = true;
						}
					}
				}
				sort($temp_array);
				$find_doubles[$key] = implode($temp_array);
			}
			foreach($find_doubles as $value)
			{
				if(count(array_keys($find_doubles, $value)) > 1)
				{
					$error = true;
				}
			}
		}

		if(isset($_POST['gap']) && is_array($_POST['gap']))
		{
			foreach($_POST['gap'] as $key => $item)
			{
				$_POST['clozetype_' . $key] 		= ilUtil::stripSlashes($_POST['clozetype_' . $key]);
				$getType                    		= $_POST['clozetype_' . $key];
				$gapsize                          	= $_POST['gap_' . $key . '_gapsize'];
				$json[0][$key]->text_field_length 	= $gapsize > 0 ? $gapsize : '';
				if($getType == CLOZE_TEXT || $getType == CLOZE_SELECT)
				{
					$_POST['gap_' . $key] = ilUtil::stripSlashesRecursive($_POST['gap_' . $key]);
					$gapText              = $_POST['gap_' . $key];
					foreach($gapText['answer'] as $row => $answer)
					{
						if(!isset($answer) || $answer == '')
						{
							$error = true;
						}
					}
					$points_sum = 0;
					if(array_key_exists('points', $gapText))
					{
						foreach($gapText['points'] as $row => $points)
						{
							if(isset($points) && $points != '' && is_numeric($points))
							{
								$points_sum += $points;
							}
							else
							{
								$error = true;
							}
						}
						if(is_array($gap_with_points) && array_key_exists($key, $gap_with_points))
						{
							$points_sum += $gap_with_points[$key];
						}
						if($points_sum == 0)
						{
							$error = true;
						}
						if($getType == CLOZE_SELECT)
						{
							$_POST['shuffle_' . $key] = ilUtil::stripSlashes($_POST['shuffle_' . $key]);
							if(!isset($_POST['shuffle_' . $key]))
							{
								$error = true;
							}
						}
					}
					else
					{
						$error = true;
					}
				}
				if($getType == CLOZE_NUMERIC)
				{
					$_POST['gap_' . $key . 'numeric']        = ilUtil::stripSlashes($_POST['gap_' . $key . 'numeric'], FALSE);
					$_POST['gap_' . $key . 'numeric_lower']  = ilUtil::stripSlashes($_POST['gap_' . $key . 'numeric_lower'], FALSE);
					$_POST['gap_' . $key . 'numeric_upper']  = ilUtil::stripSlashes($_POST['gap_' . $key . 'numeric_upper'], FALSE);
					$_POST['gap_' . $key . 'numeric_points'] = ilUtil::stripSlashes($_POST['gap_' . $key . 'numeric_points']);
					$mark_errors                             = array('answer' => false, 'lower' => false, 'upper' => false, 'points' => false);
					$eval                                    = new EvalMath();
					$eval->suppress_errors                   = true;
					$formula                                 = $_POST['gap_' . $key . '_numeric'];
					$result                                  = $eval->e(str_replace(',', '.', $_POST['gap_' . $key . '_numeric'], $formula));

					if($result === false)
					{
						$error = true;
					}

					$lower              = $_POST['gap_' . $key . '_numeric_lower'];
					$assClozeTestObject = new assClozeTest();
					$has_valid_chars    = $assClozeTestObject->checkForValidFormula($lower);
					$result             = $eval->e(str_replace(',', '.', $lower), FALSE);

					if($result === false || !$has_valid_chars)
					{
						$error = true;
					}

					$_POST['gap_' . $key . '_numeric_lower'] = $result;
					$result                                  = $eval->e(str_replace(',', '.', $_POST['gap_' . $key . '_numeric_upper']), FALSE);

					if($result === false)
					{
						$error = true;
					}

					$_POST['gap_' . $key . '_numeric_upper'] = $result;
					$points                                  = $_POST['gap_' . $key . '_numeric_points'];

					if(is_array($gap_with_points) && array_key_exists($key, $gap_with_points))
					{
						$points += $gap_with_points[$key];
					}

					if(!isset($points) || $points == '' || !is_numeric($points) || $points == 0)
					{
						$error = true;
					}

					$json[0][$key]->values[0]->error = $mark_errors;
				}
			}
		}
		$_POST['gap_json_post'] = json_encode($json);
		return !$error;
	}

	public function setValueByArray($data)
	{
		$this->setValue($data);
	}

	/**
	 * @param ilTemplate $template
	 */
	public function insert(ilTemplate $template)
	{
		global $lng;
		include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
		$modal = ilModalGUI::getInstance();
		$modal->setHeading($lng->txt(''));
		$modal->setId("ilGapModal");
		$modal->setBody('');

		$custom_template = new ilTemplate('tpl.il_as_cloze_gap_builder.html', true, true, 'Modules/TestQuestionPool');
		$custom_template->setVariable("MY_MODAL", 						$modal->getHTML());
		$custom_template->setVariable('GAP_JSON', 						json_encode(array($this->getValue())));
		$custom_template->setVariable('GAP_COMBINATION_JSON', 			json_encode(json_decode(json_encode($this->getValueCombination()), true)));
		$custom_template->setVariable('TEXT_GAP', 						$lng->txt('text_gap'));
		$custom_template->setVariable('SELECT_GAP', 					$lng->txt('select_gap'));
		$custom_template->setVariable('NUMERIC_GAP', 					$lng->txt('numeric_gap'));
		$custom_template->setVariable('GAP_SIZE', 						$lng->txt('cloze_fixed_textlength'));
		$custom_template->setVariable('GAP_SIZE_INFO', 					$lng->txt('cloze_gap_size_info'));
		$custom_template->setVariable('ANSWER_TEXT', 					$lng->txt('answer_text'));
		$custom_template->setVariable('POINTS', 						$lng->txt('points'));
		$custom_template->setVariable('VALUE', 							$lng->txt('value'));
		$custom_template->setVariable('UPPER_BOUND', 					$lng->txt('range_upper_limit'));
		$custom_template->setVariable('LOWER_BOUND', 					$lng->txt('range_lower_limit'));
		$custom_template->setVariable('ACTIONS', 						$lng->txt('actions'));
		$custom_template->setVariable('REMOVE_GAP', 					$lng->txt('remove_gap'));
		$custom_template->setVariable('SHUFFLE_ANSWERS', 				$lng->txt('shuffle_answers'));
		$custom_template->setVariable('POINTS_ERROR', 					$lng->txt('enter_enough_positive_points'));
		$custom_template->setVariable('MISSING_VALUE', 					$lng->txt('msg_input_is_required'));
		$custom_template->setVariable('NOT_A_FORMULA', 					$lng->txt('err_no_formula'));
		$custom_template->setVariable('NOT_A_NUMBER', 					$lng->txt('err_no_numeric_value'));
		$custom_template->setVariable('CLOSE', 							$lng->txt('close'));
		$custom_template->setVariable('DELETE_GAP', 					$lng->txt('are_you_sure'));
		$custom_template->setVariable('PLEASE_SELECT', 					$lng->txt('please_select'));
		$custom_template->setVariable('BEST_POSSIBLE_SOLUTION_HEADER', 	$lng->txt('tst_best_solution_is'));
		$custom_template->setVariable('BEST_POSSIBLE_SOLUTION', 		$lng->txt('value'));
		$custom_template->setVariable('MAX_POINTS', 					$lng->txt('max_points'));
		$custom_template->setVariable('OUT_OF_BOUND', 					$lng->txt('out_of_range'));
		$custom_template->setVariable('TYPE',							$lng->txt('type'));
		$custom_template->setVariable('VALUES', 						$lng->txt('values'));
		$custom_template->setVariable('GAP_COMBINATION', 				$lng->txt('gap_combination'));
		$custom_template->setVariable('COPY', 							$lng->txt('copy_of'));
		$template->setCurrentBlock('prop_generic');
		$template->setVariable('PROP_GENERIC', $custom_template->get());
		$template->parseCurrentBlock();
	}
} 
