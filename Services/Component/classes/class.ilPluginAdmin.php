<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilComponent.php");

/**
 * Administration class for plugins. Handles basic data from plugin.php files.
 *
 * This class currently needs refactoring. There are a lot of methods which are related to some specific slots.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesComponent
 */
class ilPluginAdmin
{

    /**
     * @var array
     */
    protected $data;
    /**
     * @var bool
     */
    protected $got_data = false;
    /**
     * cached lists of active plugins
     *
     * @var    array
     */
    public static $active_plugins = array();
    /**
     * cached lists of plugin objects
     *
     * @var    array
     */
    protected static $plugin_objects = array();
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected ilComponentDataDB $component_data_db;


    /**
     * ilPluginAdmin constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("cmps");
        $this->component_data_db = $DIC["component.db"];
    }


    protected function getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname) {
        return $this->component_data_db
            ->getComponentByTypeAndName(
                $a_ctype,
                $a_cname
            )
            ->getPluginSlotById(
                $a_slot_id
            )
            ->getPluginByName(
                $a_pname
            );
    }

    /**
     * Checks whether plugin is active (include version checks)
     *
     * ATTENTION: If one tries to remove this, the task doesn't look very hard initially.
     * `grep -r "isActive([^)]*,.*)" Modules/` (or in Services) only reveals a handful
     * of locations that actually use this function. But: If you attempt to remove these
     * locations, you run into a dependency hell in the T&A. The T&A uses dependency
     * injection, but not container. If you add ilComponentDataDB as dependency, you need
     * to inject it ("courier anti pattern") in the classes above. This is super cumbersome
     * and I started to loose track soon. This should be removed, but currently my
     * concentration is not enough to do so.
     *
     * @deprecated
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return bool
     */
    public function isActive($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        trigger_error("DEPRECATED: ilPluginAdmin::isActive is deprecated. Remove your usages of the method.");
        try {
            return $this->getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname)->isActive();
        }
        catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get version.
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return bool
     * @throws ilPluginException
     */
    public function needsUpdate($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        return $this->getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname)->isUpdateRequired();
    }

    /**
     * Get Plugin Object
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return ilPlugin the plugin
     */
    public static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        // cache the plugin objects
        if (!isset(self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname])) {
            self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname]
                = ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
        }

        return self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname];
    }


    /**
     * Get Plugin Object
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @param string $a_class_file_name
     *
     * @return void
     */
    public static function includeClass($a_ctype, $a_cname, $a_slot_id, $a_pname, $a_class_file_name)
    {
        // cache the plugin objects
        if (!isset(self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname])) {
            self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname]
                = ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
        }
        /**
         * @var $pl ilPlugin
         */
        $pl = self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname];
        $pl->includeClass($a_class_file_name);
    }


    /**
     * Checks whether plugin has active learning progress
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return    bool
     * @throws ilPluginException
     */
    public function hasLearningProgress($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        return $this->getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname)->supportsLearningProgress();
    }


    /**
     * Checks whether plugin supports export/import
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return    bool
     * @throws ilPluginException
     */
    public function supportsExport($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        return $this->getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname)->supportsExport();
    }

    /**
     * Get info for all plugins.
     *
     * @return    array<string, array>
     */
    public static function getAllPlugins()
    {
        static $all_plugins;

        global $DIC;
        $component_data_db = $DIC["component.db"];
        if (!isset($all_plugins)) {
            $all_plugins = [];
            foreach ($component_data_db->getPlugins() as $id => $plugin) {
                $all_plugins[$id] = [
                    "component_type" => $plugin->getComponent()->getType(),
                    "component_name" => $plugin->getComponent()->getName(),
                    "slot_id" => $plugin->getPluginSlot()->getId(),
                    "name" => $plugin->getName(),
                    "last_update_version" => (string) $plugin->getCurrentVersion(),
                    "active" => $plugin->isActivated() ? "1" : "0",
                    "db_version" => $plugin->getCurrentDBVersion(),
                    "plugin_id" => $plugin->getId()
                ];
            }
        }

        return $all_plugins;
    }


    /**
     * Get info for all active plugins.
     *
     * @return    array
     */
    public static function getActivePlugins()
    {
        static $active_plugins = null;
        if ($active_plugins === null) {
            global $DIC;
            $component_data_db = $DIC["component.db"];
            $active_plugins = [];

            foreach ($component_data_db->getPlugins() as $id => $plugin) {
                if (!$plugin->isActivated()) {
                    continue;
                }
                $active_plugins[] = [
                    "component_type" => $plugin->getComponent()->getType(),
                    "component_name" => $plugin->getComponent()->getName(),
                    "slot_id" => $plugin->getPluginSlot()->getId(),
                    "name" => $plugin->getName(),
                    "last_update_version" => (string) $plugin->getCurrentVersion(),
                    "active" => $plugin->isActivated() ? "1" : "0",
                    "db_version" => $plugin->getCurrentDBVersion(),
                    "plugin_id" => $plugin->getId()
                ];
            }
        }
        return $active_plugins;
    }


    /**
     * Check, if a plugin is active
     *
     * @param string $id id of the plugin
     *
     * @return    boolean
     */
    public static function isPluginActive(string $id)
    {
        global $DIC;
        $component_data_db = $DIC["component.db"];

        return
            $component_data_db->hasPluginId($id)
            && $component_data_db->getPluginById($id)->isActivated();
    }


    /**
     * Get a plugin-object by id
     *
     * @param string $id id of the plugin
     *
     * @return    ilPlugin
     * @throws    InvalidArgumentException    if no plugin with that id is found
     */
    public static function getPluginObjectById($id)
    {
        assert(is_string($id));
        $plugs = self::getAllPlugins();
        if (!array_key_exists($id, $plugs)) {
            throw new \InvalidArgumentException("Plugin does not exist: " . $id, 1);
        }
        $pdata = $plugs[$id];

        return self::getPluginObject(
            $pdata['component_type'],
            $pdata['component_name'],
            $pdata['slot_id'],
            $pdata['name']
        );
    }


    /**
     * @deprecated
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider[]
     */
    public static function getAllGlobalScreenProviders() : array
    {
        $providers = array();
        // return array(); // current fix
        foreach (self::getActivePlugins() as $plugin) {
            $pl = self::getPluginObjectById($plugin['plugin_id']);
            if ($pl->isActive()) {
                array_push($providers, $pl->promoteGlobalScreenProvider());
            }
        }

        return $providers;
    }


    /**
     * @return Generator
     */
    public static function getGlobalScreenProviderCollections() : Generator
    {
        /**
         * @var $pl ilPlugin
         */
        foreach (self::getActivePlugins() as $plugin) {
            $pl = self::getPluginObjectById($plugin['plugin_id']);
            if ($pl->isActive()) {
                yield $pl->getGlobalScreenProviderCollection();
            }
        }
    }

    protected function getPluginSlots(array $components, string $type) : \Iterator
    {
        foreach ($this->getComponentSlotsByType($components, $type) as $slot) {
            $component = $slot->getComponent();
            yield new ilPluginSlot($component->getType(), $component->getName(), $slot->getId());
        }
    }

    protected function getComponentSlotsByType(array $components, string $type) : \Iterator
    {
        foreach ($components as $component) {
            $component = $this->component_data_db->getComponentByTypeAndName($type, $component["subdir"]);
            foreach ($component->getPluginSlots() as $component_slot) {
                yield $component_slot;
            }
        }
    }

    final public function clearCachedData()
    {
        unset($this->data);
        unset($this->got_data);
    }
}
