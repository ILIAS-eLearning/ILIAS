<?php
require_once ('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldRepresentation.php');

/**
 * Class ilDclDateTimeREpresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclNumberFieldRepresentation extends ilDclBaseFieldRepresentation {

	public function getInputField(ilPropertyFormGUI $form, $record_id = 0) {
		$input = new ilNumberInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
		$this->setupInputField($input, $this->getField());
		return $input;
	}

	public function addFilterInputFieldToTable(ilTable2GUI $table) {
		$input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_NUMBER_RANGE, false, $this->getField()->getId());
		$input->setSubmitFormOnEnter(true);

		$this->setupFilterInputField($input);

		return $this->getFilterInputFieldValue($input);
	}


	public function passThroughFilter(ilDclBaseRecordModel $record, $filter) {
		$value = $record->getRecordFieldValue($this->getField()->getId());
		if ((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to'])) {
			return true;
		}
		return false;
	}


	/**
	 * @inheritDoc
	 */
	protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);

		return $opt;
	}
}