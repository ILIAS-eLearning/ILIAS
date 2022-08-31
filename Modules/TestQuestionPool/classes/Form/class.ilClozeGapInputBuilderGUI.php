<?php

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

class ilClozeGapInputBuilderGUI extends ilSubEnabledFormPropertyGUI
{
    private $value_combination;
    private $value;
    private ArrayBasedRequestWrapper $post;
    private ArrayBasedRequestWrapper $query;

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->post = $this->http->wrapper()->post();
        $this->query = $this->http->wrapper()->query();
    }

    /**
     * Set Value.
     * @param    string $a_value Value
     */
    public function setValue($a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue()
    {
        $editOrOpen = $this->value;
        if (isset($editOrOpen['author'])) {
            $json = json_decode(ilUtil::stripSlashes($_POST['gap_json_post']));
            return $json[0];
        }
        return $this->value;
    }

    public function setValueCombination($value): void
    {
        $this->value_combination = $value;
    }

    public function getValueCombination()
    {
        $editOrOpen = $this->value;
        if (isset($editOrOpen['author'])) {
            $json = json_decode(ilUtil::stripSlashes($_POST['gap_json_combination_post']));
            return $json;
        }
        return (array) $this->value_combination;
    }

    public function setValueCombinationFromDb($value): void
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

    public function checkInput(): bool
    {
        $error = false;
        $json = self::stripSlashesRecursive(json_decode($_POST['gap_json_post'], true), false);
        $gap = self::stripSlashesRecursive($this->raw('gap'));
        $gaps_used_in_combination = array();
        if ($this->http->wrapper()->post()->has('gap_combination')) {
            $_POST['gap_combination'] = self::stripSlashesRecursive($_POST['gap_combination']);
            $_POST['gap_combination_values'] = self::stripSlashesRecursive($_POST['gap_combination_values']);
            $gap_with_points = array();

            for ($i = 0, $iMax = count($_POST['gap_combination']['select']); $i < $iMax; $i++) {
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

        if (is_array($gap)) {
            foreach ($gap as $key => $item) {
                $getType = ilUtil::stripSlashes($this->raw('clozetype_' . $key));
                $gapsize = $this->raw('gap_' . $key . '_gapsize');

                //$json[0][$key]->text_field_length = $gapsize > 0 ? $gapsize : '';
                $json[0][$key]['text_field_length'] = $gapsize > 0 ? $gapsize : '';

                $select_at_least_on_positive = false;
                if ($getType == CLOZE_TEXT || $getType == CLOZE_SELECT) {
                    $gapText = self::stripSlashesRecursive($this->raw('gap_' . $key), false);
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
                        if (isset($gap_with_points) && is_array($gap_with_points) && array_key_exists($key, $gap_with_points)) {
                            $points_sum += $gap_with_points[$key];
                        }
                        if ($points_sum <= 0) {
                            if (!array_key_exists($key, $gaps_used_in_combination) && (!$getType == 'select' || $select_at_least_on_positive == false)) {
                                $error = true;
                            }
                        }
                        if ($getType == CLOZE_SELECT) {
                            if (ilUtil::stripSlashes($this->raw('shuffle_' . $key) ?? '') === '') {
                                $error = true;
                            }
                        }
                    } else {
                        $error = true;
                    }
                }
                if ($getType == CLOZE_NUMERIC) {
                    $eval = new EvalMath();
                    $eval->suppress_errors = true;

                    $mark_errors = array('answer' => false, 'lower' => false, 'upper' => false, 'points' => false);
                    foreach (array(	'answer' => '_numeric',
                                    'lower' => '_numeric_lower',
                                    'upper' => '_numeric_upper',
                                    'points' => '_numeric_points') as $part => $suffix) {
                        $val = ilUtil::stripSlashes($this->raw('gap_' . $key . $suffix), false);
                        $val = str_replace(',', '.', $val);
                        if ($eval->e($val) === false) {
                            $mark_errors[$part] = true;
                            $error = true;
                        }

                        if ($part == 'points') {
                            $points = $val;
                        }
                    }

                    if (isset($gap_with_points) && is_array($gap_with_points) && array_key_exists($key, $gap_with_points)) {
                        $points += $gap_with_points[$key];
                    }

                    if (!isset($points) || $points == '' || !is_numeric($points) || $points == 0) {
                        if (!array_key_exists($key, $gaps_used_in_combination)) {
                            $error = true;
                        }
                    }

                    $json[0][$key]["values"][0]["error"] = $mark_errors;
                }
            }
        }
        //$_POST['gap_json_post'] = json_encode($json);
        return !$error;
    }

    public function setValueByArray($data): void
    {
        $this->setValue($data);
    }

    /**
     * @param ilTemplate $template
     */
    public function insert(ilTemplate $template): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($lng->txt(''));
        $modal->setId("ilGapModal");
        $modal->setBody('');

        $cloze_settings_js = 'ClozeSettings = {'
            . ' gaps_php             : ' . json_encode(array($this->getValue()))
            . ',gaps_combination     : ' . json_encode($this->getValueCombination())
            . ',gap_backup           : []'
            . ',unused_gaps_comb     : []'
            . ',outofbound_text      : ' . '"' . $lng->txt('out_of_range') . '"'
            . ',combination_error    : ' . '"' . $lng->txt('please_select') . '"'
            . ',combination_text     : ' . '"' . $lng->txt('gap_combination') . '"'
            . ',copy_of_combination  : ' . '"' . $lng->txt('copy_of') . ' ' . $lng->txt('gap_combination') . '"'
            . ',gap_in_more_than_one : ' . '""'
            . ',gap_text             : ' . '"' . $lng->txt('gap') . '"'
            . ',ok_text              : ' . '"' . $lng->txt('ok') . '"'
            . ',cancel_text          : ' . '"' . $lng->txt('cancel') . '"'
        . '};';

        $DIC->ui()->mainTemplate()->addOnLoadCode(
            $cloze_settings_js
            . 'ClozeGapBuilder.Init();'
        );
        $DIC->ui()->mainTemplate()->addJavascript(
            './Modules/TestQuestionPool/templates/default/cloze_gap_builder.js'
        );


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
        $custom_template->setVariable('TYPE', $lng->txt('type'));
        $custom_template->setVariable('VALUES', $lng->txt('values'));
        $custom_template->setVariable('GAP_COMBINATION', $lng->txt('gap_combination'));
        $custom_template->setVariable('COPY', $lng->txt('copy_of'));
        $custom_template->setVariable('WHITESPACE_FRONT', $lng->txt('cloze_textgap_whitespace_before'));
        $custom_template->setVariable('WHITESPACE_BACK', $lng->txt('cloze_textgap_whitespace_after'));
        $custom_template->setVariable('WHITESPACE_MULTIPLE', $lng->txt('cloze_textgap_multiple_whitespace'));
        $template->setCurrentBlock('prop_generic');
        $template->setVariable('PROP_GENERIC', $custom_template->get());
        $template->parseCurrentBlock();
    }

    /**
     * @param $data string|array
     * @deprecated
     */
    public static function stripSlashesRecursive($a_data, bool $a_strip_html = true, string $a_allow = "")
    {
        if (is_array($a_data)) {
            foreach ($a_data as $k => $v) {
                if (is_array($v)) {
                    $a_data[$k] = self::stripSlashesRecursive($v, $a_strip_html, $a_allow);
                } else {
                    $a_data[$k] = ilUtil::stripSlashes($v, $a_strip_html, $a_allow);
                }
            }
        } else {
            if ($a_data != null) {
                $a_data = ilUtil::stripSlashes($a_data, $a_strip_html, $a_allow);
            }
        }

        return $a_data;
    }
}
