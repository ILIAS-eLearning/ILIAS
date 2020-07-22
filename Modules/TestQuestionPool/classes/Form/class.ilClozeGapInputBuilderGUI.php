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
        if (isset($editOrOpen['author'])) {
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
        if (isset($editOrOpen['author'])) {
            $json = json_decode(ilUtil::stripSlashes($_POST['gap_json_combination_post']));
            return $json;
        }
        return (array) $this->value_combination;
    }

    public function setValueCombinationFromDb($value)
    {
        $return_array = array();
        if ($value) {
            foreach ($value as $row) {
                if ($row['row_id'] == 0) {
                    $return_array[$row['cid']][0][] = $row['gap_fi'];
                }
                $return_array[$row['cid']][1][$row['row_id']][] = $row['answer'];
                $return_array[$row['cid']][2][$row['row_id']] = $row['points']; //= array('key' => $row['cid'], 'points' => $row['points'], 'best_solution' => $row['best_solution']);
            }
            $this->setValueCombination($return_array);
        }
    }

    public function checkInput()
    {
        $error = false;
        $json = ilUtil::stripSlashesRecursive(json_decode($_POST['gap_json_post']), false);
        $_POST['gap'] = ilUtil::stripSlashesRecursive($_POST['gap']);
        $gaps_used_in_combination = array();
        if (array_key_exists('gap_combination', $_POST)) {
            $_POST['gap_combination'] = ilUtil::stripSlashesRecursive($_POST['gap_combination']);
            $_POST['gap_combination_values'] = ilUtil::stripSlashesRecursive($_POST['gap_combination_values']);
            $gap_with_points = array();
        
            for ($i = 0; $i < count($_POST['gap_combination']['select']); $i++) {
                foreach ($_POST['gap_combination']['select'][$i] as $key => $item) {
                    if ($item == 'none_selected_minus_one') {
                        return false;
                    }
                    $gaps_used_in_combination[$item] = $item;
                    $check_points_for_best_scoring = false;
                    foreach ($_POST['gap_combination_values'][$i] as $index => $answeritems) {
                        foreach ($answeritems as $answeritem) {
                            if ($answeritem == 'none_selected_minus_one') {
                                return false;
                            }
                        }
                        $points = $_POST['gap_combination']['points'][$i][$index];
                        if ($points > 0) {
                            $check_points_for_best_scoring = true;
                        }
                    }
                    if (!$check_points_for_best_scoring) {
                        return false;
                    }
                }
            }
        }

        if (isset($_POST['gap']) && is_array($_POST['gap'])) {
            foreach ($_POST['gap'] as $key => $item) {
                $_POST['clozetype_' . $key] = ilUtil::stripSlashes($_POST['clozetype_' . $key]);
                $getType = $_POST['clozetype_' . $key];
                $gapsize = $_POST['gap_' . $key . '_gapsize'];
                $json[0][$key]->text_field_length = $gapsize > 0 ? $gapsize : '';
                $select_at_least_on_positive = false;
                if ($getType == CLOZE_TEXT || $getType == CLOZE_SELECT) {
                    $_POST['gap_' . $key] = ilUtil::stripSlashesRecursive($_POST['gap_' . $key], false);
                    $gapText = $_POST['gap_' . $key];
                    foreach ($gapText['answer'] as $row => $answer) {
                        if (!isset($answer) || $answer == '') {
                            $error = true;
                        }
                    }
                    $points_sum = 0;
                    if (array_key_exists('points', $gapText)) {
                        foreach ($gapText['points'] as $row => $points) {
                            if (isset($points) && $points != '' && is_numeric($points)) {
                                $points_sum += $points;
                                if ($points > 0) {
                                    $select_at_least_on_positive = true;
                                }
                            } else {
                                $error = true;
                            }
                        }
                        if (is_array($gap_with_points) && array_key_exists($key, $gap_with_points)) {
                            $points_sum += $gap_with_points[$key];
                        }
                        if ($points_sum <= 0) {
                            if (!array_key_exists($key, $gaps_used_in_combination) && (!$getType == 'select' || $select_at_least_on_positive == false)) {
                                $error = true;
                            }
                        }
                        if ($getType == CLOZE_SELECT) {
                            $_POST['shuffle_' . $key] = ilUtil::stripSlashes($_POST['shuffle_' . $key]);
                            if (!isset($_POST['shuffle_' . $key])) {
                                $error = true;
                            }
                        }
                    } else {
                        $error = true;
                    }
                }
                if ($getType == CLOZE_NUMERIC) {
                    // fau: fixGapFormula - fix post indices, streamlined checks
                    include_once("./Services/Math/classes/class.EvalMath.php");
                    $eval = new EvalMath();
                    $eval->suppress_errors = true;

                    $mark_errors = array('answer' => false, 'lower' => false, 'upper' => false, 'points' => false);
                    foreach (array(	'answer' => '_numeric',
                                    'lower' => '_numeric_lower',
                                    'upper' => '_numeric_upper',
                                    'points' => '_numeric_points') as $part => $suffix) {
                        $val = ilUtil::stripSlashes($_POST['gap_' . $key . $suffix], false);
                        $val = str_replace(',', '.', $val);
                        if ($eval->e($val) === false) {
                            $mark_errors[$part] = true;
                            $error = true;
                        }

                        if ($part == 'points') {
                            $points = $val;
                        }
                    }
                    // fau.
                    if (is_array($gap_with_points) && array_key_exists($key, $gap_with_points)) {
                        $points += $gap_with_points[$key];
                    }

                    if (!isset($points) || $points == '' || !is_numeric($points) || $points == 0) {
                        if (!array_key_exists($key, $gaps_used_in_combination)) {
                            $error = true;
                        }
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
        global $DIC;
        $lng = $DIC['lng'];
        require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($lng->txt(''));
        $modal->setId("ilGapModal");
        //$modal->setBackdrop(ilModalGUI::BACKDROP_OFF);
        $modal->setBody('');

        $custom_template = new ilTemplate('tpl.il_as_cloze_gap_builder.html', true, true, 'Modules/TestQuestionPool');
        $custom_template->setVariable("MY_MODAL", $modal->getHTML());
        $custom_template->setVariable('GAP_JSON', json_encode(array($this->getValue())));
        $custom_template->setVariable('GAP', $lng->txt('gap'));
        $custom_template->setVariable('GAP_COMBINATION_JSON', json_encode($this->getValueCombination()));
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
        $custom_template->setVariable('PLEASE_SELECT', $lng->txt('please_select'));
        $custom_template->setVariable('BEST_POSSIBLE_SOLUTION_HEADER', $lng->txt('tst_best_solution_is'));
        $custom_template->setVariable('BEST_POSSIBLE_SOLUTION', $lng->txt('value'));
        $custom_template->setVariable('MAX_POINTS', $lng->txt('max_points'));
        $custom_template->setVariable('OUT_OF_BOUND', $lng->txt('out_of_range'));
        $custom_template->setVariable('TYPE', $lng->txt('type'));
        $custom_template->setVariable('VALUES', $lng->txt('values'));
        $custom_template->setVariable('GAP_COMBINATION', $lng->txt('gap_combination'));
        $custom_template->setVariable('COPY', $lng->txt('copy_of'));
        $custom_template->setVariable('OK', $lng->txt('ok'));
        $custom_template->setVariable('CANCEL', $lng->txt('cancel'));
        $custom_template->setVariable('WHITESPACE_FRONT', $lng->txt('cloze_textgap_whitespace_before'));
        $custom_template->setVariable('WHITESPACE_BACK', $lng->txt('cloze_textgap_whitespace_after'));
        $custom_template->setVariable('WHITESPACE_MULTIPLE', $lng->txt('cloze_textgap_multiple_whitespace'));
        $template->setCurrentBlock('prop_generic');
        $template->setVariable('PROP_GENERIC', $custom_template->get());
        $template->parseCurrentBlock();
    }
}
