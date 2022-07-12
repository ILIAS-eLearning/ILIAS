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
 * This class represents a multi selection list property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMultiSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    protected array $options = [];
    protected array $value = [];
    protected bool $select_all = false;
    protected bool $selected_first = false;
    private int $width = 160;
    private int $height = 100;
    protected string $widthUnit = 'px';
    protected string $heightUnit = 'px';
    protected array $custom_attributes = [];
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("multi_select");
        $this->setValue(array());
    }

    public function setWidth(int $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function setHeight(int $a_height) : void
    {
        $this->height = $a_height;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    /**
     * @param array<string,string> $a_options Options. Array ("value" => "option_text")
     */
    public function setOptions(array $a_options) : void
    {
        $this->options = $a_options;
    }

    /**
     * @return array<string,string>
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @param string[]
     */
    public function setValue(array $a_array) : void
    {
        $this->value = $a_array;
    }

    /**
     * @return string[]
     */
    public function getValue() : array
    {
        return is_array($this->value) ? $this->value : array();
    }
    
    /**
     * @param string[]
     */
    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? []);
    }

    public function enableSelectAll(bool $a_value) : void
    {
        $this->select_all = $a_value;
    }
    
    public function enableSelectedFirst(bool $a_value) : void
    {
        $this->selected_first = $a_value;
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        $val = $this->getInput();
        if ($this->getRequired() && count($val) == 0) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        if (count($val) > 0) {
            $options = array_map(function ($k) {
                return (string) $k;
            }, array_keys($this->getOptions()));
            foreach ($val as $key => $val2) {
                if ($key != 0 || $val2 != "") {
                    if (!in_array((string) $val2, $options)) {
                        $this->setAlert($lng->txt("msg_unknown_value"));
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getInput() : array
    {
        return $this->strArray($this->getPostVar());
    }

    public function render() : string
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_multi_select.html", true, true, "Services/Form");
        $values = $this->getValue();

        $options = $this->getOptions();
        if ($options) {
            if ($this->select_all) {
                // enable select all toggle
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("VAL", "");
                $tpl->setVariable("ID_VAL", ilLegacyFormElementsUtil::prepareFormOutput("all__toggle"));
                $tpl->setVariable("IID", $this->getFieldId());
                $tpl->setVariable("TXT_OPTION", "<em>" . $lng->txt("select_all") . "</em>");
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
                
                $tpl->setVariable("TOGGLE_FIELD_ID", $this->getFieldId());
                $tpl->setVariable("TOGGLE_ALL_ID", $this->getFieldId() . "_all__toggle");
                $tpl->setVariable("TOGGLE_ALL_CBOX_ID", $this->getFieldId() . "_");
            }
            
            if ($this->selected_first) {
                // move selected values to top
                $tmp_checked = $tmp_unchecked = array();
                foreach ($options as $option_value => $option_text) {
                    if (in_array($option_value, $values)) {
                        $tmp_checked[$option_value] = $option_text;
                    } else {
                        $tmp_unchecked[$option_value] = $option_text;
                    }
                }
                $options = $tmp_checked + $tmp_unchecked;
                unset($tmp_checked);
                unset($tmp_unchecked);
            }
            
            foreach ($options as $option_value => $option_text) {
                $tpl->setCurrentBlock("item");
                if ($this->getDisabled()) {
                    $tpl->setVariable(
                        "DISABLED",
                        " disabled=\"disabled\""
                    );
                }
                if (in_array($option_value, $values)) {
                    $tpl->setVariable(
                        "CHECKED",
                        " checked=\"checked\""
                    );
                }

                $tpl->setVariable("VAL", ilLegacyFormElementsUtil::prepareFormOutput($option_value));
                $tpl->setVariable("ID_VAL", ilLegacyFormElementsUtil::prepareFormOutput($option_value));
                $tpl->setVariable("IID", $this->getFieldId());
                $tpl->setVariable("TXT_OPTION", $option_text);
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
            }
        }
        
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("CUSTOM_ATTRIBUTES", implode(' ', $this->getCustomAttributes()));

        if ($this->getWidth()) {
            $tpl->setVariable("WIDTH", $this->getWidth() . ($this->getWidthUnit()?:''));
        }
        if ($this->getHeight()) {
            $tpl->setVariable("HEIGHT", $this->getHeight() . ($this->getHeightUnit()?:''));
        }
        
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

    public function getCustomAttributes() : array
    {
        return $this->custom_attributes;
    }

    public function setCustomAttributes(array $custom_attributes) : void
    {
        $this->custom_attributes = $custom_attributes;
    }


    public function addCustomAttribute(string $custom_attribute) : void
    {
        $this->custom_attributes[] = $custom_attribute;
    }

    public function getWidthUnit() : string
    {
        return $this->widthUnit;
    }

    public function setWidthUnit(string $widthUnit) : void
    {
        $this->widthUnit = $widthUnit;
    }

    public function getHeightUnit() : string
    {
        return $this->heightUnit;
    }

    public function setHeightUnit(string $heightUnit) : void
    {
        $this->heightUnit = $heightUnit;
    }

    public function unserializeData(string $a_data) : void
    {
        $data = unserialize($a_data);

        if (is_array($a_data)) {
            $this->setValue($data);
        } else {
            $this->setValue([]);
        }
    }
}
