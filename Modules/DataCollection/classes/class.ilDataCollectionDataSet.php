<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/DataSet/classes/class.ilDataSet.php");
require_once('class.ilDataCollectionCache.php');
require_once('class.ilObjDataCollection.php');

/**
 * DataCollection dataset class
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionDataSet extends ilDataSet {

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
	protected $caches = array(
		'dcl' => array(),
		'il_dcl_table' => array(),
		'il_dcl_field' => array(),
		'il_dcl_field_prop' => array(),
		'il_dcl_record' => array(),
		'il_dcl_record_field' => array(),
		'il_dcl_stloc1_value' => array(),
		'il_dcl_stloc2_value' => array(),
		'il_dcl_stloc3_value' => array(),
		'il_dcl_view' => array(),
		'il_dcl_viewdefinition' => array(),
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
	 * Caches ilDataCollectionRecordField objects. Key = id, value = object
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


	public function __construct() {
		global $ilDB, $ilUser;
		parent::__construct();
		$this->db = $ilDB;
		$this->user = $ilUser;
	}


	/**
	 * @return array
	 */
	public function getSupportedVersions() {
		return array( '4.5.0' );
	}


	/**
	 * Get cached data from a given entity
	 *
	 * @param $a_entity
	 *
	 * @return mixed
	 * @throws ilException
	 */
	public function getCache($a_entity) {
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
	public function getXmlNamespace($a_entity, $a_schema_version) {
		return 'http://www.ilias.de/xml/Modules/DataCollection/' . $a_entity;
	}


	/**
	 * @param string          $a_entity
	 * @param                 $a_types
	 * @param array           $a_rec
	 * @param ilImportMapping $a_mapping
	 * @param string          $a_schema_version
	 */
	public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version) {
		switch ($a_entity) {
			case 'dcl':
				// Calling new_obj->create() will create the main table for us
				$new_obj = new ilObjDataCollection();
				$new_obj->setTitle($a_rec['title']);
				$new_obj->setDescription($a_rec['description']);
				$new_obj->setApproval($a_rec['approval']);
				$new_obj->setPublicNotes($a_rec['public_notes']);
				$new_obj->setNotification($a_rec['notification']);
				$new_obj->setPublicNotes($a_rec['public_notes']);
				$new_obj->setOnline(false);
				$new_obj->setRating($a_rec['rating']);
				$new_obj->create();
				$this->import_dc_object = $new_obj;
				$a_mapping->addMapping('Modules/DataCollection', 'dcl', $a_rec['id'], $new_obj->getId());
				break;
			case 'il_dcl_table':
				// If maintable, update. Other tables must be created as well
				$table = ($this->count_imported_tables
					> 0) ? new ilDataCollectionTable() : ilDataCollectionCache::getTableCache($this->import_dc_object->getMainTableId());
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
				$table->setDefaultSortField($a_rec['default_sort_field_id']);
				$table->setDefaultSortFieldOrder($a_rec['default_sort_field_order']);
				$table->setPublicCommentsEnabled($a_rec['public_comments']);
				$table->setViewOwnRecordsPerm($a_rec['view_own_records_perm']);
				if ($this->count_imported_tables > 0) {
					$table->doCreate(false); // false => Do not create views! They are imported later
				} else {
					$table->doUpdate();
					$this->count_imported_tables ++;
					// Delete views from maintable because we want to import them from the xml data
					$set = $this->db->query('SELECT * FROM il_dcl_view WHERE table_id = ' . $this->db->quote($table->getId(), 'integer'));
					$view_ids = array();
					while ($row = $this->db->fetchObject($set)) {
						$view_ids[] = $row->id;
					}
					if (count($view_ids)) {
						$this->db->manipulate("DELETE FROM il_dcl_viewdefinition WHERE view_id IN (" . implode(',', $view_ids) . ")");
					}
					$this->db->manipulate("DELETE FROM il_dcl_view WHERE table_id = " . $this->db->quote($table->getId(), 'integer'));
				}
				$a_mapping->addMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['id'], $table->getId());
				break;
			case 'il_dcl_field':
				$new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
				if ($new_table_id) {
					$field = new ilDataCollectionField();
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
					$table = ilDataCollectionCache::getTableCache($new_table_id);
					if ($table && $table->getDefaultSortField() == $a_rec['id']) {
						$table->setDefaultSortField($field->getId());
						$table->doUpdate();
					}
				}
				break;
			case 'il_dcl_record':
				$new_table_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_table', $a_rec['table_id']);
				if ($new_table_id) {
					$record = new ilDataCollectionRecord();
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
					if ($a_rec['type'] == 0 && $a_rec['formtype'] == 0) {
						// RecordViewViewDefinition: Create a new RecordViewViewdefinition. Note that the associated Page object is NOT created.
						// Creation of the Page object is handled by the import of Services/COPage
						$definition = new ilDataCollectionRecordViewViewdefinition();
						$definition->setTableId($new_table_id);
						$definition->create(true); // DO not create DB entries for page object
						// This mapping is needed for the import handled by Services/COPage
						$a_mapping->addMapping('Services/COPage', 'pg', 'dclf:' . $a_rec['id'], 'dclf:' . $definition->getId());
						$a_mapping->addMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['id'], $definition->getId());
					} else {
						// Other definitions - grab next ID from il_dcl_view
						$view_id = $this->db->nextId("il_dcl_view");
						$sql = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (" . $this->db->quote($view_id, "integer") . ", "
							. $this->db->quote($new_table_id, "integer") . ", " . $this->db->quote($a_rec['type'], "integer") . ", "
							. $this->db->quote($a_rec['formtype'], "integer") . ")";
						$this->db->manipulate($sql);
						$a_mapping->addMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['id'], $view_id);
					}
				}
				break;
			case 'il_dcl_viewdefinition':
				$new_view_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_view', $a_rec['view_id']);
				$new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field']);
				$field = ($new_field_id) ? $new_field_id : $a_rec['field'];
				if ($new_view_id) {
					$sql =
						'INSERT INTO il_dcl_viewdefinition (view_id, field, field_order, is_set) VALUES (' . $this->db->quote($new_view_id, 'integer')
						. ', ' . $this->db->quote($field, 'text') . ', ' . $this->db->quote($a_rec['field_order'], 'integer') . ', '
						. $this->db->quote($a_rec['is_set'], 'integer') . ')';
					$this->db->manipulate($sql);
				}
				break;
			case 'il_dcl_field_prop':
				$new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['field_id']);
				if ($new_field_id) {
					$prop = new ilDataCollectionFieldProp();
					$prop->setFieldId($new_field_id);
					$prop->setDatatypePropertyId($a_rec['datatype_prop_id']);
					// For field references, we need to get the new field id of the referenced field
					// If the field_id does not yet exist (e.g. referenced table not yet created), store temp info and fix before finishing import
					$value = $a_rec['value'];
					$refs = array( ilDataCollectionField::PROPERTYID_REFERENCE, ilDataCollectionField::PROPERTYID_N_REFERENCE );
					$fix_refs = false;
					if (in_array($prop->getDatatypePropertyId(), $refs)) {
						$new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $a_rec['value']);
						if ($new_field_id === false) {
							$value = NULL;
							$fix_refs = true;
						}
					}
					$prop->setValue($value);
					$prop->doCreate();
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
					$record = ilDataCollectionCache::getRecordCache($record_id);
					$field = ilDataCollectionCache::getFieldCache($field_id);
					$record_field = new ilDataCollectionRecordField($record, $field); // Created in constructor if not existing
					$a_mapping->addMapping('Modules/DataCollection', 'il_dcl_record_field', $a_rec['id'], $record_field->getId());
					$this->import_record_field_cache[$record_field->getId()] = $record_field;
				}
				break;
			case 'il_dcl_stloc1_value':
			case 'il_dcl_stloc2_value':
			case 'il_dcl_stloc3_value':
				$new_record_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record_field', $a_rec['record_field_id']);
				if ($new_record_field_id) {
					/** @var ilDataCollectionRecordField $record_field */
					$record_field = $this->import_record_field_cache[$new_record_field_id];
					if (is_object($record_field)) {
						// Need to rewrite internal references and lookup new objects if MOB or File
						// For some fieldtypes it's better to reset the value, e.g. ILIAS_REF
						switch ($record_field->getField()->getDatatypeId()) {
							case ilDataCollectionDatatype::INPUTFORMAT_MOB:
								// Check if we got a mapping from old object
								$new_mob_id = $a_mapping->getMapping('Services/MediaObjects', 'mob', $a_rec['value']);
								$value = ($new_mob_id) ? (int)$new_mob_id : NULL;
								$this->import_temp_new_mob_ids[] = $new_mob_id;
								break;
							case ilDataCollectionDatatype::INPUTFORMAT_FILE:
								$new_file_id = $a_mapping->getMapping('Modules/File', 'file', $a_rec['value']);
								$value = ($new_file_id) ? (int)$new_file_id : NULL;
								break;
							case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
							case ilDataCollectioNDatatype::INPUTFORMAT_REFERENCELIST:
								// If we are referencing to a record from a table that is not yet created, return value is always false because the record does exist neither
								// Solution: Temporary store all references and fix them before finishing the import.
								$new_record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $a_rec['value']);
								if ($new_record_id === false) {
									$this->import_temp_refs[$new_record_field_id] = $a_rec['value'];
								}
								$value = ($new_record_id) ? (int)$new_record_id : NULL;
								break;
							case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
								$value = NULL;
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
	public function beforeFinishImport(ilImportMapping $a_mapping) {
		foreach ($this->import_temp_new_mob_ids as $new_mob_id) {
			ilObjMediaObject::_saveUsage($new_mob_id, "dcl:html", $a_mapping->getTargetId());
		}
		foreach ($this->import_temp_refs as $record_field_id => $old_record_id) {
			$new_record_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_record', $old_record_id);
			$value = ($new_record_id) ? (int)$new_record_id : NULL;
			/** @var ilDataCollectionRecordField $record_field */
			$record_field = $this->import_record_field_cache[$record_field_id];
			$record_field->setValue($value, true);
			$record_field->doUpdate();
		}
		foreach ($this->import_temp_refs_props as $field_prop_id => $old_field_id) {
			$new_field_id = $a_mapping->getMapping('Modules/DataCollection', 'il_dcl_field', $old_field_id);
			$value = ($new_field_id) ? (int)$new_field_id : NULL;
			$field_prop = new ilDataCollectionFieldProp($field_prop_id);
			$field_prop->setValue($value);
			$field_prop->doUpdate();
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
	protected function getTypes($a_entity, $a_version) {
		switch ($a_entity) {
			case 'dcl':
				return array(
					"id" => "integer",
					"title" => "text",
					"description" => "text",
					'main_table_id' => 'integer',
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
					'default_sort_field_id' => 'text',
					'default_sort_field_order' => 'text',
					'description' => 'text',
					'public_comments' => 'integer',
					'view_own_records_perm' => 'integer',
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
			case 'il_dcl_field_prop':
				return array(
					'id' => 'integer',
					'field_id' => 'integer',
					'datatype_prop_id' => 'integer',
					'value' => 'integer',
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
			case 'il_dcl_view':
				return array(
					'id' => 'integer',
					'table_id' => 'integer',
					'type' => 'integer',
					'formtype' => 'integer',
				);
			case 'il_dcl_viewdefinition':
				return array(
					'view_id' => 'integer',
					'field' => 'string',
					'field_order' => 'integer',
					'is_set' => 'integer',
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
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids) {
		if (!$a_rec && !$a_ids) {
			return false;
		}
		switch ($a_entity) {
			case 'dcl':
				$set = $this->db->query('SELECT * FROM il_dcl_table WHERE obj_id = ' . $this->db->quote($a_rec['id'], 'integer') . ' ORDER BY id');
				$ids = $this->buildCache('il_dcl_table', $set);

				return array(
					'il_dcl_table' => array( 'ids' => $ids ),
				);
				break;
			case 'il_dcl_table':
				$set = $this->db->query('SELECT * FROM il_dcl_record WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
				$ids_records = $this->buildCache('il_dcl_record', $set);
				$set = $this->db->query('SELECT * FROM il_dcl_field WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
				$ids_fields = $this->buildCache('il_dcl_field', $set);
				$set = $this->db->query('SELECT * FROM il_dcl_view WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
				$ids_views = $this->buildCache('il_dcl_view', $set);

				return array(
					'il_dcl_field' => array( 'ids' => $ids_fields ),
					'il_dcl_record' => array( 'ids' => $ids_records ),
					'il_dcl_view' => array( 'ids' => $ids_views ),
				);
			case 'il_dcl_field':
				$set = $this->db->query('SELECT * FROM il_dcl_field_prop WHERE field_id = ' . $this->db->quote($a_rec['id'], 'integer'));
				$ids = $this->buildCache('il_dcl_field_prop', $set);

				return array(
					'il_dcl_field_prop' => array( 'ids' => $ids ),
				);
			case 'il_dcl_record':
				$sql = 'SELECT rf.*, d.storage_location FROM il_dcl_record_field AS rf' . ' INNER JOIN il_dcl_field AS f ON (f.id = rf.field_id)'
					. ' INNER JOIN il_dcl_datatype AS d ON (f.datatype_id = d.id) ' . ' WHERE rf.record_id = '
					. $this->db->quote($a_rec['id'], 'integer');
				$set = $this->db->query($sql);
				$ids = $this->buildCache('il_dcl_record_field', $set);

				$set = $this->db->query($sql);
				while ($rec = $this->db->fetchObject($set)) {
					$this->record_field_ids_2_storage[$rec->id] = $rec->storage_location;
				}
				// Also build a cache of all values, no matter in which table they are (il_dcl_stloc(1|2|3)_value)
				$sql =
					'SELECT rf.id AS record_field_id, st1.value AS value1, st2.value AS value2, st3.value AS value3 FROM il_dcl_record_field AS rf '
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
						'value' => $rec->{$value}
					);
				}

				return array(
					'il_dcl_record_field' => array( 'ids' => $ids )
				);
			case 'il_dcl_view':
				$set = $this->db->query('SELECT * FROM il_dcl_viewdefinition WHERE view_id = ' . $this->db->quote($a_rec['id'], 'integer'));
				$ids = $this->buildCache('il_dcl_viewdefinition', $set);

				return array(
					'il_dcl_viewdefinition' => array( 'ids' => $ids )
				);
			case 'il_dcl_record_field':
				$record_field_id = $a_rec['id'];
				$storage_loc = $this->record_field_ids_2_storage[$record_field_id];

				return array(
					"il_dcl_stloc{$storage_loc}_value" => array( 'ids' => array( $record_field_id ) )
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
	public function readData($a_entity, $a_version, $a_ids) {
		$this->data = array();
		if (!is_array($a_ids)) {
			$a_ids = array( $a_ids );
		}
		$this->_readData($a_entity, $a_ids);
	}


	/**
	 * Build data array, data is read from cache except dcl object itself
	 *
	 * @param $a_entity
	 * @param $a_ids
	 */
	protected function _readData($a_entity, $a_ids) {
		switch ($a_entity) {
			case 'dcl':
				foreach ($a_ids as $dcl_id) {
					if (ilObject::_lookupType($dcl_id) == 'dcl') {
						$obj = new ilObjDataCollection($dcl_id, false);
						$data = array(
							'id' => $dcl_id,
							'title' => $obj->getTitle(),
							'description' => $obj->getDescription(),
							'main_table_id' => $obj->getMainTableId(),
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
	protected function buildCache($a_entity, $set) {
		$fields = array_keys($this->getTypes($a_entity, ''));
		$ids = array();
		while ($rec = $this->db->fetchObject($set)) {
			$data = array();
			foreach ($fields as $field) {
				$data[$field] = $rec->{$field};
			}
			// il_dcl_viewdefinition is the only table that has no internal id, so we build primary from view_id AND field columns
			$id = ($a_entity == 'il_dcl_viewdefinition') ? $rec->view_id . '_' . $rec->field : $rec->id;
			$this->caches[$a_entity][$id] = $data;
			$ids[] = $id;
		}

		return $ids;
	}
}