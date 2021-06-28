<?php

/**
 * Class ilCachedComponentData
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedComponentData
{
    protected static ?ilCachedComponentData $instance;
    protected array $obj_def_name_and_type_raw;
    protected array $il_pluginslot_by_id = [];
    protected array $il_pluginslot_by_name = [];
    protected array $il_pluginslot_by_comp = [];
    protected array $il_plugin_by_id = [];
    protected array $il_plugin_by_name = [];
    protected array $il_plugin_active = [];

    protected function __construct()
    {
        $this->global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT);
        $this->readFromDB();
    }

    protected function readFromDB()
    {
        global $DIC;
        $ilDB = $DIC->database();
        /**
         * @var $ilDB ilDB
         */
        $set = $ilDB->query('SELECT * FROM il_component');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->obj_def_name_and_type_raw[$rec['type']][$rec['name']] = $rec;
        }


        $set = $ilDB->query('SELECT * FROM il_pluginslot');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->il_pluginslot_by_id[$rec['id']] = $rec;
            $this->il_pluginslot_by_name[$rec['name']] = $rec;
            $this->il_pluginslot_by_comp[$rec['component']][] = $rec;
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
    }

    /**
     * @return array
     */
    public function getIlPluginslotById()
    {
        return $this->il_pluginslot_by_id;
    }

    /**
     * @return array
     */
    public function getIlPluginById()
    {
        return $this->il_plugin_by_id;
    }

    /**
     * @return array
     */
    public function getIlPluginActive()
    {
        return $this->il_plugin_active;
    }

    /**
     * @return ilCachedComponentData
     */
    public static function getInstance()
    {
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


    public static function flush()
    {
        ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT)->flush();
        self::$instance = null;
    }


    /**
     * @param $name
     *
     * @return mixed
     */
    public function lookupPluginByName($name)
    {
        return $this->il_plugin_by_name[$name];
    }


    /**
     * @param $slot_id
     *
     * @return mixed
     */
    public function lookupActivePluginsBySlotId($slot_id)
    {
        if (isset($this->il_plugin_active[$slot_id]) && is_array($this->il_plugin_active[$slot_id])) {
            return $this->il_plugin_active[$slot_id];
        } else {
            return array();
        }
    }

    /**
     * @param $name
     * @param $type
     *
     * @return mixed
     */
    public function lookCompId($type, $name)
    {
        return $this->obj_def_name_and_type_raw[$type][$name]['id'];
    }

    /**
     * @param $component
     *
     * @return mixed
     */
    public function lookupPluginSlotByComponent($component)
    {
        if (isset($this->il_pluginslot_by_comp[$component]) && is_array($this->il_pluginslot_by_comp[$component])) {
            return $this->il_pluginslot_by_comp[$component];
        }

        return array();
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function lookupPluginSlotById($id)
    {
        return $this->il_pluginslot_by_id[$id];
    }


    /**
     * @param $name
     *
     * @return mixed
     */
    public function lookupPluginSlotByName($name)
    {
        return $this->il_pluginslot_by_name[$name];
    }
}
