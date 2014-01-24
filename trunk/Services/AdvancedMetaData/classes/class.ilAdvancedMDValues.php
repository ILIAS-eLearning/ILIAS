<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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

	/**
	 * Get all values of an object per subtype
	 * Uses internal cache.
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _getValuesByObjIdAndSubtype($a_obj_id, $a_subtype)
	{
		global $ilDB;

		$result = array();
		$set = $ilDB->query($q = "SELECT field_id, value, sub_type, sub_id FROM adv_md_values ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND sub_type = ".$ilDB->quote($a_subtype,'text')
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}

		return $result;
	}
	
	/**
	 * Query data for given object records
	 *
	 * @param
	 * @return
	 */
	static public function queryForRecords($a_obj_id, $a_subtype, $a_records, $a_obj_id_key, $a_obj_subid_key, $a_amet_filter = "")
	{
		$result = $val = array();
		if (!is_array($a_obj_id))
		{
			$a_obj_id = array($a_obj_id);
		}
		
		// read amet data
		foreach ($a_obj_id as $obj_id)
		{
			$values = self::_getValuesByObjIdAndSubtype($obj_id, $a_subtype);
			foreach ($values as $v)
			{
				$val[$obj_id][$v["sub_id"]][$v["field_id"]] = $v;
			}
		}
//var_dump($a_amet_filter);
		// add amet data to records
		foreach ($a_records as $rec)
		{
			// check filter
			$skip = false;
			if (is_array($a_amet_filter))
			{
				foreach ($a_amet_filter as $fk => $fv)
				{
					if (!$skip && $fv != "" && substr($fk, 0, 3) == "md_")
					{
						$fka = explode("_", $fk);
						
						if (!isset($val[$rec[$a_obj_id_key]][$rec[$a_obj_subid_key]][$fka[1]]["value"]))
						{
							$skip = true;
						}
						else
						{
							$md_val = $val[$rec[$a_obj_id_key]][$rec[$a_obj_subid_key]][$fka[1]]["value"];
							if (trim($md_val) != trim($fv))
							{
								$skip = true;
							}
						}
					}
				}
			}
			if ($skip)
			{
				continue;
			}
			
			
			if (is_array($val[$rec[$a_obj_id_key]][$rec[$a_obj_subid_key]]))
			{
				foreach ($val[$rec[$a_obj_id_key]][$rec[$a_obj_subid_key]] as $k => $v)
				{
					$rec["md_".$k] = $v["value"];
				}
			}
			$results[] = $rec;
		}

		return $results;
	}

}
?>