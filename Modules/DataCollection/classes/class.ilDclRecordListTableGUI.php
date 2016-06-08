<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilDclRecordViewGUI.php');
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php');
require_once('./Services/Tracking/classes/class.ilLPStatus.php');
require_once('./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php');
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTablePropertiesStorage.php');
require_once('./Modules/DataCollection/classes/class.ilDclContentExporter.php');

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
class ilDclRecordListTableGUI extends ilTable2GUI {

	const EXPORT_EXCEL_ASYNC = 10;

	/**
	 * @var ilDclTable
	 */
	protected $table;
	/**
	 * @var ilDclBaseRecordModel[]
	 */
	protected $object_data;
	/**
	 * @var array
	 */
	protected $numeric_fields = array();
	/**
	 * @var array
	 */
	protected $filter = array();
	/**
	 * @var int
	 */
	protected $mode;



	/**
	 * @param ilDclRecordListGUI $a_parent_obj
	 * @param string                        $a_parent_cmd
	 * @param ilDclTable         $table
	 * @param int                           $mode
	 */
	public function  __construct(ilDclRecordListGUI $a_parent_obj, $a_parent_cmd, ilDclTable $table, $mode = ilDclRecordListGUI::MODE_VIEW) {
		global $lng, $ilCtrl;

		$identifier = 'dcl_rl' . $table->getId();
		$this->setPrefix($identifier);
		$this->setFormName($identifier);
		$this->setId($identifier);
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->table = $table;
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");
		$this->mode = $mode;

		// Setup columns and sorting columns
		if ($this->mode == ilDclRecordListGUI::MODE_MANAGE) {
			// Add checkbox columns
			$this->addColumn("", "", "1", true);
			$this->setSelectAllCheckbox("record_ids[]");
			$this->addMultiCommand("confirmDeleteRecords", $lng->txt('dcl_delete_records'));
		}

		if (ilDclRecordViewGUI::hasTableValidViewDefinition($this->table)) {
			$this->addColumn("", "_front", '15px');
		}

		$this->numeric_fields = array();
		foreach ($this->table->getVisibleFields() as $field) {
			$title = $field->getTitle();
			$sort_field = ($field->getRecordQuerySortObject() != null)? $field->getSortField() : '';

			if($field->hasNumericSorting()) {
				$this->numeric_fields[] = $title;
			}

			$this->addColumn($title, $sort_field);

			if ($field->hasProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
				$this->addColumn($lng->txt("dcl_status"), "_status_" . $field->getTitle());
			}
		}
		$this->addColumn($lng->txt("actions"), "", "30px");
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(true);
		$this->setShowTemplates(true);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderDirection($this->table->getDefaultSortFieldOrder());
		// Set a default sorting?
		$default_sort_title = 'id';
		if ($fieldId = $this->table->getDefaultSortField()) {
			if (ilDclStandardField::_isStandardField($fieldId)) {
				/** @var $stdField ilDclStandardField */
				foreach (ilDclStandardField::_getStandardFields($this->table->getId()) as $stdField) {
					if ($stdField->getId() == $fieldId) {
						$default_sort_title = $stdField->getTitle();
						break;
					}
				}
			} else {
				$default_sort_title = ilDclCache::getFieldCache($fieldId)->getTitle();
			}
			$this->setDefaultOrderField($default_sort_title);
		}

		if (($this->table->getExportEnabled() OR $this->table->hasPermissionToFields($this->parent_obj->parent_obj->object->getRefId()))) {
			$this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_EXCEL_ASYNC));
		}

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
		$this->initFilter();
		$this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
	}


	/**
	 * @description Return array of fields that are currently stored in the filter. Return empty array if no filtering is required.
	 *
	 * @return array
	 */
	public function getFilter() {
		return $this->filter;
	}


	public function setRecordData($data) {
		$this->object_data = $data;
		$this->buildData($data);
	}


	public function numericOrdering($field) {
		return in_array($field, $this->numeric_fields);
	}


	/**
	 * @description Parse data from record objects to an array that is then set to this table with ::setData()
	 */
	private function buildData() {
		global $ilCtrl, $lng;

		$data = array();
		foreach ($this->object_data as $record) {
			$record_data = array();
			$record_data["_front"] = NULL;
			$record_data['_record'] = $record;

			foreach ($this->table->getVisibleFields() as $field) {
				$title = $field->getTitle();
				$record_data[$title] = $record->getRecordFieldHTML($field->getId());

				// Additional column filled in ::fillRow() method, showing the learning progress
				if ($field->getProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
					$record_data["_status_" . $title] = $this->getStatus($record, $field);
				}

				if ($field->getId() == 'comments') {
					$record_data['n_comments'] = count($record->getComments());
				}
			}

			$ilCtrl->setParameterByClass("ildclfieldeditgui", "record_id", $record->getId());
			$ilCtrl->setParameterByClass("ildclrecordviewgui", "record_id", $record->getId());
			$ilCtrl->setParameterByClass("ildclrecordeditgui", "record_id", $record->getId());
			$ilCtrl->setParameterByClass("ildclrecordeditgui", "mode", $this->mode);

			if (ilDclRecordViewGUI::hasTableValidViewDefinition($this->table)) {
				$record_data["_front"] = $ilCtrl->getLinkTargetByClass("ildclrecordviewgui", 'renderRecord');
			}

			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($record->getId());
			$alist->setListTitle($lng->txt("actions"));

			if (ilDclRecordViewGUI::hasTableValidViewDefinition($this->table)) {
				$alist->addItem($lng->txt('view'), 'view', $ilCtrl->getLinkTargetByClass("ildclrecordviewgui", 'renderRecord'));
			}

			if ($record->hasPermissionToEdit($this->parent_obj->parent_obj->ref_id)) {
				$alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildclrecordeditgui", 'edit'));
			}

			if ($record->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id)) {
				$alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildclrecordeditgui", 'confirmDelete'));
			}

			if ($this->table->getPublicCommentsEnabled()) {
				$alist->addItem($lng->txt('dcl_comments'), 'comment', '', '', '', '', '', '', $this->getCommentsAjaxLink($record->getId()));
			}

			$record_data["_actions"] = $alist->getHTML();
			$data[] = $record_data;
		}
		$this->setData($data);
	}

	/**
	 * @param array $record_data
	 *
	 * @return bool|void
	 */
	public function fillRow($record_data) {
		global $ilUser, $ilAccess;
		$record_obj = $record_data['_record'];

		/**
		 * @var $record_obj ilDclBaseRecordModel
		 * @var $ilAccess   ilAccessHandler
		 */
		foreach ($this->table->getVisibleFields() as $field) {
			$title = $field->getTitle();
			$this->tpl->setCurrentBlock("field");
			$content = $record_data[$title];
			if ($content === false || $content === NULL) {
				$content = '';
			} // SW - This ensures to display also zeros in the table...

			//TODO: move to specific class
			switch ($field->getDatatypeId()) {
				case ilDclDatatype::INPUTFORMAT_NUMBER:
					$this->tpl->setVariable("ADDITIONAL_CLASS", 'text-right');
					break;
			}

			$this->tpl->setVariable("CONTENT", $content);
			$this->tpl->parseCurrentBlock();

			if ($field->getProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
				$this->tpl->setCurrentBlock("field");
				$this->tpl->setVariable("CONTENT", $record_data["_status_" . $title]);
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($record_data["_front"]) {
			$this->tpl->setCurrentBlock('view');
			$this->tpl->setVariable("VIEW_IMAGE_LINK", $record_data["_front"]);
			$this->tpl->setVariable("VIEW_IMAGE_SRC", ilUtil::img(ilUtil::getImagePath("enlarge.svg"), $this->lng->txt('dcl_display_record_alt')));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("ACTIONS", $record_data["_actions"]);

		if ($this->mode == ilDclRecordListGUI::MODE_MANAGE) {
			if ($record_obj->getOwner() == $ilUser->getId() || $ilAccess->checkAccess('write', '', $_GET['ref_id'])) {
				$this->tpl->setCurrentBlock('mode_manage');
				$this->tpl->setVariable('RECORD_ID', $record_obj->getId());
				$this->tpl->parseCurrentBlock();
			} else {
				$this->tpl->touchBlock('mode_manage_no_owner');
			}
		}

		return true;
	}


	/**
	 * @description This adds the collumn for status.
	 *
	 * @param ilDclBaseRecordModel $record
	 * @param ilDclBaseFieldModel  $field
	 *
	 * @return string
	 */
	protected function getStatus(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field) {
		$record_field = ilDclCache::getRecordFieldCache($record, $field);
		$return = "";
		if ($status = $record_field->getStatus()) {
			$return = "<img src='" . ilLearningProgressBaseGUI::_getImagePathForStatus($status->status) . "'>";
		}

		return $return;
	}


	public function initFilter() {
		foreach ($this->table->getFilterableFields() as $field) {
			$filter_value = ilDclCache::getFieldRepresentation($field)->addFilterInputFieldToTable($this);
			$this->applyFilter($field->getId(), $filter_value);
		}
	}

	public function applyFilter($field_id, $filter_value) {
		if($filter_value) {
			$this->filter["filter_" . $field_id] = $filter_value;
		}
	}


	/**
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function loadProperty($type) {
		global $ilUser;

		if ($ilUser instanceof ilObjUser AND $this->getId()) {
			$tab_prop = new ilTablePropertiesStorage();

			return $tab_prop->getProperty($this->getId(), $ilUser->getId(), $type);
		}
	}


	/**
	 * @description Get the ajax link for displaying the comments in the right panel (to be wrapped in an onclick attr)
	 *
	 * @param int $recordId Record-ID
	 *
	 * @return string
	 */
	protected function getCommentsAjaxLink($recordId) {
		$ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $_GET['ref_id'], 'dcl', $this->parent_obj->obj_id, 'dcl', $recordId);

		return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
	}
	

	/**
	 * Exports the table
	 *
	 * @param int         $format
	 * @param bool|false  $send
	 * @param null|string $filepath
	 *
	 * @return null|string
	 */
	public function exportData($format, $send = false, $filepath = null) {
		if ($this->dataExists()) {
			// #9640: sort
			/*if (!$this->getExternalSorting() && $this->enabled["sort"]) {
				$this->determineOffsetAndOrder(true);

				$this->row_data = ilUtil::sortArray($this->row_data, $this->getOrderField(), $this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
			}*/

			$exporter = new ilDclContentExporter($this->parent_obj->parent_obj->object->getRefId(), $this->table->getId(), $this->filter);
			$exporter->export(ilDclContentExporter::EXPORT_EXCEL, null, true);
		}
	}

}

?>