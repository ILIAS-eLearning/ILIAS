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
	
	/**
	* Constructor
	*/
	function __construct($a_c_type, $a_c_name, $a_slot_id)
	{
		$this->setComponentType($a_c_type);
		$this->setComponentName($a_c_name);
		$this->setSlotId($a_slot_id);
		
		if ($a_slot_id != "")
		{
			$this->read();
		}
	}
	
	/**
	* Read properties from DB
	*/
	function read()
	{
		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookupPluginSlotById($this->getSlotId());
		$this->setSlotName($rec["name"]);
	}
	
	/**
	* Set Component Type.
	*
	* @param	string	$a_componenttype	Component Type
	*/
	function setComponentType($a_componenttype)
	{
		$this->componenttype = $a_componenttype;
	}

	/**
	* Get Component Type.
	*
	* @return	string	Component Type
	*/
	function getComponentType()
	{
		return $this->componenttype;
	}

	/**
	* Set Component Name.
	*
	* @param	string	$a_componentname	Component Name
	*/
	function setComponentName($a_componentname)
	{
		$this->componentname = $a_componentname;
	}

	/**
	* Get Component Name.
	*
	* @return	string	Component Name
	*/
	function getComponentName()
	{
		return $this->componentname;
	}

	/**
	* Set Slot ID.
	*
	* @param	string	$a_slotid	Slot ID
	*/
	function setSlotId($a_slotid)
	{
		$this->slotid = $a_slotid;
	}

	/**
	* Get Slot ID.
	*
	* @return	string	Slot ID
	*/
	function getSlotId()
	{
		return $this->slotid;
	}

	/**
	* Set Slot Name.
	*
	* @param	string	$a_slotname	Slot Name
	*/
	function setSlotName($a_slotname)
	{
		$this->slotname = $a_slotname;
	}

	/**
	* Get Slot Name.
	*
	* @return	string	Slot Name
	*/
	function getSlotName()
	{
		return $this->slotname;
	}

	/**
	* Get directory of 
	*/
	function getPluginsDirectory()
	{
		return "./Customizing/global/plugins/".$this->getComponentType().
			"/".$this->getComponentName()."/".$this->getSlotName();
	}
	
	/**
	* Get plugins directory
	*/
	static function _getPluginsDirectory($a_ctype, $a_cname, $a_slot_id)
	{
		return "./Customizing/global/plugins/".$a_ctype.
			"/".$a_cname."/".ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);
	}
	
	
	/**
	* Get File name for plugin.php
	*/
	function getPluginPhpFileName($a_plugin_name)
	{
		return $this->getPluginsDirectory()."/".
			$a_plugin_name."/plugin.php";
	}
	
	/**
	* Check whether plugin.php file is available for plugin or not
	*/
	function checkPluginPhpFileAvailability($a_plugin_name)
	{
		if (@is_file($this->getPluginPhpFileName($a_plugin_name)))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Get Class File name for plugin
	*/
	function getPluginClassFileName($a_plugin_name)
	{
		return $this->getPluginsDirectory()."/".
			$a_plugin_name."/classes/class.il".$a_plugin_name."Plugin.php";
	}

	/**
	* Check whether Plugin class file is available for plugin or not
	*/
	function checkClassFileAvailability($a_plugin_name)
	{
		if (@is_file($this->getPluginClassFileName($a_plugin_name)))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Get slot prefix, used for lang vars and db tables. Needs
	* plugin id appended.
	*/
	function getPrefix()
	{
		if ($this->prefix == "")
		{
			$this->prefix = 
				ilComponent::lookupId($this->getComponentType(),
				$this->getComponentName())."_".$this->getSlotId();
		}
			
		return $this->prefix;
	}

	/**
	* Get information an all plugins and their status.
	*/
	function getPluginsInformation()
	{
		global $DIC;
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		
		// read plugins directory
		$pl_dir = $this->getPluginsDirectory();

		if (!@is_dir($pl_dir))
		{
			return array();
		}
		
		$dir = opendir($pl_dir);

		$plugins = array();
		while($file = readdir($dir))
		{
			if ($file != "." and
				$file != "..")
			{
				// directories
				if (@is_dir($pl_dir."/".$file) && substr($file, 0, 1) != "." &&
					$this->checkPluginPhpFileAvailability($file)
				) {
					$plugin = ilPlugin::lookupStoredData($this->getComponentType(),
						$this->getComponentName(), $this->getSlotId(), $file);

					// create record in il_plugin table (if not existing)
					if(count($plugin) == 0) {
						ilPlugin::createPluginRecord($this->getComponentType(),
							$this->getComponentName(), $this->getSlotId(), $file);
							
						$plugin = ilPlugin::lookupStoredData($this->getComponentType(),
							$this->getComponentName(), $this->getSlotId(), $file);
					}

					$pdata = $ilPluginAdmin->getAllData($this->getComponentType(),
						$this->getComponentName(), $this->getSlotId(), $file);

					$plugin = array_merge($plugin, $pdata);

					$plugin["name"] = $file;
					$plugin["plugin_php_file_status"] = $this->checkPluginPhpFileAvailability($file);
					$plugin["class_file_status"] = $this->checkClassFileAvailability($file);
					$plugin["class_file"] = $this->getPluginClassFileName($file);
					
					$plugins[] = $plugin;
				}
			}
		}

		return $plugins;
	}
	
	/**
	* Lookup slot ID for component and slot name
	*/
	static function lookupSlotId($a_ctype, $a_cname, $a_slot_name)
	{
		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookupPluginSlotByName($a_slot_name);

		return $rec['id'];
	}

	/**
	* Lookup slot name for component and slot id
	*/
	static function lookupSlotName($a_ctype, $a_cname, $a_slot_id)
	{
		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookupPluginSlotById($a_slot_id);

		return $rec['name'];
	}

	/**
	* Get active plugins of slot
	*/
	function getActivePlugins()
	{
		global $DIC;
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		
		return $ilPluginAdmin->getActivePluginsForSlot($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId());
	}
	
	
	/**
	* Get all plugin slots
	*/
	static function getAllSlots()
	{
		$cached_component = ilCachedComponentData::getInstance();
		$recs = $cached_component->getIlPluginslotById();

		foreach($recs as $rec)
		{
			$pos = strpos($rec["component"], "/");
			$slots[] = array(
				"component_type" => substr($rec["component"], 0, $pos),
				"component_name" => substr($rec["component"], $pos + 1),
				"slot_id" => $rec["id"],
				"slot_name" => $rec["name"]
				);
		}
		
		return $slots;
	}
	
}
?>
