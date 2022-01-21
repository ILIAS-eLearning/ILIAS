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

    protected \ilComponentInfo $component;

    public function __construct()
    {
        global $DIC;
        $this->component_repository = $DIC["component.repository"];

        $this->component = $this->component_repository->getComponentByTypeAndName(
            $this->getComponentType(),
            $this->getName()
        );
    }

    /**
    * Get Id.
    *
    * @return	string	Id
    */
    final public function getId()
    {
        return $this->component->getId();
    }

    /**
    * Get Plugin Slots.
    *
    * @return	array	Plugin Slots
    */
    final public function getPluginSlots()
    {
        $pluginslots = [];
        foreach ($this->component->getPluginSlots() as $slot) {
            $pluginslots[$slot->getId()] = [
                "component" => $this->component->getQualifiedName(),
                "id" => $slot->getId(),
                "name" => $slot->getName(),
                "dir_pres" => "Customizing/global/plugins/<br />" . $slot->getQualifiedName(),
                "lang_prefix" => $this->component->getId() . "_" . $slot->getId() . "_"
            ];
        }
        return $pluginslots;
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
        global $DIC;
        $component_repository = $DIC["component.repository"];
        return $component_repository->getComponentByTypeAndName($a_type, $a_name)->getId();
    }

    /**
     * lookup component name
     * @global type $ilDB
     * @param type $a_component_id
     * @return type
     */
    public static function lookupComponentName($a_component_id)
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        if (!$component_repository->hasComponent($a_component_id)) {
            return null;
        }
        return $component_repository->getComponentById($a_component_id)->getName();
    }
}
