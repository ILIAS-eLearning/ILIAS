<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/class.ilObjDataCollectionAccess.php');
require_once('./Modules/DataCollection/classes/class.ilObjDataCollectionGUI.php');
require_once('./Modules/DataCollection/classes/Content/class.ilDclRecordListGUI.php');
require_once('./Modules/DataCollection/classes/Table/class.ilDclTable.php');
require_once ('./Services/Export/classes/class.ilExport.php');
require_once ('./Services/Excel/classes/class.ilExcel.php');


/**
 * Hook-Class for exporting data-collections (used in SOAP-Class)
 * This Class avoids duplicated code by routing the request to the right place
 * 
 * @author Michael Herren <mh@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclContentImporter
{
	//const SOAP_FUNCTION_NAME = 'exportDataCollectionContent';

	const EXPORT_EXCEL = 'xls';

	/**
	 * @var int
	 */
	protected $max_imports = 100;

	/**
	 * @var array
	 */
	protected $supported_import_datatypes = array(
		ilDclDatatype::INPUTFORMAT_BOOLEAN,
		ilDclDatatype::INPUTFORMAT_NUMBER,
		ilDclDatatype::INPUTFORMAT_REFERENCE,
		ilDclDatatype::INPUTFORMAT_TEXT,
		ilDclDatatype::INPUTFORMAT_DATETIME,
		ilDclDatatype::INPUTFORMAT_PLUGIN
	);

	protected $warnings;

	/**
	 * @var int $ref_id Ref-ID of DataCollection
	 */
	protected $ref_id;

	/**
	 * @var int $table_id Table-Id for export
	 */
	protected $table_id;

	/**
	 * @var ilObjDataCollection
	 */
	protected $dcl;

	/**
	 * @var ilDclTable[]
	 */
	protected $tables;

	/**
	 * @var
	 */
	protected $lng;

	public function __construct($ref_id, $table_id = null) {
		global $DIC;
		$lng = $DIC['lng'];

		$this->ref_id = $ref_id;
		$this->table_id = $table_id;

		$this->lng = $lng;

		$this->dcl = new ilObjDataCollection($ref_id);
		$this->tables = ($table_id)? array($this->dcl->getTableById($table_id)) : $this->dcl->getTables();
	}

	public function import($file, $simulate = false) {
		global $DIC;
		$ilUser = $DIC['ilUser'];

		$this->warnings = array();
		try {
			$excel = new ilExcel();
			$excel->loadFromFile($file);
		} catch (Exception $e) {
			$this->warnings[] = $this->lng->txt("dcl_file_not_readable");
		}

		$sheet_count = $excel->getSheetCount();
		$excel->setActiveSheet(0);

		if($sheet_count != count($this->tables)) {
			$this->warnings[] = $this->lng->txt('dcl_file_not_readable');
		}

		if (count($this->warnings)) {
			return array('line'=>0, 'warnings'=>$this->warnings);
		}

		for($sheet = 0; $sheet < $sheet_count; $sheet++) {
			$excel->setActiveSheet($sheet);
			$table = $this->tables[$sheet];

			// only 31 character-long table-titles are allowed
			$sheet_title = substr($table->getTitle(), 0, 31);
			if($excel->getSheetTitle() != $sheet_title) {
				$this->warnings[] = $this->lng->txt('dcl_table_title_not_matching');
				continue;
			}

			$field_names = array();
			$sheet_data = $excel->getSheetAsArray();

			foreach ($sheet_data[0] as $column) {
				$field_names[] = $column;
			}
			$fields = $this->getImportFieldsFromTitles($table, $field_names);

			for ($i = 2; $i <= count($sheet_data); $i ++) {
				$record = new ilDclBaseRecordModel();
				$record->setOwner($ilUser->getId());
				$date_obj = new ilDateTime(time(), IL_CAL_UNIX);
				$record->setCreateDate($date_obj->get(IL_CAL_DATETIME));
				$record->setTableId($table->getId());
				if (!$simulate) {
					$record->doCreate();
				}
				foreach ($fields as $col => $field) {
					try {
						$value = $record->getRecordFieldValueFromExcel($excel, $i, $col, $field);

						if (is_array($value) && isset($value['warning'])) {
							$this->warnings[] = $value['warning'];
							$value = '';
						}

						$field->checkValidity($value, $record->getId());
						if (!$simulate) {
							$record->setRecordFieldValue($field->getId(), $value);
						}
					} catch (ilDclInputException $e) {
						$this->warnings[] = "(" . $i . ", " . ilDataCollectionImporter::getExcelCharForInteger($col + 1) . ") " . $e;
					}
				}

				if (!$simulate) {
					$record->doUpdate();
				}
				if ($i - 1 > $this->max_imports) {
					$this->warnings[] = $this->lng->txt("dcl_max_import") . ($excel->rowcount() - 1) . " > " . $this->max_imports;
					break;
				}
			}
		}


		return array('line'=>$i-2, 'warnings'=>$this->warnings);
	}

	/**
	 * @param ilDclBaseFieldModel $field
	 *
	 * @return bool
	 */
	protected function checkImportType($field) {
		if (in_array($field->getDatatypeId(), $this->supported_import_datatypes)) {
			return true;
		} else {
			$this->warnings[] = $field->getTitle() . ": " . $this->lng->txt("dcl_not_supported_in_import");

			return false;
		}
	}


	/**
	 * @param ilDclTable $table
	 * @param $titles string[]
	 *
	 * @return ilDclBaseFieldModel[]
	 */
	protected function getImportFieldsFromTitles($table, $titles) {
		$fields = $table->getRecordFields();
		$import_fields = array();
		foreach ($fields as $field) {
			if ($this->checkImportType($field)) {
				foreach ($titles as $key => $value) {
					if ($value == $field->getTitle()) {
						$import_fields[$key] = $field;
						if ($field->hasProperty(ilDclBaseFieldModel::PROP_URL) && $titles[$key+1] == $field->getTitle().'_title') {
							unset($titles[$key+1]);
						}
					}
				}
			}
		}

		foreach ($titles as $key => $value) {
			if (!isset($import_fields[$key])) {
				$this->warnings[] = "(1, " . ilDataCollectionImporter::getExcelCharForInteger($key+1) . ") \"" . $value . "\" " . $this->lng->txt("dcl_row_not_found");
			}
		}

		return $import_fields;
	}
}

?>