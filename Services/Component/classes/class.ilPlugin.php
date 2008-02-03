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
	* Constructor
	*/
	function __construct()
	{
	}
	
	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	final function setId($a_id)
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
	protected function init()
	{
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
		
		$file = "./Customizing/global/plugins/".$ilDB->quote($a_ctype)."/".
			$ilDB->quote($a_cname)."/".$ilDB->quote($a_sname)."/".
			$ilDB->quote($a_pname)."/classes/class.il".$ilDB->quote($a_pname)."Plugin.php";
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
			$plugin->init();		// individual part of initialization
			return $plugin;
		}
		
		return null;
	}
	
	/**
	* Lookup ID of a component
	*/
	final static function lookupId($a_type, $a_name)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM il_plugin WHERE ".
			" name = ".$ilDB->quote($a_name));
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $rec["id"];
	}
}
?>
