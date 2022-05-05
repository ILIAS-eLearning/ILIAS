<?php

/**
 * Class ilDclDateTimeREpresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclDatetimeFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0): ilDateTimeInputGUI
    {
        $input = new ilDateTimeInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setStartYear(date("Y") - 100);
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): ?array
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_DATE_RANGE,
            false, $this->getField()->getId());
        $input->setSubmitFormOnEnter(true);
        $input->setStartYear(date("Y") - 100);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    public function passThroughFilter(ilDclBaseRecordModel $record, array $filter): bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if ((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to'])) {
            return true;
        }

        return false;
    }
}
