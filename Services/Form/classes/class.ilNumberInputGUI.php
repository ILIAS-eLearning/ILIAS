<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
* This class represents a number property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilNumberInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected $value;
    protected $maxlength = 200;
    protected $size = 40;
    protected $suffix;
    protected $minvalue = false;
    protected $minvalueShouldBeGreater = false;
    protected $minvalue_visible = false;
    protected $maxvalue = false;
    protected $maxvalueShouldBeLess = false;
    protected $maxvalue_visible = false;
    protected $decimals;
    protected $allow_decimals = false;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
    }

    /**
    * Set suffix.
    *
    * @param	string	$a_value	suffix
    */
    public function setSuffix($a_value)
    {
        $this->suffix = $a_value;
    }

    /**
    * Get suffix.
    *
    * @return	string	suffix
    */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->value = str_replace(',', '.', $a_value);
        
        // empty strings are allowed
        if ($this->value != "") {
            // integer
            if (!$this->areDecimalsAllowed()) {
                $this->value = round($this->value);
            }
            // float
            elseif ($this->getDecimals() > 0) {
                // get rid of unwanted decimals
                $this->value = round($this->value, $this->getDecimals());

                // pad value to specified format
                $this->value = number_format($this->value, $this->getDecimals(), ".", "");
            }
        }
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }

    /**
    * Set Max Length.
    *
    * @param	int	$a_maxlength	Max Length
    */
    public function setMaxLength($a_maxlength)
    {
        $this->maxlength = $a_maxlength;
    }

    /**
    * Get Max Length.
    *
    * @return	int	Max Length
    */
    public function getMaxLength()
    {
        return $this->maxlength;
    }

    /**
    * Set minvalueShouldBeGreater
    *
    * @param	boolean	$a_bool	true if the minimum value should be greater than minvalue
    */
    public function setMinvalueShouldBeGreater($a_bool)
    {
        $this->minvalueShouldBeGreater = $a_bool;
    }
    
    /**
    * Get minvalueShouldBeGreater
    *
    * @return	boolean	true if the minimum value should be greater than minvalue
    */
    public function minvalueShouldBeGreater()
    {
        return $this->minvalueShouldBeGreater;
    }

    /**
    * Set maxvalueShouldBeLess
    *
    * @param	boolean	$a_bool	true if the maximum value should be less than maxvalue
    */
    public function setMaxvalueShouldBeLess($a_bool)
    {
        $this->maxvalueShouldBeLess = $a_bool;
    }
    
    /**
    * Get maxvalueShouldBeLess
    *
    * @return	boolean	true if the maximum value should be less than maxvalue
    */
    public function maxvalueShouldBeLess()
    {
        return $this->maxvalueShouldBeLess;
    }
    
    /**
    * Set Size.
    *
    * @param	int	$a_size	Size
    */
    public function setSize($a_size)
    {
        $this->size = $a_size;
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    /**
    * Get Size.
    *
    * @return	int	Size
    */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
    * Set Minimum Value.
    *
    * @param	float	$a_minvalue	Minimum Value
    * @param	bool	$a_display_always
    */
    public function setMinValue($a_minvalue, $a_display_always = false)
    {
        $this->minvalue = $a_minvalue;
        $this->minvalue_visible = (bool) $a_display_always;
    }

    /**
    * Get Minimum Value.
    *
    * @return	float	Minimum Value
    */
    public function getMinValue()
    {
        return $this->minvalue;
    }

    /**
    * Set Maximum Value.
    *
    * @param	float	$a_maxvalue	Maximum Value
    * @param	bool	$a_display_always
    */
    public function setMaxValue($a_maxvalue, $a_display_always = false)
    {
        $this->maxvalue = $a_maxvalue;
        $this->maxvalue_visible = (bool) $a_display_always;
    }

    /**
    * Get Maximum Value.
    *
    * @return	float	Maximum Value
    */
    public function getMaxValue()
    {
        return $this->maxvalue;
    }

    /**
    * Set Decimal Places.
    *
    * @param	int	$a_decimals	Decimal Places
    */
    public function setDecimals($a_decimals)
    {
        $this->decimals = (int) $a_decimals;
        if ($this->decimals) {
            $this->allowDecimals(true);
        }
    }

    /**
    * Get Decimal Places.
    *
    * @return	int	Decimal Places
    */
    public function getDecimals()
    {
        return $this->decimals;
    }
    
    /**
    * Toggle Decimals
    *
    * @param	bool	$a_value
    */
    public function allowDecimals($a_value)
    {
        $this->allow_decimals = (bool) $a_value;
    }
    
    /**
     *
     *
     * @return bool
     */
    public function areDecimalsAllowed()
    {
        return $this->allow_decimals;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        if (trim($_POST[$this->getPostVar()]) != "" &&
            !is_numeric(str_replace(',', '.', $_POST[$this->getPostVar()]))) {
            $this->minvalue_visible = true;
            $this->maxvalue_visible = true;
            $this->setAlert($lng->txt("form_msg_numeric_value_required"));
            return false;
        }

        if ($this->minvalueShouldBeGreater()) {
            if (trim($_POST[$this->getPostVar()]) != "" &&
                $this->getMinValue() !== false &&
                $_POST[$this->getPostVar()] <= $this->getMinValue()) {
                $this->minvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_low"));
                return false;
            }
        } else {
            if (trim($_POST[$this->getPostVar()]) != "" &&
                $this->getMinValue() !== false &&
                $_POST[$this->getPostVar()] < $this->getMinValue()) {
                $this->minvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_low"));
                return false;
            }
        }

        if ($this->maxvalueShouldBeLess()) {
            if (trim($_POST[$this->getPostVar()]) != "" &&
                $this->getMaxValue() !== false &&
                $_POST[$this->getPostVar()] >= $this->getMaxValue()) {
                $this->maxvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_high"));
                return false;
            }
        } else {
            if (trim($_POST[$this->getPostVar()]) != "" &&
                $this->getMaxValue() !== false &&
                $_POST[$this->getPostVar()] > $this->getMaxValue()) {
                $this->maxvalue_visible = true;
                $this->setAlert($lng->txt("form_msg_value_too_high"));
                return false;
            }
        }
        
        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Insert property html
    */
    public function render()
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_number.html", true, true, "Services/Form");

        if (strlen($this->getValue())) {
            $tpl->setCurrentBlock("prop_number_propval");
            $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
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
        
        /*
        $tpl->setVariable("JS_DECIMALS_ALLOWED", (int)$this->areDecimalsAllowed());
        */
        
        // constraints
        if ($this->areDecimalsAllowed() && $this->getDecimals() > 0) {
            $constraints = $lng->txt("form_format") . ": ###." . str_repeat("#", $this->getDecimals());
            $delim = ", ";
        }
        if ($this->getMinValue() !== false && $this->minvalue_visible) {
            $constraints .= $delim . $lng->txt("form_min_value") . ": " . (($this->minvalueShouldBeGreater()) ? "&gt; " : "") . $this->getMinValue();
            $delim = ", ";
        }
        if ($this->getMaxValue() !== false && $this->maxvalue_visible) {
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

    /**
     * parse post value to make it comparable
     *
     * used by combination input gui
     */
    public function getPostValueForComparison()
    {
        $value = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($value != "") {
            return (int) $value;
        }
    }
}
