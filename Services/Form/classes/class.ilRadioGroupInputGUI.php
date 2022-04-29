<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * This class represents a property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRadioGroupInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
    protected array $options = array();
    protected string $value = "";
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("radio");
    }
    
    public function addOption(ilRadioOption $a_option) : void
    {
        $this->options[] = $a_option;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return $this->value;
    }
    
    public function setValueByArray(array $a_values) : void
    {
        $this->setValue((string) ($a_values[$this->getPostVar()] ?? ""));
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                $item->setValueByArray($a_values);
            }
        }
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        $val = $this->getInput();
        if ($this->getRequired() && trim($val) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        $ok = true;
        $value = $this->getInput();
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                if ($value == $option->getValue()) {
                    if (!$item->checkInput()) {
                        $ok = false;
                    }
                }
            }
        }
        return $ok;
    }

    public function getInput() : string
    {
        return $this->str($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function render() : string
    {
        $tpl = new ilTemplate("tpl.prop_radio.html", true, true, "Services/Form");
        
        foreach ($this->getOptions() as $option) {
            // information text for option
            if ($option->getInfo() != "") {
                $tpl->setCurrentBlock("radio_option_desc");
                $tpl->setVariable("RADIO_OPTION_DESC", $option->getInfo());
                $tpl->parseCurrentBlock();
            }
            
            
            if (count($option->getSubItems()) > 0) {
                if ($option->getValue() != $this->getValue()) {
                    // #10930
                    if ($this->global_tpl) {
                        $hop_id = $this->getFieldId() . "_" . $option->getValue();
                        $this->global_tpl->addOnloadCode(
                            "il.Form.hideSubForm('subform_$hop_id');"
                        );
                    }
                }
                $tpl->setCurrentBlock("radio_option_subform");
                $pf = new ilPropertyFormGUI();
                $pf->setMode("subform");
                $pf->setItems($option->getSubItems());
                $tpl->setVariable("SUB_FORM", $pf->getContent());
                $tpl->setVariable("SOP_ID", $this->getFieldId() . "_" . $option->getValue());
                if ($pf->getMultipart()) {
                    $this->getParentForm()->setMultipart(true);
                }
                $tpl->parseCurrentBlock();
                if ($pf->getMultipart()) {
                    $this->getParentForm()->setMultipart(true);
                }
            }

            $tpl->setCurrentBlock("prop_radio_option");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("VAL_RADIO_OPTION", $option->getValue());
            $tpl->setVariable("OP_ID", $this->getFieldId() . "_" . $option->getValue());
            $tpl->setVariable("FID", $this->getFieldId());
            if ($this->getDisabled() or $option->getDisabled()) {
                $tpl->setVariable('DISABLED', 'disabled="disabled" ');
            }
            if ($option->getValue() == $this->getValue()) {
                $tpl->setVariable(
                    "CHK_RADIO_OPTION",
                    'checked="checked"'
                );
            }
            $tpl->setVariable("TXT_RADIO_OPTION", $option->getTitle());
            
            
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("ID", $this->getFieldId());
        
        if ($this->getDisabled()) {
            $tpl->setVariable(
                "HIDDEN_INPUT",
                $this->getHiddenTag($this->getPostVar(), $this->getValue())
            );
        }

        return $tpl->get();
    }

    public function getItemByPostVar(string $a_post_var) : ?ilFormPropertyGUI
    {
        if ($this->getPostVar() == $a_post_var) {
            return $this;
        }

        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                if ($item->getType() != "section_header") {
                    $ret = $item->getItemByPostVar($a_post_var);
                    if (is_object($ret)) {
                        return $ret;
                    }
                }
            }
        }
        
        return null;
    }

    public function getTableFilterHTML() : string
    {
        return $this->render();
    }

    public function getSubInputItemsRecursive() : array
    {
        $subInputItems = parent::getSubInputItemsRecursive();
        foreach ($this->getOptions() as $option) {
            /**
             * @var $option ilRadioOption
             */
            $subInputItems = array_merge($subInputItems, $option->getSubInputItemsRecursive());
        }

        return $subInputItems;
    }

    public function getFormLabelFor() : string
    {
        return "";
    }
}
