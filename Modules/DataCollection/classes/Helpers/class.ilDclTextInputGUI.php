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
 ********************************************************************
 */

/**
 * Class ilDclTextInputGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclTextInputGUI extends ilTextInputGUI
{
    public function setValueByArray(array $a_values) : void
    {
        parent::setValueByArray($a_values);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function checkInput() : bool
    {
        // validate regex
        $has_postvar = $this->http->wrapper()->post()->has($this->getPostVar());
        if ($this->getPostVar() == 'prop_' . ilDclBaseFieldModel::PROP_REGEX && $has_postvar) {
            $regex = $this->http->wrapper()->post()->retrieve($this->getPostVar(),
                $this->refinery->kindlyTo()->string());
            if (substr($regex, 0, 1) != "/") {
                $regex = "/" . $regex;
            }
            if (substr($regex, -1) != "/") {
                $regex .= "/";
            }
            try {
                preg_match($regex, '');
            } catch (Exception $e) {
                global $DIC;
                $lng = $DIC['lng'];
                $this->setAlert($lng->txt('msg_input_does_not_match_regexp'));

                return false;
            }
        }

        return parent::checkInput();
    }
}
