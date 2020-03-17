<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * DataCollection dataset class
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionDataSet extends ilDataSet
{

    /**
     * @var ilDB
     */
    protected $db;
    /**
     * @var array
     */
    protected $data = array();
    /**
     * Maps a given record_field ID (key) to the correct table where the value is stored (il_dcl_stloc(1|2|3)_value)
     *
     * @var array
     */
    protected $record_field_ids_2_storage = array();
    /**
     * Cache for all data of the entities (all related data for each mySQL table) for the DCL being exported
     *
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
     *
     * Get cache from a given entity:
     *  ilDataCollectionDataSet::getCache('il_dcl_table');
     *
     * @var array
     */
    protected $caches
        = array(
            'dcl' => array(),
            'il_dcl_table' => array(),
            'il_dcl_field' => array(),
            'il_dcl_field_prop' => array(),
            'il_dcl_sel_opts' => array(),
            'il_dcl_record' => array(),
            'il_dcl_record_field' => array(),
            'il_dcl_stloc1_value' => array(),
            'il_dcl_stloc2_value' => array(),
            'il_dcl_stloc3_value' => array(),
            'il_dcl_tfield_set' => array(),
            'il_dcl_tableview' => array(),
            'il_dcl_tview_set' => array(),
        );
    /**
     * @var ilObjDataCollection
     */
    protected $import_dc_object;
    /**
     * @var int
     */
    protected $count_imported_tables = 0;
    /**
     * Caches ilDclBaseRecordFieldModel objects. Key = id, value = object
     *
     * @var array
     */
    protected $import_record_field_cache = array();
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var array
     */
    protected $import_temp_refs = array();
    /**
     * @var array
     */
    protected $import_temp_refs_props = array();
    /**
     * @var array
     */
    protected $import_temp_new_mob_ids = array();


    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        parent::__construct();
        $this->db = $ilDB;
        $this->user = $ilUser;
    }


    /**
     * @return array
     */
    public function getSupportedVersions()
    {
        return array('4.5.0');
    }


    /**
     * Get cached data from a given entity
     *
     * @param $a_entity
     *
     * @return mixed
     * @throws ilException
     */
    public function getCache($a_entity)
    {
        if (!in_array($a_entity, array_keys($this->caches))) {
            throw new ilException("Entity '$a_entity' does not exist in Cache");
        }

        return $this->caches[$a_entity];
    }


    /**
     * @param string $a_entity
     * @param string $a_schema_version
     *
     * @return string
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return 'http://www.ilias.de/xml/Modules/DataCollection/' . $a_entity;
    }


    /**
     * @param string          $a_entity
     * @param                 $a_types
     * @param array           $a_rec
     * @param ilImportMapping $a_mapping
     * @param string          $a_schema_version
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
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
                $table = new ilDclTable();//($this->count_imported_tables > 0) ? new ilDclTable() : ilDclCache::getTableCache($this->import_dc_object->getMainTableId());
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
                    $field->setRequired($a_rec['required']);
                    $field->setUnique($a_rec['is_unique']);
                    $field->setLocked($a_rec['is_locked']);
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
                    $setting = ilDclTableFieldSetting::getInstance($new_table_id, $new_field_id ? $new_field_id : $a_rec['field']);
                    $setting->setFieldOrder($a_rec['field_order']);
                    $setting->setExportable($a_rec['exportable']);
                    $setting->store();
                }
                break;
            case 'il_dcl_tview_set':
                $new_tableview_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_tableview', $a_rec['tableview_id']);
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
                if ($new_tableview_id) {
                    $setting = new ilDclTableViewFieldSetting();
                    $setting->setTableviewId($new_tableview_id);
                    $setting->setVisible($a_rec['visible']);
                    $setting->setField($new_field_id ? $new_field_id : $a_rec['field']);
                    $setting->setInFilter($a_rec['in_filter']);
                    $setting->setFilterValue($a_rec['filter_value'] ? $a_rec['filter_value'] : null);
                    $setting->setFilterChangeable($a_rec['filter_changeable']);
                    $setting->create();
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
                        $a_mapping->addMapping('Services/COPage', 'pg', 'dclf:' . $a_rec['id'], 'dclf:' . $tableview->getId());
                        $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['id'], $tableview->getId());
                    } else {
                        $a_mapping->addMapping(
                            'Modules/DataCollection',
                            'il_dcl_view',
                            $a_rec['id'],
                            array('type' => $a_rec['type'], 'table_id' => $new_table_id, 'tableview_id' => $tableview->getId())
                        );
                    }
                }
                break;
            case 'il_dcl_viewdefinition':
                $map = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['view_id']);
                $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
                $field = ($new_field_id) ? $new_field_id : $a_rec['field'];
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
                        $properties = array(
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
                        );

                        $name = $properties[$a_rec['datatype_prop_id']];
                    }

                    $prop->setName($name);
                    // For field references, we need to get the new field id of the referenced field
                    // If the field_id does not yet exist (e.g. referenced table not yet created), store temp info and fix before finishing import
                    $value = $a_rec['value'];
                    $refs = array(ilDclBaseFieldModel::PROP_REFERENCE, ilDclBaseFieldModel::PROP_N_REFERENCE);
                    $fix_refs = false;

                    if (in_array($prop->getName(), $refs)) {
                        $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['value']);
                        if ($new_field_id === false) {
                            $value = null;
                            $fix_refs = true;
                        } else {
                            $value = $new_field_id;
                        }
                    }
                    $prop->setValue($value);
                    $prop->save();
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_field_prop', $a_rec['id'], $prop->getId());
                    if ($fix_refs) {
                        $this->import_temp_refs_props[$prop->getId()] = $a_rec['value'];
                    }
                }
                break;
            case 'il_dcl_record_field':
                $record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $a_rec['record_id']);
                $field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field_id']);
                if ($record_id && $field_id) {
                    $record = ilDclCache::getRecordCache($record_id);
                    $field = ilDclCache::getFieldCache($field_id);
                    $record_field = new ilDclBaseRecordFieldModel($record, $field);
                    $a_mapping->addMapping('Modules/DataCollection', 'il_dcl_record_field', $a_rec['id'], $record_field->getId());
                    $this->import_record_field_cache[$record_field->getId()] = $record_field;
                }
                break;
            case 'il_dcl_stloc1_value':
            case 'il_dcl_stloc2_value':
            case 'il_dcl_stloc3_value':
                $new_record_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record_field', $a_rec['record_field_id']);
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
                                $new_record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $a_rec['value']);
                                if ($new_record_id === false) {
                                    $this->import_temp_refs[$new_record_field_id] = $a_rec['value'];
                                }
                                $value = ($new_record_id) ? (int) $new_record_id : null;
                                break;
                            case ilDclDatatype::INPUTFORMAT_ILIAS_REF:
                                $value = null;
                                break;
                            default:
                                $value = $a_rec['value'];
                                if ($a_entity == 'il_dcl_stloc3_value' && (is_null($value) || empty($value))) {
                                    $value = '0000-00-00 00:00:00';
                                }
                        }
                        $record_field->setValue($value, true);
                        $record_field->doUpdate();
                    }
                }
                break;
        }
    }


    /**
     * Called before finishing import. Fix references inside DataCollections
     *
     * @param ilImportMapping $a_mapping
     */
    public function beforeFinishImport(ilImportMapping $a_mapping)
    {
        foreach ($this->import_temp_new_mob_ids as $new_mob_id) {
            ilObjMediaObject::_saveUsage($new_mob_id, "dcl:html", $a_mapping->getTargetId());
        }
        foreach ($this->import_temp_refs as $record_field_id => $old_record_id) {
            $new_record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $old_record_id);
            $value = ($new_record_id) ? (int) $new_record_id : null;
            /** @var ilDclBaseRecordFieldModel $record_field */
            $record_field = $this->import_record_field_cache[$record_field_id];
            $record_field->setValue($value, true);
            $record_field->doUpdate();
        }
        foreach ($this->import_temp_refs_props as $field_prop_id => $old_field_id) {
            $new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $old_field_id);
            $value = ($new_field_id) ? (int) $new_field_id : null;
            $field_prop = new ilDclFieldProperty($field_prop_id);
            $field_prop->setValue($value);
            $field_prop->update();
        }
    }


    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     *
     * @param string $a_entity
     * @param string $a_version
     *
     * @return array
     */
    protected function getTypes($a_entity, $a_version)
    {
        switch ($a_entity) {
            case 'dcl':
                return array(
                    "id" => "integer",
                    "title" => "text",
                    "description" => "text",
                    'is_online' => 'integer',
                    'rating' => 'integer',
                    'public_notes' => 'integer',
                    'approval' => 'integer',
                    'notification' => 'integer',
                );
            case 'il_dcl_table':
                return array(
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
                );
            case 'il_dcl_tableview':
                return array(
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'title' => 'text',
                    'roles' => 'text',
                    'description' => 'text',
                    'tableview_order' => 'integer',
                );
            case 'il_dcl_field':
                return array(
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'title' => 'text',
                    'description' => 'text',
                    'datatype_id' => 'integer',
                    'required' => 'integer',
                    'is_unique' => 'integer',
                    'is_locked' => 'integer',
                );
            case 'il_dcl_tview_set':
                return array(
                    'id' => 'integer',
                    'tableview_id' => 'integer',
                    'field' => 'text',
                    'visible' => 'integer',
                    'in_filter' => 'integer',
                    'filter_value' => 'text',
                    'filter_changeable' => 'integer',
                );
            case 'il_dcl_tfield_set':
                return array(
                    'id' => 'integer',
                    'table_id' => 'integer',
                    'field' => 'text',
                    'field_order' => 'integer',
                    'exportable' => 'integer',
                );
            case 'il_dcl_field_prop':
                return array(
                    'id' => 'integer',
                    'field_id' => 'integer',
                    'name' => 'text',
                    'value' => 'integer',
                );
            case 'il_dcl_sel_opts':
                return array(
                    'id' => 'integer',
                    'field_id' => 'integer',
                    'opt_id' => 'integer',
                    'sorting' => 'integer',
                    'value' => 'text',
                );
            case 'il_dcl_record':
                return array(
                    'id' => 'integer',
                    'table_id' => 'integer',
                );
            case 'il_dcl_record_field':
                return array(
                    'id' => 'integer',
                    'record_id' => 'integer',
                    'field_id' => 'integer',
                );
            case 'il_dcl_stloc1_value':
                return array(
                    'id' => 'integer',
                    'record_field_id' => 'integer',
                    'value' => 'text',
                );
            case 'il_dcl_stloc2_value':
                return array(
                    'id' => 'integer',
                    'record_field_id' => 'integer',
                    'value' => 'text',
                );
            case 'il_dcl_stloc3_value':
                return array(
                    'id' => 'integer',
                    'record_field_id' => 'integer',
                    'value' => 'text',
                );
            default:
                return array();
        }
    }


    /**
     * Return dependencies form entities to other entities (in our case these are all the DB relations)
     *
     * @param $a_entity
     * @param $a_version
     * @param $a_rec
     * @param $a_ids
     *
     * @return array
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        if (!$a_rec && !$a_ids) {
            return false;
        }
        switch ($a_entity) {
            case 'dcl':
                $set = $this->db->query('SELECT * FROM il_dcl_table WHERE obj_id = ' . $this->db->quote($a_rec['id'], 'integer') . ' ORDER BY id');
                $ids = $this->buildCache('il_dcl_table', $set);

                return array(
                    'il_dcl_table' => array('ids' => $ids),
                );
                break;
            case 'il_dcl_table':
                $set = $this->db->query('SELECT * FROM il_dcl_record WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $ids_records = $this->buildCache('il_dcl_record', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_field WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $ids_fields = $this->buildCache('il_dcl_field', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_tableview WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $ids_tableviews = $this->buildCache('il_dcl_tableview', $set);
                $set = $this->db->query('SELECT * FROM il_dcl_tfield_set WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $ids_tablefield_settings = $this->buildCache('il_dcl_tfield_set', $set);

                return array(
                    'il_dcl_field' => array('ids' => $ids_fields),
                    'il_dcl_record' => array('ids' => $ids_records),
                    'il_dcl_tableview' => array('ids' => $ids_tableviews),
                    'il_dcl_tfield_set' => array('ids' => $ids_tablefield_settings),
                );
            case 'il_dcl_field':
                $set = $this->db->query('SELECT * FROM il_dcl_field_prop WHERE field_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $prop_ids = $this->buildCache('il_dcl_field_prop', $set);

                $set = $this->db->query('SELECT * FROM il_dcl_sel_opts WHERE field_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $opt_ids = $this->buildCache('il_dcl_sel_opts', $set);

                return array(
                    'il_dcl_field_prop' => array('ids' => $prop_ids),
                    'il_dcl_sel_opts' => array('ids' => $opt_ids),
                );
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
                    $value = "value{$stloc}";
                    // Save reocrd field id. Internal ID is not used currently
                    $this->caches["il_dcl_stloc{$stloc}_value"][$rec->record_field_id] = array(
                        'record_field_id' => $rec->record_field_id,
                        'value' => $rec->{$value},
                    );
                }

                return array(
                    'il_dcl_record_field' => array('ids' => $ids),
                );
            case 'il_dcl_tableview':
                $set = $this->db->query('SELECT * FROM il_dcl_tview_set WHERE tableview_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                $ids = $this->buildCache('il_dcl_tview_set', $set);

                return array(
                    'il_dcl_tview_set' => array('ids' => $ids),
                );
            case 'il_dcl_record_field':
                $record_field_id = $a_rec['id'];
                $storage_loc = $this->record_field_ids_2_storage[$record_field_id];

                return array(
                    "il_dcl_stloc{$storage_loc}_value" => array('ids' => array($record_field_id)),
                );
        }

        return false;
    }


    /**
     * Read data from Cache for a given entity and ID(s)
     *
     * @param string $a_entity
     * @param string $a_version
     * @param array  $a_ids one or multiple ids
     */
    public function readData($a_entity, $a_version, $a_ids)
    {
        $this->data = array();
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        $this->_readData($a_entity, $a_ids);
    }


    /**
     * Build data array, data is read from cache except dcl object itself
     *
     * @param $a_entity
     * @param $a_ids
     */
    protected function _readData($a_entity, $a_ids)
    {
        switch ($a_entity) {
            case 'dcl':
                foreach ($a_ids as $dcl_id) {
                    if (ilObject::_lookupType($dcl_id) == 'dcl') {
                        $obj = new ilObjDataCollection($dcl_id, false);
                        $data = array(
                            'id' => $dcl_id,
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'is_online' => $obj->getOnline(),
                            'rating' => $obj->getRating(),
                            'public_notes' => $obj->getPublicNotes(),
                            'approval' => $obj->getApproval(),
                            'notification' => $obj->getNotification(),
                        );
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
     *
     * @param        $a_entity
     * @param Object $set ilDB->query() object
     *
     * @internal param string $entity
     * @return array of newly added IDs
     */
    protected function buildCache($a_entity, $set)
    {
        $fields = array_keys($this->getTypes($a_entity, ''));
        $ids = array();
        while ($rec = $this->db->fetchObject($set)) {
            $data = array();
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
