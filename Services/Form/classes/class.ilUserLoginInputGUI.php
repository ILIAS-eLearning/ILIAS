<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a user login property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilUserLoginInputGUI extends ilFormPropertyGUI
{
    protected $value;
    protected $size = 40;
    protected $max_length = 80;
    protected $checkunused = 0;

    /**
     * @var bool Flag whether the html autocomplete attribute should be set to "off" or not
     */
    protected $autocomplete_disabled = false;
    
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
    }

    /**
    * Set Check whether login is unused.
    *
    * @param	int	$a_checkunused	user id of current user
    */
    public function setCurrentUserId($a_user_id)
    {
        $this->checkunused = $a_user_id;
    }

    /**
    * Get Check whether login is unused.
    *
    * @return	boolean	Check whether login is unused
    */
    public function getCurrentUserId()
    {
        return $this->checkunused;
    }

    /**
    * Set autocomplete
    *
    * @param	bool	$a_value	Value
    */
    public function setDisableHtmlAutoComplete($a_value)
    {
        $this->autocomplete_disabled = (bool) $a_value;
    }

    /**
    * Get autocomplete
    *
    * @return	bool	Value
    */
    public function isHtmlAutoCompleteDisabled()
    {
        return $this->autocomplete_disabled;
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
        if (!ilUtil::isLogin($_POST[$this->getPostVar()])) {
            $this->setAlert($lng->txt("login_invalid"));

            return false;
        }
        
        if (ilObjUser::_loginExists($_POST[$this->getPostVar()], $this->getCurrentUserId())) {
            $this->setAlert($lng->txt("login_exists"));

            return false;
        }

        
        return true;
    }

    /**
    * Insert property html
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;
        
        $a_tpl->setCurrentBlock("prop_login");
        $a_tpl->setVariable("POST_VAR", $this->getPostVar());
        $a_tpl->setVariable("ID", $this->getFieldId());
        $a_tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        $a_tpl->setVariable("SIZE", $this->size);
        $a_tpl->setVariable("MAXLENGTH", $this->max_length);
        if ($this->getDisabled()) {
            $a_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }
        if ($this->isHtmlAutoCompleteDisabled()) {
            $a_tpl->setVariable("AUTOCOMPLETE", "autocomplete=\"off\"");
        }
        if ($this->getRequired()) {
            $a_tpl->setVariable("REQUIRED", "required=\"required\"");
        }
        $a_tpl->parseCurrentBlock();
    }
}
