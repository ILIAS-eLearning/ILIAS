<?php

/**
 * Class ilDclCheckboxInputGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclCheckboxInputGUI extends ilCheckboxInputGUI
{
    public function checkInput()
    {
        if ($this->getRequired() && !isset($_POST[$this->getPostVar()])) {
            global $DIC;
            $lng = $DIC['lng'];
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        return parent::checkInput();
    }
}
