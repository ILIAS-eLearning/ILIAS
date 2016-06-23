<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordFieldModel.php';
require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordModel.php';
require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php';
require_once("./Services/Link/classes/class.ilLink.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionImporter.php");

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclReferenceRecordFieldModel extends ilDclBaseRecordFieldModel {

	/**
	 * @var int
	 */
	protected $dcl_obj_id;

	/**
	 * @param ilDclBaseRecordModel $record
	 * @param ilDclBaseFieldModel  $field
	 */
	public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field) {
		parent::__construct($record, $field);
		$dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
	}

	/**
	 * @return int|string
	 */
	public function getExportValue() {
		$value = $this->getValue();
		if ($value) {
			if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
				foreach ($value as $val) {
					if ($val) {
						$ref_rec = ilDclCache::getRecordCache($val);
						$ref_record_field = $ref_rec->getRecordField($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
						if($ref_record_field) {
							$names[] = $ref_record_field->getExportValue();
						}

					}
				}
				return implode(', ', $names);
			} else {
				$ref_rec = ilDclCache::getRecordCache($this->getValue());
				$ref_record_field = $ref_rec->getRecordField($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));

				$exp_value = "";
				if($ref_record_field) {
					$exp_value = $ref_record_field->getExportValue();
				}

				return $exp_value;
			}
		} else {
			return "";
		}
	}

	public function getValueFromExcel($excel, $row, $col){
		global $lng;
		$value = parent::getValueFromExcel($excel, $row, $col);
		$old = $value;
		$value = $this->getReferenceFromValue($value);
		if (!$value && $old) {
			$warning = "(" . $row . ", " . ilDataCollectionImporter::getExcelCharForInteger($col+1) . ") " . $lng->txt("dcl_no_such_reference") . " "
				. $old;
			return array('warning' => $warning);
		}

		return $value;
	}

	/**
	 * @param $field ilDclBaseFieldModel
	 * @param $value
	 *
	 * @return int
	 */
	public function getReferenceFromValue($value) {
		$field = ilDclCache::getFieldCache($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
		$table = ilDclCache::getTableCache($field->getTableId());
		$record_id = 0;
		foreach ($table->getRecords() as $record) {
			if ($record->getRecordField($field->getId())->getValue() == $value) {
				$record_id = $record->getId();
			}
		}

		return $record_id;
	}
}

?>