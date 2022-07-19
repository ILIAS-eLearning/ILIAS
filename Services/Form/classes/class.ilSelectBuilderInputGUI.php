<?php declare(strict_types=1);

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
 * Input GUI for the configuration of select input elements. E.g course custum field,
 * udf field, ...
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSelectBuilderInputGUI extends ilTextWizardInputGUI
{
    protected array $open_answer_indexes = array();
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        parent::__construct($a_title, $a_postvar);
    }
    
    public function getOpenAnswerIndexes() : array
    {
        return $this->open_answer_indexes;
    }
    
    public function setOpenAnswerIndexes(array $a_indexes) : void
    {
        $this->open_answer_indexes = $a_indexes;
    }
    
    // Mark an index as open answer
    public function addOpenAnswerIndex(string $a_idx) : void
    {
        $this->open_answer_indexes[] = $a_idx;
    }
    
    public function isOpenAnswerIndex(string $a_idx) : bool
    {
        return in_array($a_idx, $this->open_answer_indexes);
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        $foundvalues = $this->getInput();
        $this->setOpenAnswerIndexes(array());
        if (is_array($foundvalues)) {
            foreach ($foundvalues as $value) {
                if ($this->getRequired() && trim($value) == "") {
                    $this->setAlert($lng->txt("msg_input_is_required"));
                    return false;
                } elseif (strlen($this->getValidationRegexp())) {
                    if (!preg_match($this->getValidationRegexp(), $value)) {
                        $this->setAlert($lng->txt("msg_wrong_format"));
                        return false;
                    }
                }
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        foreach ($this->strArray($this->getPostVar() . '_open') as $oindex => $ovalue) {
            $this->addOpenAnswerIndex((string) $oindex);
        }

        return $this->checkSubItemsInput();
    }

    public function getInput() : array
    {
        return $this->strArray($this->getPostVar());
    }
    
    public function setValueByArray(array $a_values) : void
    {
        parent::setValueByArray($a_values);

        foreach ($this->strArray($this->getPostVar() . '_open') as $oindex => $ovalue) {
            $this->addOpenAnswerIndex($oindex);
        }
    }
    
    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_selectbuilder.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->values as $value) {
            if (!is_string($value)) {
                continue;
            }
            
            if (strlen($value)) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));

                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar() . "[$i]");
            #$tpl->setVariable('POST_VAR_OPEN',$this->getPostVar().'[open]'.'['.$i.']');
            $tpl->setVariable('POST_VAR_OPEN', $this->getPostVar() . '_open' . '[' . $i . ']');
            $tpl->setVariable('POST_VAR_OPEN_ID', $this->getPostVar() . '_open[' . $i . ']');
            $tpl->setVariable('TXT_OPEN', $lng->txt("form_open_answer"));
            
            if ($this->isOpenAnswerIndex((string) $i)) {
                $tpl->setVariable('PROP_OPEN_CHECKED', 'checked="checked"');
            }
            if ($this->getDisabled()) {
                $tpl->setVariable('PROP_OPEN_DISABLED', 'disabled="disabled"');
            }
            
            $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
            if ($this->getDisabled()) {
                $tpl->setVariable(
                    "DISABLED",
                    " disabled=\"disabled\""
                );
            }
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        $tpl = $this->tpl;
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Services/Form/templates/default/textwizard.js");
    }
}
