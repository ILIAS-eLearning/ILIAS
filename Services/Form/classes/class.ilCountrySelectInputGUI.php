<?php declare(strict_types=1);

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
 * This class represents a selection list property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCountrySelectInputGUI extends ilSelectInputGUI
{
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("cselect");
    }

    public function getOptions() : array
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
