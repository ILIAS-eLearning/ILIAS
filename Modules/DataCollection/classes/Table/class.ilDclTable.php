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
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclTable
{
    protected int $id = 0;
    protected ?int $objId = null;
    protected ?ilObjDataCollection $obj = null;
    protected string $title = "";
    /**
     * @var ilDclBaseFieldModel[]
     */
    protected array $fields = [];
    /**
     * @var ilDclStandardField[]
     */
    protected array $stdFields = [];
    /**
     * @var ilDclBaseRecordModel[]
     */
    protected array $records = [];
    protected bool $is_visible = false;
    protected bool $add_perm = false;
    protected bool $edit_perm = false;
    protected bool $delete_perm = false;
    protected bool $edit_by_owner = false;
    protected bool $delete_by_owner = false;
    protected bool $save_confirmation = false;
    protected bool $limited = false;
    protected ?string $limit_start = null;
    protected ?string $limit_end = null;
    protected bool $export_enabled = false;
    protected int $table_order = 0;
    protected bool $import_enabled = false;
    /**
     * ID of the default sorting field. Can be a DB field (int) or a standard field (string)
     * @var int|string $default_sort_field
     */
    protected $default_sort_field = 0;
    /**
     * Default sort-order (asc|desc)
     */
    protected string $default_sort_field_order = 'asc';
    /**
     * Description for this table displayed above records
     */
    protected string $description = '';
    /**
     * True if users can add comments on each record of this table
     */
    protected int $public_comments = 0;
    /**
     * True if user can only view his/her own entries in the table
     */
    protected int $view_own_records_perm = 0;
    /**
     * table fields and std fields combined
     */
    protected ?array $all_fields = null;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }

    /**
     * Read table
     */
    public function doRead(): void
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
     */
    public function doDelete(bool $delete_only_content = false, bool $omit_notification = false): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        /** @var $ilDB ilDBInterface */
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

    public function doCreate(bool $create_tablefield_setting = true, bool $create_standardview = true): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $id = $ilDB->nextId("il_dcl_table");
        $this->setId($id);
        $query = "INSERT INTO il_dcl_table (" . "id" . ", obj_id" . ", title" . ", add_perm" . ", edit_perm" . ", delete_perm" . ", edit_by_owner"
            . ", limited" . ", limit_start" . ", limit_end" . ", is_visible" . ", export_enabled" . ", import_enabled" . ", default_sort_field_id"
            . ", default_sort_field_order" . ", description" . ", public_comments" . ", view_own_records_perm"
            . ", delete_by_owner, save_confirmation , table_order ) VALUES (" . $ilDB->quote(
                $this->getId(),
                "integer"
            ) . ","
            . $ilDB->quote($this->getObjId(), "integer") . "," . $ilDB->quote($this->getTitle(), "text") . ","
            . $ilDB->quote($this->getAddPerm() ? 1 : 0, "integer") . "," . $ilDB->quote(
                $this->getEditPerm() ? 1 : 0,
                "integer"
            ) . ","
            . $ilDB->quote(
                $this->getDeletePerm() ? 1 : 0,
                "integer"
            ) . "," . $ilDB->quote($this->getEditByOwner() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getLimited() ? 1 : 0, "integer") . "," . $ilDB->quote(
                $this->getLimitStart(),
                "timestamp"
            ) . ","
            . $ilDB->quote($this->getLimitEnd(), "timestamp") . "," . $ilDB->quote(
                $this->getIsVisible() ? 1 : 0,
                "integer"
            ) . ","
            . $ilDB->quote(
                $this->getExportEnabled() ? 1 : 0,
                "integer"
            ) . "," . $ilDB->quote($this->getImportEnabled() ? 1 : 0, "integer") . ","
            . $ilDB->quote($this->getDefaultSortField(), "text") . "," . $ilDB->quote(
                $this->getDefaultSortFieldOrder(),
                "text"
            ) . ","
            . $ilDB->quote($this->getDescription(), "text") . "," . $ilDB->quote(
                $this->getPublicCommentsEnabled(),
                "integer"
            ) . ","
            . $ilDB->quote(
                $this->getViewOwnRecordsPerm(),
                "integer"
            ) . "," . $ilDB->quote($this->getDeleteByOwner() ? 1 : 0, 'integer') . ","
            . $ilDB->quote($this->getSaveConfirmation() ? 1 : 0, 'integer') . "," . $ilDB->quote(
                $this->getOrder(),
                'integer'
            ) . ")";

        $ilDB->manipulate($query);

        if ($create_standardview) {
            //standard tableview
            ilDclTableView::createOrGetStandardView($this->id);
        }

        if ($create_tablefield_setting) {
            $this->buildOrderFields();
        }
    }

    public function doUpdate(): void
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
     */
    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    /**
     * Get table id
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setObjId(int $a_id): void
    {
        $this->objId = $a_id;
    }

    public function getObjId(): ?int
    {
        return $this->objId;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCollectionObject(): ilObjDataCollection
    {
        $this->loadObj();

        return $this->obj;
    }

    protected function loadObj(): void
    {
        if ($this->obj == null) {
            $this->obj = new ilObjDataCollection($this->objId, false);
        }
    }

    /**
     * @return ilDclBaseRecordModel[]
     */
    public function getRecords(): array
    {
        if ($this->records == null) {
            $this->loadRecords();
        }

        return $this->records;
    }

    public function loadRecords(): void
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

    public function deleteField(int $field_id): void
    {
        $field = ilDclCache::getFieldCache($field_id);
        $records = $this->getRecords();

        foreach ($records as $record) {
            $record->deleteField($field_id);
        }

        $field->doDelete();
    }

    public function getField(string $field_id): ?ilDclBaseFieldModel
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
     * @return int[]
     */
    public function getFieldIds(): array
    {
        $field_ids = array();
        foreach ($this->getFields() as $field) {
            if ($field->getId()) {
                $field_ids[] = $field->getId();
            }
        }

        return $field_ids;
    }

    protected function loadCustomFields(): void
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

            $set = $ilDB->queryF($query, array('integer'), array($this->getId()));
            $fields = array();
            while ($rec = $ilDB->fetchAssoc($set)) {
                $field = ilDclCache::buildFieldFromRecord($rec);
                $fields[] = $field;
            }
            $this->fields = $fields;

            ilDclCache::preloadFieldProperties($fields);
        }
    }

    /**
     * @return ilDclBaseFieldModel[]
     */
    public function getCustomFields(): array
    {
        if (!$this->fields) {
            $this->loadCustomFields();
        }

        return $this->fields;
    }

    /**
     * getNewOrder
     * @return int returns the place where a new field should be placed.
     */
    public function getNewFieldOrder(): int
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
    public function getNewTableviewOrder(): int
    {
        return (ilDclTableView::getCountForTableId($this->getId()) + 1) * 10;
    }

    /**
     * @param ilDclTableView[] $tableviews
     */
    public function sortTableViews(array $tableviews = null): void
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
     * @param bool $force_include_comments by default false, so comments will only load when enabled in tablesettings
     * @return ilDclBaseFieldModel[]
     */
    public function getFields(): array
    {
        if ($this->all_fields == null) {
            $this->reloadFields();
        }

        return $this->all_fields;
    }

    public function reloadFields(): void
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
    public function getTableViews(): array
    {
        return ilDclTableView::getAllForTableId($this->getId());
    }

    /**
     * For current user
     * @return ilDclTableView[]
     */
    public function getVisibleTableViews(int $ref_id, bool $with_active_detailedview = false, int $user_id = 0): array
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
     * @return bool|int|null
     */
    public function getFirstTableViewId(int $ref_id, int $user_id = 0)
    {
        $uid = $user_id;
        $array = $this->getVisibleTableViews($ref_id, false, $uid);
        $tableview = array_shift($array);

        return $tableview ? $tableview->getId() : false;
    }

    /**
     * Returns all fields of this table including the standard fields, wich are supported for formulas
     * @return ilDclBaseFieldModel[]
     */
    public function getFieldsForFormula(): array
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
     * @return ilDclStandardField[]
     */
    public function getStandardFields(): array
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
     * @return ilDclBaseFieldModel[]
     */
    public function getRecordFields(): array
    {
        $this->loadCustomFields();

        return $this->fields;
    }

    /**
     * @param bool $creation_mode
     * @return array
     */
    public function getEditableFields(bool $creation_mode): array
    {
        $fields = $this->getRecordFields();
        $editableFields = array();

        foreach ($fields as $field) {
            $tableview_id = $this->http->wrapper()->post()->retrieve(
                'tableview_id',
                $this->refinery->kindlyTo()->int()
            );
            if (!$field->getViewSetting($tableview_id)->isLocked($creation_mode)) {
                $editableFields[] = $field;
            }
        }

        return $editableFields;
    }

    /**
     * Return all the fields that are marked as exportable
     * @return ilDclBaseFieldModel[]
     */
    public function getExportableFields(): array
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
     * @return bool
     */
    public function hasPermissionToEditRecord(int $ref_id, ilDclBaseRecordModel $record): bool
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
     * @param int                  $ref_id the reference id of the current datacollection object
     * @param ilDclBaseRecordModel $record the record which will be deleted
     */
    public function hasPermissionToDeleteRecord(int $ref_id, ilDclBaseRecordModel $record): bool
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
     * @return bool
     */
    public function hasPermissionToDeleteRecords(int $ref_id): bool
    {
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }

        return ((ilObjDataCollectionAccess::hasAddRecordAccess($ref_id) && $this->getDeletePerm())
            || ilObjDataCollectionAccess::hasWriteAccess($ref_id));
    }

    public function hasPermissionToViewRecord(int $ref_id, ilDclBaseRecordModel $record, int $user_id = 0): bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        if ($this->getObjId() != ilObjDataCollection::_lookupObjectId($ref_id)) {
            return false;
        }
        if (ilObjDataCollectionAccess::hasWriteAccess(
            $ref_id,
            $user_id
        ) || ilObjDataCollectionAccess::hasEditAccess($ref_id, $user_id)) {
            return true;
        }
        if (ilObjDataCollectionAccess::hasReadAccess($ref_id)) {
            // Check for view only own entries setting
            if ($this->getViewOwnRecordsPerm() && ($user_id ?: $ilUser->getId()) != $record->getOwner()) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function doesRecordBelongToUser(ilDclBaseRecordModel $record): bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        return ($ilUser->getId() == $record->getOwner());
    }

    public function checkLimit(): bool
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
    public function updateFields(): void
    {
        foreach ($this->getFields() as $field) {
            $field->doUpdate();
        }
    }

    /**
     * sortFields
     * @param ilDclBaseFieldModel[] $fields
     */
    public function sortFields(array &$fields): void
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
     * @param ilDclBaseFieldModel[] $array the array to sort
     */
    protected function sortByOrder(array &$array): void
    {
        // php-bug: https://bugs.php.net/bug.php?id=50688
        // fixed in php 7 but for now we need the @ a workaround
        usort($array, array($this, "compareOrder"));
    }

    /**
     * buildOrderFields
     * orders the fields.
     */
    public function buildOrderFields(): void
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
     */
    public function getFieldByTitle(string $title): ilDclBaseFieldModel
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

    public function setAddPerm(bool $add_perm): void
    {
        $this->add_perm = $add_perm;
    }

    public function getAddPerm(): bool
    {
        return $this->add_perm;
    }

    public function setDeletePerm(bool $delete_perm): void
    {
        $this->delete_perm = $delete_perm;
        if (!$delete_perm) {
            $this->setDeleteByOwner(false);
        }
    }

    public function getDeletePerm(): bool
    {
        return $this->delete_perm;
    }

    public function setEditByOwner(bool $edit_by_owner): void
    {
        $this->edit_by_owner = $edit_by_owner;
        if ($edit_by_owner) {
            $this->setEditPerm(true);
        }
    }

    public function getEditByOwner(): bool
    {
        return $this->edit_by_owner;
    }

    public function getDeleteByOwner(): bool
    {
        return $this->delete_by_owner;
    }

    public function setDeleteByOwner(bool $delete_by_owner): void
    {
        $this->delete_by_owner = $delete_by_owner;
        if ($delete_by_owner) {
            $this->setDeletePerm(true);
        }
    }

    public function setEditPerm(bool $edit_perm): void
    {
        $this->edit_perm = $edit_perm;
        if (!$edit_perm) {
            $this->setEditByOwner(false);
        }
    }

    public function getEditPerm(): bool
    {
        return $this->edit_perm;
    }

    public function setLimited(bool $limited): void
    {
        $this->limited = $limited;
    }

    public function getLimited(): bool
    {
        return $this->limited;
    }

    public function setLimitEnd(?string $limit_end): void
    {
        $this->limit_end = $limit_end;
    }

    public function getLimitEnd(): ?string
    {
        return $this->limit_end;
    }

    public function setLimitStart(?string $limit_start): void
    {
        $this->limit_start = $limit_start;
    }

    public function getLimitStart(): ?string
    {
        return $this->limit_start;
    }

    public function setIsVisible(bool $is_visible): void
    {
        $this->is_visible = $is_visible;
    }

    public function getIsVisible(): bool
    {
        return $this->is_visible;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDefaultSortField(string $default_sort_field): void
    {
        $default_sort_field = ($default_sort_field) ?: 0; // Change null or empty strings to zero
        $this->default_sort_field = $default_sort_field;
    }

    public function getDefaultSortField(): string
    {
        return $this->default_sort_field;
    }

    public function setDefaultSortFieldOrder(string $default_sort_field_order): void
    {
        if (!in_array($default_sort_field_order, array('asc', 'desc'))) {
            $default_sort_field_order = 'asc';
        }
        $this->default_sort_field_order = $default_sort_field_order;
    }

    public function getDefaultSortFieldOrder(): string
    {
        return $this->default_sort_field_order;
    }

    public function setPublicCommentsEnabled(bool $public_comments): void
    {
        $this->public_comments = $public_comments;
    }

    public function getPublicCommentsEnabled(): bool
    {
        return $this->public_comments;
    }

    public function setViewOwnRecordsPerm(bool $view_own_perm): void
    {
        $this->view_own_records_perm = (int) $view_own_perm;
    }

    public function getViewOwnRecordsPerm(): bool
    {
        return (bool) $this->view_own_records_perm;
    }

    public function getSaveConfirmation(): bool
    {
        return $this->save_confirmation;
    }

    public function setSaveConfirmation(bool $save_confirmation): void
    {
        $this->save_confirmation = $save_confirmation;
    }

    /**
     * hasCustomFields
     * @return boolean
     */
    public function hasCustomFields(): bool
    {
        $this->loadCustomFields();

        return (count($this->fields) > 0) ? true : false;
    }

    public function compareOrder(ilDclBaseFieldModel $a, ilDclBaseFieldModel $b): int
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

    public function cloneStructure(ilDclTable $original): void
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
            $std_field->clone($org_std_fields[$element_key]);
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

    public function afterClone(): void
    {
        foreach ($this->getFields() as $field) {
            $field->afterClone($this->getRecords());
        }
    }

    /**
     * _hasRecords
     */
    public function _hasRecords(): bool
    {
        return (count($this->getRecords()) > 0) ? true : false;
    }

    /**
     * @param ilDclBaseFieldModel $field add an already created field for eg. ordering.
     */
    public function addField(ilDclBaseFieldModel $field): void
    {
        $this->all_fields[$field->getId()] = $field;
    }

    /**
     * @return bool returns true if there exists a table with id $table_id
     */
    public static function _tableExists(int $table_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM il_dcl_table WHERE id = " . $table_id;
        $result = $ilDB->query($query);

        return $result->numRows() != 0;
    }

    /**
     * @param int $obj_id Datacollection object ID where the table belongs to
     */
    public static function _getTableIdByTitle(string $title, int $obj_id): int
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

    public function setExportEnabled(bool $export_enabled): void
    {
        $this->export_enabled = $export_enabled;
    }

    public function getExportEnabled(): bool
    {
        return $this->export_enabled;
    }

    public function getOrder(): int
    {
        if (!$this->table_order) {
            $this->updateOrder();
        }

        return $this->table_order;
    }

    public function updateOrder(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query('SELECT MAX(table_order) AS table_order FROM il_dcl_table WHERE obj_id = ' . $ilDB->quote(
            $this->getCollectionObject()->getId(),
            'integer'
        ));
        $this->table_order = $ilDB->fetchObject($result)->table_order + 10;
        $ilDB->query('UPDATE il_dcl_table SET table_order = ' . $ilDB->quote(
            $this->table_order,
            'integer'
        ) . ' WHERE id = ' . $ilDB->quote($this->getId(), 'integer'));
    }

    public function setOrder(int $table_order): void
    {
        $this->table_order = $table_order;
    }

    public function setImportEnabled(bool $import_enabled): void
    {
        $this->import_enabled = $import_enabled;
    }

    public function getImportEnabled(): bool
    {
        return $this->import_enabled;
    }

    /**
     * Checks if a table has a field with the given title
     * @param string $title  Title of field
     * @param int    $obj_id of the table
     */
    public static function _hasFieldByTitle(string $title, int $obj_id): bool
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
     * @param string $sort      Title of a field where the ilTable2GUI is sorted
     * @param string $direction 'desc' or 'asc'
     * @param int    $limit     Limit of records
     * @param int    $offset    Offset from records
     * @param array  $filter    Containing the filter values
     * @return array Array with two keys: 'record' => Contains the record objects, 'total' => Number of total records (without slicing)
     */
    public function getPartialRecords(
        string $sort,
        string $direction,
        int $limit,
        int $offset,
        array $filter = array()
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /**
         * @var $ilDB ilDBInterface
         */
        $ilUser = $DIC['ilUser'];

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

        $set = $ilDB->query($sql);
        $total_record_ids = array();

        $ref = filter_input(INPUT_GET, 'ref_id');
        $is_allowed_to_view = (ilObjDataCollectionAccess::hasWriteAccess($ref) || ilObjDataCollectionAccess::hasEditAccess($ref));
        while ($rec = $ilDB->fetchAssoc($set)) {
            // Quick check if the current user is allowed to view the record
            if (!$is_allowed_to_view && ($this->getViewOwnRecordsPerm() && $ilUser->getId() != $rec['owner'])) {
                continue;
            }
            $total_record_ids[] = $rec['id'];
        }
        // Save record-ids in session to enable prev/next links in detail view
        ilSession::set('dcl_table_id', $this->getId());
        ilSession::set('dcl_record_ids', $total_record_ids);

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
