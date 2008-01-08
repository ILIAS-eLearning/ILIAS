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


/**
* Block Setting class.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilBlockSetting
{
	/**
	* Lookup setting from database.
	*
	*/
	public static function _lookup($a_type, $a_setting, $a_user = 0, $a_block_id = 0)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_block_setting WHERE type = ".
			$ilDB->quote($a_type)." AND user = ".
			$ilDB->quote($a_user)." AND setting = ".
			$ilDB->quote($a_setting)." AND block_id = ".
			$ilDB->quote($a_block_id);
		$set = $ilDB->query($query);
		if ($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			return $rec["value"];
		}
		else
		{
			return false;
		}
	}

	/**
	* Write setting to database.
	*
	*/
	public static function _write($a_type, $a_setting, $a_value, $a_user = 0, $a_block_id = 0)
	{
		global $ilDB;
		
		$query = "REPLACE INTO il_block_setting  (type, user, setting, block_id, value) VALUES (".
			$ilDB->quote($a_type).", ".$ilDB->quote($a_user).",".
			$ilDB->quote($a_setting).",".$ilDB->quote($a_block_id).",".$ilDB->quote($a_value).")";
		$ilDB->query($query);
	}

	/**
	* Lookup detail level.
	*
	*/
	public static function _lookupDetailLevel($a_type, $a_user = 0, $a_block_id = 0)
	{
		$detail = ilBlockSetting::_lookup($a_type, "detail", $a_user, $a_block_id);

		if ($detail === false)		// return a level of 2 (standard value)
		{							// if record does not exist
			return 2;
		}
		else
		{
			return $detail;
		}
	}

	/**
	* Write detail level to database.
	*
	*/
	public static function _writeDetailLevel($a_type, $a_value, $a_user = 0, $a_block_id = 0)
	{
		ilBlockSetting::_write($a_type, "detail", $a_value, $a_user, $a_block_id);
	}

	/**
	* Lookup number.
	*
	*/
	public static function _lookupNr($a_type, $a_user = 0, $a_block_id = 0)
	{
		$nr = ilBlockSetting::_lookup($a_type, "nr", $a_user, $a_block_id);

		return $nr;
	}

	/**
	* Write number to database.
	*
	*/
	public static function _writeNumber($a_type, $a_value, $a_user = 0, $a_block_id = 0)
	{
		ilBlockSetting::_write($a_type, "nr", $a_value, $a_user, $a_block_id);
	}

	/**
	* Lookup side.
	*
	*/
	public static function _lookupSide($a_type, $a_user = 0, $a_block_id = 0)
	{
		$side = ilBlockSetting::_lookup($a_type, "side", $a_user, $a_block_id);

		return $side;
	}

	/**
	* Write side to database.
	*
	*/
	public static function _writeSide($a_type, $a_value, $a_user = 0, $a_block_id = 0)
	{
		ilBlockSetting::_write($a_type, "side", $a_value, $a_user, $a_block_id);
	}

	/**
	* Delete block settings of user
	*
	*/
	public static function _deleteSettingsOfUser($a_user)
	{
		global $ilDB;
		
		if ($a_user > 0)
		{
			$query = "DELETE FROM il_block_setting  WHERE user = ".
				$ilDB->quote($a_user);

			$ilDB->query($query);
		}
	}

	/**
	* Delete block settings of block
	*
	*/
	public static function _deleteSettingsOfBlock($a_block_id, $a_block_type)
	{
		global $ilDB;
		
		if ($a_block_id > 0)
		{
			$query = "DELETE FROM il_block_setting  WHERE block_id = ".
				$ilDB->quote($a_block_id).
				" AND type = ".$ilDB->quote($a_block_type);

			$ilDB->query($query);
		}
	}
	
}
?>
