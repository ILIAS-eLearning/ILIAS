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
 * This class represents a selection list property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSelectInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
{
    protected array $cust_attr = array();
    protected array $options = array();
    /**
     * @var string|array
     */
    protected $value;
    protected bool $hide_sub = false;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("select");
    }

    public function setOptions(array $a_options) : void
    {
        $this->options = $a_options;
    }

    public function getOptions() : array
    {
        return $this->options ?: array();
    }

    /**
     * Set Value.
     *
     * @param string|array $a_value Value
     */
    public function setValue($a_value) : void
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string|array	Value
    */
    public function getValue()
    {
        return $this->value;
    }
    
    
    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        $valid = true;
        if (!$this->getMulti()) {
            if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
                $valid = false;
            } elseif (!array_key_exists($this->str($this->getPostVar()), $this->getOptions())) {
                $this->setAlert($lng->txt('msg_invalid_post_input'));
                return false;
            }
        } else {
            $values = $this->strArray($this->getPostVar());
            foreach ($values as $value) {
                if (!array_key_exists($value, $this->getOptions())) {
                    $this->setAlert($lng->txt('msg_invalid_post_input'));
                    return false;
                }
            }
            if ($this->getRequired() && !trim(implode("", $values))) {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
     * @return string|string[]
     */
    public function getInput()
    {
        if (!$this->getMulti()) {
            return $this->str($this->getPostVar());
        }
        return $this->strArray($this->getPostVar());
    }

    public function addCustomAttribute(string $a_attr) : void
    {
        $this->cust_attr[] = $a_attr;
    }
    
    public function getCustomAttributes() : array
    {
        return $this->cust_attr;
    }

    public function render($a_mode = "") : string
    {
        $sel_value = "";
        $tpl = new ilTemplate("tpl.prop_select.html", true, true, "Services/Form");
        
        foreach ($this->getCustomAttributes() as $attr) {
            $tpl->setCurrentBlock('cust_attr');
            $tpl->setVariable('CUSTOM_ATTR', $attr);
            $tpl->parseCurrentBlock();
        }
        
        // determine value to select. Due to accessibility reasons we
        // should always select a value (per default the first one)
        $first = true;
        foreach ($this->getOptions() as $option_value => $option_text) {
            if ($first) {
                $sel_value = $option_value;
            }
            $first = false;
            if ((string) $option_value == (string) $this->getValue()) {
                $sel_value = $option_value;
            }
        }
        foreach ($this->getOptions() as $option_value => $option_text) {
            $tpl->setCurrentBlock("prop_select_option");
            $tpl->setVariable("VAL_SELECT_OPTION", ilLegacyFormElementsUtil::prepareFormOutput((string) $option_value));
            if ((string) $sel_value == (string) $option_value) {
                $tpl->setVariable(
                    "CHK_SEL_OPTION",
                    'selected="selected"'
                );
            }
            $tpl->setVariable("TXT_SELECT_OPTION", $option_text);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("ID", $this->getFieldId());
        
        $postvar = $this->getPostVar();
        if ($this->getMulti() && substr($postvar, -2) != "[]") {
            $postvar .= "[]";
        }

        $tpl->setVariable("POST_VAR", $postvar);
        if ($this->getDisabled()) {
            if ($this->getMulti()) {
                $value = $this->getMultiValues();
                $hidden = "";
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $hidden .= $this->getHiddenTag($postvar, $item);
                    }
                }
            } else {
                $hidden = $this->getHiddenTag($postvar, (string) $this->getValue());
            }
            if ($hidden) {
                $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
                $tpl->setVariable("HIDDEN_INPUT", $hidden);
            }
        }
        
        // multi icons
        if ($this->getMulti() && !$a_mode && !$this->getDisabled()) {
            $tpl->touchBlock("inline_in_bl");
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        $tpl->setVariable("ARIA_LABEL", ilLegacyFormElementsUtil::prepareFormOutput($this->getTitle()));

        return $tpl->get();
    }
    
    public function insert(ilTemplate $a_tpl) : void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }

    public function getToolbarHTML() : string
    {
        $html = $this->render("toolbar");
        return $html;
    }
    
    /**
     * Set initial sub form visibility, optionally add dynamic value-based condition
     */
    public function setHideSubForm(
        bool $a_value,
        ?string $a_condition = null
    ) : void {
        $this->hide_sub = $a_value;
        
        if ($a_condition) {
            $this->addCustomAttribute('onchange="if(this.value ' . $a_condition . ')' .
                ' { il.Form.showSubForm(\'subform_' . $this->getFieldId() . '\', \'il_prop_cont_' . $this->getFieldId() . '\'); }' .
                ' else { il.Form.hideSubForm(\'subform_' . $this->getFieldId() . '\'); };"');
        }
    }

    public function hideSubForm() : bool
    {
        return $this->hide_sub;
    }
}
