<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssClozeTestCombinationVariantsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssClozeTestCombinationVariantsInputGUI extends ilAnswerWizardInputGUI
{
    public function setValue($a_value)
    {
        foreach ($this->values as $index => $value) {
            $this->values[$index]['points'] = $a_value['points'][$index];
        }
    }
    
    public function checkInput()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $lng = $DIC->language();
        
        $this->sanitizeSuperGlobalSubmitValue();
        
        $values = $_POST[$this->getPostVar()];
        
        $max = 0;
        if (is_array($values['points'])) {
            foreach ($values['points'] as $points) {
                if ($points > $max) {
                    $max = $points;
                }
                if (((strlen($points)) == 0) || (!is_numeric($points))) {
                    $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                    return false;
                }
                if ($this->minvalueShouldBeGreater()) {
                    if (trim($points) != "" &&
                        $this->getMinValue() !== false &&
                        $points <= $this->getMinValue()) {
                        $this->setAlert($lng->txt("form_msg_value_too_low"));
                        
                        return false;
                    }
                } else {
                    if (trim($points) != "" &&
                        $this->getMinValue() !== false &&
                        $points < $this->getMinValue()) {
                        $this->setAlert($lng->txt("form_msg_value_too_low"));
                        
                        return false;
                    }
                }
            }
        }
        if ($max == 0) {
            $this->setAlert($lng->txt("enter_enough_positive_points"));
            return false;
        }
        
        return true;
    }
    
    public function insert($a_tpl)
    {
        $tpl = new ilTemplate('tpl.prop_gap_combi_answers_input.html', true, true, 'Modules/TestQuestionPool');
        
        $gaps = array();
        
        foreach ($this->values as $varId => $variant) {
            foreach ($variant['gaps'] as $gapIndex => $answer) {
                $gaps[$gapIndex] = $gapIndex;

                $tpl->setCurrentBlock('gap_answer');
                $tpl->setVariable('GAP_ANSWER', $answer);
                $tpl->parseCurrentBlock();
            }
            
            $tpl->setCurrentBlock('variant');
            $tpl->setVariable('POSTVAR', $this->getPostVar());
            $tpl->setVariable('POINTS', $variant['points']);
            $tpl->parseCurrentBlock();
        }
        
        foreach ($gaps as $gapIndex) {
            $tpl->setCurrentBlock('gap_header');
            $tpl->setVariable('GAP_HEADER', 'Gap ' . ($gapIndex + 1));
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('POINTS_HEADER', 'Points');
            
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
