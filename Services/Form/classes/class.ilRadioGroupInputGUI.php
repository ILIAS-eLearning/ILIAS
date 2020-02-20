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

include_once("./Services/Form/classes/class.ilRadioOption.php");

/**
* This class represents a property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRadioGroupInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
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
        $this->setType("radio");
    }
    
    /**
    * Add Option.
    *
    * @param	object		$a_option	RadioOption object
    */
    public function addOption($a_option)
    {
        $this->options[] = $a_option;
    }

    /**
    * Get Options.
    *
    * @return	array	Array of RadioOption objects
    */
    public function getOptions()
    {
        return $this->options;
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
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
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        
        $ok = true;
        foreach ($this->getOptions() as $option) {
            foreach ($option->getSubItems() as $item) {
                if ($_POST[$this->getPostVar()] == $option->getValue()) {
                    if (!$item->checkInput()) {
                        $ok = false;
                    }
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
                    $tpl->setCurrentBlock("prop_radio_opt_hide");
                    $tpl->setVariable("HOP_ID", $this->getFieldId() . "_" . $option->getValue());
                    $tpl->parseCurrentBlock();
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
            if (!$this->getDisabled()) {
                $tpl->setVariable("POST_VAR", $this->getPostVar());
            }
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
