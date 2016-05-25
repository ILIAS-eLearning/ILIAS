<?php

require_once('./Services/Form/classes/class.ilTextInputGUI.php');

/**
 * Class ilDclTextInputGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclTextInputGUI extends ilTextInputGUI
{

    function setValueByArray($a_values)
    {
        parent::setValueByArray($a_values);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }

    }

}