<?php
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldRepresentation.php');

/**
 * Class ilDclTextFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclReferenceFieldRepresentation extends ilDclBaseFieldRepresentation {
	const REFERENCE_SEPARATOR = " -> ";

	public function getInputField(ilPropertyFormGUI $form, $record_id = 0) {
		if (!$this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
			$input = new ilSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
		} else {
			$input = new ilMultiSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
			$input->setWidth(100);
			$input->setWidthUnit('%');
		}

		$this->setupInputField($input, $this->getField());

		$fieldref = $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);

		$reffield = ilDclCache::getFieldCache($fieldref);
		$options = array();
		if (!$this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
			$options[""] = $this->lng->txt('dcl_please_select');
		}
		$reftable = ilDclCache::getTableCache($reffield->getTableId());
		foreach ($reftable->getRecords() as $record) {
			// If the referenced field is MOB or FILE, we display the filename in the dropdown
			switch ($reffield->getDatatypeId()) {
				case ilDclDatatype::INPUTFORMAT_FILE:
					$file_obj = new ilObjFile($record->getRecordFieldValue($fieldref), false);
					$options[$record->getId()] = $file_obj->getFileName();
					break;
				case ilDclDatatype::INPUTFORMAT_MOB:
					$media_obj = new ilObjMediaObject($record->getRecordFieldValue($fieldref), false);
					$options[$record->getId()] = $media_obj->getTitle();
					break;
				case ilDclDatatype::INPUTFORMAT_DATETIME:
					$options[$record->getId()] = $record->getRecordFieldSingleHTML($fieldref);
					break;
				case ilDclDatatype::INPUTFORMAT_TEXT:
					$value = $record->getRecordFieldValue($fieldref);
					$json = json_decode($value);
					if ($json instanceof stdClass) {
						$value = $json->title ? $json->title : $json->link;
					}
					$options[$record->getId()] = $value;
					break;
				default:
					$options[$record->getId()] = $record->getRecordFieldValue($fieldref);
					break;
			}
		}
		asort($options);
		$input->setOptions($options);

		if ($reftable->hasPermissionToAddRecord($_GET['ref_id'])) {
			$input->addCustomAttribute('data-ref="1"');
			$input->addCustomAttribute('data-ref-table-id="' . $reftable->getId() . '"');
			$input->addCustomAttribute('data-ref-field-id="' . $reffield->getId() . '"');
		}

		return $input;
	}

	public function addFilterInputFieldToTable(ilTable2GUI $table) {
		$input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT, false, $this->getField()->getId());
		$ref_field_id = $this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE);
		$ref_field = ilDclCache::getFieldCache($ref_field_id);
		$ref_table = ilDclCache::getTableCache($ref_field->getTableId());
		$options = array();
		foreach ($ref_table->getRecords() as $record) {
			$options[$record->getId()] = $record->getRecordFieldValue($ref_field_id);
		}
		// Sort by values ASC
		asort($options);
		$options = array('' => $this->lng->txt('dcl_any')) + $options;
		$input->setOptions($options);

		$this->setupFilterInputField($input);

		return $this->getFilterInputFieldValue($input);
	}


	public function passThroughFilter(ilDclBaseRecordModel $record, $filter) {
		$value = $record->getRecordFieldValue($this->getField()->getId());

		$pass = false;
		if ($filter && $this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE) && is_array($value) && in_array($filter, $value)) {
			$pass = true;
		}
		if (!$filter || $filter == $value) {
			$pass = true;
		}

		return $pass;
	}

	/**
	 * @inheritDoc
	 */
	public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);

		$options = array();
		// Get Tables
		require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
		$tables = $dcl->getTables();
		foreach ($tables as $table) {
			foreach ($table->getRecordFields() as $field) {
				//referencing references may lead to endless loops.
				if ($field->getDatatypeId() != ilDclDatatype::INPUTFORMAT_REFERENCE) {
					$options[$field->getId()] = $table->getTitle() . self::REFERENCE_SEPARATOR . $field->getTitle();
				}
			}
		}
		$prop_table_selection = new ilSelectInputGUI($this->lng->txt('dcl_reference_title'), 'prop_' .ilDclBaseFieldModel::PROP_REFERENCE);
		$prop_table_selection->setOptions($options);

		$opt->addSubItem($prop_table_selection);

		$prop_ref_link = new ilDclCheckboxInputGUI($this->lng->txt('dcl_reference_link'), 'prop_'.ilDclBaseFieldModel::PROP_REFERENCE_LINK);
		$opt->addSubItem($prop_ref_link);

		$prop_multi_select = new ilDclCheckboxInputGUI($this->lng->txt('dcl_multiple_selection'), 'prop_'.ilDclBaseFieldModel::PROP_N_REFERENCE);
		$opt->addSubItem($prop_multi_select);

		return $opt;
	}
}