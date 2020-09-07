<?php

/**
 * Class ilDclFileuploadFieldRepresentaion
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclMobFieldRepresentation extends ilDclFileuploadFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilFileInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setSuffixes(ilDclMobFieldModel::$mob_suffixes);
        $input->setAllowDeletion(true);

        $this->requiredWorkaroundForInputField($input, $record_id);

        return $input;
    }


    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_TEXT, false, $this->getField()->getId());
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }


    public function passThroughFilter(ilDclBaseRecordModel $record, $filter)
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());

        $m_obj = new ilObjMediaObject($value, false);
        $file_name = $m_obj->getTitle();
        if (!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false) {
            return true;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = new ilRadioOption($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle()), $this->getField()->getDatatypeId());
        $opt->setInfo($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle() . '_desc'));

        $opt->setInfo(sprintf($opt->getInfo(), implode(", ", ilDclMobFieldModel::$mob_suffixes)));

        $prop_width = new ilNumberInputGUI($this->lng->txt('dcl_width'), 'prop_' . ilDclBaseFieldModel::PROP_WIDTH);
        $prop_width->setSize(5);
        $prop_width->setMaxValue(4000);

        $opt->addSubItem($prop_width);

        $prop_height = new ilNumberInputGUI($this->lng->txt('dcl_height'), 'prop_' . ilDclBaseFieldModel::PROP_HEIGHT);
        $prop_height->setSize(5);
        $prop_height->setMaxValue(4000);

        $opt->addSubItem($prop_height);

        $prop_page_details = new ilDclCheckboxInputGUI($this->lng->txt('dcl_link_detail_page'), 'prop_' . ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
        $opt->addSubItem($prop_page_details);

        return $opt;
    }
}
