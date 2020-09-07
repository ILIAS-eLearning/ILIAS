<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("./Services/Form/classes/class.ilCheckboxOption.php");

/**
* This class represents a property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCheckboxGroupInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $options = array();
    protected $value;
    protected $use_values_as_keys = false;


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
        $this->setType("checkboxgroup");
    }

    /**
     * Set use values as keys
     *
     * @param bool $a_val use values as keys
     */
    public function setUseValuesAsKeys($a_val)
    {
        $this->use_values_as_keys = $a_val;
    }
    
    /**
     * Get use values as keys
     *
     * @return bool use values as keys
     */
    public function getUseValuesAsKeys()
    {
        return $this->use_values_as_keys;
    }
    
    /**
    * Add Option.
    *
    * @param	object		$a_option	CheckboxOption object
    */
    public function addOption($a_option)
    {
        $this->options[] = $a_option;
    }

    /**
    * Set Options.
    *
    * @param	array	$a_options	Options. Array ("value" => "option_text")
    */
    public function setOptions($a_options)
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

    /**
    * Get Options.
    *
    * @return	array	Array of CheckboxOption objects
    */
    public function getOptions()
    {
        return $this->options;
    }

    /**
    * Set Value.
    *
    * @param	array	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	array	Value
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
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                $item->setValueByArray($a_values);
            }
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

        if ($this->getRequired() && (!is_array($_POST[$this->getPostVar()]) || count($_POST[$this->getPostVar()]) === 0)) {
            $this->setAlert($lng->txt('msg_input_is_required'));
            return false;
        }

        $ok = true;
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                $item_ok = $item->checkInput();
                if (!$item_ok && in_array($option->getValue(), $_POST[$this->getPostVar()])) {
                    $ok = false;
                }
            }
        }
        return $ok;
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
    * Get item by post var
    *
    * @return	mixed	false or item object
    */
    public function getItemByPostVar($a_post_var)
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

        return false;
    }
    
    public function getTableFilterHTML()
    {
        return $this->render();
    }
    
    public function getToolbarHTML()
    {
        return $this->render('toolbar');
    }
    
    protected function render($a_mode = '')
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
                    if ($cval[$option->getValue()] == 1) {
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

    /**
     * returns a flat array of possibly existing subitems recursively
     *
     * @return array
     */
    public function getSubInputItemsRecursive()
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
