<?php

/**
 * Class ilDclDateTimeREpresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilTextInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setValue($this->lng->txt("dcl_editable_in_table_gui"));
        $input->setDisabled(true);
        $this->setupInputField($input, $this->getField());

        return $input;
    }


    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT, false, $this->getField()->getId());
        $options = array("" => $this->lng->txt("dcl_any"), 1 => ">1", 2 => ">2", 3 => ">3", 4 => ">4", 5 => "5");
        $input->setOptions($options);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }


    public function passThroughFilter(ilDclBaseRecordModel $record, $filter)
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if (!$filter || $filter <= $value['avg']) {
            return true;
        }

        return false;
    }
}
