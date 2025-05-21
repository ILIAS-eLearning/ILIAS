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
 *********************************************************************/

class ilDclCopyFieldRepresentation extends ilDclBaseFieldRepresentation
{
    private const VALID_TYPES = [
        ilDclDatatype::INPUTFORMAT_TEXT,
        ilDclDatatype::INPUTFORMAT_NUMBER,
        ilDclDatatype::INPUTFORMAT_BOOLEAN,
        ilDclDatatype::INPUTFORMAT_DATE,
    ];

    /**
     * @return ilSelectInputGUI|ilMultiSelectInputGUI
     */
    public function getInputField(ilPropertyFormGUI $form, ?int $record_id = null): ilFormPropertyGUI
    {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $input = new ilMultiSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        } else {
            $input = new ilSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        }

        $this->setupInputField($input, $this->getField());

        $options = [];
        if (!$this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $options[''] = $this->lng->txt('dcl_please_select');
        }

        $value = null;
        $copy_id = $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
        $copy_field = ilDclCache::getFieldCache($copy_id);
        if ($copy_field->getTableId() !== 0) {
            $copy_table = ilDclCache::getTableCache($copy_field->getTableId());
            foreach ($copy_table->getRecords() as $record) {
                $option = $record->getRecordField($copy_field->getId())->getPlainText();
                if (!in_array($option, $options)) {
                    $options[$option] = $option;
                }
            }
        } else {
            $input->setAlert($this->lng->txt('dcl_origin_not_found'));
        }

        if ($record_id !== null) {
            $value = ilDclCache::getRecordCache($record_id)->getRecordFieldValue($this->getField()->getId());
            if ($value !== '' && !array_key_exists($value, $options)) {
                $options[$value] = $value . ' ' . $this->lng->txt('dcl_deprecated_copy');
            }
        }

        $input->setOptions($options);

        return $input;
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->getField()->getId()
        );
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    public function passThroughFilter(ilDclBaseRecordModel $record, $filter): bool
    {
        $pass = parent::passThroughFilter($record, $filter);

        $value = $record->getRecordFieldValue($this->getField()->getId());
        if (!$filter || strpos(strtolower($value), strtolower($filter)) !== false) {
            $pass = true;
        }

        return $pass;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $datetype_title = $this->getField()->getPresentationTitle();
        $opt = new ilRadioOption($this->getField()->getPresentationTitle(), $this->getField()->getDatatypeId());
        $opt->setInfo($this->getField()->getPresentationDescription());

        $options = [];
        $tables = $dcl->getTables();
        foreach ($tables as $table) {
            foreach ($table->getRecordFields() as $field) {
                if (in_array($field->getDatatypeId(), self::VALID_TYPES)) {
                    $options[$field->getId()] = $table->getTitle() . ' -> ' . $field->getTitle();
                }
            }
        }

        $prop_table_selection = new ilSelectInputGUI(
            $this->lng->txt('dcl_copy_title'),
            'prop_' . ilDclBaseFieldModel::PROP_REFERENCE
        );
        $prop_table_selection->setOptions($options);
        $opt->addSubItem($prop_table_selection);

        $prop_multi_select = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_multiple_selection'),
            'prop_' . ilDclBaseFieldModel::PROP_N_REFERENCE
        );
        $opt->addSubItem($prop_multi_select);

        return $opt;
    }
}
