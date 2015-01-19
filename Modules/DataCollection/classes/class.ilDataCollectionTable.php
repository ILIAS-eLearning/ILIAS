<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/DataCollection/classes/class.ilDataCollectionStandardField.php';
include_once './Modules/DataCollection/classes/class.ilDataCollectionRecord.php';

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionTable {

	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var int
	 */
	protected $objId;
	/**
	 * @var ilObjDataCollection
	 */
	protected $obj;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var array ilDataCollectionField[]
	 */
	protected $fields;
	/**
	 * @var array ilDataCollectionStandardField[]
	 */
	protected $stdFields;
	/**
	 * @var array ilDataCollectionRecord[]
	 */
	protected $records;
	/**
	 * @var bool
	 */
	protected $is_visible;
	/**
	 * @var bool
	 */
	protected $add_perm;
	/**
	 * @var bool
	 */
	protected $edit_perm;
	/**
	 * @var bool
	 */
	protected $delete_perm;
	/**
	 * @var bool
	 */
	protected $edit_by_owner;
	/**
	 * @var bool
	 */
	protected $limited;
	/**
	 * @var string
	 */
	protected $limit_start;
	/**
	 * @var string
	 */
	protected $limit_end;
	/**
	 * @var bool
	 */
	protected $export_enabled;
	/**
	 * ID of the default sorting field. Can be a DB field (int) or a standard field (string)
	 *
	 * @var string
	 */
	protected $default_sort_field = 0;
	/**
	 * Default sort-order (asc|desc)
	 *
	 * @var string
	 */
	protected $default_sort_field_order = 'asc';
	/**
	 * Description for this table displayed above records
	 *
	 * @var string
	 */
	protected $description = '';
	/**
	 * True if users can add comments on each record of this table
	 *
	 * @var bool
	 */
	protected $public_comments = 0;
	/**
	 * True if user can only view his/her own entries in the table
	 *
	 * @var bool
	 */
	protected $view_own_records_perm = 0;


	/**
	 * @param int $a_id
	 */
	public function __construct($a_id = 0) {
		if ($a_id != 0) {
			$this->id = $a_id;
			$this->doRead();
		}
	}


	/**
	 * Read table
	 */
	public function doRead() {
		global $ilDB;

		$query = "SELECT * FROM il_dcl_table WHERE id = " . $ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setObjId($rec["obj_id"]);
		$this->setTitle($rec["title"]);
		$this->setAddPerm($rec["add_perm"]);
		$this->setEditPerm($rec["edit_perm"]);
		$this->setDeletePerm($rec["delete_perm"]);
		$this->setEditByOwner($rec["edit_by_owner"]);
		$this->setExportEnabled($rec["export_enabled"]);
		$this->setLimited($rec["limited"]);
		$this->setLimitStart($rec["limit_start"]);
		$this->setLimitEnd($rec["limit_end"]);
		$this->setIsVisible($rec["is_visible"]);
		$this->setDescription($rec['description']);
		$this->setDefaultSortField($rec['default_sort_field_id']);
		$this->setDefaultSortFieldOrder($rec['default_sort_field_order']);
		$this->setPublicCommentsEnabled($rec['public_comments']);
		$this->setViewOwnRecordsPerm($rec['view_own_records_perm']);
	}


	/**
	 * Delete table
	 * Attention this does not delete the maintable of it's the maintable of the collection.
	 * unlink the the maintable in the collections object to make this work.
	 *
	 * @param boolean $delete_main_table true to delete table anyway
	 */
	public function doDelete($delete_main_table = false) {
		global $ilDB;

		/** @var $ilDB ilDB */
		foreach ($this->getRecords() as $record) {
			$record->doDelete();
		}

		foreach ($this->getRecordFields() as $field) {
			$field->doDelete();
		}

		// SW: Fix #12794 und #11405
		// Problem is that when the DC object gets deleted, $this::getCollectionObject() tries to load the DC but it's not in the DB anymore
		// If $delete_main_table is true, avoid getting the collection object
		$exec_delete = false;
		if ($delete_main_table) {
			$exec_delete = true;
		}
		if (!$exec_delete && $this->getCollectionObject()->getMainTableId() != $this->getId()) {
			$exec_delete = true;
		}
		if ($exec_delete) {
			$query = "DELETE FROM il_dcl_table WHERE id = " . $ilDB->quote($this->getId(), "integer");
			$ilDB->manipulate($query);

			// Delete also view definitions
			$set = $ilDB->query('SELECT * FROM il_dcl_view WHERE table_id = ' . $ilDB->quote($this->getId(), 'integer'));
			$view_ids = array();
			while ($row = $ilDB->fetchObject($set)) {
				$view_ids[] = $row->id;
			}
			if (count($view_ids)) {
				$ilDB->manipulate("DELETE FROM il_dcl_viewdefinition WHERE view_id IN (" . implode(',', $view_ids) . ")");
			}
			$ilDB->manipulate("DELETE FROM il_dcl_view WHERE table_id = " . $ilDB->quote($this->getId(), 'integer'));
		}
	}


	/**
	 * @param bool $create_views
	 */
	public function doCreate($create_views = true) {
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_table");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_table (" . "id" . ", obj_id" . ", title" . ", add_perm" . ", edit_perm" . ", delete_perm" . ", edit_by_owner"
			. ", limited" . ", limit_start" . ", limit_end" . ", is_visible" . ", export_enabled" . ", default_sort_field_id"
			. ", default_sort_field_order" . ", description" . ", public_comments" . ", view_own_records_perm" . " ) VALUES ("
			. $ilDB->quote($this->getId(), "integer") . "," . $ilDB->quote($this->getObjId(), "integer") . ","
			. $ilDB->quote($this->getTitle(), "text") . "," . $ilDB->quote($this->getAddPerm() ? 1 : 0, "integer") . ","
			. $ilDB->quote($this->getEditPerm() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getDeletePerm() ? 1 : 0, "integer") . ","
			. $ilDB->quote($this->getEditByOwner() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getLimited() ? 1 : 0, "integer") . ","
			. $ilDB->quote($this->getLimitStart(), "timestamp") . "," . $ilDB->quote($this->getLimitEnd(), "timestamp") . ","
			. $ilDB->quote($this->getIsVisible() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getExportEnabled() ? 1 : 0, "integer") . ","
			. $ilDB->quote($this->getDefaultSortField(), "text") . "," . $ilDB->quote($this->getDefaultSortFieldOrder(), "text") . ","
			. $ilDB->quote($this->getDescription(), "text") . "," . $ilDB->quote($this->getPublicCommentsEnabled(), "integer") . ","
			. $ilDB->quote($this->getViewOwnRecordsPerm(), "integer") . ")";

		$ilDB->manipulate($query);

		if ($create_views) {
			//add view definition
			$view_id = $ilDB->nextId("il_dcl_view");
			$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (" . $ilDB->quote($view_id, "integer") . ", "
				. $ilDB->quote($this->id, "integer") . ", " . $ilDB->quote(ilDataCollectionField::VIEW_VIEW, "integer") . ", "
				. $ilDB->quote(1, "integer") . ")";
			$ilDB->manipulate($query);

			//add edit definition
			$view_id = $ilDB->nextId("il_dcl_view");
			$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (" . $ilDB->quote($view_id, "integer") . ", "
				. $ilDB->quote($this->id, "integer") . ", " . $ilDB->quote(ilDataCollectionField::EDIT_VIEW, "integer") . ", "
				. $ilDB->quote(1, "integer") . ")";
			$ilDB->manipulate($query);

			//add filter definition
			$view_id = $ilDB->nextId("il_dcl_view");
			$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (" . $ilDB->quote($view_id, "integer") . ", "
				. $ilDB->quote($this->id, "integer") . ", " . $ilDB->quote(ilDataCollectionField::FILTER_VIEW, "integer") . ", "
				. $ilDB->quote(1, "integer") . ")";
			$ilDB->manipulate($query);

			//add filter definition
			$view_id = $ilDB->nextId("il_dcl_view");
			$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (" . $ilDB->quote($view_id, "integer") . ", "
				. $ilDB->quote($this->id, "integer") . ", " . $ilDB->quote(ilDataCollectionField::EXPORTABLE_VIEW, "integer") . ", "
				. $ilDB->quote(1, "integer") . ")";
			$ilDB->manipulate($query);

			$this->buildOrderFields();
		}
	}


	/*
	 * doUpdate
	 */
	public function doUpdate() {
		global $ilDB;

		$ilDB->update("il_dcl_table", array(
			"obj_id" => array( "integer", $this->getObjId() ),
			"title" => array( "text", $this->getTitle() ),
			"add_perm" => array( "integer", $this->getAddPerm() ),
			"edit_perm" => array( "integer", $this->getEditPerm() ),
			"delete_perm" => array( "integer", $this->getDeletePerm() ),
			"edit_by_owner" => array( "integer", $this->getEditByOwner() ),
			"limited" => array( "integer", $this->getLimited() ),
			"limit_start" => array( "timestamp", $this->getLimitStart() ),
			"limit_end" => array( "timestamp", $this->getLimitEnd() ),
			"is_visible" => array( "integer", $this->getIsVisible() ? 1 : 0 ),
			"export_enabled" => array( "integer", $this->getExportEnabled() ? 1 : 0 ),
			"description" => array( "text", $this->getDescription() ),
			"default_sort_field_id" => array( "text", $this->getDefaultSortField() ),
			"default_sort_field_order" => array( "text", $this->getDefaultSortFieldOrder() ),
			"public_comments" => array( "integer", $this->getPublicCommentsEnabled() ? 1 : 0 ),
			"view_own_records_perm" => array( "integer", $this->getViewOwnRecordsPerm() ),
		), array(
			"id" => array( "integer", $this->getId() )
		));
	}


	/**
	 * Set table id
	 *
	 * @param int $a_id
	 */
	public function setId($a_id) {
		$this->id = $a_id;
	}


	/**
	 * Get table id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param $a_id
	 */
	public function setObjId($a_id) {
		$this->objId = $a_id;
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->objId;
	}


	/**
	 * @param $a_title
	 */
	public function setTitle($a_title) {
		$this->title = $a_title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @return ilObjDataCollection
	 */
	public function getCollectionObject() {
		$this->loadObj();

		return $this->obj;
	}


	protected function loadObj() {
		if ($this->obj == NULL) {
			$this->obj = new ilObjDataCollection($this->objId, false);
		}
	}


	/**
	 * @return ilDataCollectionRecord[]
	 */
	public function getRecords() {
		$this->loadRecords();

		return $this->records;
	}


	/**
	 * getRecordsByFilter
	 *
	 * @param $filter
	 * filter is of the form array("filter_{field_id}" => filter);
	 * For dates and integers this filter must be of the form array("from" => from, "to" => to).
	 * In case of dates from and to have to be ilDateTime objects
	 * in case of integers they have to be integers as well.
	 *
	 * @return ilDataCollectionRecord[]
	 */
	public function getRecordsByFilter(array $filter = array()) {
		$this->loadRecords();
		// Only pass records trough filter if there is filtering required #performance-improvements
		if (!count($filter)) {
			return $this->records;
		}
		$filtered = array();
		foreach ($this->getRecords() as $record) {
			if ($record->passThroughFilter($filter)) {
				$filtered[] = $record;
			}
		}

		return $filtered;
	}


	protected function loadRecords() {
		if ($this->records == NULL) {
			global $ilDB;

			$records = array();
			$query = "SELECT id FROM il_dcl_record WHERE table_id = " . $ilDB->quote($this->id, "integer");
			$set = $ilDB->query($query);

			while ($rec = $ilDB->fetchAssoc($set)) {
				$records[$rec['id']] = ilDataCollectionCache::getRecordCache($rec['id']);
			}

			$this->records = $records;
		}
	}

	//TODO: replace this method with DataCollection->getTables()
	/**
	 * @param $a_id
	 *
	 * @return array
	 */
	public function getAll($a_id) {
		global $ilDB;

		// build query
		$query = "SELECT * FROM il_dcl_table WHERE obj_id = " . $ilDB->quote($a_id, "integer");
		$set = $ilDB->query($query);

		$all = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$all[$rec['id']] = $rec;
		}

		return $all;
	}


	/**
	 * @param $field_id
	 */
	public function deleteField($field_id) {
		$field = ilDataCollectionCache::getFieldCache($field_id);
		$records = $this->getRecords();

		foreach ($records as $record) {
			$record->deleteField($field_id);
		}

		$field->doDelete();
	}


	/**
	 * @param $field_id
	 *
	 * @return ilDataCollectionField|null
	 */
	public function getField($field_id) {
		$fields = $this->getFields();
		$field = NULL;
		foreach ($fields as $field_1) {
			if ($field_1->getId() == $field_id) {
				$field = $field_1;
			}
		}

		return $field;
	}


	/**
	 * @return array
	 */
	public function getFieldIds() {
		return array_keys($this->getFields());
	}


	protected function loadFields() {
		if ($this->fields == NULL) {
			global $ilDB;

			$query = "SELECT DISTINCT field.* FROM il_dcl_field AS field
			          INNER JOIN il_dcl_view AS view ON view.table_id = field.table_id
			          INNER JOIN il_dcl_viewdefinition AS def ON def.view_id = view.id
			          WHERE field.table_id =" . $ilDB->quote($this->getId(), "integer") . "
			          ORDER BY def.field_order DESC";
			$fields = array();
			$set = $ilDB->query($query);

			while ($rec = $ilDB->fetchAssoc($set)) {
				$field = ilDataCollectionCache::buildFieldFromRecord($rec);
				$fields[$field->getId()] = $field;
			}
			$this->sortByOrder($fields);
			$this->fields = $fields;
		}
	}


	/**
	 * getNewOrder
	 *
	 * @return int returns the place where a new field should be placed.
	 */
	public function getNewOrder() {
		$fields = $this->getFields();
		$place = 0;
		foreach ($fields as $field) {
			if (!$field->isStandardField()) {
				$place = $field->getOrder() + 1;
			}
		}

		return $place;
	}


	/**
	 * Returns all fields of this table including the standard fields
	 *
	 * @return ilDataCollectionField[]
	 */
	public function getFields() {
		$this->loadFields();
		$this->stdFields = $this->getStandardFields();
		$fields = array_merge($this->fields, $this->stdFields);
		$this->sortByOrder($fields);

		return $fields;
	}


	/**
	 * Returns all fields of this table including the standard fields, wich are supported for formulas
	 *
	 * @return ilDataCollectionField[]
	 */
	public function getFieldsForFormula() {
		$unsupported = array(
			ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF,
			ilDataCollectionDatatype::INPUTFORMAT_FORMULA,
			ilDataCollectionDatatype::INPUTFORMAT_MOB,
			ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN,
			ilDataCollectionDatatype::INPUTFORMAT_REFERENCELIST,
			ilDataCollectionDatatype::INPUTFORMAT_REFERENCE,
			ilDataCollectionDatatype::INPUTFORMAT_FILE,
			ilDataCollectionDatatype::INPUTFORMAT_RATING,
		);

		$this->loadFields();
		$return = $this->getStandardFields();
		/**
		 * @var $field ilDataCollectionField
		 */
		foreach ($this->fields as $field) {
			if (!in_array($field->getDatatypeId(), $unsupported)) {
				$return[] = $field;
			}
		}

		return $return;
	}


	/**
	 * Returns the fields all datacollections have by default.
	 *
	 * @return ilDataCollectionStandardField[]
	 */
	public function getStandardFields() {
		if ($this->stdFields == NULL) {
			$this->stdFields = ilDataCollectionStandardField::_getStandardFields($this->id);
			// Don't return comments as field if this feature is not activated in the settings
			if (!$this->getPublicCommentsEnabled()) {
				/** @var $field ilDataCollectionStandardField */
				foreach ($this->stdFields as $k => $field) {
					if ($field->getId() == 'comments') {
						unset($this->stdFields[$k]);
						break;
					}
				}
			}
		}

		return $this->stdFields;
	}


	/**
	 * Returns all fields of this table which are NOT standard fields.
	 *
	 * @return ilDataCollectionField[]
	 */
	public function getRecordFields() {
		$this->loadFields();

		return $this->fields;
	}


	/**
	 * Returns all fields of this table who have set their visibility to true, including standard fields.
	 *
	 * @return ilDataCollectionField[]
	 */
	public function getVisibleFields() {
		$fields = $this->getFields();

		$visibleFields = array();

		foreach ($fields as $field) {
			if ($field->isVisible()) {
				$visibleFields[] = $field;
			}
		}

		return $visibleFields;
	}


	/**
	 * @return array
	 */
	public function getEditableFields() {
		$fields = $this->getRecordFields();
		$editableFields = array();

		foreach ($fields as $field) {
			if (!$field->getLocked()) {
				$editableFields[] = $field;
			}
		}

		return $editableFields;
	}


	/**
	 * getFilterableFields
	 * Returns all fields of this table who have set their filterable to true, including standard fields.
	 *
	 * @return ilDataCollectionField[]
	 */
	public function getFilterableFields() {
		$fields = $this->getFields();
		$filterableFields = array();

		foreach ($fields as $field) {
			if ($field->isFilterable()) {
				$filterableFields[] = $field;
			}
		}

		return $filterableFields;
	}


	/**
	 * Return all the fields that are marked as exportable
	 *
	 * @return array ilDataCollectionField
	 */
	public function getExportableFields() {
		$fields = $this->getFields();
		$exportableFields = array();
		foreach ($fields as $field) {
			if ($field->getExportable()) {
				$exportableFields[] = $field;
			}
		}

		return $exportableFields;
	}


	/**
	 * @param int $ref_id
	 *
	 * @return bool
	 */
	public function hasPermissionToFields($ref_id) {
		return ilObjDataCollectionAccess::_hasWriteAccess($ref_id);
	}


	/**
	 * @param int $ref_id
	 *
	 * @return bool
	 */
	public function hasPermissionToAddTable($ref_id) {
		return ilObjDataCollectionAccess::_hasWriteAccess($ref_id);
	}


	/**
	 * @param int $ref_id
	 *y
	 *
	 * @return bool
	 */
	public function hasPermissionToAddRecord($ref_id) {
		return ($this->getAddPerm() AND ilObjDataCollectionAccess::_hasReadAccess($ref_id) AND $this->checkLimit());
	}


	/**
	 * @param $ref_id int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be edited
	 *
	 * @return bool
	 */
	public function hasPermissionToEditRecord($ref_id, ilDataCollectionRecord $record) {
		return ($this->getEditPerm() AND ilObjDataCollectionAccess::_hasReadAccess($ref_id) AND $this->checkLimit())
		OR $this->checkEditByOwner($record);
	}


	/**
	 * @param $ref_id int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be deleted
	 *
	 * @return bool
	 */
	public function hasPermissionToDeleteRecord($ref_id, ilDataCollectionRecord $record) {
		return ($this->getDeletePerm() AND ilObjDataCollectionAccess::_hasReadAccess($ref_id) AND $this->checkLimit())
		OR $this->checkEditByOwner($record);
	}


	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public function hasPermissionToDeleteRecords($ref_id) {
		return ($this->getDeletePerm() AND ilObjDataCollectionAccess::_hasReadAccess($ref_id));
	}


	/**
	 * @param int $ref_id
	 * @param     $record ilDataCollectionRecord
	 *
	 * @return bool
	 */
	public function hasPermissionToViewRecord($ref_id, $record) {
		global $ilUser, $rbacreview;
		/** @var ilRbacReview $rbacreview */
		// Owner of the DataCollection object and ILIAS Administrators can view each record by default
		if ($this->getCollectionObject()->getOwner() == $ilUser->getId() || $rbacreview->isAssigned($ilUser->getId(), 2)) {
			return true;
		}
		if (ilObjDataCollectionAccess::_hasReadAccess($ref_id)) {
			// Check for view only own entries setting
			if ($this->getViewOwnRecordsPerm() && $ilUser->getId() != $record->getOwner()) {
				return false;
			}

			return true;
		}

		return false;
	}


	/**
	 * @param ilDataCollectionRecord $record
	 *
	 * @return bool
	 */
	protected function checkEditByOwner(ilDataCollectionRecord $record) {
		global $ilUser;

		if ($this->getEditByOwner() AND $ilUser->getId() != $record->getOwner()) {
			return false;
		}

		return true;
	}


	/**
	 * @return bool
	 */
	protected function checkLimit() {
		if ($this->getLimited()) {
			$now = new ilDateTime(time(), IL_CAL_UNIX);
			$from = new ilDateTime($this->getLimitStart(), IL_CAL_DATE);
			$to = new ilDateTime($this->getLimitEnd(), IL_CAL_DATE);

			if (!($from <= $now && $now <= $to)) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Update fields
	 */
	public function updateFields() {
		foreach ($this->getFields() as $field) {
			$field->doUpdate();
		}
	}


	/**
	 * sortFields
	 *
	 * @param $fields ilDataCollectionField[]
	 */
	public function sortFields(&$fields) {
		$this->sortByOrder($fields);
		//After sorting the array loses it's keys respectivly their keys are set form $field->id to 1,2,3... so we reset the keys.
		$named = array();
		foreach ($fields as $field) {
			$named[$field->getId()] = $field;
		}

		$fields = $named;
	}


	/**
	 *
	 * @param $array ilDataCollectionField[] the array to sort
	 */
	protected function sortByOrder(&$array) {
		usort($array, array( $this, "compareOrder" ));
	}


	/**
	 * buildOrderFields
	 * orders the fields.
	 */
	public function buildOrderFields() {
		$fields = $this->getFields();
		$this->sortByOrder($fields);
		$count = 10;
		$offset = 10;
		foreach ($fields as $field) {
			if (!is_null($field->getOrder())) {
				$field->setOrder($count);
				$count = $count + $offset;
				$field->doUpdate();
			}
		}
	}


	/**
	 * Get a field by title
	 *
	 * @param $title
	 *
	 * @return ilDataCollectionField
	 */
	public function getFieldByTitle($title) {
		$return = NULL;
		foreach ($this->getFields() as $field) {
			if ($field->getTitle() == $title) {
				$return = $field;
				break;
			}
		}

		return $return;
	}


	/**
	 * @param boolean $add_perm
	 */
	public function setAddPerm($add_perm) {
		$this->add_perm = $add_perm;
	}


	/**
	 * @return boolean
	 */
	public function getAddPerm() {
		return $this->add_perm;
	}


	/**
	 * @param boolean $delete_perm
	 */
	public function setDeletePerm($delete_perm) {
		$this->delete_perm = $delete_perm;
	}


	/**
	 * @return boolean
	 */
	public function getDeletePerm() {

		return $this->delete_perm;
	}


	/**
	 * @param boolean $edit_by_owner
	 */
	public function setEditByOwner($edit_by_owner) {
		$this->edit_by_owner = $edit_by_owner;
	}


	/**
	 * @return boolean
	 */
	public function getEditByOwner() {
		return $this->edit_by_owner;
	}


	/**
	 * @param boolean $edit_perm
	 */
	public function setEditPerm($edit_perm) {
		$this->edit_perm = $edit_perm;
	}


	/**
	 * @return boolean
	 */
	public function getEditPerm() {
		return $this->edit_perm;
	}


	/**
	 * @param boolean $limited
	 */
	public function setLimited($limited) {
		$this->limited = $limited;
	}


	/**
	 * @return boolean
	 */
	public function getLimited() {
		return $this->limited;
	}


	/**
	 * @param string $limit_end
	 */
	public function setLimitEnd($limit_end) {
		$this->limit_end = $limit_end;
	}


	/**
	 * @return string
	 */
	public function getLimitEnd() {
		return $this->limit_end;
	}


	/**
	 * @param string $limit_start
	 */
	public function setLimitStart($limit_start) {
		$this->limit_start = $limit_start;
	}


	/**
	 * @return string
	 */
	public function getLimitStart() {
		return $this->limit_start;
	}


	/**
	 * @param boolean $is_visible
	 */
	public function setIsVisible($is_visible) {
		$this->is_visible = $is_visible;
	}


	/**
	 * @return boolean
	 */
	public function getIsVisible() {
		return $this->is_visible;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 *
	 * /**
	 * @param string $default_sort_field
	 */
	public function setDefaultSortField($default_sort_field) {
		$default_sort_field = ($default_sort_field) ? $default_sort_field : 0; // Change null or empty strings to zero
		$this->default_sort_field = $default_sort_field;
	}


	/**
	 * @return string
	 */
	public function getDefaultSortField() {
		return $this->default_sort_field;
	}


	/**
	 * @param string $default_sort_field_order
	 */
	public function setDefaultSortFieldOrder($default_sort_field_order) {
		if (!in_array($default_sort_field_order, array( 'asc', 'desc' ))) {
			$default_sort_field_order = 'asc';
		}
		$this->default_sort_field_order = $default_sort_field_order;
	}


	/**
	 * @return string
	 */
	public function getDefaultSortFieldOrder() {
		return $this->default_sort_field_order;
	}


	/**
	 * @param boolean $public_comments
	 */
	public function setPublicCommentsEnabled($public_comments) {
		$this->public_comments = $public_comments;
	}


	/**
	 * @return boolean
	 */
	public function getPublicCommentsEnabled() {
		return $this->public_comments;
	}


	/**
	 * @param boolean $view_own_perm
	 */
	public function setViewOwnRecordsPerm($view_own_perm) {
		$this->view_own_records_perm = (int)$view_own_perm;
	}


	/**
	 * @return boolean
	 */
	public function getViewOwnRecordsPerm() {
		return (bool)$this->view_own_records_perm;
	}


	/**
	 * hasCustomFields
	 *
	 * @return boolean
	 */
	public function hasCustomFields() {
		$this->loadFields();

		return (count($this->fields) > 0) ? true : false;
	}


	function compareOrder($a, $b) {
		if (is_null($a->getOrder() == NULL) && is_null($b->getOrder() == NULL)) {
			return 0;
		}
		if (is_null($a->getOrder())) {
			return 1;
		}
		if (is_null($b->getOrder())) {
			return - 1;
		}

		return $a->getOrder() < $b->getOrder() ? - 1 : 1;
	}


	/**
	 * @param ilDataCollectionTable $original
	 */
	public function cloneStructure(ilDataCollectionTable $original) {
		$this->setTitle($original->getTitle());
		$this->setDescription($original->getDescription());
		$this->setIsVisible($original->getIsVisible());
		$this->setEditByOwner($original->getEditByOwner());
		$this->setAddPerm($original->getAddPerm());
		$this->setEditPerm($original->getEditPerm());
		$this->setDeletePerm($original->getDeletePerm());
		$this->setLimited($original->getLimited());
		$this->setLimitStart($original->getLimitStart());
		$this->setLimitEnd($original->getLimitEnd());
		$this->setViewOwnRecordsPerm($original->getViewOwnRecordsPerm());
		$this->setExportEnabled($original->getExportEnabled());
		$this->setPublicCommentsEnabled($original->getPublicCommentsEnabled());
		$this->setDefaultSortFieldOrder($original->getDefaultSortFieldOrder());

		$this->doCreate();
		// reset stdFields to get new for the created object

		$default_sort_field = 0;
		// Clone standard-fields
		$org_std_fields = $original->getStandardFields();
		foreach ($this->getStandardFields() as $element_key => $std_field) {
			$std_field->cloneStructure($org_std_fields[$element_key]);
			if ($std_field->getId() == $original->getDefaultSortField()) {
				$default_sort_field = $std_field->getId();
			}
		}

		// Clone fields
		$new_fields = array();
		foreach ($original->getFields() as $orig_field) {
			if (!$orig_field->isStandardField()) {
				$new_field = new ilDataCollectionField();
				$new_field->setTableId($this->getId());
				$new_field->cloneStructure($orig_field->getId());
				$new_fields[$orig_field->getId()] = $new_field;

				if ($orig_field->getId() == $original->getDefaultSortField()) {
					$default_sort_field = $new_field->getId();
				}
			}
		}

		$this->setDefaultSortField($default_sort_field);
		$this->doUpdate();

		//TODO: Find better way to copy data (include referenced data)
		// Clone Records with recordfields
		/*foreach($original->getRecords() as $orig_record){
			$new_record = new ilDataCollectionRecord();
			$new_record->setTableId($this->getId());
			$new_record->cloneStructure($orig_record->getId(), $new_fields);
		}*/

		if ($old_view_id = ilDataCollectionRecordViewViewdefinition::getIdByTableId($original->getId())) {
			$old_view = new ilDataCollectionRecordViewViewdefinition($old_view_id);
			$old_view->setTableId($original->getId());
			$viewdef = new ilDataCollectionRecordViewViewdefinition();
			$viewdef->setTableId($this->id);
			$viewdef->setXMLContent($old_view->getXMLContent(false));
			$viewdef->create();
		}
	}


	/**
	 * _hasRecords
	 *
	 * @return boolean
	 */
	public function _hasRecords() {
		return (count($this->getRecords()) > 0) ? true : false;
	}


	/**
	 * @param $field ilDataCollectionField add an already created field for eg. ordering.
	 */
	public function addField($field) {
		$this->fields[$field->getId()] = $field;
	}


	/**
	 * @param $table_id int
	 *
	 * @return bool returns true iff there exists a table with id $table_id
	 */
	public static function _tableExists($table_id) {
		global $ilDB;
		$query = "SELECT * FROM il_dcl_table WHERE id = " . $table_id;
		$result = $ilDB->query($query);

		return $result->numRows() != 0;
	}


	/**
	 * @param $title  Title of table
	 * @param $obj_id DataCollection object ID where the table belongs to
	 *
	 * @return int
	 */
	public static function _getTableIdByTitle($title, $obj_id) {
		global $ilDB;
		$result = $ilDB->query('SELECT id FROM il_dcl_table WHERE title = ' . $ilDB->quote($title, 'text') . ' AND obj_id = '
			. $ilDB->quote($obj_id, 'integer'));
		$id = 0;
		while ($rec = $ilDB->fetchAssoc($result)) {
			$id = $rec['id'];
		}

		return $id;
	}


	/**
	 * @param boolean $export_enabled
	 */
	public function setExportEnabled($export_enabled) {
		$this->export_enabled = $export_enabled;
	}


	/**
	 * @return boolean
	 */
	public function getExportEnabled() {
		return $this->export_enabled;
	}


	/**
	 * Checks if a table has a field with the given title
	 *
	 * @param $title  Title of field
	 * @param $obj_id Obj-ID of the table
	 *
	 * @return bool
	 */
	public static function _hasFieldByTitle($title, $obj_id) {
		global $ilDB;
		$result = $ilDB->query('SELECT * FROM il_dcl_field WHERE table_id = ' . $ilDB->quote($obj_id, 'integer') . ' AND title = '
			. $ilDB->quote($title, 'text'));

		return ($ilDB->numRows($result)) ? true : false;
	}


	/**
	 * Return only the needed subset of record objects for the table, according to sorting, paging and filters
	 *
	 * @param string $sort      Title of a field where the ilTable2GUI is sorted
	 * @param string $direction 'desc' or 'asc'
	 * @param int    $limit     Limit of records
	 * @param int    $offset    Offset from records
	 * @param array  $filter    Containing the filter values
	 *
	 * @return array Array with two keys: 'record' => Contains the record objects, 'total' => Number of total records (without slicing)
	 */
	public function getPartialRecords($sort, $direction, $limit, $offset, array $filter = array()) {
		global $ilDB, $ilUser, $rbacreview;

		$sort_field = ($sort) ? $this->getFieldByTitle($sort) : $this->getField('id');
		$direction = strtolower($direction);
		$direction = (in_array($direction, array( 'desc', 'asc' ))) ? $direction : 'asc';

		// Sorting by a status from an ILIAS Ref field. This column is added dynamically to the table, there is no field model
		$sort_by_status = false;
		if (substr($sort, 0, 8) == '_status_') {
			$sort_by_status = true;
			$sort_field = $this->getFieldByTitle(substr($sort, 8));
		}

		if (is_null($sort_field)) {
			$sort_field = $this->getField('id');
		}

		$id = $sort_field->getId();
		$stl = $sort_field->getStorageLocation();
		$select_str = '';
		$join_str = '';
		$where_additions = '';
		$has_nref = false;

		if ($sort_field->isStandardField()) {
			if ($id == 'owner' || $id == 'last_edit_by') {
				$join_str .= "LEFT JOIN usr_data AS sort_usr_data_{$id} ON (sort_usr_data_{$id}.usr_id = record.{$id})";
				$select_str .= " sort_usr_data_{$id}.login AS field_{$id},";
			} else {
				$select_str .= " record.{$id} AS field_{$id},";
			}
		} else {
			switch ($sort_field->getDatatypeId()) {
				case ilDataCollectionDatatype::INPUTFORMAT_RATING:
					$join_str .= "LEFT JOIN (SELECT AVG(sort_avg_rating.rating) AS avg_rating, sort_avg_rating.obj_id AS obj_id FROM il_rating as sort_avg_rating WHERE sort_avg_rating.sub_obj_id = {$sort_field->getId()} GROUP BY sort_avg_rating.obj_id) AS sort_avg_rating on sort_avg_rating.obj_id = record.id ";
					$select_str .= " sort_avg_rating.avg_rating AS field_{$id},";
					break;
				case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
					$join_str .=
						"LEFT JOIN il_dcl_record_field AS sort_record_field_{$id} ON (sort_record_field_{$id}.record_id = record.id AND sort_record_field_{$id}.field_id = "
						. $ilDB->quote($sort_field->getId(), 'integer') . ") ";
					$join_str .= "LEFT JOIN il_dcl_stloc{$stl}_value AS sort_stloc_{$id} ON (sort_stloc_{$id}.record_field_id = sort_record_field_{$id}.id) ";
					$join_str .= "LEFT JOIN object_reference AS sort_object_reference_{$id} ON (sort_object_reference_{$id}.ref_id = sort_stloc_{$id}.value AND sort_object_reference_{$id}.deleted IS NULL)";
					$join_str .= "LEFT JOIN object_data AS sort_object_data_{$id} ON (sort_object_data_{$id}.obj_id = sort_object_reference_{$id}.obj_id)";
					if ($sort_by_status) {
						global $ilUser;
						$join_str .= "LEFT JOIN ut_lp_marks AS ut ON (ut.obj_id = sort_object_data_{$id}.obj_id AND ut.usr_id = "
							. $ilDB->quote($ilUser->getId(), 'integer') . ") ";
					}
					$select_str .= (!$sort_by_status) ? " sort_object_data_{$id}.title AS field_{$id}," : " ut.status AS field_{$id}";
					break;
				case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				case ilDataCollectionDatatype::INPUTFORMAT_MOB:
					$join_str .=
						"LEFT JOIN il_dcl_record_field AS sort_record_field_{$id} ON (sort_record_field_{$id}.record_id = record.id AND sort_record_field_{$id}.field_id = "
						. $ilDB->quote($sort_field->getId(), 'integer') . ") ";
					$join_str .= "LEFT JOIN il_dcl_stloc{$stl}_value AS sort_stloc_{$id} ON (sort_stloc_{$id}.record_field_id = sort_record_field_{$id}.id) ";
					$join_str .= "LEFT JOIN object_data AS sort_object_data_{$id} ON (sort_object_data_{$id}.obj_id = sort_stloc_{$id}.value) ";
					$select_str .= " sort_object_data_{$id}.title AS field_{$id},";
					break;
				case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
					$prop = $sort_field->getPropertyvalues();
					$ref_field = ilDataCollectionCache::getFieldCache($sort_field->getFieldRef());
					$n_ref = $prop[ilDataCollectionField::PROPERTYID_N_REFERENCE];
					if ($n_ref) {
						$has_nref = true;
					}
					$select_str .= ($n_ref) ? " GROUP_CONCAT(stloc_{$id}_joined.value) AS field_{$id}" : "stloc_{$id}_joined.value AS field_{$id},";
					$join_str .=
						"LEFT JOIN il_dcl_record_field AS record_field_{$id} ON (record_field_{$id}.record_id = record.id AND record_field_{$id}.field_id = "
						. $ilDB->quote($sort_field->getId(), 'integer') . ") ";
					$join_str .= "LEFT JOIN il_dcl_stloc{$stl}_value AS stloc_{$id} ON (stloc_{$id}.record_field_id = record_field_{$id}.id) ";
					$join_str .=
						"LEFT JOIN il_dcl_record_field AS record_field_{$id}_joined ON (record_field_{$id}_joined.record_id = stloc_{$id}.value AND record_field_{$id}_joined.field_id = "
						. $ilDB->quote($ref_field->getId(), 'integer') . ") ";
					$join_str .= "LEFT JOIN il_dcl_stloc{$ref_field->getStorageLocation()}_value AS stloc_{$id}_joined ON (stloc_{$id}_joined.record_field_id = record_field_{$id}_joined.id) ";
					break;
				case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
					$select_str .= " sort_stloc_{$id}.value AS field_{$id},";
					$join_str .=
						"LEFT JOIN il_dcl_record_field AS sort_record_field_{$id} ON (sort_record_field_{$id}.record_id = record.id AND sort_record_field_{$id}.field_id = "
						. $ilDB->quote($sort_field->getId(), 'integer') . ") ";
					$join_str .= "LEFT JOIN il_dcl_stloc{$stl}_value AS sort_stloc_{$id} ON (sort_stloc_{$id}.record_field_id = sort_record_field_{$id}.id) ";
					break;
			}
		}

		if (count($filter)) {
			foreach ($filter as $key => $filter_value) {
				$filter_field_id = substr($key, 7);
				$filter_field = $this->getField($filter_field_id);
				switch ($filter_field->getDatatypeId()) {
					case ilDataCollectionDatatype::INPUTFORMAT_RATING:
						$join_str .=
							"INNER JOIN (SELECT AVG(avg_rating.rating) AS avg_rating, avg_rating.obj_id AS obj_id FROM il_rating as avg_rating WHERE avg_rating.sub_obj_id = {$filter_field_id} GROUP BY avg_rating.obj_id) AS avg_rating on avg_rating.avg_rating >= "
							. $ilDB->quote($filter_value, 'integer') . " AND avg_rating.obj_id = record.id ";
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
						$join_str .=
							"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
							. $ilDB->quote($filter_field_id, 'integer') . ") ";
						$join_str .= "INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id) ";
						$join_str .= "INNER JOIN object_reference AS filter_object_reference_{$filter_field_id} ON (filter_object_reference_{$filter_field_id}.ref_id = filter_stloc_{$filter_field_id}.value ) ";
						$join_str .=
							"INNER JOIN object_data AS filter_object_data_{$filter_field_id} ON (filter_object_data_{$filter_field_id}.obj_id = filter_object_reference_{$filter_field_id}.obj_id AND filter_object_data_{$filter_field_id}.title LIKE "
							. $ilDB->quote("%$filter_value%", 'text') . ") ";
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_MOB:
					case ilDataCollectionDatatype::INPUTFORMAT_FILE:
						$join_str .=
							"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
							. $ilDB->quote($filter_field_id, 'integer') . ") ";
						$join_str .= "INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id) ";
						$join_str .=
							"INNER JOIN object_data AS filter_object_data_{$filter_field_id} ON (filter_object_data_{$filter_field_id}.obj_id = filter_stloc_{$filter_field_id}.value AND filter_object_data_{$filter_field_id}.title LIKE "
							. $ilDB->quote("%$filter_value%", 'text') . ") ";
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
						$date_from = (isset($filter_value['from']) && is_object($filter_value['from'])) ? $filter_value['from'] : NULL;
						$date_to = (isset($filter_value['to']) && is_object($filter_value['to'])) ? $filter_value['to'] : NULL;
						if ($filter_field->isStandardField()) {
							if ($date_from) {
								$where_additions .= " AND (record.{$filter_field_id} >= " . $ilDB->quote($date_from, 'date') . ")";
							}
							if ($date_to) {
								$where_additions .= " AND (record.{$filter_field_id} <= " . $ilDB->quote($date_to, 'date') . ")";
							}
						} else {
							$join_str .=
								"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
								. $ilDB->quote($filter_field_id, 'integer') . ") ";
							$join_str .= "INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id ";
							if ($date_from) {
								$join_str .= "AND filter_stloc_{$filter_field_id}.value >= " . $ilDB->quote($date_from, 'date') . " ";
							}
							if ($date_to) {
								$join_str .= "AND filter_stloc_{$filter_field_id}.value <= " . $ilDB->quote($date_to, 'date') . " ";
							}
							$join_str .= ") ";
						}
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
						$from = (isset($filter_value['from'])) ? (int)$filter_value['from'] : NULL;
						$to = (isset($filter_value['to'])) ? (int)$filter_value['to'] : NULL;
						if ($filter_field->isStandardField()) {
							if (!is_null($from)) {
								$where_additions .= " AND record.{$filter_field_id} >= " . $ilDB->quote($from, 'integer');
							}
							if (!is_null($to)) {
								$where_additions .= " AND record.{$filter_field_id} <= " . $ilDB->quote($to, 'integer');
							}
						} else {
							$join_str .=
								"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
								. $ilDB->quote($filter_field_id, 'integer') . ") ";
							$join_str .= "INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id";
							if (!is_null($from)) {
								$join_str .= " AND filter_stloc_{$filter_field_id}.value >= " . $ilDB->quote($from, 'integer');
							}
							if (!is_null($to)) {
								$join_str .= " AND filter_stloc_{$filter_field_id}.value <= " . $ilDB->quote($to, 'integer');
							}
							$join_str .= ") ";
						}
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
						if ($filter_value == "checked") {
							$join_str .=
								"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
								. $ilDB->quote($filter_field_id, 'integer') . ") ";
							$join_str .= "INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id";
							$join_str .= " AND filter_stloc_{$filter_field_id}.value = " . $ilDB->quote(1, 'integer');
						} else {
							$join_str .=
								"INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
								. $ilDB->quote($filter_field_id, 'integer') . ") ";
							$join_str .= "LEFT JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id";
							$where_additions .= " AND (filter_stloc_{$filter_field_id}.value <> " . $ilDB->quote(1, 'integer')
								. " OR filter_stloc_{$filter_field_id}.value is NULL)";
						}
						$join_str .= " ) ";
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
						if ($filter_field->isStandardField()) {
							$join_str .=
								"INNER JOIN usr_data AS filter_usr_data_{$filter_field_id} ON (filter_usr_data_{$filter_field_id}.usr_id = record.{$filter_field_id} AND filter_usr_data_{$filter_field_id}.login LIKE "
								. $ilDB->quote("%$filter_value%", 'text') . ") ";
						} else {
							$join_str .=
								" INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
								. $ilDB->quote($filter_field_id, 'integer') . ") ";
							$join_str .=
								" INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id AND filter_stloc_{$filter_field_id}.value LIKE "
								. $ilDB->quote("%$filter_value%", 'text') . ") ";
						}
						break;
					case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
						$join_str .=
							" INNER JOIN il_dcl_record_field AS filter_record_field_{$filter_field_id} ON (filter_record_field_{$filter_field_id}.record_id = record.id AND filter_record_field_{$filter_field_id}.field_id = "
							. $ilDB->quote($filter_field_id, 'integer') . ") ";
						$prop = $filter_field->getPropertyvalues();
						$n_ref = $prop[ilDataCollectionField::PROPERTYID_N_REFERENCE];
						if ($n_ref) {
							$join_str .=
								" INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id AND filter_stloc_{$filter_field_id}.value LIKE "
								. $ilDB->quote("%$filter_value%", 'text') . ") ";
						} else {
							$join_str .=
								" INNER JOIN il_dcl_stloc{$filter_field->getStorageLocation()}_value AS filter_stloc_{$filter_field_id} ON (filter_stloc_{$filter_field_id}.record_field_id = filter_record_field_{$filter_field_id}.id AND filter_stloc_{$filter_field_id}.value = "
								. $ilDB->quote($filter_value, 'integer') . ") ";
						}
						break;
				}
			}
		}

		// Build the query string
		$sql = "SELECT DISTINCT record.id, record.owner, ";
		$sql .= rtrim($select_str, ',') . " FROM il_dcl_record AS record ";
		$sql .= $join_str;
		$sql .= " WHERE record.table_id = " . $ilDB->quote($this->getId(), 'integer') . $where_additions;
		if ($has_nref) {
			$sql .= " GROUP BY record.id";
		}
		$sql .= " ORDER BY field_{$id} {$direction}";
		$set = $ilDB->query($sql);
		$total_record_ids = array();
		// Save record-ids in session to enable prev/next links in detail view
		$_SESSION['dcl_record_ids'] = array();
		$is_allowed_to_view = ($this->getCollectionObject()->getOwner() == $ilUser->getId() || ($rbacreview->isAssigned($ilUser->getId(), 2)));
		while ($rec = $ilDB->fetchAssoc($set)) {
			// Quick check if the current user is allowed to view the record
			if (!$is_allowed_to_view && ($this->getViewOwnRecordsPerm() && $ilUser->getId() != $rec['owner'])) {
				continue;
			}
			$total_record_ids[] = $rec['id'];
			$_SESSION['dcl_record_ids'][] = $rec['id'];
		}
		// Now slice the array to load only the needed records in memory
		$record_ids = array_slice($total_record_ids, $offset, $limit);
		$records = array();
		foreach ($record_ids as $id) {
			$records[] = ilDataCollectionCache::getRecordCache($id);
		}

		return array( 'records' => $records, 'total' => count($total_record_ids) );
	}
}

?>