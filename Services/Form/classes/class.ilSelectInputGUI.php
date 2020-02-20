<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';
include_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';

/**
* This class represents a selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSelectInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
{
    protected $cust_attr = array();
    protected $options = array();
    protected $value;
    
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
        $this->setType("select");
    }

    /**
    * Set Options.
    *
    * @param	array	$a_options	Options. Array ("value" => "option_text")
    */
    public function setOptions($a_options)
    {
        $this->options = $a_options;
    }

    /**
    * Get Options.
    *
    * @return	array	Options. Array ("value" => "option_text")
    */
    public function getOptions()
    {
        return $this->options ? $this->options : array();
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
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
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }
    
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;

        $valid = true;
        if (!$this->getMulti()) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
                $valid = false;
            } elseif (!array_key_exists($_POST[$this->getPostVar()], (array) $this->getOptions())) {
                $this->setAlert($lng->txt('msg_invalid_post_input'));
                return false;
            }
        } else {
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
                if (!array_key_exists($value, (array) $this->getOptions())) {
                    $this->setAlert($lng->txt('msg_invalid_post_input'));
                    return false;
                }
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

            if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }
    
    public function addCustomAttribute($a_attr)
    {
        $this->cust_attr[] = $a_attr;
    }
    
    public function getCustomAttributes()
    {
        return (array) $this->cust_attr;
    }

    /**
    * Render item
    */
    public function render($a_mode = "")
    {
        $tpl = new ilTemplate("tpl.prop_select.html", true, true, "Services/Form");
        
        foreach ($this->getCustomAttributes() as $attr) {
            $tpl->setCurrentBlock('cust_attr');
            $tpl->setVariable('CUSTOM_ATTR', $attr);
            $tpl->parseCurrentBlock();
        }
        
        // determin value to select. Due to accessibility reasons we
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
            $tpl->setVariable("VAL_SELECT_OPTION", ilUtil::prepareFormOutput($option_value));
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
                $hidden = $this->getHiddenTag($postvar, $this->getValue());
            }
            if ($hidden) {
                $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
                $tpl->setVariable("HIDDEN_INPUT", $hidden);
            }
        } else {
            $tpl->setVariable("POST_VAR", $postvar);
        }
        
        // multi icons
        if ($this->getMulti() && !$a_mode && !$this->getDisabled()) {
            $tpl->touchBlock("inline_in_bl");
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        $tpl->setVariable("ARIA_LABEL", ilUtil::prepareFormOutput($this->getTitle()));

        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }

    /**
    * Get HTML for toolbar
    */
    public function getToolbarHTML()
    {
        $html = $this->render("toolbar");
        return $html;
    }
    
    /**
     * Set initial sub form visibility, optionally add dynamic value-based condition
     *
     * @see ilObjBookingPoolGUI
     * @param bool $a_value
     * @param string $a_condition
     */
    public function setHideSubForm($a_value, $a_condition = null)
    {
        $this->hide_sub = (bool) $a_value;
        
        if ($a_condition) {
            $this->addCustomAttribute('onchange="if(this.value ' . $a_condition . ')' .
                ' { il.Form.showSubForm(\'subform_' . $this->getFieldId() . '\', \'il_prop_cont_' . $this->getFieldId() . '\'); }' .
                ' else { il.Form.hideSubForm(\'subform_' . $this->getFieldId() . '\'); };"');
        }
    }

    public function hideSubForm()
    {
        return (bool) $this->hide_sub;
    }
}
