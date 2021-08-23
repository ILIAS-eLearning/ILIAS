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
     * Get a plugin-object by id
     *
     * @throws    InvalidArgumentException    if no plugin with that id is found
     */
    public static function getPluginObjectById(string $id) : \ilPlugin
    {
        global $DIC;
        $plugin_info = $DIC["component.db"]->getPluginById($id);

        return self::getPluginObject(
            $plugin_info->getComponent()->getType(),
            $plugin_info->getComponent()->getName(),
            $plugin_info->getPluginSlot()->getId(),
            $plugin_info->getName()
        );
    }
}
