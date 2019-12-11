<?php

/**
 * Class ilDclDateTimeREpresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFormulaFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilTextInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setDisabled(true);
        $input->setValue('-');
        $input->setInfo($this->getField()->getDescription() . '<br>' . $this->lng->txt('dcl_formula_detail_desc'));

        return $input;
    }


    /**
     * @inheritDoc
     */
    protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $table = ilDclCache::getTableCache((int) $_GET['table_id']);
        $fields = array();
        foreach ($table->getFieldsForFormula() as $f) {
            $placeholder = ($f->isStandardField()) ? $f->getId() : $f->getTitle();
            $fields[] = '<a class="dclPropExpressionField" data-placeholder="' . $placeholder . '">' . $f->getTitle() . '</a>';
        }
        $subitem = new ilTextAreaInputGUI($this->lng->txt('dcl_prop_expression'), 'prop_' . ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
        $operators = implode(', ', array_keys(ilDclExpressionParser::getOperators()));
        $functions = implode(', ', ilDclExpressionParser::getFunctions());
        $subitem->setInfo(sprintf($this->lng->txt('dcl_prop_expression_info'), $operators, $functions, implode('<br>', $fields)));
        $opt->addSubItem($subitem);

        return $opt;
    }
}
