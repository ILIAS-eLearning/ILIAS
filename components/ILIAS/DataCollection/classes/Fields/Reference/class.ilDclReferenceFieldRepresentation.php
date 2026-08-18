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

use ILIAS\UI\Component\Input\Container\Form\FormInput;

class ilDclReferenceFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public const string REFERENCE_SEPARATOR = " -> ";

    public function getInputField(): FormInput
    {
        $options = [];
        $fieldref = (int) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
        $ref_field = ilDclCache::getFieldCache($fieldref);
        if ($ref_field->getTableId() !== 0) {
            $ref_table = ilDclCache::getTableCache($ref_field->getTableId());
            foreach ($ref_table->getRecords() as $record) {
                $record_field = $record->getRecordField($fieldref);
                if ($record_field->getValue()) {
                    switch ($ref_field->getDatatypeId()) {
                        case ilDclDatatype::INPUTFORMAT_FILEUPLOAD:
                        case ilDclDatatype::INPUTFORMAT_DATE:
                            $options[$record->getId()] = $record->getRecordFieldSingleHTML($fieldref);
                            break;
                        case ilDclDatatype::INPUTFORMAT_MOB:
                            $options[$record->getId()] = (new ilObjMediaObject($record_field->getValue()))->getTitle();
                            break;
                        case ilDclDatatype::INPUTFORMAT_ILIAS_REF:
                            $value = $record_field->getValue();
                            $options[$record->getId()] = ilObject::_lookupTitle(ilObject::_lookupObjectId($value)) . ' [' . $value . ']';
                            break;
                        default:
                            $options[$record->getId()] = $record_field->getPlainText();
                            break;
                    }
                }
            }
        }
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            return $this->factory->input()->field()->multiSelect(
                $this->getField()->getTitle(),
                $options,
                $this->field->getDescription()
            );
        } else {
            return $this->factory->input()->field()->select(
                $this->getField()->getTitle(),
                $options,
                $this->field->getDescription()
            );
        }
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): mixed
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->getField()->getId()
        );
        $ref_field_id = (int) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
        $ref_field = ilDclCache::getFieldCache($ref_field_id);
        $ref_table = ilDclCache::getTableCache($ref_field->getTableId());
        $options = [];
        foreach ($ref_table->getRecords() as $record) {
            $options[$record->getId()] = $record->getRecordField($ref_field_id)->getPlainText();
        }
        // Sort by values ASC
        asort($options);
        $options = ['' => $this->lng->txt('dcl_all_entries')]
            + $options
            + ['none' => $this->lng->txt('dcl_no_entry')];
        $input->setOptions($options);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $options = [];
        // Get Tables
        $tables = $dcl->getTables();
        foreach ($tables as $table) {
            foreach ($table->getRecordFields() as $field) {
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
