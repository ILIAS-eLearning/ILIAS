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

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Operators;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Functions;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\Tokenizer;

class ilDclFormulaFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, ?int $record_id = null): ilTextInputGUI
    {
        $input = new ilTextInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setDisabled(true);
        $input->setValue('-');
        $input->setInfo($this->getField()->getDescription() . '<br>' . $this->lng->txt('dcl_formula_detail_desc'));

        return $input;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        $table = ilDclCache::getTableCache($table_id);
        $fields = [];
        foreach ($table->getFieldsForFormula() as $f) {
            $placeholder = ($f->isStandardField()) ? $f->getId() : $f->getTitle();
            $fields[] = '<a class="dclPropExpressionField" data-placeholder="' . $placeholder . '">' . $f->getTitle() . '</a>';
        }
        $subitem = new ilTextAreaInputGUI(
            $this->lng->txt('dcl_prop_expression'),
            'prop_' . ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION
        );
        $operators = implode(', ', array_map(
            static fn(Operators $operator): string => $operator->value,
            Tokenizer::$operators
        ));
        $functions = implode(', ', array_map(
            static fn(Functions $function): string => $function->value,
            Tokenizer::$functions
        ));
        $subitem->setInfo(sprintf(
            $this->lng->txt('dcl_prop_expression_info'),
            $operators,
            $functions,
            implode('<br>', $fields)
        ));
        $opt->addSubItem($subitem);

        return $opt;
    }
}
