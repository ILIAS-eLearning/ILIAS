<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Component/classes/class.ilComponent.php");

/**
* Administration class for plugins. Handles basic data from plugin.php files.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
class ilPluginAdmin
{
	var $got_data = false;
	
	/**
	 * cached lists of active plugins
	 * @var	array
	 */
	static $active_plugins = array();

	/**
	 * cached lists of plugin objects
	 * @var	array
	 */
	static $plugin_objects = array();
	
	
	/**
	* Constructor
	*/
	function __construct()
	{
	}
	
	/**
	* Get basic data of plugin from plugin.php
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	private final function getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB, $lng;

		if (!isset($this->got_data[$a_ctype][$a_cname][$a_slot_id][$a_pname]))
		{
			include_once "./Services/Component/classes/class.ilPluginSlot.php";
			$slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);

			$plugin_php_file = "./Customizing/global/plugins/".$a_ctype."/".
				$a_cname."/".$slot_name."/".$a_pname."/plugin.php";
				
			$rec = ilPlugin::getPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname);
			
			if (is_file($plugin_php_file))
			{
				include_once($plugin_php_file);
				$this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname] =
					array("version" => $version, "id" => $id,
					"ilias_min_version" => $ilias_min_version,
					"ilias_max_version" => $ilias_max_version,
					"responsible" => $responsible,
					"responsible_mail" => $responsible_mail,
					"learning_progress" => (bool)$learning_progress);
			}
			
			$active = $rec["active"];
			$needs_update = false;
			$activation_possible = !$active;
			$inactive_reason = "";
			
			// version checks
			if (ilComponent::isVersionGreaterString($ilias_min_version, ILIAS_VERSION_NUMERIC))
			{
				$active = false;
				if (is_object($lng))
				{
					$inactive_reason = $lng->txt("cmps_needs_newer_ilias_version");
				}
				else
				{
					$inactive_reason = "Plugin needs a newer version of ILIAS.";
				}
				$activation_possible = false;
			}
			else if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, $ilias_max_version))
			{
				$active = false;
				if (is_object($lng))
				{
					$inactive_reason = $lng->txt("cmps_needs_newer_plugin_version");
				}
				else
				{
					$inactive_reason = "Plugin does not support current version of ILIAS. Newer version of plugin needed.";
				}
				$activation_possible = false;
			}
			else if ($rec["last_update_version"] == "")
			{
				$active = false;
				if (is_object($lng))
				{
					$inactive_reason = $lng->txt("cmps_needs_update");
				}
				else
				{
					$inactive_reason = "Update needed.";
				}
				$needs_update = true;
				$activation_possible = false;
			}
			else if (ilComponent::isVersionGreaterString($rec["last_update_version"], $version))
			{
				$active = false;
				if (is_object($lng))
				{
					$inactive_reason = $lng->txt("cmps_needs_upgrade");
				}
				else
				{
					$inactive_reason = "Upgrade needed.";
				}
				$activation_possible = false;
			}
			else if ($rec["last_update_version"] != $version)
			{
				$active = false;
				if (is_object($lng))
				{
					$inactive_reason = $lng->txt("cmps_needs_update");
				}
				else
				{
					$inactive_reason = "Update needed.";
				}
				$needs_update = true;
				$activation_possible = false;
			}
			
			$this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["is_active"] = $active;
			$this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["inactive_reason"] = $inactive_reason;
			$this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["needs_update"] = $needs_update;
			$this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["activation_possible"] = $activation_possible;

			$this->got_data[$a_ctype][$a_cname][$a_slot_id][$a_pname] = true;
		}
	}
	
	/**
	* Get version of plugin.
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function getVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["version"];
	}

	/**
	* Get Ilias Min Version
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function getIliasMinVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["ilias_min_version"];
	}

	/**
	* Get Ilias Max Version
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function getIliasMaxVersion($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["ilias_max_version"];
	}

	/**
	* Get ID
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function getId($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["id"];
	}

	/**
	* Checks whether plugin is active (include version checks)
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function isActive($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["is_active"];
	}

	/**
	* Checks whether plugin exists
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function exists($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return isset($this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]);
	}

	/**
	* Get version.
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function needsUpdate($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["needs_update"];
	}

	/**
	* Get all data from file in an array
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function getAllData($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname];
	}

	/**
	* Get all active plugins for a slot
	*/
	function getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id)
	{
		// cache the list of active plugins
		if (!isset(self::$active_plugins[$a_ctype][$a_cname][$a_slot_id]))
		{
			include_once "./Services/Component/classes/class.ilPlugin.php";
			
			self::$active_plugins[$a_ctype][$a_cname][$a_slot_id] = 
				ilPlugin::getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id);	
		}
		return self::$active_plugins[$a_ctype][$a_cname][$a_slot_id];
	}
	
	/**
	* Get Plugin Object
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	 * @return ilPlugin the plugin
	*/
	static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		// cache the plugin objects
		if (!isset(self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname]))
		{
			self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname] = 
				ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
		}
		return self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname];
	}
	
   /**
	* Get Plugin Object
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	 * @return ilPlugin the plugin
	*/
	static function includeClass($a_ctype, $a_cname, $a_slot_id, $a_pname,
		$a_class_file_name)
	{
		// cache the plugin objects
		if (!isset(self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname]))
		{
			self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname] = 
				ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
		}
		$pl = self::$plugin_objects[$a_ctype][$a_cname][$a_slot_id][$a_pname];
		$pl->includeClass($a_class_file_name);
	}
	
	/**
	* Checks whether plugin has active learning progress
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	function hasLearningProgress($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$this->getPluginData($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $this->data[$a_ctype][$a_cname][$a_slot_id][$a_pname]["learning_progress"];
	}
}

?>
