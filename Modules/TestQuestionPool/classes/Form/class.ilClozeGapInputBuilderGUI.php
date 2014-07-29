<?php

require_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
class ilClozeGapInputBuilderGUI extends ilSubEnabledFormPropertyGUI
{
	/**
	 * Set Value.
	 * @param    string $a_value    Value
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
		$editOrOpen=$this->value;
		if(isset($editOrOpen['author']))
		{
			$json=json_decode(ilUtil::stripSlashes($_POST['gap_json_post' ]));
			return $json[0];
		}
		return $this->value;
	}

	public function checkInput()
	{
		$error=false;
		$json=json_decode(ilUtil::stripSlashes($_POST['gap_json_post' ]));
		$_POST['gap']=ilUtil::stripSlashesRecursive($_POST['gap']);
		if(isset($_POST['gap']) && is_array($_POST['gap']))
		{
			foreach($_POST['gap'] as $key => $item)
			{
				$_POST['clozetype_' . $key]= ilUtil::stripSlashes($_POST['clozetype_' . $key]);
				$getType=$_POST['clozetype_' . $key];
				if( $getType== CLOZE_TEXT || $getType == CLOZE_SELECT )
				{
					$_POST['gap_' . $key] = ilUtil::stripSlashesRecursive($_POST['gap_' . $key]);
					$gapText=$_POST['gap_' . $key];
					foreach($gapText['answer'] as $row => $answer)
					{
						if(!isset($answer) || $answer == "")
						{
							$error=true;
						}
					}
					$points_sum=0;
					foreach($gapText['points'] as $row => $points)
					{
						if(isset($points) && $points != "" && is_numeric($points))
						{
							$points_sum += $points;
						}
						else
						{
							$error=true;
						}
					}
					if($points_sum == 0)
					{
						$error=true;
					}
					if($getType == CLOZE_SELECT)
					{
						$_POST['shuffle_' . $key]= ilUtil::stripSlashes($_POST['shuffle_' . $key]);
						if(!isset($_POST['shuffle_' . $key]))
						{
							$error=true;
						}
					}
				}
				if( $getType == CLOZE_NUMERIC )
				{
					$_POST['gap_' . $key .'numeric'] = ilUtil::stripSlashes($_POST['gap_' . $key .'numeric'], FALSE);
					$_POST['gap_' . $key .'numeric_lower'] = ilUtil::stripSlashes($_POST['gap_' . $key .'numeric_lower'], FALSE);
					$_POST['gap_' . $key .'numeric_upper'] = ilUtil::stripSlashes($_POST['gap_' . $key .'numeric_upper'], FALSE);
					$_POST['gap_' . $key .'numeric_points'] = ilUtil::stripSlashes($_POST['gap_' . $key .'numeric_points']);
					$mark_errors= array('answer' => false, 'lower' => false, 'upper' => false , 'points' => false);
					$eval = new EvalMath();
					$eval->suppress_errors = true;
					$formula=$_POST['gap_' . $key .'_numeric'];
					$result = $eval->e(str_replace(",", ".",$_POST['gap_' . $key .'_numeric'],$formula));
					if ($result === false)
					{
						$error=true;
					}
					$lower = $_POST['gap_' . $key .'_numeric_lower'];
					$has_valid_chars = ilClozeGapInputBuilderGUI::checkForValidFormula($lower);
					$result = $eval->e(str_replace(",", ".", $lower), FALSE);
					if ($result === false || !$has_valid_chars )
					{
						$error=true;
					}
					$_POST['gap_' . $key .'_numeric_lower'] = $result;
					$result = $eval->e(str_replace(",", ".",$_POST['gap_' . $key .'_numeric_upper']), FALSE);
					if ($result === false)
					{
						$error=true;
					}
					$_POST['gap_' . $key .'_numeric_upper'] = $result;
					$points=$_POST['gap_' . $key .'_numeric_points'];
					if(!isset($points) || $points == "" || !is_numeric($points) || $points == 0)
					{
						$error=true;
						$mark_errors['points']=true;
					}
					$json[0][$key]->values[0]->error=$mark_errors;
				}
			}
		}
		$_POST['gap_json_post']=json_encode($json);
		return !$error;
	}
	
	protected function checkForValidFormula($value)
	{
		return preg_match("/^-?(\\d*)(,|\\.|\\/){0,1}(\\d*)$/", $value, $matches);
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
		$custom_template = new ilTemplate('tpl.il_as_cloze_gap_builder.html', true, true, 'Modules/TestQuestionPool');
		$custom_template->setVariable('GAP_JSON', json_encode(array($this->getValue())));
		$custom_template->setVariable('TEXT_GAP', $lng->txt('text_gap'));
		$custom_template->setVariable('SELECT_GAP', $lng->txt('select_gap'));
		$custom_template->setVariable('NUMERIC_GAP', $lng->txt('numeric_gap'));
		$custom_template->setVariable('GAP_SIZE', $lng->txt('cloze_fixed_textlength'));
		$custom_template->setVariable('GAP_SIZE_INFO', $lng->txt('cloze_gap_size_info'));
		$custom_template->setVariable('ANSWER_TEXT', $lng->txt('answer_text'));
		$custom_template->setVariable('POINTS', $lng->txt('points'));
		$custom_template->setVariable('VALUE', $lng->txt('value'));
		$custom_template->setVariable('UPPER_BOUND', $lng->txt('range_upper_limit'));
		$custom_template->setVariable('LOWER_BOUND', $lng->txt('range_lower_limit'));
		$custom_template->setVariable('ACTIONS', $lng->txt('actions'));
		$custom_template->setVariable('REMOVE_GAP', $lng->txt('remove_gap'));
		$custom_template->setVariable('SHUFFLE_ANSWERS', $lng->txt('shuffle_answers'));
		$custom_template->setVariable('POINTS_ERROR', $lng->txt('enter_enough_positive_points'));
		$custom_template->setVariable('MISSING_VALUE', $lng->txt('msg_input_is_required'));
		$custom_template->setVariable('NOT_A_FORMULA', $lng->txt('err_no_formula'));
		$custom_template->setVariable('NOT_A_NUMBER', $lng->txt('err_no_numeric_value'));
		$custom_template->setVariable('CLOSE', $lng->txt('close'));
		$custom_template->setVariable('DELETE_GAP', $lng->txt('are_you_sure'));
		$template->setCurrentBlock("prop_generic");
		$template->setVariable("PROP_GENERIC", $custom_template->get());
		$template->parseCurrentBlock();
	}
} 
