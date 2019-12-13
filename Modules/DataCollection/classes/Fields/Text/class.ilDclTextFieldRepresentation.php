<?php

/**
 * Class ilDclTextFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextFieldRepresentation extends ilDclBaseFieldRepresentation
{

    /**
     * @inheritdoc
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_TEXT, false, $this->getField()->getId());
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }


    /**
     * @inheritdoc
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter)
    {
        $pass = parent::passThroughFilter($record, $filter);

        $value = $record->getRecordFieldValue($this->getField()->getId());
        if (!$filter || strpos(strtolower($value), strtolower($filter)) !== false) {
            $pass = true;
        }

        return $pass;
    }


    /**
     * @inheritdoc
     */
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilDclTextInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_TEXTAREA)) {
            $input = new ilTextAreaInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        }

        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_LENGTH)) {
            $input->setInfo($this->lng->txt("dcl_max_text_length") . ": " . $this->getField()->getProperty(ilDclBaseFieldModel::PROP_LENGTH));
            if (!$this->getField()->getProperty(ilDclBaseFieldModel::PROP_TEXTAREA)) {
                $input->setMaxLength($this->getField()->getProperty(ilDclBaseFieldModel::PROP_LENGTH));
            }
        }

        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $input->setInfo($this->lng->txt('dcl_text_email_detail_desc'));
            $title_field = new ilDclTextInputGUI($this->lng->txt('dcl_text_email_title'), 'field_' . $this->getField()->getId() . '_title');
            $title_field->setInfo($this->lng->txt('dcl_text_email_title_info'));
            $input->addSubItem($title_field);
        }

        $this->setupInputField($input, $this->getField());

        return $input;
    }


    /**
     * @inheritDoc
     */
    public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_length = new ilNumberInputGUI($this->lng->txt('dcl_length'), $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_LENGTH));
        $prop_length->setSize(5);
        $prop_length->setMaxValue(4000);
        $prop_length->setInfo($this->lng->txt('dcl_length_info'));

        $opt->addSubItem($prop_length);

        $prop_regex = new ilDclTextInputGUI($this->lng->txt('dcl_regex'), $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_REGEX));
        $prop_regex->setInfo($this->lng->txt('dcl_regex_info'));

        $opt->addSubItem($prop_regex);

        $prop_url = new ilDclCheckboxInputGUI($this->lng->txt('dcl_url'), $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_URL));
        $opt->addSubItem($prop_url);

        $prop_textarea = new ilDclCheckboxInputGUI($this->lng->txt('dcl_text_area'), $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_TEXTAREA));
        $opt->addSubItem($prop_textarea);

        $prop_page_details = new ilDclCheckboxInputGUI($this->lng->txt('dcl_link_detail_page'), $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT));
        $opt->addSubItem($prop_page_details);

        return $opt;
    }
}
