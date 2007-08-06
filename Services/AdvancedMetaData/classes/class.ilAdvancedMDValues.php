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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDValues
{
	private static $cached_values = array();

	/**
	 * Get all values of an object.
	 * Uses internal cache.
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _getValuesByObjId($a_obj_id)
	{
		global $ilDB;
		
		if(isset(self::$cached_values[$a_obj_id]))
		{
			return self::$cached_values[$a_obj_id];
		}
		$query = "SELECT field_id,value FROM adv_md_values ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$res = $ilDB->query($query);
		
		self::$cached_values[$a_obj_id] = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$cached_values[$a_obj_id][$row->field_id] = $row->value;
		}
		return self::$cached_values[$a_obj_id];
	}
	
	/**
	 * preload object values
	 *
	 * @access public
	 * @static
	 *
	 * @param array obj_ids
	 */
	public static function _preloadValuesByObjIds(array $obj_ids)
	{
		global $ilDB;
		
		$query = "SELECT obj_id,field_id,value FROM adv_md_values ".
			"WHERE obj_id IN (".implode("','",$obj_ids).")";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$cached_values[$row->obj_id][$row->field_id] = $row->value;
		}
		return true;
	}
	
	/**
	 * Delete values by field_id.
	 * Typically called after deleting a field
	 *
	 * @access public
	 * @static
	 *
	 * @param int field id
	 */
	public static function _deleteByFieldId($a_field_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM adv_md_values ".
	 		"WHERE field_id = ".$ilDB->quote($a_field_id)." ";
	 	$ilDB->query($query);	
	}
	
	/**
	 * Delete by objekt id 
	 *
	 * @access public
	 * @static
	 * 
	 * @param int obj_id
	 */
	public static function _deleteByObjId($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM adv_md_values ".
	 		"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
	 	$ilDB->query($query);
	}
}
?>