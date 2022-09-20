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
 * This class represents a custom property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated Deprecated since 4.4, inherit directly from InputGUI instead
 */
class ilCustomInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected string $html = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;
        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("custom");
    }

    public function setHtml(string $a_html): void
    {
        $this->html = $a_html;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setValueByArray(array $a_values): void
    {
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        if ($this->getPostVar()) {
            if ($this->getRequired() && $this->getInput() == "") {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return $this->checkSubItemsInput();
    }

    public function getInput(): string
    {
        return trim($this->str($this->getPostVar()));
    }
}
