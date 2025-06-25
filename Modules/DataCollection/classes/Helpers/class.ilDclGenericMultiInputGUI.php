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

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilDclGenericMultiInputGUI extends ilFormPropertyGUI
{
    protected Factory $ui_factory;
    protected Renderer $renderer;

    protected ilFormPropertyGUI $input;
    protected ?array $line_values = [];

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        parent::__construct($a_title, $a_postvar);
    }

    public function setInput(ilFormPropertyGUI $input): void
    {
        $this->input = $input;
    }

    public function setValue(string $value): void
    {
        if ($this->input instanceof ilDateTimeInputGUI) {
            $this->input->setDate(new ilDateTime(strtotime($value), IL_CAL_UNIX));
        } else {
            $this->input->setValue($value);
        }
    }

    public function setValueByArray(array $a_values): void
    {
        $this->line_values = $a_values[$this->getPostVar()] ?? [];
    }

    public function checkInput(): bool
    {
        return true;
    }

    public function getInput(): array
    {
        return $this->strArray($this->getPostVar());
    }

    public function render(int $iterator_id): string
    {
        $tpl = new ilTemplate("tpl.prop_generic_multi_line.html", true, true, 'Modules/DataCollection');

        $input = clone $this->input;
        $input->setPostVar($this->getPostVar() . '[' . $iterator_id . ']');

        $tpl->setCurrentBlock('input');
        $tpl->setVariable('CONTENT', $input->render());
        $tpl->parseCurrentBlock();

        $tpl->setVariable('IMAGE_PLUS', $this->renderer->render($this->ui_factory->symbol()->glyph()->add()));
        $tpl->setVariable('IMAGE_MINUS', $this->renderer->render($this->ui_factory->symbol()->glyph()->remove()));
        $tpl->setVariable('IMAGE_UP', $this->renderer->render($this->ui_factory->symbol()->glyph()->up()));
        $tpl->setVariable('IMAGE_DOWN', $this->renderer->render($this->ui_factory->symbol()->glyph()->down()));

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $output = $this->render(0);

        if (is_array($this->line_values) && count($this->line_values) > 0) {
            $counter = 0;
            foreach ($this->line_values as $i => $data) {
                $object = $this;
                $object->setValue($data);
                $output .= $object->render($i);
                $counter++;
            }
        } else {
            $output .= $this->render(1);
        }

        $output = '<div id="' . $this->getFieldId() . '" class="multi_line_input">' . $output . '</div>';
        $this->global_tpl->addJavaScript('Modules/DataCollection/js/generic_multi_line_input.js');
        $id = $this->getFieldId();
        $this->global_tpl->addOnLoadCode("il.DataCollection.genericMultiLineInit('$id');");

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $output);
        $a_tpl->parseCurrentBlock();
    }
}
