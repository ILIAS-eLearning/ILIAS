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

/**
* This class represents a text wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextWizardInputGUI extends ilTextInputGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $values = array();
    protected $allowMove = false;
    
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
        $this->tpl = $DIC["tpl"];
        parent::__construct($a_title, $a_postvar);
        $this->validationRegexp = "";
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values)
    {
        $this->values = $a_values;
    }
    
    /**
    * Set Value
    *
    * @param	array	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->values = $a_value;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues()
    {
        return $this->values;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move)
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove()
    {
        return $this->allowMove;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            foreach ($foundvalues as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
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
    
    public function render($a_mode = "")
    {
        $tpl = new ilTemplate("tpl.prop_textwizardinput.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->values as $value) {
            if (strlen($value)) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value));
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
