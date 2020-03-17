<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/DataCollection/classes/Fields/Base/class.ilDclStandardField.php';
include_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordModel.php';
include_once './Modules/DataCollection/classes/TableView/class.ilDclTableView.php';

/**
 * Class ilDclBaseFieldModel
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
class ilDclTable
{

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
     * @var array ilDclBaseFieldModel[]
     */
    protected $fields;
    /**
     * @var array ilDclStandardField[]
     */
    protected $stdFields;
    /**
     * @var array ilDclBaseRecordModel[]
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
    protected $delete_by_owner;
    /**
     * @var bool
     */
    protected $save_confirmation;
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
     * @var integer
     */
    protected $table_order;
    /**
     * @var bool
     */
    protected $import_enabled;
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
     * table fields and std fields combined
     *
     * @var null|array
     */
    protected $all_fields = null;


    /**
     * @param int $a_id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }


    /**
     * Read table
     */
    public function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

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
        $this->setImportEnabled($rec["import_enabled"]);
        $this->setLimited($rec["limited"]);
        $this->setLimitStart($rec["limit_start"]);
        $this->setLimitEnd($rec["limit_end"]);
        $this->setIsVisible($rec["is_visible"]);
        $this->setDescription($rec['description']);
        $this->setDefaultSortField($rec['default_sort_field_id']);
        $this->setDefaultSortFieldOrder($rec['default_sort_field_order']);
        $this->setPublicCommentsEnabled($rec['public_comments']);
        $this->setViewOwnRecordsPerm($rec['view_own_records_perm']);
        $this->setDeleteByOwner($rec['delete_by_owner']);
        $this->setSaveConfirmation($rec['save_confirmation']);
        $this->setOrder($rec['table_order']);
    }


    /**
     * Delete table
     * Attention this does not delete the maintable of it's the maintable of the collection.
     * unlink the the maintable in the collections object to make this work.
     *
     * @param boolean $delete_main_table true to delete table anyway
     */
    public function doDelete($delete_only_content = false, $omit_notification = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        /** @var $ilDB ilDB */
        foreach ($this->getRecords() as $record) {
            $record->doDelete($omit_notification);
        }

        foreach ($this->getRecordFields() as $field) {
            $field->doDelete();
        }

        //		// SW: Fix #12794 und #11405
        //		// Problem is that when the DC object gets deleted, $this::getCollectionObject() tries to load the DC but it's not in the DB anymore
        //		// If $delete_main_table is true, avoid getting the collection object
        //		$exec_delete = false;
        //		if ($delete_main_table) {
        //			$exec_delete = true;
        //		}
        //		if (!$exec_delete && $this->getCollectionObject()->getFirstVisibleTableId() != $this->getId()) {
        //			$exec_delete = true;
        //		}
        if (!$delete_only_content) {
            $query = "DELETE FROM il_dcl_table WHERE id = " . $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($query);
        }
    }


    /**
     * @param bool $create_views
     */
    public function doCreate($create_tablefield_setting = true, $create_standardview = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $id = $ilDB->nextId("il_dcl_table");
        $this->setId($id);
        $query = "INSERT INTO il_dcl_table (" . "id" . ", obj_id" . ", title" . ", add_perm" . ", edit_perm" . ", delete_perm" . ", edit_by_owner"
            . ", limited" . ", limit_start" . ", limit_end" . ", is_visible" . ", export_enabled" . ", import_enabled" . ", default_sort_field_id"
            . ", default_sort_field_order" . ", description" . ", public_comments" . ", view_own_records_perm"
            . ", delete_by_owner, save_confirmation , table_order ) VALUES (" . $ilDB->quote($this->getId(), "integer") . ","
            . $ilDB->quote($this->getObjId(), "integer") . "," . $ilDB->quote($this->getTitle(), "text") . ","
            . $ilDB->quote($this->getAddPerm() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getEditPerm() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getDeletePerm() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getEditByOwner() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getLimited() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getLimitStart(), "timestamp") . ","
            . $ilDB->quote($this->getLimitEnd(), "timestamp") . "," . $ilDB->quote($this->getIsVisible() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getExportEnabled() ? 1 : 0, "integer") . "," . $ilDB->quote($this->getImportEnabled() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getDefaultSortField(), "text") . "," . $ilDB->quote($this->getDefaultSortFieldOrder(), "text") . ","
            . $ilDB->quote($this->getDescription(), "text") . "," . $ilDB->quote($this->getPublicCommentsEnabled(), "integer") . ","
            . $ilDB->quote($this->getViewOwnRecordsPerm(), "integer") . "," . $ilDB->quote($this->getDeleteByOwner() ? 1 : 0, 'integer') . ","
            . $ilDB->quote($this->getSaveConfirmation() ? 1 : 0, 'integer') . "," . $ilDB->quote($this->getOrder(), 'integer') . ")";

        $ilDB->manipulate($query);

        if ($create_standardview) {
            //standard tableview
            ilDclTableView::createOrGetStandardView($this->id);
        }

        if ($create_tablefield_setting) {
            $this->buildOrderFields();
        }
    }


    /*
     * doUpdate
     */
    public function doUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            "il_dcl_table",
            array(
            "obj_id" => array("integer", $this->getObjId()),
            "title" => array("text", $this->getTitle()),
            "add_perm" => array("integer", (int) $this->getAddPerm()),
            "edit_perm" => array("integer", (int) $this->getEditPerm()),
            "delete_perm" => array("integer", (int) $this->getDeletePerm()),
            "edit_by_owner" => array("integer", (int) $this->getEditByOwner()),
            "limited" => array("integer", $this->getLimited()),
            "limit_start" => array("timestamp", $this->getLimitStart()),
            "limit_end" => array("timestamp", $this->getLimitEnd()),
            "is_visible" => array("integer", $this->getIsVisible() ? 1 : 0),
            "export_enabled" => array("integer", $this->getExportEnabled() ? 1 : 0),
            "import_enabled" => array("integer", $this->getImportEnabled() ? 1 : 0),
            "description" => array("text", $this->getDescription()),
            "default_sort_field_id" => array("text", $this->getDefaultSortField()),
            "default_sort_field_order" => array("text", $this->getDefaultSortFieldOrder()),
            "public_comments" => array("integer", $this->getPublicCommentsEnabled() ? 1 : 0),
            "view_own_records_perm" => array("integer", $this->getViewOwnRecordsPerm()),
            'delete_by_owner' => array('integer', $this->getDeleteByOwner() ? 1 : 0),
            'save_confirmation' => array('integer', $this->getSaveConfirmation() ? 1 : 0),
            'table_order' => array('integer', $this->getOrder()),
        ),
            array(
                "id" => array("integer", $this->getId()),
            )
        );
    }


    /**
     * Set table id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }


    /**
     * Get table id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param $a_id
     */
    public function setObjId($a_id)
    {
        $this->objId = $a_id;
    }


    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }


    /**
     * @param $a_title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @return ilObjDataCollection
     */
    public function getCollectionObject()
    {
        $this->loadObj();

        return $this->obj;
    }


    protected function loadObj()
    {
        if ($this->obj == null) {
            $this->obj = new ilObjDataCollection($this->objId, false);
        }
    }


    /**
     * @return ilDclBaseRecordModel[]
     */
    public function getRecords()
    {
        if ($this->records == null) {
            $this->loadRecords();
        }

        return $this->records;
    }


    public function loadRecords()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $records = array();
        $query = "SELECT id FROM il_dcl_record WHERE table_id = " . $ilDB->quote($this->id, "integer");
        $set = $ilDB->query($query);

        while ($rec = $ilDB->fetchAssoc($set)) {
            $records[$rec['id']] = ilDclCache::getRecordCache($rec['id']);
        }

        $this->records = $records;
    }


    /**
     * @param $field_id
     */
    public function deleteField($field_id)
    {
        $field = ilDclCache::getFieldCache($field_id);
        $records = $this->getRecords();

        foreach ($records as $record) {
            $record->deleteField($field_id);
        }

        $field->doDelete();
    }


    /**
     * @param $field_id
     *
     * @return ilDclBaseFieldModel|null
     */
    public function getField($field_id)
    {
        $fields = $this->getFields();
        $field = null;
        foreach ($fields as $field_1) {
            if ($field_1->getId() == $field_id) {
                $field = $field_1;
            }
        }

        return $field;
    }


    /**
     * @param bool $force_include_comments
     *
     * @return array
     */
    public function getFieldIds()
    {
        $field_ids = array();
        foreach ($this->getFields() as $field) {
            if ($field->getId()) {
                $field_ids[] = $field->getId();
            }
        }

        return $field_ids;
    }


    protected function loadCustomFields()
    {
        if (!$this->fields) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            /**
             * @var $ilDB ilDBInterface
             */
            $query
                = "SELECT DISTINCT il_dcl_field.*, il_dcl_tfield_set.field_order
						    FROM il_dcl_field
						         INNER JOIN il_dcl_tfield_set
						            ON (    il_dcl_tfield_set.field NOT IN ('owner',
						                                                    'last_update',
						                                                    'last_edit_by',
						                                                    'id',
						                                                    'create_date')
						                AND il_dcl_tfield_set.table_id = il_dcl_field.table_id
						                AND il_dcl_tfield_set.field = " . $ilDB->cast("il_dcl_field.id", "text") . ")
						   WHERE il_dcl_field.table_id = %s
						ORDER BY il_dcl_tfield_set.field_order ASC";

            $set = $ilDB->queryF($query, array('integer'), array((int) $this->getId()));
            $fields = array();
            while ($rec = $ilDB->fetchAssoc($set)) {
                $field = ilDclCache::buildFieldFromRecord($rec);
                $fields[] = $field;
            }
            $this->fields = $fields;

            ilDclCache::preloadFieldProperties($fields);
        }
    }


    public function getCustomFields()
    {
        if (!$this->fields) {
            $this->loadCustomFields();
        }

        return $this->fields;
    }


    /**
     * getNewOrder
     *
     * @return int returns the place where a new field should be placed.
     */
    public function getNewFieldOrder()
    {
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
     * @return int
     */
    public function getNewTableviewOrder()
    {
        return (ilDclTableView::getCountForTableId($this->getId()) + 1) * 10;
    }


    /**
     * @param ilDclTableView[] $tableviews
     */
    public function sortTableViews(array $tableviews = null)
    {
        if ($tableviews == null) {
            $tableviews = $this->getTableViews();
        }

        $order = 10;
        foreach ($tableviews as $tableview) {
            $tableview->setTableviewOrder($order);
            $tableview->update();
            $order += 10;
        }
    }


    /**
     * Returns all fields of this table including the standard fields
     *
     * @param bool $force_include_comments by default false, so comments will only load when enabled in tablesettings
     *
     * @return ilDclBaseFieldModel[]
     */
    public function getFields()
    {
        if ($this->all_fields == null) {
            $this->reloadFields();
        }

        return $this->all_fields;
    }


    public function reloadFields()
    {
        $this->loadCustomFields();
        $this->stdFields = $this->getStandardFields();
        $fields = array_merge($this->fields, $this->stdFields);

        $this->sortByOrder($fields);

        $this->all_fields = $fields;
    }


    /**
     * @return ilDclTableView[] all tableviews ordered by tableview_order
     */
    public function getTableViews()
    {
        return ilDclTableView::getAllForTableId($this->getId());
    }


    /**
     * For current user
     *
     * @param int $ref_id DataCollections reference
     * @param int $user_id
     *
     * @return ilDclTableView[]
     */
    public function getVisibleTableViews($ref_id, $with_active_detailedview = false, $user_id = 0)
    {
        if (ilObjDataCollectionAccess::hasWriteAccess($ref_id, $user_id) && !$with_active_detailedview) {
            return $this->getTableViews();
        }

        $visible_views = array();
        foreach ($this->getTableViews() as $tableView) {
            if (ilObjDataCollectionAccess::hasAccessToTableView($tableView, $user_id)) {
                if (!$with_active_detailedview || ilDclDetailedViewDefinition::isActive($tableView->getId())) {
                    $visible_views[] = $tableView;
                }
            }
        }

        return $visible_views;
    }


    /**
     * get id of first (for current user) available view
     *
     * @param     $ref_id
     * @param int $user_id
     *
     * @return bool
     */
    public function getFirstTableViewId($ref_id, $user_id = 0)
    {
        $tableview = array_shift($this->getVisibleTableViews($ref_id, false, $user_id));

        return $tableview ? $tableview->getId() : false;
    }


    /**
     * Returns all fields of this table including the standard fields, wich are supported for formulas
     *
     * @return ilDclBaseFieldModel[]
     */
    public function getFieldsForFormula()
    {
        $unsupported = array(
            ilDclDatatype::INPUTFORMAT_ILIAS_REF,
            ilDclDatatype::INPUTFORMAT_FORMULA,
            ilDclDatatype::INPUTFORMAT_MOB,
            ilDclDatatype::INPUTFORMAT_REFERENCELIST,
            ilDclDatatype::INPUTFORMAT_REFERENCE,
            ilDclDatatype::INPUTFORMAT_FILE,
            ilDclDatatype::INPUTFORMAT_RATING,
        );

        $this->loadCustomFields();
        $return = $this->getStandardFields();
        /**
         * @var $field ilDclBaseFieldModel
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
     * Comments are only included if active in this table
     *
     * @return ilDclStandardField[]
     */
    public function getStandardFields()
    {
        if ($this->stdFields == null) {
            $this->stdFields = ilDclStandardField::_getStandardFields($this->id);
            // Don't return comments as field if this feature is not activated in the settings
            if (!$this->getPublicCommentsEnabled()) {
                /** @var $field ilDclStandardField */
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
     * @return ilDclBaseFieldModel[]
     */
    public function getRecordFields()
    {
        $this->loadCustomFields();

        return $this->fields;
    }


    /**
     * @return array
     */
    public function getEditableFields()
    {
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
     * Return all the fields that are marked as exportable
     *
     * @return array ilDclBaseFieldModel
     */
    public function getExportableFields()
    {
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
     * @param $ref_id int the reference id of the current datacollection object
     * @param $record ilDclBaseRecordModel the record which will be edited
     *
     * @return bool
     */
    public function hasPermissionToEditRecord($ref_id, ilDclBaseRecordModel $record)
    {
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }
        if (ilObjDataCollectionAccess::hasWriteAccess($ref_id) || ilObjDataCollectionAccess::hasEditAccess($ref_id)) {
            return true;
        }
        if (!ilObjDataCollectionAccess::hasAddRecordAccess($ref_id)) {
            return false;
        }
        if (!$this->checkLimit()) {
            return false;
        }
        if ($this->getEditPerm() && !$this->getEditByOwner()) {
            return true;
        }
        if ($this->getEditByOwner()) {
            return $this->doesRecordBelongToUser($record);
        }

        return false;
    }


    /**
     * @param $ref_id int the reference id of the current datacollection object
     * @param $record ilDclBaseRecordModel the record which will be deleted
     *
     * @return bool
     */
    public function hasPermissionToDeleteRecord($ref_id, ilDclBaseRecordModel $record)
    {
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }
        if (ilObjDataCollectionAccess::hasWriteAccess($ref_id)) {
            return true;
        }
        if (!ilObjDataCollectionAccess::hasAddRecordAccess($ref_id)) {
            return false;
        }
        if (!$this->checkLimit()) {
            return false;
        }
        if ($this->getDeletePerm() && !$this->getDeleteByOwner()) {
            return true;
        }
        if ($this->getDeleteByOwner()) {
            return $this->doesRecordBelongToUser($record);
        }

        return false;
    }


    /**
     * @param $ref_id
     *
     * @return bool
     */
    public function hasPermissionToDeleteRecords($ref_id)
    {
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }
        return ((ilObjDataCollectionAccess::hasAddRecordAccess($ref_id) && $this->getDeletePerm())
            || ilObjDataCollectionAccess::hasWriteAccess($ref_id));
    }


    /**
     * @param int $ref_id
     * @param     $record ilDclBaseRecordModel
     * @param int $user_id
     *
     * @return bool
     */
    public function hasPermissionToViewRecord($ref_id, $record, $user_id = 0)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }
        if (ilObjDataCollectionAccess::hasWriteAccess($ref_id, $user_id) || ilObjDataCollectionAccess::hasEditAccess($ref_id, $user_id)) {
            return true;
        }
        if (ilObjDataCollectionAccess::hasReadAccess($ref_id)) {
            // Check for view only own entries setting
            if ($this->getViewOwnRecordsPerm() && ($user_id ? $user_id : $ilUser->getId()) != $record->getOwner()) {
                return false;
            }

            return true;
        }

        return false;
    }


    /**
     * @param ilDclBaseRecordModel $record
     *
     * @return bool
     */
    protected function doesRecordBelongToUser(ilDclBaseRecordModel $record)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        return ($ilUser->getId() == $record->getOwner());
    }


    /**
     * @return bool
     */
    public function checkLimit()
    {
        if ($this->getLimited()) {
            $now = new ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATE);
            $from = new ilDateTime($this->getLimitStart(), IL_CAL_DATE);
            $to = new ilDateTime($this->getLimitEnd(), IL_CAL_DATE);

            return ($from <= $now && $now <= $to);
        }

        return true;
    }


    /**
     * Update fields
     */
    public function updateFields()
    {
        foreach ($this->getFields() as $field) {
            $field->doUpdate();
        }
    }


    /**
     * sortFields
     *
     * @param $fields ilDclBaseFieldModel[]
     */
    public function sortFields(&$fields)
    {
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
     * @param $array ilDclBaseFieldModel[] the array to sort
     */
    protected function sortByOrder(&$array)
    {
        // php-bug: https://bugs.php.net/bug.php?id=50688
        // fixed in php 7 but for now we need the @ a workaround
        @usort($array, array($this, "compareOrder"));
    }


    /**
     * buildOrderFields
     * orders the fields.
     */
    public function buildOrderFields()
    {
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
     * @return ilDclBaseFieldModel
     */
    public function getFieldByTitle($title)
    {
        $return = null;
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
    public function setAddPerm($add_perm)
    {
        $this->add_perm = $add_perm;
    }


    /**
     * @return boolean
     */
    public function getAddPerm()
    {
        return (bool) $this->add_perm;
    }


    /**
     * @param boolean $delete_perm
     */
    public function setDeletePerm($delete_perm)
    {
        $this->delete_perm = $delete_perm;
        if (!$delete_perm) {
            $this->setDeleteByOwner(false);
        }
    }


    /**
     * @return boolean
     */
    public function getDeletePerm()
    {
        return (bool) $this->delete_perm;
    }


    /**
     * @param boolean $edit_by_owner
     */
    public function setEditByOwner($edit_by_owner)
    {
        $this->edit_by_owner = $edit_by_owner;
        if ($edit_by_owner) {
            $this->setEditPerm(true);
        }
    }


    /**
     * @return boolean
     */
    public function getEditByOwner()
    {
        return (bool) $this->edit_by_owner;
    }


    /**
     * @return boolean
     */
    public function getDeleteByOwner()
    {
        return (bool) $this->delete_by_owner;
    }


    /**
     * @param boolean $delete_by_owner
     */
    public function setDeleteByOwner($delete_by_owner)
    {
        $this->delete_by_owner = $delete_by_owner;
        if ($delete_by_owner) {
            $this->setDeletePerm(true);
        }
    }


    /**
     * @param boolean $edit_perm
     */
    public function setEditPerm($edit_perm)
    {
        $this->edit_perm = $edit_perm;
        if (!$edit_perm) {
            $this->setEditByOwner(false);
        }
    }


    /**
     * @return boolean
     */
    public function getEditPerm()
    {
        return (bool) $this->edit_perm;
    }


    /**
     * @param boolean $limited
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;
    }


    /**
     * @return boolean
     */
    public function getLimited()
    {
        return $this->limited;
    }


    /**
     * @param string $limit_end
     */
    public function setLimitEnd($limit_end)
    {
        $this->limit_end = $limit_end;
    }


    /**
     * @return string
     */
    public function getLimitEnd()
    {
        return $this->limit_end;
    }


    /**
     * @param string $limit_start
     */
    public function setLimitStart($limit_start)
    {
        $this->limit_start = $limit_start;
    }


    /**
     * @return string
     */
    public function getLimitStart()
    {
        return $this->limit_start;
    }


    /**
     * @param boolean $is_visible
     */
    public function setIsVisible($is_visible)
    {
        $this->is_visible = $is_visible;
    }


    /**
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     *
     * /**
     * @param string $default_sort_field
     */
    public function setDefaultSortField($default_sort_field)
    {
        $default_sort_field = ($default_sort_field) ? $default_sort_field : 0; // Change null or empty strings to zero
        $this->default_sort_field = $default_sort_field;
    }


    /**
     * @return string
     */
    public function getDefaultSortField()
    {
        return $this->default_sort_field;
    }


    /**
     * @param string $default_sort_field_order
     */
    public function setDefaultSortFieldOrder($default_sort_field_order)
    {
        if (!in_array($default_sort_field_order, array('asc', 'desc'))) {
            $default_sort_field_order = 'asc';
        }
        $this->default_sort_field_order = $default_sort_field_order;
    }


    /**
     * @return string
     */
    public function getDefaultSortFieldOrder()
    {
        return $this->default_sort_field_order;
    }


    /**
     * @param boolean $public_comments
     */
    public function setPublicCommentsEnabled($public_comments)
    {
        $this->public_comments = $public_comments;
    }


    /**
     * @return boolean
     */
    public function getPublicCommentsEnabled()
    {
        return $this->public_comments;
    }


    /**
     * @param boolean $view_own_perm
     */
    public function setViewOwnRecordsPerm($view_own_perm)
    {
        $this->view_own_records_perm = (int) $view_own_perm;
    }


    /**
     * @return boolean
     */
    public function getViewOwnRecordsPerm()
    {
        return (bool) $this->view_own_records_perm;
    }


    /**
     * @return boolean
     */
    public function getSaveConfirmation()
    {
        return $this->save_confirmation;
    }


    /**
     * @param boolean $save_confirmation
     */
    public function setSaveConfirmation($save_confirmation)
    {
        $this->save_confirmation = $save_confirmation;
    }


    /**
     * hasCustomFields
     *
     * @return boolean
     */
    public function hasCustomFields()
    {
        $this->loadCustomFields();

        return (count($this->fields) > 0) ? true : false;
    }


    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    public function compareOrder($a, $b)
    {
        if (is_null($a->getOrder() == null) && is_null($b->getOrder() == null)) {
            return 0;
        }
        if (is_null($a->getOrder())) {
            return 1;
        }
        if (is_null($b->getOrder())) {
            return -1;
        }

        return $a->getOrder() < $b->getOrder() ? -1 : 1;
    }


    /**
     * @param ilDclTable $original
     */
    public function cloneStructure(ilDclTable $original)
    {
        $this->setTitle($original->getTitle());
        $this->setDescription($original->getDescription());
        $this->setIsVisible($original->getIsVisible());
        $this->setEditByOwner($original->getEditByOwner());
        $this->setAddPerm($original->getAddPerm());
        $this->setEditPerm($original->getEditPerm());
        $this->setDeleteByOwner($original->getDeleteByOwner());
        $this->setSaveConfirmation($original->getSaveConfirmation());
        $this->setDeletePerm($original->getDeletePerm());
        $this->setLimited($original->getLimited());
        $this->setLimitStart($original->getLimitStart());
        $this->setLimitEnd($original->getLimitEnd());
        $this->setViewOwnRecordsPerm($original->getViewOwnRecordsPerm());
        $this->setExportEnabled($original->getExportEnabled());
        $this->setImportEnabled($original->getImportEnabled());
        $this->setPublicCommentsEnabled($original->getPublicCommentsEnabled());
        $this->setDefaultSortFieldOrder($original->getDefaultSortFieldOrder());
        $this->setOrder($original->getOrder());

        $this->doCreate(true, false);
        // reset stdFields to get new for the created object

        $default_sort_field = 0;
        // Clone standard-fields
        $org_std_fields = $original->getStandardFields();
        foreach ($this->getStandardFields() as $element_key => $std_field) {
            $std_field->cloneStructure($org_std_fields[$element_key]);
            if ($std_field->getId() === $original->getDefaultSortField()) {
                $default_sort_field = $std_field->getId();
            }
        }

        // Clone fields
        $new_fields = array();
        foreach ($original->getFields() as $orig_field) {
            if (!$orig_field->isStandardField()) {
                $class_name = get_class($orig_field);
                $new_field = new $class_name();
                $new_field->setTableId($this->getId());
                $new_field->cloneStructure($orig_field->getId());
                $new_fields[$orig_field->getId()] = $new_field;

                if ($orig_field->getId() === $original->getDefaultSortField()) {
                    $default_sort_field = $new_field->getId();
                }
            }
        }

        $this->setDefaultSortField($default_sort_field);
        $this->doUpdate();

        // Clone Records with recordfields
        foreach ($original->getRecords() as $orig_record) {
            $new_record = new ilDclBaseRecordModel();
            $new_record->setTableId($this->getId());
            $new_record->cloneStructure($orig_record->getId(), $new_fields);
        }

        //clone tableviews (includes pageobjects)
        foreach ($original->getTableViews() as $orig_tableview) {
            $new_tableview = new ilDclTableView();
            $new_tableview->setTableId($this->getId());
            $new_tableview->cloneStructure($orig_tableview, $new_fields);
        }

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($original->getId(), $this->getId(), ilDclCache::TYPE_TABLE);
    }


    /**
     *
     */
    public function afterClone()
    {
        foreach ($this->getFields() as $field) {
            $field->afterClone($this->getRecords());
        }
    }


    /**
     * _hasRecords
     *
     * @return boolean
     */
    public function _hasRecords()
    {
        return (count($this->getRecords()) > 0) ? true : false;
    }


    /**
     * @param $field ilDclBaseFieldModel add an already created field for eg. ordering.
     */
    public function addField($field)
    {
        $this->all_fields[$field->getId()] = $field;
    }


    /**
     * @param $table_id int
     *
     * @return bool returns true iff there exists a table with id $table_id
     */
    public static function _tableExists($table_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
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
    public static function _getTableIdByTitle($title, $obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            'SELECT id FROM il_dcl_table WHERE title = ' . $ilDB->quote($title, 'text') . ' AND obj_id = '
            . $ilDB->quote($obj_id, 'integer')
        );
        $id = 0;
        while ($rec = $ilDB->fetchAssoc($result)) {
            $id = $rec['id'];
        }

        return $id;
    }


    /**
     * @param boolean $export_enabled
     */
    public function setExportEnabled($export_enabled)
    {
        $this->export_enabled = $export_enabled;
    }


    /**
     * @return boolean
     */
    public function getExportEnabled()
    {
        return $this->export_enabled;
    }


    /**
     * @return int
     */
    public function getOrder()
    {
        if (!$this->table_order) {
            $this->updateOrder();
        }

        return $this->table_order;
    }


    /**
     *
     */
    public function updateOrder()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query('SELECT MAX(table_order) AS table_order FROM il_dcl_table WHERE obj_id = ' . $ilDB->quote($this->getCollectionObject()->getId(), 'integer'));
        $this->table_order = $ilDB->fetchObject($result)->table_order + 10;
        $ilDB->query('UPDATE il_dcl_table SET table_order = ' . $ilDB->quote($this->table_order, 'integer') . ' WHERE id = ' . $ilDB->quote($this->getId(), 'integer'));
    }


    /**
     * @param int $table_order
     */
    public function setOrder($table_order)
    {
        $this->table_order = $table_order;
    }


    /**
     * @param boolean $import_enabled
     */
    public function setImportEnabled($import_enabled)
    {
        $this->import_enabled = $import_enabled;
    }


    /**
     * @return boolean
     */
    public function getImportEnabled()
    {
        return $this->import_enabled;
    }


    /**
     * Checks if a table has a field with the given title
     *
     * @param $title  Title of field
     * @param $obj_id Obj-ID of the table
     *
     * @return bool
     */
    public static function _hasFieldByTitle($title, $obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            'SELECT * FROM il_dcl_field WHERE table_id = ' . $ilDB->quote($obj_id, 'integer') . ' AND title = '
            . $ilDB->quote($title, 'text')
        );

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
    public function getPartialRecords($sort, $direction, $limit, $offset, array $filter = array())
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /**
         * @var $ilDB ilDBInterface
         */
        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];

        $sort_field = ($sort) ? $this->getFieldByTitle($sort) : $this->getField('id');
        $direction = strtolower($direction);
        $direction = (in_array($direction, array('desc', 'asc'))) ? $direction : 'asc';

        // Sorting by a status from an ILIAS Ref field. This column is added dynamically to the table, there is no field model
        $sort_by_status = false;
        if (substr($sort, 0, 8) == '_status_') {
            $sort_by_status = true;
            $sort_field = $this->getFieldByTitle(substr($sort, 8));
        }

        if (is_null($sort_field)) {
            $sort_field = $this->getField('id');
        }

        $sort_query_object = $sort_field->getRecordQuerySortObject($direction, $sort_by_status);

        $select_str = ($sort_query_object != null) ? $sort_query_object->getSelectStatement() : '';
        $join_str = ($sort_query_object != null) ? $sort_query_object->getJoinStatement() : '';
        $where_str = ($sort_query_object != null) ? $sort_query_object->getWhereStatement() : '';
        $order_str = ($sort_query_object != null) ? $sort_query_object->getOrderStatement() : '';
        $group_str = ($sort_query_object != null) ? $sort_query_object->getGroupStatement() : '';

        if (count($filter)) {
            foreach ($filter as $key => $filter_value) {
                $filter_field_id = substr($key, 7);
                $filter_field = $this->getField($filter_field_id);
                $filter_record_query_object = $filter_field->getRecordQueryFilterObject($filter_value, $sort_field);

                if ($filter_record_query_object) {
                    $select_str .= $filter_record_query_object->getSelectStatement();
                    $join_str .= $filter_record_query_object->getJoinStatement();
                    $where_str .= $filter_record_query_object->getWhereStatement();
                    $group_str .= $filter_record_query_object->getGroupStatement();
                }
            }
        }

        // Build the query string
        $sql = "SELECT DISTINCT record.id, record.owner";
        if ($select_str) {
            $sql .= ', ';
        }

        $as = ' AS ';

        $sql .= rtrim($select_str, ',') . " FROM il_dcl_record {$as} record ";
        $sql .= $join_str;
        $sql .= " WHERE record.table_id = " . $ilDB->quote($this->getId(), 'integer');

        if (strlen($where_str) > 0) {
            $sql .= $where_str;
        }

        if (strlen($group_str) > 0) {
            $sql .= " GROUP BY " . $group_str;
        }

        if (strlen($order_str) > 0) {
            $sql .= " ORDER BY " . $order_str;
        }

        //var_dump($sql);
        /*global $DIC;
        /*$ilLog = $DIC['ilLog'];
        $ilLog->write($sql, ilLogLevel::CRITICAL);*/

        $set = $ilDB->query($sql);
        $total_record_ids = array();
        // Save record-ids in session to enable prev/next links in detail view
        $_SESSION['dcl_record_ids'] = array();
        $_SESSION['dcl_table_id'] = $this->getId();
        $ref = filter_input(INPUT_GET, 'ref_id');
        $is_allowed_to_view = (ilObjDataCollectionAccess::hasWriteAccess($ref) || ilObjDataCollectionAccess::hasEditAccess($ref));
        while ($rec = $ilDB->fetchAssoc($set)) {
            // Quick check if the current user is allowed to view the record
            if (!$is_allowed_to_view && ($this->getViewOwnRecordsPerm() && $ilUser->getId() != $rec['owner'])) {
                continue;
            }
            $total_record_ids[] = $rec['id'];
            $_SESSION['dcl_record_ids'][] = $rec['id'];
        }

        if ($sort_query_object != null) {
            $total_record_ids = $sort_query_object->applyCustomSorting($sort_field, $total_record_ids, $direction);
        }

        // Now slice the array to load only the needed records in memory
        $record_ids = array_slice($total_record_ids, $offset, $limit);

        $records = array();
        foreach ($record_ids as $id) {
            $records[] = ilDclCache::getRecordCache($id);
        }

        return array('records' => $records, 'total' => count($total_record_ids));
    }
}
