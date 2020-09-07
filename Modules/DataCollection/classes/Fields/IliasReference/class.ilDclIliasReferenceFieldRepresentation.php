<?php

/**
 * Class ilDclIliasReferenceFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclIliasReferenceFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilRepositorySelector2InputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId(), false, get_class($form));
        $this->setupInputField($input, $this->getField());

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
        $obj_id = ilObject::_lookupObjId($value);
        if (!$filter || strpos(strtolower(ilObject::_lookupTitle($obj_id)), strtolower($filter)) !== false) {
            return true;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_ref_link = new ilDclCheckboxInputGUI($this->lng->txt('dcl_learning_progress'), 'prop_' . ilDclBaseFieldModel::PROP_LEARNING_PROGRESS);
        $opt->addSubItem($prop_ref_link);

        $prop_multi_select = new ilDclCheckboxInputGUI($this->lng->txt('dcl_ilias_reference_link'), 'prop_' . ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK);
        $opt->addSubItem($prop_multi_select);

        $prop_multi_select = new ilDclCheckboxInputGUI($this->lng->txt('dcl_display_action_menu'), 'prop_' . ilDclBaseFieldModel::PROP_DISPLAY_COPY_LINK_ACTION_MENU);
        $opt->addSubItem($prop_multi_select);

        return $opt;
    }
}
