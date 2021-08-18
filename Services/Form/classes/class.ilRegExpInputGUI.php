<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a regular expression input property in a property form.
 *
 * @author Roland Küstermann <roland.kuestermann@kit.edu>
 */
class ilRegExpInputGUI extends ilTextInputGUI
{
    private $pattern;
    
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
        $this->setType("feedurl");
    }

    /**
    * Set Message, if input does not match.
    *
    * @param	string	$a_nomatchmessage	Message, if input does not match
    */
    public function setNoMatchMessage($a_nomatchmessage)
    {
        $this->nomatchmessage = $a_nomatchmessage;
    }

    /**
    * Get Message, if input does not match.
    *
    * @return	string	Message, if input does not match
    */
    public function getNoMatchMessage()
    {
        return $this->nomatchmessage;
    }

    /**
     * set pattern
     *
     * @param string regular expression pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }
    
    /**
     * return pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        // this line is necessary, otherwise it is a security issue (Alex)
        $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        
        $value = $_POST[$this->getPostVar()];
        
        if (!$this->getRequired() && strcasecmp($value, "") == 0) {
            return true;
        }
        
        if ($this->getRequired() && trim($value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        $result = preg_match($this->pattern, $value);
        if (!$result) {
            if ($this->getNoMatchMessage() == "") {
                $this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
            } else {
                $this->setAlert($this->getNoMatchMessage());
            }
        }
        return $result;
    }
}
