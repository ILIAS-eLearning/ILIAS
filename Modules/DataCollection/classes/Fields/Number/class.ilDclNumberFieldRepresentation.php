<?php

/**
 * Class ilDclDateTimeREpresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclNumberFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ilNumberInputGUI
    {
        $input = new ilNumberInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        // 9 is the maximum number of digits for an integer
        $input->setMaxLength(9);
        $input->setInfo($this->lng->txt('dcl_max_digits') . ": 9");
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    /**
     * @param ilTable2GUI $table
     * @return array|string|null
     * @throws Exception
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_NUMBER_RANGE, false, $this->getField()->getId());
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    /**
     * @param array $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter) : bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if ((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to'])) {
            return true;
        }

        return false;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create') : ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        return $opt;
    }
}
