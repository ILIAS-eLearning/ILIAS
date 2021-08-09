<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Component/classes/class.ilPlugin.php");

/**
* Plugin Slot
*
* A plugin slot defines an interface for a set of
* plugins that share the same characteristics
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
class ilPluginSlot
{
    protected $prefix = "";

    protected ilComponentDataDB $component_data_db;

    /**
    * Constructor
    */
    public function __construct($a_c_type, $a_c_name, $a_slot_id)
    {
        global $DIC;
        $this->component_data_db = $DIC["component.db"];

        $this->setComponentType($a_c_type);
        $this->setComponentName($a_c_name);
        $this->setSlotId($a_slot_id);
        
        if ($a_slot_id != "") {
            $this->read();
        }
    }
    
    /**
    * Read properties from DB
    */
    public function read()
    {
        $component_data_db = $this->component_data_db;
        $this->setSlotName($component_data_db->getPluginSlotById($this->getSlotId())->getName());
    }
    
    /**
    * Set Component Type.
    *
    * @param	string	$a_componenttype	Component Type
    */
    public function setComponentType($a_componenttype)
    {
        $this->componenttype = $a_componenttype;
    }

    /**
    * Get Component Type.
    *
    * @return	string	Component Type
    */
    public function getComponentType()
    {
        return $this->componenttype;
    }

    /**
    * Set Component Name.
    *
    * @param	string	$a_componentname	Component Name
    */
    public function setComponentName($a_componentname)
    {
        $this->componentname = $a_componentname;
    }

    /**
    * Get Component Name.
    *
    * @return	string	Component Name
    */
    public function getComponentName()
    {
        return $this->componentname;
    }

    /**
    * Set Slot ID.
    *
    * @param	string	$a_slotid	Slot ID
    */
    public function setSlotId($a_slotid)
    {
        $this->slotid = $a_slotid;
    }

    /**
    * Get Slot ID.
    *
    * @return	string	Slot ID
    */
    public function getSlotId()
    {
        return $this->slotid;
    }

    /**
    * Set Slot Name.
    *
    * @param	string	$a_slotname	Slot Name
    */
    public function setSlotName($a_slotname)
    {
        $this->slotname = $a_slotname;
    }

    /**
    * Get Slot Name.
    *
    * @return	string	Slot Name
    */
    public function getSlotName()
    {
        return $this->slotname;
    }

    /**
    * Get directory of
    */
    public function getPluginsDirectory()
    {
        return "./Customizing/global/plugins/" . $this->getComponentType() .
            "/" . $this->getComponentName() . "/" . $this->getSlotName();
    }
    
    /**
    * Get plugins directory
    */
    public static function _getPluginsDirectory($a_ctype, $a_cname, $a_slot_id)
    {
        return "./Customizing/global/plugins/" . $a_ctype .
            "/" . $a_cname . "/" . ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);
    }
    
    
    /**
    * Get File name for plugin.php
    */
    public function getPluginPhpFileName($a_plugin_name)
    {
        return $this->getPluginsDirectory() . "/" .
            $a_plugin_name . "/plugin.php";
    }
    
    /**
    * Check whether plugin.php file is available for plugin or not
    */
    public function checkPluginPhpFileAvailability($a_plugin_name)
    {
        if (@is_file($this->getPluginPhpFileName($a_plugin_name))) {
            return true;
        }
        
        return false;
    }
    
    /**
    * Get Class File name for plugin
    */
    public function getPluginClassFileName($a_plugin_name)
    {
        return $this->getPluginsDirectory() . "/" .
            $a_plugin_name . "/classes/class.il" . $a_plugin_name . "Plugin.php";
    }

    /**
    * Check whether Plugin class file is available for plugin or not
    */
    public function checkClassFileAvailability($a_plugin_name)
    {
        if (@is_file($this->getPluginClassFileName($a_plugin_name))) {
            return true;
        }
        
        return false;
    }
    
    /**
    * Get slot prefix, used for lang vars and db tables. Needs
    * plugin id appended.
    */
    public function getPrefix()
    {
        if ($this->prefix == "") {
            $this->prefix =
                ilComponent::lookupId(
                    $this->getComponentType(),
                    $this->getComponentName()
                ) . "_" . $this->getSlotId();
        }
            
        return $this->prefix;
    }

    /**
    * Lookup slot name for component and slot id
    */
    public static function lookupSlotName($a_ctype, $a_cname, $a_slot_id)
    {
        global $DIC;
        $component_data_db = $DIC["component.db"];
        return $component_data_db->getPluginSlotById($a_slot_id)->getName();
    }
}
