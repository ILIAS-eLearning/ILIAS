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


    /**
     * ilPluginAdmin constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("cmps");
    }


    /**
     * Get basic data of plugin from plugin.php
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @throws ilPluginException
     */
    final private function getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        if (!isset($this->got_data[$a_ctype][$a_cname][$a_slot_id][$a_pname])) {
            $slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);

            $plugin_php_file = "./Customizing/global/plugins/" . $a_ctype . "/" . $a_cname . "/" . $slot_name . "/" . $a_pname . "/plugin.php";

            if (!is_file($plugin_php_file)) {
                throw new ilPluginException("No plugin.php file found for Plugin :" . $a_pname . ".");
            }

            $plugin_db_data = ilPlugin::getPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname);
            $plugin_data = $this->parsePluginPhp($plugin_php_file);

            if ($plugin_db_data["plugin_id"] === null) {
                $this->setMustInstall($plugin_data);
            } else {
                $this->setCurrentState($plugin_data, (bool) $plugin_db_data["active"]);
                if ($this->pluginSupportCurrentILIAS($plugin_data)) {
                    $this->updateRequired($plugin_data, $plugin_db_data["last_update_version"]);
                }
            }

            $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname] = $plugin_data;
            $this->got_data[$a_ctype][$a_cname][$a_slot_id][$a_pname] = true;
        }
    }


    /**
     * Plugin supports current ILIAS
     *
     * @param string[] &$plugin_data
     *
     * @return bool
     */
    protected function pluginSupportCurrentILIAS(array &$plugin_data)
    {
        if (ilComponent::isVersionGreaterString($plugin_data["ilias_min_version"], ILIAS_VERSION_NUMERIC)) {
            $plugin_data["is_active"] = false;
            $plugin_data["needs_update"] = false;
            $plugin_data["activation_possible"] = false;

            if ($this->lng instanceof ilLanguage) {
                $inactive_reason = $this->lng->txt("cmps_needs_newer_ilias_version");
            } else {
                $inactive_reason = "Plugin needs a newer version of ILIAS.";
            }
            $plugin_data["inactive_reason"] = $inactive_reason;

            return false;
        }

        if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, $plugin_data["ilias_max_version"])) {
            $plugin_data["is_active"] = false;
            $plugin_data["needs_update"] = false;
            $plugin_data["activation_possible"] = false;
            if ($this->lng instanceof ilLanguage) {
                $inactive_reason = $this->lng->txt("cmps_needs_newer_plugin_version");
            } else {
                $inactive_reason = "Plugin does not support current version of ILIAS. Newer version of plugin needed.";
            }
            $plugin_data["inactive_reason"] = $inactive_reason;

            return false;
        }

        return true;
    }


    /**
     * Should the plugin be updated
     *
     * @param string[] &$plugin_data
     * @param string    $last_update_version
     *
     * @return void
     */
    protected function updateRequired(array &$plugin_data, $last_update_version)
    {
        if ($last_update_version == "") {
            $plugin_data["is_active"] = false;
            if ($this->lng instanceof ilLanguage) {
                $inactive_reason = $this->lng->txt("cmps_needs_update");
            } else {
                $inactive_reason = "Update needed.";
            }
            $plugin_data["inactive_reason"] = $inactive_reason;
            $plugin_data["needs_update"] = true;
            $plugin_data["activation_possible"] = false;
        } else {
            if (ilComponent::isVersionGreaterString($last_update_version, $plugin_data["version"])) {
                $plugin_data["is_active"] = false;
                if ($this->lng instanceof ilLanguage) {
                    $inactive_reason = $this->lng->txt("cmps_needs_upgrade");
                } else {
                    $inactive_reason = "Upgrade needed.";
                }
                $plugin_data["inactive_reason"] = $inactive_reason;
                $plugin_data["activation_possible"] = false;
            } else {
                if ($last_update_version != $plugin_data["version"]) {
                    $plugin_data["is_active"] = false;
                    if ($this->lng instanceof ilLanguage) {
                        $inactive_reason = $this->lng->txt("cmps_needs_update");
                    } else {
                        $inactive_reason = "Update needed.";
                    }
                    $plugin_data["inactive_reason"] = $inactive_reason;
                    $plugin_data["needs_update"] = true;
                    $plugin_data["activation_possible"] = false;
                }
            }
        }
    }


    /**
     * Set plugin data for intall
     *
     * @param string[] &$plugin_data
     *
     * @return void
     */
    protected function setMustInstall(array &$plugin_data)
    {
        $plugin_data["must_install"] = true;
        $plugin_data["is_active"] = false;
        $plugin_data["needs_update"] = false;
        $plugin_data["activation_possible"] = false;

        if ($this->lng instanceof ilLanguage) {
            $inactive_reason = $this->lng->txt("cmps_must_installed");
        } else {
            $inactive_reason = "Plugin must be installed.";
        }
        $plugin_data["inactive_reason"] = $inactive_reason;
    }


    /**
     * Set current state to static values,
     * excluding active and activatoin possible. There will be set from
     * db value $active
     *
     * @param string[] &$plugin_data
     * @param bool      $active
     *
     * @return void
     */
    protected function setCurrentState(array &$plugin_data, $active)
    {
        $plugin_data["is_active"] = $active;
        $plugin_data["activation_possible"] = !$active;
        $plugin_data["must_install"] = false;
        $plugin_data["needs_update"] = false;
        $plugin_data["inactive_reason"] = "";
    }


    /**
     * Get informations from plugin php file
     *
     * @param string $plugin_php_file
     *
     * @return string[]
     */
    protected function parsePluginPhp($plugin_php_file)
    {
        include_once($plugin_php_file);

        $values = ["version" => $version,
                   "id" => $id,
                   "ilias_min_version" => $ilias_min_version,
                   "ilias_max_version" => $ilias_max_version,
                   "responsible" => $responsible,
                   "responsible_mail" => $responsible_mail,
                   "learning_progress" => (bool) $learning_progress,
                   "supports_export" => (bool) $supports_export];

        return $values;
    }


    /**
     * Get version of plugin.
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return string
     * @throws ilPluginException
     */
    public function getVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["version"];
    }


    /**
     * Get Ilias Min Version
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return string
     * @throws ilPluginException
     */
    public function getIliasMinVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["ilias_min_version"];
    }


    /**
     * Get Ilias Max Version
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return string
     * @throws ilPluginException
     */
    public function getIliasMaxVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["ilias_max_version"];
    }


    /**
     * Get ID
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return string
     * @throws ilPluginException
     */
    public function getId($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["id"];
    }


    /**
     * Checks whether plugin is active (include version checks)
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return bool
     */
    public function isActive($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        try {
            $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
        } catch (ilPluginException $e) {
            return false;
        }

        return (bool) $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["is_active"];
    }


    /**
     * Checks whether plugin exists
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return bool
     * @throws ilPluginException
     */
    public function exists($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return isset($this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]);
    }


    /**
     * Get version.
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return bool
     * @throws ilPluginException
     */
    public function needsUpdate($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return (bool) $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["needs_update"];
    }


    /**
     * Get all data from file in an array
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return array
     * @throws ilPluginException
     */
    public function getAllData($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname];
    }


    /**
     * Get all active plugins for a slot
     *
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     *
     * @return array
     */
    public static function getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id)
    {
        // cache the list of active plugins
        if (!isset(self::$active_plugins[$a_ctype][$a_cname][$a_slot_id])) {
            self::$active_plugins[$a_ctype][$a_cname][$a_slot_id]
                = ilPlugin::getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id);
        }

        return self::$active_plugins[$a_ctype][$a_cname][$a_slot_id];
    }


    /**
     * Get Plugin Object
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
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
     * @param    string        $a_ctype   Component Type
     * @param    string        $a_cname   Component Name
     * @param    string        $a_slot_id Slot ID
     * @param    string        $a_pname   Plugin Name
     *
     * @param           string $a_class_file_name
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
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return    bool
     * @throws ilPluginException
     */
    public function hasLearningProgress($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["learning_progress"];
    }


    /**
     * Checks whether plugin supports export/import
     *
     * @param    string $a_ctype   Component Type
     * @param    string $a_cname   Component Name
     * @param    string $a_slot_id Slot ID
     * @param    string $a_pname   Plugin Name
     *
     * @return    bool
     * @throws ilPluginException
     */
    public function supportsExport($a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        $this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["supports_export"];
    }


    /**
     * Get info for all plugins.
     *
     * @return    array<string, array>
     */
    public static function getAllPlugins()
    {
        $cached_component = ilCachedComponentData::getInstance();

        return $cached_component->getIlPluginById();
    }


    /**
     * Get info for all active plugins.
     *
     * @return    array
     */
    public static function getActivePlugins()
    {
        $cached_component = ilCachedComponentData::getInstance();
        $plugins = $cached_component->getIlPluginActive();
        $buf = array();
        foreach ($plugins as $slot => $plugs) {
            $buf = array_merge($buf, $plugs);
        }

        return $buf;
    }


    /**
     * Check, if a plugin is active
     *
     * @param    string $id id of the plugin
     *
     * @return    boolean
     */
    public static function isPluginActive($id)
    {
        assert(is_string($id));
        $cached_component = ilCachedComponentData::getInstance();
        $plugs = $cached_component->getIlPluginById();
        if (array_key_exists($id, $plugs) && $plugs[$id]['active']) {
            return true;
        }

        return false;
    }


    /**
     * Get a plugin-object by id
     *
     * @param    string $id id of the plugin
     *
     * @throws    InvalidArgumentException    if no plugin with that id is found
     * @return    ilPlugin
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
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider[]
     */
    public static function getAllGlobalScreenProviders() : array
    {
        $providers = array();
        foreach (self::getActivePlugins() as $plugin) {
            $pl = self::getPluginObjectById($plugin['plugin_id']);
            if ($pl instanceof ilPlugin && $pl->isActive()) {
                array_push($providers, $pl->promoteGlobalScreenProvider());
            }
        }

        return $providers;
    }
}
