<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
