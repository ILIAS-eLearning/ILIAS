<?php

/**
 * Class ilDclFileuploadFieldRepresentaion
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFileuploadFieldRepresentation extends ilDclBaseFieldRepresentation
{

    /**
     * @inheritdoc
     */
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        $input = new ilFileInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setSuffixes($this->getField()->getSupportedExtensions());
        $input->setAllowDeletion(true);

        $this->requiredWorkaroundForInputField($input, $record_id);

        return $input;
    }


    protected function requiredWorkaroundForInputField($input, $record_id)
    {
        if ($record_id) {
            $record = ilDclCache::getRecordCache($record_id);
        }

        $this->setupInputField($input, $this->getField());

        //WORKAROUND
        // If field is from type file: if it's required but already has a value it is no longer required as the old value is taken as default without the form knowing about it.
        if ($record_id && $record->getId()) {
            $field_value = $record->getRecordFieldValue($this->getField()->getId());
            if ($field_value) {
                $input->setRequired(false);
            }
        }
        // If this is an ajax request to return the form, input files are currently not supported
        if ($this->ctrl->isAsynch()) {
            $input->setDisabled(true);
        }
    }


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
        $value = $record->getRecordFieldValue($this->getField()->getId());
        $pass = false;
        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
            $pass = true;
        }

        $file_obj = new ilObjFile($value, false);
        $file_name = $file_obj->getTitle();
        if (!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false) {
            $pass = true;
        }

        return $pass;
    }


    /**
     * @inheritDoc
     */
    public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_filetype = new ilTextInputGUI($this->lng->txt('dcl_supported_filetypes'), 'prop_' . ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES);
        $prop_filetype->setInfo($this->lng->txt('dcl_supported_filetypes_desc'));

        $opt->addSubItem($prop_filetype);

        return $opt;
    }
}
