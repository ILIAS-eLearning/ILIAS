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
 * This class represents a formula text property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilFormulaInputGUI extends ilTextInputGUI
{
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;
        parent::__construct($a_title, $a_postvar);

        $this->lng = $DIC->language();
    }

    /**
     * @param string|array $a_value
     */
    public function setValue($a_value) : void
    {
        $this->value = str_replace(',', '.', $a_value);
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return    bool        Input ok, true/false
    */
    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        if ($this->getRequired() && $this->getInput() == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        } else {
            $eval = new EvalMath();
            $eval->suppress_errors = true;
            $result = $eval->e(str_replace(",", ".", $this->getInput()));
            if ($result === false) {
                $this->setAlert($lng->txt("form_msg_formula_is_required"));
                return false;
            }
        }
        
        return $this->checkSubItemsInput();
    }

    public function getInput() : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return ilUtil::stripSlashes(
            (string) ($this->getRequestParam($this->getPostVar(), $t) ?? ""),
            false
        );
    }
}
