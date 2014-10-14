<?php
require_once('./Services/GlobalCache/classes/class.ilGlobalCacheDBLayer.php');

/**
 * Class ilCachedComponentData
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedComponentData {

	/**
	 * @var array
	 */
	protected static $cached_results = array();
	/**
	 * @var ilCachedComponentData
	 */
	protected static $instance;
	/**
	 * @var array
	 */
	protected static $stored_results = array();
	/**
	 * @var bool
	 */
	protected $changed = false;
	/**
	 * @var bool
	 */
	protected $loaded = false;
	/**
	 * @var array
	 */
	protected $obj_def_name_to_id = array();
	/**
	 * @var array
	 */
	protected $obj_def_name_and_type_raw = array();
	/**
	 * @var array
	 */
	protected $il_component = array();
	/**
	 * @var array
	 */
	protected $il_object_def = array();
	/**
	 * @var array
	 */
	protected $il_object_subobj = array();
	/**
	 * @var array
	 */
	protected $subobj_for_parent = array();
	/**
	 * @var array
	 */
	protected $grouped_rep_obj_types = array();
	/**
	 * @var array
	 */
	protected $il_pluginslot_by_comp = array();
	/**
	 * @var array
	 */
	protected $il_pluginslot_by_id = array();
	/**
	 * @var array
	 */
	protected $il_pluginslot_by_name = array();
	/**
	 * @var array
	 */
	protected $il_plugin_by_id = array();
	/**
	 * @var array
	 */
	protected $il_object_group = array();
	/**
	 * @var array
	 */
	protected $il_object_sub_type = array();
	/**
	 * @var array
	 */
	protected $il_plugin_active = array();
	/**
	 * @var array
	 */
	protected $il_plugin_by_name = array();


	protected function __construct() {
		$this->global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT);
		$this->readFromDB();
	}


	/**
	 * @return boolean
	 */
	public function getLoaded() {
		return $this->loaded;
	}


	/**
	 * @param boolean $loaded
	 */
	public function setLoaded($loaded) {
		$this->loaded = $loaded;
	}


	protected function readFromDB() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */

		$set = $ilDB->query('SELECT * FROM il_component');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_component[$rec['id']] = $rec;
			$this->obj_def_name_to_id[$rec['id']] = $rec['name'];
			$this->obj_def_name_and_type_raw[$rec['type']][$rec['name']] = $rec;
		}

		$set = $ilDB->query('SELECT * FROM il_object_def');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_object_def[$rec['id']] = $rec;
		}

		$set = $ilDB->query('SELECT * FROM il_object_subobj');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_object_subobj[] = $rec;
			$parent = $rec['parent'];
			$this->subobj_for_parent[$parent][] = $rec;
		}
		$set = $ilDB->query('SELECT DISTINCT(id) AS sid, parent, il_object_def.* FROM il_object_def, il_object_subobj WHERE NOT (system = 1) AND NOT (sideblock = 1) AND subobj = id');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->grouped_rep_obj_types[$rec['parent']][] = $rec;
		}

		$set = $ilDB->query('SELECT * FROM il_pluginslot');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_pluginslot_by_comp[$rec['component']][] = $rec;
			$this->il_pluginslot_by_id[$rec['id']] = $rec;
			$this->il_pluginslot_by_name[$rec['name']] = $rec;
		}

		$set = $ilDB->query('SELECT * FROM il_plugin');
		$this->il_plugin_active = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_plugin_by_id[$rec['plugin_id']] = $rec;
			$this->il_plugin_by_name[$rec['name']] = $rec;
			if ($rec['active'] == 1) {
				$this->il_plugin_active[$rec['slot_id']][] = $rec;
			}
		}
		$set = $ilDB->query('SELECT * FROM il_object_group');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_object_group[$rec['id']] = $rec;
		}
		$set = $ilDB->query('SELECT * FROM il_object_sub_type');
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->il_object_sub_type[$rec['obj_type']][] = $rec;
		}
	}


	/**
	 * @return array
	 */
	public function getIlComponent() {
		return $this->il_component;
	}


	/**
	 * @param array $il_component
	 */
	public function setIlComponent($il_component) {
		$this->il_component = $il_component;
	}


	/**
	 * @return array
	 */
	public function getObjDefNameAndTypeRaw() {
		return $this->obj_def_name_and_type_raw;
	}


	/**
	 * @param array $obj_def_name_and_type_raw
	 */
	public function setObjDefNameAndTypeRaw($obj_def_name_and_type_raw) {
		$this->obj_def_name_and_type_raw = $obj_def_name_and_type_raw;
	}


	/**
	 * @return array
	 */
	public function getObjDefNameToId() {
		return $this->obj_def_name_to_id;
	}


	/**
	 * @param array $obj_def_name_to_id
	 */
	public function setObjDefNameToId($obj_def_name_to_id) {
		$this->obj_def_name_to_id = $obj_def_name_to_id;
	}


	/**
	 * @return array
	 */
	public function getIlObjectDef() {
		return $this->il_object_def;
	}


	/**
	 * @param array $il_object_def
	 */
	public function setIlObjectDef($il_object_def) {
		$this->il_object_def = $il_object_def;
	}


	/**
	 * @return array
	 */
	public function getIlObjectSubobj() {
		return $this->il_object_subobj;
	}


	/**
	 * @param array $il_object_subobj
	 */
	public function setIlObjectSubobj($il_object_subobj) {
		$this->il_object_subobj = $il_object_subobj;
	}


	/**
	 * @return array
	 */
	public function getGroupedRepObjTypes() {
		return $this->grouped_rep_obj_types;
	}


	/**
	 * @param array $grouped_rep_obj_types
	 */
	public function setGroupedRepObjTypes($grouped_rep_obj_types) {
		$this->grouped_rep_obj_types = $grouped_rep_obj_types;
	}


	/**
	 * @return array
	 */
	public function getIlPluginslotByComp() {
		return $this->il_pluginslot_by_comp;
	}


	/**
	 * @param array $il_pluginslot_by_service
	 */
	public function setIlPluginslotByComp($il_pluginslot_by_service) {
		$this->il_pluginslot_by_comp = $il_pluginslot_by_service;
	}


	/**
	 * @return array
	 */
	public function getIlPluginslotById() {
		return $this->il_pluginslot_by_id;
	}


	/**
	 * @param array $il_pluginslot_by_id
	 */
	public function setIlPluginslotById($il_pluginslot_by_id) {
		$this->il_pluginslot_by_id = $il_pluginslot_by_id;
	}


	/**
	 * @return array
	 */
	public function getIlPluginslotByName() {
		return $this->il_pluginslot_by_name;
	}


	/**
	 * @param array $il_pluginslot_by_name
	 */
	public function setIlPluginslotByName($il_pluginslot_by_name) {
		$this->il_pluginslot_by_name = $il_pluginslot_by_name;
	}


	/**
	 * @return array
	 */
	public function getIlPluginById() {
		return $this->il_plugin_by_id;
	}


	/**
	 * @param array $il_plugin_by_id
	 */
	public function setIlPluginById($il_plugin_by_id) {
		$this->il_plugin_by_id = $il_plugin_by_id;
	}


	/**
	 * @return array
	 */
	public function getIlPluginByName() {
		return $this->il_plugin_by_name;
	}


	/**
	 * @param array $il_plugin_by_name
	 */
	public function setIlPluginByName($il_plugin_by_name) {
		$this->il_plugin_by_name = $il_plugin_by_name;
	}


	/**
	 * @return array
	 */
	public function getIlPluginActive() {
		return $this->il_plugin_active;
	}


	/**
	 * @param array $il_plugin_active
	 */
	public function setIlPluginActive($il_plugin_active) {
		$this->il_plugin_active = $il_plugin_active;
	}


	/**
	 * @return array
	 */
	public function getIlObjectGroup() {
		return $this->il_object_group;
	}


	/**
	 * @param array $il_object_group
	 */
	public function setIlObjectGroup($il_object_group) {
		$this->il_object_group = $il_object_group;
	}


	/**
	 * @return array
	 */
	public function getIlObjectSubType() {
		return $this->il_object_sub_type;
	}


	/**
	 * @param array $il_object_sub_type
	 */
	public function setIlObjectSubType($il_object_sub_type) {
		$this->il_object_sub_type = $il_object_sub_type;
	}


	/**
	 * @return ilCachedComponentData
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			$global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT);
			$cached_obj = $global_cache->get('ilCachedComponentData');
			if ($cached_obj instanceof ilCachedComponentData) {
				self::$instance = $cached_obj;
			} else {
				self::$instance = new self();
				$global_cache->set('ilCachedComponentData', self::$instance);
			}
		}

		return self::$instance;
	}


	public static function flush() {
		ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT)->flush();
		self::$instance = NULL;
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function lookupPluginByName($name) {
		return $this->il_plugin_by_name[$name];
	}


	/**
	 * @param $slot_id
	 *
	 * @return mixed
	 */
	public function lookupActivePluginsBySlotId($slot_id) {
		if (is_array($this->il_plugin_active[$slot_id])) {
			return $this->il_plugin_active[$slot_id];
		} else {
			return array();
		}
	}


	/**
	 * @param $parent
	 *
	 * @return mixed
	 */
	public function lookupSubObjForParent($parent) {
		if (is_array($parent)) {
			$index = md5(serialize($parent));
			if (isset(self::$cached_results['subop_par'][$index])) {
				return self::$cached_results['subop_par'][$index];
			}

			$return = array();
			foreach ($parent as $p) {
				if (is_array($this->subobj_for_parent[$p])) {
					foreach ($this->subobj_for_parent[$p] as $rec) {
						$return[] = $rec;
					}
				}
			}

			self::$cached_results['subop_par'][$index] = $return;
			$this->changed = true;

			return $return;
		}

		return $this->subobj_for_parent[$parent];
	}


	/**
	 * @param $name
	 * @param $type
	 *
	 * @return mixed
	 */
	public function lookCompId($type, $name) {
		return $this->obj_def_name_and_type_raw[$type][$name]['id'];
	}


	/**
	 * @param $name
	 * @param $type
	 *
	 * @return mixed
	 */
	public function lookupCompInfo($type, $name) {
		if (!$type) {
			if (isset($this->obj_def_name_and_type_raw['Modules'][$name])) {
				$type = 'Modules';
			} else {
				$type = 'Services';
			}
		}

		return $this->obj_def_name_and_type_raw[$type][$name];
	}


	public function __destruct() {
		if ($this->changed) {
			$this->global_cache->set('ilCachedComponentData', $this);
		}
	}


	/**
	 * @param $parent
	 *
	 * @return mixed
	 */
	public function lookupGroupedRepObj($parent) {
		if (is_array($parent)) {
			$index = md5(serialize($parent));
			if (isset($cached_results['grpd_repo'][$index])) {
				return $cached_results['grpd_repo'][$index];
			}

			$return = array();
			$sids = array();
			foreach ($parent as $p) {
				$s = $this->grouped_rep_obj_types[$p];
				foreach ($s as $child) {
					if (!in_array($child['sid'], $sids)) {
						$sids[] = $child['sid'];
						$return[] = $child;
					}
				}
			}
			$this->changed = true;
			$cached_results['grpd_repo'][$index] = $return;

			return $return;
		} else {
			return $this->grouped_rep_obj_types[$parent];
		}
	}


	/**
	 * @param $component
	 *
	 * @return mixed
	 */
	public function lookupPluginSlotByComponent($component) {
		if (is_array($this->il_pluginslot_by_comp[$component])) {
			return $this->il_pluginslot_by_comp[$component];
		}

		return array();
	}


	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function lookupPluginSlotById($id) {
		return $this->il_pluginslot_by_id[$id];
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function lookupPluginSlotByName($name) {
		return $this->il_pluginslot_by_name[$name];
	}


	/**
	 * @return array
	 */
	public function getSubobjForParent() {
		return $this->subobj_for_parent;
	}


	/**
	 * @param array $subobj_for_parent
	 */
	public function setSubobjForParent($subobj_for_parent) {
		$this->subobj_for_parent = $subobj_for_parent;
	}
}

?>
