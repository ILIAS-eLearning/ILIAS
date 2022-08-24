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
 * This class represents a hidden form property in a property form.
 *
 * @author Roland Küstermann (rkuestermann@mps.de)
 */
class ilHiddenInputGUI extends ilFormPropertyGUI implements ilToolbarItem
{
    protected string $value = "";

    public function __construct(
        string $a_postvar
    ) {
        parent::__construct("", $a_postvar);
        $this->setType("hidden");
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function checkInput(): bool
    {
        return true;		// please overwrite
    }

    public function getInput(): string
    {
        return $this->str($this->getPostVar());
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue((string) ($a_values[$this->getPostVar()] ?? ""));
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock("hidden");
        $a_tpl->setVariable('PROP_INPUT_TYPE', 'hidden');
        $a_tpl->setVariable("POST_VAR", $this->getPostVar());
        $a_tpl->setVariable("ID", $this->getFieldId());
        $a_tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()));
        $a_tpl->parseCurrentBlock();
    }

    public function getToolbarHTML(): string
    {
        return "<input type=\"hidden\"" .
            " name=\"" . $this->getPostVar() . "\"" .
            " value=\"" . ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()) . "\"" .
            " id=\"" . $this->getFieldId() . "\" />";
    }
}
