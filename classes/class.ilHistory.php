<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* This class methods for maintain history enties for objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @package ilias-core
*/
class ilHistory
{

	/**
	* Creates a new history entry for an object. The information text parameters
	* have to be separated by comma. The information text has to be stored
	* in a langage variable "hist_<object_type>_<action>". This text can contain
	* placeholders %1, %2, ... for each parameter. The placehoders are replaced
	* by the parameters in ilHistoryGUI->getHistoryTable().
	*
	* @param	int			$a_obj_id		object id
	* @param	string		$a_action		action
	* @param	string		$a_info_params	information text parameters, separated by comma
	*/
	function _createEntry($a_obj_id, $a_action, $a_info_params)
	{
		global $ilDB, $ilUser;
		
		$query = "INSERT INTO history (obj_id, action, hdate, usr_id, info_params) VALUES ".
			"(".
			$ilDB->quote($a_obj_id).", ".
			$ilDB->quote($a_action).", ".
			"now(), ".
			$ilDB->quote($ilUser->getId()).", ".
			$ilDB->quote($a_info_params).
			")";
		
		$ilDB->query($query);
	}
	
	/**
	* get all history entries for an object
	*
	* @param	int		$a_obj_id		object id
	*
	* @return	array	array of history entries (arrays with keys
	*					"date", "user_id", "obj_id", "action", "info_params")
	*/
	function _getEntriesForObject($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM history WHERE obj_id = ".
			$ilDB->quote($a_obj_id).
			" ORDER BY hdate";

		$hist_set = $ilDB->query($query);

		$hist_items = array();
		while ($hist_rec = $hist_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$hist_items[] = array("date" => $hist_rec["hdate"],
				"user_id" => $hist_rec["usr_id"],
				"obj_id" => $a_obj_id,
				"action" => $hist_rec["action"],
				"info_params" => $hist_rec["info_params"]);
		}
		
		return $hist_items;
	}

} // END class.ilHistory
?>
