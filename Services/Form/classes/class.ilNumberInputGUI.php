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
 * This class represents a number property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNumberInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected ?float $value = null;
    protected int $maxlength = 200;
    protected int $size = 40;
    protected string $suffix = "";
    protected ?float $minvalue = null;
    protected bool $minvalueShouldBeGreater = false;
    protected bool $minvalue_visible = false;
    protected ?float $maxvalue = null;
    protected bool $maxvalueShouldBeLess = false;
    protected bool $maxvalue_visible = false;
    protected int $decimals = 0;
    protected bool $allow_decimals = false;
    protected bool $client_side_validation = false;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
    }

    public function setSuffix(string $a_value) : void
    {
        $this->suffix = $a_value;
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function setValue(?string $a_value) : void
    {
        if ($a_value == "" || is_null($a_value)) {
            $this->value = null;
            return;
        }
        $this->value = (float) str_replace(',', '.', $a_value);
        
        // integer
        if (!$this->areDecimalsAllowed()) {
            $this->value = round($this->value);
        }
        // float
        elseif ($this->getDecimals() > 0) {
            // get rid of unwanted decimals
            $this->value = round($this->value, $this->getDecimals());

            // pad value to specified format
            $this->value = (float) number_format($this->value, $this->getDecimals(), ".", "");
        }
    }

    public function getValue() : ?float
    {
        return $this->value;
    }

    public function setMaxLength(int $a_maxlength) : void
    {
        $this->maxlength = $a_maxlength;
    }

    public function getMaxLength() : int
    {
        return $this->maxlength;
    }

    // true if the minimum value should be greater than minvalue
    public function setMinvalueShouldBeGreater(bool $a_bool) : void
    {
        $this->minvalueShouldBeGreater = $a_bool;
    }
    
    public function minvalueShouldBeGreater() : bool
    {
        return $this->minvalueShouldBeGreater;
    }

    //	true if the maximum value should be less than maxvalue
    public function setMaxvalueShouldBeLess(bool $a_bool) : void
    {
        $this->maxvalueShouldBeLess = $a_bool;
    }

    public function maxvalueShouldBeLess() : bool
    {
        return $this->maxvalueShouldBeLess;
    }
    
    public function setSize(int $a_size) : void
    {
        $this->size = $a_size;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue((string) ($a_values[$this->getPostVar()] ?? ""));
    }

    public function getSize() : int
    {
        return $this->size;
    }
    
    public function setMinValue(
        float $a_minvalue,
        bool $a_display_always = false
    ) : void {
        $this->minvalue = $a_minvalue;
        $this->minvalue_visible = $a_display_always;
    }

    public function getMinValue() : ?float
    {
        return $this->minvalue;
    }

    public function setMaxValue(
        float $a_maxvalue,
        bool $a_display_always = false
    ) : void {
        $this->maxvalue = $a_maxvalue;
        $this->maxvalue_visible = $a_display_always;
    }

    public function getMaxValue() : ?float
    {
        return $this->maxvalue;
    }

    public function setDecimals(int $a_decimals) : void
    {
        $this->decimals = $a_decimals;
        if ($this->decimals) {
            $this->allowDecimals(true);
        }
    }

    public function getDecimals() : int
    {
        return $this->decimals;
    }

    public function allowDecimals(bool $a_value) : void
    {
        $this->allow_decimals = $a_value;
    }

    public function areDecimalsAllowed() : bool
    {
        return $this->allow_decimals;
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        $val = trim($this->str($this->getPostVar()));
        if ($this->getRequired() && $val == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        $val = str_replace(',', '.', $val);

        if ($val != "" && !is_numeric($val)) {
            $this->minvalue_visible = true;
            $this->maxvalue_visible = true;
            $this->setAlert($lng->txt("form_msg_numeric_value_required"));
            return false;
        }

        if ($this->minvalueShouldBeGreater()) {
            if ($val != "" && $this->getMinValue() !== null &&
                $val <= $this->getMinValue()) {
                $this->minvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_low"));
                return false;
            }
        } else {
            if ($val != "" &&
                $this->getMinValue() !== null &&
                $val < $this->getMinValue()) {
                $this->minvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_low"));
                return false;
            }
        }

        if ($this->maxvalueShouldBeLess()) {
            if ($val != "" &&
                $this->getMaxValue() !== null &&
                $val >= $this->getMaxValue()) {
                $this->maxvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_high"));
                return false;
            }
        } else {
            if ($val != "" &&
                $this->getMaxValue() !== null &&
                $val > $this->getMaxValue()) {
                $this->maxvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_high"));
                return false;
            }
        }
        
        return $this->checkSubItemsInput();
    }

    public function getInput() : ?float
    {
        $value = $this->str($this->getPostVar());
        if (trim($value) == "") {
            return null;
        }
        return (float) str_replace(',', '.', $value);
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
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_number.html", true, true, "Services/Form");

        if (strlen((string) $this->getValue())) {
            $tpl->setCurrentBlock("prop_number_propval");
            $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getValue()));
            $tpl->parseCurrentBlock();
        }
        $tpl->setCurrentBlock("prop_number");
        
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("SIZE", $this->getSize());
        $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
        if (strlen($this->getSuffix())) {
            $tpl->setVariable("INPUT_SUFFIX", $this->getSuffix());
        }
        if ($this->getDisabled()) {
            $tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }

        if ($this->client_side_validation) {
            $tpl->setVariable("JS_DECIMALS_ALLOWED", (int) $this->areDecimalsAllowed());
            $tpl->setVariable("JS_ID", $this->getFieldId());
        }


        // constraints
        $constraints = "";
        $delim = "";
        if ($this->areDecimalsAllowed() && $this->getDecimals() > 0) {
            $constraints = $lng->txt("form_format") . ": ###." . str_repeat("#", $this->getDecimals());
            $delim = ", ";
        }
        if ($this->getMinValue() !== null && $this->minvalue_visible) {
            $constraints .= $delim . $lng->txt("form_min_value") . ": " . (($this->minvalueShouldBeGreater()) ? "&gt; " : "") . $this->getMinValue();
            $delim = ", ";
        }
        if ($this->getMaxValue() !== null && $this->maxvalue_visible) {
            $constraints .= $delim . $lng->txt("form_max_value") . ": " . (($this->maxvalueShouldBeLess()) ? "&lt; " : "") . $this->getMaxValue();
            $delim = ", ";
        }
        if ($constraints != "") {
            $tpl->setVariable("TXT_NUMBER_CONSTRAINTS", $constraints);
        }
        
        if ($this->getRequired()) {
            $tpl->setVariable("REQUIRED", "required=\"required\"");
        }
        
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    public function getPostValueForComparison() : ?float
    {
        return $this->getInput();
    }

    public function setClientSideValidation(bool $validate) : void
    {
        $this->client_side_validation = $validate;
    }
}
