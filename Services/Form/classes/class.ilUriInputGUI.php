<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Legacy Uri input
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUriInputGUI extends ilTextInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $value;
    protected $maxlength = 500;
    protected $size = 40;

    /**
     * Constructor
     * @param string $a_title   Title
     * @param string $a_postvar Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("uri");
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;

        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);

        // check required
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        // check feed url
        $url = $_POST[$this->getPostVar()];

        try {
            new \ILIAS\Data\URI($url);
        } catch (\Throwable $e) {
            $this->setAlert($lng->txt("form_invalid_uri"));
            return false;
        }

        return true;
    }
}
