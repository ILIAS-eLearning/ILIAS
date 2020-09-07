<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilKVPWizardInputGUI extends ilTextInputGUI
{
    protected $values = array();
    protected $allowMove = false;
    protected $key_size = 20;
    protected $value_size = 20;
    protected $key_maxlength = 255;
    protected $value_maxlength = 255;
    protected $key_name = "";
    protected $value_name = "";
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['key'])) {
                foreach ($a_value['key'] as $idx => $key) {
                    array_push($this->values, array($key, $a_value['value'][$idx]));
                }
            }
        }
    }

    /**
    * Set key size.
    *
    * @param	integer	$a_size	Key size
    */
    public function setKeySize($a_size)
    {
        $this->key_size = $a_size;
    }

    /**
    * Get key size.
    *
    * @return	integer	Key size
    */
    public function getKeySize()
    {
        return $this->key_size;
    }
    
    /**
    * Set value size.
    *
    * @param	integer	$a_size	value size
    */
    public function setValueSize($a_size)
    {
        $this->value_size = $a_size;
    }

    /**
    * Get value size.
    *
    * @return	integer	value size
    */
    public function getValueSize()
    {
        return $this->value_size;
    }
    
    /**
    * Set key maxlength.
    *
    * @param	integer	$a_size	Key maxlength
    */
    public function setKeyMaxlength($a_maxlength)
    {
        $this->key_maxlength = $a_maxlength;
    }

    /**
    * Get key maxlength.
    *
    * @return	integer	Key maxlength
    */
    public function getKeyMaxlength()
    {
        return $this->key_maxlength;
    }
    
    /**
    * Set value maxlength.
    *
    * @param	integer	$a_size	value maxlength
    */
    public function setValueMaxlength($a_maxlength)
    {
        $this->value_maxlength = $a_maxlength;
    }

    /**
    * Get value maxlength.
    *
    * @return	integer	value maxlength
    */
    public function getValueMaxlength()
    {
        return $this->value_maxlength;
    }
    
    /**
    * Set value name.
    *
    * @param	string	$a_name	value name
    */
    public function setValueName($a_name)
    {
        $this->value_name = $a_name;
    }

    /**
    * Get value name.
    *
    * @return	string	value name
    */
    public function getValueName()
    {
        return $this->value_name;
    }
    
    /**
    * Set key name.
    *
    * @param	string	$a_name	value name
    */
    public function setKeyName($a_name)
    {
        $this->key_name = $a_name;
    }

    /**
    * Get key name.
    *
    * @return	string	value name
    */
    public function getKeyName()
    {
        return $this->key_name;
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
        global $DIC;
        $lng = $DIC['lng'];
        
        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['key']) && is_array($foundvalues['value'])) {
                foreach ($foundvalues['key'] as $val) {
                    if ($this->getRequired() && (strlen($val)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
                foreach ($foundvalues['value'] as $val) {
                    if ($this->getRequired() && (strlen($val)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            } else {
                if ($this->getRequired()) {
                    $this->setAlert($lng->txt("msg_input_is_required"));
                    return false;
                }
            }
        } else {
            if ($this->getRequired()) {
                $this->setAlert($lng->txt("msg_input_is_required"));
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
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate("tpl.prop_kvpwizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (is_array($value)) {
                $tpl->setCurrentBlock("prop_key_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value[0]));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("prop_value_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value[1]));
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("row");
            $class = ($i % 2 == 0) ? "even" : "odd";
            if ($i == 0) {
                $class .= " first";
            }
            if ($i == count($this->values) - 1) {
                $class .= " last";
            }
            $tpl->setVariable("ROW_CLASS", $class);
            $tpl->setVariable("ROW_NUMBER", $i);
            
            $tpl->setVariable("KEY_SIZE", $this->getKeySize());
            $tpl->setVariable("KEY_ID", $this->getPostVar() . "[key][$i]");
            $tpl->setVariable("KEY_MAXLENGTH", $this->getKeyMaxlength());

            $tpl->setVariable("VALUE_SIZE", $this->getValueSize());
            $tpl->setVariable("VALUE_ID", $this->getPostVar() . "[value][$i]");
            $tpl->setVariable("VALUE_MAXLENGTH", $this->getValueMaxlength());

            $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));

            $tpl->setVariable("POST_VAR", $this->getPostVar());

            $tpl->parseCurrentBlock();

            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("KEY_TEXT", $this->getKeyName());
        $tpl->setVariable("VALUE_TEXT", $this->getValueName());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/kvpwizard.js");
    }
}
