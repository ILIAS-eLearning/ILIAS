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

declare(strict_types=1);

class ilDclReferenceFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public const REFERENCE_SEPARATOR = " -> ";

    public function getInputField(ilPropertyFormGUI $form, ?int $record_id = null): ilSelectInputGUI|ilMultiSelectInputGUI
    {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $input = new ilMultiSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
            $input->setWidth(100);
            $input->setWidthUnit('%');
        } else {
            $input = new ilSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        }

        $this->setupInputField($input, $this->getField());

        $options = $this->getSortedRecords();
        if (!$this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $options = ['' => $this->lng->txt('dcl_please_select')] + $options;
        }

        $input->setOptions($options);
        if ($input instanceof ilMultiSelectInputGUI) {
            $input->setHeight(32 * min(5, max(1, count($options))));
        }

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

        $fieldref = (int) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
        $reffield = ilDclCache::getFieldCache($fieldref);
        if (ilObjDataCollectionAccess::hasPermissionToAddRecord($ref_id, $reffield->getTableId())) {
            $input->addCustomAttribute('data-ref="1"');
            $input->addCustomAttribute('data-ref-table-id="' . $reffield->getTableId() . '"');
            $input->addCustomAttribute('data-ref-field-id="' . $reffield->getId() . '"');
        }

        return $input;
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): array|string|null
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->getField()->getId()
        );
        $options = ['' => $this->lng->txt('dcl_all_entries')]
            + $this->getSortedRecords()
            + ['none' => $this->lng->txt('dcl_no_entry')];
        $input->setOptions($options);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    protected function getSortedRecords(): array
    {
        $options = [];
        $fieldref = (int) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
        $reffield = ilDclCache::getFieldCache($fieldref);
        $reftable = ilDclCache::getTableCache($reffield->getTableId());
        foreach ($reftable->getRecords() as $record) {
            $record_field = $record->getRecordField($fieldref);
            switch ($reffield->getDatatypeId()) {
                case ilDclDatatype::INPUTFORMAT_FILEUPLOAD:
                    if ($record_field->getValue()) {
                        $file_obj = new ilObjFile($record_field->getValue(), false);
                        $options[$record->getId()] = $file_obj->getFileName();
                    }
                    break;
                case ilDclDatatype::INPUTFORMAT_MOB:
                    $media_obj = new ilObjMediaObject($record_field->getValue());
                    $options[$record->getId()] = $media_obj->getTitle();
                    break;
                case ilDclDatatype::INPUTFORMAT_DATE:
                    $options[$record->getId()] = strtotime($record->getRecordField($fieldref)->getPlainText());
                    $options2[$record->getId()] = $record->getRecordField($fieldref)->getPlainText();
                    break;
                case ilDclDatatype::INPUTFORMAT_TEXT:
                    $value = $record_field->getValue();
                    if ($record->getRecordField((int) $fieldref)->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
                        if (!is_array($value)) {
                            $value = ['title' => '', 'link' => $value];
                        }
                        $value = $value['title'] ?: $value['link'];
                    }
                    $options[$record->getId()] = $value;
                    break;
                case ilDclDatatype::INPUTFORMAT_ILIAS_REF:
                    $value = $record_field->getValue();
                    $options[$record->getId()] = ilObject::_lookupTitle(ilObject::_lookupObjectId($value)) . ' [' . $value . ']';
                    break;
                default:
                    $options[$record->getId()] = $record_field->getExportValue();
                    break;
            }
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        if ($reffield->getDatatypeId() === ilDclDatatype::INPUTFORMAT_DATE) {
            foreach ($options as $key => $opt) {
                if ($key != "" && isset($options2) && is_array($options2)) {
                    $options[$key] = $options2[$key];
                }
            }
        }

        return $options;
    }

    /**
     * @param int $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter): bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());

        $pass = false;
        if ($filter && $this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE) && is_array($value) && in_array(
            $filter,
            $value
        )) {
            $pass = true;
        }
        if (!$filter || $filter == $value) {
            $pass = true;
        }

        return $pass;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $options = [];
        // Get Tables
        $tables = $dcl->getTables();
        foreach ($tables as $table) {
            foreach ($table->getRecordFields() as $field) {
                //referencing references may lead to endless loops.
                if ($field->getDatatypeId() != ilDclDatatype::INPUTFORMAT_REFERENCE) {
                    $options[$field->getId()] = $table->getTitle() . self::REFERENCE_SEPARATOR . $field->getTitle();
                }
            }
        }
        $prop_table_selection = new ilSelectInputGUI(
            $this->lng->txt('dcl_reference_title'),
            'prop_' . ilDclBaseFieldModel::PROP_REFERENCE
        );
        $prop_table_selection->setOptions($options);
        $prop_table_selection->setInfo($this->lng->txt('dcl_reference_title_desc'));

        $opt->addSubItem($prop_table_selection);

        $prop_ref_link = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_reference_link'),
            'prop_' . ilDclBaseFieldModel::PROP_REFERENCE_LINK
        );
        $prop_ref_link->setInfo($this->lng->txt('dcl_reference_link_info'));
        $opt->addSubItem($prop_ref_link);

        $prop_multi_select = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_multiple_selection'),
            'prop_' . ilDclBaseFieldModel::PROP_N_REFERENCE
        );
        $opt->addSubItem($prop_multi_select);

        return $opt;
    }
}
