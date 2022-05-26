<?php

/**
 * Class ilDclCheckboxInputGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclCheckboxInputGUI extends ilCheckboxInputGUI
{
    public function checkInput() : bool
    {
        $has_postvar = $this->http->wrapper()->post()->has($this->getPostVar());
        if ($this->getRequired() && !$has_postvar) {
            global $DIC;
            $lng = $DIC['lng'];
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        return parent::checkInput();
    }
}
