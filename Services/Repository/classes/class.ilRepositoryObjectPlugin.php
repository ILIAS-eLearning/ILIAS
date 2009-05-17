<?php

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all repository object plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRepository
*/
abstract class ilRepositoryObjectPlugin extends ilPlugin
{
	/**
	* Get Component Type
	*
	* @return        string        Component Type
	*/
	final function getComponentType()
	{
			return IL_COMP_SERVICE;
	}
	
	/**
	* Get Component Name.
	*
	* @return        string        Component Name
	*/
	final function getComponentName()
	{
			return "Repository";
	}

	/**
	* Get Slot Name.
	*
	* @return        string        Slot Name
	*/
	final function getSlot()
	{
			return "RepositoryObject";
	}

	/**
	* Get Slot ID.
	*
	* @return        string        Slot Id
	*/
	final function getSlotId()
	{
			return "robj";
	}

	/**
	* Object initialization done by slot.
	*/
	protected final function slotInit()
	{
			// nothing to do here
	}
	
	/**
	* Get Icon
	*/
	static function _getIcon($a_type, $a_size)
	{
		switch($a_size)
		{
			case "small": $suff = ""; break;
			case "tiny": $suff = "_s"; break;
			default: $suff = "_b"; break;
		}
		return ilPlugin::_getImagePath(IL_COMP_SERVICE, "Repository", "robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj",$a_type),
			"icon_".$a_type.$suff.".gif");
	}
	
	/**
	* Get class name
	*/
	function _getName($a_id)
	{
		$name = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj",$a_id);
		if ($name != "")
		{
			return $name;
		}
	}
	
}
?>
