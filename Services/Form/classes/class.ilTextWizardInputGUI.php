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
 * This class represents a text wizard property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilTextWizardInputGUI extends ilTextInputGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected array $values = array();
    protected bool $allowMove = false;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        parent::__construct($a_title, $a_postvar);
        $this->validationRegexp = "";
    }

    public function setValues(array $a_values) : void
    {
        $this->values = $a_values;
    }

    /**
     * @param array|string $a_value
     */
    public function setValue($a_value) : void
    {
        $this->values = $a_value;
    }

    public function getValues() : array
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

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        $foundvalues = $this->getInput();
        if (count($foundvalues) > 0) {
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
        } elseif ($this->getRequired()) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        return $this->checkSubItemsInput();
    }

    public function getInput() : array
    {
        return $this->strArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }
    
    public function render(string $a_mode = "") : string
    {
        $tpl = new ilTemplate("tpl.prop_textwizardinput.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->values as $value) {
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
            $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
            
            if ($this->getDisabled()) {
                $tpl->setVariable(
                    "DISABLED",
                    " disabled=\"disabled\""
                );
            } else {
                $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            }
            
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());
        
        if (!$this->getDisabled()) {
            $this->tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
            $this->tpl->addJavascript("./Services/Form/templates/default/textwizard.js");
        }
        
        return $tpl->get();
    }
}
