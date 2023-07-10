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
 * This class represents an advanced selection list property in a property form.
 * It can hold graphical selection items, uses javascript and falls back
 * to a normal selection list, when javascript is disabled.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAdvSelectInputGUI extends ilFormPropertyGUI
{
    protected array $options = array();
    protected string $value = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("advselect");
    }

    public function addOption(
        string $a_value,
        string $a_text,
        string $a_html = ""
    ): void {
        $this->options[$a_value] = array("value" => $a_value,
            "txt" => $a_text, "html" => $a_html);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return true;
    }

    public function getInput(): string
    {
        return $this->str($this->getPostVar());
    }

    protected function getAdvSelection(): ilAdvancedSelectionListGUI
    {
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setFormSelectMode(
            $this->getPostVar(),
            "",
            false,
            "",
            "",
            "",
            ""
        );
        $selection->setId($this->getPostVar());
        $selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $selection->setSelectedValue($this->getValue());
        $selection->setUseImages(false);
        $selection->setOnClickMode(ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT);

        foreach ($this->getOptions() as $option) {
            $selection->addItem(
                $option["txt"],
                $option["value"],
                "",
                "",
                $option["value"],
                "",
                $option["html"]
            );
            if ($this->getValue() == $option["value"]) {
                $selection->setListTitle($option["txt"]);
            }
        }
        return $selection;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $selection = $this->getAdvSelection();
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $selection->getHTML());
        $a_tpl->parseCurrentBlock();
    }

    public function getOnloadCode(): array
    {
        return $this->getAdvSelection()->getOnloadCode();
    }
}
