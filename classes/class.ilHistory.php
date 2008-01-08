<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
	* Please note that the object type must be specified, if the object is not
	* derived from ilObject.
	*
	* @param	int			$a_obj_id		object id
	* @param	string		$a_action		action
	* @param	string		$a_info_params	information text parameters, separated by comma
	*										or as an array
	* @param	string		$a_obj_type		object type (must only be set, if object is not
	*										in object_data table)
	* @param	string		$a_user_comment	user comment
	*/
	function _createEntry($a_obj_id, $a_action, $a_info_params = "", $a_obj_type = "",
		$a_user_comment = "", $a_update_last = false)
	{
		global $ilDB, $ilUser;
		
		if ($a_obj_type == "")
		{
			$a_obj_type = ilObject::_lookupType($a_obj_id);
		}
		
		if (is_array($a_info_params))
		{
			foreach($a_info_params as $key => $param)
			{
				$a_info_params[$key] = str_replace(",", "&#044;", $param);
			}
			$a_info_params = implode(",", $a_info_params);
		}
		
		// get last entry of object
		$last_entry_sql = "SELECT * FROM history WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			" obj_type = ".$ilDB->quote($a_obj_type)." ORDER BY hdate DESC limit 1";
		$last_entry_set = $ilDB->query($last_entry_sql);
		$last_entry = $last_entry_set->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		// note: insert is forced if last entry already has a comment and a 
		// new comment is given too OR
		// if entry should not be updated OR
		// if current action or user id are not equal with last entry
		if (($a_user_comment != "" && $last_entry["user_comment"] != "")
			|| !$a_update_last || $a_action != $last_entry["action"]
			|| $ilUser->getId() != $last_entry["usr_id"])
		{
			$query = "INSERT INTO history (obj_id, obj_type, action, hdate, usr_id, info_params, user_comment) VALUES ".
				"(".
				$ilDB->quote($a_obj_id).", ".
				$ilDB->quote($a_obj_type).", ".
				$ilDB->quote($a_action).", ".
				"now(), ".
				$ilDB->quote($ilUser->getId()).", ".
				$ilDB->quote($a_info_params).", ".
				$ilDB->quote($a_user_comment).
				")";
			$ilDB->query($query);
		}
		else
		{
			// if entry should be updated, update user comment only
			// if it is set (this means, user comment has been empty
			// because if old and new comment are given, an INSERT is forced
			// see if statement above)
			$uc_str = ($a_user_comment != "")
				? ", user_comment = ".$ilDB->quote($a_user_comment)
				: "";
			$query = "UPDATE history SET ".
				" hdate = now() ".
				$uc_str.
				" WHERE id = ".$ilDB->quote($last_entry["id"]);
			$ilDB->query($query);
		}
		
	}
	
	/**
	* get all history entries for an object
	*
	* @param	int		$a_obj_id		object id
	*
	* @return	array	array of history entries (arrays with keys
	*					"date", "user_id", "obj_id", "action", "info_params")
	*/
	function _getEntriesForObject($a_obj_id, $a_obj_type = "")
	{
		global $ilDB;

		if ($a_obj_type == "")
		{
			$a_obj_type = ilObject::_lookupType($a_obj_id);
		}
		
		$query = "SELECT * FROM history WHERE obj_id = ".
			$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type).
			" ORDER BY hdate DESC";

		$hist_set = $ilDB->query($query);

		$hist_items = array();
		while ($hist_rec = $hist_set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$hist_items[] = array("date" => $hist_rec["hdate"],
				"user_id" => $hist_rec["usr_id"],
				"obj_id" => $hist_rec["obj_id"],
				"obj_type" => $hist_rec["obj_type"],
				"action" => $hist_rec["action"],
				"info_params" => $hist_rec["info_params"],
				"user_comment" => $hist_rec["user_comment"],
				"hist_entry_id" => $hist_rec["id"],
				"title" => $hist_rec["title"]);
		}

		if ($a_obj_type == "lm" || $a_obj_type == "dbk")
		{
			$query = "SELECT h.*, l.title as title FROM history as h, lm_data as l WHERE ".
				" l.lm_id = ".$ilDB->quote($a_obj_id)." AND ".
				" l.obj_id = h.obj_id ".
				" ORDER BY h.hdate DESC";
				
			$hist_set = $ilDB->query($query);
			while ($hist_rec = $hist_set->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$hist_items[] = array("date" => $hist_rec["hdate"],
					"user_id" => $hist_rec["usr_id"],
					"obj_id" => $hist_rec["obj_id"],
					"obj_type" => $hist_rec["obj_type"],
					"action" => $hist_rec["action"],
					"info_params" => $hist_rec["info_params"],
					"user_comment" => $hist_rec["user_comment"],
					"hist_entry_id" => $hist_rec["id"],
					"title" => $hist_rec["title"]);
			}
			usort($hist_items, array("ilHistory", "_compareHistArray"));
			$hist_items2 = array_reverse($hist_items);
			return $hist_items2;
		}

		return $hist_items;
	}

	function _compareHistArray($a, $b)
	{
		if ($a["date"] == $b["date"])
		{
			return 0;
		}
		return ($a["date"] < $b["date"]) ? -1 : 1;
	}

	/**
	* remove all history entries for an object
	*
	* @param	int		$a_obj_id		object id
	*
	* @return	boolean
	*/
	function _removeEntriesForObject($a_obj_id)
	{
		global $ilDB;

		$q = "DELETE FROM history WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$r = $ilDB->query($q);
		
		return true;
	}
	
	/**
	* copy all history entries for an object
	*
	* @param	integer $a_src_id		source object id
	* @param	integer $a_dst_id		destination object id
	* @return	boolean
	*/
	function _copyEntriesForObject($a_src_id,$a_dst_id)
	{
		global $ilDB;

		$q = "SELECT * FROM history WHERE obj_id = ".$ilDB->quote($a_src_id);
		$r = $ilDB->query($q);
		
		while ($row = $r->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$q = "INSERT INTO history (obj_id, obj_type, action, hdate, usr_id, info_params, user_comment) VALUES ".
				 "(".
					$ilDB->quote($a_dst_id).", ".
					$ilDB->quote($row->obj_type).", ".
					$ilDB->quote($row->action).", ".
					$ilDB->quote($row->hdate).", ".
					$ilDB->quote($row->usr_id).", ".
					$ilDB->quote($row->info_params).", ".
					$ilDB->quote($row->user_comment).
				 ")";

			$ilDB->query($q);
		}
		
		return true;
	}
	
	/**
	 * returns a single history entry
	 * 
	 * 
	 */
	function _getEntryByHistoryID($a_hist_entry_id)
	{
		global $ilDB;

		$q = "SELECT * FROM history WHERE id = ".$ilDB->quote($a_hist_entry_id);
		$r = $ilDB->query($q);
		
		return $r->fetchRow(MDB2_FETCHMODE_ASSOC);
	}
} // END class.ilHistory
?>
