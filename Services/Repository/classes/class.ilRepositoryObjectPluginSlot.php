<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Helper methods for repository object plugins
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
class ilRepositoryObjectPluginSlot
{
	/**
	* Adds objects that can be created to the add new object list array
	*/
	static function addCreatableSubObjects($a_obj_array)
	{
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Repository", "robj");
		foreach ($pl_names as $pl)
		{
			$pl_id = $ilPluginAdmin->getId(IL_COMP_SERVICE, "Repository", "robj", $pl);
			$a_obj_array[$pl_id] = array("name" => $pl_id, "lng" => $pl_id, "plugin" => true);
		}

		return $a_obj_array;
	}
	
	/**
	* Checks whether a repository type is a plugin or not
	*/
	static function isTypePlugin($a_type, $a_active_status = true)
	{
		global $ilPluginAdmin;
		
		include_once("./Services/Component/classes/class.ilPlugin.php");
		$pname = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $a_type);
		if ($pname == "")
		{
			return false;
		}

		if ($ilPluginAdmin->exists(IL_COMP_SERVICE, "Repository", "robj", $pname))
		{
			if (!$a_active_status ||
				$ilPluginAdmin->isActive(IL_COMP_SERVICE, "Repository", "robj", $pname))
			{
				return true;
			}
		}
		return false;
	}
}
