<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTextSubsetCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssAnswerCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
    /**
     * @var bool
     */
    protected $hidePointsEnabled = false;
    
    /**
     * @return bool
     */
    public function isHidePointsEnabled() : bool
    {
        return $this->hidePointsEnabled;
    }
    
    /**
     * @param bool $hidePointsEnabled
     */
    public function setHidePointsEnabled(bool $hidePointsEnabled)
    {
        $this->hidePointsEnabled = $hidePointsEnabled;
    }
    
    public function setValue($a_value)
    {
        if (is_array($a_value)) {
            if (is_array($a_value['points'])) {
                foreach ($a_value['points'] as $index => $value) {
                    $this->values[$index]->setPoints($a_value['points'][$index]);
                }
            }
        }
    }
    
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->sanitizeSuperGlobalSubmitValue();
        $foundvalues = $_POST[$this->getPostVar()];
        
        if ($this->isHidePointsEnabled()) {
            return true;
        }
        
        if (is_array($foundvalues)) {
            // check points
            $max = 0;
            if (is_array($foundvalues['points'])) {
                foreach ($foundvalues['points'] as $points) {
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
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        return $this->checkSubItemsInput();
    }
    
    public function insert($a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate("tpl.prop_textsubsetcorrection_input.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (!$this->isHidePointsEnabled()) {
                $tpl->setCurrentBlock("points");
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->setVariable("ROW_NUMBER", $i);
                $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
                $tpl->setVariable("POINTS", ilUtil::prepareFormOutput($value->getPoints()));
                $tpl->parseCurrentBlock();
            }
            
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("ANSWER", ilUtil::prepareFormOutput($value->getAnswertext()));
            $tpl->parseCurrentBlock();
            $i++;
        }
        
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getTextInputLabel($lng));
        
        if (!$this->isHidePointsEnabled()) {
            $tpl->setVariable("POINTS_TEXT", $this->getPointsInputLabel($lng));
        }
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
