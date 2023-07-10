<?php

declare(strict_types=1);

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
 * This class represents a numeric style property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNumericStyleValueInputGUI extends ilFormPropertyGUI
{
    protected ilObjUser $user;
    protected string $value = "";
    protected bool $allowpercentage = true;

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("style_numeric");
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setAllowPercentage(bool $a_allowpercentage): void
    {
        $this->allowpercentage = $a_allowpercentage;
    }

    public function getAllowPercentage(): bool
    {
        return $this->allowpercentage;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        $input = $this->getInput();
        $num_value = $input["num_value"];
        $num_unit = $input["num_unit"];

        if ($this->getRequired() && trim($num_value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        if (!is_numeric($num_value) && $num_value != "") {
            $this->setAlert($lng->txt("sty_msg_input_must_be_numeric"));

            return false;
        }

        if (trim($num_value) != "") {
            $this->setValue($num_value . $num_unit);
        }

        return true;
    }

    public function getInput(): array
    {
        return $this->strArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate("tpl.prop_style_numeric.html", true, true, "Services/Style/Content");

        $tpl->setVariable("POSTVAR", $this->getPostVar());

        $unit_options = ilObjStyleSheet::_getStyleParameterNumericUnits(!$this->getAllowPercentage());

        $value = strtolower(trim($this->getValue()));

        $current_unit = "";
        foreach ($unit_options as $u) {
            if (substr($value, strlen($value) - strlen($u)) == $u) {
                $current_unit = $u;
            }
        }
        $tpl->setVariable(
            "VAL_NUM",
            substr($value, 0, strlen($value) - strlen($current_unit))
        );
        if ($current_unit == "") {
            $current_unit = "px";
        }

        foreach ($unit_options as $option) {
            $tpl->setCurrentBlock("unit_option");
            $tpl->setVariable("VAL_UNIT", $option);
            $tpl->setVariable("TXT_UNIT", $option);
            if ($current_unit == $option) {
                $tpl->setVariable("UNIT_SELECTED", 'selected="selected"');
            }
            $tpl->parseCurrentBlock();
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()]["num_value"] .
            $a_values[$this->getPostVar()]["num_unit"]);
    }
}
