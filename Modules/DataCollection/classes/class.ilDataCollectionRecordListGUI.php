<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");
require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
require_once('./Modules/DataCollection/classes/class.ilDataCollectionLinkButton.php');

/**
 * Class ilDataCollectionRecordListGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordListGUI {

	const MODE_VIEW = 1;
	const MODE_MANAGE = 2;
	/**
	 * Stores current mode active
	 *
	 * @var int
	 */
	protected $mode = self::MODE_VIEW;
	/**
	 * @var int
	 */
	protected $max_imports = 100;
	/**
	 * @var array
	 */
	protected $supported_import_datatypes = array(
		ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN,
		ilDataCollectionDatatype::INPUTFORMAT_NUMBER,
		ilDataCollectionDatatype::INPUTFORMAT_REFERENCE,
		ilDataCollectionDatatype::INPUTFORMAT_TEXT,
		ilDataCollectionDatatype::INPUTFORMAT_DATETIME
	);
	/**
	 * @var ilDataCollectionTable
	 */
	protected $table_obj;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var array
	 */
	protected static $available_modes = array( self::MODE_VIEW, self::MODE_MANAGE );


	/**
	 * @param ilObjDataCollectionGUI $a_parent_obj
	 * @param                        $table_id
	 */
	public function  __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id) {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->main_table_id = $a_parent_obj->object->getMainTableId();
		$this->table_id = $table_id;
		if ($this->table_id == NULL) {
			$this->table_id = $_GET["table_id"];
		}
		$this->obj_id = $a_parent_obj->obj_id;
		$this->parent_obj = $a_parent_obj;
		$this->table_obj = ilDataCollectionCache::getTableCache($table_id);
		$ilCtrl->setParameterByClass("ildatacollectionrecordeditgui", "table_id", $table_id);
		$this->mode = (isset($_GET['mode']) AND in_array($_GET['mode'], self::$available_modes)) ? (int)$_GET['mode'] : self::MODE_VIEW;
	}


	/**
	 * execute command
	 */
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case 'listRecords':
				$this->setSubTabs();
				$this->listRecords();
				break;
			case 'confirmDeleteRecords':
				$this->confirmDeleteRecords();
				break;
			case 'cancelDelete':
				$this->setSubTabs();
				$this->listRecords();
				break;
			case 'deleteRecords':
				$this->deleteRecords();
				break;
			default:
				$this->$cmd();
				break;
		}
	}


	public function listRecords() {
		global $tpl, $lng, $ilToolbar;
		/**
		 * @var $ilToolbar ilToolbarGUI
		 * @var $ilToolbar ilToolbarGUI
		 */
		// Show tables
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
		if (ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id)) {
			$tables = $this->parent_obj->object->getTables();
		} else {
			$tables = $this->parent_obj->object->getVisibleTables();
		}
		$options = array();
		foreach ($tables as $table) {
			$options[$table->getId()] = $table->getTitle();
		}

		$tpl->addCss("./Modules/DataCollection/css/dcl_reference_hover.css");

		$list = new ilDataCollectionRecordListTableGUI($this, "listRecords", $this->table_obj, $this->mode);
		$list->setExternalSegmentation(true);
		$list->setExternalSorting(true);
		$list->determineLimit();
		$list->determineOffsetAndOrder();
		$data = $this->table_obj->getPartialRecords($list->getOrderField(), $list->getOrderDirection(), $list->getLimit(), $list->getOffset(), $list->getFilter());
		$records = $data['records'];
		$total = $data['total'];
		$list->setMaxCount($total);
		$list->setRecordData($records);

		if (count($options) > 0) {
			include_once './Services/Form/classes/class.ilSelectInputGUI.php';
			$table_selection = new ilSelectInputGUI('', 'table_id');
			$table_selection->setOptions($options);
			$table_selection->setValue($this->table_id);

			$ilToolbar->setFormAction($this->ctrl->getFormActionByClass("ilDataCollectionRecordListGUI", "doTableSwitch"));
			$ilToolbar->addText($lng->txt("dcl_table"));
			$ilToolbar->addInputItem($table_selection);
			$ilToolbar->addFormButton($lng->txt('change'), 'doTableSwitch');
			$ilToolbar->addSeparator();
		}
		$permission_to_add_or_import = $this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id) AND $this->table_obj->hasCustomFields();
		if ($permission_to_add_or_import) {
			$this->ctrl->setParameterByClass("ildatacollectionrecordeditgui", "record_id", NULL);

			$add_new = ilLinkButton::getInstance();
			$add_new->setCaption("dcl_add_new_record");
			$add_new->setUrl($this->ctrl->getFormActionByClass("ildatacollectionrecordeditgui", "create"));
			//			$add_new->addCSSClass('emphsubmit');
			$ilToolbar->addButtonInstance($add_new);
		}

		if (($this->table_obj->getExportEnabled() OR $this->table_obj->hasPermissionToFields($this->parent_obj->ref_id))) {

			$export = ilDataCollectionLinkButton::getInstance();
			$export->setCaption("dcl_export_table_excel");
			$export->setUrl($this->ctrl->getFormActionByClass("ildatacollectionrecordlistgui", "exportExcel"));
			if (count($this->table_obj->getExportableFields()) == 0 OR $total == 0) {
				$export->setUseWrapper(true);
				$export->setDisabled(true);
				$export->addAttribute('data-toggle', 'datacollection-tooltip', true);
				$export->addAttribute('data-placement', 'bottom', true);
				$export->addAttribute('title', $lng->txt('dcl_no_exportable_fields_or_no_data'), true);
			}
			$ilToolbar->addButtonInstance($export);
		}

		if ($permission_to_add_or_import) {
			$this->ctrl->setParameterByClass("ildatacollectionrecordeditgui", "record_id", NULL);

			$import = ilLinkButton::getInstance();
			$import->setCaption("dcl_import_records .xls");
			$import->setUrl($this->ctrl->getFormActionByClass("ildatacollectionrecordlistgui", "showImportExcel"));
			$ilToolbar->addButtonInstance($import);
		}

		// requested not to implement this way...
		//$tpl->addJavaScript("Modules/DataCollection/js/fastTableSwitcher.js");

		if (count($this->table_obj->getRecordFields()) == 0) {
			ilUtil::sendInfo($lng->txt("dcl_no_fields_yet") . " "
				. ($this->table_obj->hasPermissionToFields($this->parent_obj->ref_id) ? $lng->txt("dcl_create_fields") : ""));
		}

		$tpl->getStandardTemplate();
		$tpl->setPermanentLink("dcl", $this->parent_obj->ref_id);
		if ($desc = $this->table_obj->getDescription()) {
			$desc = "<div class='ilDclTableDescription'>{$desc}</div>";
		}
		$tpl->setContent($desc . $list->getHTML());
	}


	/**
	 * Export DC as Excel sheet
	 *
	 */
	public function exportExcel() {
		global $ilCtrl, $lng;
		if (!($this->table_obj->getExportEnabled() || $this->table_obj->hasPermissionToFields($this->parent_obj->ref_id))) {
			echo $lng->txt("access_denied");
			exit;
		}

		require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
		$list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$list->setRecordData($this->table_obj->getRecordsByFilter($list->getFilter()));
		$list->setExternalSorting(true);
		if (!$list->dataExists()) {
			$this->ctrl->redirect($this->parent_obj);
		}

		$list->exportData(ilTable2GUI::EXPORT_EXCEL, true);
	}


	public function showImportExcel($form = NULL) {
		global $tpl;
		if (!$form) {
			$form = $this->initForm();
		}
		$tpl->setContent($form->getHTML());
	}


	/**
	 * Init form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function initForm() {
		global $lng, $ilCtrl;
		/** @var $ilCtrl ilCtrl */
		$ilCtrl = $ilCtrl;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		$item = new ilCustomInputGUI();
		$item->setHtml($lng->txt('dcl_file_format_description'));
		$item->setTitle("Info");
		$form->addItem($item);

		$file = new ilFileInputGUI($lng->txt("import_file"), "import_file");
		$file->setRequired(true);
		$form->addItem($file);

		$cb = new ilCheckboxInputGUI($lng->txt("dcl_simulate_import"), "simulate");
		$cb->setInfo($lng->txt("dcl_simulate_info"));
		$form->addItem($cb);

		$form->addCommandButton("importExcel", $lng->txt("save"));

		return $form;
	}


	/**
	 * Import Data from Excel sheet
	 */
	public function importExcel() {
		global $lng;

		if (!($this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id))) {
			echo $lng->txt("access_denied");
			exit;
		}
		$form = $this->initForm();
		if ($form->checkInput()) {
			$file = $form->getInput("import_file");
			$file_location = $file["tmp_name"];
			$simulate = $form->getInput("simulate");
			$this->importRecords($file_location, $simulate);
		} else {
			$this->showImportExcel($form);
		}
	}


	/**
	 * Import records from Excel file
	 *
	 * @param      $file
	 * @param bool $simulate
	 */
	private function importRecords($file, $simulate = false) {
		global $ilUser, $lng;
		include_once("./Modules/DataCollection/libs/ExcelReader/excel_reader2.php");

		$warnings = array();
		try {
			$excel = new Spreadsheet_Excel_Reader($file);
		} catch (Exception $e) {
			$warnings[] = $lng->txt("dcl_file_not_readable");
		}
		if (count($warnings)) {
			$this->endImport(0, $warnings);

			return;
		}
		$field_names = array();
		for ($i = 1; $i <= $excel->colcount(); $i ++) {
			$field_names[$i] = $excel->val(1, $i);
		}
		$fields = $this->getImportFieldsFromTitles($field_names, $warnings);

		for ($i = 2; $i <= $excel->rowcount(); $i ++) {
			$record = new ilDataCollectionRecord();
			$record->setTableId($this->table_obj->getId());
			$record->setOwner($ilUser->getId());
			$date_obj = new ilDateTime(time(), IL_CAL_UNIX);
			$record->setCreateDate($date_obj->get(IL_CAL_DATETIME));
			$record->setTableId($this->table_id);
			if (!$simulate) {
				$record->doCreate();
			}
			foreach ($fields as $col => $field) {
				$value = $excel->val($i, $col);
				$value = utf8_encode($value);
				try {
					if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
						$old = $value;
						$value = $this->getReferenceFromValue($field, $value);
						if (!$value) {
							$warnings[] = "(" . $i . ", " . $this->getExcelCharForInteger($col) . ") " . $lng->txt("dcl_no_such_reference") . " "
								. $old;
						}
					} else {
						if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_DATETIME) {
							$value = array(
								'date' => date('Y-m-d', strtotime($value)),
								'time' => '00:00:00',
							);
						}
					}
					$field->checkValidity($value, $record->getId());
					if (!$simulate) {
						$record->setRecordFieldValue($field->getId(), $value);
					}
				} catch (ilDataCollectionInputException $e) {
					$warnings[] = "(" . $i . ", " . $this->getExcelCharForInteger($col) . ") " . $e;
				}
			}
			if (!$simulate) {
				$record->doUpdate();
			}
			if ($i - 1 > $this->max_imports) {
				$warnings[] = $lng->txt("dcl_max_import") . ($excel->rowcount() - 1) . " > " . $this->max_imports;
				break;
			}
		}
		$this->endImport($i - 2, $warnings);
	}


	/**
	 * End import
	 *
	 * @param $i
	 * @param $warnings
	 */
	public function endImport($i, $warnings) {
		global $tpl, $lng, $ilCtrl;
		$output = new ilTemplate("tpl.dcl_import_terminated.html", true, true, "Modules/DataCollection");
		$output->setVariable("IMPORT_TERMINATED", $lng->txt("dcl_import_terminated") . ": " . $i);
		foreach ($warnings as $warning) {
			$output->setCurrentBlock("warnings");
			$output->setVariable("WARNING", $warning);
			$output->parseCurrentBlock();
		}
		if (!count($warnings)) {
			$output->setCurrentBlock("warnings");
			$output->setVariable("WARNING", $lng->txt("dcl_no_warnings"));
			$output->parseCurrentBlock();
		}
		$output->setVariable("BACK_LINK", $ilCtrl->getLinkTargetByClass("ilDataCollectionRecordListGUI", "listRecords"));
		$output->setVariable("BACK", $lng->txt("back"));
		$tpl->setContent($output->get());
	}


	/**
	 * @param $field ilDataCollectionField
	 * @param $value
	 *
	 * @return int
	 */
	public function getReferenceFromValue($field, $value) {
		$field = ilDataCollectionCache::getFieldCache($field->getFieldRef());
		$table = ilDataCollectionCache::getTableCache($field->getTableId());
		$record_id = 0;
		foreach ($table->getRecords() as $record) {
			if ($record->getRecordField($field->getId())->getValue() == $value) {
				$record_id = $record->getId();
			}
		}

		return $record_id;
	}


	private function getExcelCharForInteger($int) {
		$char = "";
		$rng = range("A", "Z");
		while ($int > 0) {
			$diff = $int % 26;
			$char = $rng[$diff - 1] . $char;
			$int -= $char;
			$int /= 26;
		}

		return $char;
	}


	/**
	 * @param ilDataCollectionField $field
	 * @param array                 $warnings
	 *
	 * @return bool
	 */
	private function checkImportType($field, &$warnings) {
		global $lng;
		if (in_array($field->getDatatypeId(), $this->supported_import_datatypes)) {
			return true;
		} else {
			$warnings[] = $field->getTitle() . ": " . $lng->txt("dcl_not_supported_in_import");

			return false;
		}
	}


	/**
	 * @param $titles string[]
	 * @param $warnings
	 *
	 * @return ilDataCollectionField[]
	 */
	private function getImportFieldsFromTitles($titles, &$warnings) {
		global $lng;
		$fields = $this->table_obj->getRecordFields();
		$import_fields = array();
		foreach ($fields as $field) {
			if ($this->checkImportType($field, $warnings)) {
				foreach ($titles as $key => $value) {
					if ($value == $field->getTitle()) {
						$import_fields[$key] = $field;
					}
				}
			}
		}
		foreach ($titles as $key => $value) {
			if (!isset($import_fields[$key])) {
				$warnings[] = "(1, " . $this->getExcelCharForInteger($key) . ") \"" . $value . "\" " . $lng->txt("dcl_row_not_found");
			}
		}

		return $import_fields;
	}


	/**
	 * doTableSwitch
	 */
	public function doTableSwitch() {
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
		$ilCtrl->redirect($this, "listRecords");
	}


	protected function applyFilter() {
		$table = new ilDataCollectionRecordListTableGUI($this, "listRecords", $this->table_obj);
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->ctrl->redirect($this, 'listRecords');
		//		$this->listRecords();
	}


	protected function resetFilter() {
		$table = new ilDataCollectionRecordListTableGUI($this, "listRecords", $this->table_obj);
		$table->resetOffset();
		$table->resetFilter();
		$this->ctrl->redirect($this, 'listRecords');
		//		$this->listRecords();
	}


	/**
	 * send File to User
	 */
	public function sendFile() {
		global $ilAccess;
		//need read access to receive file
		if ($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id)) {
			$rec_id = $_GET['record_id'];
			$record = ilDataCollectionCache::getRecordCache($rec_id);
			$field_id = $_GET['field_id'];
			$file_obj = new ilObjFile($record->getRecordFieldValue($field_id), false);
			if (!$this->recordBelongsToCollection($record, $this->parent_obj->ref_id)) {
				return;
			}
			ilUtil::deliverFile($file_obj->getFile(), $file_obj->getTitle());
		}
	}


	/**
	 * Confirm deletion of multiple records
	 *
	 */
	public function confirmDeleteRecords() {
		global $ilCtrl, $lng, $tpl, $ilTabs;
		/** @var ilTabsGUI $ilTabs */
		$ilTabs->clearSubTabs();
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('dcl_confirm_delete_records'));
		$record_ids = isset($_POST['record_ids']) ? $_POST['record_ids'] : array();
		foreach ($record_ids as $record_id) {
			/** @var ilDataCollectionRecord $record */
			$record = ilDataCollectionCache::getRecordCache($record_id);
			if ($record) {
				$conf->addItem('record_ids[]', $record->getId(), rtrim(implode(", ", $record->getRecordFieldValues()), ', '));
			}
		}
		$conf->addHiddenItem('table_id', $this->table_id);
		$conf->setConfirm($lng->txt('dcl_delete_records'), 'deleteRecords');
		$conf->setCancel($lng->txt('cancel'), 'cancelDelete');
		$tpl->setContent($conf->getHTML());
	}


	/**
	 * Delete multiple records
	 *
	 * @param array $record_ids
	 */
	public function deleteRecords(array $record_ids = array()) {
		/** @var ilCtrl $ilCtrl */
		global $ilCtrl, $lng;

		$record_ids = count($record_ids) ? $record_ids : $_POST['record_ids'];
		$record_ids = (is_null($record_ids)) ? array() : $record_ids;

		// Invoke deletion
		$n_skipped = 0;
		foreach ($record_ids as $record_id) {
			/** @var ilDataCollectionRecord $record */
			$record = ilDataCollectionCache::getRecordCache($record_id);
			if ($record) {
				if ($record->hasPermissionToDelete((int)$_GET['ref_id'])) {
					$record->doDelete();
				} else {
					$n_skipped ++;
				}
			}
		}

		$n_deleted = (count($record_ids) - $n_skipped);
		if ($n_deleted) {
			ilUtil::sendSuccess(sprintf($lng->txt('dcl_deleted_records'), $n_deleted), true);
		}
		if ($n_skipped) {
			ilUtil::sendInfo(sprintf($lng->txt('dcl_skipped_delete_records'), $n_skipped), true);
		}
		$ilCtrl->redirect($this, 'listRecords');
	}


	/**
	 * @param ilDataCollectionRecord $record
	 *
	 * @return bool
	 */
	private function recordBelongsToCollection(ilDataCollectionRecord $record) {
		$table = $record->getTable();
		$obj_id = $this->parent_obj->object->getId();
		$obj_id_rec = $table->getCollectionObject()->getId();

		return $obj_id == $obj_id_rec;
	}


	/**
	 * Add subtabs
	 *
	 */
	protected function setSubTabs() {
		global $ilTabs, $lng, $ilCtrl;

		/** @var ilCtrl $ilCtrl */
		/** @var ilTabsGUI $ilTabs */
		$ilTabs->addSubTab('mode_1', $lng->txt('view'), $ilCtrl->getLinkTarget($this, 'listRecords'));
		if ($this->table_obj->hasPermissionToDeleteRecords((int)$_GET['ref_id'])) {
			$ilCtrl->setParameter($this, 'mode', self::MODE_MANAGE);
			$ilTabs->addSubTab('mode_2', $lng->txt('dcl_manage'), $ilCtrl->getLinkTarget($this, 'listRecords'));
			$ilCtrl->clearParameters($this);
		}
		$ilTabs->setSubTabActive('mode_' . $this->mode);
	}
}

?>