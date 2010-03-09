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
* @author Stefan Meyer <meyer@leifos.com>
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
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');
		$res = $ilDB->query($query);
		
		self::$cached_values[$a_obj_id] = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$cached_values[$a_obj_id][$row->field_id] = $row->value;
		}
		return self::$cached_values[$a_obj_id];
	}
	
	/**
	 * Clone Advanced Meta Data
	 *
	 * @access public
	 * @static
	 *
	 * @param int source obj_id
	 * @param int target obj_id
	 */
	public static function _cloneValues($a_source_id,$a_target_id)
	{
		global $ilLog;
		
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
		
		
		if(!count($defs = ilAdvancedMDFieldDefinition::_getActiveDefinitionsByObjType(ilObject::_lookupType($a_source_id))))
		{
			$ilLog->write(__METHOD__.': No advanced meta data found.');
			return true;
		}
		
		$ilLog->write(__METHOD__.': Start cloning advanced meta data.');
		
		foreach(self::_getValuesByObjId($a_source_id) as $field_id => $value)
		{
			if(!in_array($field_id,$defs))
			{
				continue;
			}
			$new_value = new ilAdvancedMDValue($field_id,$a_target_id);
			$new_value->setValue($value);
			$new_value->save();
			
		}
		return true;		
	}
	
	/**
	 * Get xml of object values
	 *
	 * @access public
	 * @static
	 * @param object instance of ilXmlWriter
	 * @param int $a_obj_id
	 */
	public static function _appendXMLByObjId(ilXmlWriter $xml_writer,$a_obj_id)
	{
		global $ilDB;
		
		$type = ilObject::_lookupType($a_obj_id);

		// Get active field_definitions
		$query = "SELECT field_id FROM adv_md_record amr ".
			"JOIN adv_md_record_objs amro ON amr.record_id = amro.record_id ".
			"JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id ".
			"WHERE active = 1 ".
			"AND obj_type = ".$ilDB->quote($type ,'text')." ";
			
		$xml_writer->xmlStartTag('AdvancedMetaData');	
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
			$value = ilAdvancedMDValue::_getInstance($a_obj_id,$row->field_id);
			$value->appendXML($xml_writer);
		}
		$xml_writer->xmlEndTag('AdvancedMetaData');	
	}
	
	/**
	 * preload object values
	 *
	 * @access public
	 * @static
	 *
	 * @param array obj_ids
	 */
	public static function _preloadValuesByObjIds($obj_ids)
	{
		global $ilDB;
		
		$query = "SELECT obj_id,field_id,value FROM adv_md_values ".
			"WHERE ".$ilDB->in('obj_id',$obj_ids,false,'integer');
			
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
	 		"WHERE field_id = ".$ilDB->quote($a_field_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
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
	 		"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
	}
}
?>