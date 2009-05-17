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
* Administration class for plugins. Handles basic data from plugin.php files.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
class ilPluginAdmin
{
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
		
		if (!$this->got_data[$a_ctype][$a_cname][$a_slot_id][$a_pname])
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
					"responsible_mail" => $responsible_mail);
			}
			
			$active = $rec["active"];
			$needs_update = false;
			$activation_possible = !$active;
			
			// version checks
			if (ilComponent::isVersionGreaterString($ilias_min_version, ILIAS_VERSION_NUMERIC))
			{
				$active = false;
				$inactive_reason = $lng->txt("cmps_needs_newer_ilias_version");
				$activation_possible = false;
			}
			else if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, $ilias_max_version))
			{
				$active = false;
				$inactive_reason = $lng->txt("cmps_needs_newer_plugin_version");
				$activation_possible = false;
			}
			else if ($rec["last_update_version"] == "")
			{
				$active = false;
				$inactive_reason = $lng->txt("cmps_needs_update");
				$needs_update = true;
				$activation_possible = false;
			}
			else if (ilComponent::isVersionGreaterString($rec["last_update_version"], $version))
			{
				$active = false;
				$inactive_reason = $lng->txt("cmps_needs_upgrade");
				$activation_possible = false;
			}
			else if ($rec["last_update_version"] != $version)
			{
				$active = false;
				$inactive_reason = $lng->txt("cmps_needs_update");
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
		include_once "./Services/Component/classes/class.ilPlugin.php";
		return ilPlugin::getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id);
	}
	
	/**
	* Get Plugin Object
	*
	* @param	string	$a_ctype	Component Type
	* @param	string	$a_cname	Component Name
	* @param	string	$a_slot_id	Slot ID
	* @param	string	$a_pname	Plugin Name
	*/
	static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		return ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
	}
}

?>
