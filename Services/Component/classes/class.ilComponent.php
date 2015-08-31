<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('class.ilCachedComponentData.php');

define ("IL_COMP_MODULE", "Modules");
define ("IL_COMP_SERVICE", "Services");
define ("IL_COMP_PLUGIN", "Plugins");
define ("IL_COMP_SLOTS", "Slots");

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
	abstract function getVersion();
	
	abstract function isCore();
	
	abstract static function getComponentType();
	
	/**
	* Get Name.
	*
	* @return	string	Name
	*/
	abstract function getName();


	/**
	 * @var ilCachedComponentData
	 */
	protected $global_cache;

	function __construct()
	{
//		global $ilDB;
		$this->global_cache = ilCachedComponentData::getInstance();

		$rec = $this->global_cache->lookCompId($this->getName(), $this->getComponentType());
		$this->setId($rec["id"]);
		$this->setPluginSlots(ilComponent::lookupPluginSlots(
			$this->getComponentType(), $this->getName()));

//
//		echo '<pre>' . print_r($data, 1) . '</pre>';
//
//
//		$set = $ilDB->queryF("SELECT * FROM il_component WHERE type = %s ".
//			" AND name = %s", array("text", "text"),
//			array($this->getComponentType(), $this->getName()));
//		$rec = $ilDB->fetchAssoc($set);
//
//		$this->setId($rec["id"]);
//		$this->setPluginSlots(ilComponent::lookupPluginSlots(
//			$this->getComponentType(), $this->getName()));
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
	* Set Plugin Slots.
	*
	* @param	array	$a_pluginslots	Plugin Slots
	*/
	final function setPluginSlots($a_pluginslots)
	{
		$this->pluginslots = $a_pluginslots;
	}

	/**
	* Get Plugin Slots.
	*
	* @return	array	Plugin Slots
	*/
	final function getPluginSlots()
	{
		return $this->pluginslots;
	}

	/**
	* Get component object.
	*
	* @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
	* @param	string	$a_cname	component name
	*/
	final static function getComponentObject($a_ctype, $a_cname)
	{
		global $ilDB;

		$set = $ilDB->queryF("SELECT * FROM il_component WHERE type = %s ".
			" AND name = %s", array("text", "text"),
			array($a_ctype, $a_cname));
		if (!$ilDB->fetchAssoc($set))
		{
			return null;
		}
		
		switch ($a_ctype)
		{
			case IL_COMP_MODULE:
				if (is_file("./Modules/".$a_cname."/classes/class.il".$a_cname."Module.php"))
				{
					include_once("./Modules/".$a_cname."/classes/class.il".$a_cname."Module.php");
					$class = "il".$a_cname."Module";
					$comp = new $class();
					return $comp;
				}
				break;
				
			case IL_COMP_SERVICE:
				if (is_file("./Services/".$a_cname."/classes/class.il".$a_cname."Service.php"))
				{
					include_once("./Services/".$a_cname."/classes/class.il".$a_cname."Service.php");
					$class = "il".$a_cname."Service";
					$comp = new $class();
					return $comp;
				}
				break;
		}
		
		return null;
	}

	/**
	* Set Sub Directory.
	*
	* @param	string	$a_subdirectory	Sub Directory
	*/
	function setSubDirectory($a_subdirectory)
	{
		$this->subdirectory = $a_subdirectory;
	}

	/**
	* Get Sub Directory.
	*
	* @return	string	Sub Directory
	*/
	function getSubDirectory()
	{
		return $this->subdirectory;
	}
	
	/**
	* Lookup all plugin slots of a component
	*/
	static function lookupPluginSlots($a_type, $a_name)
	{
//		global $ilDB;

		$cached_component = ilCachedComponentData::getInstance();
		$recs = $cached_component->lookupPluginSlotByComponent($a_type."/".$a_name);

		//$set = $ilDB->query("SELECT * FROM il_pluginslot WHERE component = ".
		//	$ilDB->quote($a_type."/".$a_name, "text"));
		$ps = array();
//echo "<br>".$a_type."/".$a_name;
		//while($rec = $ilDB->fetchAssoc($set))
		foreach($recs as $rec)
		{
			$rec["dir"] = "Customizing/global/plugins/".$a_type."/".$a_name."/".$rec["name"];
			$rec["dir_pres"] = "Customizing/global/plugins/<br />".$a_type."/".$a_name."/".$rec["name"];
			$rec["lang_prefix"] = ilComponent::lookupId($a_type,$a_name)."_".$rec["id"]."_";
			$ps[$rec["id"]] = $rec;
		}
		return $ps;
	}
	
	/**
	* Get name of plugin slot.
	*
	* @param	string	$a_id	Plugin Slot ID
	*/
	function getPluginSlotName($a_id)
	{
		$slots = $this->getPluginSlots();
		
		return $slots[$a_id]["name"];
	}

	/**
	* Get directory of plugin slot.
	*
	* @param	string	$a_id	Plugin Slot ID
	*/
	function getPluginSlotDirectory($a_id)
	{
		$slots = $this->getPluginSlots();
		
		return "Customizing/global/plugins/".$this->getComponentType()."/".
			$this->getName()."/".$slots[$a_id]["name"];
	}
	
	/**
	* Get language prefix for plugin slot.
	*
	* @param	string	$a_id	Plugin Slot ID
	*/
	function getPluginSlotLanguagePrefix($a_id)
	{
		$slots = $this->getPluginSlots();
		return $this->getId()."_".$slots[$a_id]["id"]."_";
	}
	
	/**
	* Lookup ID of a component
	*/
	static function lookupId($a_type, $a_name)
	{
		$global_cache = ilCachedComponentData::getInstance();

		return $global_cache->lookCompId($a_type, $a_name);

		//global $ilDB;

		//$set = $ilDB->queryF("SELECT * FROM il_component WHERE type = %s ".
		//	" AND name = %s", array("text", "text"),
		//	array($a_type, $a_name));
		//$rec = $ilDB->fetchAssoc($set);
		
		//return $rec["id"];
	}


	/**
	 * @param $a_type
	 * @param $a_name
	 *
	 * @return mixed
	 */
	public static function getComponentInfo($a_type, $a_name){
		$global_cache = ilCachedComponentData::getInstance();

		return $global_cache->lookupCompInfo($a_type, $a_name);
	}
	
	/**
	* Check version number.
	*/
	static final function checkVersionNumber($a_ver)
	{
		global $lng;
		
		$parts = explode(".", $a_ver);
		
		if (count($parts) != 3)
		{
			return "Version Number does not conform to format a.b.c";
		}
		
		if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2]))
		{
			return "Not all version number parts a.b.c are numeric.";
		}
		
		return $parts;
	}

	static final function isVersionGreaterString($a_ver1, $a_ver2)
	{
		$a_arr1 = ilComponent::checkVersionNumber($a_ver1);
		$a_arr2 = ilComponent::checkVersionNumber($a_ver2);
		if (is_array($a_arr1) && is_array($a_arr2))
		{
			return ilComponent::isVersionGreater($a_arr1, $a_arr2);
		}
		else
		{
			return false;
		}
	}

	/**
	* Check whether version number is greater than another version number
	*
	* @param	$a_ver1		array	version number as array as returned by checkVersionNumber()
	* @param	$a_ver2		array	version number as array as returned by checkVersionNumber()
	*
	* $return	boolean		true, if $a_ver1 is greater than $a_ver2
	*/
	static final function isVersionGreater($a_ver1, $a_ver2)
	{
		if ($a_ver1[0] > $a_ver2[0])
		{
			return true;
		}
		else if ($a_ver1[0] < $a_ver2[0])
		{
			return false;
		}
		else if ($a_ver1[1] > $a_ver2[1])
		{
			return true;
		}
		else if ($a_ver1[1] < $a_ver2[1])
		{
			return false;
		}
		else if ($a_ver1[2] > $a_ver2[2])
		{
			return true;
		}

		return false;
	}

}
?>
