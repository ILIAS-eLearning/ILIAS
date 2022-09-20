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
 * Class ilDclDateTimeREpresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFormulaFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0): ilTextInputGUI
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
        $fields = array();
        foreach ($table->getFieldsForFormula() as $f) {
            $placeholder = ($f->isStandardField()) ? $f->getId() : $f->getTitle();
            $fields[] = '<a class="dclPropExpressionField" data-placeholder="' . $placeholder . '">' . $f->getTitle() . '</a>';
        }
        $subitem = new ilTextAreaInputGUI(
            $this->lng->txt('dcl_prop_expression'),
            'prop_' . ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION
        );
        $operators = implode(', ', array_keys(ilDclExpressionParser::getOperators()));
        $functions = implode(', ', ilDclExpressionParser::getFunctions());
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
