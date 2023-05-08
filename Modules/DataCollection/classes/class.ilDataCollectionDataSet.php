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
 * DataCollection dataset class
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionDataSet extends ilDataSet
{
    /**
     * Maps a given record_field ID (key) to the correct table where the value is stored (il_dcl_stloc(1|2|3)_value)
     * @var array
     */
    protected array $record_field_ids_2_storage = [];
    /**
     * Cache for all data of the entities (all related data for each mySQL table) for the DCL being exported
     * $caches = array(
     *  'il_dcl_table' => array(
     *      [1] => array('id' => 1, 'obj_id' => 222, 'title' => 'Table I'...),
     *      [2] => array('id' => 2, 'obj_id' => 222, 'title' => 'Table II'),
     *  ),
     *  'il_dcl_field' => array(
     *      [123] => array('id' => 123, 'table_id' => 1, 'title' => 'Field I'...),
     *      [124] => array('id' => 124, 'table_id' => 1, 'title' => 'Field II'...),
     *  ),
     * );
     * Get cache from a given entity:
     *  ilDataCollectionDataSet::getCache('il_dcl_table');
     * @var array
     */
    protected array $caches
        = [
            'dcl' => [],
            'il_dcl_table' => [],
            'il_dcl_field' => [],
            'il_dcl_field_prop' => [],
            'il_dcl_sel_opts' => [],
            'il_dcl_record' => [],
            'il_dcl_record_field' => [],
            'il_dcl_stloc1_value' => [],
            'il_dcl_stloc2_value' => [],
            'il_dcl_stloc3_value' => [],
            'il_dcl_stloc1_default' => [],
            'il_dcl_stloc2_default' => [],
            'il_dcl_stloc3_default' => [],
            'il_dcl_tfield_set' => [],
            'il_dcl_tableview' => [],
            'il_dcl_tview_set' => [],
        ];

    protected ilObjDataCollection $import_dc_object;

    /**
     * Caches ilDclBaseRecordFieldModel objects. Key = id, value = object
     */
    protected array $import_record_field_cache = [];
    protected ilObjUser $user;
    protected array $import_temp_refs = [];
    protected array $import_temp_refs_props = [];
    protected array $import_temp_new_mob_ids = [];

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
    }

    public function getSupportedVersions(): array
    {
        return ['4.5.0'];
    }

    /**
     * Get cached data from a given entity
     * @throws ilException
     */
    public function getCache(string $a_entity): array
    {
        if (!in_array($a_entity, array_keys($this->caches))) {
            throw new ilException("Entity '$a_entity' does not exist in Cache");
        }

        return $this->caches[$a_entity];
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return 'https://www.ilias.de/xml/Modules/DataCollection/' . $a_entity;
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case 'dcl':
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['id'])) {
                    $new_obj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $new_obj = new ilObjDataCollection();
                    $new_obj->create(true);
                }
                $new_obj->setTitle($a_rec['title']);
                $new_obj->setDescription($a_rec['description']);
                $new_obj->setApproval($a_rec['approval']);
                $new_obj->setPublicNotes($a_rec['public_notes']);
                $new_obj->setNotification($a_rec['notification']);
                $new_obj->setPublicNotes($a_rec['public_notes']);
                $new_obj->setOnline(false);
                $new_obj->setRating($a_rec['rating']);
                $new_obj->update(); //clone mode, so no table will be created
                $this->import_dc_object = $new_obj;
                $a_mapping->addMapping('Modules/DataCollection', 'dcl', $a_rec['id'], $new_obj->getId());
                break;
            case 'il_dcl_table':
                $table = new ilDclTable();
                $table->setTitle($a_rec['title']);
                $table->setObjId($this->import_dc_object->getId());
                $table->setDescription($a_rec['description']);
                $table->setAddPerm($a_rec['add_perm']);
                $table->setEditPerm($a_rec['edit_perm']);
                $table->setDeletePerm($a_rec['delete_perm']);
                $table->setEditByOwner($a_rec['edit_by_owner']);
                $table->setLimited($a_rec['limited']);
                $table->setLimitStart($a_rec['limit_start']);
                $table->setLimitEnd($a_rec['limit_end']);
                $table->setIsVisible($a_rec['is_visible']);
                $table->setExportEnabled($a_rec['export_enabled']);
                $table->setImportEnabled($a_rec['import_enabled']);
                $table->setDefaultSortField($a_rec['default_sort_field_id']);
                $table->setDefaultSortFieldOrder($a_rec['default_sort_field_order']);
                $table->setPublicCommentsEnabled($a_rec['public_comments']);
                $table->setViewOwnRecordsPerm($a_rec['view_own_records_perm']);
                $table->setDeleteByOwner($a_rec['delete_by_owner']);
                $table->setSaveConfirmation($a_rec['save_confirmation']);
                $table->setOrder($a_rec['table_order']);
                $table->doCreate(false, false); // false => Do not create views! They are imported later
                $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['id'], $table->getId());
                break;
            case 'il_dcl_tableview':
                $new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
                if ($new_table_id) {
                    $tableview = new ilDclTableView();
                    $tableview->setTitle($a_rec['title']);
                    $tableview->setTableId($new_table_id);
                    $tableview->setDescription($a_rec['description']);
                    $tableview->setTableviewOrder($a_rec['tableview_order']);
                    if (!is_array($a_rec['roles'])) {
                        $a_rec['roles'] = json_decode($a_rec['roles']);
                    }
                    $tableview->setRoles($a_rec['roles']);

                    $step_is_null = !array_key_exists('step_vs', $a_rec) || is_null($a_rec['step_vs']);
                    $step_is_null ? $tableview->setStepVs(1) : $tableview->setStepVs($a_rec['step_vs']);

                    $step_is_null = !array_key_exists('step_c', $a_rec) || is_null($a_rec['step_c']);
                    $step_is_null ? $tableview->setStepC(0) : $tableview->setStepC($a_rec['step_c']);

                    $step_is_null = !array_key_exists('step_e', $a_rec) || is_null($a_rec['step_e']);
                    $step_is_null ? $tableview->setStepE(0) : $tableview->setStepE($a_rec['step_e']);

                    $step_is_null = !array_key_exists('step_o', $a_rec) || is_null($a_rec['step_o']);
                    $step_is_null ? $tableview->setStepO(0) : $tableview->setStepO($a_rec['step_o']);

                    $step_is_null = !array_key_exists('step_s', $a_rec) || is_null($a_rec['step_s']);
                    $step_is_null ? $tableview->setStepS(0) : $tableview->setStepS($a_rec['step_s']);

                    $tableview->create(false);    //do not create default setting as they are imported too
                }
                $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_tableview', $a_rec['id'], $tableview->getId());
                $a_mapping->addMapping('Services/COPage', 'pg', 'dclf:' . $a_rec['id'], 'dclf:' . $tableview->getId());
                break;
            case 'il_dcl_field':
                $new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
                if ($new_table_id) {
                    $field = new ilDclBaseFieldModel();
                    $field->setTableId($new_table_id);
                    $field->setDatatypeId($a_rec['datatype_id']);
                    $field->setTitle($a_rec['title']);
                    $field->setDescription($a_rec['description']);
                    $field->setUnique($a_rec['is_unique']);
                    $field->doCreate();
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['id'], $field->getId());
                    // Check if this field was used as default order by, if so, update to new id
                    $table = ilDclCache::getTableCache($new_table_id);
                    if ($table && $table->getDefaultSortField() == $a_rec['id']) {
                        $table->setDefaultSortField($field->getId());
                        $table->doUpdate();
                    }
                }
                break;
            case 'il_dcl_tfield_set':
                $new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
                if ($new_table_id) {
                    $setting = ilDclTableFieldSetting::getInstance(
                        $new_table_id,
                        $new_field_id ?: $a_rec['field']
                    );
                    $setting->setFieldOrder($a_rec['field_order']);
                    $setting->setExportable($a_rec['exportable']);
                    $setting->store();
                }
                break;
            case 'il_dcl_tview_set':
                $new_tableview_id = $a_mapping->getMapping(
                    'Modules/DataCollection',
                    'il_dcl_tableview',
                    $a_rec['tableview_id']
                );
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
                if ($new_tableview_id) {
                    $setting = new ilDclTableViewFieldSetting();
                    $setting->setTableviewId($new_tableview_id);
                    $setting->setVisible($a_rec['visible']);
                    $setting->setField($new_field_id ?: $a_rec['field']);
                    $setting->setInFilter($a_rec['in_filter']);
                    $setting->setFilterValue($a_rec['filter_value'] ?: null);
                    $setting->setFilterChangeable($a_rec['filter_changeable']);
                    is_null($a_rec['required_create']) ? $setting->setRequiredCreate(0) : $setting->setRequiredCreate($a_rec['required_create']);
                    is_null($a_rec['locked_create']) ? $setting->setLockedCreate(0) : $setting->setLockedCreate($a_rec['locked_create']);
                    is_null($a_rec['visible_create']) ? $setting->setVisibleCreate(1) : $setting->setVisibleCreate($a_rec['visible_create']);
                    is_null($a_rec['visible_edit']) ? $setting->setVisibleEdit(1) : $setting->setVisibleEdit($a_rec['visible_edit']);
                    is_null($a_rec['required_edit']) ? $setting->setRequiredEdit(0) : $setting->setRequiredEdit($a_rec['required_edit']);
                    is_null($a_rec['locked_edit']) ? $setting->setLockedEdit(0) : $setting->setLockedEdit($a_rec['locked_edit']);
                    $setting->setDefaultValue($a_rec['default_value']);
                    $setting->create();
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_tview_set', $a_rec['id'], $setting->getId());
                }
                break;
            case 'il_dcl_record':
                $new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
                if ($new_table_id) {
                    $record = new ilDclBaseRecordModel();
                    $record->setTableId($new_table_id);
                    $datetime = new ilDateTime(time(), IL_CAL_UNIX);
                    $record->setCreateDate($datetime);
                    $record->setLastUpdate($datetime);
                    $record->setOwner($this->user->getId());
                    $record->setLastEditBy($this->user->getId());
                    $record->doCreate();
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_record', $a_rec['id'], $record->getId());
                }
                break;
            case 'il_dcl_view':
                $new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
                if ($new_table_id) {
                    //if import contains il_dcl_view, it must origin from an earlier ILIAS Version and therefore contains no tableviews
                    //->create standard view
                    $tableview = ilDclTableView::createOrGetStandardView($new_table_id);
                    if ($a_rec['type'] == 0 && $a_rec['formtype'] == 0) { //set page_object to tableview
                        // This mapping is needed for the import handled by Services/COPage
                        $a_mapping->addMapping(
                            'Services/COPage',
                            'pg',
                            'dclf:' . $a_rec['id'],
                            'dclf:' . $tableview->getId()
                        );
                        $a_mapping->addMapping(
                            'Modules/DataCollection',
                            'il_dcl_view',
                            $a_rec['id'],
                            $tableview->getId()
                        );
                    } else {
                        $a_mapping->addMapping(
                            'Modules/DataCollection',
                            'il_dcl_view',
                            $a_rec['id'],
                            json_encode(['type' => $a_rec['type'],
                                         'table_id' => $new_table_id,
                                         'tableview_id' => $tableview->getId()
                            ])
                        );
                    }
                }
                break;
            case 'il_dcl_viewdefinition':
                $map = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['view_id']);
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
                $field = ($new_field_id) ?: $a_rec['field'];
                switch ($map['type']) {
                    case 1: //visible
                        $viewfield_setting = ilDclTableViewFieldSetting::getInstance($map['tableview_id'], $field);
                        $viewfield_setting->setVisible($a_rec['is_set']);
                        $viewfield_setting->store();
                        break;
                    case 3: //in_filter
                        $viewfield_setting = ilDclTableViewFieldSetting::getInstance($map['tableview_id'], $field);
                        $viewfield_setting->setInFilter($a_rec['is_set']);
                        $viewfield_setting->store();
                        break;
                    case 4: //exportable
                        $tablefield_setting = ilDclTableFieldSetting::getInstance($map['table_id'], $field);
                        $tablefield_setting->setExportable($a_rec['is_set']);
                        $tablefield_setting->setFieldOrder($a_rec['field_order']);
                        $tablefield_setting->store();
                        break;
                }
                break;
            case 'il_dcl_sel_opts':
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field_id']);
                if ($new_field_id) {
                    $opt = new ilDclSelectionOption();
                    $opt->setFieldId($new_field_id);
                    $opt->setOptId($a_rec['opt_id']);
                    $opt->setSorting($a_rec['sorting']);
                    $opt->setValue($a_rec['value']);
                    $opt->store();
                }
                break;
            case 'il_dcl_field_prop':
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field_id']);
                if ($new_field_id) {
                    $prop = new ilDclFieldProperty();
                    $prop->setFieldId($new_field_id);

                    // OLD IMPORT! Backwards-compatibility
                    $name = $a_rec['name'];
                    if (!isset($name) && isset($a_rec['datatype_prop_id'])) {
                        $properties = [
                            1 => 'length',
                            2 => 'regex',
                            3 => 'table_id',
                            4 => 'url',
                            5 => 'text_area',
                            6 => 'reference_link',
                            7 => 'width',
                            8 => 'height',
                            9 => 'learning_progress',
                            10 => 'ILIAS_reference_link',
                            11 => 'multiple_selection',
                            12 => 'expression',
                            13 => 'display_action_menu',
                            14 => 'link_detail_page',
                            15 => 'link_detail_page',
                        ];

                        $name = $properties[$a_rec['datatype_prop_id']];
                    }

                    $prop->setName($name);
                    // For field references, we need to get the new field id of the referenced field
                    // If the field_id does not yet exist (e.g. referenced table not yet created), store temp info and fix before finishing import
                    $value = $a_rec['value'];
                    $refs = [ilDclBaseFieldModel::PROP_REFERENCE, ilDclBaseFieldModel::PROP_N_REFERENCE];

                    if (in_array($prop->getName(), $refs)) {
                        $new_field_id = $a_mapping->getMapping(
                            'Modules/DataCollection',
                            'il_dcl_field',
                            $a_rec['value']
                        );
                        if ($new_field_id === false) {
                            $value = null;
                        } else {
                            $value = $new_field_id;
                        }
                    }
                    $prop->setValue($value);
                    $prop->save();
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_field_prop', $a_rec['id'], $prop->getId());
                    $this->import_temp_refs_props[$prop->getId()] = $a_rec['value'];
                }
                break;
            case 'il_dcl_record_field':
                $record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $a_rec['record_id']);
                $field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field_id']);
                if ($record_id && $field_id) {
                    $record = ilDclCache::getRecordCache($record_id);
                    $field = ilDclCache::getFieldCache($field_id);
                    $record_field = new ilDclBaseRecordFieldModel($record, $field);
                    $a_mapping->addMapping(
                        'Modules/DataCollection',
                        'il_dcl_record_field',
                        $a_rec['id'],
                        $record_field->getId()
                    );
                    $this->import_record_field_cache[$record_field->getId()] = $record_field;
                }
                break;
            case 'il_dcl_stloc1_value':
            case 'il_dcl_stloc2_value':
            case 'il_dcl_stloc3_value':
                $new_record_field_id = $a_mapping->getMapping(
                    'Modules/DataCollection',
                    'il_dcl_record_field',
                    $a_rec['record_field_id']
                );
                if ($new_record_field_id) {
                    /** @var ilDclBaseRecordFieldModel $record_field */
                    $record_field = $this->import_record_field_cache[$new_record_field_id];
                    if (is_object($record_field)) {
                        // Need to rewrite internal references and lookup new objects if MOB or File
                        // For some fieldtypes it's better to reset the value, e.g. ILIAS_REF
                        switch ($record_field->getField()->getDatatypeId()) {
                            case ilDclDatatype::INPUTFORMAT_MOB:
                                // Check if we got a mapping from old object
                                $new_mob_id = $a_mapping->getMapping('Services/MediaObjects', 'mob', $a_rec['value']);
                                $value = ($new_mob_id) ? (int) $new_mob_id : null;
                                $this->import_temp_new_mob_ids[] = $new_mob_id;
                                break;
                            case ilDclDatatype::INPUTFORMAT_FILE:
                                $new_file_id = $a_mapping->getMapping('Modules/File', 'file', $a_rec['value']);
                                $value = ($new_file_id) ? (int) $new_file_id : null;
                                break;
                            case ilDclDatatype::INPUTFORMAT_REFERENCE:
                            case ilDclDatatype::INPUTFORMAT_REFERENCELIST:
                                // If we are referencing to a record from a table that is not yet created, return value is always false because the record does exist neither
                                // Solution: Temporary store all references and fix them before finishing the import.
                                $new_record_id = $a_mapping->getMapping(
                                    'Modules/DataCollection',
                                    'il_dcl_record',
                                    $a_rec['value']
                                );
                                $this->import_temp_refs[$new_record_field_id] = $a_rec['value'];
                                $value = ($new_record_id) ? (int) $new_record_id : null;
                                break;
                            case ilDclDatatype::INPUTFORMAT_ILIAS_REF:
                                $value = null;
                                break;
                            case ilDclDatatype::INPUTFORMAT_DATETIME:
                                $value = $a_rec['value'];
                                if ($value=='0000-00-00 00:00:00') {
                                    $value = null;
                                }
                                break;
                            default:
                                $value = $a_rec['value'];
                                if ($a_entity == 'il_dcl_stloc3_value' && empty($value)) {
                                    $value = null;
                                }
                        }
                        $record_field->setValue($value, true);
                        $record_field->doUpdate();
                    }
                }
                break;
            case 'il_dcl_stloc1_default':
            case 'il_dcl_stloc2_default':
            case 'il_dcl_stloc3_default':

                $tview_set_id = $a_mapping->getMapping(
                    'Modules/DataCollection',
                    'il_dcl_tview_set',
                    $a_rec['tview_set_id']
                );

                if ($tview_set_id) {
                    $value = $a_rec['value'];
                    if ($value) {
                        $stloc_default = (new ilDclDefaultValueFactory())->createByTableName($a_entity);
                        if ($a_entity == ilDclTableViewNumberDefaultValue::returnDbTableName()) {
                            $value = (int)$value;
                        }
                        $stloc_default->setValue($value);
                        $stloc_default->setTviewSetId($tview_set_id);
                        $stloc_default->create();
                    }
                }
                break;
        }
    }

    /**
     * Called before finishing import. Fix references inside DataCollections
     * @param ilImportMapping $a_mapping
     */
    public function beforeFinishImport(ilImportMapping $a_mapping): void
    {
        foreach ($this->import_temp_new_mob_ids as $new_mob_id) {
            if ($new_mob_id) {
                ilObjMediaObject::_saveUsage($new_mob_id, "dcl:html", $a_mapping->getTargetId());
            }
        }
        foreach ($this->import_temp_refs as $record_field_id => $old_record_id) {
            $new_record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $old_record_id);
            $value = ($new_record_id) ? (int) $new_record_id : null;
            /** @var ilDclBaseRecordFieldModel $record_field */
            $record_field = $this->import_record_field_cache[$record_field_id];
            $record_field->setValue($value, true);
            $record_field->doUpdate();
        }
        foreach ($this->import_temp_refs_props as $field_prop_id => $prop_value) {
            $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $prop_value);
            $value = ($new_field_id) ? (int) $new_field_id : $prop_value;

            $field_prop = new ilDclFieldProperty($field_prop_id);
            $field_prop->setValue($value);
            $field_prop->update();
        }
    }

    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        switch ($a_entity) {
            case 'dcl':
                return [
                    "id" => "integer",
                    "title" => "text",
                    "description" => "text",
                    'is_online' => 'integer',
                    'rating' => 'integer',
                    'public_notes' => 'integer',
                    'approval' => 'integer',
                    'notification' => 'integer',
                ];
            case 'il_dcl_table':
                return [
                    'id' => 'integer',
                    'obj_id' => 'integer',
                    'title' => 'text',
                    'add_perm' => 'integer',
                    'edit_perm' => 'integer',
                    'delete_perm' => 'integer',
                    'edit_by_owner' => 'integer',
                    'limited' => 'integer',
                    'limit_start' => 'text',
                    'limit_end' => 'text',
                    'is_visible' => 'integer',
                    'export_enabled' => 'integer',
                    'import_enabled' => 'integer',
                    'default_sort_field_id' => 'text',
                    'default_sort_field_order' => 'text',
                    'description' => 'text',
                    'public_comments' => 'integer',
                    'view_own_records_perm' => 'integer',
                    'delete_by_owner' => 'integer',
                    'save_confirmation' => 'integer',
                    'table_order' => 'integer',
                ];
            case 'il_dcl_tableview':
                return [
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'title' => 'text',
                    'roles' => 'text',
                    'step_vs' => 'integer',
                    'step_c' => 'integer',
                    'step_e' => 'integer',
                    'step_o' => 'integer',
                    'step_s' => 'integer',
                    'description' => 'text',
                    'tableview_order' => 'integer',
                ];
            case 'il_dcl_field':
                return [
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'title' => 'text',
                    'description' => 'text',
                    'datatype_id' => 'integer',
                    'is_unique' => 'integer',
                ];
            case 'il_dcl_tview_set':
                return [
                    'id' => 'integer',
                    'tableview_id' => 'integer',
                    'field' => 'text',
                    'visible' => 'integer',
                    'in_filter' => 'integer',
                    'filter_value' => 'text',
                    'filter_changeable' => 'integer',
                    'required_create' => 'integer',
                    'required_edit' => 'integer',
                    'locked_create' => 'integer',
                    'locked_edit' => 'integer',
                    'visible_create' => 'integer',
                    'visible_edit' => 'integer',
                    'default_value' => 'text',
                ];
            case 'il_dcl_tfield_set':
                return [
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'field' => 'text',
                    'field_order' => 'integer',
                    'exportable' => 'integer',
                ];
            case 'il_dcl_field_prop':
                return [
                    'id' => 'integer',
                    'field_id' => 'integer',
                    'name' => 'text',
                    'value' => 'integer',
                ];
            case 'il_dcl_sel_opts':
                return [
                    'id' => 'integer',
                    'field_id' => 'integer',
                    'opt_id' => 'integer',
                    'sorting' => 'integer',
                    'value' => 'text',
                ];
            case 'il_dcl_record':
                return [
                    'id' => 'integer',
                    'table_id' => 'integer',
                ];
            case 'il_dcl_record_field':
                return [
                    'id' => 'integer',
                    'record_id' => 'integer',
                    'field_id' => 'integer',
                ];
            case 'il_dcl_stloc1_value':
            case 'il_dcl_stloc2_value':
            case 'il_dcl_stloc3_value':
                return [
                    'id' => 'integer',
                    'record_field_id' => 'integer',
                    'value' => 'text',
                ];
            case 'il_dcl_stloc1_default':
            case 'il_dcl_stloc2_default':
            case 'il_dcl_stloc3_default':
                return [
                    'id' => 'integer',
                    'tview_set_id' => 'integer',
                    'value' => 'text',
                ];
            default:
                return [];
        }
    }

    /**
     * Return dependencies form entities to other entities (in our case these are all the DB relations)
     * @param string     $a_entity
     * @param string     $a_version
     * @param array|null $a_rec
     * @param array|null $a_ids
     * @return array
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        if (!$a_rec && !$a_ids) {
            return [];
        }
        switch ($a_entity) {
            case 'dcl':
                $set = $this->db->query('SELECT * FROM il_dcl_table WHERE obj_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ) . ' ORDER BY id');
                $ids = $this->buildCache('il_dcl_table', $set);

                return [
                    'il_dcl_table' => ['ids' => $ids],
                ];
            case 'il_dcl_table':
                $set = $this->db->query('SELECT * FROM il_dcl_record WHERE table_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $ids_records = $this->buildCache('il_dcl_record', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_field WHERE table_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $ids_fields = $this->buildCache('il_dcl_field', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_tableview WHERE table_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $ids_tableviews = $this->buildCache('il_dcl_tableview', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_tfield_set WHERE table_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $ids_tablefield_settings = $this->buildCache('il_dcl_tfield_set', $set);

                return [
                    'il_dcl_field' => ['ids' => $ids_fields],
                    'il_dcl_record' => ['ids' => $ids_records],
                    'il_dcl_tableview' => ['ids' => $ids_tableviews],
                    'il_dcl_tfield_set' => ['ids' => $ids_tablefield_settings],
                ];
            case 'il_dcl_field':
                $set = $this->db->query('SELECT * FROM il_dcl_field_prop WHERE field_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $prop_ids = $this->buildCache('il_dcl_field_prop', $set);

                $set = $this->db->query('SELECT * FROM il_dcl_sel_opts WHERE field_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $opt_ids = $this->buildCache('il_dcl_sel_opts', $set);

                return [
                    'il_dcl_field_prop' => ['ids' => $prop_ids],
                    'il_dcl_sel_opts' => ['ids' => $opt_ids],
                ];
            case 'il_dcl_record':
                $sql = 'SELECT rf.*, d.storage_location FROM il_dcl_record_field AS rf' . ' INNER JOIN il_dcl_field AS f ON (f.id = rf.field_id)'
                    . ' INNER JOIN il_dcl_datatype AS d ON (f.datatype_id = d.id) ' . ' WHERE rf.record_id = '
                    . $this->db->quote($a_rec['id'], 'integer');
                $set = $this->db->query($sql);
                $ids = $this->buildCache('il_dcl_record_field', $set);

                $set = $this->db->query($sql);
                while ($rec = $this->db->fetchObject($set)) {
                    $this->record_field_ids_2_storage[$rec->id] = ilDclCache::getFieldCache($rec->field_id)->getStorageLocation();
                }
                // Also build a cache of all values, no matter in which table they are (il_dcl_stloc(1|2|3)_value)
                $sql
                    = 'SELECT rf.id AS record_field_id, st1.value AS value1, st2.value AS value2, st3.value AS value3 FROM il_dcl_record_field AS rf '
                    . 'LEFT JOIN il_dcl_stloc1_value AS st1 ON (st1.record_field_id = rf.id) '
                    . 'LEFT JOIN il_dcl_stloc2_value AS st2 ON (st2.record_field_id = rf.id) '
                    . 'LEFT JOIN il_dcl_stloc3_value AS st3 ON (st3.record_field_id = rf.id) ' . 'WHERE rf.record_id = '
                    . $this->db->quote($a_rec['id'], 'integer');
                $set = $this->db->query($sql);

                while ($rec = $this->db->fetchObject($set)) {
                    $stloc = $this->record_field_ids_2_storage[$rec->record_field_id];
                    $value = null;
                    if ($stloc != 0) {
                        $value = "value{$stloc}";
                        $value = $rec->{$value};
                    }
                    // Save reocrd field id. Internal ID is not used currently
                    $this->caches["il_dcl_stloc{$stloc}_value"][$rec->record_field_id] = [
                        'record_field_id' => $rec->record_field_id,
                        'value' => $value,
                    ];
                }

                return [
                    'il_dcl_record_field' => ['ids' => $ids],
                ];
            case 'il_dcl_tableview':
                $set = $this->db->query('SELECT * FROM il_dcl_tview_set WHERE tableview_id = ' . $this->db->quote(
                    $a_rec['id'],
                    'integer'
                ));
                $ids = $this->buildCache('il_dcl_tview_set', $set);

                return [
                    'il_dcl_tview_set' => ['ids' => $ids],
                ];
            case 'il_dcl_tview_set':
                if (!(int)$a_rec['field']>0) {
                    break;
                }
                // Also build a cache of all values, no matter in which table they are (il_dcl_stloc(1|2|3)_value)
                $sql
                    = '
                        SELECT tview_set.id AS tview_set_id, st1.value AS value1, st2.value AS value2, st3.value AS value3, 
                        st1.id AS id1, st2.id AS id2, st3.id AS id3
                        FROM il_dcl_tview_set AS tview_set 
                            LEFT JOIN il_dcl_stloc1_default AS st1 ON (st1.tview_set_id = tview_set.id)
                            LEFT JOIN il_dcl_stloc2_default AS st2 ON (st2.tview_set_id = tview_set.id)
                            LEFT JOIN il_dcl_stloc3_default AS st3 ON (st3.tview_set_id = tview_set.id)
                            WHERE tview_set.id = ' . $this->db->quote($a_rec['id'], 'integer');
                $set = $this->db->query($sql);

                while ($rec = $this->db->fetchObject($set)) {
                    $stloc = ilDclCache::getFieldCache($a_rec['field'])->getStorageLocation();
                    if ($stloc != 0) {
                        $value_str = "value{$stloc}";
                        $value = $rec->{$value_str};
                        $id_str = "id{$stloc}";
                        $id = $rec->{$id_str};
                        $tview_set_id = $rec->tview_set_id;

                        // Save reocrd field id. Internal ID is not used currently
                        $this->caches["il_dcl_stloc{$stloc}_default"][$rec->tview_set_id] = [
                            'id' => $id,
                            'tview_set_id' => $rec->tview_set_id,
                            'value' => $value,
                        ];

                        return [
                            "il_dcl_stloc{$stloc}_default" => ['ids' => [$tview_set_id]],
                        ];
                    }
                }
                break;
            case 'il_dcl_record_field':
                $record_field_id = $a_rec['id'];
                $storage_loc = $this->record_field_ids_2_storage[$record_field_id];

                return [
                    "il_dcl_stloc{$storage_loc}_value" => ['ids' => [$record_field_id]],
                ];
        }

        return [];
    }

    /**
     * Read data from Cache for a given entity and ID(s)
     * @param array $a_ids one or multiple ids
     */
    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $this->data = [];
        $this->_readData($a_entity, $a_ids);
    }

    /**
     * Build data array, data is read from cache except dcl object itself
     * @param $a_entity
     * @param $a_ids
     */
    protected function _readData(string $a_entity, array $a_ids): void
    {
        switch ($a_entity) {
            case 'dcl':
                foreach ($a_ids as $dcl_id) {
                    if (ilObject::_lookupType($dcl_id) === 'dcl') {
                        $obj = new ilObjDataCollection((int) $dcl_id, false);
                        $data = [
                            'id' => $dcl_id,
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'is_online' => $obj->getOnline(),
                            'rating' => $obj->getRating(),
                            'public_notes' => $obj->getPublicNotes(),
                            'approval' => $obj->getApproval(),
                            'notification' => $obj->getNotification(),
                        ];
                        $this->caches['dcl'][$dcl_id] = $data;
                        $this->data[] = $data;
                    }
                }
                break;
            default:
                $data = $this->getCache($a_entity);
                foreach ($a_ids as $id) {
                    $this->data[] = $data[$id];
                }
        }
    }

    /**
     * Helper method to build cache for data of all entities
     * @param        $a_entity
     * @param Object $set ilDB->query() object
     * @return array of newly added IDs
     * @internal param string $entity
     */
    protected function buildCache(string $a_entity, object $set): array
    {
        $fields = array_keys($this->getTypes($a_entity, ''));
        $ids = [];
        while ($rec = $this->db->fetchObject($set)) {
            $data = [];
            foreach ($fields as $field) {
                $data[$field] = $rec->{$field};
            }
            $id = $rec->id;
            $this->caches[$a_entity][$id] = $data;
            $ids[] = $id;
        }

        return $ids;
    }
}
