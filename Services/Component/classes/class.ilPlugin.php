<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


include_once("./Services/Component/classes/class.ilComponent.php");

/**
* @defgroup ServicesComponent Services/Component
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilPlugin
{
	/**
	* Constructor
	*/
	final function __construct()
	{
		$this->__init();
	}

	/**
	* Get Component Type
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Component Type
	*/
	abstract function getComponentType();

	/**
	* Get Component Name.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Component Name
	*/
	abstract function getComponentName();

	/**
	* Get Slot Name.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Slot Name
	*/
	abstract function getSlot();

	/**
	* Get Slot ID.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Slot Id
	*/
	abstract function getSlotId();
	
	/**
	* Get Plugin Name. Must be same as in class name il<Name>Plugin
	* and must correspond to plugins subdirectory name.
	*
	* Must be overwritten in plugin class of plugin
	* (and should be made final)
	*
	* @return	string	Plugin Name
	*/
	abstract function getPluginName();

	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	private final function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	final function getId()
	{
		return $this->id;
	}

	/**
	* Set Version of last update.
	*
	* @param	string	$a_lastupdateversion	Version of last update
	*/
	private final function setLastUpdateVersion($a_lastupdateversion)
	{
		$this->lastupdateversion = $a_lastupdateversion;
	}

	/**
	* Get Version of last update.
	*
	* @return	string	Version of last update
	*/
	final function getLastUpdateVersion()
	{
		return $this->lastupdateversion;
	}

	/**
	* Set Current Version (from xml file).
	*
	* @param	string	$a_currentversion	Current Version (from xml file)
	*/
	private final function setCurrentVersion($a_currentversion)
	{
		$this->currentversion = $a_currentversion;
	}

	/**
	* Get Current Version (from xml file).
	*
	* @return	string	Current Version (from xml file)
	*/
	final function getCurrentVersion()
	{
		return $this->currentversion;
	}

	/**
	* Set Required ILIAS min. release.
	*
	* @param	string	$a_iliasminversion	Required ILIAS min. release
	*/
	private final function setIliasMinVersion($a_iliasminversion)
	{
		$this->iliasminversion = $a_iliasminversion;
	}

	/**
	* Get Required ILIAS min. release.
	*
	* @return	string	Required ILIAS min. release
	*/
	final function getIliasMinVersion()
	{
		return $this->iliasminversion;
	}

	/**
	* Set Required ILIAS max. release.
	*
	* @param	string	$a_iliasmaxversion	Required ILIAS max. release
	*/
	private final function setIliasMaxVersion($a_iliasmaxversion)
	{
		$this->iliasmaxversion = $a_iliasmaxversion;
	}

	/**
	* Get Required ILIAS max. release.
	*
	* @return	string	Required ILIAS max. release
	*/
	final function getIliasMaxVersion()
	{
		return $this->iliasmaxversion;
	}

	/**
	* Set Active.
	*
	* @param	boolean	$a_active	Active
	*/
	private final function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* Get Active.
	*
	* @return	boolean	Active
	*/
	final function getActive()
	{
		return $this->active;
	}

	/**
	* Default initialization
	*/
	final private function __init()
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_plugin".
			" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
			" AND component_name = ".$ilDB->quote($this->getComponentName()).
			" AND slot_id = ".$ilDB->quote($this->getSlotId()).
			" AND name = ".$ilDB->quote($this->getPluginName());

		$set = $ilDB->query($q);
		
		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->setId($rec["id"]);
			$this->setLastUpdateVersion($rec["last_update_version"]);
			$this->setCurrentVersion($rec["current_version"]);
			$this->setIliasMinVersion($rec["ilias_min_version"]);
			$this->setIliasMaxVersion($rec["ilias_max_version"]);
			$this->setActive($rec["active"]);
		}
		
		$this->slotInit();
		$this->init();
	}

	/**
	* Object initialization done by slot.
	* Must be overwritten in plugin class of plugin slot.
	*
	* (and should be made protected final)
	*/
	abstract protected function slotInit();

	/**
	* Object initialization. Can be overwritten by plugin class
	* (and should be made protected final)
	*/
	protected function init()
	{
	}

	/**
	* Check possible activation
	*/
	final public function isActivatable()
	{
		// standard check
		$result = $this->__isActivatableCheck();
		
		// check done by slot
		if ($result === true)
		{
			$result = $this->isActivatableSlotCheck();
		}

		// check done by plugin
		if ($result === true)
		{
			return $this->isActivatableCheck();
		}
		
		return $result;
	}
	
	/**
	* Check possible activation (internal default checks)
	*/
	final private function __isActivatableCheck()
	{
		global $lng;
		
		// check whether current version has been successfully run its update
		if ($this->getLastUpdateVersion() != $this->getCurrentVersion())
		{
			return $lng->txt("cmps_plugin_needs_update");
		}
		
		return true;
	}
	
	/**
	* Slot check of plugin activation
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made protected final)
	*/
	abstract protected function isActivatableSlotCheck();
	
	/**
	* Check whether activation is possible.
	*
	* This must be overwritten by plugins plugin class
	* (and should be made protected final)
	*/
	abstract protected function isActivatableCheck();

	/**
	* Check whether plugin is active
	*/
	public final function isActive()
	{
		global $ilSetting;
		
		$result = true;
		
		// check whether current version has been successfully run its update
		if ($this->getLastUpdateVersion() != $this->getCurrentVersion() ||
			$this->getCurrentVersion() != $this->getCodeVersion())
		{
			$result = $lng->txt("cmps_plugin_needs_update");
		}

		// check general activation
		if ($result === true)
		{
			if (!$this->getActive())
			{
				$result = $lng->txt("cmps_plugin_is_deactivated");
			}
		}
		
		return $result;
	}
	
	
	/**
	* Get version of code. Must be overwritten by plugin
	* and return the same value as in plugin.xml.
	*/
	abstract protected function getCodeVersion();
	
	/**
	* Check whether update is needed.
	*/
	public final function needsUpdate()
	{
		if ($this->getLastUpdateVersion() != $this->getCurrentVersion() ||
			$this->getCurrentVersion() != $this->getCodeVersion())
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Check whether update is possible
	*/
	public final function isUpdatePossible()
	{
		if (!$this->__isUpdatePossible())
		{
			return $this->__isUpdatePossible();
		}
		
		return $this->isUpdatePossibleCheck();
	}
	
	/**
	* Default check for possible update
	*/
	final private function __isUpdatePossible()
	{
		global $lng;
		
		$l = $this->getLastUpdateVersion();
		$c = $this->getCurrentVersion();
		
		$lver = ilComponent::checkVersionNumber($l);
		if (!is_array($lver))
		{
			return $lver;
		}
		$cver = ilComponent::checkVersionNumber($c);
		if (!is_array($cver))
		{
			return $lver;
		}
		
		if (!ilComponent::isVersionGreater($cver, $lver))
		{
			return $lng->txt("cmps_plugin_current_code_older_than_last_updated");
		}
		
		$result = $this->isUpdatePossibleCheck();
		
		return $result;
	}
	
	/**
	* Check whether update is possible, may be overwritten by plugins plugin class.
	*
	* (Must not be overwritten by slot's plugin class).
	*
	* (and should be made protected final)
	*/
	protected function isUpdatePossibleCheck()
	{
		return true;
	}
	
	/**
	* Activate 
	*/
	final function activate()
	{
		global $lng, $ilDB;
		
		$result = true;
		
		// first: load xml information
		if (!$this->loadPluginXmlInformation())
		{
			return $lng->txt("cmps_could_not_load_plugin_xml_file");
		}
		
		// check whether update is necessary
		if ($this->needsUpdate())
		{
			$result = $this->isUpdatePossible();
			
			// do update
			if ($result === true)
			{
				$result = $this->update();
			}
		}
		
		// activate plugin
		if ($result === true)
		{
			$q = "UPDATE il_plugin SET active = 1".
				" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
				" AND component_name = ".$ilDB->quote($this->getComponentName()).
				" AND slot_id = ".$ilDB->quote($this->getSlotId()).
				" AND name = ".$ilDB->quote($this->getPluginName());
				
			$ilDB->query($q);
		}
	}

	/**
	* Deactivate 
	*/
	final function deactivate()
	{
		global $ilDB;
		
		$result = true;
		
		$q = "UPDATE il_plugin SET active = 0".
			" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
			" AND component_name = ".$ilDB->quote($this->getComponentName()).
			" AND slot_id = ".$ilDB->quote($this->getSlotId()).
			" AND name = ".$ilDB->quote($this->getPluginName());
			
		$ilDB->query($q);

		return $result;
	}
	
	/**
	* Update
	*/
	final function update()
	{
		global $ilDB;
		
		$result = true;
		
		// DB update
		
		// Load language files
		
		// set last update version to current version
		if ($result === true)
		{
			$q = "UPDATE il_plugin SET last_update_version = current_version ".
				" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
				" AND component_name = ".$ilDB->quote($this->getComponentName()).
				" AND slot_id = ".$ilDB->quote($this->getSlotId()).
				" AND name = ".$ilDB->quote($this->getPluginName());
				
			$ilDB->query($q);
		}
		
		return $result;
	}
	
	/**
	* Get plugin object.
	*
	* @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
	* @param	string	$a_cname	component name
	* @param	string	$a_sname	plugin slot name
	* @param	string	$a_pname	plugin name
	*/
	final static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;
		
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);
		
