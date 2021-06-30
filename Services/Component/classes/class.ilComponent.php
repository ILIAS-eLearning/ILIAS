<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_COMP_MODULE", "Modules");
define("IL_COMP_SERVICE", "Services");
define("IL_COMP_PLUGIN", "Plugins");
define("IL_COMP_SLOTS", "Slots");

/**
* @defgroup ServicesComponent Services/Component
*
* ILIAS Component. This is the parent class for all ILIAS components.
* Components are Modules (Modules are ressources that can be added to the
* ILIAS repository), Services (Services provide cross-sectional functionalities
* for other ILIAS components) and Plugins.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilComponent
{
    /**
    * Get Version Number of Component. The number should be changed
    * if anything in the code is changed. Otherwise ILIAS will not be able
    * to recognize any change in the module.
    *
    * The format must be:
    * <major number>.<minor number>.<bugfix number>
    * <bugfix number> should be increased for bugfixes
    * <minor number> should be increased for behavioural changes (and new functionalities)
    * <major number> should be increased for major revisions
    *
    * The number should be returned directly as string, e.g. return "1.0.2";
    *
    * @return	string		version number
    */
    abstract public function getVersion();
    
    abstract public function isCore();

    /**
     * @return string
     */
    abstract public function getComponentType() : string;
    
    /**
    * Get Name.
    *
    * @return	string	Name
    */
    abstract public function getName();

    public function __construct()
    {
        $this->component_data_db = new ilArtifactComponentDataDB();

        $this->setId(
            $this->component_data_db->getComponent(
                $this->getComponentType(),
                $this->getName()
            )->getId()
        );
        $this->setPluginSlots(ilComponent::lookupPluginSlots(
            $this->getComponentType(),
            $this->getName()
        ));
    }
    
    /**
    * Set Id.
    *
    * @param	string	$a_id	Id
    */
    final public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * Get Id.
    *
    * @return	string	Id
    */
    final public function getId()
    {
        return $this->id;
    }

    /**
    * Set Plugin Slots.
    *
    * @param	array	$a_pluginslots	Plugin Slots
    */
    final public function setPluginSlots($a_pluginslots)
    {
        $this->pluginslots = $a_pluginslots;
    }

    /**
    * Get Plugin Slots.
    *
    * @return	array	Plugin Slots
    */
    final public function getPluginSlots()
    {
        return $this->pluginslots;
    }

    /**
    * Get component object.
    *
    * @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
    * @param	string	$a_cname	component name
    */
    final public static function getComponentObject($a_ctype, $a_cname)
    {
        $component_data_db = new ilArtifactComponentDataDB();
        if (!$component_data_db->hasComponent($a_ctype, $a_cname)) {
            return null;
        }
        
        switch ($a_ctype) {
            case IL_COMP_MODULE:
                return new ilModule($a_cname);
            case IL_COMP_SERVICE:
                return new ilService($a_cname);
            default:
                throw new \UnexpectedValueException(
                    "Unknown component type: $a_ctype"
                );
        }
        
        return null;
    }

    /**
    * Set Sub Directory.
    *
    * @param	string	$a_subdirectory	Sub Directory
    */
    public function setSubDirectory($a_subdirectory)
    {
        $this->subdirectory = $a_subdirectory;
    }

    /**
    * Get Sub Directory.
    *
    * @return	string	Sub Directory
    */
    public function getSubDirectory()
    {
        return $this->subdirectory;
    }
    
    /**
    * Lookup all plugin slots of a component
    */
    public static function lookupPluginSlots($a_type, $a_name)
    {
        $cached_component = ilCachedComponentData::getInstance();
        $recs = $cached_component->lookupPluginSlotByComponent($a_type . "/" . $a_name);

        $ps = array();
        foreach ($recs as $rec) {
            $rec["dir"] = "Customizing/global/plugins/" . $a_type . "/" . $a_name . "/" . $rec["name"];
            $rec["dir_pres"] = "Customizing/global/plugins/<br />" . $a_type . "/" . $a_name . "/" . $rec["name"];
            $rec["lang_prefix"] = ilComponent::lookupId($a_type, $a_name) . "_" . $rec["id"] . "_";
            $ps[$rec["id"]] = $rec;
        }
        return $ps;
    }
    
    /**
    * Get name of plugin slot.
    *
    * @param	string	$a_id	Plugin Slot ID
    */
    public function getPluginSlotName($a_id)
    {
        $slots = $this->getPluginSlots();
        
        return $slots[$a_id]["name"];
    }

    /**
    * Get directory of plugin slot.
    *
    * @param	string	$a_id	Plugin Slot ID
    */
    public function getPluginSlotDirectory($a_id)
    {
        $slots = $this->getPluginSlots();
        
        return "Customizing/global/plugins/" . $this->getComponentType() . "/" .
            $this->getName() . "/" . $slots[$a_id]["name"];
    }
    
    /**
    * Get language prefix for plugin slot.
    *
    * @param	string	$a_id	Plugin Slot ID
    */
    public function getPluginSlotLanguagePrefix($a_id)
    {
        $slots = $this->getPluginSlots();
        return $this->getId() . "_" . $slots[$a_id]["id"] . "_";
    }
    
    /**
    * Lookup ID of a component
    */
    public static function lookupId($a_type, $a_name)
    {
        $component_data_db = new ilArtifactComponentDataDB();
        return $component_data_db->getComponentByTypeAndName($a_type, $a_name)->getId();
    }
    
    /**
    * Check version number.
    */
    final public static function checkVersionNumber($a_ver)
    {
        $parts = explode(".", $a_ver);

        if (count($parts) < 2 || count($parts) > 3) {
            return "Version number does not conform to format a.b or a.b.c";
        }
        
        if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return "Not all version number parts a.b or a.b.c are numeric.";
        }

        if (isset($parts[2]) && !is_numeric($parts[2])) {
            return "Not all version number parts a.b.c are numeric.";
        }

        return $parts;
    }

    final public static function isVersionGreaterString($a_ver1, $a_ver2)
    {
        $a_arr1 = ilComponent::checkVersionNumber($a_ver1);
        $a_arr2 = ilComponent::checkVersionNumber($a_ver2);

        if (is_array($a_arr1) && is_array($a_arr2)) {
            return ilComponent::isVersionGreater($a_ver1, $a_ver2);
        } else {
            return false;
        }
    }

    /**
     * @param string $version1
     * @param string $version2
     * @return bool
     */
    final public static function isVersionGreater(string $version1, string $version2) : bool
    {
        return version_compare($version1, $version2, '>');
    }
    
    /**
     * lookup component name
     * @global type $ilDB
     * @param type $a_component_id
     * @return type
     */
    public static function lookupComponentName($a_component_id)
    {
        $component_data_db = new ilArtifactComponentDataDB();
        if (!$component_data_db->hasComponent($a_component_id)) {
            return null;
        }
        return $component_data_db->getComponentById($a_component_id)->getName();
    }
}
