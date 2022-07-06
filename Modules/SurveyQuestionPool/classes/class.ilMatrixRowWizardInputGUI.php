<?php

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
 *
 *********************************************************************/

/**
 * This class represents a survey question category wizard property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilMatrixRowWizardInputGUI extends ilTextInputGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ?SurveyCategories $values = null;
    protected bool $allowMove = false;
    protected bool $show_wizard = false;
    protected bool $show_save_phrase = false;
    protected string $categorytext;
    protected string $labeltext;
    protected bool $use_other_answer;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);

        $this->show_wizard = false;
        $this->show_save_phrase = false;
        $this->categorytext = $lng->txt('row_text');
        $this->use_other_answer = false;

        $this->setMaxLength(1000); // #6803
    }
    
    public function getUseOtherAnswer() : bool
    {
        return $this->use_other_answer;
    }
    
    public function setUseOtherAnswer(bool $a_value) : void
    {
        $this->use_other_answer = $a_value;
    }
    
    /**
     * @param string|array $a_value
     */
    public function setValue($a_value) : void
    {
        $this->values = new SurveyCategories();
        if (is_array($a_value) && is_array($a_value['answer'])) {
            foreach ($a_value['answer'] as $index => $value) {
                $this->values->addCategory($value, $a_value['other'][$index] ?? 0);
            }
        }
    }

    public function setValues(SurveyCategories $a_values) : void
    {
        $this->values = $a_values;
    }

    public function getValues() : SurveyCategories
    {
        return $this->values;
    }

    public function setAllowMove(bool $a_allow_move) : void
    {
        $this->allowMove = $a_allow_move;
    }

    public function getAllowMove() : bool
    {
        return $this->allowMove;
    }
    
    public function setShowWizard(bool $a_value) : void
    {
        $this->show_wizard = $a_value;
    }
    
    public function getShowWizard() : bool
    {
        return $this->show_wizard;
    }
    
    public function setCategoryText(string $a_text) : void
    {
        $this->categorytext = $a_text;
    }
    
    public function getCategoryText() : string
    {
        return $this->categorytext;
    }
    
    public function setLabelText(string $a_text) : void
    {
        $this->labeltext = $a_text;
    }
    
    public function getLabelText() : string
    {
        return $this->labeltext;
    }
    
    public function setShowSavePhrase(bool $a_value) : void
    {
        $this->show_save_phrase = $a_value;
    }
    
    public function getShowSavePhrase() : bool
    {
        return $this->show_save_phrase;
    }
    
    public function checkInput() : bool
    {
        $lng = $this->lng;
        $foundvalues = $this->getInput();
        if (count($foundvalues) > 0) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $idx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && ($this->getRequired() && (!$foundvalues['other'][$idx]))) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        return $this->checkSubItemsInput();
    }

    public function getInput() : array
    {
        $val = $this->arrayArray($this->getPostVar());
        $val = ilArrayUtil::stripSlashesRecursive($val);
        return $val;
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_matrixrowwizardinput.html", true, true, "Modules/SurveyQuestionPool");
        if (is_object($this->values)) {
            for ($i = 0; $i < $this->values->getCategoryCount(); $i++) {
                $cat = $this->values->getCategory($i);
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $cat->title));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("prop_label_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $cat->label));
                $tpl->parseCurrentBlock();

                if ($this->getUseOtherAnswer()) {
                    $tpl->setCurrentBlock("other_answer_checkbox");
                    $tpl->setVariable("POST_VAR", $this->getPostVar());
                    $tpl->setVariable("OTHER_ID", $this->getPostVar() . "[other][$i]");
                    $tpl->setVariable("ROW_NUMBER", $i);
                    if ($cat->other) {
                        $tpl->setVariable("CHECKED_OTHER", ' checked="checked"');
                    }
                    $tpl->parseCurrentBlock();
                }

                if ($this->getAllowMove()) {
                    $tpl->setCurrentBlock("move");
                    $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                    $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                    $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                    $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                    $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                    $tpl->parseCurrentBlock();
                }
                
                $tpl->setCurrentBlock("row");
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->setVariable("ROW_NUMBER", $i);
                $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("ID_LABEL", $this->getPostVar() . "[label][$i]");
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SIZE_LABEL", 15);
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
                    $tpl->setVariable("DISABLED_LABEL", " disabled=\"disabled\"");
                }

                $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->getShowWizard()) {
            $tpl->setCurrentBlock("wizard");
            $tpl->setVariable("CMD_WIZARD", 'cmd[wizard' . $this->getFieldId() . ']');
            $tpl->setVariable("WIZARD_BUTTON", ilUtil::getImagePath('wizard.svg'));
            $tpl->setVariable("WIZARD_TEXT", $lng->txt('add_phrase'));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->getShowSavePhrase()) {
            $tpl->setCurrentBlock('savephrase');
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("VALUE_SAVE_PHRASE", $lng->txt('save_phrase'));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->getUseOtherAnswer()) {
            $tpl->setCurrentBlock('other_answer_title');
            $tpl->setVariable("OTHER_TEXT", $lng->txt('use_other_answer'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getCategoryText());
        $tpl->setVariable("LABEL_TEXT", $this->getLabelText());
        $tpl->setVariable("ACTIONS_TEXT", $lng->txt('actions'));
    
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        $tpl = $this->tpl;
        $tpl->addJavaScript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavaScript("./Modules/SurveyQuestionPool/js/matrixrowwizard.js");
    }
}
