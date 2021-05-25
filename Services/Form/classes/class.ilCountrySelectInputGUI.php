<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a selection list property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCountrySelectInputGUI extends ilSelectInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    
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
        $this->setType("cselect");
    }

    /**
     * Get Options.
     *
     * @return	array	Options. Array ("value" => "option_text")
     */
    public function getOptions()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");
        $lng->loadLanguageModule("form");

        foreach (ilCountry::getCountryCodes() as $c) {
            $options[$c] = $lng->txt("meta_c_" . $c);
        }
        asort($options);

        $options = array("" => "- " . $lng->txt("form_please_select") . " -")
            + $options;

        return $options;
    }
}
