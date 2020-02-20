<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.ilSingleChoiceWizardInputGUI.php";

class ilEssayKeywordWizardInputGUI extends ilSingleChoiceWizardInputGUI
{
    /**
     * Set Value.
     *
     * @param    string    $a_value    Value
     */
    public function setValue($a_value)
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
                    $answer = new ASS_AnswerMultipleResponseImage($value, $a_value['points'][$index], $index, $a_value['points_unchecked'][$index], $a_value['imagename'][$index]);
                    array_push($this->values, $answer);
                }
            }
        }
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return    boolean        Input ok, true/false
     */
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];

        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive(
                $_POST[$this->getPostVar()],
                false,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString(
                                                                             "assessment"
                                                                         )
            );
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && (strlen($foundvalues['imagename'][$aidx]) == 0)) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
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

    /**
     * Insert property html
     *
     * @return    int    Size
     */
    public function insert($a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.prop_essaykeywordswizardinput.html", true, true, "Modules/TestQuestionPool");
        $i   = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPointsChecked()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('singleline');
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
                $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            } else {
                if (!$this->getSingleline()) {
                    if (is_object($value)) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                        $tpl->parseCurrentBlock();
                    }
                }
            }

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));
        $tpl->setVariable("POINTS_CHECKED_TEXT", $lng->txt('checkbox_checked'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/essaykeywordwizard.js");
    }
}