//echo "1-$pname-";
		// this check is done due to security reasons
		$set = $ilDB->query("SELECT * FROM il_component WHERE type = ".
			$ilDB->quote($a_ctype)." AND name = ".$ilDB->quote($a_cname));
		if ($set->numRows() == 0)
		{
			return null;
		}
		
		$file = "./Customizing/global/plugins/".$a_ctype."/".
			$a_cname."/".$slot_name."/".
			$a_pname."/classes/class.il".$a_pname."Plugin.php";

		if (is_file($file))
		{
			include_once($file);
			$class = "il".$a_pname."Plugin";
			$plugin = new $class();
			return $plugin;
		}
		
		return null;
	}
	
	/**
	* Get prefix for tables and language variables
	*/
	final function getPrefix()
	{
		if ($this->prefix == "")
		{
			$this->prefix = ilComponent::lookupId($this->getComponentType(),
				$this->getComponentName())."_".
				$this->getSlotId()."_".$this->getId();
		}
		
		return $this->prefix;
	}
	
	/**
	* Reload all plugins' information from plugin.xml files into db
	*/
	static function refreshPluginXmlInformation()
	{
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		include_once("./Services/Component/classes/class.ilPluginReader.php");
		
		// modules
		include_once("./Services/Component/classes/class.ilModule.php");
		$modules = ilModule::getAvailableCoreModules();
		foreach ($modules as $module)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE, $module["subdir"]);
			foreach($plugin_slots as $slot)
			{
				$slot_obj = new ilPluginSlot(IL_COMP_MODULE, $module["subdir"], $slot["id"]);
				$plugins = $slot_obj->getPluginsInformation();
				foreach ($plugins as $plugin)
				{
					$reader = new ilPluginReader($plugin["xml_file_path"], IL_COMP_MODULE,
						$module["subdir"], $slot["id"], $plugin["name"]);
					$reader->startParsing();
				}
			}
		}

		// services
		include_once("./Services/Component/classes/class.ilService.php");
		$services = ilService::getAvailableCoreServices();
		foreach ($services as $service)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $service["subdir"]);
			foreach($plugin_slots as $slot)
			{
				$slot_obj = new ilPluginSlot(IL_COMP_SERVICE, $service["subdir"], $slot["id"]);
				$plugins = $slot_obj->getPluginsInformation();
				foreach ($plugins as $plugin)
				{
					$reader = new ilPluginReader($plugin["xml_file_path"], IL_COMP_SERVICE,
						$service["subdir"], $slot["id"], $plugin["name"]);
					$reader->startParsing();
				}
			}
		}
	}
	
	/**
	* Load plugin information for this plugin into db
	*
	* 
	*/
	function loadPluginXmlInformation()
	{
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		include_once("./Services/Component/classes/class.ilPluginReader.php");
		
		$slot_obj = new ilPluginSlot($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId());
		$plugins = $slot_obj->getPluginsInformation();
		foreach ($plugins as $plugin)
		{
			if ($plugin["name"] == $this->getPluginName())
			{
				$reader = new ilPluginReader($plugin["xml_file_path"], IL_COMP_MODULE,
					$module["subdir"], $slot["id"], $plugin["name"]);
				$reader->startParsing();
				
				return true;
			}
		}
		
		return false;
	}

	
	/**
	* Lookup information data in il_plugin
	*/
	static function lookupStoredData($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_plugin WHERE ".
				" component_type = ".$ilDB->quote($a_ctype)." AND ".
				" component_name = ".$ilDB->quote($a_cname)." AND ".
				" slot_id = ".$ilDB->quote($a_slot_id)." AND ".
				" name = ".$ilDB->quote($a_pname);

		$set = $ilDB->query($q);
		
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $rec;
	}
}
?>
