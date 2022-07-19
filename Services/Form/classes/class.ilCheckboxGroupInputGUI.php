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
 * This class represents a property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilCheckboxGroupInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    protected array $options = array();
    protected ?array $value = null;
    protected bool $use_values_as_keys = false;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("checkboxgroup");
    }

    /**
     * Set use values as keys
     */
    public function setUseValuesAsKeys(bool $a_val) : void
    {
        $this->use_values_as_keys = $a_val;
    }
    
    public function getUseValuesAsKeys() : bool
    {
        return $this->use_values_as_keys;
    }

    /**
     * @param ilCheckboxOption|ilCheckboxInputGUI $a_option
     */
    public function addOption($a_option) : void
    {
        $this->options[] = $a_option;
    }

    /**
     * Set Options.
     *
     * @param	array	$a_options	Options. Array ("value" => "option_text")
     */
    public function setOptions(array $a_options) : void
    {
        foreach ($a_options as $key => $label) {
            if (is_string($label)) {
                $chb = new ilCheckboxInputGUI($label, $key);
                $this->options[] = $chb;
            } elseif ($label instanceof ilCheckboxInputGUI) {
                $this->options[] = $label;
            }
        }
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    public function setValue(?array $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : ?array
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? null);
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                $item->setValueByArray($a_values);
            }
        }
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        $values = $this->strArray($this->getPostVar());
        if ($this->getRequired() && count($values) === 0) {
            $this->setAlert($lng->txt('msg_input_is_required'));
            return false;
        }

        $ok = true;
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                $item_ok = $item->checkInput();
                if (!$item_ok && in_array($option->getValue(), $values)) {
                    $ok = false;
                }
            }
        }
        return $ok;
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
    
    public function getToolbarHTML() : string
    {
        return $this->render('toolbar');
    }
    
    protected function render($a_mode = '') : string
    {
        $tpl = new ilTemplate("tpl.prop_checkbox_group.html", true, true, "Services/Form");

        foreach ($this->getOptions() as $option) {
            // information text for option
            if ($option->getInfo() != "") {
                $tpl->setCurrentBlock("checkbox_option_desc");
                $tpl->setVariable("CHECKBOX_OPTION_DESC", $option->getInfo());
                $tpl->parseCurrentBlock();
            }


            if (count($option->getSubItems()) > 0) {
                $tpl->setCurrentBlock("checkbox_option_subform");
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

            $tpl->setCurrentBlock("prop_checkbox_option");
            
            if (!$this->getUseValuesAsKeys()) {
                $tpl->setVariable("POST_VAR", $this->getPostVar() . '[]');
                $tpl->setVariable("VAL_CHECKBOX_OPTION", $option->getValue());
            } else {
                $tpl->setVariable("POST_VAR", $this->getPostVar() . '[' . $option->getValue() . ']');
                $tpl->setVariable("VAL_CHECKBOX_OPTION", "1");
            }
            
            $tpl->setVariable("OP_ID", $this->getFieldId() . "_" . $option->getValue());
            $tpl->setVariable("FID", $this->getFieldId());
            
            if ($this->getDisabled() or $option->getDisabled()) {
                $tpl->setVariable('DISABLED', 'disabled="disabled" ');
            }

            if (is_array($this->getValue())) {
                if (!$this->getUseValuesAsKeys()) {
                    if (in_array($option->getValue(), $this->getValue())) {
                        $tpl->setVariable(
                            "CHK_CHECKBOX_OPTION",
                            'checked="checked"'
                        );
                    }
                } else {
                    $cval = $this->getValue();
                    if (isset($cval[$option->getValue()]) && $cval[$option->getValue()] == 1) {
                        $tpl->setVariable(
                            "CHK_CHECKBOX_OPTION",
                            'checked="checked"'
                        );
                    }
                }
            }
            $tpl->setVariable("TXT_CHECKBOX_OPTION", $option->getTitle());


            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("ID", $this->getFieldId());

        return $tpl->get();
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
}
