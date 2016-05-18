<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordModel.php");
require_once ("./Modules/DataCollection/classes/class.ilDclTable.php");
require_once ("./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php");
require_once ('./Modules/DataCollection/classes/class.ilDclRecordListTableGUI.php');
require_once ("./Modules/DataCollection/classes/Helpers/class.ilDclLinkButton.php");
require_once ('./Modules/DataCollection/classes/class.ilDclRecordListTableGUI.php');
require_once ('./Modules/DataCollection/classes/class.ilDclContentImporter.php');

/**
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclRecordListGUI {

	const MODE_VIEW = 1;
	const MODE_MANAGE = 2;
	/**
	 * Stores current mode active
	 *
	 * @var int
	 */
	protected $mode = self::MODE_VIEW;

	/**
	 * @var ilDclTable
	 */
	protected $table_obj;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var array
	 */
	protected static $available_modes = array( self::MODE_VIEW, self::MODE_MANAGE );


	/**
	 * @param ilObjDataCollectionGUI $a_parent_obj
	 * @param                        $table_id
	 */
	public function  __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id) {
		global $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->main_table_id = $a_parent_obj->object->getMainTableId();
		$this->table_id = $table_id;
		if ($this->table_id == NULL) {
			$this->table_id = $_GET["table_id"];
		}
		$this->obj_id = $a_parent_obj->obj_id;
		$this->parent_obj = $a_parent_obj;
		$this->table_obj = ilDclCache::getTableCache($table_id);
		$this->ctrl->setParameterByClass("ildclrecordeditgui", "table_id", $table_id);
		$this->mode = (isset($_GET['mode']) && in_array($_GET['mode'], self::$available_modes)) ? (int)$_GET['mode'] : self::MODE_VIEW;
	}


	/**
	 * execute command
	 */
	public function executeCommand() {
		global $ilTabs;
		$this->ctrl->saveParameter($this, 'mode');
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
			case 'showImportExcel':
				$ilTabs->setBack2Target($this->lng->txt('back'), $this->ctrl->getLinkTarget($this->parent_obj));
				$this->$cmd();
				break;

			default:
				$this->$cmd();
				break;
		}
	}


	public function listRecords() {
		global $tpl, $ilToolbar;
		/**
		 * @var $ilToolbar ilToolbarGUI
		 * @var $ilToolbar ilToolbarGUI
		 */
		// Show tables
		require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
		$options = $this->getAvailableTables();

		$tpl->addCss("./Modules/DataCollection/css/dcl_reference_hover.css");

		list($list, $total) = $this->getRecordListTableGUI();

		if (count($options) > 0) {
			include_once './Services/Form/classes/class.ilSelectInputGUI.php';
			$table_selection = new ilSelectInputGUI('', 'table_id');
			$table_selection->setOptions($options);
			$table_selection->setValue($this->table_id);

			$ilToolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclRecordListGUI", "doTableSwitch"));
			$ilToolbar->addText($this->lng->txt("dcl_table"));
			$ilToolbar->addInputItem($table_selection);
			$ilToolbar->addFormButton($this->lng->txt('change'), 'doTableSwitch');
			$ilToolbar->addSeparator();
		}

		$permission_to_add_or_import = $this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id) AND $this->table_obj->hasCustomFields();
		if ($permission_to_add_or_import) {
			$this->ctrl->setParameterByClass("ildclrecordeditgui", "record_id", NULL);

			$add_new = ilLinkButton::getInstance();
			$add_new->setPrimary(true);
			$add_new->setCaption("dcl_add_new_record");
			$add_new->setUrl($this->ctrl->getFormActionByClass("ildclrecordeditgui", "create"));
			$ilToolbar->addStickyItem($add_new);
		}

		if ($permission_to_add_or_import) {
			$this->ctrl->setParameterByClass("ildclrecordeditgui", "record_id", NULL);

			$import = ilLinkButton::getInstance();
			$import->setCaption("dcl_import_records .xls");
			$import->setUrl($this->ctrl->getFormActionByClass("ildclrecordlistgui", "showImportExcel"));
			$ilToolbar->addButtonInstance($import);
		}

		// requested not to implement this way...
		//$tpl->addJavaScript("Modules/DataCollection/js/fastTableSwitcher.js");

		if (count($this->table_obj->getRecordFields()) == 0) {
			ilUtil::sendInfo($this->lng->txt("dcl_no_fields_yet") . " "
				. ($this->table_obj->hasPermissionToFields($this->parent_obj->ref_id) ? $this->lng->txt("dcl_create_fields") : ""));
		}

		$tpl->getStandardTemplate();
		$tpl->setPermanentLink("dcl", $this->parent_obj->ref_id);
		if ($desc = $this->table_obj->getDescription()) {
			$desc = "<div class='ilDclTableDescription'>{$desc}</div>";
		}
		$tpl->setContent($desc . $list->getHTML());
	}

	public function showImportExcel($form = NULL) {
		global $tpl;
		if (!$form) {
			$form = $this->initImportForm();
		}
		$tpl->setContent($form->getHTML());
	}


	/**
	 * Init form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function initImportForm() {
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		$item = new ilCustomInputGUI();
		$item->setHtml($this->lng->txt('dcl_file_format_description'));
		$item->setTitle("Info");
		$form->addItem($item);

		$file = new ilFileInputGUI($this->lng->txt("import_file"), "import_file");
		$file->setRequired(true);
		$form->addItem($file);

		$cb = new ilCheckboxInputGUI($this->lng->txt("dcl_simulate_import"), "simulate");
		$cb->setInfo($this->lng->txt("dcl_simulate_info"));
		$form->addItem($cb);

		$form->addCommandButton("importExcel", $this->lng->txt("import"));

		return $form;
	}


	/**
	 * Import Data from Excel sheet
	 */
	public function importExcel() {
		if (!($this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id))) {
			throw new ilDclException($this->lng->txt("access_denied"));
		}
		$form = $this->initImportForm();
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
		global $ilUser;
		include_once("./Modules/DataCollection/classes/class.ilDclContentImporter.php");

		$importer = new ilDclContentImporter($this->parent_obj->object->getRefId(), $this->table_id);
		$result = $importer->import($file, $simulate);

		$this->endImport($result['line'], $result['warnings']);
	}


	/**
	 * End import
	 *
	 * @param $i
	 * @param $warnings
	 */
	public function endImport($i, $warnings) {
		global $tpl;
		$output = new ilTemplate("tpl.dcl_import_terminated.html", true, true, "Modules/DataCollection");
		$output->setVariable("IMPORT_TERMINATED", $this->lng->txt("dcl_import_terminated") . ": " . $i);
		foreach ($warnings as $warning) {
			$output->setCurrentBlock("warnings");
			$output->setVariable("WARNING", $warning);
			$output->parseCurrentBlock();
		}
		if (!count($warnings)) {
			$output->setCurrentBlock("warnings");
			$output->setVariable("WARNING", $this->lng->txt("dcl_no_warnings"));
			$output->parseCurrentBlock();
		}
		$output->setVariable("BACK_LINK", $this->ctrl->getLinkTargetByClass("ilDclRecordListGUI", "listRecords"));
		$output->setVariable("BACK", $this->lng->txt("back"));
		$tpl->setContent($output->get());
	}


	/**
	 * doTableSwitch
	 */
	public function doTableSwitch() {
		$this->ctrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
		$this->ctrl->redirect($this, "listRecords");
	}


	protected function applyFilter() {
		$table = new ilDclRecordListTableGUI($this, "listRecords", $this->table_obj);
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->ctrl->redirect($this, 'listRecords');
		//		$this->listRecords();
	}


	protected function resetFilter() {
		$table = new ilDclRecordListTableGUI($this, "listRecords", $this->table_obj);
		$table->resetOffset();
		$table->resetFilter();
		$this->ctrl->redirect($this, 'listRecords');
		//		$this->listRecords();
	}


	/**
	 * send File to User
	 */
	public function sendFile() {
		global $ilAccess, $ilUser;
		//need read access to receive file
		if ($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id)) {

			// deliver temp-files
			if(isset($_GET['ilfilehash'])) {
				$filehash = $_GET['ilfilehash'];
				$field_id = $_GET['field_id'];
				$file = ilDclPropertyFormGUI::getTempFileByHash($filehash, $ilUser->getId());

				$filepath = $file["field_".$field_id]['tmp_name'];
				$filetitle = $file["field_".$field_id]['name'];
			} else {
				$rec_id = $_GET['record_id'];
				$record = ilDclCache::getRecordCache($rec_id);
				$field_id = $_GET['field_id'];
				$file_obj = new ilObjFile($record->getRecordFieldValue($field_id), false);
				if (!$this->recordBelongsToCollection($record, $this->parent_obj->ref_id)) {
					return;
				}
				$filepath = $file_obj->getFile();
				$filetitle = $file_obj->getTitle();
			}

			ilUtil::deliverFile($filepath, $filetitle);
		}
	}


	/**
	 * Confirm deletion of multiple records
	 *
	 */
	public function confirmDeleteRecords() {
		global $tpl, $ilTabs;
		/** @var ilTabsGUI $ilTabs */
		$ilTabs->clearSubTabs();

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('dcl_confirm_delete_records'));
		$record_ids = isset($_POST['record_ids']) ? $_POST['record_ids'] : array();
		$all_fields = $this->table_obj->getRecordFields();
		foreach ($record_ids as $record_id) {
			/** @var ilDclBaseRecordModel $record */
			$record = ilDclCache::getRecordCache($record_id);
			if ($record) {
				$record_data = "";
				foreach($all_fields as $key=>$field) {
					$field_record = ilDclFieldFactory::getRecordFieldInstance($field, $record);

					$record_representation = ilDclFieldFactory::getRecordRepresentationInstance($field_record);
					if($record_representation->getConfirmationHTML() !== false) {
						$record_data .= $field->getTitle().": ".$record_representation->getConfirmationHTML() ."<br />";
					}
				}
				$conf->addItem('record_ids[]', $record->getId(), $record_data);
			}
		}
		$conf->addHiddenItem('table_id', $this->table_id);
		$conf->setConfirm($this->lng->txt('dcl_delete_records'), 'deleteRecords');
		$conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');
		$tpl->setContent($conf->getHTML());
	}


	/**
	 * Delete multiple records
	 *
	 * @param array $record_ids
	 */
	public function deleteRecords(array $record_ids = array()) {
		$record_ids = count($record_ids) ? $record_ids : $_POST['record_ids'];
		$record_ids = (is_null($record_ids)) ? array() : $record_ids;

		// Invoke deletion
		$n_skipped = 0;
		foreach ($record_ids as $record_id) {
			/** @var ilDclBaseRecordModel $record */
			$record = ilDclCache::getRecordCache($record_id);
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
			ilUtil::sendSuccess(sprintf($this->lng->txt('dcl_deleted_records'), $n_deleted), true);
		}
		if ($n_skipped) {
			ilUtil::sendInfo(sprintf($this->lng->txt('dcl_skipped_delete_records'), $n_skipped), true);
		}
		$this->ctrl->redirect($this, 'listRecords');
	}


	/**
	 * @param ilDclBaseRecordModel $record
	 *
	 * @return bool
	 */
	private function recordBelongsToCollection(ilDclBaseRecordModel $record) {
		$table = $record->getTable();
		$obj_id = $this->parent_obj->object->getId();
		$obj_id_rec = $table->getCollectionObject()->getId();

		return $obj_id == $obj_id_rec;
	}


	/**
	 * Add subtabs
	 *
	 */
	protected function setSubTabs($active_id = 'mode') {
		global $ilTabs;

		/** @var ilTabsGUI $ilTabs */
		$this->ctrl->setParameter($this, 'mode', self::MODE_VIEW);
		$ilTabs->addSubTab('mode_1', $this->lng->txt('view'), $this->ctrl->getLinkTarget($this, 'listRecords'));
		$this->ctrl->clearParameters($this);
		
		if ($this->table_obj->hasPermissionToDeleteRecords((int)$_GET['ref_id'])) {
			$this->ctrl->setParameter($this, 'mode', self::MODE_MANAGE);
			$ilTabs->addSubTab('mode_2', $this->lng->txt('dcl_manage'), $this->ctrl->getLinkTarget($this, 'listRecords'));
			$this->ctrl->clearParameters($this);
		}

		if($active_id == 'mode') {
			$active_id = 'mode_' . $this->mode;
		}

		$ilTabs->setSubTabActive($active_id);

	}

	/**
	 * @return array
	 */
	protected function getAvailableTables() {
		if (ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id)) {
			$tables = $this->parent_obj->object->getTables();
		} else {
			$tables = $this->parent_obj->object->getVisibleTables();
		}
		$options = array();
		foreach ($tables as $table) {
			$options[$table->getId()] = $table->getTitle();
		}

		return $options;
	}


	/**
	 * @param int|null $table_id
	 * @param bool $export
	 *
	 * @return array
	 */
	protected function getRecordListTableGUI($table_id = null, $export = false) {
		$table_obj = $this->table_obj;
		if($table_id != null) {
			$table_obj = ilDclCache::getTableCache($table_id);
		}
		$list = new ilDclRecordListTableGUI($this, "listRecords", $table_obj, $this->mode);
		$list->setExternalSegmentation(true);
		$list->setExternalSorting(true);
		$list->determineLimit();
		$list->determineOffsetAndOrder();

		$limit = $export? null : $list->getLimit();
		$offset = $export? null : $list->getOffset();

		$data = $table_obj->getPartialRecords($list->getOrderField(), $list->getOrderDirection(), $limit, $offset, $list->getFilter());
		$records = $data['records'];
		$total = $data['total'];
		$list->setMaxCount($total);
		$list->setRecordData($records);

		return array( $list, $total );
	}
}

?>