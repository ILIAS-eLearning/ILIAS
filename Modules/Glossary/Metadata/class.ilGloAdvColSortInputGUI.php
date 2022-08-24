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
 * Input for adv meta data column sorting in glossaries.
 * Please note, that data us already an array, we do not use the MultipleValues
 * interface here.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGloAdvColSortInputGUI extends ilFormPropertyGUI
{
    protected array $value;

    public function __construct(
        string $a_title = "",
        string $a_id = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_id);
        $this->setType("glo_adv_col_sort");
    }

    public function setValue(array $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): array
    {
        return $this->value;
    }


    /**
     * Input should always be valid, since we sort only
     */
    public function checkInput(): bool
    {
        return true;
    }

    public function getInput(): array
    {
        $val = $this->arrayArray($this->getPostVar());
        $val = ilArrayUtil::stripSlashesRecursive($val);
        return $val;
    }

    public function render(): string
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.adv_col_sort_input.html", true, true, "Modules/Glossary");
        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $k => $v) {
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("TEXT", $v["text"]);
                $tpl->setVariable("ID", $this->getFieldId() . "~" . $k);
                $tpl->setVariable("DOWN", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->setVariable("TXT_DOWN", $lng->txt("down"));
                $tpl->setVariable("UP", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("TXT_UP", $lng->txt("up"));
                $tpl->setVariable('NAME', $this->getPostVar() . "[" . $k . "][id]");
                $tpl->setVariable('TNAME', $this->getPostVar() . "[" . $k . "][text]");
                $tpl->setVariable('VAL', ilLegacyFormElementsUtil::prepareFormOutput($v["id"]));
                $tpl->setVariable('TVAL', ilLegacyFormElementsUtil::prepareFormOutput($v["text"]));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $a_values): void
    {
        if ($this->getPostVar() && isset($a_values[$this->getPostVar()])) {
            $this->setValue($a_values[$this->getPostVar()]);
        }
    }

    public function getTableFilterHTML(): string
    {
        $html = $this->render();
        return $html;
    }
}
