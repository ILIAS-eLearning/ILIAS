<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssSingleChoiceCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssSingleChoiceCorrectionsInputGUI extends ilSingleChoiceWizardInputGUI
{
    /**
     * @var assSingleChoice
     */
    protected $qstObject;
    
    public function setValue($a_value)
    {
        if (is_array($a_value)) {
            if (is_array($a_value['points'])) {
                foreach ($a_value['points'] as $index => $value) {
                    $this->values[$index]->setPoints($value);
                }
            }
        }
    }
    
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        
        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
        }
        $foundvalues = $_POST[$this->getPostVar()];
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
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $lng = $DIC->language();
        
        $tpl = new ilTemplate("tpl.prop_singlechoicecorrection_input.html", true, true, "Modules/TestQuestionPool");
        
        $i = 0;
        
        if ($this->values === null) {
            $this->values = $this->value;
        }
        
        foreach ($this->values as $value) {
            if ($this->qstObject->isSingleline) {
                if (strlen($value->getImage())) {
                    $imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
                    if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                        if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage())) {
                            $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
                        }
                    }
                    
                    $tpl->setCurrentBlock('image');
                    $tpl->setVariable('SRC_IMAGE', $imagename);
                    $tpl->setVariable('IMAGE_NAME', $value->getImage());
                    $tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock('image');
                    $tpl->touchBlock('image');
                    $tpl->parseCurrentBlock();
                }
            }
            
            $tpl->setCurrentBlock("answer");
            $tpl->setVariable("ANSWER", $value->getAnswertext());
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("prop_points_propval");
            $tpl->setVariable("POINTS_POST_VAR", $this->getPostVar());
            $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("row");
            $tpl->parseCurrentBlock();
        }
        
        if ($this->qstObject->isSingleline) {
            $tpl->setCurrentBlock("image_heading");
            $tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
            $tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setCurrentBlock("points_heading");
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        $tpl->parseCurrentBlock();
        
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
