<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Radio input for character seelctor definition
 */
class ilCharSelectorRadioGroupInputGUI extends ilRadioGroupInputGUI
{

    /**
     * Constructor
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        parent::__construct($a_title, $a_postvar);
        $this->lng = $DIC->language();
    }

    /**
     * Additional check for either block or custom chars
     *
     * @return	boolean		Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;
        if (!parent::checkInput()) {
            return false;
        }

        if ($_POST['char_selector_availability'] == ilCharSelectorConfig::ENABLED
            and trim(implode($_POST['char_selector_blocks'])) == ""
            and trim($_POST['char_selector_custom_items']) == '') {
            $this->setAlert($lng->txt("char_selector_msg_blocks_or_custom_needed"));
            return false;
        } else {
            return true;
        }
    }
}
