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


define ("IL_COMP_MODULE", "Modules");
define ("IL_COMP_SERVICE", "Services");
define ("IL_COMP_PLUGIN", "Plugins");

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
abstract class ilPlugin
{
	/**
	* Get Version Number of plugin. The number should be changed
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
	abstract function getVersion();

	/**
	* Get Id. ID is a short alphanumeric identifier (best 3-5 letters)
	* that will be part of language variable and database table prefixes.
	*
	* @return	string	Id
	*/
	abstract function getId();

	/**
	* Constructor
	*/
	function __construct()
	{
	}

	/**
	* Set Component Type.
	*
	* @param	string	$a_componenttype	Component Type
	*/
	final function setComponentType($a_componenttype)
	{
		$this->componenttype = $a_componenttype;
	}

	/**
	* Get Component Type.
	*
	* @return	string	Component Type
	*/
	final function getComponentType()
	{
		return $this->componenttype;
	}

	/**
	* Set Component Name.
	*
	* @param	string	$a_componentname	Component Name
	*/
	final function setComponentName($a_componentname)
	{
		$this->componentname = $a_componentname;
	}

	/**
	* Get Component Name.
	*
	* @return	string	Component Name
	*/
	final function getComponentName()
	{
		return $this->componentname;
	}

	/**
	* Set Slot Name.
	*
	* @param	string	$a_slot	Slot Name
	*/
	final function setSlot($a_slot)
	{
		$this->slot = $a_slot;
	}

	/**
	* Get Slot Name.
	*
	* @return	string	Slot Name
	*/
	final function getSlot()
	{
		return $this->slot;
	}

	/**
	* Set Plugin Name.
	*
	* @param	string	$a_name	Plugin Name
	*/
	final function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	* Get Plugin Name.
	*
	* @return	string	Plugin Name
	*/
	final function getName()
	{
		return $this->name;
	}

	/**
	* Default initialization
	*/
	final private function __init()
	{
	}

	/**
	* Object initialization. Can be overwritten by derived class
	*/
	protected function pluginInit()
	{
	}

	/**
	* Check possible activation
	*/
	final public function checkActivationPossible()
	{
		$result = $this->__checkActivationPossible();
		
		if ($result === true)
		{
			return $this->pluginCheckActivationPossible();
		}
		
		return $result;
	}
	
	/**
	* Check possible activation (internal default checks)
	*/
	final private function __checkActivationPossible()
	{
		global $lng;
		
		// check whether current version has been successfully run its update
		if ($this->getLastUpdatedVersion() != $this->getVersion())
		{
			return $lng->txt("cmps_plugin_needs_update");
		}
		
		return true;
	}
	
	/**
	* Check whether activation is possible.
	*/
	abstract protected function pluginCheckActivationPossible();

	/**
	* Check whether 
	*/
	public final function isActivated()
	{
		global $ilSetting;
		
		if ($this->getLastUpdatedVersion() == $this->getVersion() &&
			$ilSetting->get("plugin_active_".getPrefix()))
		{
			return $this->pluginIsActivated();
		}
		
		return false;
	}
	
	/**
	* Check activation, may be overwritten by plugin
	*/
	protected function pluginIsActivated()
	{
		return true;
	}
	
	/**
	* Check whether update is needed.
	*/
	public final function checkUpdateNeeded()
	{
		if ($this->getLastUpdatedVersion() != $this->getVersion())
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Check whether update is possible
	*/
	public final function checkUpdatePossible()
	{
		if (!$this->__checkUpdatePossible())
		{
			return $this->__checkUpdatePossible();
		}
		
		return $this->pluginCheckUpdatePossible();
	}
	
	/**
	* Default check for possible update
	*/
	final private function __checkUpdatePossible()
	{
		global $lng;
		
		$l = $this->getLastUpdatedVersion();
		$c = $this->getVersion();
		
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
		
		return true;
	}
	
	/**
	* Check whether update is possible, may be overwritten by derived class.
	*/
	protected function pluginCheckUpdatePossible()
	{
		return true;
	}
	
	/**
	* Get version from last update.
	*/
	function getLastUpdatedVersion()
	{
		global $ilSetting;
		
		return $ilSetting->get("plugin_".$this->getPrefix()."_up_version");
	}
	
	/**
	* Get plugin object.
	*
	* @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
	* @param	string	$a_cname	component name
	* @param	string	$a_sname	plugin slot name
	* @param	string	$a_pname	plugin name
	*/
	final static function getPluginObject($a_ctype, $a_cname, $a_sname, $a_pname)
	{
		global $ilDB;
		
		// this check is done due to security reasons
		$set = $ilDB->query("SELECT * FROM il_component WHERE type = ".
			$ilDB->quote($a_ctype)." AND name = ".$ilDB->quote($a_cname));
		if ($set->numRows() == 0)
		{
			return null;
		}
		
		$file = "./Customizing/global/plugins/".$a_ctype."/".
			$a_cname."/".$a_sname."/".
			$a_pname."/classes/class.il".$a_pname."Plugin.php";

		if (is_file($file))
		{
			include_once($file);
			$class = "il".$a_pname."Plugin";
			$plugin = new $class();
			$plugin->setComponentType($a_ctype);
			$plugin->setComponentName($a_cname);
			$plugin->setSlot($a_sname);
			$plugin->setName($a_pname);
			$plugin->__init();		// default initialization
			$plugin->pluginInit();		// individual part of initialization
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
				ilPluginSlot::lookupSlotId($this->getComponentType(),
				$this->getComponentName(), $this->getSlot)."_".$this->getId();
		}
		
		return $this->prefix;
	}
	
	/**
	* Reload plugin information from plugin.xml files into db
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

		$ilDB->query($q);
	}
}
?>
