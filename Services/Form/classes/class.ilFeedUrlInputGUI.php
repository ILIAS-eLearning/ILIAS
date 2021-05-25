<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a feed url property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilFeedUrlInputGUI extends ilTextInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $value;
    protected $maxlength = 200;
    protected $size = 40;
    
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
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("feed");
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            
        // remove safari pseudo protocol
        if (substr($_POST[$this->getPostVar()], 0, 5) == "feed:") {
            $_POST[$this->getPostVar()] = "http:" .
                substr($_POST[$this->getPostVar()], 5);
        }
        
        // add missing http://
        if (!is_int(strpos($_POST[$this->getPostVar()], "://"))) {
            $_POST[$this->getPostVar()] = "http://" . $_POST[$this->getPostVar()];
        }
            
        // check required
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        
        // check feed url
        $url = $_POST[$this->getPostVar()];
        $check = ilExternalFeed::_checkUrl($url);

        // try to determine a feed url, if we failed here
        if ($check !== true) {
            $url2 = ilExternalFeed::_determineFeedUrl($url);
            $check2 = ilExternalFeed::_checkUrl($url2);
            
            if ($check2 === true) {
                $_POST[$this->getPostVar()] = $url2;
                $check = true;
            }
        }

        // if check failed, output error message
        if ($check !== true) {
            $check = str_replace("MagpieRSS:", "", $check);
            $this->setAlert($lng->txt("feed_no_valid_url") . "<br />" . $check);
            return false;
        }
        
        return true;
    }
}
