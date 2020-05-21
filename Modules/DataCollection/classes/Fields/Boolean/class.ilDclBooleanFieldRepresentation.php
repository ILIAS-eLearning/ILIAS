<?php

/**
 * Class ilDclFileuploadFieldRepresentaion
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBooleanFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilDclCheckboxInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $this->setupInputField($input, $this->getField());

        return $input;
    }


    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT, false, $this->getField()->getId());
        $input->setOptions(
            array(
                "" => $this->lng->txt("dcl_any"),
                "not_checked" => $this->lng->txt("dcl_not_checked"),
                "checked" => $this->lng->txt("dcl_checked"),
            )
        );

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }


    public function passThroughFilter(ilDclBaseRecordModel $record, $filter)
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if ((($filter == "checked" && $value == 1) || ($filter == "not_checked" && $value == 0)) || $filter == '' || !$filter) {
            return true;
        }

        return false;
    }
}
