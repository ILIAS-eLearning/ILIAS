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
		global $ilDB;
		
		$q = "SELECT * FROM il_pluginslot WHERE component = ".
			$ilDB->quote($this->getComponentType()."/".$this->getComponentName()).
			" AND id = ".$ilDB->quote($this->getSlotId());
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
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
	* Get XML File name for plugin
	*/
	function getPluginXMLFileName($a_plugin_name)
	{
		return $this->getPluginsDirectory()."/".
			$a_plugin_name."/plugin.xml";
	}
	
	/**
	* Check whether plugin.xml file is available for plugin or not
	*/
	function checkXMLFileAvailability($a_plugin_name)
	{
		if (@is_file($this->getPluginXMLFileName($a_plugin_name)))
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
	* Get information an all plugins and their status.
	*/
	function getPluginsInformation()
	{
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
				if (@is_dir($pl_dir."/".$file))
				{
					$plugin = array();
					
					$plugin = ilPlugin::lookupStoredData($this->getComponentType(),
						$this->getComponentName(), $this->getSlotId(), $file);
					$plugin["name"] = $file;
					$plugin["xml_file_status"] = $this->checkXMLFileAvailability($file);
					$plugin["xml_file_path"] = $this->getPluginXMLFileName($file);
					$plugin["class_file_status"] = $this->checkClassFileAvailability($file);
					$plugin["class_file"] = "class.il".$plugin["name"]."Plugin.php";
					
					$plugins[] = $plugin;
				}
			}
		}

		return $plugins;
	}
	
	/**
	* Lookup slot ID for component and slot name
	*/
	function lookupSlotId($a_ctype, $a_cname, $a_slot_name)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_pluginslot WHERE component = ".
			$ilDB->quote($a_ctype."/".$a_cname).
			" AND name = ".$ilDB->quote($a_slot_name);
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $rec["id"];
	}

	/**
	* Lookup slot name for component and slot id
	*/
	function lookupSlotName($a_ctype, $a_cname, $a_slot_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_pluginslot WHERE component = ".
			$ilDB->quote($a_ctype."/".$a_cname).
			" AND id = ".$ilDB->quote($a_slot_id);
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $rec["name"];
	}

}
?>
