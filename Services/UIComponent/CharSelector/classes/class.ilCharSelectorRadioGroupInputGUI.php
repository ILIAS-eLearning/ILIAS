<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Radio input for character seelctor definition
 */
class ilCharSelectorRadioGroupInputGUI extends ilRadioGroupInputGUI
{
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        parent::__construct($a_title, $a_postvar);
        $this->lng = $DIC->language();
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;
        if (!parent::checkInput()) {
            return false;
        }

        if ($this->int('char_selector_availability') === ilCharSelectorConfig::ENABLED
            && trim(implode("", $this->strArray('char_selector_blocks'))) === ""
            && trim($this->str('char_selector_custom_items')) === '') {
            $this->setAlert($lng->txt("char_selector_msg_blocks_or_custom_needed"));
            return false;
        } else {
            return true;
        }
    }
}
